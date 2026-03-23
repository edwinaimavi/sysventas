@extends('layouts.app')

@section('subtitle', 'Caja')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-cash-register"></i> Caja
                    {{-- Botón Aperturar Caja --}}
                    <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#cashOpenModal">
                        <i class="fas fa-door-open"></i> Aperturar
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
                            <i class="fas fa-cash-register"></i> Caja
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
                <table id="tableCash"
                    class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
                    <thead style="background-color: #f5f5f5; color: #444;">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>SUCURSAL</th>
                            <th>FECHA APERTURA</th>
                            <th>SALDO INICIAL</th>
                            <th>INGRESOS</th>
                            <th>EGRESOS</th>
                            <th>SALDO FINAL</th>
                            <th>ESTADO</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    {{-- Modals de Caja --}}
    @include('admin.cash-box.partials.open_modal') {{-- Apertura de caja --}}
    @include('admin.cash-box.partials.close_modal') {{-- Apertura de caja --}}
    @include('admin.cash-box.partials.cash_replenish') {{-- Apertura de caja --}}
    {{--  @include('admin.cash.partials.close_modal') --}} {{-- Cierre de caja --}}
    {{-- @include('admin.cash.partials.movements_modal') --}} {{-- Movimientos --}}
@stop
@push('js')
    <script>
        window.routes = {
            cashList: "{{ route('admin.cash-box.list') }}", // GET DataTables
            openCash: "{{ route('admin.cash-box.store') }}", // POST apertura
            cashSummary: "{{ route('admin.cash-box.summary', ':id') }}",
            cashReplenish: "{{ route('admin.cash-box.replenish') }}"

        }
    </script>

    @vite(['resources/js/pages/cash-box.js'])
@endpush
