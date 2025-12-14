@extends('layouts.app')

@section('subtitle', 'Pagos de Préstamos')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-cash-register"></i> Pagos
                    <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#paymentModal">
                        <i class="fas fa-plus-circle"></i> Nuevo
                    </button>
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
                            <i class="fas fa-cash-register"></i> Pagos
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
                <table id="tableLoanPayments"
                    class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>CÓDIGO</th>
                            <th>PRÉSTAMO</th>
                            <th>CLIENTE</th>
                            <th>FECHA</th>
                            <th>MONTO</th>
                            <th>MÉTODO</th>
                            <th>TIPO</th> {{-- ⭐ nueva columna --}}
                            <th>ESTADO</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    {{-- Modals --}}
    @include('admin.loan-payments.partials.modal') {{-- Registrar / editar pago --}}

    @include('admin.loan-payments.partials.view') {{-- Ver pago --}}
@stop

@push('js')
    <script>
        window.routes = {

            storePayment: "{{ route('admin.loan-payments.store') }}",
            paymentList: "{{ route('admin.loan-payments.list') }}",
            generatePaymentCode: "{{ route('admin.loan-payments.generate-code') }}",

        }
    </script>

    @vite(['resources/js/pages/loan-payment.js'])
@endpush
