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
                <form action="{{ route('addresses.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nombre de la sede</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Ej: Matriz - Centro"
                            value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label>Dirección completa</label>
                        <textarea name="full_address"
                                class="form-control"
                                rows="3"
                                placeholder="Ej: Av. Principal y Calle 10, Edificio X, Piso 2">{{ old('full_address') }}</textarea>
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