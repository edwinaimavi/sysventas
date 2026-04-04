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

        .header {
            width: 100%;
            border-bottom: 2px solid #28a745;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header table {
            width: 100%;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }

        .subtitle {
            font-size: 11px;
            color: #777;
        }

        .row {
            width: 100%;
            display: flex;
            margin-bottom: 10px;
        }

        .col {
            width: 50%;
            padding: 5px;
        }

        .card {
            border-radius: 8px;
            border: 1px solid #ddd;
            overflow: hidden;
        }

        .card-header {
            padding: 8px;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }

        .success {
            background: #28a745;
        }

        .danger {
            background: #dc3545;
        }

        .info {
            background: #17a2b8;
        }

        .warning {
            background: #f4b400;
        }

        .card-body {
            padding: 10px;
        }

        .line {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #ddd;
            padding: 4px 0;
        }

        .line:last-child {
            border-bottom: none;
        }

        .totals {
            margin-top: 15px;
        }

        .box {
            width: 32%;
            display: inline-block;
            padding: 12px;
            color: white;
            border-radius: 8px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        table {
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }
    </style>

</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td width="20%">
                    <img src="{{ public_path('vendor/adminlte/dist/img/logo2.png') }}" height="50">
                </td>
                <td width="80%" style="text-align:right;">
                    <div class="title">REPORTE DE OPERACIONES</div>
                    <div class="subtitle">
                        Fecha: {{ date('d/m/Y H:i') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- INGRESOS / SALIDAS -->
    <table width="100%" cellspacing="10">
        <tr>
            <!-- INGRESOS -->
            <td width="50%" valign="top">
                <div class="card">
                    <div class="card-header success">INGRESOS</div>
                    <div class="card-body">

                        <div class="line">
                            <span>Monto cobrado</span>
                            <strong>S/ {{ number_format($montoCobrado, 2) }}</strong>
                        </div>

                        <div class="line">
                            <span>Capital recuperado</span>
                            <strong>S/ {{ number_format($capitalRecuperado, 2) }}</strong>
                        </div>

                        <div class="line">
                            <span>Intereses</span>
                            <strong>S/ {{ number_format($interesesCobrados, 2) }}</strong>
                        </div>

                        <div class="line">
                            <span>Otros ingresos</span>
                            <strong>S/ {{ number_format($gastosAdicionales, 2) }}</strong>
                        </div>

                    </div>
                </div>
            </td>

            <!-- SALIDAS -->
            <td width="50%" valign="top">
                <div class="card">
                    <div class="card-header danger">SALIDAS</div>
                    <div class="card-body">

                        <div class="line">
                            <span>Capital revolvente</span>
                            <strong>S/ {{ number_format($capitalRevolvente, 2) }}</strong>
                        </div>

                        <div class="line">
                            <span>Capital en cuotas</span>
                            <strong>S/ {{ number_format($capitalCuotas, 2) }}</strong>
                        </div>

                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TOTALES -->
    <table width="100%" cellspacing="10" style="margin-top:15px;">
        <tr>

            <td width="33%">
                <div class="box info">
                    Total Ingresos<br>
                    S/ {{ number_format($totalIngresos, 2) }}
                </div>
            </td>

            <td width="33%">
                <div class="box warning">
                    Total Salidas<br>
                    S/ {{ number_format($totalSalidas, 2) }}
                </div>
            </td>

            <td width="33%">
                <div class="box success">
                    Saldo en Caja<br>
                    S/ {{ number_format($saldoCaja, 2) }}
                </div>
            </td>

        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Sistema de Gestión Financiera · CICO Ingenieros
    </div>

</body>

</html>
