<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanPayment;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class LoansReportSummarySheet implements FromArray, WithTitle
{
    public function __construct(private Request $request) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        // Misma lógica que PDF: préstamos con paid_total en el rango
        $loans = Loan::query()
            ->when($this->request->branch_id, fn($q) => $q->where('branch_id', $this->request->branch_id))
            ->when($this->request->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->request->date_from))
            ->when($this->request->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->request->date_to))
            ->addSelect([
                'paid_total' => LoanPayment::query()
                    ->selectRaw('COALESCE(SUM(amount),0)')
                    ->whereColumn('loan_id', 'loans.id')
                    ->when($this->request->date_from, fn($qq) => $qq->whereDate('created_at', '>=', $this->request->date_from))
                    ->when($this->request->date_to, fn($qq) => $qq->whereDate('created_at', '<=', $this->request->date_to))
            ])
            ->get(['id', 'amount', 'total_payable', 'paid_total']);

        $totalPrestado   = (float) $loans->sum('amount');
        $totalAPagar     = (float) $loans->sum('total_payable');
        $totalRecuperado = (float) $loans->sum('paid_total');

        $gananciaEsperada = max(0, $totalAPagar - $totalPrestado);
        $pendienteTotal   = max(0, $totalAPagar - $totalRecuperado);

        $recoveryRate = $totalPrestado > 0
            ? round(($totalRecuperado / $totalPrestado) * 100, 2)
            : 0;

        // ✅ Tabla tipo “PDF”
        return [
            ['Reporte Consolidado de Préstamos'],
            ['Rango', ($this->request->date_from ?: '—') . ' a ' . ($this->request->date_to ?: '—')],
            ['Sucursal', $this->request->branch_id ?: 'Todas'],
            [''],
            ['Total prestado', 'S/ ' . number_format($totalPrestado, 2)],
            ['Total a pagar', 'S/ ' . number_format($totalAPagar, 2)],
            ['Total recuperado', 'S/ ' . number_format($totalRecuperado, 2)],
            ['Ganancia esperada (interés)', 'S/ ' . number_format($gananciaEsperada, 2)],
            ['Pendiente total', 'S/ ' . number_format($pendienteTotal, 2)],
            ['% Recuperación', number_format($recoveryRate, 2) . '%'],
        ];
    }
}
