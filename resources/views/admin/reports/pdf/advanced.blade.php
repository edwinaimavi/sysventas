<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Avanzado</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #e9ecef;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        th {
            font-size: 10px;
        }

        td {
            font-size: 9px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f1f1f1;
        }

        .badge-success {
            background: #28a745;
            color: #fff;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .badge-warning {
            background: #ffc107;
            color: #000;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>📊 REPORTE AVANZADO</h2>
    <p>{{ date('d/m/Y H:i') }}</p>
</div>

@php
    $totalAmount = 0;
    $totalPaid = 0;
    $totalCapital = 0;
    $totalInterest = 0;
    $totalExpenses = 0;
    $totalBalance = 0;
@endphp

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Fecha V.</th>
            <th>Cliente</th>
            <th>Préstamo</th>
            <th>Monto</th>
            <th>Pagado</th>
            <th>Capital</th>
            <th>Interés</th>
            <th>Gastos</th>
            <th>Saldo</th>
            <th>Estado</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $index => $row)

        @php
            $totalAmount += $row->amount;
            $totalPaid += $row->total_paid;
            $totalCapital += $row->total_capital;
            $totalInterest += $row->total_interest;
            $totalExpenses += $row->total_expenses;
            $totalBalance += $row->balance;
        @endphp

        <tr>
            <td class="text-center">{{ $index + 1 }}</td>

            <td class="text-center">
                {{ date('Y-m-d', strtotime($row->created_at)) }}
            </td>

            <td class="text-center">
                {{ date('Y-m-d', strtotime($row->due_date)) }}
            </td>

            <td>{{ $row->full_name }}</td>

            <td class="text-center">{{ $row->loan_code }}</td>

            <td class="text-right">S/ {{ number_format($row->amount, 2) }}</td>
            <td class="text-right">S/ {{ number_format($row->total_paid, 2) }}</td>
            <td class="text-right">S/ {{ number_format($row->total_capital, 2) }}</td>
            <td class="text-right">S/ {{ number_format($row->total_interest, 2) }}</td>
            <td class="text-right">S/ {{ number_format($row->total_expenses, 2) }}</td>
            <td class="text-right">S/ {{ number_format($row->balance, 2) }}</td>

            <td class="text-center">
                @if($row->balance <= 0)
                    <span class="badge-success">Finalizado</span>
                @else
                    <span class="badge-warning">Pendiente</span>
                @endif
            </td>
        </tr>

        @endforeach

        <!-- TOTAL -->
        <tr class="total-row">
            <td colspan="5" class="text-center">TOTAL</td>

            <td class="text-right">S/ {{ number_format($totalAmount, 2) }}</td>
            <td class="text-right">S/ {{ number_format($totalPaid, 2) }}</td>
            <td class="text-right">S/ {{ number_format($totalCapital, 2) }}</td>
            <td class="text-right">S/ {{ number_format($totalInterest, 2) }}</td>
            <td class="text-right">S/ {{ number_format($totalExpenses, 2) }}</td>
            <td class="text-right">S/ {{ number_format($totalBalance, 2) }}</td>
            <td></td>
        </tr>

    </tbody>
</table>

</body>
</html>