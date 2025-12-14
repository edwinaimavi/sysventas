@extends('adminlte::auth.auth-page', ['authType' => 'login'])

{{-- Fondo y estilos personalizados --}}
@section('adminlte_css')
    <style>
        body.login-page {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.30), rgba(255, 255, 255, 0.35)),
                url('{{ asset('images/login-bg.jpg') }}') no-repeat center center fixed;
            background-size: cover;
        }

        .login-page .login-box {
            width: 100%;
            max-width: 420px;
        }

        /* Card moderna */
        .login-page .login-box .card {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(6px);
            border-radius: 18px;
            border: 0;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.35);
        }

        /* Header del auth */
        .login-page .card-header {
            border-bottom: none;
            background: transparent;
            text-align: center;
            padding-bottom: 0;
        }

        .login-logo-img {
            max-width: 90px;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        }

        .login-title {
            font-weight: 700;
            letter-spacing: .02em;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Inputs modernos */
        .login-page .input-group {
            border-radius: 999px;
            background: #f5f7fb;
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .login-page .input-group:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18);
            background: #ffffff;
        }

        .login-page .input-group .form-control {
            border: none;
            background: transparent;
            padding: 0.65rem 0.95rem;
            font-size: 0.92rem;
        }

        .login-page .input-group .form-control:focus {
            box-shadow: none;
        }

        .login-page .input-group-text {
            border: none;
            background: transparent;
        }

        .login-page .input-group-text span {
            color: #6b7280;
        }

        /* Labels arriba de los campos */
        .login-field-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.2rem;
        }

        /* Checkbox "Recuérdame" más limpio */
        .login-page .icheck-primary > input:first-child:checked + label::before,
        .login-page .icheck-primary > input:first-child:not(:checked) + label::before {
            border-radius: 4px;
        }

        /* Botón principal custom */
        .btn-login-main {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.45);
            transition: all 0.18s ease-in-out;
        }

        .btn-login-main:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.55);
            transform: translateY(-1px);
        }

        .btn-login-main:active {
            transform: translateY(0);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.40);
        }

        /* Footer del login (por si luego lo usas) */
        .login-page .auth-footer {
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
@stop

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

{{-- Header del login (título + logo) --}}
@section('auth_header')
    <div class="text-center">
        <h1 class="h4 login-title mb-1 text-dark">
            Bienvenido
        </h1>
        <p class="login-subtitle mb-0">
            Inicia sesión para acceder al sistema
        </p>
    </div>
@stop

@section('auth_body')
    <form action="{{ $loginUrl }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="form-group mb-3">
            <label for="email" class="login-field-label">
                {{ __('adminlte::adminlte.email') }}
            </label>
            <div class="input-group">
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="tucorreo@ejemplo.com"
                    autofocus
                >

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
            </div>

            {{-- Errores de validación del EMAIL (requerido, formato, etc) --}}
            @error('email')
                @php
                    // Detectamos si es el error de credenciales (auth.failed) para NO mostrarlo aquí
                    $isAuthFailed = in_array($message, ['auth.failed', __('auth.failed')], true);
                @endphp

                @if (! $isAuthFailed)
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @endif
            @enderror
        </div>

        {{-- Password field --}}
        <div class="form-group mb-3">
            <label for="password" class="login-field-label">
                {{ __('adminlte::adminlte.password') }}
            </label>
            <div class="input-group">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                >

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
            </div>

            {{-- Errores de validación del PASSWORD (requerido, etc) --}}
            @error('password')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Mensaje general de credenciales incorrectas (debajo de ambos campos) --}}
        @if ($errors->has('email'))
            @php
                $emailError = $errors->first('email');
                $isAuthFailed = in_array($emailError, ['auth.failed', __('auth.failed')], true);
            @endphp

            @if ($isAuthFailed)
                <div class="alert alert-danger mt-2 mb-0 py-2 px-3 small rounded">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Correo electrónico o contraseña incorrectos. Verifica tus datos e inténtalo nuevamente.
                </div>
            @endif
        @endif

        {{-- Login field --}}
        <div class="row align-items-center mt-3">
            <div class="col-7">
                <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        {{ __('adminlte::adminlte.remember_me') }}
                    </label>
                </div>
            </div>

            <div class="col-5">
                <button
                    type="submit"
                    class="btn btn-login-main btn-block {{ config('adminlte.classes_auth_btn', '') }}"
                >
                    <span class="fas fa-sign-in-alt mr-1"></span>
                    {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    {{-- Sin enlaces extra por ahora --}}
@stop
