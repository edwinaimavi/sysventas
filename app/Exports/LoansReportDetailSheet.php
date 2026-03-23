<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class LoansReportDetailSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(private Request $request) {}

    public function title(): string
    {
        return 'Detalle';
    }

    public function headings(): array
    {
        return [
            '#',
            'Código',
            'Cliente',
            'Fecha',
            'Monto',
            'Total a pagar',
            'Pagado (rango)',
            'Pendiente',
            'Ganancia esperada',
            'Estado',
        ];
    }

    public function collection(): Collection
    {
        $loans = Loan::with('client')
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
            ->orderBy('created_at', 'desc')
            ->get();

        return $loans->values()->map(function ($loan, $i) {
            $paid = (float) ($loan->paid_total ?? 0);
            $remaining = max(0, ((float)$loan->total_payable - $paid));
            $profit = max(0, ((float)$loan->total_payable - (float)$loan->amount));

            return [
                $i + 1,
                $loan->loan_code,
                $loan->client->full_name ?? '—',
                optional($loan->created_at)->format('Y-m-d'),
                number_format((float)$loan->amount, 2),
                number_format((float)$loan->total_payable, 2),
                number_format($paid, 2),
                number_format($remaining, 2),
                number_format($profit, 2),
                $loan->status,
            ];
        });
    }
}
