@extends('adminlte::page')

@section('title', 'Todas las direcciones · FamySalud')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Todas las direcciones</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                    <a href="{{ route('addresses.create') }}">+ Agregar nueva</a>
                </li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="card py-2 px-2">

                    <div class="card-body p-0 table-scroll-wrap">
                        <table id="myTable" class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de sede</th>
                                    <th>Dirección</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($addresses as $address)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $address->name ?? '' }}</td>
                                        <td>{{ $address->full_address ?? '' }}</td>
                                        <td>
                                            <span class="badge badge-success">Activa</span>
                                        </td>
                                        <td>
                                            <a class="btn btn-info btn-sm">
                                                <i class="fas fa-pencil-alt"></i>
                                                Editar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No hay direcciones registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>
@stop