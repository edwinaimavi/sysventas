
@extends('layouts.app')

@section('subtitle', 'Clientes')
@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-tie"></i> Clientes
                @can('admin.clientes.store')
                <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#clientModal">
                    <i class="fas fa-plus-circle"></i>Nuevo
                </button>
                @endcan
                </h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-fw fa-house-user"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-tie"></i> Clientes</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>    
@stop

@section('content_body')
<div class="card shadow-sm border-0 rounded-lg">
<div class="card-body  p-3">
  
    <div class=" table-responsive">
        <table id="tableClient" class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
            <thead class="background-color: #f5f5f5; color: #444;">
                <tr>
                    <th>#</th>
                    <th>Id</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>NRO DOC</th>
                    <th>TIPO DOC</th>
                    <th>TELEFONO</th>
                    <th>EMAIL</th>
                    <th>STATUS</th>
                    <th></th>
                  
                </tr>
            </thead>
        </table>
    </div>
</div>  
</div> 

{{-- Modal --}}
@include('admin.clients.partials.modal')
@include('admin.clients.partials.viewModal')
@include('admin.clients.partials.contacts_modal')
@include('admin.clients.partials.contact_form_modal')


   
@stop

@push('css')
    
@endpush

@push('js')
     <script>
        window.routes= {
           /*  storeUser:"{{ route('admin.users.store') }}" */
            clientList:"{{ route('admin.clients.list') }}",
            storeClient: "{{ route('admin.clients.store') }}",
            deleteClient:"{{ url('admin/clients') }}" ,
            clientContactsList: "{{ route('admin.client-contacts.list') }}",
            deleteClientContact: "{{ url('admin/client-contacts') }}", // para el DELETE /admin/client-contacts/{id}
            consultarDocumento: "{{ route('admin.clients.consultarDniRuc', ['dniruc' => 'DOC_PLACEHOLDER']) }}",
        }

        /* function previewImage(event,querySelector){
            let input = event.target;
            let imgPreview = document.querySelector(querySelector);
            if (!input.files.length) return
            let file = input.files[0];
            let objectURL = URL.createObjectURL(file);
            imgPreview.src = objectURL;
        } */
    </script>
    @vite(['resources/js/pages/client.js']) 
@endpush
