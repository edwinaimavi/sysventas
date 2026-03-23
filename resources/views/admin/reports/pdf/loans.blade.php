<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte Consolidado de Préstamos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .muted {
            color: #666;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .kpi td {
            border: none;
            padding: 4px 6px;
        }

        .kpi .label {
            color: #555;
        }

        .kpi .val {
            font-weight: 700;
        }
    </style>
</head>

<body>




    <div class="title">Reporte Consolidado de Préstamos</div>
    <div class="muted">
        Rango: <b>{{ $filters['date_from'] ?: '—' }}</b> a <b>{{ $filters['date_to'] ?: '—' }}</b>
        &nbsp; | &nbsp; Sucursal: <b>{{ $filters['branch_name'] }}</b>
        &nbsp; | &nbsp; Cliente: <b>{{ $filters['client_name'] }}</b>
    </div>

    <div class="box">
        <table class="kpi">
            <tr>
                <td class="label">Total prestado</td>
                <td class="val right">S/ {{ number_format($summary['totalPrestado'], 2) }}</td>

                <td class="label">Total a pagar</td>
                <td class="val right">S/ {{ number_format($summary['totalAPagar'], 2) }}</td>

                <td class="label">% Recuperación</td>
                <td class="val right">{{ number_format($summary['recoveryRate'], 2) }}%</td>
            </tr>
            <tr>
                <td class="label">Total recuperado</td>
                <td class="val right">S/ {{ number_format($summary['totalRecuperado'], 2) }}</td>

                <td class="label">Ganancia esperada (interés)</td>
                <td class="val right">S/ {{ number_format($summary['gananciaEsperada'], 2) }}</td>

                <td class="label">Pendiente total</td>
                <td class="val right">S/ {{ number_format($summary['pendienteTotal'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <b>Detalle por préstamo</b>
        <div class="muted" style="margin-bottom:6px;">
            * “Pagado” corresponde a pagos realizados dentro del rango seleccionado.
        </div>

        <table>
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th class="center">Fecha</th>
                    <th class="right">Monto</th>
                    <th class="right">Total a pagar</th>
                    <th class="right">Pagado</th>
                    <th class="right">Pendiente</th>
                    <th class="right">Ganancia</th>
                    <th class="center">Estado</th>
                    <th class="right">Capital</th>
                    <th class="right">Interés</th>
                    <th class="center"># Cuotas</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($loans as $i => $loan)
                    @php
                        $paid = (float) ($loan->paid_total ?? 0);
                        $cap = (float) ($loan->capital_total ?? 0);
                        $int = (float) ($loan->interest_total ?? 0);
                        $inst = (int) ($loan->installments_paid ?? 0);

                        $remaining = max(0, (float) $loan->total_payable - $paid);
                        $profit = max(0, (float) $loan->total_payable - (float) $loan->amount);
                    @endphp

                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $loan->loan_code }}</td>
                        <td>{{ $loan->client->full_name ?? '—' }}</td>
                        <td class="center">{{ optional($loan->created_at)->format('Y-m-d') }}</td>
                        <td class="right">S/ {{ number_format((float) $loan->amount, 2) }}</td>
                        <td class="right">S/ {{ number_format((float) $loan->total_payable, 2) }}</td>
                        <td class="right">S/ {{ number_format($paid, 2) }}</td>
                        <td class="right">S/ {{ number_format($remaining, 2) }}</td>
                        <td class="right">S/ {{ number_format($profit, 2) }}</td>
                        <td class="center">{{ $loan->status }}</td>

                        {{-- ✅ NUEVO --}}
                        <td class="right">S/ {{ number_format($cap, 2) }}</td>
                        <td class="right">S/ {{ number_format($int, 2) }}</td>
                        <td class="center">{{ $inst }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</body>

</html>
