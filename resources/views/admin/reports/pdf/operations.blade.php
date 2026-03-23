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

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            border-bottom: 2px solid #444;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .header small {
            color: #666;
        }

        /* ===== INFO ===== */
        .info {
            margin-bottom: 12px;
        }

        .info span {
            display: inline-block;
            margin-right: 15px;
        }

        /* ===== BOXES ===== */
        .box {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .box-title {
            background: #f5f5f5;
            padding: 6px 8px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }

        .box-body {
            padding: 8px;
        }

        .row {
            margin-bottom: 4px;
        }

        .label {
            display: inline-block;
            width: 65%;
        }

        .value {
            display: inline-block;
            width: 34%;
            text-align: right;
            font-weight: bold;
        }

        /* ===== COLORS ===== */
        .green { color: #1e7e34; }
        .red { color: #c82333; }
        .blue { color: #0056b3; }

        /* ===== RESUMEN ===== */
        .summary {
            border: 2px solid #444;
            margin-top: 15px;
        }

        .summary .box-title {
            background: #444;
            color: #fff;
            text-align: center;
            letter-spacing: 1px;
        }

        .saldo {
            font-size: 14px;
            margin-top: 6px;
            text-align: center;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>REPORTE DE OPERACIONES DE CAJA</h2>
        <small>Sistema de Créditos</small>
    </div>

    {{-- FILTROS --}}
    <div class="info">
        <span><strong>Desde:</strong> {{ $filters['date_from'] ?? '—' }}</span>
        <span><strong>Hasta:</strong> {{ $filters['date_to'] ?? '—' }}</span>
    </div>

    {{-- INGRESOS --}}
    <div class="box">
        <div class="box-title green">INGRESOS</div>
        <div class="box-body">
            <div class="row">
                <span class="label">Monto cobrado</span>
                <span class="value">S/ {{ number_format($montoCobrado, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Capital recuperado</span>
                <span class="value">S/ {{ number_format($capitalRecuperado, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Intereses cobrados</span>
                <span class="value">S/ {{ number_format($interesesCobrados, 2) }}</span>
            </div>
            {{-- <div class="row">
                <span class="label">Vuelto al cliente</span>
                <span class="value red">S/ {{ number_format($vueltoCliente, 2) }}</span>
            </div> --}}
        </div>
    </div>

    {{-- SALIDAS --}}
    <div class="box">
        <div class="box-title red">SALIDAS</div>
        <div class="box-body">
            <div class="row">
                <span class="label">Capital revolvente prestado</span>
                <span class="value">S/ {{ number_format($capitalRevolvente, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Capital en cuotas prestado</span>
                <span class="value">S/ {{ number_format($capitalCuotas, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- RESUMEN --}}
    <div class="box summary">
        <div class="box-title">RESUMEN DE CAJA</div>
        <div class="box-body">
            <div class="row">
                <span class="label">Total ingresos</span>
                <span class="value green">S/ {{ number_format($totalIngresos, 2) }}</span>
            </div>
            <div class="row">
                <span class="label">Total salidas</span>
                <span class="value red">S/ {{ number_format($totalSalidas, 2) }}</span>
            </div>

            <div class="saldo {{ $saldoCaja < 0 ? 'red' : 'green' }}">
                SALDO EN CAJA: S/ {{ number_format($saldoCaja, 2) }}
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Reporte generado automáticamente · {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
