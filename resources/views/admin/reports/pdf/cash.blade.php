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

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 15px;
            color: #666;
        }

        .box {
            border: 1px solid #bbb;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #f9f9f9;
        }

        .saldo {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>

<body>

<h2>CUADRE DE CAJA</h2>
<div class="subtitle">
    Desde {{ $filters['date_from'] ?? '—' }} |
    Hasta {{ $filters['date_to'] ?? '—' }}
</div>

<div class="box">
    <strong>INGRESOS</strong>
    <table>
        <tr>
            <td>Monto de apertura</td>
            <td class="right">S/ {{ number_format($montoApertura, 2) }}</td>
        </tr>
        <tr>
            <td>Monto cobrado (pagos)</td>
            <td class="right">S/ {{ number_format($montoCobrado, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total ingresos</td>
            <td class="right">S/ {{ number_format($totalIngresos, 2) }}</td>
        </tr>
    </table>
</div>

<div class="box">
    <strong>SALIDAS</strong>
    <table>
        <tr>
            <td>Vuelto a cliente</td>
            <td class="right">S/ {{ number_format($vueltoCliente, 2) }}</td>
        </tr>
        <tr>
            <td>Capital revolvente prestado</td>
            <td class="right">S/ {{ number_format($capitalRevolvente, 2) }}</td>
        </tr>
        <tr>
            <td>Capital en cuotas prestado</td>
            <td class="right">S/ {{ number_format($capitalCuotas, 2) }}</td>
        </tr>
        <tr>
            <td>Otras salidas</td>
            <td class="right">S/ {{ number_format($otrasSalidas, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total salidas</td>
            <td class="right">S/ {{ number_format($totalSalidas, 2) }}</td>
        </tr>
    </table>
</div>

<div class="box">
    <strong>SALDO FINAL EN CAJA</strong>
    <div class="saldo">
        S/ {{ number_format($saldoCaja, 2) }}
    </div>
</div>

</body>
</html>
