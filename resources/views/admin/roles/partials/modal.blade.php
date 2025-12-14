<!-- Modal Crear / Editar Rol -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

      {{-- HEADER --}}
      <div class="modal-header align-items-center"
           style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
        <div class="d-flex align-items-center">
          <div class="icon-circle bg-light mr-3"
               style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.06);">
            <i class="fas fa-user-shield text-secondary"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="exampleModalLabel">Nuevo Rol</h5>
            <small class="text-muted">Define el nombre del rol y asigna sus permisos</small>
          </div>
        </div>

        <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      {{-- BODY --}}
      <div class="modal-body p-3" style="background:#f8fbfc;">
        <div class="card border-0 rounded-lg shadow-sm mb-0">
          <div class="card-body">

            <form id="roleForm">
              @csrf

              {{-- Mensajes de error generales --}}
              <div id="error-messages" class="alert alert-danger d-none mb-3"></div>

              {{-- Nombre del rol --}}
              <div class="form-group mb-3">
                <label class="small font-weight-bold text-secondary" for="name">
                  <i class="fas fa-check-circle mr-1 text-warning"></i>
                  NOMBRE DEL ROL <span class="text-danger">*</span>
                </label>
                <input type="text"
                       class="form-control form-control-sm"
                       id="name"
                       name="name"
                       placeholder="Ej. Administrador, Supervisor, Cajero"
                       required>
              </div>

              <hr class="my-3">

              {{-- Título de permisos --}}
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h2 class="h6 mb-0 text-secondary">
                    <i class="fas fa-list-check mr-1"></i>
                    Lista de permisos
                  </h2>
                  <small class="text-muted d-block">Marca los accesos que tendrá este rol</small>
                </div>
              </div>

              {{-- Recuadro de permisos (mismo estilo base pero refinado) --}}
              <div class="border rounded-lg bg-white p-3"
                   style="max-height:320px; overflow-y:auto; box-shadow:0 4px 12px rgba(15,23,42,0.03);">
                <div class="row">
                  @foreach ($permissions as $permission)
                    <div class="col-md-6 mb-2">
                      <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               value="{{ $permission->name }}"
                               id="permission_{{ $permission->id }}"
                               name="permissions[]">
                        <label class="custom-control-label" for="permission_{{ $permission->id }}">
                          {{ $permission->description }}
                          <small class="text-muted d-block">
                            ({{ $permission->guard_name }})
                          </small>
                        </label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- Footer botones --}}
              <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">
                  <i class="fas fa-times mr-1"></i> Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i> Guardar rol
                </button>
              </div>

            </form>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
