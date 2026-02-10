@extends('adminlte::page')

@section('title', 'Editar Área de Atención · FamySalud')

@section('content_header')

    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 style="text-decoration: underline;" class="m-0 text-decoration-underline">Editar {{ $category->title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Áreas de atención</li>
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

    <div class="container-fluid">
        <div class=" justify-content-between pb-5">

            <form role="form" method="post" action="{{ route('category.update', $category->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-light">
                            <div class="card-header">
                                <h3 class="card-title">Editar Área de atención
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Contraer">
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
                                        id="title" name="title" placeholder="Escribe el nombre aquí.."
                                        value="{{ old('title', $category->title) }}">
                                    <small class="text-muted"> El nombre que aparecerá en la aplicación.</small>
                                    @error('title')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="mb-0" for="inputStatus">Identificador
                                    </label>

                                    <input style="background-color: rgb(220, 220, 220);"
                                        class="form-control @error('slug') is-invalid @enderror" type="text"
                                        id="slug" name="slug" placeholder="Escribe el identificador aquí.."
                                        value="{{ old('slug', $category->slug) }}">
                                    <small class="text-muted">&nbsp;&nbsp;   El “identificador” es la versión URL amigable del nombre. Suele ser en minúsculas y contiene únicamente letras, números y guiones. 
                                    </small>

                                    @error('slug')
                                        <p class="text-danger mb-0">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="card card-light">
                            <div class="card-header">
                                <h3 class="card-title">Descripción
                                </h3>
                                <small>&nbsp;&nbsp; La descripción no es visible de forma predeterminada; sin embargo, algunos temas pueden mostrarla. 
                                </small>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Contraer">
                                        <i class="fas fa-minus" aria-hidden="true">
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <textarea class="form-control" name="body" id="" value="{{ old('body') }}" cols="30" rows="5">{{ old('body', $category->body) }}</textarea>
                                    @error('body')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>

                        <div class="card card-light">
                            <div class="card-header">
                                <h3 class="card-title">Mensaje informativo del Área</h3>
                                <small>&nbsp;&nbsp;Este mensaje se mostrará cuando el paciente vea los servicios de esta área.</small>

                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Contraer">
                                        <i class="fas fa-minus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="form-group mb-2">
                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="show_info_message_ui"
                                            name="show_info_message"
                                            value="1"
                                            {{ old('show_info_message', (int)($category->show_info_message ?? 0)) === 1 ? 'checked' : '' }}
                                        >
                                        <label class="custom-control-label" for="show_info_message_ui">Activar mensaje</label>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="mb-1" for="info_message_text_ui">Texto del mensaje</label>
                                    <textarea
                                        class="form-control"
                                        id="info_message_text_ui"
                                        name="info_message_text"
                                        rows="4"
                                        placeholder="Ej: Si necesitas una cotización especial, escríbenos por WhatsApp y con gusto te ayudamos."
                                    >{{ old('info_message_text', $category->info_message_text) }}</textarea>
                                </div>
                            </div>
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
                                    <input placeholder="Post title here for seo..." type="text"
                                        class="form-control @error('meta_title') is-invalid @enderror" name="meta_title"
                                        id="" value="{{ old('meta_title', $category->meta_title) }}">
                                    @error('meta_title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-body  pt-0 pb-0">
                                <div class="form-group">
                                    <label for="">SEO Description
                                    </label>
                                    <textarea placeholder="Post description here for seo..."
                                        class="form-control @error('meta_description') is-invalid @enderror" name="meta_description" id=""
                                        cols="0" rows="4" value="{{ old('meta_description', $category->meta_description) }}">{{ old('meta_description', $category->meta_description) }}</textarea>
                                    @error('meta_description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-body pt-0 pb-0">
                                <div class="form-group">
                                    <label for="">SEO Keywords
                                    </label>
                                    <input type="text"
                                        class="form-control @error('meta_keywords') is-invalid @enderror"
                                        placeholder="keyword1, keyword2, keyword3" name="meta_keywords" id=""
                                        value="{{ old('meta_keywords', $category->meta_keywords) }}">
                                    @error('meta_keywords')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div> --}}
                    </div>

                    <div class="col-md-4">
                        <div class="sticky-top">
                            <div class="card card-primary sticky-bottom">
                                <div class="card-header">
                                    <h3 class="card-title">Detalles del Área de atención</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                            title="Contraer">
                                            <i class="fas fa-minus" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body pb-0">
                                    <div class="form-group">
                                        <label for="inputStatus">Estado</label>
                                        <select required="required" name="status" id="inputStatus" class="form-control custom-select">
                                            <option disabled value="">Selecciona...</option>
                                            <option value="1" {{ old('published', $category->status) == 1 ? 'selected' : '' }}>PUBLICADA</option>
                                            <option value="0" {{ old('published', $category->status) == 0 ? 'selected' : '' }}>BORRADOR</option>
                                        </select>
                                    </div>
                                    {{-- <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="featured"
                                                name="featured" value="1"
                                                {{ old('featured',$category->status) == 1 ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="featured">Featured</label>
                                            <small>categories prioritize content on the homepage.</small>
                                        </div>
                                    </div> --}}

                                    <div class="form-group pt-0 pb-0 text-right">
                                        <button onclick="return confirm('¿Estás seguro de actualizar  este elemento?');" type="submit" class="btn btn-danger">Actualizar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Imagen principal</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                            title="Contraer">
                                            <i class="fas fa-minus" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body pt-0 pb-0">
                                    <div class="form-group">
                                       @if ($category->image)
                                       <div class="form-group">
                                        <label for="delete_image">Borrar imagen</label>
                                        <input type="checkbox" name="delete_image" id="delete_image">
                                    </div>
                                       @endif
                                        <div class="justify-content-between">
                                            <div>
                                                {{-- <small class="text-red">&nbsp;&nbsp;Note: Webp Image | size: Width-1280px
                                                    Height: 720px
                                                </small><br> --}}
                                                <input class="form-control mt-2" name="image" accept="image/*" type="file" id="imgInp">

                                            </div>
                                            <div>
                                                @if ($category->image)
                                                    <img class="img-fluid"
                                                        style="width: 150px; margin-top:10px; border:1px solid black;"
                                                        id="blah"
                                                        src="{{ asset('uploads/images/category/' . $category->image) }}"
                                                        alt="Vista previa de la imagen">
                                                @else
                                                    <img style="width: 150px; margin-top:10px; border:1px solid black;"
                                                        id="blah" src="{{ asset('uploads/images/no-image.jpg') }}"
                                                        alt="Vista previa de la imagen">
                                                @endif
                                            </div>
                                        </div>

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

    {{-- show image --}}
    <script>
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $(".alert").delay(6000).slideUp(300);
        });
    </script>

    <script>
        $(document).ready(function () {
            const $sw = $('#show_info_message_ui');
            const $txt = $('#info_message_text_ui');

            function syncInfoMsgUI(){
                const enabled = $sw.is(':checked');
                $txt.prop('disabled', !enabled);
            }

            $sw.on('change', syncInfoMsgUI);
            syncInfoMsgUI();
        });
    </script>

@stop
