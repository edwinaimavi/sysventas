@extends('layouts.app')

@section('subtitle', 'home')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-chart-pie mr-2 text-secondary"></i> Dashboard
                </h1>
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
                            Dashboard
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

        // Helpers UI
        $statusLabel = [
            'pending' => ['Pendiente', 'warning'],
            'approved' => ['Aprobado', 'info'],
            'disbursed' => ['Desembolsado', 'primary'],
            'finished' => ['Finalizado', 'success'],
            'cancelled' => ['Cancelado', 'danger'],
        ];
    @endphp

    {{-- ===================== MODAL: Seleccionar sucursal ===================== --}}
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

    {{-- ===================== MODAL: Crear sucursal ===================== --}}
    <div class="modal fade" id="branchCreateModal" tabindex="-1" aria-labelledby="branchCreateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header"
                    style="background: linear-gradient(135deg, #f5f5f5, #e8e8e8); border-bottom: 1px solid #d9d9d9;">
                    <h5 class="modal-title fw-semibold text-dark" id="branchCreateModalLabel">
                        <i class="fas fa-plus"></i> Nueva Sucursal
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"
                        style="filter: invert(0.5);"></button>
                </div>

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

                                <div class="row g-3">
                                    <div class="col-sm-9">
                                        <label class="form-label small fw-semibold">
                                            NOMBRE DE LA SUCURSAL <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm shadow-sm" id="name"
                                            name="name" placeholder="Nombre Sucursal" required>
                                    </div>
                                    <div class="col-sm-3">
                                        <label class="form-label small fw-semibold">CODIGO</label>
                                        <input type="text" class="form-control form-control-sm shadow-sm" id="code"
                                            name="code" placeholder="Codigo Sucursal">
                                    </div>
                                    <div class="col-sm-12">
                                        <label class="form-label small fw-semibold">
                                            DIRECCION <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm shadow-sm" id="address"
                                            name="address" placeholder="Dirección de la Sucursal" required>
                                    </div>
                                </div>

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
                                            <label for="is_active" class="form-label fw-semibold text-secondary mb-1">
                                                <i class="fas fa-toggle-on mr-1 text-muted"></i> Estado <span
                                                    class="text-danger">*</span>
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

                                <div class="text-right mt-4">
                                    <button type="button" class="btn btn-light border mr-2" data-dismiss="modal">
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

    {{-- ===================== DASHBOARD ===================== --}}
    <div class="container-fluid">

        @if (!$kpis)
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle mr-1"></i>
                Selecciona una sucursal para ver el dashboard.
            </div>
        @else
            {{-- KPIs (estilo elegante con info-box) --}}
            <div class="row">

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Clientes</span>
                            <span class="info-box-number">{{ $kpis['totalClients'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Préstamos</span>
                            <span class="info-box-number">{{ $kpis['totalLoans'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Desembolsado</span>
                            <span class="info-box-number">S/ {{ number_format($kpis['sumDisbursed'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cobrado</span>
                            <span class="info-box-number">S/ {{ number_format($kpis['sumPaid'], 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Gráfico + Estados --}}
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i> Préstamos (últimos 6 meses)
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartLoans" height="120"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-outline card-dark shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group mr-1"></i> Estados de préstamos
                            </h3>
                        </div>
                        <div class="card-body">
                            @php $s = $kpis['loansByStatus']; @endphp

                            @foreach (['pending', 'approved', 'disbursed', 'finished', 'cancelled'] as $st)
                                @php
                                    $lbl = $statusLabel[$st][0];
                                    $clr = $statusLabel[$st][1];
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ $lbl }}</span>
                                    <span class="badge badge-{{ $clr }} px-3 py-2">{{ $s[$st] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tablas --}}
            <div class="row">

                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i> Últimos préstamos
                            </h3>
                        </div>

                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lastLoans as $l)
                                        @php
                                            $st = $l->status ?? 'pending';
                                            $badge = $statusLabel[$st][1] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold">{{ $l->loan_code ?? 'CR-' . $l->id }}</td>
                                            <td>{{ optional($l->client)->full_name ?? '—' }}</td>
                                            <td>S/ {{ number_format($l->amount ?? 0, 2) }}</td>
                                            <td><span
                                                    class="badge badge-{{ $badge }}">{{ $statusLabel[$st][0] ?? $st }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Sin registros</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i> Próximos pagos
                            </h3>
                        </div>

                        <div class="card-body table-responsive p-0">
                            @if ($nextPayments->isEmpty())
                                <div class="p-3 text-muted">No hay pagos pendientes.</div>
                            @else
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Préstamo</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($nextPayments as $p)
                                            <tr>
                                                <td class="font-weight-bold">{{ optional($p->loan)->loan_code ?? '—' }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') }}</td>
                                                <td>S/ {{ number_format($p->amount ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

        @endif
    </div>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ============================
            // HELPERS
            // ============================
            function resetBranchCreateModal() {
                const $form = $('#branchCreateForm');

                // reset form
                $form[0].reset();

                // limpiar errores
                $('#error-messages').addClass('d-none').html('');

                // reset botón
                $form.find('button[type="submit"]')
                    .prop('disabled', false)
                    .html('<i class="fas fa-save"></i> Guardar');
            }

            // ============================
            // CONTROL INICIAL MODALES
            // ============================
            const hasBranch = @json(session()->has('branch_id'));
            const branchCount = @json($branches->count());

            if (!hasBranch) {
                if (branchCount === 0) {
                    $('#branchCreateModal')
                        .modal({
                            backdrop: 'static',
                            keyboard: false
                        })
                        .modal('show');
                } else {
                    $('#selectBranchModal')
                        .modal({
                            backdrop: 'static',
                            keyboard: false
                        })
                        .modal('show');
                }
            }

            // ============================
            // SELECCIONAR SUCURSAL
            // ============================
            $('#btnSaveBranch').on('click', function() {
                const branchId = $('#branch_id').val();
                $('#branch-error').addClass('d-none').text('');

                if (!branchId) {
                    $('#branch-error').removeClass('d-none')
                        .text('Debes seleccionar una sucursal.');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.select-branch') }}",
                    type: 'POST',
                    data: $('#branchSelectForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        window.location.reload();
                    },
                    error: function() {
                        $('#branch-error')
                            .removeClass('d-none')
                            .text('Ocurrió un error al guardar la sucursal.');
                    }
                });
            });

            // ============================
            // ABRIR MODAL CREAR SUCURSAL
            // ============================
            $('#btnOpenCreateBranch').on('click', function() {
                $('#selectBranchModal').modal('hide');
                resetBranchCreateModal();
                $('#branchCreateModal').modal('show');
            });

            // ============================
            // CREAR SUCURSAL (AJAX)
            // ============================
            $('#branchCreateForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                const $err = $('#error-messages');

                // seguridad anti doble click
                if ($btn.prop('disabled')) return;

                $err.addClass('d-none').html('');
                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: "{{ route('admin.branches.store') }}",
                    type: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(res) {
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Guardar');

                        if (res.branch) {
                            $('#branch_id').append(
                                new Option(res.branch.name, res.branch.id, true, true)
                            );
                        }

                        $('#branchCreateModal').modal('hide');
                        $('#selectBranchModal')
                            .modal({
                                backdrop: 'static',
                                keyboard: false
                            })
                            .modal('show');
                    },

                    error: function(xhr) {
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Guardar');

                        let html = 'Ocurrió un error al registrar la sucursal.';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const list = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .map(e => `<li>${e}</li>`)
                                .join('');
                            html = `<ul class="mb-0">${list}</ul>`;
                        }

                        $err.removeClass('d-none').html(html);
                    }
                });
            });

            // ============================
            // LIMPIAR MODAL AL CERRAR
            // ============================
            $('#branchCreateModal').on('hidden.bs.modal', function() {
                resetBranchCreateModal();
            });

            // ============================
            // CHART
            // ============================
            @if ($kpis)
                const dataLoans = @json($chartLoans);
                const labels = dataLoans.map(x => x.label);
                const values = dataLoans.map(x => x.total);

                const el = document.getElementById('chartLoans');
                if (el) {
                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Préstamos',
                                data: values,
                                tension: 0.35,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            @endif

        });
    </script>
@endpush
