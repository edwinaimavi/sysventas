<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class AdvancedReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();

        $totalAmount = 0;
        $totalPaid = 0;
        $totalCapital = 0;
        $totalInterest = 0;
        $totalExpenses = 0;
        $totalBalance = 0;

        foreach ($this->data as $index => $row) {

            $totalAmount += $row->amount;
            $totalPaid += $row->total_paid;
            $totalCapital += $row->total_capital;
            $totalInterest += $row->total_interest;
            $totalExpenses += $row->total_expenses;
            $totalBalance += $row->balance;

            $rows->push([
                $index + 1,
                date('Y-m-d', strtotime($row->created_at)),
                date('Y-m-d', strtotime($row->due_date)),
                $row->full_name,
                $row->loan_code,
                $row->amount,
                $row->total_paid,
                $row->total_capital,
                $row->total_interest,
                $row->total_expenses,
                $row->balance,
                $row->balance <= 0 ? 'Finalizado' : 'Pendiente'
            ]);
        }

        // 🔥 FILA TOTAL
        $rows->push([
            '',
            '',
            '',
            '',
            'TOTAL',
            $totalAmount,
            $totalPaid,
            $totalCapital,
            $totalInterest,
            $totalExpenses,
            $totalBalance,
            ''
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            '#',
            'Fecha',
            'Fecha V.',
            'Cliente',
            'Préstamo',
            'Monto',
            'Pagado',
            'Capital',
            'Interés',
            'Gastos',
            'Saldo',
            'Estado'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"S/ "#,##0.00',
            'G' => '"S/ "#,##0.00',
            'H' => '"S/ "#,##0.00',
            'I' => '"S/ "#,##0.00',
            'J' => '"S/ "#,##0.00',
            'K' => '"S/ "#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
