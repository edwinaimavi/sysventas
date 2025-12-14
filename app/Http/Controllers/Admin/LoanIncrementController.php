<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanIncrement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanIncrementController extends Controller
{
    /**
     * Registrar un incremento de préstamo.
     */
    public function store(Request $request)
    {
        // Sucursal actual desde sesión
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // Validamos solo lo que viene del form
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

            // Traer el préstamo SOLO de la sucursal actual
            $loan = Loan::where('id', $data['loan_id'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (! $loan) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
                ], 422);
            }

            // Reglas de negocio: solo permitir incrementar si está aprobado o desembolsado
            if (! in_array($loan->status, ['approved', 'disbursed'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Solo se pueden incrementar préstamos aprobados o desembolsados.',
                ], 422);
            }

            $increment = (float) $data['increment_amount'];
            $oldAmount = (float) $loan->amount;
            $newAmount = $oldAmount + $increment;

            // ============================
            //  RE-CALCULO DE CUOTA/TOTAL
            // ============================
            $termMonths   = (int) $loan->term_months;
            $interestRate = (float) $loan->interest_rate; // %

            if ($termMonths > 0) {
                $interestAmount = $newAmount * ($interestRate / 100);
                $monthly        = $newAmount + $interestAmount;
                $total          = $monthly * $termMonths;

                $loan->monthly_payment = round($monthly, 2);
                $loan->total_payable   = round($total, 2);
            }

            // Actualizamos monto del préstamo
            $loan->amount = $newAmount;

            if (Auth::check()) {
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

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Incremento registrado correctamente.',
                'data'    => [
                    'loan_id'    => $loan->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error registrando incremento de préstamo: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el incremento del préstamo.',
                'error'   => $e->getMessage(), // 👈 para ver el detalle si quieres
            ], 500);
        }
    }


    public function byLoan($loanId)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 403);
        }

        // Aseguramos que el préstamo pertenece a la sucursal actual
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
                    'count'          => $data->count(),
                    'total_increment' => $data->sum('increment_amount'),
                    'last_new_amount' => $data->last()['new_amount'] ?? null,
                ],
            ],
        ]);
    }
}
