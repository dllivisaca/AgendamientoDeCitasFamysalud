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

@section('css')
    <style>
        .table-scroll-wrap{
            overflow: visible;
        }

        @media (max-width: 1200px){
            .table-scroll-wrap{
                overflow-x: auto;
                overflow-y: visible;
                -webkit-overflow-scrolling: touch;
                border-radius: .25rem;
            }

            #myTable{
                min-width: 900px;
                width: 100%;
            }
        }

        @media (max-width: 740px){
            .table-scroll-wrap{
                overflow-x: auto;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                max-height: 70vh;
                border-radius: .25rem;
            }

            #myTable{
                min-width: 800px;
                width: 100%;
            }

            #myTable thead th{
                position: sticky;
                top: 0;
                background: #fff;
                z-index: 2;
            }
        }
    </style>
@stop

@section('js')

    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: false,
                autoWidth: false,
                language: {
                    lengthMenu: "Mostrar _MENU_ registros",
                    search: "Buscar:",
                    info: "Mostrando registros _START_–_END_ de _TOTAL_",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    zeroRecords: "No se encontraron resultados",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    processing: "Procesando...",
                    loadingRecords: "Cargando...",
                    emptyTable: "No hay datos disponibles en la tabla",
                    aria: {
                        sortAscending: ": activar para ordenar la columna de manera ascendente",
                        sortDescending: ": activar para ordenar la columna de manera descendente"
                    }
                }
            });
        });
    </script>

@stop

@section('content')
<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissable mt-2">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

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
                                    <th>Referencia</th>
                                    <th>Ciudad</th>
                                    <th>Google Maps</th>
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

                                        <td>{{ $address->reference ?? '—' }}</td>
                                        <td>{{ $address->city ?? '—' }}</td>

                                        <td>
                                            @if (!empty($address->google_maps_url))
                                                <a href="{{ $address->google_maps_url }}" target="_blank" rel="noopener">
                                                    Ver mapa
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td>
                                            @if ($address->status)
                                                <span class="badge badge-success">Activa</span>
                                            @else
                                                <span class="badge badge-danger">Inactiva</span>
                                            @endif
                                        </td>

                                        <td class="project-actions text-center align-middle">
                                            <div class="d-flex justify-content-center align-items-center gap-3">
                                                <a class="btn btn-info btn-sm mr-2"
                                                href="{{ route('addresses.edit', $address->id) }}">
                                                    <i class="fas fa-pencil-alt"></i>
                                                    Editar
                                                </a>

                                                <form action="{{ route('addresses.destroy', $address->id) }}" method="POST" class="mb-0">
                                                    @csrf
                                                    @method('delete')
                                                    <button
                                                        onclick="return confirm('¿Estás seguro de eliminar esta dirección?');"
                                                        type="submit"
                                                        class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                        Borrar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
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