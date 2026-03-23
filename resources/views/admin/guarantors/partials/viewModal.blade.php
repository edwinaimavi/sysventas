<!-- View Guarantor Modal -->
<div class="modal fade" id="viewGuarantorModal" tabindex="-1" role="dialog" aria-labelledby="viewGuarantorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded">

      <!-- Header -->
      <div class="modal-header" style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
        <h5 class="modal-title" id="viewGuarantorModalLabel">
          <i class="fas fa-user-shield text-secondary mr-2"></i> Información del Garante
        </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-white">
        <div class="container-fluid">
          <div class="row">

            <!-- FOTO -->
            <div class="col-md-4 text-center border-right">

              <div class="mb-3">
                <img id="vg_photo"
                     src="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                     class="img-fluid rounded mb-2"
                     style="max-width:180px; object-fit:cover;">
              </div>

              <h5 id="vg_full_name" class="font-weight-bold text-dark mb-1">Nombre Garante</h5>
              <p id="vg_document" class="text-muted mb-1">DNI · 00000000</p>

              <div class="mt-2">
                <span id="vg_status" class="badge badge-success py-2 px-3">Activo</span>
              </div>

              <div class="mt-3 text-left">
                <small class="text-muted">Tipo de garante</small>
                <div id="vg_type" class="font-weight-600">Externo</div>

                <small class="text-muted mt-2 d-block">ID Garante</small>
                <div id="vg_id" class="font-weight-600"></div>
              </div>
            </div>

            <!-- DETALLES -->
            <div class="col-md-8">

              <!-- Contacto -->
              <div class="mb-3">
                <h6 class="text-secondary mb-1">Contacto</h6>
                <div class="d-flex flex-wrap">
                  <div class="mr-4">
                    <small class="text-muted">Teléfono</small>
                    <div id="vg_phone" class="font-weight-600"></div>
                  </div>
                  <div class="mr-4">
                    <small class="text-muted">Teléfono Alternativo</small>
                    <div id="vg_alt_phone" class="font-weight-600"></div>
                  </div>
                  <div>
                    <small class="text-muted">Email</small>
                    <div id="vg_email" class="font-weight-600"></div>
                  </div>
                </div>
              </div>

              <hr class="my-2">

              <!-- Información Personal / Empresa -->
              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-2">
                    <small class="text-muted">Nombres</small>
                    <div id="vg_first_name" class="font-weight-600"></div>
                  </div>

                  <div class="mb-2">
                    <small class="text-muted">Apellidos</small>
                    <div id="vg_last_name" class="font-weight-600"></div>
                  </div>

                  <div class="mb-2">
                    <small class="text-muted">Ocupación</small>
                    <div id="vg_occupation" class="font-weight-600"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="mb-2">
                    <small class="text-muted">Razón Social</small>
                    <div id="vg_company_name" class="font-weight-600"></div>
                  </div>

                  <div class="mb-2">
                    <small class="text-muted">RUC</small>
                    <div id="vg_ruc" class="font-weight-600"></div>
                  </div>

                  <div class="mb-2">
                    <small class="text-muted">Relación con el Cliente</small>
                    <div id="vg_relationship" class="font-weight-600"></div>
                  </div>
                </div>
              </div>

              <hr class="my-2">

              <!-- Dirección -->
              <div class="mb-3">
                <small class="text-muted">Dirección</small>
                <div id="vg_address" class="font-weight-600"></div>
              </div>

              <!-- Información sistema -->
              <div class="row">
                <div class="col-sm-6">
                  <small class="text-muted">Registrado por</small>
                  <div id="vg_created_by" class="font-weight-600"></div>
                </div>

                <div class="col-sm-6">
                  <small class="text-muted">Fecha de registro</small>
                  <div id="vg_created_at" class="font-weight-600"></div>
                </div>
              </div>

            </div> <!-- col-md-8 -->
          </div>
        </div>
      </div>

    </div>
  </div>
</div>