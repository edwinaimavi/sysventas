<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Comprobante</title>

    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            margin: 0;
            font-family: "Courier New", monospace;
        }

        .ticket {
            width: 80mm;
            padding: 8px 10px;
            margin: 0 auto;
            box-sizing: border-box;
            font-size: 12px;
            line-height: 1.25;
            color: #111;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .big {
            font-size: 14px;
        }

        .muted {
            color: #444;
            font-size: 12px;
        }

        .mono {
            font-family: "Courier New", monospace;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        .mt6 {
            margin-top: 6px;
        }

        .mt10 {
            margin-top: 10px;
        }

        .highlight {
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        // ==========================
        // HELPERS UI (ESPAÑOL)
        // ==========================
        $methodRaw = strtolower(trim((string) ($payment->method ?? '')));
        $statusRaw = strtolower(trim((string) ($payment->status ?? '')));

        // Método en español
        $methodMap = [
            'cash' => 'Efectivo',
            'efectivo' => 'Efectivo',
            'bank_transfer' => 'Transferencia bancaria',
            'transferencia' => 'Transferencia bancaria',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'other' => 'Otro',
            'otro' => 'Otro',
        ];
        $methodLabel = $methodMap[$methodRaw] ?? ($payment->method ?? '—');

        // Estado en español
        $statusMap = [
            'completed' => 'Completado',
            'pending' => 'Pendiente',
            'reversed' => 'Revertido',
        ];
        $statusLabel = $statusMap[$statusRaw] ?? ucfirst(($payment->status ?? '—'));

        // Efectivo?
        $isCash = in_array($methodRaw, ['cash', 'efectivo'], true);

        // ==========================
        // DESGLOSE (CAPITAL / INTERÉS / MORA)
        // ==========================
        $capital = (float) ($payment->capital ?? 0);
        $interest = (float) ($payment->interest ?? 0);
        $lateFee = (float) ($payment->late_fee ?? 0);

        // Monto total de la cuota = capital + interés + mora
        $quotaTotal = $capital + $interest + $lateFee;

        // Fallback: si no estás guardando desglose, usa amount como cuota
        if ($quotaTotal <= 0.009) {
            $quotaTotal = (float) ($payment->amount ?? 0);
        }

        // ==========================
        // GASTO ADICIONAL
        // ==========================
        $expenseAmount = 0;
        $expenseTypeRaw = '';
        $expenseDesc = '';

        if ($payment->expense) {
            $expenseAmount = (float) ($payment->expense->expense_amount ?? 0);
            $expenseTypeRaw = strtolower(trim((string) ($payment->expense->expense_type ?? '')));
            $expenseDesc = (string) ($payment->expense->expense_description ?? '');
        }

        // Tipo de gasto en español
        $expenseTypeMap = [
            'transport' => 'Transporte',
            'transporte' => 'Transporte',
            'pasaje' => 'Pasaje',
            'movilidad' => 'Movilidad',
            'atm' => 'Retiro en cajero',
            'cajero' => 'Retiro en cajero',
            'comision' => 'Comisión',
            'commission' => 'Comisión',
            'otros' => 'Otros',
            'other' => 'Otros',
        ];
        $expenseTypeLabel = $expenseTypeRaw ? ($expenseTypeMap[$expenseTypeRaw] ?? ($payment->expense->expense_type ?? '')) : '';

        // ==========================
        // TOTALES (según audio del cliente)
        // ==========================
        $otherExpenses = ($expenseAmount > 0.009) ? $expenseAmount : 0;
        $totalToCollect = $quotaTotal + $otherExpenses; // TOTAL A COBRAR

        // Pagó con / vuelto (si efectivo)
        $cashReceived = (float) ($payment->cash_received ?? 0);
        $change = $isCash ? max(0, $cashReceived - $totalToCollect) : 0;

        // Referencia
        $reference = $payment->reference ?? '—';
        if (is_string($reference)) {
            $reference = trim($reference) !== '' ? $reference : '—';
        }
    @endphp

    <div class="ticket">

        <div class="center">
            <div class="bold big">{{ $payment->loan->branch->name ?? 'MI NEGOCIO' }}</div>
            <div class="muted">RUC: {{ $payment->loan->branch->ruc ?? '—' }}</div>
            <div class="muted">Dirección: {{ $payment->loan->branch->address ?? '—' }}</div>
            <div class="line"></div>
            <div class="bold">COMPROBANTE DE PAGO</div>
            <div class="muted mono">N° {{ $payment->payment_code ?? $payment->id }}</div>
        </div>

        <div class="mt6">
            <table>
                <tr>
                    <td class="muted">Fecha</td>
                    <td class="right">{{ optional($payment->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <td class="muted">Usuario</td>
                    <td class="right">{{ $payment->user->name ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="line"></div>

        <div class="mt6">
            <div class="bold">Cliente</div>
            <div>{{ $payment->loan->client->full_name ?? '—' }}</div>
            <div class="muted">Préstamo: {{ $payment->loan->loan_code ?? '—' }}</div>
        </div>

        <div class="line"></div>

        {{-- ==========================
             DESGLOSE + ORDEN (según audio)
             ========================== --}}
        <table>
            <tr>
                <td>Capital</td>
                <td class="right">S/ {{ number_format($capital, 2) }}</td>
            </tr>
            <tr>
                <td>Interés</td>
                <td class="right">S/ {{ number_format($interest, 2) }}</td>
            </tr>
            <tr>
                <td>Mora</td>
                <td class="right">S/ {{ number_format($lateFee, 2) }}</td>
            </tr>

            <tr>
                <td class="highlight">Monto total de la cuota</td>
                <td class="right bold">S/ {{ number_format($quotaTotal, 2) }}</td>
            </tr>

            @if ($otherExpenses > 0.009)
                <tr>
                    <td>Otros gastos</td>
                    <td class="right bold">S/ {{ number_format($otherExpenses, 2) }}</td>
                </tr>

                @if (!empty($expenseTypeLabel))
                    <tr>
                        <td class="muted">Tipo</td>
                        <td class="right muted">{{ $expenseTypeLabel }}</td>
                    </tr>
                @endif

                @if (!empty($expenseDesc))
                    <tr>
                        <td class="muted">Detalle</td>
                        <td class="right muted">{{ $expenseDesc }}</td>
                    </tr>
                @endif
            @endif

            <tr>
                <td class="highlight">TOTAL A COBRAR</td>
                <td class="right bold">S/ {{ number_format($totalToCollect, 2) }}</td>
            </tr>

            @if ($isCash)
                <tr>
                    <td>Pagó con</td>
                    <td class="right">S/ {{ number_format($cashReceived, 2) }}</td>
                </tr>
                <tr>
                    <td class="highlight">Vuelto</td>
                    <td class="right bold">S/ {{ number_format($change, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td>Método</td>
                <td class="right">{{ $methodLabel ?? '—' }}</td>
            </tr>
            <tr>
                <td>Referencia</td>
                <td class="right">{{ $reference }}</td>
            </tr>
            <tr>
                <td>Estado</td>
                <td class="right">{{ $statusLabel ?? '—' }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="center mt10">
            <div class="muted">Gracias por su pago</div>
            <div class="muted">—</div>
        </div>

        <div class="no-print" style="text-align:center; margin:10px 0;">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">Imprimir</button>
            <button type="button" onclick="window.close()" class="btn btn-secondary btn-sm">Cerrar</button>
        </div>

        <div style="height:12mm;"></div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            setTimeout(() => window.close(), 500);
        };
    </script>
</body>

</html>
