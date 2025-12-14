@extends('layouts.app')

@section('subtitle', 'Préstamos')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-hand-holding-usd"></i> Préstamos
                    {{-- @can('admin.loans.store') --}}
                    <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#loanModal">
                        <i class="fas fa-plus-circle"></i> Nuevo
                    </button>
                    {{-- @endcan --}}
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

@stop

@push('css')
    {{-- CSS extra si lo necesitas --}}
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
            
        }
    </script>

    @vite(['resources/js/pages/loan.js'])
@endpush
