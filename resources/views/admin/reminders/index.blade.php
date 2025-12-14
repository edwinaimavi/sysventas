{{-- resources/views/admin/reminders/index.blade.php --}}
@extends('layouts.app')

@section('subtitle', 'Recordatorios')

@section('header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-bell"></i> Recordatorios
                    {{-- Botón para crear nuevo recordatorio --}}
                    <button class="btn btn-app bg-dark" type="button" data-toggle="modal" data-target="#reminderModal">
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
                            <i class="fas fa-bell"></i> Recordatorios
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
                <table id="tableReminders"
                    class="tableStiles table table-hover align-middle mb-0 text-center shadow-sm rounded-lg">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>TÍTULO</th>
                            <th>CLIENTE</th>
                            <th>PRÉSTAMO</th>
                            <th>FECHA RECORDATORIO</th> {{-- remind_at --}}
                            <th>PRIORIDAD</th> {{-- priority --}}
                            <th>TIPO</th> {{-- type: manual / payment_due / ... --}}
                            <th>ESTADO</th> {{-- status: pending / triggered / cancelled --}}
                            <th>CANAL</th> {{-- channel: system / email / whatsapp --}}
                            <th></th> {{-- acciones --}}
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>

    {{-- Modals --}}
    {{-- Modal para crear / editar recordatorio --}}
    @include('admin.reminders.partials.modal')

    {{-- Modal para ver detalle de recordatorio --}}
    {{--  @include('admin.reminders.partials.view') --}}
@stop

@push('js')
    <script>
        window.routes = {
            reminderList: "{{ route('admin.reminders.list') }}",
            storeReminder: "{{ route('admin.reminders.store') }}",


            clientsList: "{{ route('admin.reminders.clients') }}",
            clientLoans: "{{ route('admin.reminders.client-loans', ['client' => '__ID__']) }}",

        }
    </script>

    {{-- JS específico de la página de recordatorios --}}
    @vite(['resources/js/pages/reminder.js'])
@endpush
