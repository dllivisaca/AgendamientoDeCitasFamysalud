@extends('adminlte::page')

@section('title', 'Agregar dirección · FamySalud')

@section('content_header')
    <h1>Agregar dirección</h1>
@stop

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <form>

                    <div class="form-group">
                        <label>Nombre de la sede</label>
                        <input type="text" class="form-control" placeholder="Ej: Matriz - Centro">
                    </div>

                    <div class="form-group">
                        <label>Dirección completa</label>
                        <textarea class="form-control" rows="3"
                            placeholder="Ej: Av. Principal y Calle 10, Edificio X, Piso 2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Link de Google Maps (opcional)</label>
                        <input type="text" class="form-control"
                            placeholder="https://maps.google.com/...">
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