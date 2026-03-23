<?php

// app/Http/Controllers/Admin/LoanScheduleController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanScheduleController extends Controller
{
    public function byLoan(Loan $loan, Request $request)
    {
        // seguridad simple por sucursal (si usas branch_id en sesión)
        $branchId = session('branch_id');
        if ($branchId && (int)$loan->branch_id !== (int)$branchId) {
            return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        $rows = $loan->schedules()
            ->orderBy('installment_no')
            ->get([
                'installment_no',
                'due_date',
                'opening_balance',
                'interest',
                'amortization',
                'payment',
                'closing_balance',
            ]);

        // si quieres la fila MES 0 (saldo inicial) como en el excel del cliente
        $month0 = null;
        if ($rows->count() > 0) {
            $first = $rows->first();
            $month0 = [
                'installment_no'  => 0,
                'due_date'        => null,
                'opening_balance' => (float)$first->opening_balance,
                'interest'        => 0,
                'amortization'    => 0,
                'payment'         => 0,
                'closing_balance' => (float)$first->opening_balance,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'month0' => $month0,
                'rows' => $rows,
            ]
        ]);
    }
}
