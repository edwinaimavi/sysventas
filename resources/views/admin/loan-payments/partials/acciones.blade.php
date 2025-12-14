{{-- resources/views/admin/loan-payments/partials/acciones.blade.php --}}

@php
    $loan = $payment->loan ?? null;
    $client = $loan->client ?? null;
@endphp

<div class="btn-group" role="group" aria-label="Acciones">

    {{-- VER PAGO --}}
    <button class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewPayment"
        data-bs-toggle="tooltip" data-bs-title="Ver pago" data-id="{{ $payment->id }}"
        data-payment_code="{{ $payment->payment_code }}"
        data-payment_date="{{ $payment->payment_date }}"
        data-amount="{{ $payment->amount }}"
        data-capital="{{ $payment->capital }}"
        data-interest="{{ $payment->interest }}"
        data-late_fee="{{ $payment->late_fee }}"
        data-method="{{ $payment->method }}"
        data-reference="{{ $payment->reference }}"
        data-receipt_number="{{ $payment->receipt_number }}"
        data-receipt_file="{{ $payment->receipt_file }}"
        data-status="{{ $payment->status }}"
        data-notes="{{ $payment->notes }}"
        data-loan_id="{{ $payment->loan_id }}"
        data-loan_code="{{ $loan->loan_code ?? '' }}"
        data-client_name="{{ $client->full_name ?? '—' }}"
        data-branch_name="{{ optional($payment->branch)->name }}"
        data-user_name="{{ optional($payment->user)->name }}">
        <i class="fas fa-eye"></i>
    </button>

    {{-- ANULAR PAGO (solo si NO está revertido) --}}
    @if($payment->status !== 'reversed')
        <button
            class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deletePayment"
            data-id="{{ $payment->id }}"
            data-bs-toggle="tooltip"
            data-bs-title="Anular pago">
            <i class="fas fa-ban"></i>
        </button>
    @endif

</div>
