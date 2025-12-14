<!-- Modal -->
<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">

      <!-- Encabezado -->
      <div class="modal-header" style="background: linear-gradient(135deg, #f5f5f5, #e8e8e8); border-bottom: 1px solid #d9d9d9;">
        <h5 class="modal-title fw-semibold text-dark" id="branchModalLabel">
          <i class="fas fa-plus"></i> Nueva Sucursal
        </h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" style="filter: invert(0.5);"></button>
      </div>

      <!-- Cuerpo -->
      <div class="modal-body bg-light">

        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body">
            <p class="text-muted small mb-3">
              <i class="fas fa-info-circle"></i> <span class="text-danger fw-semibold">*</span> Campos obligatorios
            </p>

            <form id="branchForm" enctype="multipart/form-data">
              @csrf
              <div id="error-messages" class="alert alert-danger d-none"></div>

              <!-- Primera fila -->
              <div class="row g-3">
                <div class="col-sm-9">
                  <label class="form-label small fw-semibold">NOMBRE DE LA SUCURSAL <span class="text-danger">*</span></label>
                  <input type="text" class="form-control form-control-sm shadow-sm" id="name" name="name" placeholder="Nombre Sucursal" required>
                </div>
                <div class="col-sm-3">
                  <label class="form-label small fw-semibold">CODIGO</label>
                  <input type="text" class="form-control form-control-sm shadow-sm" id="code" name="code" placeholder="Codigo Sucursal" required>
                </div>
                <div class="col-sm-12">
                  <label class="form-label small fw-semibold">DIRECCION <span class="text-danger">*</span></label>
                  <input type="text" class="form-control form-control-sm shadow-sm" id="address" name="address" placeholder="Dirección del la Sucursal" required>
                </div>
              </div>

              <!-- Segunda fila -->
              <div class="row g-3 mt-2">
                <div class="col-sm-4">
                  <label class="form-label small fw-semibold">TELEFONO <span class="text-danger">*</span></label>
                  <input type="text" class="form-control form-control-sm shadow-sm" id="phone" name="phone" placeholder="Teéfono de contacto" required>
                </div>
                <div class="col-sm-8">
                  <label class="form-label small fw-semibold">EMAIL <span class="text-danger">*</span></label>
                  <input type="email" class="form-control form-control-sm shadow-sm" id="email" name="email">
                </div>
                
              </div>

      

              <!-- Cuarta fila -->
<div class="row mt-3">

    <div class="col-sm-8">
      <div class="form-group">
                    <label for="manager_user_id " class="form-label fw-semibold text-secondary mb-1">
                        <i class="fas fa-user-tie"></i> Responsable <span class="text-danger">*</span>
                    </label>
                    <select id="manager_user_id" name="manager_user_id" 
                            class="form-control form-control-lg border-0 shadow-sm rounded-3" 
                            style="background-color:#f8f9fa; font-size: 15px;">
                            <option value="">Seleccione un Responsable </option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                    
                    </select>
                    </div>              
    </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="is_active" class="form-label fw-semibold text-secondary mb-1">
        <i class="fas fa-toggle-on me-1 text-muted"></i> Estado <span class="text-danger">*</span>
      </label>
      <select id="is_active" name="is_active" 
              class="form-control form-control-lg border-0 shadow-sm rounded-3" 
              style="background-color:#f8f9fa; font-size: 15px;">
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
  </div>
</div>




              <!-- Botones -->
              <div class="text-end mt-4">
                <button type="button" class="btn btn-light border me-2" data-dismiss="modal">
                  <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="submit" class="btn btn-secondary shadow-sm">
                  <i class="fas fa-save"></i> Guardar
                </button>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
