 {{-- @can('admin.customers.update') --}}
<div class="btn-group" role="group" aria-label="Acciones">
   
    <button 
   class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center editBranch"
    data-bs-toggle="tooltip" 
    data-bs-title="Editar sucursal"
    data-id="{{ $branch->id }}" 
    data-name="{{ $branch->name }}" 
    data-code ="{{ $branch->code }}"
    data-address="{{ $branch->address }}" 
    data-phone="{{ $branch->phone }}" 
    data-email="{{ $branch->email }}" 
    data-manager_user_id ="{{ $branch->manager_user_id  }}" 
    data-is_active="{{ $statusOriginal }}"
   ><i class="fas fa-pen"></i></button>-
{{-- @endcan --}}
{{-- @can('admin.customers.destroy') --}}
    <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center deleteBranch" data-id="{{ $branch->id }}"
     data-bs-toggle="tooltip" 
    data-bs-title="Eliminar sucursal"   
    >
        
    <i class="fa fa-trash"></i>
    </button>
{{-- @endcan --}}
</div>

