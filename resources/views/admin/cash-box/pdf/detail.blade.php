<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detalle de Caja</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            border-bottom: 2px solid #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .subtitle {
            font-size: 11px;
            color: #777;
        }

        .info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            padding: 3px 0;
        }

        .summary {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .summary-box {
            width: 24%;
            display: inline-block;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px;
            margin-right: 1%;
        }

        .summary-title {
            font-size: 10px;
            color: #777;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
        }

        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #007bff; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: #2c3e50;
            color: #fff;
        }

        th {
            padding: 6px;
            font-size: 10px;
            text-align: center;
        }

        td {
            padding: 5px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            color: #fff;
        }

        .badge-in {
            background-color: #28a745;
        }

        .badge-out {
            background-color: #dc3545;
        }

        .footer {
            margin-top: 15px;
            font-size: 10px;
            text-align: right;
            color: #888;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div class="title">📊 Detalle de Caja</div>
        <div class="subtitle">Reporte generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- INFO -->
    <div class="info">
        <table class="info-table">
            <tr>
                <td><strong>Sucursal:</strong> {{ $cash->branch->name }}</td>
                <td><strong>Fecha apertura:</strong> {{ $cash->opened_at }}</td>
            </tr>
        </table>
    </div>

    <!-- RESUMEN -->
    <div class="summary">

        <div class="summary-box">
            <div class="summary-title">Saldo Inicial</div>
            <div class="summary-value">
                S/ {{ number_format($cash->opening_amount,2) }}
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Ingresos</div>
            <div class="summary-value text-success">
                S/ {{ number_format($income,2) }}
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Egresos</div>
            <div class="summary-value text-danger">
                S/ {{ number_format($expense,2) }}
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Saldo Final</div>
            <div class="summary-value text-primary">
                S/ {{ number_format($balance,2) }}
            </div>
        </div>

    </div>

    <!-- TABLA -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Usuario</th>
            </tr>
        </thead>

        <tbody>
            @foreach($movements as $i => $m)
                <tr>
                    <td>{{ $i+1 }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}
                    </td>

                    <td>
                        @if($m->type == 'in')
                            <span class="badge badge-in">INGRESO</span>
                        @else
                            <span class="badge badge-out">EGRESO</span>
                        @endif
                    </td>

                    <td>{{ ucfirst(str_replace('_',' ', $m->concept)) }}</td>

                    <td class="text-right">
                        S/ {{ number_format($m->amount,2) }}
                    </td>

                    <td>{{ $m->user->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Sistema de Caja • Reporte automático
    </div>

</body>
</html>