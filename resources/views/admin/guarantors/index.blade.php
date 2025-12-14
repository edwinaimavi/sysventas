@extends('layouts.app')

@section('subtitle', 'Garantes')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-user-shield"></i> Garantes
                    {{-- @can('admin.guarantors.store') --}}
                    <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#guarantorModal">
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
                            <i class="fas fa-user-shield"></i> Garantes
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
                <table id="tableGuarantor" class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
                    <thead class="background-color: #f5f5f5; color: #444;">
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>NRO DOC</th>
                            <th>TIPO DOC</th>
                            <th>TELÉFONO</th>
                            <th>EMAIL</th>
                            <th>STATUS</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    {{-- Modals de garantes (los crearás igual que en clientes) --}}
    @include('admin.guarantors.partials.modal')       {{-- Registrar / editar garante --}}
    {{-- @include('admin.guarantors.partials.viewModal') --}}   {{-- Ver garante --}}
@stop

@push('css')
    {{-- CSS extra si lo necesitas --}}
@endpush

@push('js')
    <script>
        window.routes = {
            guarantorList: "{{ route('admin.guarantors.list') }}",     // GET lista para DataTables
            storeGuarantor: "{{ route('admin.guarantors.store') }}",   // POST crear garante
            deleteGuarantor: "{{ url('admin/guarantors') }}"           // DELETE /admin/guarantors/{id}
        }
    </script>

    @vite(['resources/js/pages/guarantor.js'])
@endpush
