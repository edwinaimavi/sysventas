<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanDisbursement;
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
    public function byLoan($loanId)
    {
        $branchId = session('branch_id');

        // Si por alguna razón no hay sucursal en sesión
        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 403);
        }

        // Traer préstamo solo si pertenece a la sucursal actual
        $loan = Loan::where('id', $loanId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        // Traer desembolsos de ese préstamo
        $disbursements = LoanDisbursement::where('loan_id', $loan->id)
            ->orderBy('disbursement_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Mapear para enviar sólo lo necesario al front
        $data = $disbursements->map(function ($d) {
            $url = $d->receipt_file ? Storage::url($d->receipt_file) : null;
            $ext = $d->receipt_file ? strtolower(pathinfo($d->receipt_file, PATHINFO_EXTENSION)) : null;

            return [
                'id'                => $d->id,
                'disbursement_date' => optional($d->disbursement_date)->format('Y-m-d'),
                'amount'            => (float) $d->amount,
                'method'            => $d->method,
                'reference'         => $d->reference,
                'receipt_number'    => $d->receipt_number,
                'status'            => $d->status,
                'notes'             => $d->notes,
                'receipt_file_url'  => $url,
                'receipt_file_type' => $ext,
            ];
        });

        $total = $data->sum('amount');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'disbursements' => $data,
                'summary' => [
                    'count'        => $data->count(),
                    'total_amount' => $total,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // 🔹 Quitamos branch_id y user_id del validate (los definimos nosotros)
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

        // 🔹 Sobrescribimos branch_id y user_id
        $data['branch_id'] = $branchId;

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // Código de desembolso
        $data['disbursement_code'] = $this->generateDisbursementCode();

        DB::beginTransaction();

        try {
            // Traemos el préstamo solo de la sucursal actual
            $loan = Loan::withSum('disbursements as total_disbursed', 'amount')
                ->where('id', $data['loan_id'])
                ->where('branch_id', $branchId)
                ->firstOrFail();

            if (! in_array($loan->status, ['approved', 'disbursed'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Solo se pueden registrar desembolsos para préstamos en estado APROBADO o DESEMBOLSADO. ' .
                        'Estado actual: ' . strtoupper($loan->status),
                ], 422);
            }

            $alreadyDisbursed = (float) ($loan->total_disbursed ?? 0);
            $loanAmount       = (float) $loan->amount;
            $newAmount        = (float) $data['amount'];

            // 1) Si ya está totalmente desembolsado, NO permitir
            if ($alreadyDisbursed >= $loanAmount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El préstamo ya se encuentra totalmente desembolsado.',
                ], 422);
            }

            // 2) Evitar pasarse del monto
            if ($alreadyDisbursed + $newAmount > $loanAmount) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El monto a desembolsar excede el monto del préstamo.',
                ], 422);
            }

            // Subir archivo si existe
            if ($request->hasFile('receipt_file')) {
                $data['receipt_file'] = $request->file('receipt_file')
                    ->store('disbursements', 'public');
            }

            // Calcular saldo restante después de este desembolso
            $remaining = $loanAmount - ($alreadyDisbursed + $newAmount);
            $data['remaining_balance'] = round($remaining, 2);

            // Crear desembolso
            $disbursement = LoanDisbursement::create($data);

            // Si el desembolso está completado y el saldo queda en 0 => marcar préstamo como "disbursed"
            if ($data['status'] === 'completed' && $remaining <= 0) {
                $loan->status = 'disbursed';
                $loan->disbursement_date = $data['disbursement_date'];
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
            Log::error('Error registrando desembolso: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el desembolso.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

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
