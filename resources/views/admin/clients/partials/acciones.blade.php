@php
    $photoUrl = $client->photo
        ? asset('storage/' . $client->photo)
        : 'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';

    $branchName = optional($client->branch)->name ?? 'Sin sucursal';
    $userName = optional($client->user)->name ?? 'Usuario no definido';
@endphp

<div class="btn-group" role="group" aria-label="Acciones">
    @can('admin.clientes.index')
        <button class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center viewClient"
            data-bs-toggle="tooltip" data-bs-title="Ver Cliente" data-id="{{ $client->id }}"
            data-full_name="{{ $client->full_name }}" data-document_type="{{ $client->document_type }}"
            data-document_number="{{ $client->document_number }}" data-status="{{ $client->status }}"
            data-phone="{{ $client->phone }}" data-email="{{ $client->email }}" data-birth_date="{{ $client->birth_date }}"
            data-occupation="{{ $client->occupation }}" data-gender="{{ $client->gender }}"
            data-company_name="{{ $client->company_name }}" data-ruc="{{ $client->ruc }}"
            data-marital_status="{{ $client->marital_status }}" {{-- nuevos --}} data-photo="{{ $photoUrl }}"
            data-branch="{{ $branchName }}" data-user="{{ $userName }}"
            data-created_at="{{ $client->created_at ? $client->created_at->format('d/m/Y H:i') : '' }}"
            data-credit_score="{{ $client->credit_score }}">
            <i class="fas fa-eye"></i>
        </button>-
    @endcan

    @can('admin.clientes.update')
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center editClient"
            data-bs-toggle="tooltip" data-bs-title="Editar Cliente" data-id="{{ $client->id }}"
            data-document_type ="{{ $client->document_type }}" data-document_number ="{{ $client->document_number }}"
            data-first_name ="{{ $client->first_name }}" data-last_name ="{{ $client->last_name }}"
            data-birth_date ="{{ $client->birth_date }}" data-gender ="{{ $client->gender }}"
            data-marital_status ="{{ $client->marital_status }}" data-occupation ="{{ $client->occupation }}"
            data-phone ="{{ $client->phone }}" data-email = "{{ $client->email }}" data-status="{{ $client->status }}"
            {{-- nueva: foto para el modal de edición --}} data-photo="{{ $photoUrl }}">
            <i class="fas fa-pen"></i>
        </button>-
    @endcan

    @can('admin.clientes.store')
        {{-- Botón gestionar contactos --}}
        <button type="button" class="btn btn-sm btn-outline-success manageContacts" data-id="{{ $client->id }}"
            data-full_name="{{ $client->full_name }}" data-toggle="tooltip" title="Contactos del cliente">
            <i class="fas fa-address-book"></i>
        </button>-
    @endcan

    @can('admin.clientes.destroy')
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deleteClient"
            data-id="{{ $client->id }}">
            <i class="fa fa-trash"></i>
        </button>
    @endcan
</div>
