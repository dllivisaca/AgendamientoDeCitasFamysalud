@extends('adminlte::page')

@section('title', 'Configuración · FamySalud')

@section('content_header')

    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Panel</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                </ol>
            </div>
        </div>
    </div>

@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>{{ session('success') }}</strong>
        </div>
    @endif

    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                @if ($setting->dark_logo)
                    <div class="card card-primary card-outline bg-dark">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="w-50 mx-auto"
                                    src="{{ asset('public/uploads/images/logo/' . $setting->dark_logo) }}" alt="">
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if ($setting->logo)
                                <img class="w-50 mx-auto" src="{{ asset('public/uploads/images/logo/' . $setting->logo) }}"
                                    alt="">
                                <hr>
                            @endif
                            <span class="text-muted text-center text-dark h4">{{ $setting->bname }}</span> <br>
                        </div>

                    </div>

                </div>


            </div>
            <!-- /.col -->
            <div class="col-md-9">
                @if (count($errors) > 0)
                    <div class="alert alert-dismissable alert-danger mt-3">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Ups!</strong> Hubo errores en tu solicitud.<br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card">
                    <form action="{{ route('setting.update', $setting->id) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#business" data-toggle="tab">Información del negocio</a></li>
                                <li class="nav-item"><a class="nav-link" href="#social" data-toggle="tab">Redes sociales</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#header" data-toggle="tab">Encabezado</a></li>
                                <li class="nav-item"><a class="nav-link" href="#footer" data-toggle="tab">Pie de página</a></li>
                                {{-- <li class="nav-item"><a class="nav-link" href="#smtp" data-toggle="tab">SMTP</a>
                                </li> --}}
                                <li class="nav-item"><a class="nav-link" href="#seo" data-toggle="tab">SEO</a></li>
                                {{-- <li class="nav-item"><a class="nav-link" href="#social-login" data-toggle="tab">Social
                                        login</a></li>
                                <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab">Advance</a>
                                </li> --}}

                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="business">
                                    <input type="hidden" class="form-control" name="id" id="inputName" value="">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Nombre del negocio</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control @error('bname') is-invalid @enderror"
                                                name="bname" id="inputName" placeholder="Nombre del negocio"
                                                value="{{ $setting->bname }}">
                                            @error('bname')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Correo</label>
                                        <div class="col-sm-10">
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                id="inputEmail" placeholder="Correo" value="{{ $setting->email }}">
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Moneda</label>
                                        <div class="col-sm-10">
                                            <input type="text"
                                                class="form-control @error('currency') is-invalid @enderror" name="currency"
                                                id="inputEmail" placeholder="USD" value="{{ $setting->currency }}">
                                                <small class="text-muted">Ejemplo: USD (usa solo la abreviatura) </small>
                                            @error('currency')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Teléfono</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="phone"
                                                placeholder="Número de contacto" value="{{ $setting->phone }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">WhatsApp</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="whatsapp"
                                                placeholder="Número de WhatsApp" value="{{ $setting->whatsapp }}">
                                            <small>Ingresa el número con código de país y sin espacios. Ej: 593999999999</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Logo del sitio</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" id="inputName2"
                                                placeholder="Profiel Picture" name="logo">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Dirección</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Dirección" value="{{ $setting->address }}" name="address">
                                            <small>La dirección se mostrará en la página de contacto</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Google
                                            Map</label>
                                        <div class="col-sm-10">
                                            <small>Pega aquí el iframe de Google Maps. Ten en cuenta el alto y ancho del mapa en la página de contacto.</small>
                                            <textarea name="map" id="" class="form-control" cols="30" rows="8">{{ $setting->map }}</textarea>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.Social -->
                                <div class="tab-pane" id="social">
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Facebook</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Facebook" value="{{ $setting->social['facebook'] ?? '' }}"
                                                name="social[facebook]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Instagram</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="Instagram" value="{{ $setting->social['instagram'] ?? '' }}"
                                                name="social[instagram]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">X</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="X" value="{{ $setting->social['twitter'] ?? '' }}"
                                                name="social[twitter]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">LinkedIn</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="LinkedIn" value="{{ $setting->social['linkedin'] ?? '' }}"
                                                name="social[linkedin]">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">YouTube</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2"
                                                placeholder="YouTube" value="{{ $setting->social['youtube'] ?? '' }}"
                                                name="social[youtube]">
                                        </div>
                                    </div>
                                </div>
                                <!-- /.Social ends-->
                                <!-- google analytics -->
                                <div class="tab-pane" id="header">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Encabezado</label>
                                        <div class="col-sm-10">
                                            <small>Head del sitio: pega aquí Google Analytics o CSS personalizado</small>
                                            <textarea name="header" id="" class="form-control" cols="30" rows="20">{{ $setting->header }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="footer">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Pie de página</label>
                                        <div class="col-sm-10">
                                            <small>Footer del sitio: agrega aquí JavaScript personalizado o lógica adicional</small>
                                            <textarea name="footer" id="" class="form-control" cols="30" rows="20">{{ $setting->footer }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="security">
                                    <div class="tab-pane" id="advance">
                                        <h3>Google Captcha</h3>
                                        <div class="form-group row pt-3">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Google Captcha Site
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="captcha[site_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="captcha[site_key]" placeholder="Captcha Site Key"
                                                    value="{{ $setting->captcha['site_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Google Captcha Secret
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="captcha[secret_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="captcha[secret_key]" placeholder="Captcha Secret Key"
                                                    value="{{ $setting->captcha['secret_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="captcha[login_form]" value="0">
                                                <input type="checkbox" class="custom-control-input" id="login_form"
                                                    name="captcha[login_form]" value="1"
                                                    {{ $setting->captcha['login_form'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="login_form">Login Form</label>
                                                <small>&nbsp;&nbsp;disable/enable the captcha for login page and forgot
                                                    password page</small>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-switch pb-3">
                                                <input type="hidden" name="captcha[contact_form]" value="0">
                                                <input type="checkbox" class="custom-control-input" id="contact_form"
                                                    name="captcha[contact_form]" value="1"
                                                    {{ $setting->captcha['contact_form'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="contact_form">Contact
                                                    Form</label>
                                                <small>&nbsp;&nbsp;disable/enable the captcha for contact form on
                                                    website</small>
                                            </div>
                                        </div>
                                        <hr>
                                        <h3>One Signal - Push Notifications</h3>
                                        <div class="form-group row pt-3">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Site
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[site_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="onesignal[site_key]" placeholder="One Signal Site Key"
                                                    value="{{ $setting->onesignal['site_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Secret
                                                Key</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[secret_key]" value="">
                                                <input type="text" class="form-control" id="inputName2"
                                                    name="onesignal[secret_key]" placeholder="One Signal Secret Key"
                                                    value="{{ $setting->onesignal['secret_key'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">One Signal Script</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="onesignal[script]" value="">
                                                <textarea class="form-control" name="onesignal[script]" id="" cols="30" rows="8">{{ $setting->onesignal['script'] ?? '' }}</textarea>

                                            </div>
                                        </div>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="onesignal[status]" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="push_notification" name="onesignal[status]" value="1"
                                                    {{ $setting->onesignal['status'] ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="push_notification">Disable/Enable
                                                    Push Notifications</label>
                                            </div>
                                        </div>

                                        <hr>
                                        <h3>User Registration</h3>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="user_registration" name="user_registration" value="1"
                                                    {{ $setting->user_registration ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="user_registration">Disable/Enable
                                                    </label> (<span class="text-danger">New User Registration</span>)
                                            </div>
                                        </div>

                                        <hr>
                                        <h3>Maintainence Mode</h3>
                                        <div class="form-group pb-3">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="maintenance_mode" value="0">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="maintenance_mode" name="maintenance_mode" value="1"
                                                    {{ $setting->maintenance_mode ?? false ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="maintenance_mode">Disable/Enable
                                                    </label> (<span class="text-danger">Website shows under maintainence</span>)
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- google analytics ends-->
                                <!-- google map -->
                                <div class="tab-pane" id="smtp">
                                    <h3 class="mb-1">SMTP</h3>
                                    <small>Este correo se usará para enviar notificaciones</small>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Servidor de correo</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[host]" value="">
                                            <input type="text" class="form-control" id="inputName2" name="smtp[host]"
                                                placeholder="smtp.gmail.com" value="{{ $setting->smtp['host'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Servidor de correo</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[port]" value="">
                                            <input type="text" class="form-control" id="inputName2" name="smtp[port]"
                                                placeholder="465" value="{{ $setting->smtp['port'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Encriptación</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[encryption]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[encryption]" placeholder="TLS/SSL"
                                                value="{{ $setting->smtp['encryption'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Usuario</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[user_name]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[user_name]" placeholder="tuemail@gmail.com"
                                                value="{{ $setting->smtp['user_name'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Contraseña</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="smtp[password]" value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="smtp[password]" placeholder="contraseña"
                                                value="{{ $setting->smtp['password'] ?? '' }}">
                                        </div>
                                    </div>

                                </div>

                                <div class="tab-pane" id="seo">
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Título del sitio</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" name="meta_title"
                                                placeholder="Título SEO del sitio" value="{{ $setting->meta_title }}">
                                            <small>Título que verá Google</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Palabras clave</label>
                                        <div class="col-sm-10">

                                            <textarea name="meta_keywords" id="" placeholder="palabra1, palabra2, palabra3" class="form-control"
                                                cols="30" rows="2">{{ $setting->meta_keywords }}</textarea>
                                            <small>Agrega palabras clave SEO para tu sitio</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Descripción del sitio</label>
                                        <div class="col-sm-10">
                                            <textarea name="meta_description" id="" placeholder="Descripción del sitio..." class="form-control"
                                                cols="30" rows="3">{{ $setting->meta_description }}</textarea>
                                            <small>Descripción SEO aquí</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- social login --}}

                                <div class="tab-pane" id="social-login">
                                    <h3>Login with Google</h3>
                                    <div class="form-group row pt-3">
                                        <label for="clientId" class="col-sm-2 col-form-label">Client Id</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][client_id]" value="">
                                            <input type="text" class="form-control" id="clientId"
                                                name="social_login[google][client_id]" placeholder="Google client id"
                                                value="{{ $setting->social_login['google']['client_id'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Secret Key</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][secret_key]" value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[google][secret_key]" placeholder="Google secret key"
                                                value="{{ $setting->social_login['google']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Callback Url</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[google][callback_url]"
                                                value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[google][callback_url]"
                                                placeholder="https://www.yoursite.com/google/callback"
                                                value="{{ $setting->social_login['google']['callback_url'] ?? '' }}">
                                            <small>Ex: https://www.yoursite.com/google/callback</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch pb-3">
                                            <input type="hidden" name="social_login[google][status]" value="0">
                                            <input type="checkbox" class="custom-control-input" id="google_login_status"
                                                name="social_login[google][status]" value="1"
                                                {{ $setting->social_login['google']['status'] ?? false ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="google_login_status">Disable/Enable
                                            </label>
                                        </div>
                                    </div>

                                    <hr>
                                    <h3>Login with Facebook</h3>
                                    <div class="form-group row pt-3">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Client Id</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][client_id]"
                                                value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="social_login[facebook][client_id]" placeholder="Facebook client id"
                                                value="{{ $setting->social_login['facebook']['client_id'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Secret
                                            Key</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][secret_key]"
                                                value="">
                                            <input type="text" class="form-control" id="inputName2"
                                                name="social_login[facebook][secret_key]"
                                                placeholder="Facebook secret Key"
                                                value="{{ $setting->social_login['facebook']['secret_key'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-3">
                                        <label for="SecretId" class="col-sm-2 col-form-label">Callback Url</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="social_login[facebook][callback_url]"
                                                value="">
                                            <input type="text" class="form-control" id="SecretId"
                                                name="social_login[facebook][callback_url]"
                                                placeholder="https://www.yoursite.com/facebook/callback"
                                                value="{{ $setting->social_login['facebook']['callback_url'] ?? '' }}">
                                            <small>Ex: https://www.yoursite.com/facebook/callback</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="social_login[facebook][status]" value="0">
                                            <input type="checkbox" class="custom-control-input"
                                                id="facebook_login_status" name="social_login[facebook][status]"
                                                value="1"
                                                {{ $setting->social_login['facebook']['status'] ?? false ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="facebook_login_status">Disable/Enable</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <!-- /.tab-content -->
                        <div class="form-group row">
                            <div class="offset-sm-2 col-sm-10">
                                <button onclick="return confirm('¿Seguro que deseas actualizar la configuración?');"
                                    type="submit" class="btn btn-danger text-left">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- change password-->

            <!-- change password ends--->
        </div>
        <!-- /.col -->

    </div>

    </section>

@stop



@section('js')
    {{-- <script>
        $(document).ready(function() {
            // Add click event listener to each nav-link
            $('.nav-link').on('click', function() {
                // Store the clicked tab's href in local storage
                localStorage.setItem('activeTab', $(this).attr('href'));
            });

            // Check if there is an active tab stored in local storage
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                // Remove 'active' class from all nav-links and tab-panes
                $('.nav-link').removeClass('active');
                $('.tab-pane').removeClass('active');

                // Add 'active' class to the stored tab and corresponding tab-pane
                $('a[href="' + activeTab + '"]').addClass('active');
                $(activeTab).addClass('active');
            } else {
                // If no active tab is found in local storage, select the tab with id 'business'
                $('a[href="#business"]').addClass('active');
                $('#business').addClass('active');
            }
        });

        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script> --}}
@stop

