<!-- Modal elegante para Garante (Bootstrap 4) -->
<div class="modal fade" id="guarantorModal" tabindex="-1" role="dialog" aria-labelledby="guarantorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3 icon_modal_guarantor">
                        <i class="fas fa-user-shield text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="guarantorModalLabel">Nuevo Garante</h5>
                        <small class="text-muted">Registro de garante · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background: #f8fbfc;">
                <form id="guarantorForm" enctype="multipart/form-data" autocomplete="off" class="row">
                    @csrf

                    {{-- Relación opcional con un cliente (se llenará por JS si aplica) --}}
                    <input type="hidden" id="guarantor_client_id" name="client_id">

                    <!-- LEFT: avatar + meta -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar-preview mb-3">
                                    <img id="guarantor_img_preview"
                                        src="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                                        alt="avatar" class="rounded-circle img-fluid"
                                        style="width:140px; height:140px; object-fit:cover; border:6px solid #fff; box-shadow:0 6px 18px rgba(47,63,78,0.08);">
                                </div>

                                <label for="guarantor_image" class="btn btn-sm btn-light border text-secondary mb-2"
                                    style="cursor:pointer;">
                                    <i class="fas fa-camera mr-1"></i> Subir foto
                                </label>
                                <input type="file" id="guarantor_image" name="photo" accept="image/*"
                                    class="d-none" onchange="previewGuarantorImage(event)">

                                <hr>

                                <div class="text-left mb-2">
                                    <small class="text-muted d-block mb-1">Tipo de garante</small>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_external"
                                            name="is_external" value="1" checked>
                                        <label class="custom-control-label small font-weight-bold text-secondary"
                                            for="is_external">
                                            Garante externo (no es cliente)
                                        </label>
                                    </div>
                                    <span class="invalid-feedback d-block" id="is_external-error"></span>
                                </div>

                                <div class="text-left mt-3">
                                    <small class="text-muted d-block">Estado</small>
                                    <div>
                                        <select id="guarantor_status" name="status"
                                            class="form-control form-control-sm mt-1">
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <span class="invalid-feedback" id="status-error"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: form fields -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                <!-- row 1: doc type + doc number -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="guarantor_document_type"
                                            class="small font-weight-bold text-secondary">
                                            TIPO DE DOCUMENTO <span class="text-danger">*</span>
                                        </label>
                                        <select id="guarantor_document_type" name="document_type"
                                            class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="DNI">DNI</option>
                                            <option value="RUC">RUC</option>
                                            <option value="CE">CARNET DE EXTRANJERÍA</option>
                                        </select>
                                        <span class="invalid-feedback" id="document_type-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="guarantor_document_number"
                                            class="small font-weight-bold text-secondary">
                                            NÚMERO DE DOCUMENTO <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_document_number" name="document_number"
                                            placeholder="00000000">
                                        <span class="invalid-feedback" id="document_number-error"></span>
                                    </div>
                                </div>

                                <!-- row 2: first_name + last_name -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="guarantor_first_name"
                                            class="small font-weight-bold text-secondary">
                                            NOMBRES
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_first_name" name="first_name" placeholder="Nombres">
                                        <span class="invalid-feedback" id="first_name-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="guarantor_last_name"
                                            class="small font-weight-bold text-secondary">
                                            APELLIDOS
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_last_name" name="last_name" placeholder="Apellidos">
                                        <span class="invalid-feedback" id="last_name-error"></span>
                                    </div>
                                </div>

                                <!-- row 3: company_name + ruc -->
                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="guarantor_company_name"
                                            class="small font-weight-bold text-secondary">
                                            RAZÓN SOCIAL (si es empresa)
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_company_name" name="company_name"
                                            placeholder="Razón social de la empresa">
                                        <span class="invalid-feedback" id="company_name-error"></span>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label for="guarantor_ruc" class="small font-weight-bold text-secondary">
                                            RUC
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="guarantor_ruc"
                                            name="ruc" placeholder="RUC">
                                        <span class="invalid-feedback" id="ruc-error"></span>
                                    </div>
                                </div>

                                <!-- row 4: phones -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="guarantor_phone" class="small font-weight-bold text-secondary">
                                            TELÉFONO PRINCIPAL
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_phone" name="phone" placeholder="9XXXXXXXX">
                                        <span class="invalid-feedback" id="phone-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="guarantor_alt_phone"
                                            class="small font-weight-bold text-secondary">
                                            TELÉFONO ALTERNATIVO
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_alt_phone" name="alt_phone" placeholder="Otro teléfono">
                                        <span class="invalid-feedback" id="alt_phone-error"></span>
                                    </div>
                                </div>

                                <!-- row 5: email + relationship -->
                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="guarantor_email" class="small font-weight-bold text-secondary">
                                            EMAIL
                                        </label>
                                        <input type="email" class="form-control form-control-sm"
                                            id="guarantor_email" name="email" placeholder="correo@dominio.com">
                                        <span class="invalid-feedback" id="email-error"></span>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label for="guarantor_relationship"
                                            class="small font-weight-bold text-secondary">
                                            RELACIÓN CON EL CLIENTE
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_relationship" name="relationship"
                                            placeholder="Ej: Padre, Amigo, Jefe">
                                        <span class="invalid-feedback" id="relationship-error"></span>
                                    </div>
                                </div>

                                <!-- row 6: occupation -->
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="guarantor_occupation"
                                            class="small font-weight-bold text-secondary">
                                            OCUPACIÓN / PROFESIÓN
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_occupation" name="occupation"
                                            placeholder="Profesión u ocupación">
                                        <span class="invalid-feedback" id="occupation-error"></span>
                                    </div>
                                </div>

                                <!-- row 7: address -->
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="guarantor_address" class="small font-weight-bold text-secondary">
                                            DIRECCIÓN
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="guarantor_address" name="address"
                                            placeholder="Calle, número, mz, lote, referencia...">
                                        <span class="invalid-feedback" id="address-error"></span>
                                    </div>
                                </div>

                                <!-- small note -->
                                <div class="form-row">
                                    <div class="col-12">
                                        <small class="text-muted">
                                            Si el garante es empresa, utiliza Razón Social y RUC.
                                            Si es persona natural, usa Nombres y Apellidos.
                                        </small>
                                    </div>
                                </div>

                                <!-- actions -->
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-light border mr-2"
                                            data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i> Cerrar
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="btnSaveGuarantor">
                                            <i class="fas fa-save mr-1"></i> Guardar Garante
                                        </button>
                                    </div>
                                </div>

                            </div> <!-- card-body -->
                        </div> <!-- card -->
                    </div> <!-- col-lg-8 -->

                </form>
            </div> <!-- modal-body -->

        </div>
    </div>
</div>
