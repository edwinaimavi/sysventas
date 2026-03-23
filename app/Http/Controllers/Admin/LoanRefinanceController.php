<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;

use App\Models\LoanRefinancing;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LoanSchedule;

class LoanRefinanceController extends Controller
{
    /**
     * Devuelve info para el modal:
     * - saldo pendiente actual
     * - si está vencido
     * - si tiene refinance activo
     */
    public function info(Loan $loan)
    {
        $branchId = session('branch_id');

        if (!$branchId || $loan->branch_id != $branchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Préstamo no válido para la sucursal seleccionada.',
            ], 422);
        }

        $totalPaid = LoanPayment::where('loan_id', $loan->id)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');

        $remaining = (float)$loan->total_payable - (float)$totalPaid;
        if ($remaining < 0) $remaining = 0;

        $today = Carbon::today();
        $due = $loan->due_date ? Carbon::parse($loan->due_date) : null;
        $isOverdue = $due ? $today->gt($due) : false;

        $hasActiveRefinance = $loan->activeRefinance()->exists();

        return response()->json([
            'status' => 'success',
            'data' => [
                'loan_id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'due_date' => $loan->due_date ? $loan->due_date->format('Y-m-d') : null,
                'is_overdue' => $isOverdue,
                'total_paid' => round((float)$totalPaid, 2),
                'remaining_balance' => round((float)$remaining, 2),
                'has_active_refinance' => $hasActiveRefinance,
            ]
        ]);
    }

    /**
     * Crea refinanciamiento e impacta el préstamo.
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

        // 1) Validación básica del request
        $data = $request->validate([
            'loan_id'         => 'required|exists:loans,id',
            'refinance_date'  => 'required|date',
            'new_due_date'    => 'required|date|after_or_equal:refinance_date',
            'new_term_months' => 'required|integer|min:1|max:360',
            'interest_rate'   => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:500',
        ], [
            'loan_id.required' => 'Debes seleccionar un préstamo.',
            'loan_id.exists'   => 'El préstamo no existe.',
        ]);

        // 2) Cargar préstamo y validar que sea de la sucursal
        $loan = Loan::where('id', $data['loan_id'])
            ->where('branch_id', $branchId)
            ->first();

        if (!$loan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
            ], 422);
        }

        // 3) Reglas fuertes (tus condiciones exactas)
        // 3.1: Solo refinanciar si está desembolsado
        if ($loan->status !== 'disbursed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Solo se puede refinanciar préstamos en estado "Desembolsado".',
            ], 409);
        }

        // 3.2: Debe estar vencido según tu regla (due_date < hoy + saldo>0 + disbursed)
        if (!$loan->isExpired()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este préstamo no está vencido (o no tiene saldo pendiente).',
            ], 409);
        }

        // 3.3: No debe existir refinanciamiento activo
        if ($loan->hasActiveRefinance()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este préstamo ya tiene un refinanciamiento activo.',
            ], 409);
        }

        // 3.4: Saldo pendiente real (por seguridad)
        $remaining = (float) $loan->remainingBalance();
        if ($remaining <= 0.009) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo no tiene saldo pendiente para refinanciar.',
            ], 409);
        }

        // 4) Cálculo del nuevo plan (igual que tu fórmula actual)
        $newTermMonths   = (int) $data['new_term_months'];
        $interestRate    = (float) $data['interest_rate'];

        $baseBalance     = $remaining;
        $interestAmount  = $baseBalance * ($interestRate / 100);
        $newTotalPayable = $baseBalance + $interestAmount;

        // monthly_payment si quieres:
        $monthlyPayment  = $newTermMonths > 0 ? ($newTotalPayable / $newTermMonths) : $newTotalPayable;

        try {
            DB::beginTransaction();

            // 5) Crear registro histórico de refinanciamiento
            $ref = LoanRefinancing::create([
                'loan_id'               => $loan->id,
                'branch_id'             => $branchId,
                'user_id'               => Auth::id(),
                'refinance_date'        => $data['refinance_date'],
                'new_due_date'          => $data['new_due_date'],
                'new_term_months'       => $newTermMonths,
                'base_balance'          => round($remaining, 2),
                'interest_rate'         => round($interestRate, 2),
                'interest_amount'       => round($interestAmount, 2),
                'new_total_payable'     => round($newTotalPayable, 2),
                'prev_total_payable'    => round((float) $loan->total_payable, 2),
                'prev_remaining_balance' => round($remaining, 2),
                'status'                => 'active',
                'notes'                 => $data['notes'] ?? null,
            ]);

            // 6) Actualizar el préstamo (para que vuelva a habilitarse el pago)
            $loan->term_months = $newTermMonths;
            $loan->interest_rate = $interestRate;
            $loan->monthly_payment = round($monthlyPayment, 2);
            $loan->total_payable = round($newTotalPayable, 2);
            $loan->due_date = $data['new_due_date'];

            $loan->current_balance = round($newTotalPayable, 2);
            $loan->is_refinanced = 1;
            $loan->refinance_count = ($loan->refinance_count ?? 0) + 1;

            $loan->status = 'disbursed';

            $loan->save();

            /*
|--------------------------------------------------------------------------
| REGENERAR CRONOGRAMA DEL PRÉSTAMO
|--------------------------------------------------------------------------
*/

            LoanSchedule::where('loan_id', $loan->id)->delete();

            LoanSchedule::create([
                'loan_id' => $loan->id,
                'installment_no' => 1,
                'due_date' => $data['new_due_date'],
                'opening_balance' => $baseBalance,
                'interest' => $interestAmount,
                'amortization' => $baseBalance,
                'payment' => $newTotalPayable,
                'closing_balance' => 0,
                'status' => 'pending',
                'paid_amount' => 0
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Refinanciamiento registrado correctamente. El préstamo ya está habilitado para pagos.',
                'data'    => [
                    'refinance' => $ref,
                    'loan'      => $loan,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error refinanciando préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el refinanciamiento.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function history(Loan $loan)
    {
        $branchId = session('branch_id');

        if (!$branchId || $loan->branch_id != $branchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Préstamo no válido para la sucursal seleccionada.',
            ], 422);
        }

        $refinances = LoanRefinancing::where('loan_id', $loan->id)
            ->where('branch_id', $branchId)
            ->with('user:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'refinance_date' => optional($r->refinance_date)->format('Y-m-d') ?? '—',
                    'new_due_date' => optional($r->new_due_date)->format('Y-m-d') ?? '—',
                    'new_term_months' => (int) $r->new_term_months,
                    'base_balance' => (float) $r->base_balance,
                    'interest_rate' => (float) $r->interest_rate,
                    'interest_amount' => (float) $r->interest_amount,
                    'new_total_payable' => (float) $r->new_total_payable,
                    'prev_total_payable' => $r->prev_total_payable !== null ? (float) $r->prev_total_payable : null,
                    'prev_remaining_balance' => $r->prev_remaining_balance !== null ? (float) $r->prev_remaining_balance : null,
                    'status' => $r->status ?? '—',
                    'notes' => $r->notes ?? '—',
                    'user_name' => optional($r->user)->name ?? '—',
                    'created_at' => optional($r->created_at)->format('Y-m-d H:i') ?? '—',
                ];
            });

        $hasActive = LoanRefinancing::where('loan_id', $loan->id)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->exists();

        $last = $refinances->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'refinances' => $refinances,
                'summary' => [
                    'has_active_refinance' => (bool) $hasActive,
                    'last_refinance_date'  => $last['refinance_date'] ?? null,
                    'last_new_due_date'    => $last['new_due_date'] ?? null,
                ],
            ]
        ]);
    }


    public function refinanceHistory(Loan $loan)
    {
        $items = $loan->refinances()
            ->with('user') // por si usas user_name
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($r) {

                $refDate = $r->refinance_date
                    ? Carbon::parse($r->refinance_date)->format('Y-m-d')
                    : null;

                return [
                    'refinance_date'  => $refDate ?? '—',
                    'base_balance'    => (float) $r->base_balance,
                    'interest_rate'   => (float) $r->interest_rate,
                    'new_term_months' => (int) $r->new_term_months,
                    'new_due_date'    => $r->new_due_date ? Carbon::parse($r->new_due_date)->format('Y-m-d') : '—',
                    'status'          => $r->status ?? '—',
                    'notes'           => $r->notes ?? '—',
                    'created_at'      => $r->created_at ? $r->created_at->format('Y-m-d H:i') : '—',
                    'user_name'       => optional($r->user)->name ?? '—',
                ];
            });

        $hasActive = $loan->hasActiveRefinance();
        $last = $items->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'refinances' => $items,
                'summary' => [
                    'has_active_refinance' => (bool) $hasActive,
                    'last_refinance_date'  => $last['refinance_date'] ?? null,
                    'last_new_due_date'    => $last['new_due_date'] ?? null,
                ],
            ],
        ]);
    }
}
