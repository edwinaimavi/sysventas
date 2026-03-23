@php
    use App\Models\Branch;
    use Illuminate\Support\Facades\Auth;

    $currentBranch = Branch::find(session('branch_id'));
    $currentBranchName = $currentBranch->name ?? 'Sin sucursal seleccionada';

    $currentUser = Auth::user();
    $currentUserName = $currentUser->name ?? 'Usuario';
@endphp
<!-- Modal elegante para Cliente (Bootstrap 4) -->
<div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3 icon_modal">
                        <i class="fas fa-user-plus text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="clientModalLabel">Nuevo Cliente</h5>
                        <small class="text-muted">Registro rápido · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close" style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-3" style="background: #f8fbfc;">
                <form id="clientForm" enctype="multipart/form-data" autocomplete="off" class="row">
                    @csrf
                    <input type="hidden" id="branch_id" name="branch_id" value="1">
                    <input type="hidden" id="user_id" name="user_id" value="1">

                    <!-- LEFT: avatar + meta -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-0 rounded-lg shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <div class="avatar-preview mb-3">
                                    <img id="client_img_preview"
                                        src="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                                        data-default-avatar="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                                        alt="avatar" class="rounded-circle img-fluid"
                                        style="width:140px; height:140px; object-fit:cover; border:6px solid #fff; box-shadow:0 6px 18px rgba(47,63,78,0.08);">
                                </div>


                                <label for="client_image" class="btn btn-sm btn-light border text-secondary mb-2"
                                    style="cursor:pointer;">
                                    <i class="fas fa-camera mr-1"></i> Subir foto
                                </label>
                                <input type="file" id="client_image" name="image" accept="image/*" class="d-none">

                                <hr>

                                <div class="text-left">
                                    <small class="text-muted">Sucursal</small>
                                    {{-- 👇 aquí mostramos la sucursal actual --}}
                                    <div id="left_branch" class="font-weight-600">
                                        {{ $currentBranchName }}
                                    </div>

                                    <small class="text-muted d-block mt-2">Registrado por</small>
                                    {{-- 👇 aquí mostramos el usuario logueado --}}
                                    <div id="left_user" class="font-weight-600">
                                        {{ $currentUserName }}
                                    </div>

                                    {{-- <small class="text-muted d-block mt-2">Estado</small>
                                    <div id="left_status" class="badge badge-success py-2 px-3 mt-1">Activo</div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- RIGHT: form fields -->
                    <div class="col-lg-8">
                        <div class="card border-0 rounded-lg shadow-sm">
                            <div class="card-body">

                                <!-- row 1 -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="document_type" class="small font-weight-bold text-secondary">TIPO DE
                                            DOCUMENTO <span class="text-danger">*</span></label>
                                        <select id="document_type" name="document_type"
                                            class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="DNI">DNI</option>
                                            <option value="RUC">RUC</option>
                                            <option value="CE">CARNET DE EXTRANJERÍA</option>
                                        </select>
                                        <span class="invalid-feedback" id="document_type-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="document_number"
                                            class="small font-weight-bold text-secondary">NÚMERO DE DOCUMENTO <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="document_number"
                                            name="document_number" placeholder="00000000" required>
                                        <span class="invalid-feedback" id="document_number-error"></span>
                                    </div>
                                </div>

                                <!-- row 2 -->
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="first_name"
                                            class="small font-weight-bold text-secondary">NOMBRES <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="first_name"
                                            name="first_name" placeholder="Nombres" required>
                                        <span class="invalid-feedback" id="first_name-error"></span>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="last_name" class="small font-weight-bold text-secondary">APELLIDOS
                                            <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="last_name"
                                            name="last_name" placeholder="Apellidos" required>
                                        <span class="invalid-feedback" id="last_name-error"></span>
                                    </div>
                                </div>

                                <!-- row 3 -->
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="birth_date" class="small font-weight-bold text-secondary">FECHA
                                            NACIMIENTO</label>
                                        <input type="date" class="form-control form-control-sm" id="birth_date"
                                            name="birth_date">
                                        <span class="invalid-feedback" id="birth_date-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="gender"
                                            class="small font-weight-bold text-secondary">GÉNERO</label>
                                        <select id="gender" name="gender" class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                            <option value="O">Otro</option>
                                        </select>
                                        <span class="invalid-feedback" id="gender-error"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="marital_status"
                                            class="small font-weight-bold text-secondary">ESTADO CIVIL</label>
                                        <select id="marital_status" name="marital_status"
                                            class="form-control form-control-sm">
                                            <option value="">Seleccione</option>
                                            <option value="soltero">Soltero</option>
                                            <option value="casado">Casado</option>
                                            <option value="divorciado">Divorciado</option>
                                            <option value="viudo">Viudo</option>
                                        </select>
                                        <span class="invalid-feedback" id="marital_status-error"></span>
                                    </div>
                                </div>

                                <!-- row 4 -->
                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="occupation"
                                            class="small font-weight-bold text-secondary">OCUPACIÓN / PROFESIÓN</label>
                                        <input type="text" class="form-control form-control-sm" id="occupation"
                                            name="occupation" placeholder="Profesión u ocupación">
                                        <span class="invalid-feedback" id="occupation-error"></span>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label for="phone" class="small font-weight-bold text-secondary">TELÉFONO
                                            <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="phone"
                                            name="phone" placeholder="9XXXXXXXX" required>
                                        <span class="invalid-feedback" id="phone-error"></span>
                                    </div>
                                </div>

                                <!-- row 5 -->
                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="email"
                                            class="small font-weight-bold text-secondary">EMAIL</label>
                                        <input type="email" class="form-control form-control-sm" id="email"
                                            name="email" placeholder="correo@dominio.com">
                                        <span class="invalid-feedback" id="email-error"></span>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label for="status"
                                            class="small font-weight-bold text-secondary">ESTADO</label>
                                        <select id="status" name="status" class="form-control form-control-sm">
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <span class="invalid-feedback" id="status-error"></span>
                                    </div>


                                </div>


                                <!-- small note -->
                                <div class="form-row">
                                    <div class="col-12">
                                        <small class="text-muted">Si el cliente es persona jurídica (RUC), utiliza el
                                            campo de nombres/apellidos para la razón social.</small>
                                    </div>
                                </div>

                                <!-- actions -->
                                <div class="form-row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-light border mr-2"
                                            data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cerrar</button>
                                        <button type="submit" class="btn btn-primary" id="btnSaveClient"><i
                                                class="fas fa-save mr-1"></i> Guardar Cliente</button>
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
