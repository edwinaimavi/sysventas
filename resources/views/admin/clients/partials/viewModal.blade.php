<!-- View Client Modal -->
<div class="modal fade" id="viewClientModal" tabindex="-1" role="dialog" aria-labelledby="viewClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded">

      <!-- Header -->
      <div class="modal-header" style="background: linear-gradient(135deg,#f7f7f7,#ececec); border-bottom:1px solid #e0e0e0;">
        <h5 class="modal-title" id="viewClientModalLabel">
          <i class="fas fa-eye text-secondary mr-2"></i> Información del Cliente
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-white">
        <div class="container-fluid">
          <div class="row">
            <!-- FOTO -->
            <div class="col-md-4 text-center border-right">
              <div class="mb-3">
                <img id="vc_photo" src="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg" alt="Foto cliente"
                     class="img-fluid rounded mb-2" style="max-width:180px; object-fit:cover;">
              </div>

              <h5 id="vc_full_name" class="font-weight-bold text-dark mb-1">Nombre Completo</h5>
              <p id="vc_document" class="text-muted mb-1">DNI · 00000000</p>

              <div class="mt-3">
                <span id="vc_status" class="badge badge-success py-2 px-3">Activo</span>
              </div>

              <div class="mt-3 text-left w-100">
                <small class="text-muted">Sucursal</small>
                <div id="vc_branch" class="font-weight-600">Nombre Sucursal</div>

                <small class="text-muted mt-2 d-block">Registrado por</small>
                <div id="vc_user" class="font-weight-600">Usuario</div>
              </div>
            </div>

            <!-- DETALLES -->
            <div class="col-md-8">
              <div class="row mb-2">
                <div class="col-12">
                  <h6 class="text-secondary mb-1">Contacto</h6>
                  <div class="d-flex flex-wrap">
                    <div class="mr-4"><small class="text-muted">Teléfono</small><div id="vc_phone" class="font-weight-600"></div></div>
                    <div class="mr-4"><small class="text-muted">Email</small><div id="vc_email" class="font-weight-600"></div></div>
                  </div>
                </div>
              </div>

              <hr class="my-2">

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-2"><small class="text-muted">Fecha de Nacimiento</small>
                    <div id="vc_birth_date" class="font-weight-600"></div>
                  </div>
                  <div class="mb-2"><small class="text-muted">Género</small>
                    <div id="vc_gender" class="font-weight-600"></div>
                  </div>
                  <div class="mb-2"><small class="text-muted">Estado Civil</small>
                    <div id="vc_marital_status" class="font-weight-600"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="mb-2"><small class="text-muted">Ocupación</small>
                    <div id="vc_occupation" class="font-weight-600"></div>
                  </div>
                  <div class="mb-2"><small class="text-muted">Empresa (si aplica)</small>
                    <div id="vc_company" class="font-weight-600"></div>
                  </div>
                  <div class="mb-2"><small class="text-muted">RUC</small>
                    <div id="vc_ruc" class="font-weight-600"></div>
                  </div>
                </div>
              </div>

              <hr class="my-2">

              <div class="row">
                <div class="col-12">
                  <h6 class="text-secondary mb-1">Información adicional</h6>

                  <div class="mb-2"><small class="text-muted">ID Cliente</small>
                    <div id="vc_id" class="font-weight-600"></div>
                  </div>

                  <div class="mb-2"><small class="text-muted">Fecha de registro</small>
                    <div id="vc_created_at" class="font-weight-600"></div>
                  </div>

                  <div class="mb-3">
                    <small class="text-muted">Puntaje crediticio</small>
                    <div class="progress" style="height:10px;">
                      <div id="vc_credit_score_bar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
                    </div>
                    <div class="mt-1"><span id="vc_credit_score" class="font-weight-600">—</span> / 100</div>
                  </div>

                </div>
              </div>

            </div> <!-- /col-md-8 -->
          </div> <!-- /row -->
        </div> <!-- /container-fluid -->
      </div> <!-- /modal-body -->

      <!-- Footer -->
      

    </div>
  </div>
</div>

