<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 11px;
            color: #222;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-top: -60px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            background: #f3f4f6;
            padding: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #111827;
            color: white;
            padding: 8px;
            font-size: 10px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            color: white;
            font-size: 10px;
        }

        .success {
            background: #16a34a;
        }

        .warning {
            background: #d97706;
        }

        .primary {
            background: #2563eb;
        }

        .danger {
            background: #dc2626;
        }

        .info-table td {
            border: none;
            padding: 4px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">

        <img src="{{ public_path('vendor/adminlte/dist/img/logo2.png') }}" class="logo">

        <div class="title">
            REPORTE DE PRÉSTAMO
        </div>

        <div class="subtitle">
            Sistema financiero
        </div>

    </div>

    <!-- CLIENTE -->
    <div class="card">

        <div class="section-title">
            INFORMACIÓN DEL CLIENTE
        </div>

        <table class="info-table">

            <tr>
                <td width="25%">
                    <strong>Cliente:</strong>
                </td>

                <td>
                    {{ $loan->client->full_name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Garante:</strong>
                </td>

                <td>
                    {{ $loan->guarantor->full_name ?? 'Sin garante' }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Sucursal:</strong>
                </td>

                <td>
                    {{ $loan->branch->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Registrado por:</strong>
                </td>

                <td>
                    {{ $loan->user->name ?? '-' }}
                </td>
            </tr>

        </table>

    </div>

    <!-- PRESTAMO -->
    <div class="card">

        <div class="section-title">
            INFORMACIÓN DEL PRÉSTAMO
        </div>

        <table class="info-table">

            <tr>

                <td width="25%">
                    <strong>Código:</strong>
                </td>

                <td>
                    {{ $loan->loan_code }}
                </td>

                <td width="25%">
                    <strong>Estado:</strong>
                </td>

                <td>

                    <span class="badge success">
                        {{ strtoupper($loan->status) }}
                    </span>

                </td>

            </tr>

            <tr>

                <td>
                    <strong>Monto:</strong>
                </td>

                <td>
                    S/ {{ number_format($loan->amount, 2) }}
                </td>

                <td>
                    <strong>Interés:</strong>
                </td>

                <td>
                    {{ $loan->interest_rate }} %
                </td>

            </tr>

            <tr>

                <td>
                    <strong>Cuota mensual:</strong>
                </td>

                <td>
                    S/ {{ number_format($loan->monthly_payment, 2) }}
                </td>

                <td>
                    <strong>Total pagar:</strong>
                </td>

                <td>
                    S/ {{ number_format($loan->total_payable, 2) }}
                </td>

            </tr>

            <tr>

                <td>
                    <strong>Fecha desembolso:</strong>
                </td>

                <td>
                    {{ $loan->disbursement_date }}
                </td>

                <td>
                    <strong>Fecha vencimiento:</strong>
                </td>

                <td>
                    {{ $loan->due_date }}
                </td>

            </tr>

        </table>

    </div>

    <!-- CRONOGRAMA -->
    <div class="card">

        <div class="section-title">
            CRONOGRAMA DE PAGOS
        </div>

        <table>

            <thead>

                <tr>

                    <th>#</th>
                    <th>Vencimiento</th>
                    <th>Capital</th>
                    <th>Interés</th>
                    <th>Amortización</th>
                    <th>Cuota</th>

                </tr>

            </thead>

            <tbody>

                @foreach ($loan->schedules as $item)
                    <tr>

                        <td>
                            {{ $item->installment }}
                        </td>

                        <td>
                            {{ $item->due_date }}
                        </td>

                        <td class="text-right">
                            S/ {{ number_format($item->capital_amount, 2) }}
                        </td>

                        <td class="text-right">
                            S/ {{ number_format($item->interest_amount, 2) }}
                        </td>

                        <td class="text-right">
                            S/ {{ number_format($item->amortization_amount, 2) }}
                        </td>

                        <td class="text-right">
                            S/ {{ number_format($item->installment_amount, 2) }}
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>

</body>

</html>
