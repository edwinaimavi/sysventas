@extends('layouts.app')

@section('subtitle', 'Préstamos')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-hand-holding-usd"></i> Préstamos
                    @can('admin.loans.store')
                        <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#loanModal">
                            <i class="fas fa-plus-circle"></i> Nuevo
                        </button>
                    @endcan
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
                            <i class="fas fa-hand-holding-usd"></i> Préstamos
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@stop

@section('content_body')
    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-body p-3">

            <div class="table-responsive">
                <table id="tableLoan"
                    class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
                    <thead class="background-color: #f5f5f5; color: #444;">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>CÓDIGO</th>
                            <th>CLIENTE</th>
                            <th>GARANTE</th>
                            <th>MONTO</th>
                            <th>DESEMBOLSO</th> <!-- 🔥 -->
                            <th>VENCIMIENTO</th> <!-- 🔥 -->
                            <th>PLAZO (MESES)</th>
                            <th>ESTADO</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    {{-- Modals de préstamos --}}
    @include('admin.loans.partials.modal') {{-- Registrar / editar préstamo --}}
    @include('admin.loans.partials.viewModal') {{-- Ver préstamo (si luego lo creas) --}}
    @include('admin.loans.partials.disbursement_modal')
    @include('admin.loans.partials.increment_modal')
    @include('admin.loans.partials.refinance_modal')



@stop

@push('css')
    <style>
        .loan-status-wrap {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            flex-wrap: wrap;
        }

        .loan-dot {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .18rem .5rem;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 700;
            border: 1px solid rgba(0, 0, 0, .06);
            background: #fff;
            box-shadow: 0 8px 16px rgba(16, 24, 40, .06);
        }

        .loan-dot .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .03);
        }

        .loan-dot-ref {
            color: #b4232c;
            border-color: rgba(220, 53, 69, .20);
        }

        .loan-dot-ref .dot {
            background: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, .12);
        }

        .loan-dot-over {
            color: #8a5a00;
            border-color: rgba(245, 158, 11, .25);
        }

        .loan-dot-over .dot {
            background: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .14);
        }

        .loan-status-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 34px;
            /* 🔑 fuerza altura pareja */
        }

        /* placeholder invisible para mantener alineación */
        .loan-dot-placeholder {
            visibility: hidden;
            padding: .18rem .5rem;
            border-radius: 999px;
            font-size: .74rem;
        }


        /*estilos de desembolso y vencimient*/
        .loan-date-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 12px;
            border-radius: 12px;
            min-width: 130px;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .loan-date-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }

        /* LABEL */
        .loan-date-label {
            font-size: 11px;
            color: #888;
            margin-bottom: 3px;
        }

        /* VALOR */
        .loan-date-value {
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* EXTRA TEXTO */
        .loan-date-extra {
            font-size: 11px;
            margin-top: 3px;
        }

        /* COLORES */

        /* 🔵 Desembolso */
        .loan-date-disbursement {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #0d47a1;
        }

        /* 🟢 OK */
        .loan-date-due.ok {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: #1b5e20;
        }

        /* 🟡 Warning */
        .loan-date-due.warning {
            background: linear-gradient(135deg, #fff8e1, #ffe082);
            color: #e65100;
        }

        /* 🔴 Overdue */
        .loan-date-due.overdue {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #b71c1c;
            animation: pulse 1.5s infinite;
        }

        /* ⚫ FINALIZADO */
        .loan-date-due.finished {
            background: linear-gradient(135deg, #424242, #757575);
            color: #fff;
            border: 2px solid #616161;
        }

        .loan-date-due.finished .loan-date-extra {
            color: #e0e0e0;
        }

        /* 🔥 animación vencido */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(183, 28, 28, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(183, 28, 28, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(183, 28, 28, 0);
            }
        }
    </style>
@endpush

@push('js')
    <script>
        window.routes = {
            loanList: "{{ route('admin.loans.list') }}", // GET lista para DataTables
            storeLoan: "{{ route('admin.loans.store') }}", // POST crear préstamo
            deleteLoan: "{{ url('admin/loans') }}", // DELETE /admin/loans/{id}
            generateCode: "{{ route('admin.loans.generate-code') }}",
            storeDisbursement: "{{ route('admin.loan-disbursements.store') }}",
            loanDisbursementsByLoan: '{{ route('admin.loans.disbursements.byLoan', ['loan' => ':id']) }}',
            storeLoanIncrement: "{{ route('admin.loans.increments.store') }}",
            loanIncrementsByLoan: "{{ route('admin.loans.increments.byLoan', ':id') }}",
            // ✅ REFINANCIAMIENTO (RUTAS REALES)
            refinanceInfo: "{{ route('admin.loans.refinance.info', ['loan' => ':id']) }}",
            refinanceStore: "{{ route('admin.loans.refinance') }}",
            refinanceHistory: "{{ route('admin.loans.refinance.history', ['loan' => ':id']) }}",
            // resources/views/admin/loans/index.blade.php (tu window.routes)
            loanSchedulesByLoan: "{{ url('/admin/loans') }}/:id/schedules",







        }
    </script>

    @vite(['resources/js/pages/loan.js'])
@endpush
