@extends('adminlte::page')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('title', 'Reportes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">📊 Reportes</h1>
            <small class="text-muted">Comercial · Operaciones · Cuadre de Caja</small>
        </div>
    </div>
@stop

@section('content')

    {{-- =======================
        FILTROS GENERALES
    ======================== --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="reportFilters" class="row">

                <div class="col-md-3">
                    <label class="small font-weight-bold text-secondary">Desde</label>
                    <input type="date" class="form-control form-control-sm" id="date_from" name="date_from">
                </div>

                <div class="col-md-3">
                    <label class="small font-weight-bold text-secondary">Hasta</label>
                    <input type="date" class="form-control form-control-sm" id="date_to" name="date_to">
                </div>

                <div class="col-md-3">
                    <label class="small font-weight-bold text-secondary">Sucursal</label>
                    <select class="form-control form-control-sm" id="branch_id" name="branch_id">
                        <option value="">Todas</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="small font-weight-bold text-secondary">Cliente</label>
                    <select class="form-control form-control-sm" id="client_id" name="client_id">
                        <option value="">Todos</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 mt-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters">
                        <i class="fas fa-filter mr-1"></i> Aplicar filtros
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- =======================
        TABS DE REPORTES
    ======================== --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="reportTabs" role="tablist">

                {{-- 1. REPORTE COMERCIAL --}}
                <li class="nav-item">
                    <a class="nav-link active" id="tab-commercial" data-toggle="tab" href="#panel-loans" role="tab">
                        <i class="fas fa-chart-pie mr-1"></i> Reporte Comercial
                    </a>
                </li>

                {{-- 2. REPORTE DE OPERACIONES --}}
                <li class="nav-item">
                    <a class="nav-link" id="tab-operations" data-toggle="tab" href="#panel-payments" role="tab">
                        <i class="fas fa-cash-register mr-1"></i> Reporte de Operaciones
                    </a>
                </li>

                {{-- 3. CUADRE DE CAJA --}}
                <li class="nav-item">
                    <a class="nav-link" id="tab-cash" data-toggle="tab" href="#panel-recovery" role="tab">
                        <i class="fas fa-balance-scale mr-1"></i> Cuadre de Caja
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tab-cashbook" data-toggle="tab" href="#panel-book" role="tab">
                        <i class="fas fa-book mr-1"></i> Libro de Caja
                    </a>
                </li>

            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">



                {{-- =====================================================
                    1. REPORTE COMERCIAL (CAPITAL EN CIRCULACIÓN)
                ====================================================== --}}
                <div class="tab-pane fade show active" id="panel-loans" role="tabpanel">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small">
                            Capital colocado, recuperado y pendiente por préstamo
                        </div>

                        <div class="btn-group">
                            <a href="#" class="btn btn-outline-danger btn-sm" id="btnLoansPdf">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm" id="btnLoansExcel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Capital Revolvente</strong><br>
                            Capital: <span id="com_rev_capital">S/ 0.00</span><br>
                            Interés: <span id="com_rev_interest">S/ 0.00</span>
                        </div>

                        <div class="col-md-4">
                            <strong>Capital en Cuotas</strong><br>
                            Capital: <span id="com_cuo_capital">S/ 0.00</span><br>
                            Interés: <span id="com_cuo_interest">S/ 0.00</span>
                        </div>

                        <div class="col-md-4">
                            <strong>Capital Vencido</strong><br>
                            Capital: <span id="com_ven_capital">S/ 0.00</span><br>
                            Interés: <span id="com_ven_interest">S/ 0.00</span>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm w-100" id="tableReportLoans">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Total a pagar</th>
                                    <th>Recuperado</th>
                                    <th>Capital</th>
                                    <th>Interés</th>
                                    <th># Cuotas</th>
                                    <th>Pendiente</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>

                {{-- =====================================================
                    2. REPORTE DE OPERACIONES (PAGOS)
                ====================================================== --}}

                {{-- PANEL: OPERACIONES --}}
                <div class="tab-pane fade" id="panel-payments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div class="text-muted small">
                            Ingresos y salidas generados por pagos y desembolsos
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnOperationsRefresh">
                                <i class="fas fa-sync-alt mr-1"></i> Actualizar
                            </button>

                            <a href="#" class="btn btn-outline-danger btn-sm" id="btnOperationsPdf">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>

                            {{--   <a href="#" class="btn btn-outline-success btn-sm" id="btnOperationsExcel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </a> --}}
                        </div>

                    </div>

                    <div class="row">

                        {{-- INGRESOS --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>INGRESOS</strong>
                                    <button class="btn btn-xs btn-outline-success" id="btnDetailIngresos">
                                        <i class="fas fa-eye mr-1"></i> Ver detalle
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Monto cobrado</td>
                                            <td class="text-right font-weight-bold" id="op_monto_cobrado">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Capital recuperado</td>
                                            <td class="text-right font-weight-bold" id="op_capital_recuperado">S/ 0.00
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Otros Ingresos</td>
                                            <td class="text-right font-weight-bold" id="op_intereses_cobrados">S/ 0.00
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Gastos extras</td>
                                            <td class="text-right font-weight-bold" id="op_gastos_adicionales">S/ 0.00
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- SALIDAS --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-danger">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>SALIDAS</strong>
                                    <button class="btn btn-xs btn-outline-danger" id="btnDetailSalidas">
                                        <i class="fas fa-eye mr-1"></i> Ver detalle
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">

                                        <tr>
                                            <td>Capital revolvente prestado</td>
                                            <td class="text-right font-weight-bold" id="op_capital_revolvente">S/ 0.00
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Capital en cuotas prestado</td>
                                            <td class="text-right font-weight-bold" id="op_capital_cuotas">S/ 0.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- RESUMEN --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4 id="op_total_ingresos">S/ 0.00</h4>
                                    <p>Total Ingresos</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4 id="op_total_salidas">S/ 0.00</h4>
                                    <p>Total Salidas</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4 id="op_saldo_caja">S/ 0.00</h4>
                                    <p>Saldo en Caja</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- =====================================================
    3. CUADRE DE CAJA (RESUMEN CONTABLE)
====================================================== --}}
                <div class="tab-pane fade" id="panel-recovery" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div class="text-muted small">
                            Resumen contable de caja por rango de fechas
                        </div>

                        <div class="btn-group">
                            <a href="#" class="btn btn-outline-danger btn-sm" id="btnCashPdf">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                        </div>

                    </div>

                    <div class="row">

                        {{-- =====================
            INGRESOS
        ====================== --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <strong>INGRESOS</strong>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">

                                        <tr>
                                            <td>
                                                Monto de apertura
                                                <button class="btn btn-xs btn-link p-0 ml-1 text-success"
                                                    id="btnDetailApertura">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </td>

                                            <td class="text-right font-weight-bold" id="cash_apertura">
                                                S/ 0.00
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>Reposición de caja</td>
                                            <td class="text-right font-weight-bold" id="cash_reposicion">
                                                S/ 0.00
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>Monto cobrado (pago cliente)</td>
                                            <td class="text-right font-weight-bold" id="cash_cobrado">
                                                S/ 0.00
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>Gastos extras cobrados</td>
                                            <td class="text-right font-weight-bold" id="cash_gastos_extras">
                                                S/ 0.00
                                            </td>
                                        </tr>


                                        <tr class="table-success">
                                            <td><strong>Total ingresos</strong></td>
                                            <td class="text-right font-weight-bold" id="cash_total_ingresos">
                                                S/ 0.00
                                            </td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- =====================
            SALIDAS
        ====================== --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-danger">
                                <div class="card-header">
                                    <strong>SALIDAS</strong>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">

                                        <tr class="table-info">
                                            <td>Capital revolvente prestado en el mes</td>
                                            <td class="text-right font-weight-bold" id="cash_capital_revolvente">
                                                S/ 0.00
                                            </td>
                                        </tr>
                                        <tr class="table-info">
                                            <td>Capital en cuotas prestado en el mes</td>
                                            <td class="text-right font-weight-bold" id="cash_capital_cuotas">
                                                S/ 0.00
                                            </td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td>Otras salidas</td>
                                            <td class="text-right font-weight-bold" id="cash_otras_salidas">
                                                S/ 0.00
                                            </td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td>
                                                <strong>Total salidas</strong>
                                                <button class="btn btn-xs btn-link p-0 ml-1 text-danger"
                                                    id="btnDetailCashOut">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </td>

                                            <td class="text-right font-weight-bold" id="cash_total_salidas">
                                                S/ 0.00
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- =====================
        SALDO FINAL
    ====================== --}}
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-success d-flex justify-content-between align-items-center">
                                <strong>SALDO EN CAJA</strong>
                                <h4 class="mb-0" id="cash_saldo_final">S/ 0.00</h4>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small">
                        Cuadre de caja efectivo según ingresos y salidas del rango seleccionado
                    </div>

                </div>

                {{-- =====================================================
                    3. CUADRE DE CAJA (RESUMEN)
                ====================================================== --}}







                <div class="tab-pane fade" id="panel-book" role="tabpanel">


                    <div class="table-responsive">

                        <table id="tableCashBook" class="table table-striped table-bordered w-100">
                            <thead>

                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Ingreso</th>
                                    <th>Salida</th>
                                    <th>Saldo</th>
                                </tr>

                            </thead>
                        </table>



                    </div>

                    {{-- RESUMEN --}}


                </div>




            </div>
        </div>
    </div>

@stop

@push('js')
    <script>
        window.routes = window.routes || {};

        // REPORTES - COMERCIAL / PRÉSTAMOS
        window.routes.reportsLoans = "{{ route('admin.reports.loans') }}";
        window.routes.loansPdf = "{{ route('admin.reports.loans.pdf') }}";
        window.routes.commercialPdf = "{{ route('admin.reports.commercial.pdf') }}";
        window.routes.loansExcel = "{{ route('admin.reports.loans.excel') }}";

        // REPORTES - PAGOS / OPERACIONES
        window.routes.reportsPayments = "{{ route('admin.reports.payments') }}";
        window.routes.reportsRecovery = "{{ route('admin.reports.recovery') }}";
        window.routes.reportsOperations = "{{ route('admin.reports.operations') }}";
        window.routes.reportsOperationsPdf = "{{ route('admin.reports.operations.pdf') }}";

        window.routes.reportsCommercial = "{{ route('admin.reports.commercial') }}";
        window.routes.reportsCashPdf = "{{ route('admin.reports.cash.pdf') }}";
        window.routes.reportsDetails = "{{ route('admin.reports.details') }}";

        /* NUEVO */
        window.routes.reportsCashbook = "{{ route('admin.reports.cashbook') }}";

        // CUADRE / RECUPERACIÓN
    </script>

    @vite(['resources/js/pages/report.js'])
@endpush


{{-- ===============================
MODAL DETALLE DE CAJA / OPERACIONES
================================ --}}
<div class="modal fade" id="modalReportDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalReportDetailTitle">
                    Detalle de movimientos
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                {{-- CONTEXTO --}}
                <div class="mb-2 text-muted small" id="modalReportDetailContext">
                    Rango seleccionado, sucursal y cliente
                </div>

                {{-- TABLA --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100" id="tableReportDetail">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Cliente</th>
                                <th>Préstamo</th>
                                <th class="text-right">Monto</th>
                                <th class="text-right">Capital</th>
                                <th class="text-right">Interés</th>
                                <th class="text-right">Gastos</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- AJAX --}}
                        </tbody>

                        <!-- ✅ FOOTER PARA TOTALES -->
                        <tfoot>
                            <tr class="table-success font-weight-bold">
                                <th colspan="6" class="text-right">TOTAL:</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
