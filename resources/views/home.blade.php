@extends('layouts.app')

@section('subtitle', 'home')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-tie"></i> Home</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">
                                <i class="fa fa-fw fa-house-user"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-user-tie"></i> Clientes
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@stop

@section('content_body')
    @php
        $branches = \App\Models\Branch::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
    @endphp

    {{-- Modal para seleccionar sucursal --}}
    <div class="modal fade" id="selectBranchModal" tabindex="-1" role="dialog" aria-labelledby="selectBranchModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg rounded">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="selectBranchModalLabel">
                        <i class="fas fa-store mr-2"></i> Seleccionar sucursal
                    </h5>
                </div>
                <div class="modal-body">
                    <form id="branchSelectForm">
                        @csrf
                        <div class="form-group">
                            <label for="branch_id">Sucursal</label>
                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">Seleccione sucursal</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger small d-none" id="branch-error"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="btnOpenCreateBranch">
                        <i class="fas fa-plus mr-1"></i> Registrar sucursal
                    </button>

                    <button type="button" class="btn btn-primary" id="btnSaveBranch">
                        <i class="fas fa-check mr-1"></i> Aceptar
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal para CREAR sucursal (copiado del módulo de sucursales) --}}
    <div class="modal fade" id="branchCreateModal" tabindex="-1" aria-labelledby="branchCreateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <!-- Encabezado -->
                <div class="modal-header"
                     style="background: linear-gradient(135deg, #f5f5f5, #e8e8e8); border-bottom: 1px solid #d9d9d9;">
                    <h5 class="modal-title fw-semibold text-dark" id="branchCreateModalLabel">
                        <i class="fas fa-plus"></i> Nueva Sucursal
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"
                            style="filter: invert(0.5);"></button>
                </div>

                <!-- Cuerpo -->
                <div class="modal-body bg-light">

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle"></i>
                                <span class="text-danger fw-semibold">*</span> Campos obligatorios
                            </p>

                            <form id="branchCreateForm" enctype="multipart/form-data">
                                @csrf
                                <div id="error-messages" class="alert alert-danger d-none"></div>

                                <!-- Primera fila -->
                                <div class="row g-3">
                                    <div class="col-sm-9">
                                        <label class="form-label small fw-semibold">
                                            NOMBRE DE LA SUCURSAL <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm shadow-sm"
                                               id="name" name="name" placeholder="Nombre Sucursal" required>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="form-label small fw-semibold">CODIGO</label>
                                        <input type="text" class="form-control form-control-sm shadow-sm"
                                               id="code" name="code" placeholder="Codigo Sucursal">
                                    </div>
                                    <div class="col-sm-12">
                                        <label class="form-label small fw-semibold">
                                            DIRECCION <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm shadow-sm"
                                               id="address" name="address" placeholder="Dirección de la Sucursal" required>
                                    </div>
                                </div>

                                <!-- Segunda fila -->
                                <div class="row g-3 mt-2">
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold">
                                            TELEFONO <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm shadow-sm"
                                               id="phone" name="phone" placeholder="Teléfono de contacto">
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="form-label small fw-semibold">
                                            EMAIL <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control form-control-sm shadow-sm"
                                               id="email" name="email" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>

                                <!-- Cuarta fila -->
                                <div class="row mt-3">
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="manager_user_id"
                                                   class="form-label fw-semibold text-secondary mb-1">
                                                <i class="fas fa-user-tie"></i> Responsable
                                            </label>
                                            <select id="manager_user_id" name="manager_user_id"
                                                    class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                                    style="background-color:#f8f9fa; font-size: 15px;">
                                                <option value="">Seleccione un Responsable</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="is_active"
                                                   class="form-label fw-semibold text-secondary mb-1">
                                                <i class="fas fa-toggle-on me-1 text-muted"></i> Estado
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select id="is_active" name="is_active"
                                                    class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                                    style="background-color:#f8f9fa; font-size: 15px;">
                                                <option value="1" selected>Activo</option>
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

@stop

@push('css')
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasBranch   = @json(session()->has('branch_id'));
            const branchCount = @json($branches->count());

            // Si no hay sucursal en sesión, obligamos a elegir o crear una
            if (!hasBranch) {
                if (branchCount === 0) {
                    // No hay sucursales registradas -> abrir directamente modal de creación
                    $('#branchCreateModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    }).modal('show');
                } else {
                    // Ya hay sucursales -> abrir selector
                    $('#selectBranchModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    }).modal('show');
                }
            }

            // Botón "Aceptar" (guardar sucursal elegida)
            $('#btnSaveBranch').on('click', function () {
                const $form    = $('#branchSelectForm');
                const branchId = $('#branch_id').val();

                $('#branch-error').addClass('d-none').text('');

                if (!branchId) {
                    $('#branch-error')
                        .removeClass('d-none')
                        .text('Debes seleccionar una sucursal.');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.select-branch') }}",
                    type: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        $('#branch-error')
                            .removeClass('d-none')
                            .text('Ocurrió un error al guardar la sucursal.');
                    }
                });
            });

            // Botón "Registrar sucursal" desde el selector
            $('#btnOpenCreateBranch').on('click', function () {
                $('#selectBranchModal').modal('hide');
                $('#branchCreateModal').modal('show');
            });

            // Enviar formulario de creación de sucursal (AJAX)
            $('#branchCreateForm').on('submit', function (e) {
                e.preventDefault();

                const $form      = $(this);
                const $btnSubmit = $form.find('button[type="submit"]');
                const $errorBox  = $('#error-messages');

                $errorBox.addClass('d-none').empty();
                $btnSubmit.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: "{{ route('admin.branches.store') }}",
                    type: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $btnSubmit.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Guardar');

                        // Si el controlador devuelve la sucursal creada, la añadimos al select
                        if (res.branch) {
                            const b = res.branch;
                            $('#branch_id').append(
                                new Option(b.name, b.id, true, true)
                            );
                        }

                        // Cerramos modal de creación y abrimos el de selección
                        $('#branchCreateModal').modal('hide');

                        $('#selectBranchModal').modal({
                            backdrop: 'static',
                            keyboard: false
                        }).modal('show');
                    },
                    error: function (xhr) {
                        $btnSubmit.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Guardar');

                        console.error(xhr);
                        let html = 'Ocurrió un error al registrar la sucursal.';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            const list = Object.values(errors)
                                .flat()
                                .map(e => `<li>${e}</li>`)
                                .join('');
                            html = `<ul class="mb-0">${list}</ul>`;
                        }

                        $errorBox
                            .removeClass('d-none')
                            .html(html);
                    }
                });
            });
        });
    </script>
@endpush
