{{-- Modal elegante para Usuario (Bootstrap 4) --}}
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-xl border-0 rounded-xl overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header align-items-center"
                style="background: linear-gradient(90deg,#ffffff,#f3f6f8); border-bottom:1px solid #e6eaee;">

                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-light mr-3">
                        <i class="fas fa-user text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="userModalLabel">Nuevo Usuario</h5>
                        <small class="text-muted">Registro de usuario · completa los campos obligatorios</small>
                    </div>
                </div>

                <button type="button" class="close ml-3" data-dismiss="modal" aria-label="Close"
                    style="opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3" style="background:#f8fbfc;">

                <form id="userForm" autocomplete="off" enctype="multipart/form-data">
                    @csrf

                    <div id="error-messages" class="alert alert-danger d-none"></div>

                    <div class="card border-0 rounded-lg shadow-sm">
                        <div class="card-body">

                            <span class="text-danger small d-block mb-3">
                                * Campos obligatorios
                            </span>

                            {{-- FILA 1 --}}
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold text-secondary">
                                        DNI <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="dni"
                                        name="dni" placeholder="Num DNI" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-secondary">
                                        Nombres <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="name"
                                        name="name" placeholder="Nombres" required>
                                </div>

                                <div class="form-group col-md-5">
                                    <label class="small font-weight-bold text-secondary">
                                        Apellidos <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="lastname"
                                        name="lastname" placeholder="Apellidos" required>
                                </div>
                            </div>

                            {{-- FILA 2 --}}
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold text-secondary">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control form-control-sm"
                                        id="email" name="email" placeholder="Email" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold text-secondary">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-sm"
                                        id="password" name="password">
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold text-secondary">
                                        Repetir Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-sm"
                                        id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>

                            {{-- FILA 3 --}}
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label class="small font-weight-bold text-secondary">
                                        Celular
                                    </label>
                                    <input type="text" class="form-control form-control-sm"
                                        id="phone" name="phone" placeholder="Número de celular">
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="small font-weight-bold text-secondary">
                                        Dirección
                                    </label>
                                    <input type="text" class="form-control form-control-sm"
                                        id="address" name="address"
                                        placeholder="Dirección del usuario">
                                </div>
                            </div>

                            {{-- FILA 4 --}}
                            <div class="form-row">

                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-secondary">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-secondary">
                                        Rol <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-sm"
                                        name="role" id="role" required>
                                        <option value="">Seleccione un Rol</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- FOTO --}}
                                <div class="form-group col-md-4">
                                    <label class="small font-weight-bold text-secondary d-block">
                                        Foto de perfil
                                    </label>

                                    <div class="position-relative"
                                        style="aspect-ratio: 1/1; max-width:160px;">
                                        <img id="imgPreview"
                                            src="https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                                            class="w-100 h-100 object-fit-cover rounded-lg border"
                                            alt="image">

                                        <label class="position-absolute"
                                            style="bottom:10px; right:10px; cursor:pointer;">
                                            <span class="btn btn-sm btn-light shadow-sm">
                                                <i class="fas fa-upload"></i>
                                            </span>
                                            <input type="file" class="d-none"
                                                name="image" id="image"
                                                accept="image/*"
                                                onchange="previewImage(event, '#imgPreview')">
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            {{-- BOTONES --}}
                            <div class="form-row mt-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button"
                                        class="btn btn-light border mr-2"
                                        data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i> Cerrar
                                    </button>

                                    <button type="submit"
                                        class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Guardar Usuario
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
