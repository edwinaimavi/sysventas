{{-- ✅ admin/loans/partials/acciones.blade.php --}}
{{-- PEGA ESTE ARCHIVO COMPLETO (opción A: ocultar el botón si no toca) --}}

@php
    $statusRaw = $loan->status ?? '';
    $statusClean = strtolower(trim($statusRaw)); // "finished", "approved", etc.

    // Por si no llegara desde el controlador (evita errores)
    $canRefinance = $canRefinance ?? false;
@endphp

<div class="btn-group" role="group" aria-label="Acciones">
    @can('admin.loans.index')
        {{-- SIEMPRE permitir VER --}}
        <button class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewLoan mr-2"
            data-bs-toggle="tooltip" data-bs-title="Ver Préstamo" data-id="{{ $loan->id }}"
            data-loan_code="{{ $loan->loan_code }}" data-amount="{{ $loan->amount }}"
            data-term_months="{{ $loan->term_months }}" data-interest_rate="{{ $loan->interest_rate }}"
            data-monthly_payment="{{ $loan->monthly_payment }}" data-total_payable="{{ $loan->total_payable }}"
            data-disbursement_date="{{ $loan->disbursement_date }}" data-status="{{ $loan->status }}"
            data-notes="{{ $loan->notes }}" data-client_id="{{ $loan->client_id }}"
            data-client_name="{{ optional($loan->client)->full_name }}" data-guarantor_id="{{ $loan->guarantor_id }}"
            data-guarantor_name="{{ optional($loan->guarantor)->full_name }}" data-branch_id="{{ $loan->branch_id }}"
            data-branch_name="{{ optional($loan->branch)->name }}" data-created_at="{{ $loan->created_at }}"
            data-user_id="{{ $loan->user_id }}" data-user_name="{{ optional($loan->user)->name }}">
            <i class="fas fa-eye"></i>
        </button>
    @endcan
    {{-- SOLO SI NO ESTÁ FINALIZADO --}}
    @if ($statusClean !== 'finished')
        @can('admin.loans.update')
            {{-- EDITAR PRÉSTAMO --}}
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center editLoan mr-2"
                data-bs-toggle="tooltip" data-bs-title="Editar Préstamo" data-id="{{ $loan->id }}"
                data-loan_code="{{ $loan->loan_code }}" data-amount="{{ $loan->amount }}"
                data-term_months="{{ $loan->term_months }}" data-interest_rate="{{ $loan->interest_rate }}"
                data-monthly_payment="{{ $loan->monthly_payment }}" data-total_payable="{{ $loan->total_payable }}"
                data-disbursement_date="{{ $loan->disbursement_date ? \Carbon\Carbon::parse($loan->disbursement_date)->format('Y-m-d') : '' }}"
                data-status="{{ $loan->status }}"
                data-due_date="{{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('Y-m-d') : '' }}"
                data-notes="{{ $loan->notes }}" data-client_id="{{ $loan->client_id }}"
                data-client_name="{{ optional($loan->client)->full_name }}" data-guarantor_id="{{ $loan->guarantor_id }}"
                data-guarantor_name="{{ optional($loan->guarantor)->full_name }}" data-branch_id="{{ $loan->branch_id }}"
                data-user_id="{{ $loan->user_id }}" data-user_name="{{ optional($loan->user)->name }}">
                <i class="fas fa-pen"></i>
            </button>
        @endcan
        @can('admin.loans.increments')
            {{-- BOTÓN PARA INCREMENTAR --}}
            @php
                $hasDisbursement = (float) $totalDisbursed > 0;
            @endphp

            <button
                class="btn btn-sm d-flex align-items-center justify-content-center incrementLoan mr-2 
    {{ $hasDisbursement ? 'btn-outline-warning' : 'btn-secondary' }}"
                data-bs-toggle="tooltip"
                data-bs-title="{{ $hasDisbursement ? 'Ampliar / Incrementar préstamo' : 'Debe tener al menos un desembolso' }}"
                data-id="{{ $loan->id }}" data-loan_code="{{ $loan->loan_code }}"
                data-client_name="{{ optional($loan->client)->full_name }}" data-amount="{{ $loan->amount }}"
                data-status="{{ $loan->status }}" {{ !$hasDisbursement ? 'disabled' : '' }}>
                <i class="fas fa-plus-circle"></i>
            </button>
        @endcan
        {{-- BOTÓN PARA DESEMBOLSO --}}
        @php
            $isApproved = in_array($statusClean, ['approved', 'disbursed']);
            $btnClass = 'btn-success';
            $tooltip = 'Registrar desembolso';

            if ($isFullyDisbursed) {
                $btnClass = 'btn-secondary';
                $tooltip = 'Préstamo totalmente desembolsado';
            } elseif (!$isApproved) {
                $btnClass = 'btn-secondary';
                $tooltip = 'Solo se pueden desembolsar préstamos aprobados o desembolsados';
            }

            $remaining = max(0, (float) $loan->amount - (float) $totalDisbursed);
        @endphp
        @can('admin.loans.disbursements')
            <button
                class="btn btn-sm d-flex align-items-center justify-content-center disbursementModal {{ $btnClass }} mr-2"
                data-bs-toggle="tooltip" data-bs-title="{{ $tooltip }}" data-loan_id="{{ $loan->id }}"
                data-loan_code="{{ $loan->loan_code }}" data-client_name="{{ optional($loan->client)->full_name }}"
                data-amount="{{ $loan->amount }}" data-total_disbursed="{{ $totalDisbursed }}"
                data-remaining="{{ $remaining }}" data-status="{{ $statusClean }}"
                data-is_fully_disbursed="{{ $isFullyDisbursed ? '1' : '0' }}">
                <i class="fas fa-money-bill-wave"></i>
            </button>
        @endcan
        {{-- ✅ BOTÓN REFINANCIAR: SOLO SI TOCA (vencido + disbursed + saldo>0 + sin refinance activo) --}}
        @if ($canRefinance)
            @can('admin.loans.refinance')
                <button
                    class="btn btn-outline-dark btn-sm d-flex align-items-center justify-content-center refinanceLoan mr-2"
                    data-bs-toggle="tooltip" data-bs-title="Refinanciar" data-id="{{ $loan->id }}"
                    data-loan_code="{{ $loan->loan_code }}" data-client_name="{{ optional($loan->client)->full_name }}"
                    data-due_date="{{ $loan->due_date ? \Carbon\Carbon::parse($loan->due_date)->format('Y-m-d') : '' }}"
                    data-remaining="{{ $loan->remainingBalance() }}" data-interest_rate="{{ $loan->interest_rate }}"
                    data-term_months="{{ $loan->term_months }}">
                    <i class="fas fa-sync-alt"></i>
                </button>
            @endcan
        @endif


        {{-- BORRAR PRÉSTAMO --}}
        @can('admin.loans.destroy')
            <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deleteLoan"
                data-id="{{ $loan->id }}" data-bs-toggle="tooltip" data-bs-title="Eliminar Préstamo">
                <i class="fa fa-trash"></i>
            </button>
        @endcan
    @endif
</div>
