@extends('adminlte::page')

@section('title', 'Editar Servicio · FamySalud')

@section('content_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="m-0">Editar servicio</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Editar servicio</li>
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
    <div class="">
        <form action="{{ route('service.update',$service->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">Editar servicio
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus" aria-hidden="true">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="inputStatus">Nombre
                                </label>
                                <input class="form-control @error('title') is-invalid @enderror" type="text"
                                    id="title" name="title" placeholder="Escribe el nombre aquí.." value="{{ old('title',$service->title) }}">
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="inputStatus">Identificador
                                </label>
                                <small>&nbsp;&nbsp;URL única del servicio
                                </small>
                                <input class="form-control bg-light @error('slug') is-invalid @enderror" type="text"
                                    id="slug" name="slug" placeholder="Escribe el identificador aquí.." value="{{ old('slug',$service->slug) }}">
                                @error('slug')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- <div class="form-group">
                                <label for="">Service Description
                                </label>

                                <textarea style="height: 600px;" id="summernote" name="body" value="{{ old('body',$service->body) }}"> {{ old('body',$service->body) }}</textarea>
                                @error('body')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> --}}
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">Precio
                            </h3>
                            <small class="text-muted pl-2"> SIN SÍMBOLO DE MONEDA – SIN ESPACIO</small>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus" aria-hidden="true">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-0" for="price">Precio</label>
                                    <input class="form-control" type="number" name="price" placeholder="Precio"
                                        value="{{ old('price',$service->price) }}">
                                        <p class="mb-0 text-muted small">Precio principal</p>
                                    @error('price')
                                        <span class="text-danger">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-0" for="sale_price">Precio promoción</label>
                                    <input class="form-control" type="number" name="sale_price" placeholder="Precio promoción"
                                        value="{{ old('sale_price',$service->sale_price) }}">
                                        <p class="mb-0 text-muted small">Precio promoción vigente</p>
                                    @error('sale_price')
                                        <span class="text-danger">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                          </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">Descripción
                            </h3>
                            <small>&nbsp;&nbsp;Una descripción corta del servicio
                            </small>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus" aria-hidden="true">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" name="excerpt" id="" value="{{ old('excerpt') }}" cols="30"
                                    rows="5">{{ old('excerpt',$service->excerpt) }}</textarea>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                    {{-- seo --}}
                    {{-- <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">SEO
                            </h3>
                            <small>&nbsp;&nbsp;Search engine details
                            </small>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                    title="Collapse">
                                    <i class="fas fa-minus" aria-hidden="true">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-3 pb-0">
                            <div class="form-group">
                                <label for="">SEO Title
                                </label>
                                <input placeholder="Service title here for seo..." type="text" class="form-control"
                                    name="meta_title" id="" value="{{ old('meta_title',$service->meta_title) }}">
                            </div>
                        </div>
                        <div class="card-body  pt-0 pb-0">
                            <div class="form-group">
                                <label for="">SEO Description
                                </label>
                                <textarea placeholder="Service description here for seo..." class="form-control" name="meta_description"
                                    id="" cols="0" rows="4" value="{{ old('meta_description',$service->meta_description) }}">{{ old('meta_description',$service->meta_description) }}</textarea>
                            </div>
                        </div>
                        <div class="card-body pt-0 pb-0">
                            <div class="form-group">
                                <label for="">SEO Keywords
                                </label>
                                <input type="text" class="form-control" placeholder="keyword1, keyword2, keyword3"
                                    name="meta_keyword" id="" value="{{ old('meta_keyword',$service->meta_keyword) }}">
                            </div>
                        </div>

                        <!-- /.card-body -->
                    </div> --}}
                </div>
                <div class="col-md-4">
                    <div class="sticky-top">
                        <div class="card card-primary sticky-bottom">
                            <div class="card-header">
                                <h3 class="card-title">Detalles del servicio
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus" aria-hidden="true">
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pb-0">
                                <div class="form-group select2-dark">
                                    <label>Área de atención
                                    </label>
                                    <small>&nbsp;&nbsp;Selecciona el área de atención para este servicio</small>

                                    <select id="category" name="category_id" class="form-control select2"
                                        data-placeholder="Buscar área de atención"
                                        data-allow-clear="true"
                                        style="width: 100%;">
                                    <option value=""></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>


                                @error('category_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror


                                </div>
                                {{-- ✅ Modalidad del servicio --}}
                                <div class="form-group">
                                    <label class="mb-1">Modalidad del servicio <span class="text-danger">*</span></label>

                                    @if ($errors->has('modalities'))
                                        <div class="text-danger small mb-2">{{ $errors->first('modalities') }}</div>
                                    @endif

                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="is_presential"
                                            name="is_presential"
                                            value="1"
                                            {{ old('is_presential', (int) $service->is_presential) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="is_presential">Presencial</label>
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="is_virtual"
                                            name="is_virtual"
                                            value="1"
                                            {{ old('is_virtual', (int) $service->is_virtual) ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="is_virtual">Virtual</label>
                                    </div>

                                    <small class="text-muted d-block mt-1">Puedes seleccionar una o ambas.</small>
                                </div>

                                {{-- ✅ Dirección (solo si Presencial) --}}
                                <div class="form-group" id="addressWrap" style="display:none;">
                                    <label class="mb-1">Dirección <span class="text-danger">*</span></label>

                                    <select name="address_id" id="address_id" class="form-control custom-select">
                                        <option value="">Selecciona una dirección</option>

                                        @if(isset($addresses))
                                            @foreach($addresses as $addr)
                                                <option value="{{ $addr->id }}"
                                                    {{ old('address_id', $service->address_id) == $addr->id ? 'selected' : '' }}>
                                                    {{ $addr->title ?? $addr->name ?? $addr->address ?? $addr->direccion ?? ('Dirección #' . $addr->id) }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    @error('address_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="inputStatus">Estado</label>
                                    <select required="required" name="status" id="inputStatus" class="form-control custom-select">
                                        <option disabled value="">Selecciona...</option>
                                        <option value="1" {{ isset($service->status) && $service->status == 1 ? 'selected' : '' }}>
                                            PUBLICADO
                                        </option>
                                        <option value="0" {{ isset($service->status) && $service->status == 0 ? 'selected' : '' }}>
                                            BORRADOR
                                        </option>
                                    </select>
                                </div>


                                {{-- <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featured"
                                            name="featured" value="1" {{ old('featured',$service->featured) == 1 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="featured">Featured
                                        </label>
                                        <small>Featured shall be shown on home as priorty</small>
                                    </div>
                                </div> --}}
                                <div class="form-group pt-0 pb-0 text-right">
                                    <button onclick="return confirm('¿Estás seguro de editar este elemento?');" type="submit" class="btn btn-danger">Actualizar
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <div class="card card-primary ">
                            <div class="card-header">
                                <h3 class="card-title">Imagen principal
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus" aria-hidden="true">
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-0 pb-0">
                                <div class="form-group">
                                    <small class="text-red">Nota: Tamaño: ancho 1200 px, alto 800 px. 
                                    </small>
                                    <input class="form-control" name="image" accept="image/*" type="file" id="imgInp">
                                    @if ($service->image)
                                    <img class="img-fluid"
                                        style="width: 150px; margin-top:10px; border:1px solid black;"
                                        id="blah"
                                        src="{{ asset('uploads/images/service/' . $service->image) }}"
                                        alt="Imagen del servicio">
                                @else
                                    <img style="width: 150px; margin-top:10px; border:1px solid black;"
                                        id="blah" src="{{ asset('uploads/images/no-image.jpg') }}"
                                        alt="your image">
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        /* summer note */
        .modal-header .close,
        .modal-header .mailbox-attachment-close {
            padding: 0rem;
            margin: 0 auto;
        }

        .modal-header {
            display: -ms-flexbox;
            display: block;
            -ms-flex-align: start;
            align-items: flex-start;
            -ms-flex-pack: justify;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            border-top-left-radius: calc(0.3rem - 1px);
            border-top-right-radius: calc(0.3rem - 1px);
        }

        /* ✅ Fix alineación Select2 (AdminLTE + Select2) */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            padding: .375rem .75rem !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px) !important;
            top: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-top: 0 !important;
            align-self: center !important;
        }

    </style>

@stop

@section('js')

    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

    {{-- summer note --}}
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                height: 400,

                callbacks: {
                    onImageUpload: function(files) {
                        uploadImage(files[0]);
                    },
                    onMediaDelete: function(target) {
                        deleteImage(target[0].src);
                        if (target[0].nodeName === 'VIDEO') {
                            // Check if the deleted element is a video
                            target.remove(); // Remove the video element
                        }
                    },

                }
            });

            function uploadImage(file) {
                let formData = new FormData();
                formData.append('image', file);

                $.ajax({
                    url: '{{ route('summer.upload.image') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let imageUrl = response.url;
                        $('#summernote').summernote('editor.insertImage', imageUrl);
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }

            function deleteImage(imageSrc) {
                console.log('Deleting image with source URL:', imageSrc);

                $.ajax({
                    url: '{{ route('summer.delete.image') }}',
                    type: 'POST',
                    data: {
                        imageSrc: imageSrc
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response.message);
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }

        });

    </script>



    {{-- view image while uploading --}}
    <script>
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }
    </script>
    {{-- create live slug --}}
    <script>
        $('#title').on("change keyup paste click", function() {
            var Text = $(this).val().trim();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');
            $('#slug').val(Text);
        });
    </script>
    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('#tags').select2();
        });
    </script>


    {{-- for auto hide alert message --}}
    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>


    {{-- ck editor image updoad --}}

    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('#tags').select2();
        });
    </script>

    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('#category').select2({
                placeholder: $('#category').data('placeholder'),
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const $presential = $('#is_presential');
            const $wrap = $('#addressWrap');
            const $select = $('#address_id');

            function syncAddress() {
                const isOn = $presential.is(':checked');

                if (isOn) {
                    $wrap.show();
                    $select.prop('required', true);
                } else {
                    $wrap.hide();
                    $select.prop('required', false);
                    $select.val('');
                }
            }

            $presential.on('change', syncAddress);

            // init (para que cargue visible si ya es presencial en BD)
            syncAddress();
        });
    </script>

    {{-- disable multiple select2 --}}


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
                    title: 'Hay errores en el formulario. Por favor corrígelos.'
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

@stop
