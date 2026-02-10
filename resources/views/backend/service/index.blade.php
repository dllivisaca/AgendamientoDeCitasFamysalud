@extends('adminlte::page')

@section('title', 'Todos los Servicios · FamySalud')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Todos los servicios</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('service.create') }}">+ Agregar nuevo</a> |</li>
                <li class=""> &nbsp; <a href="{{ route('service.trash') }}">Ver papelera</a></li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="">
        <!-- Content Header (Page header) -->
        <!-- Content Header (Page header) -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
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
            @if (session('success'))
                <div class="alert alert-success alert-dismissable">
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
                                            <th style="width: 1%">
                                                #
                                            </th>
                                            <th style="width: 20%">
                                                Nombre
                                            </th>
                                            <th style="width: 10%">
                                                Imagen
                                            </th>
                                            <th style="width: 10%">
                                                Área de atención
                                            </th>
                                            {{-- <th style="width: 10%">
                                                Featured
                                            </th> --}}
                                            <th style="width: 10%" class="text-center">
                                                Estado
                                            </th>
                                            <th style="width: 8%">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($services as $service)
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td>
                                                    <a>
                                                        {{ $service->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($service->image)
                                                        <img style="width:75px;"
                                                            src="{{ asset('uploads/images/service/' . $service->image) }}"
                                                            alt="">
                                                    @else
                                                        <img style="width:75px;"
                                                            src="{{ asset('uploads/images/no-image.jpg') }}"
                                                            alt="">
                                                    @endif
                                                </td>
                                                <td>

                                                    {{ $service->category->title ?? 'NA' }}
                                                </td>
                                                {{-- <td>
                                                    @if ($service->featured)
                                                        Yes
                                                    @else
                                                        No
                                                    @endif
                                                </td> --}}

                                                <td class="project-state align-middle text-center">
                                                    @if ($service->status)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="project-actions align-middle text-center">
                                                    <div class="d-flex justify-content-center align-items-center gap-3">
                                                        <a class="btn btn-info btn-sm mr-2"
                                                            href="{{ route('service.edit', $service->id) }}">
                                                            <i class="fas fa-pencil-alt"></i>
                                                            Editar
                                                        </a>

                                                        <form action="{{ route('service.destroy', $service->id) }}"
                                                            method="post" class="mb-0">
                                                            @csrf
                                                            @method('delete')
                                                            <button
                                                                onclick="return confirm('¿Estás seguro de eliminar este elemento?');"
                                                                type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                                Borrar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.col -->

                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
    </div>
@stop

@section('css')
    <style>
        /* Desktop grande: normal */
        .table-scroll-wrap{
            overflow: visible;
        }

        /* Rango intermedio (cuando el sidebar aún no colapsa) */
        @media (max-width: 1200px){
            .table-scroll-wrap{
                overflow-x: auto;
                overflow-y: visible;
                -webkit-overflow-scrolling: touch;
                border-radius: .25rem;
            }

            #myTable{
                min-width: 1000px; /* puedes subir/bajar si quieres */
                width: 100%;
            }
        }

        /* Móvil: scroll vertical + header sticky */
        @media (max-width: 740px){
            .table-scroll-wrap{
                overflow-x: auto;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                max-height: 70vh;
                border-radius: .25rem;
            }

            #myTable{
                min-width: 900px;
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

    {{-- hide notifcation --}}
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


    {{-- Sucess and error notification alert --}}
    <script>
        $(document).ready(function() {
        // show error message
        @if ($errors->any())
            //var errorMessage = @json($errors->any()); // Get the first validation error message
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5500
            });

            Toast.fire({
                icon: 'error',
                title: 'Hay errores en la validación del formulario. Por favor, corregir.'
            });
        @endif

        // success message
        @if (session('success'))
            var successMessage = @json(session('success')); // Get the first sucess message
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5500
            });

            Toast.fire({
                icon: 'success',
                title: successMessage
            });
        @endif

        });
    </script>
@endsection
