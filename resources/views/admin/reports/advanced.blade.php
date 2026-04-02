@extends('layouts.app')

@section('subtitle', 'Resumen General')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-chart-bar text-primary"></i> Resumen General
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
                        <li class="breadcrumb-item active">
                            Reporte Avanzado
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@stop


@section('content_body')
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">

        <li class="nav-item">
            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                <i class="fas fa-chart-bar"></i> Resumen General
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab">
                <i class="fas fa-money-bill-wave"></i> Resumen de Pagos
            </a>
        </li>

    </ul>

    <div class="tab-content">

        {{-- =========================
FILTROS
--}}
        <div class="tab-pane fade show active" id="general" role="tabpanel">

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form id="formAdvancedFilters" class="row">

                        <div class="col-md-3">
                            <label class="small font-weight-bold text-secondary">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="adv_date_from">
                        </div>

                        <div class="col-md-3">
                            <label class="small font-weight-bold text-secondary">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="adv_date_to">
                        </div>

                        <div class="col-md-3">
                            <label class="small font-weight-bold text-secondary">Sucursal</label>
                            <select class="form-control form-control-sm" id="adv_branch">
                                <option value="">Todas</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="btnAdvancedFilter">
                                <i class="fas fa-filter mr-1"></i> Aplicar filtros
                            </button>
                        </div>

                    </form>
                </div>
            </div>


            {{-- =========================
KPIs
========================= --}}
            <div class="row mb-3">

                <div class="col-md-3">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h4 id="adv_total_loans">S/ 0.00</h4>
                            <p>Total Colocado</p>
                        </div>
                        <div class="icon"><i class="fas fa-coins"></i></div>
                        <button class="btn-kpi-detail" data-type="loans">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h4 id="adv_total_paid">S/ 0.00</h4>
                            <p>Total Recuperado</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>

                        <button class="btn-kpi-detail" data-type="payments">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h4 id="adv_total_pending">S/ 0.00</h4>
                            <p>Saldo Pendiente</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <button class="btn-kpi-detail" data-type="pending">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-danger shadow-sm">
                        <div class="inner">
                            <h4 id="adv_total_overdue">S/ 0.00</h4>
                            <p>Capital Vencido</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <button class="btn-kpi-detail" data-type="pending">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

            </div>


            {{-- =========================
TABLA PRINCIPAL
========================= --}}
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">

                    <strong>
                        <i class="fas fa-table mr-1"></i>
                        Detalle de Préstamos y Pagos
                    </strong>

                    <div class="btn-group">
                        <button class="btn btn-outline-danger btn-sm" id="btnAdvancedPdf">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </button>

                        <button class="btn btn-outline-success btn-sm" id="btnAdvancedExcel">
                            <i class="fas fa-file-excel mr-1"></i> Excel
                        </button>
                    </div>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tableAdvancedReport"
                            class="table table-hover table-bordered table-sm text-center w-100">

                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Fecha V.</th>
                                    <th>Cliente</th>
                                    <th>Préstamo</th>

                                    <th>Monto</th>
                                    <th>Pagado</th>
                                    <th>Capital</th>
                                    <th>Interés</th>
                                    <th>O.Ingresos</th>
                                    <th>Saldo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tfoot>
                                <tr>
                                    <th colspan="5">TOTAL</th>
                                    <th id="total_amount"></th>
                                    <th id="total_paid"></th>
                                    <th id="total_capital"></th>
                                    <th id="total_interest"></th>
                                    <th id="total_expenses"></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="payments" role="tabpanel">

            <div class="card shadow-sm border-0 mt-3">

                <div class="card-header">
                    <strong>
                        <i class="fas fa-money-check-alt"></i>
                        Detalle de Pagos
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row mb-3">

                        <div class="col-md-4">
                            <label class="small font-weight-bold">Sucursal</label>
                            <select id="pay_branch" class="form-control form-control-sm">
                                <option value="">Todas</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="small font-weight-bold">Método de Pago</label>
                            <select id="pay_method" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="cash">Efectivo</option>
                                <option value="yape">Yape</option>
                                <option value="plin">Plin</option>
                                <option value="bank_transfer">Transferencia</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm w-100" id="btnFilterPayments">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>

                    </div>

                    <div class="table-responsive">

                        <table id="tablePaymentsOnly" class="table table-bordered table-sm text-center w-100">

                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Préstamo</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="font-weight-bold bg-light">
                                    <th colspan="5" class="text-right">TOTAL:</th>
                                    <th id="total_payments_amount">S/ 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                </div>

            </div>

        </div>


    </div>

@stop


@push('css')
    <style>
        .small-box {
            border-radius: 12px;
        }

        .card {
            border-radius: 12px;
        }

        .table thead th {
            font-size: 12px;
        }

        .btn-kpi-detail {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            padding: 6px 8px;
            cursor: pointer;
        }

        .small-box {
            position: relative;
        }

        foot {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
@endpush


@push('js')
    <script>
        window.routes = {
            advancedData: "{{ route('admin.reports.advanced.data') }}",
            advancedKpis: "{{ route('admin.reports.advanced.kpis') }}"
        };



        // 🔥 CLICK EN LUPA
        $(document).on('click', '.btn-view-loan', function() {

            let id = $(this).data('id');

            // limpiar tablas
            $('#tableSchedules tbody').html('');
            $('#tablePayments tbody').html('');

            $.get(`/admin/reports/advanced/${id}`, function(res) {

                // 🔹 RESUMEN
                $('#md_code').text(res.loan.loan_code);
                $('#md_client').text(res.loan.client);
                $('#md_amount').text('S/ ' + parseFloat(res.loan.amount).toFixed(2));
                $('#md_total').text('S/ ' + parseFloat(res.loan.total_payable).toFixed(2));

                // 🔹 CRONOGRAMA
                res.schedules.forEach(s => {

                    let cuota = parseFloat(s.payment);
                    let pagado = parseFloat(s.paid_amount || 0);
                    let restante = cuota - pagado;

                    let estado = '';
                    let color = '';
                    let textoExtra = '';

                    if (s.status === 'paid') {
                        estado = 'Pagado';
                        color = 'success';
                    } else if (s.status === 'partial') {
                        estado = 'Parcial';
                        color = 'warning';
                        textoExtra =
                            `<br><small class="text-danger fw-bold">Falta: S/ ${restante.toFixed(2)}</small>`;
                    } else {
                        estado = 'Pendiente';
                        color = 'secondary';
                    }

                    $('#tableSchedules tbody').append(`
        <tr>
            <td>${s.installment_no}</td>
            <td>${s.due_date}</td>
            <td>S/ ${parseFloat(s.amortization || 0).toFixed(2)}</td>
            <td>S/ ${parseFloat(s.interest).toFixed(2)}</td>
            <td>S/ ${cuota.toFixed(2)}</td>
            <td>
                <span class="badge badge-${color}">
                    ${estado}
                </span>
                ${textoExtra}
            </td>
        </tr>
    `);
                });

                // 🔹 PAGOS
                res.payments.forEach(p => {
                    $('#tablePayments tbody').append(`
                <tr>
                    <td>${p.payment_date}</td>
                    <td>S/ ${parseFloat(p.amount).toFixed(2)}</td>
                    <td>S/ ${parseFloat(p.capital).toFixed(2)}</td>
                    <td>S/ ${parseFloat(p.interest).toFixed(2)}</td>
                    <td>${traducirMetodo(p.method)}</td>
                </tr>
            `);
                });

                // 🔥 ABRIR MODAL
                $('#modalLoanDetail').modal('show');

            });

        });

        let tablePaymentsOnly;

        $('a[data-toggle="tab"][href="#payments"]').on('shown.bs.tab', function() {

            if (tablePaymentsOnly) return;

            tablePaymentsOnly = $('#tablePaymentsOnly').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('admin.reports.advanced.payments') }}",
                    data: function(d) {
                        d.date_from = $('#adv_date_from').val();
                        d.date_to = $('#adv_date_to').val();
                        d.branch_id = $('#pay_branch').val();
                        d.payment_method = $('#pay_method').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'payment_code'
                    },
                    {
                        data: 'loan_code'
                    },
                    {
                        data: 'client_name'
                    },
                    {
                        data: 'payment_date'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'method'
                    },
                ],
                language: {
                    url: "/vendor/datatables/js/i18n/es-ES.json"
                },
                drawCallback: function() {

                    let api = this.api();

                    let total = 0;

                    function parseMoney(val) {
                        return parseFloat(val.replace('S/ ', '').replace(',', '')) || 0;
                    }

                    api.rows().every(function() {
                        let d = this.data();
                        total += parseMoney(d.amount);
                    });

                    $('#total_payments_amount').html('S/ ' + total.toFixed(2));
                }
            });



        });


        $('#btnFilterPayments').on('click', function() {
            if (tablePaymentsOnly) {
                tablePaymentsOnly.ajax.reload();
            }
        });

        function traducirMetodo(method) {
            switch (method) {
                case 'cash':
                    return 'Efectivo';
                case 'transfer':
                    return 'Transferencia';
                case 'card':
                    return 'Tarjeta';
                default:
                    return method || '-';
            }
        }
    </script>




    @vite(['resources/js/pages/adanced-report.js'])

    {{-- luego conectas tu JS --}}
    {{-- @vite(['resources/js/pages/advanced-report.js']) --}}
@endpush



<div class="modal fade" id="modalLoanDetail" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Detalle del Préstamo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- 🔹 RESUMEN -->
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Código:</strong> <span id="md_code"></span></div>
                    <div class="col-md-3"><strong>Cliente:</strong> <span id="md_client"></span></div>
                    <div class="col-md-3"><strong>Monto:</strong> <span id="md_amount"></span></div>
                    <div class="col-md-3"><strong>Total:</strong> <span id="md_total"></span></div>
                </div>

                <!-- 🔹 CRONOGRAMA -->
                <h6 class="text-primary">Cronograma</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered" id="tableSchedules">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Capital</th>
                                <th>Interés</th>
                                <th>Pago</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- 🔹 PAGOS -->
                <h6 class="text-success">Pagos Realizados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="tablePayments">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Capital</th>
                                <th>Interés</th>
                                <th>Método</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
