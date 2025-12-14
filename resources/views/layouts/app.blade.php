{{-- <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
 --}}

@extends('adminlte::page')

@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle')
        | @yield('subtitle')
    @endif
@stop

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;

    $user = Auth::user();
    $rutaFoto =
        $user && $user->photo
            ? Storage::url($user->photo)
            : 'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';

    $branchName = session('branch_name', 'Sucursal no seleccionada');
@endphp

{{-- ✅ NAVBAR RIGHT --}}
@section('content_top_nav_right')

    {{-- Chip Sucursal --}}
    <li class="nav-item d-none d-md-flex align-items-center mr-2">
        <span
            style="
            padding: 6px 14px;
            border-radius: 50px;
            background: rgba(0, 123, 255, 0.15);
            backdrop-filter: blur(6px);
            color: #0055a5;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
        ">
            <i class="fas fa-store-alt mr-1" style="font-size: 0.9rem;"></i>
            {{ $branchName }}
        </span>
    </li>

    {{-- ✅ CAMPANA RECORDATORIOS --}}
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" id="reminderBell" role="button">
            <i class="far fa-bell"></i>
            <span class="badge badge-danger navbar-badge d-none" id="reminderBadge">0</span>
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width:360px;">
            <span class="dropdown-item dropdown-header">
                Recordatorios
                <span class="badge badge-danger ml-1 d-none" id="reminderBadgeHeader">0</span>
            </span>

            <div class="dropdown-divider"></div>

            <div id="reminderDropdownItems">
                <span class="dropdown-item text-muted">Cargando...</span>
            </div>

            <div class="dropdown-divider"></div>

            <a href="{{ route('admin.reminders.index') }}" class="dropdown-item dropdown-footer">
                Ver todos los recordatorios
            </a>
        </div>
    </li>

    {{-- Avatar --}}
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" role="button">
            <img src="{{ $rutaFoto }}" alt="Avatar" class="img-avatar-navbar">
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item">Mi Perfil</a>
            <a href="#" class="dropdown-item">Cerrar Sesión</a>
        </div>
    </li>
@endsection

@section('content_header')
    @yield('header')
@stop

@section('content')

    {{-- Loading global --}}
    <div id="divLoading">
        <div>
            <img src="{{ asset('images/loading.svg') }}" alt="Loading..." />
        </div>
    </div>

    {{-- ✅ SOLO UNA VEZ --}}
    @yield('content_body')

    {{-- ✅ El modal SIEMPRE dentro del content --}}
    @include('admin.reminders.partials.view')

@stop

@section('footer')
    <div class="float-right">
        Version: {{ config('app.version', '1.0.0') }}
    </div>

    <strong>
        <a href="{{ config('app.company_url', '#') }}">
            {{ config('app.company_name', 'Sys Ventas (3ACP)') }}
        </a>
    </strong>
@stop

@push('js')
    <script src="{{ asset('vendor/sweetalert2/js/sweetalert2@11.js') }}"></script>

    <!-- DataTables Bootstrap 4 (JS) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <!-- Responsive (JS) -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <!-- Buttons (JS) -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    {{-- ✅ RUTAS JS PARA NAVBAR --}}
    <script>
        window.routes = window.routes || {};
        window.routes.navbarReminders = "{{ route('admin.reminders.navbar') }}";
        window.routes.markReadReminder = "{{ route('admin.reminders.mark-read', ['reminder' => '__ID__']) }}";
        window.routes.showReminderJson = "{{ route('admin.reminders.show-json', ['reminder' => '__ID__']) }}";
        window.routes.cancelReminder = "{{ route('admin.reminders.cancel', ['reminder' => '__ID__']) }}";

    </script>

    {{-- ✅ JS NAVBAR RECORDATORIOS --}}
    <script>
        function reminderPriorityBadge(priority) {
            if (priority === 'high') return 'badge-danger';
            if (priority === 'low') return 'badge-secondary';
            return 'badge-info';
        }

        function fetchNavbarReminders() {
            if (!window.routes || !window.routes.navbarReminders) return;

            $.get(window.routes.navbarReminders)
                .done(function(res) {
                    const unread = res.unread || 0;

                    const $b1 = $('#reminderBadge');
                    const $b2 = $('#reminderBadgeHeader');

                    if (unread > 0) {
                        $b1.removeClass('d-none').text(unread);
                        $b2.removeClass('d-none').text(unread);
                    } else {
                        $b1.addClass('d-none').text('0');
                        $b2.addClass('d-none').text('0');
                    }

                    const items = res.items || [];
                    const $list = $('#reminderDropdownItems');
                    $list.empty();

                    if (items.length === 0) {
                        $list.append(`<span class="dropdown-item text-muted">No hay recordatorios pendientes</span>`);
                        return;
                    }

                    items.forEach(item => {
                        const priClass = reminderPriorityBadge(item.priority);
                        const unreadDot = item.is_read ? '' :
                            '<span class="badge badge-primary ml-2">Nuevo</span>';

                        const subtitle = [
                            item.remind_at ?
                            `<small class="text-muted"><i class="far fa-clock mr-1"></i>${item.remind_at}</small>` :
                            '',
                            item.client ?
                            `<small class="text-muted d-block"><i class="far fa-user mr-1"></i>${item.client}</small>` :
                            '',
                            item.loan ?
                            `<small class="text-muted d-block"><i class="far fa-file-alt mr-1"></i>${item.loan}</small>` :
                            '',
                        ].join('');

                        $list.append(`
                            <a href="#" class="dropdown-item reminder-item" data-id="${item.id}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="mr-2">
                                        <div class="font-weight-bold">${item.title} ${unreadDot}</div>
                                        ${subtitle}
                                    </div>
                                    <span class="badge ${priClass}">&nbsp;</span>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                        `);
                    });

                    $list.find('.dropdown-divider').last().remove();
                });
        }

        $(document).on('click', '.reminder-item', function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            if (!id) return;

            // 1) marcar como leído
            if (window.routes && window.routes.markReadReminder) {
                $.post(window.routes.markReadReminder.replace('__ID__', id)).always(fetchNavbarReminders);
            }

            // 2) traer detalle y abrir modal
            const urlShow = window.routes.showReminderJson.replace('__ID__', id);

            $.get(urlShow).done(function(res) {
                const r = res.data || {};
                $('#vr_title').text(r.title || '—');
                $('#vr_message').text(r.message || '—');
                $('#vr_client').text(r.client || '—');
                $('#vr_loan').text(r.loan || '—');
                $('#vr_remind_at').text(r.remind_at || '—');
                $('#vr_expires_at').text(r.expires_at || '—');
                $('#vr_status').text(r.status || '—');

                $('#viewReminderModal').modal('show');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            fetchNavbarReminders();
            setInterval(fetchNavbarReminders, 15000);
        });
    </script>
@endpush

@push('css')
    {{-- DataTables Bootstrap 4 (CSS) --}}
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/responsive.bootstrap4.css') }}">

    <style type="text/css">
        #divLoading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(254, 254, 255, 0.65);
            z-index: 9999;
        }

        #divLoading img {
            width: 60px;
            height: 60px;
        }

        .img-avatar-navbar {
            margin-top: -8px;
            margin-right: -15px;
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        }
    </style>
@endpush
