@extends('adminlte::master')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')

    <style>
        /* Evita que el logo se corte en pantallas pequeñas */
        .login-logo, .register-logo {
            padding: 0 10px;
            overflow: visible;
        }

        /* Imagen del logo responsive */
        .auth-logo {
            max-width: 100%;
            height: auto;
            max-height: 80px;
            display: inline-block;
        }

        /* En pantallas MUY pequeñas, reduce un poco el alto */
        @media (max-width: 260px) {
            .auth-logo {
                max-height: 60px;
            }
        }

        /* ===== Fondo rotativo SOLO para login ===== */
        body.login-page {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: background-image .8s ease-in-out;
        }

        /* Overlay para que el card siempre sea legible */
        body.login-page::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35); /* ajusta: 0.25 más claro, 0.45 más oscuro */
            z-index: 0;
        }

        /* Asegura que el contenido esté encima del overlay */
        .login-box, .register-box {
            position: relative;
            z-index: 1;
        }

        @media (min-width: 576px) {
            .login-page .icheck-primary label {
                margin-bottom: 0;
                line-height: 1.1;
            }
        }

        /* Botón Iniciar sesión con esquinas suavemente redondeadas */
        .login-page .btn-primary {
            border-radius: 4px;
        }

        /* Footer copyright con esquinas suavemente redondeadas */
        .login-page .main-footer {
            border-radius: 4px;
        }

        /* Eliminar header vacío del card en login */
        .login-page .card-header:empty {
            display: none;
        }
    </style>
@stop

@section('classes_body'){{ ($auth_type ?? 'login') . '-page' }}@stop

@section('body')
    <div class="{{ $auth_type ?? 'login' }}-box">

        {{-- Logo --}}
        <!-- <div class="{{ $auth_type ?? 'login' }}-logo text-center mb-3">
            <a href="{{ $dashboard_url }}">

                {{-- Logo Image --}}
                @if (config('adminlte.auth_logo.enabled', false))
                    <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                         alt="{{ config('adminlte.auth_logo.img.alt') }}"
                         @if (config('adminlte.auth_logo.img.class', null))
                            class="{{ config('adminlte.auth_logo.img.class') }}"
                         @endif
                         @if (config('adminlte.auth_logo.img.width', null))
                            width="{{ config('adminlte.auth_logo.img.width') }}"
                         @endif
                         @if (config('adminlte.auth_logo.img.height', null))
                            height="{{ config('adminlte.auth_logo.img.height') }}"
                         @endif>
                @else
                    <img src="{{ asset('img/logo1.png') }}"
                        alt="FamySALUD"
                        class="auth-logo">
                @endif
                

            </a>
        </div> -->

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}">

            {{-- Card Header (logo solamente) --}}
            <div class="card-header text-center">
                <div>
                    <img src="{{ asset('img/logo1.png') }}"
                        alt="FamySALUD"
                        style="max-width: 240px; width: 100%; height: auto;">
                </div>
            </div>

            {{-- Card Body --}}
            <div class="card-body {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    <script>
        (function () {
            // Solo aplicar en login-page
            if (!document.body.classList.contains('login-page')) return;

            const images = [
                "{{ asset('img/login/bg1.jpg') }}",
                "{{ asset('img/login/bg2.jpg') }}",
                "{{ asset('img/login/bg3.jpg') }}"
            ];

            let index = 0;

            // Precarga para evitar parpadeo
            images.forEach(src => { const im = new Image(); im.src = src; });

            function setBackground(i) {
                document.body.style.backgroundImage = `url('${images[i]}')`;
            }

            setBackground(index);

            setInterval(() => {
                index = (index + 1) % images.length;
                setBackground(index);
            }, 5000);
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (passwordInput && toggleIcon) {
                toggleIcon.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    toggleIcon.classList.toggle('fa-eye');
                    toggleIcon.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
@stop
