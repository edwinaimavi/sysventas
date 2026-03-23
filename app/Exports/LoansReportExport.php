<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanPayment;
use Maatwebsite\Excel\Concerns\FromArray;

class LoansReportExport implements FromArray
{
    protected Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        // ================================
        // PRÉSTAMOS + PAGADO EN RANGO
        // ================================
        $loans = Loan::with('client')
            ->when(
                $this->request->branch_id,
                fn($q) =>
                $q->where('branch_id', $this->request->branch_id)
            )
            ->when(
                $this->request->date_from,
                fn($q) =>
                $q->whereDate('created_at', '>=', $this->request->date_from)
            )
            ->when(
                $this->request->date_to,
                fn($q) =>
                $q->whereDate('created_at', '<=', $this->request->date_to)
            )
            ->addSelect([
                'paid_total' => LoanPayment::query()
                    ->selectRaw('COALESCE(SUM(amount),0)')
                    ->whereColumn('loan_id', 'loans.id')
                    ->when(
                        $this->request->date_from,
                        fn($qq) =>
                        $qq->whereDate('created_at', '>=', $this->request->date_from)
                    )
                    ->when(
                        $this->request->date_to,
                        fn($qq) =>
                        $qq->whereDate('created_at', '<=', $this->request->date_to)
                    )
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // ================================
        // KPIs (IGUAL QUE EL PDF)
        // ================================
        $totalPrestado   = (float) $loans->sum('amount');
        $totalAPagar     = (float) $loans->sum('total_payable');
        $totalRecuperado = (float) $loans->sum('paid_total');

        $gananciaEsperada = max(0, $totalAPagar - $totalPrestado);
        $pendienteTotal   = max(0, $totalAPagar - $totalRecuperado);

        $recoveryRate = $totalPrestado > 0
            ? round(($totalRecuperado / $totalPrestado) * 100, 2)
            : 0;

        // ================================
        // ARMAR EXCEL (RESUMEN + TABLA)
        // ================================
        $rows = [];

        // 🔹 RESUMEN
        $rows[] = ['REPORTE CONSOLIDADO DE PRÉSTAMOS'];
        $rows[] = ['Rango', ($this->request->date_from ?: '—') . ' a ' . ($this->request->date_to ?: '—')];
        $rows[] = ['Sucursal', $this->request->branch_id ?: 'Todas'];
        $rows[] = [''];
        $rows[] = ['Total prestado', 'S/ ' . number_format($totalPrestado, 2)];
        $rows[] = ['Total a pagar', 'S/ ' . number_format($totalAPagar, 2)];
        $rows[] = ['Total recuperado', 'S/ ' . number_format($totalRecuperado, 2)];
        $rows[] = ['Ganancia esperada (interés)', 'S/ ' . number_format($gananciaEsperada, 2)];
        $rows[] = ['Pendiente total', 'S/ ' . number_format($pendienteTotal, 2)];
        $rows[] = ['% Recuperación', number_format($recoveryRate, 2) . '%'];

        // espacio
        $rows[] = [''];
        $rows[] = ['DETALLE DE PRÉSTAMOS'];

        // 🔹 HEADERS TABLA
        $rows[] = [
            '#',
            'Código',
            'Cliente',
            'Fecha',
            'Monto',
            'Total a pagar',
            'Recuperado',
            'Pendiente',
            'Ganancia',
            'Estado',
        ];

        // 🔹 FILAS TABLA
        foreach ($loans as $i => $loan) {
            $paid = (float) $loan->paid_total;
            $pending = max(0, ((float)$loan->total_payable - $paid));
            $profit = max(0, ((float)$loan->total_payable - (float)$loan->amount));

            $rows[] = [
                $i + 1,
                $loan->loan_code,
                $loan->client->full_name ?? '—',
                optional($loan->created_at)->format('Y-m-d'),
                number_format((float)$loan->amount, 2),
                number_format((float)$loan->total_payable, 2),
                number_format($paid, 2),
                number_format($pending, 2),
                number_format($profit, 2),
                $loan->status,
            ];
        }

        return $rows;
    }
}
