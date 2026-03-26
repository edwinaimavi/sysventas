<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanDisbursement;
use App\Models\LoanSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LoanDisbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Listar desembolsos por préstamo (solo dentro de la sucursal actual)
     */
    /* =========================================================
     | LISTAR DESEMBOLSOS POR PRÉSTAMO
     ========================================================= */
    public function byLoan($loanId)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 403);
        }

        $loan = Loan::where('id', $loanId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $disbursements = LoanDisbursement::where('loan_id', $loan->id)
            ->orderBy('disbursement_date')
            ->orderBy('id')
            ->get();

        $data = $disbursements->map(function ($d) {
            return [
                'id'                => $d->id,
                'disbursement_date' => optional($d->disbursement_date)->format('Y-m-d'),
                'amount'            => (float) $d->amount,
                'method'            => $d->method,
                'reference'         => $d->reference,
                'receipt_number'    => $d->receipt_number,
                'status'            => $d->status,
                'notes'             => $d->notes,
                'receipt_file_url'  => $d->receipt_file ? Storage::url($d->receipt_file) : null,
                'receipt_file_type' => $d->receipt_file
                    ? strtolower(pathinfo($d->receipt_file, PATHINFO_EXTENSION))
                    : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'disbursements' => $data,
                'summary' => [
                    'count'        => $data->count(),
                    'total_amount' => $data->sum('amount'),
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /* =========================================================
     | REGISTRAR DESEMBOLSO + SALIDA DE CAJA
     ========================================================= */
    public function store(Request $request)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            'loan_id'           => 'required|exists:loans,id',
            'amount'            => 'required|numeric|min:0.01',
            'disbursement_date' => 'required|date',
            'method'            => 'nullable|string|max:40',
            'reference'         => 'nullable|string|max:120',
            'receipt_number'    => 'nullable|string|max:120',
            'receipt_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'status'            => 'required|in:pending,completed,reversed',
            'notes'             => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            /* ============================
               PRÉSTAMO
            ============================ */
            $loan = Loan::withSum('disbursements as total_disbursed', 'amount')
                ->where('id', $data['loan_id'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($loan->status, ['approved', 'disbursed'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El préstamo no permite desembolsos.',
                ], 422);
            }

            $alreadyDisbursed = (float) ($loan->total_disbursed ?? 0);
            $loanAmount       = (float) $loan->amount;
            $newAmount        = (float) $data['amount'];

            if ($alreadyDisbursed + $newAmount > $loanAmount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El monto excede el total del préstamo.',
                ], 422);
            }

            /* ============================
               CAJA ABIERTA
            ============================ */
            $cashBox = CashBox::where('branch_id', $branchId)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (! $cashBox) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No existe una caja abierta para realizar el desembolso.',
                ], 422);
            }

            $totalIn = CashMovement::where('cash_box_id', $cashBox->id)
                ->where('type', 'in')
                ->sum('amount');

            $totalOut = CashMovement::where('cash_box_id', $cashBox->id)
                ->where('type', 'out')
                ->sum('amount');

            $available = $totalIn - $totalOut;

            if ($newAmount > $available) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Saldo insuficiente en caja. Disponible: S/ ' . number_format($available, 2),
                ], 422);
            }

            /* ============================
               ARCHIVO
            ============================ */
            if ($request->hasFile('receipt_file')) {
                $data['receipt_file'] = $request->file('receipt_file')
                    ->store('disbursements', 'public');
            }

            /* ============================
               CREAR DESEMBOLSO
            ============================ */
            $data['branch_id'] = $branchId;
            $data['user_id']   = Auth::id();
            $data['disbursement_code'] = $this->generateDisbursementCode();

            $disbursement = LoanDisbursement::create($data);
            // 🔥 Detectar si es incremento
            $hasIncrement = \App\Models\LoanIncrement::where('loan_id', $loan->id)->exists();

            // 🔥 Contar desembolsos ANTES de este (porque aún no se crea el nuevo)
            $previousDisbursements = LoanDisbursement::where('loan_id', $loan->id)->count();

            // 🔥 Definir concepto
            $concept = 'loan_disbursement';

            if ($hasIncrement && $previousDisbursements >= 1) {
                $concept = 'loan_increment';
            }
            /* ============================
               MOVIMIENTO DE CAJA (SALIDA)
            ============================ */
            CashMovement::create([
                'cash_box_id'     => $cashBox->id,
                'branch_id'       => $branchId,
                'type'            => 'out',
                'concept'         => $concept,
                'amount'          => $disbursement->amount,
                'reference_table' => 'loan_disbursements',
                'notes'       => $data['notes'] ?? null,
                'reference_id'    => $disbursement->id,
                'user_id'     => Auth::id()
            ]);

            /* ============================
               ESTADO DEL PRÉSTAMO
            ============================ */
            if ($alreadyDisbursed + $newAmount >= $loanAmount) {

                // recalcular saldo desde cronograma
                $remaining = LoanSchedule::where('loan_id', $loan->id)
                    ->sum(DB::raw('payment - paid_amount'));

                if ($remaining < 0) {
                    $remaining = 0;
                }

                $loan->status = 'disbursed';
                $loan->disbursement_date = $data['disbursement_date'];

                // ⭐ saldo inicial correcto
                $loan->current_balance = $remaining;

                $loan->save();
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Desembolso registrado correctamente.',
                'data'    => $disbursement,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en desembolso: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el desembolso.',
            ], 500);
        }
    }

    /* =========================================================
     | CÓDIGO DE DESEMBOLSO
     ========================================================= */
    private function generateDisbursementCode()
    {
        $last = LoanDisbursement::orderBy('id', 'DESC')->first();

        if (! $last || ! $last->disbursement_code) {
            return 'DB-0001';
        }

        $num = intval(substr($last->disbursement_code, 3)) + 1;

        return 'DB-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
