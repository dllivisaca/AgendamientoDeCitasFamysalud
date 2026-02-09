@extends('adminlte::page')

@section('title', 'Todas las Áreas de Atención · FamySalud')

@section('content_header')

    <div class="container-fluid">
        <div class="row ">
            <div class="col-sm-6">
                <h1 class="m-0">Todas las Áreas de Atención</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Áreas de Atención</li>
                </ol>
            </div>
        </div>
    </div>

@stop

@section('content')

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

    <div class="container-fluid">
        <div class="row  justify-content-between">

            <div class="col-md-12 ">
                <h5><a href="{{ route('category.create') }}" class="btn btn-primary mb-1"><i class="fas fa-fw fa-plus "></i>
                        Agregar nueva</a>
                </h5>
                <div class="card p-2">

                    <div id="" class="card-body p-0">
                        <table id="myTable" class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th style="width: 1%">
                                        #
                                    </th>
                                    <th style="width: 20%">
                                        Nombre
                                    </th>
                                    <th style="width: 20%">
                                        Identificador
                                    </th>
                                    <th style="width: 15%">
                                        Cantidad de servicios
                                    </th>
                                    <th style="width: 7%">
                                        Estado
                                    </th>
                                    <th style="width: 25%">Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td> {{ $loop->iteration }} </td>
                                        <td>
                                            <a>
                                                {{ $category->title }}
                                            </a>

                                        </td>
                                        <td>
                                            <a>{{ $category->slug }}</a>
                                        </td>
                                        {{-- <td>

                                            {{ $category->posts->count() }}
                                        </td> --}}
                                        <td>
                                            {{ $category->services->count() }}
                                        </td>
                                        <td>
                                            @if ($category->status)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="project-actions text-right d-flex">

                                            <div>
                                                <a class="btn btn-info btn-sm ml-2"
                                                    href="{{ route('category.edit', $category->id) }}">
                                                    <i class="fas fa-pencil-alt">
                                                    </i>
                                                    Editar
                                                </a>
                                            </div>
                                            <div>
                                                <form action="{{ route('category.destroy', $category->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        onclick="return confirm('Category cannot be delted - Post attached');"
                                                        class="btn btn-danger btn-sm ml-2">
                                                        <i class="fas fa-trash"></i>
                                                        Borrar
                                                        </a>
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
        </div>
    </div>
@stop

@section('css')
    <style>
    /* =========================
        BASE: no tocar desktop
        ========================= */
    #myTable { width: 100%; }

    /* =========================
        MÓVIL: <= 730px
        - headers y celdas pueden volverse verticales
        - EXCEPTO Acción
        ========================= */
    @media (max-width: 730px){

        /* 1) Permitir “verticalidad” en TODO (header + body) */
        #myTable thead th,
        #myTable tbody td{
        white-space: normal !important;
        word-break: break-all !important;   /* vertical letra por letra si hace falta */
        overflow-wrap: anywhere !important;
        vertical-align: middle;
        text-align: center;
        }

        /* 2) EXCEPCIÓN: columna Acción (th + td) NO se rompe */
        #myTable thead th:nth-child(6),
        #myTable tbody td:nth-child(6){
        white-space: nowrap !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        }

        /* 3) Header "Acción" centrado y sin romper */
        #myTable thead th:nth-child(6){
        text-align: center;
        min-width: 120px; /* para que “Acción” no se aplaste */
        }

        /* 4) Botones Acción: uno debajo del otro, sin angostarse demasiado */
        #myTable tbody td:nth-child(6){
        min-width: 150px;              /* ancho mínimo para botones */
        display: flex !important;
        flex-direction: column !important;  /* uno debajo del otro */
        align-items: flex-end !important;
        justify-content: center !important;
        gap: 8px;
        }

        /* 5) Que los botones no se partan (texto en una línea) */
        #myTable tbody td:nth-child(6) .btn{
        white-space: nowrap !important;
        width: fit-content;
        min-width: 110px; /* evita que queden súper angostos */
        }

        /* 6) Quitar el d-flex que venía del td (para que no gane a tu layout) */
        #myTable tbody td.project-actions{
        display: block; /* por si en algún navegador se pelea con flex */
        }
    }
    </style>
@stop

@section('js')
    <script>
        $('#title').on("change keyup paste click", function() {
            var Text = $(this).val().trim();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');
            $('#slug').val(Text);
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
                    }
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>
@stop