<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanIncrement;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanIncrementController extends Controller
{
    /**
     * Registrar un incremento de préstamo.
     * - Recalcula cuota/total con SISTEMA FRANCÉS (cuota fija)
     * - Regenera el cronograma (loan_schedules) con el nuevo monto
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            'loan_id'          => 'required|exists:loans,id',
            'increment_amount' => 'required|numeric|min:0.01',
            'notes'            => 'nullable|string',
        ], [
            'loan_id.required'          => 'Debe especificar el préstamo.',
            'loan_id.exists'            => 'El préstamo seleccionado no es válido.',
            'increment_amount.required' => 'El monto a incrementar es obligatorio.',
            'increment_amount.numeric'  => 'El monto a incrementar debe ser numérico.',
            'increment_amount.min'      => 'El monto a incrementar debe ser mayor a 0.',
        ]);

        try {
            DB::beginTransaction();

            // Traer el préstamo SOLO de la sucursal actual y bloquear fila
            $loan = Loan::where('id', $data['loan_id'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (!$loan) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
                ], 422);
            }

            // Reglas de negocio
            if (!in_array($loan->status, ['approved', 'disbursed'])) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Solo se pueden incrementar préstamos aprobados o desembolsados.',
                ], 422);
            }

            $increment = (float) $data['increment_amount'];
            $oldAmount = (float) $loan->amount;
            $newAmount = $oldAmount + $increment;

            // ============================
            // ✅ RE-CÁLCULO (SISTEMA FRANCÉS)
            // ============================
            $termMonths   = (int) $loan->term_months;
            $interestRate = (float) $loan->interest_rate; // % mensual

            $calc = $this->calcFrenchAmortization($newAmount, $termMonths, $interestRate);

            // Actualizar préstamo
            $loan->amount          = round($newAmount, 2);
            $loan->monthly_payment = $calc['monthly_payment'];
            $loan->total_payable   = $calc['total_payable'];

            if (Auth::check()) {
                // OJO: asegúrate que exista la columna updated_by en loans
                $loan->updated_by = Auth::id();
            }

            $loan->save();

            // Guardar historial en loan_increments
            LoanIncrement::create([
                'loan_id'          => $loan->id,
                'branch_id'        => $branchId,
                'user_id'          => Auth::id(),
                'old_amount'       => $oldAmount,
                'increment_amount' => $increment,
                'new_amount'       => $newAmount,
                'notes'            => $data['notes'] ?? null,
            ]);

            // ============================
            // ✅ REGENERAR CRONOGRAMA
            // ============================
            $disbDate = $loan->disbursement_date ?? now()->toDateString();

            // Borra cronograma anterior
            LoanSchedule::where('loan_id', $loan->id)->delete();

            // Genera cronograma nuevo con el monto actualizado
            $rows = $this->buildFrenchSchedule(
                (float) $loan->amount,
                (int) $loan->term_months,
                (float) $loan->interest_rate,
                $disbDate
            );

            foreach ($rows as $row) {
                LoanSchedule::create(array_merge($row, [
                    'loan_id' => $loan->id,
                ]));
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Incremento registrado correctamente.',
                'data'    => [
                    'loan_id'          => $loan->id,
                    'old_amount'       => $oldAmount,
                    'increment_amount' => $increment,
                    'new_amount'       => $newAmount,
                    'monthly_payment'  => $loan->monthly_payment,
                    'total_payable'    => $loan->total_payable,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error registrando incremento de préstamo: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el incremento del préstamo.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function byLoan($loanId)
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 403);
        }

        // Asegurar que el préstamo pertenece a la sucursal actual
        $loan = Loan::where('id', $loanId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $increments = LoanIncrement::where('loan_id', $loan->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $data = $increments->map(function ($inc) {
            return [
                'id'               => $inc->id,
                'created_at'       => optional($inc->created_at)->format('Y-m-d H:i'),
                'old_amount'       => (float) $inc->old_amount,
                'increment_amount' => (float) $inc->increment_amount,
                'new_amount'       => (float) $inc->new_amount,
                'notes'            => $inc->notes,
                'user_name'        => optional($inc->user)->name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'increments' => $data,
                'summary'    => [
                    'count'           => $data->count(),
                    'total_increment' => $data->sum('increment_amount'),
                    'last_new_amount' => $data->last()['new_amount'] ?? null,
                ],
            ],
        ]);
    }

    // ==========================================================
    // Helpers: Sistema Francés + Cronograma
    // ==========================================================
    private function calcFrenchAmortization(float $amount, int $termMonths, float $interestRatePercent): array
    {
        $r = $interestRatePercent / 100; // mensual decimal
        $n = $termMonths;

        if ($amount <= 0 || $n <= 0) {
            return ['monthly_payment' => 0, 'total_payable' => 0];
        }

        if ($r <= 0) {
            $pmt = $amount / $n;
            return [
                'monthly_payment' => round($pmt, 2),
                'total_payable'   => round($pmt * $n, 2),
            ];
        }

        // PMT = P * [ r*(1+r)^n ] / [ (1+r)^n - 1 ]
        $pow = pow(1 + $r, $n);
        $pmt = $amount * (($r * $pow) / ($pow - 1));

        return [
            'monthly_payment' => round($pmt, 2),
            'total_payable'   => round($pmt * $n, 2),
        ];
    }

    private function buildFrenchSchedule(float $amount, int $termMonths, float $interestRatePercent, string $disbursementDate): array
    {
        $r = $interestRatePercent / 100; // mensual decimal
        $n = $termMonths;

        if ($amount <= 0 || $n <= 0) return [];

        // Cuota fija (PMT)
        if ($r <= 0) {
            $pmt = $amount / $n;
        } else {
            $pow = pow(1 + $r, $n);
            $pmt = $amount * (($r * $pow) / ($pow - 1));
        }

        $pmt = round($pmt, 2);

        $balance = round($amount, 2);
        $start = \Carbon\Carbon::parse($disbursementDate);

        $rows = [];

        for ($i = 1; $i <= $n; $i++) {
            $due = $start->copy()->addMonths($i)->toDateString();

            $opening  = $balance;
            $interest = ($r <= 0) ? 0 : round($opening * $r, 2);
            $amort    = round($pmt - $interest, 2);

            // ajuste por redondeo en la última cuota
            if ($i === $n) {
                $amort = $opening;
                $pmtLast = round($amort + $interest, 2);
                $closing = 0;
            } else {
                $pmtLast = $pmt;
                $closing = round($opening - $amort, 2);
                if ($closing < 0) $closing = 0;
            }

            $rows[] = [
                'installment_no'  => $i,
                'due_date'        => $due,
                'opening_balance' => $opening,
                'interest'        => $interest,
                'amortization'    => $amort,
                'payment'         => $pmtLast,
                'closing_balance' => $closing,
            ];

            $balance = $closing;
        }

        return $rows;
    }
}
