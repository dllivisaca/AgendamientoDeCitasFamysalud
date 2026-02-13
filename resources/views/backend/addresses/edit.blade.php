@extends('adminlte::page')

@section('title', 'Editar dirección · FamySalud')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 class="m-0">Editar {{ $address->name }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Editar dirección</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="content py-2">
        <div class="">

            @if (session()->has('success'))
                <div class="alert alert-dismissable alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>{!! session()->get('success') !!}</strong>
                </div>
            @endif

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

            <form action="{{ route('addresses.update', $address->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row pl-md-2">
                    <div class="col-md-8">
                        <div class="row">

                            {{-- Nombre de la sede (obligatorio) --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="my-0">Nombre de la sede <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clinic-medical"></i></span>
                                        </div>
                                        <input type="text"
                                            class="form-control @error('name') is-invalid @enderror"
                                            name="name"
                                            value="{{ old('name', $address->name) }}"
                                            placeholder="Ej: Matriz - Centro"
                                            required>
                                    </div>
                                    @error('name')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Dirección completa (obligatorio) --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="my-0">Dirección completa <span class="text-danger">*</span></label>
                                    <textarea
                                        name="full_address"
                                        class="form-control @error('full_address') is-invalid @enderror"
                                        rows="3"
                                        placeholder="Ej: Av. Principal y Calle 10, Edificio X, Piso 2"
                                        required>{{ old('full_address', $address->full_address) }}</textarea>

                                    @error('full_address')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ciudad (obligatorio) --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="my-0">Ciudad <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text"
                                            class="form-control @error('city') is-invalid @enderror"
                                            name="city"
                                            value="{{ old('city', $address->city) }}"
                                            placeholder="Ej: Guayaquil"
                                            required>
                                    </div>
                                    @error('city')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Referencia (opcional) --}}
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="my-0">Referencia (opcional)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-location-arrow"></i></span>
                                        </div>
                                        <input type="text"
                                            class="form-control @error('reference') is-invalid @enderror"
                                            name="reference"
                                            value="{{ old('reference', $address->reference) }}"
                                            placeholder="Ej: Frente a la farmacia / Piso 2">
                                    </div>
                                    @error('reference')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Google Maps URL (opcional) --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="my-0">Link de Google Maps (opcional)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map"></i></span>
                                        </div>
                                        <input type="text"
                                            class="form-control @error('google_maps_url') is-invalid @enderror"
                                            name="google_maps_url"
                                            value="{{ old('google_maps_url', $address->google_maps_url) }}"
                                            placeholder="https://maps.google.com/...">
                                    </div>
                                    @error('google_maps_url')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Switch Activo --}}
                            <div class="col-12 pt-2">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="status" value="0">

                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="status"
                                            name="status"
                                            value="1"
                                            {{ old('status', (int)$address->status) ? 'checked' : '' }}>

                                        <label class="custom-control-label" for="status">Activa</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-12 pt-2 pl-md-3">
                    <button type="submit"
                        class="btn btn-danger"
                        onclick="return confirm('¿Estás seguro de actualizar esta dirección?')">
                        Actualizar dirección
                    </button>

                    <a href="{{ route('addresses.index') }}" class="btn btn-secondary ml-2">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
@endsection