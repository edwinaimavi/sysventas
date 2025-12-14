<!-- Modal Contactos de Cliente -->
<div class="modal fade" id="contactsModal" tabindex="-1" role="dialog" aria-labelledby="contactsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

      <!-- HEADER -->
      <div class="modal-header align-items-center" style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
        <div>
          <h5 class="modal-title mb-0" id="contactsModalLabel">
            <i class="fas fa-address-book mr-1 text-secondary"></i> Contactos del Cliente
          </h5>
          <small class="text-muted">
            Cliente: <span id="contacts_client_name" class="font-weight-bold text-dark">—</span>
          </small>
        </div>
        <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-3" style="background:#f8fbfc;">

        <!-- Botón agregar contacto -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="m-0 font-weight-bold text-secondary">
            Lista de direcciones, teléfonos y referencias
          </h6>
          <button type="button" class="btn btn-sm btn-primary" id="btnAddContact">
            <i class="fas fa-plus mr-1"></i> Agregar contacto
          </button>
        </div>

        <!-- Tabla de contactos -->
        <div class="card border-0 shadow-sm rounded-lg">
          <div class="card-body p-2">
            <div class="table-responsive">
              <table id="contactsTable" class="table table-sm table-hover table-bordered w-100 mb-0">
                <thead class="thead-light">
                  <tr class="text-center">
                    <th style="min-width:110px;">Tipo</th>
                    <th style="min-width:110px;">Teléfono</th>
                    <th style="min-width:180px;">Dirección</th>
                    <th style="min-width:110px;">Distrito</th>                    
                    <th style="min-width:150px;">Contacto / Referencia</th>
                    <th style="min-width:80px;">Principal</th>
                    <th style="min-width:85px;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                      No hay contactos registrados.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Cerrar
        </button>
      </div>

    </div>
  </div>
</div>
