{{-- resources/views/admin/loan-payments/partials/acciones.blade.php --}}
@php
    use App\Models\LoanSchedule;

    $loan = $payment->loan ?? null;
    $client = $loan->client ?? null;

    // ✅ gasto adicional
    $expenseAmount = optional($payment->expense)->expense_amount ?? 0;
    $expenseType = optional($payment->expense)->expense_type ?? null;
    $expenseDesc = optional($payment->expense)->expense_description ?? null;

    // ✅ cuotas pagadas ese día (inferidas por paid_at)
    $installments = [];
    if ($payment->payment_date && $payment->loan_id) {
        $installments = LoanSchedule::where('loan_id', $payment->loan_id)
            ->whereDate('paid_at', \Carbon\Carbon::parse($payment->payment_date)->toDateString())
            ->orderBy('installment_no')
            ->pluck('installment_no')
            ->toArray();
    }

    // Si pagó varias cuotas, mandamos "1,2,3". Si ninguna, mandamos vacío.
    $installmentsStr = implode(',', $installments);
@endphp

@php
    $loan = $payment->loan ?? null;
    $client = $loan->client ?? null;
@endphp

<div class="btn-group" role="group" aria-label="Acciones">

    {{-- VER PAGO --}}
    <button class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewPayment"
        data-bs-toggle="tooltip" data-bs-title="Ver pago" data-id="{{ $payment->id }}"
        data-payment_code="{{ $payment->payment_code }}" data-payment_date="{{ $payment->payment_date }}"
        data-amount="{{ $payment->amount }}" data-capital="{{ $payment->capital }}"
        data-interest="{{ $payment->interest }}" data-late_fee="{{ $payment->late_fee }}"
        data-method="{{ $payment->method }}" data-reference="{{ $payment->reference }}"
        data-receipt_number="{{ $payment->receipt_number }}" data-receipt_file="{{ $payment->receipt_file }}"
        data-status="{{ $payment->status }}" data-notes="{{ $payment->notes }}"
        data-loan_id="{{ $payment->loan_id }}" data-loan_code="{{ $loan->loan_code ?? '' }}"
        data-client_name="{{ $client->full_name ?? '—' }}" data-branch_name="{{ optional($payment->branch)->name }}"
        data-user_name="{{ optional($payment->user)->name }}" data-expense_amount="{{ $expenseAmount }}"
        data-expense_type="{{ $expenseType }}" data-expense_description="{{ $expenseDesc }}"
        data-installments="{{ $installmentsStr }}">


        <i class="fas fa-eye"></i>
    </button>-

    {{-- IMPRIMIR TICKET --}}
    {{-- IMPRIMIR TICKET --}}
    @if ($payment->status !== 'reversed')
        <button type="button"
            class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center printPayment"
            data-id="{{ $payment->id }}" data-bs-toggle="tooltip" data-bs-title="Imprimir comprobante">
            <i class="fas fa-print"></i>
        </button>
    @endif

    {{-- ANULAR PAGO (solo si NO está revertido) --}}
    @if ($payment->status !== 'reversed')
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deletePayment"
            data-id="{{ $payment->id }}" data-bs-toggle="tooltip" data-bs-title="Anular pago">
            <i class="fas fa-ban"></i>
        </button>
    @endif

</div>
