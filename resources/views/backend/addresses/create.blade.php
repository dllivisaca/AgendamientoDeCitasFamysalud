@extends('adminlte::page')

@section('title', 'Agregar dirección · FamySalud')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 class="m-0">Agregar dirección</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Agregar dirección</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Ups!</strong> Revisa los campos.<br>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>{{ session('success') }}</strong>
                    </div>
                @endif

                <form action="{{ route('addresses.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nombre de la sede <span class="text-danger">*</span></label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Ej: Matriz - Centro"
                            value="{{ old('name') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Dirección completa <span class="text-danger">*</span></label>
                        <textarea name="full_address"
                                class="form-control"
                                rows="3"
                                placeholder="Ej: Av. Principal y Calle 10, Edificio X, Piso 2"
                                required>{{ old('full_address') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Ciudad <span class="text-danger">*</span></label>
                        <input type="text"
                            name="city"
                            class="form-control"
                            placeholder="Ej: Guayaquil"
                            value="{{ old('city') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Referencia (opcional)</label>
                        <input type="text"
                            name="reference"
                            class="form-control"
                            placeholder="Ej: Cerca del parque / frente a la farmacia"
                            value="{{ old('reference') }}">
                    </div>

                    <div class="form-group">
                        <label>Link de Google Maps (opcional)</label>
                        <input type="text"
                            name="google_maps_url"
                            class="form-control"
                            placeholder="https://maps.google.com/..."
                            value="{{ old('google_maps_url') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Guardar dirección
                    </button>

                </form>
            </div>
        </div>

    </div>
</section>
@stop