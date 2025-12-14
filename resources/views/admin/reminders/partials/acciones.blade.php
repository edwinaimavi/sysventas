{{-- resources/views/admin/reminders/partials/acciones.blade.php --}}

<div class="btn-group" role="group" aria-label="Acciones">

    {{-- VER --}}
    <button
        class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewReminder"
        data-bs-toggle="tooltip"
        data-bs-title="Ver recordatorio"

        data-id="{{ $reminder->id }}"
        data-title="{{ $reminder->title }}"
        data-message="{{ $reminder->message }}"
        data-type="{{ $reminder->type }}"
        data-priority="{{ $reminder->priority }}"
        data-remind_at="{{ $reminder->remind_at }}"
        data-expires_at="{{ $reminder->expires_at }}"
        data-created_for_date="{{ $reminder->created_for_date }}"
        data-status="{{ $reminder->status }}"
        data-is_read="{{ $reminder->is_read }}"
        data-read_at="{{ $reminder->read_at }}"
        data-sent_at="{{ $reminder->sent_at }}"
        data-channel="{{ $reminder->channel }}"
        data-channel_status="{{ $reminder->channel_status }}"
        data-loan_id="{{ $reminder->loan_id }}"
        data-client_id="{{ $reminder->client_id }}"
    >
        <i class="fas fa-eye"></i>
    </button>

    {{-- MARCAR COMO LEÍDO (solo si no está leído) --}}
    @if(!$reminder->is_read)
        <button
            class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center markReadReminder"
            data-id="{{ $reminder->id }}"
            data-bs-toggle="tooltip"
            data-bs-title="Marcar como leído"
        >
            <i class="fas fa-check"></i>
        </button>
    @endif

    {{-- CANCELAR (solo si está pendiente) --}}
    @if($reminder->status === 'pending')
        <button
            class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center cancelReminder"
            data-id="{{ $reminder->id }}"
            data-bs-toggle="tooltip"
            data-bs-title="Cancelar recordatorio"
        >
            <i class="fas fa-ban"></i>
        </button>
    @endif

</div>
