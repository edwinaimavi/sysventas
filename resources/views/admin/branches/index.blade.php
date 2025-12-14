@extends('layouts.app')

@section('subtitle', 'Usuarios')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-code-branch"></i> Sucursales
                @can('admin.users.store')
                <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#branchModal">
                    <i class="fas fa-plus-circle"></i>Nuevo
                </button>
                @endcan
                </h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-fw fa-house-user"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-code-branch"></i> Sucursales</li>
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
          <table id="tableBranch" class=" tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
              <thead style="background-color: #f5f5f5; color: #444;">
                  <tr>
                      <th style="width: 5%;">#</th>
                      <th>ID</th>
                      <th>Sucursal</th>
                      <th>Dirección</th>
                      <th>Teléfono</th>
                      <th>Email</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody></tbody>
          </table>
      </div>
  </div>
</div>


{{-- Modal --}}
 @include('admin.branches.partials.modal') 

   
@stop

@push('css')
    
@endpush

@push('js')
    <script>
        window.routes= {
            storeBranch:"{{ route('admin.branches.store') }}",
            branchesList:"{{ route('admin.branches.list') }}",
            deleteBranch:"{{ url('admin/branches') }}"
        }

       
    </script>
    @vite(['resources/js/pages/branch.js']) 
@endpush
