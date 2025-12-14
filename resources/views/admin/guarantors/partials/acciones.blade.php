<div class="btn-group" role="group" aria-label="Acciones">

    {{-- VER GARANTE --}}
    <button 
        class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewGuarantor"
        data-bs-toggle="tooltip"
        data-bs-title="Ver Garante"

        data-full_name="{{ $guarantor->full_name }}"
        data-document_type="{{ $guarantor->document_type }}"
        data-document_number="{{ $guarantor->document_number }}"
        data-status="{{ $guarantor->status }}"
        data-phone="{{ $guarantor->phone }}"
        data-email="{{ $guarantor->email }}"
        data-alt_phone="{{ $guarantor->alt_phone }}"
        data-address="{{ $guarantor->address }}"
        data-company_name="{{ $guarantor->company_name }}"
        data-ruc="{{ $guarantor->ruc }}"
        data-relationship="{{ $guarantor->relationship }}"
        data-occupation="{{ $guarantor->occupation }}"
        data-is_external="{{ $guarantor->is_external }}"
    >
        <i class="fas fa-eye"></i>
    </button>-

    {{-- EDITAR GARANTE --}}
    <button 
        class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center editGuarantor"
        data-bs-toggle="tooltip"
        data-bs-title="Editar Garante"

        data-id="{{ $guarantor->id }}"
        data-document_type="{{ $guarantor->document_type }}"
        data-document_number="{{ $guarantor->document_number }}"
        data-first_name="{{ $guarantor->first_name }}"
        data-last_name="{{ $guarantor->last_name }}"
        data-full_name="{{ $guarantor->full_name }}"
        data-phone="{{ $guarantor->phone }}"
        data-alt_phone="{{ $guarantor->alt_phone }}"
        data-email="{{ $guarantor->email }}"
        data-address="{{ $guarantor->address }}"
        data-company_name="{{ $guarantor->company_name }}"
        data-ruc="{{ $guarantor->ruc }}"
        data-relationship="{{ $guarantor->relationship }}"
        data-occupation="{{ $guarantor->occupation }}"
        data-is_external="{{ $guarantor->is_external }}"
        data-status="{{ $guarantor->status }}"
    >
        <i class="fas fa-pen"></i>
    </button>-

    {{-- ELIMINAR GARANTE --}}
    <button 
        class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deleteGuarantor"
        data-id="{{ $guarantor->id }}"
        data-bs-toggle="tooltip"
        data-bs-title="Eliminar Garante"
    >
        <i class="fa fa-trash"></i>
    </button>

</div>
