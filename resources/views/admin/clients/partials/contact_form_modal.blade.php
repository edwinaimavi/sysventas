<!-- Modal para AGREGAR / EDITAR CONTACTO DE CLIENTE -->
<div class="modal fade" id="contactFormModal" tabindex="-1" role="dialog" aria-labelledby="contactFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

      {{-- HEADER --}}
      <div class="modal-header" style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
        <div>
          <h5 class="modal-title mb-0" id="contactFormModalLabel">
            <i class="fas fa-address-card mr-1 text-secondary"></i>
            <span id="contactFormTitle">Nuevo contacto</span>
          </h5>
          <small class="text-muted">
            Completa los datos del contacto asociado al cliente.
          </small>
        </div>

        <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      {{-- BODY --}}
      <div class="modal-body" style="background:#f8fbfc;">
        <form id="contactForm" autocomplete="off">
          @csrf

          {{-- IDs ocultos --}}
          <input type="hidden" id="contact_client_id" name="client_id">
          <input type="hidden" id="contact_id" name="contact_id">

          <div class="card border-0 rounded-lg shadow-sm">
            <div class="card-body">

              {{-- fila 1: tipo + principal --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="contact_type" class="small font-weight-bold text-secondary">
                    TIPO DE CONTACTO <span class="text-danger">*</span>
                  </label>
                  <select id="contact_type" name="contact_type" class="form-control form-control-sm">
                    <option value="Domicilio">Domicilio</option>
                    <option value="Trabajo">Trabajo</option>
                    <option value="Referencia">Referencia</option>
                    <option value="Otro">Otro</option>
                  </select>
                  <span class="invalid-feedback" id="contact_type-error"></span>
                </div>

                <div class="form-group col-md-6 d-flex align-items-center">
                  <div class="custom-control custom-switch mt-3">
                    <input type="checkbox" class="custom-control-input" id="is_primary" name="is_primary" value="1">
                    <label class="custom-control-label small font-weight-bold text-secondary" for="is_primary">
                      Marcar como contacto principal
                    </label>
                  </div>
                  <span class="invalid-feedback d-block" id="is_primary-error"></span>
                </div>
              </div>

              {{-- fila 2: dirección + distrito --}}
              <div class="form-row">
                <div class="form-group col-md-8">
                  <label for="address" class="small font-weight-bold text-secondary">
                    DIRECCIÓN
                  </label>
                  <input type="text" class="form-control form-control-sm" id="address" name="address" placeholder="Calle, número, mz, lote...">
                  <span class="invalid-feedback" id="address-error"></span>
                </div>

                <div class="form-group col-md-4">
                  <label for="district" class="small font-weight-bold text-secondary">
                    DISTRITO
                  </label>
                  <input type="text" class="form-control form-control-sm" id="district" name="district" placeholder="Distrito">
                  <span class="invalid-feedback" id="district-error"></span>
                </div>
              </div>

              {{-- fila 3: provincia + departamento --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="province" class="small font-weight-bold text-secondary">
                    PROVINCIA
                  </label>
                  <input type="text" class="form-control form-control-sm" id="province" name="province" placeholder="Provincia">
                  <span class="invalid-feedback" id="province-error"></span>
                </div>

                <div class="form-group col-md-6">
                  <label for="department" class="small font-weight-bold text-secondary">
                    DEPARTAMENTO
                  </label>
                  <input type="text" class="form-control form-control-sm" id="department" name="department" placeholder="Departamento">
                  <span class="invalid-feedback" id="department-error"></span>
                </div>
              </div>

              {{-- fila 4: referencia --}}
              <div class="form-row">
                <div class="form-group col-12">
                  <label for="reference" class="small font-weight-bold text-secondary">
                    REFERENCIA
                  </label>
                  <textarea class="form-control form-control-sm" id="reference" name="reference" rows="2"
                            placeholder="Punto de referencia (ej: frente al parque, cerca al mercado, etc.)"></textarea>
                  <span class="invalid-feedback" id="reference-error"></span>
                </div>
              </div>

              {{-- fila 5: teléfonos + email --}}
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="phone" class="small font-weight-bold text-secondary">
                    TELÉFONO
                  </label>
                  <input type="text" class="form-control form-control-sm" id="phone" name="phone" placeholder="Teléfono principal">
                  <span class="invalid-feedback" id="phone-error"></span>
                </div>

                <div class="form-group col-md-4">
                  <label for="alt_phone" class="small font-weight-bold text-secondary">
                    TELÉFONO ALTERNATIVO
                  </label>
                  <input type="text" class="form-control form-control-sm" id="alt_phone" name="alt_phone" placeholder="Otro teléfono">
                  <span class="invalid-feedback" id="alt_phone-error"></span>
                </div>

                <div class="form-group col-md-4">
                  <label for="contact_email" class="small font-weight-bold text-secondary">
                    EMAIL
                  </label>
                  <input type="email" class="form-control form-control-sm" id="contact_email" name="email" placeholder="correo@dominio.com">
                  <span class="invalid-feedback" id="email-error"></span>
                </div>
              </div>

              {{-- fila 6: persona de referencia --}}
              <div class="form-row">
                <div class="form-group col-md-7">
                  <label for="contact_name" class="small font-weight-bold text-secondary">
                    NOMBRE DE CONTACTO / REFERENCIA
                  </label>
                  <input type="text" class="form-control form-control-sm" id="contact_name" name="contact_name" placeholder="Nombre de la persona de contacto">
                  <span class="invalid-feedback" id="contact_name-error"></span>
                </div>

                <div class="form-group col-md-5">
                  <label for="relationship" class="small font-weight-bold text-secondary">
                    PARENTESCO / RELACIÓN
                  </label>
                  <input type="text" class="form-control form-control-sm" id="relationship" name="relationship" placeholder="Ej: Padre, Hermano, Jefe, Amigo">
                  <span class="invalid-feedback" id="relationship-error"></span>
                </div>
              </div>

            </div> {{-- card-body --}}
          </div> {{-- card --}}

          {{-- ACCIONES --}}
          <div class="form-row mt-3">
            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Cerrar
              </button>
              <button type="submit" class="btn btn-primary" id="btnSaveContact">
                <i class="fas fa-save mr-1"></i> Guardar contacto
              </button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
