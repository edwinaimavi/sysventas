@php
    $photoUrl = $guarantor->photo
        ? asset('storage/' . $guarantor->photo)
        : 'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';

    $userName = optional($guarantor->creator)->name ?? 'Usuario no definido';
@endphp

<div class="btn-group" role="group" aria-label="Acciones">

    {{-- VER GARANTE --}}
    @can('admin.guarantors.index')
        <button class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewGuarantor"
            data-bs-toggle="tooltip" data-bs-title="Ver Garante" data-id="{{ $guarantor->id }}"
            data-full_name="{{ $guarantor->full_name }}" data-document_type="{{ $guarantor->document_type }}"
            data-document_number="{{ $guarantor->document_number }}" data-status="{{ $guarantor->status }}"
            data-phone="{{ $guarantor->phone }}" data-alt_phone="{{ $guarantor->alt_phone }}"
            data-email="{{ $guarantor->email }}" data-address="{{ $guarantor->address }}"
            data-company_name="{{ $guarantor->company_name }}" data-ruc="{{ $guarantor->ruc }}"
            data-first_name="{{ $guarantor->first_name }}" data-last_name="{{ $guarantor->last_name }}"
            data-relationship="{{ $guarantor->relationship }}" data-occupation="{{ $guarantor->occupation }}"
            data-is_external="{{ $guarantor->is_external }}" data-photo="{{ $photoUrl }}"
            data-created_by="{{ $userName }}"
            data-created_at="{{ $guarantor->created_at ? $guarantor->created_at->format('d/m/Y H:i') : '' }}">
            <i class="fas fa-eye"></i>
        </button>-
    @endcan


    {{-- EDITAR GARANTE --}}
    @can('admin.guarantors.update')
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center editGuarantor"
            data-bs-toggle="tooltip" data-bs-title="Editar Garante" data-id="{{ $guarantor->id }}"
            data-document_type="{{ $guarantor->document_type }}" data-document_number="{{ $guarantor->document_number }}"
            data-first_name="{{ $guarantor->first_name }}" data-last_name="{{ $guarantor->last_name }}"
            data-full_name="{{ $guarantor->full_name }}" data-phone="{{ $guarantor->phone }}"
            data-alt_phone="{{ $guarantor->alt_phone }}" data-email="{{ $guarantor->email }}"
            data-address="{{ $guarantor->address }}" data-company_name="{{ $guarantor->company_name }}"
            data-ruc="{{ $guarantor->ruc }}" data-relationship="{{ $guarantor->relationship }}"
            data-occupation="{{ $guarantor->occupation }}" data-is_external="{{ $guarantor->is_external }}"
            data-status="{{ $guarantor->status }}" data-photo="{{ $photoUrl }}">
            <i class="fas fa-pen"></i>
        </button>-
    @endcan


    {{-- ELIMINAR GARANTE --}}
    @can('admin.guarantors.destroy')
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deleteGuarantor"
            data-id="{{ $guarantor->id }}" data-bs-toggle="tooltip" data-bs-title="Eliminar Garante">
            <i class="fa fa-trash"></i>
        </button>
    @endcan

</div>
