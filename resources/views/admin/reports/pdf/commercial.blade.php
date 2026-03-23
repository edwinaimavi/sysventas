<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 11px;
            color: #333;
        }

        h1 {
            font-size: 16px;
            margin: 0;
            color: #1f4fd8;
        }

        .subtitle {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
        }

        .header {
            border-bottom: 2px solid #1f4fd8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .summary-box {
            border: 1px solid #dcdcdc;
            padding: 8px;
            width: 32%;
            vertical-align: top;
        }

        .summary-title {
            font-weight: bold;
            color: #1f4fd8;
            margin-bottom: 6px;
        }

        .summary-value {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #1f4fd8;
            color: #fff;
            font-size: 10px;
            padding: 6px;
            border: 1px solid #1f4fd8;
        }

        td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 10px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 14px;
            font-size: 9px;
            text-align: right;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte Comercial</h1>
    <div class="subtitle">
        Capital en circulación<br>
        Rango: {{ $filters['date_from'] ?? '—' }} a {{ $filters['date_to'] ?? '—' }}
    </div>
</div>

<table class="summary-table">
    <tr>
        <td class="summary-box">
            <div class="summary-title">Capital Revolvente</div>
            <div class="summary-value">
                Capital: <strong>S/ {{ number_format($revCapital, 2) }}</strong>
            </div>
            <div class="summary-value">
                Interés: <strong>S/ {{ number_format($revInterest, 2) }}</strong>
            </div>
        </td>

        <td class="summary-box">
            <div class="summary-title">Capital en Cuotas</div>
            <div class="summary-value">
                Capital: <strong>S/ {{ number_format($cuoCapital, 2) }}</strong>
            </div>
            <div class="summary-value">
                Interés: <strong>S/ {{ number_format($cuoInterest, 2) }}</strong>
            </div>
        </td>

        <td class="summary-box">
            <div class="summary-title">Capital Vencido</div>
            <div class="summary-value">
                Capital: <strong>S/ {{ number_format($vencidoCapital, 2) }}</strong>
            </div>
            <div class="summary-value">
                Interés: <strong>S/ {{ number_format($vencidoInterest, 2) }}</strong>
            </div>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Código</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Total a Pagar</th>
            <th>Cuotas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $i => $loan)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $loan->loan_code }}</td>
            <td>{{ $loan->client->full_name ?? '—' }}</td>
            <td class="right">S/ {{ number_format($loan->amount, 2) }}</td>
            <td class="right">S/ {{ number_format($loan->total_payable, 2) }}</td>
            <td class="center">{{ $loan->term_months }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
