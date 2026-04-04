<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 10px;
            color: #2c3e50;
        }

        /* ===== HEADER ===== */
        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .header td {
            border: none;
        }

        .logo {
            width: 120px;
        }

        .title {
            text-align: right;
        }

        .title h2 {
            margin: 0;
            font-size: 16px;
        }

        .subtitle {
            font-size: 10px;
            color: #666;
        }

        /* ===== BOX ===== */
        .box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 10px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #34495e;
            color: white;
            font-size: 10px;
            padding: 6px;
        }

        td {
            border: 1px solid #ddd;
            padding: 5px;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        /* ===== TOTAL ===== */
        .total-row {
            background: #ecf0f1;
            font-weight: bold;
        }

        .saldo-final {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <!-- ================= HEADER ================= -->
    <table class="header">
        <tr>
            <td>
                <img src="{{ public_path('vendor/adminlte/dist/img/logo2.png') }}" class="logo">
            </td>
            <td class="title">
                <h2>LIBRO DE CAJA</h2>
                <div class="subtitle">
                    Desde: {{ $filters['date_from'] ?? '—' }} <br>
                    Hasta: {{ $filters['date_to'] ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- ================= TABLA ================= -->
    <div class="box">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Cliente</th>
                    <th>Préstamo</th>
                    <th class="right">Ingreso</th>
                    <th class="right">Salida</th>
                    <th class="right">Saldo</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $totalIngresos = 0;
                    $totalSalidas = 0;
                @endphp

                @foreach ($data as $row)
                    @php
                        $ingreso = $row['ingreso'];
                        $salida = $row['salida'];

                        $totalIngresos += $ingreso;
                        $totalSalidas += $salida;
                    @endphp

                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['fecha'])->format('d/m/Y H:i') }}</td>
                        <td>{{ $row['concepto'] }}</td>
                        <td>{{ $row['cliente'] }}</td>
                        <td>{{ $row['prestamo'] }}</td>

                        <td class="right">
                            {{ $ingreso > 0 ? 'S/ ' . number_format($ingreso, 2) : '-' }}
                        </td>

                        <td class="right">
                            {{ $salida > 0 ? 'S/ ' . number_format($salida, 2) : '-' }}
                        </td>

                        <td class="right">
                            S/ {{ number_format($row['saldo'], 2) }}
                        </td>
                    </tr>
                @endforeach

                <!-- ===== TOTALES ===== -->
                <tr class="total-row">
                    <td colspan="4" class="right">TOTALES</td>
                    <td class="right">S/ {{ number_format($totalIngresos, 2) }}</td>
                    <td class="right">S/ {{ number_format($totalSalidas, 2) }}</td>
                    <td></td>
                </tr>

            </tbody>
        </table>
    </div>

    <!-- ================= SALDO FINAL ================= -->
    <div class="saldo-final">
        SALDO FINAL: S/ {{ number_format($data->last()['saldo'] ?? 0, 2) }}
    </div>

</body>

</html>
