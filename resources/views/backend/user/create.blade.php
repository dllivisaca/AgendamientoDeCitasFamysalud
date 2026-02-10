@extends('adminlte::page')

@section('title', 'Crear Usuario · FamySalud')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 class="m-0">Agregar Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Agregar usuario</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="">
        <!-- Content Header (Page header) -->
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content  py-2">
            <div class="">
                @if (session()->has('success'))
                    <div class="alert alert-dismissable alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>
                            {!! session()->get('success') !!}
                        </strong>
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


                <form action="{{ route('user.store') }}" method="post">
                    @csrf
                    <div class="row pl-md-2">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Nombre</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend ">
                                                <span class="input-group-text ">
                                                    <i class="fas fa-user">
                                                    </i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                name="name" value="{{ old('name') }}" placeholder="Nombre completo">
                                        </div>
                                        @error('name')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Email</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope">
                                                    </i>
                                                </span>
                                            </div>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" placeholder="Correo electrónico">
                                        </div>
                                        @error('email')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Teléfono</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-phone">
                                                    </i>
                                                </span>
                                            </div>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                                name="phone" value="{{ old('phone') }}" placeholder="Número de teléfono">
                                        </div>
                                        @error('phone')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Contraseña</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            </div>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                name="password"
                                                placeholder="Ingresa una contraseña">
                                        </div>
                                        @error('password')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Confirmar contraseña</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            </div>
                                            <input type="password"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                name="password_confirmation"
                                                placeholder="Confirmar contraseña">
                                        </div>
                                        @error('password_confirmation')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12 mb-3 select2-primary">
                                    <label class="my-0"><i class="fas fa-user-lock"></i> Rol del usuario</label>
                                    <select name="roles[]" class="form-control select2 @error('roles[]') is-invalid @enderror" data-placeholder="Selecciona un rol" multiple>
                                        @foreach ($roles as $role)
                                            @if ($role->name === 'employee')
                                                <option value="{{ $role->name }}"
                                                    {{ in_array($role->name, old('roles', ['employee'])) ? 'selected' : '' }}>
                                                    Profesional
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('roles')
                                        <small class="text-danger"><strong>{{ $message }}</strong></small>
                                    @enderror
                                </div>



                            </div>
                        </div>
                    </div>

                    <div class="row pt-3 pl-md-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <!-- Use old('is_employee') to check if it was previously checked -->
                                    <input type="checkbox" class="custom-control-input" id="is_employee"
                                        name="is_employee"
                                        {{ old('is_employee') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_employee">Es Profesional</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="employee" class="row pl-md-2 pb-5">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                    <div class="mb-3">
                                        <h4 class="mb-0">Sólo para Profesionales </h4>
                                        <small class="text-muted">Llena estos detalles sólo cuando agregas a un profesional</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 mb-3 select2-dark">
                                            <label for="service_id" class="my-0"><i class="fas fa-id-card"></i>
                                                Selecciona servicio(s)</label> <small class="text-muted"> Vincula a los profesionales con sus servicios asignados</small>
                                            <select class="form-control select2 @error('service[]') is-invalid @enderror"
                                                name="service[]" data-placeholder="Selecciona servicio(s)" id="service"
                                                multiple>
                                                @foreach ($services as $service)
                                                    <option
                                                        {{ in_array($service->id, old('service', [])) ? 'selected' : '' }}
                                                        value="{{ $service->id }}">{{ $service->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('service')
                                                <small class="text-danger"><strong>{{ $message }}</strong></small>
                                            @enderror
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                                            <label for="slot_duration" class="my-0"><i class="fas fa-stopwatch"></i>
                                                Duración del/de los servicio(s)</label> <small class="text-muted"> Crea bloques de turnos según la duración de tiempo que prefieras.</small>
                                            @php
                                                $steps = ['10', '15', '20', '30', '45', '60'];
                                                $selectedStep = old('slot_duration'); // Get the selected step value from old input
                                            @endphp
                                            <select class="form-control @error('step') is-invalid @enderror"
                                                name="slot_duration" id="slot_duration">
                                                <option value="" {{ !$selectedStep ? 'selected' : '' }}>Selecciona la duración
                                                </option>
                                                @foreach ($steps as $stepValue)
                                                    <option {{ $selectedStep == $stepValue ? 'selected' : '' }}
                                                        value="{{ $stepValue }}">{{ $stepValue }}</option>
                                                @endforeach
                                            </select>
                                            @error('slot_duration')
                                                <small class="text-danger"><strong>{{ $message }}</strong></small>
                                            @enderror
                                        </div>

                                        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                                            <label for="break_duration" class="my-0"><i class="fas fa-coffee"></i>
                                                Tiempo de preparación o descanso</label> <small class="text-muted"> Descanso entre una cita y otra</small>
                                            @php
                                                $breaks = ['5', '10', '15', '20', '25', '30'];
                                                $selectedBreak = old('break_duration'); // Get the selected step value from old input
                                            @endphp
                                            <select class="form-control @error('step') is-invalid @enderror"
                                                name="break_duration" id="break_duration">
                                                <option value="" {{ !$selectedBreak ? 'selected' : '' }}>Sin descanso
                                                </option>
                                                @foreach ($breaks as $breakValue)
                                                    <option {{ $selectedBreak == $breakValue ? 'selected' : '' }}
                                                        value="{{ $breakValue }}">{{ $breakValue }}</option>
                                                @endforeach
                                            </select>
                                            @error('break_duration')
                                                <small class="text-danger"><strong>{{ $message }}</strong></small>
                                            @enderror
                                        </div>


                                    </div>

                                    <hr>
                                    <div class="row">
                                        <div class="mb-3">
                                            <h4 class="mb-0">Definir disponibilidad del profesional</h4>
                                            <small class="text-muted">
                                                Selecciona los días y horarios, con la opción de agregar múltiples franjas horarias en un mismo día,
                                                por ejemplo: 9 AM–12 PM y 4 PM–8 PM
                                            </small>
                                        </div>

                                        <div class="col-md-12">
                                            @foreach ($days as $dayKey => $dayLabel)
                                                <!-- Fila principal del día -->
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox"
                                                                    class="custom-control-input day-toggle"
                                                                    id="{{ $dayKey }}"
                                                                    data-day="{{ $dayKey }}"
                                                                    @if (old('days.' . $dayKey)) checked @endif>
                                                                <label class="custom-control-label" for="{{ $dayKey }}">
                                                                    {{ $dayLabel }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Desde:</strong>
                                                            <input type="time"
                                                                class="form-control from"
                                                                name="days[{{ $dayKey }}][]"
                                                                value="{{ old('days.' . $dayKey . '.0') }}"
                                                                id="{{ $dayKey }}From">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <strong>Hasta:</strong>
                                                            <input type="time"
                                                                class="form-control to"
                                                                name="days[{{ $dayKey }}][]"
                                                                value="{{ old('days.' . $dayKey . '.1') }}"
                                                                id="{{ $dayKey }}To">
                                                        </div>

                                                        <div style="margin-top:-15px; cursor:pointer;"
                                                            id="{{ $dayKey }}AddMore"
                                                            class="text-right text-primary d-none day-add-more"
                                                            data-day="{{ $dayKey }}">
                                                            Agregar más
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Filas adicionales (si hubo old input) -->
                                                @if (old('days.' . $dayKey))
                                                    @foreach (old('days.' . $dayKey) as $index => $time)
                                                        @if ($index > 1 && $index % 2 == 0)
                                                            <div class="row additional-{{ $dayKey }}">
                                                                <div class="col-md-2"></div>

                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <strong>Desde:</strong>
                                                                        <input type="time"
                                                                            class="form-control from"
                                                                            name="days[{{ $dayKey }}][]"
                                                                            value="{{ $time }}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <strong>Hasta:</strong>
                                                                        <input type="time"
                                                                            class="form-control to"
                                                                            name="days[{{ $dayKey }}][]"
                                                                            value="{{ old('days.' . $dayKey . '.' . ($index + 1)) }}">
                                                                    </div>

                                                                    <div style="margin-top:-15px;"
                                                                        class="text-right remove-field text-danger">
                                                                        Eliminar
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>{{-- col-md-12 / row --}}
                            </div>
                        </div>
                        <hr>

                        <div class="row d-flex">
                            <div class="col-md-10">
                                <h2 class="mb-0">Agregar Feriados</h2>
                                <p class="text-muted">
                                    No es necesario agregar horario para una jornada completa; para trabajo de medio tiempo, especifica el día y la hora.
                                </p>

                                <span id="addHoliday" class="btn btn-primary mb-2 btn-sm">
                                    <i class="fa fa-plus"></i> Agregar feriado
                                </span>

                                <div class="holidayContainer">
                                    @php
                                        // En crear usuario, solo hay old() (no hay feriados de BD todavía)
                                        $holidaysInput = old('holidays.date', []);
                                        $holidaysToDisplay = !empty($holidaysInput) ? $holidaysInput : [];
                                    @endphp

                                    @forelse($holidaysToDisplay as $index => $tmp)
                                        @php
                                            $date      = old("holidays.date.$index", '');
                                            $fromTime  = old("holidays.from_time.$index", '');
                                            $toTime    = old("holidays.to_time.$index", '');
                                            $recurring = old("holidays.recurring.$index", 0);
                                        @endphp

                                        <div class="row holiday-row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="mb-0">Fecha</label>
                                                    <input class="form-control" type="date" name="holidays[date][]" value="{{ $date }}" required>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <strong>Desde:</strong>
                                                    <input type="time" class="form-control from" name="holidays[from_time][]" value="{{ $fromTime }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <strong>Hasta:</strong>
                                                    <input type="time" class="form-control to" name="holidays[to_time][]" value="{{ $toTime }}">
                                                    <div class="text-right text-danger removeHoliday" style="cursor:pointer;">
                                                        Eliminar
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="holidays[recurring][]" value="{{ $recurring }}">
                                        </div>
                                    @empty
                                        <p>No se encontraron feriados para este usuario. Haz clic en “Agregar feriado” para crear uno.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 pt-3 pl-md-3">
                        <button type="submit" class="btn btn-primary">Agregar usuario</button>
                    </div>
            </div>
        </div>
    </div>
    </div>
    </div>



    </form>
    </div>
    </div>
    </div>
    </div>

@stop

@section('css')

@stop

@section('js')

    <script>
        $(document).ready(function () {

            function toggleDayFields(dayId) {
                const isChecked = $('#' + dayId).is(':checked');

                // Inputs principales
                $('#' + dayId + 'From, #' + dayId + 'To').prop('disabled', !isChecked);

                // Inputs de filas extra
                $('.additional-' + dayId + ' input[type="time"]').prop('disabled', !isChecked);

                // Botón Agregar más
                if (isChecked) {
                    $('#' + dayId + 'AddMore').removeClass('d-none');
                } else {
                    $('#' + dayId + 'AddMore').addClass('d-none');
                    $('.additional-' + dayId).remove();
                }
            }

            function addMoreFields(dayId) {
                const originalRow = $('#' + dayId + 'AddMore').closest('.row');
                const clonedRow = originalRow.clone();

                // Limpia valores de time
                clonedRow.find('input[type="time"]').val('');

                // Vacía la columna izquierda (switch)
                clonedRow.find('.col-md-2').first().html('');

                // Cambia Agregar más -> Eliminar
                clonedRow.find('#' + dayId + 'AddMore')
                    .removeAttr('id')
                    .removeClass('day-add-more text-primary')
                    .addClass('remove-field text-danger')
                    .removeClass('d-none')
                    .text('Eliminar');

                // Marca como fila adicional
                clonedRow.addClass('additional-' + dayId);

                // Respeta disabled según estado del día
                const isChecked = $('#' + dayId).is(':checked');
                clonedRow.find('input[type="time"]').prop('disabled', !isChecked);

                // Inserta al final de las filas adicionales
                const last = $('.additional-' + dayId).last();
                if (last.length) last.after(clonedRow);
                else originalRow.after(clonedRow);
            }

            // ✅ Toggle por data-day (NO dependemos de arrays)
            $(document).on('change', '.day-toggle', function () {
                const dayId = $(this).data('day');
                toggleDayFields(dayId);
            });

            // ✅ Agregar más por data-day
            $(document).on('click', '.day-add-more', function () {
                const dayId = $(this).data('day');
                addMoreFields(dayId);
            });

            // ✅ Eliminar fila extra
            $(document).on('click', '.remove-field', function () {
                $(this).closest('.row').remove();
            });

            // ✅ Inicializar todos los días existentes en pantalla
            $('.day-toggle').each(function () {
                toggleDayFields($(this).data('day'));
            });

        });
    </script>

    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true,
                search: true,
                //maximumSelectionLength: 2
            });
        });
    </script>


<script>
    $(document).ready(function () {
        // Initially hide the row with id 'employee' when the page loads
        // Check if the checkbox is checked on page load and toggle visibility accordingly
        if ($('#is_employee').prop('checked')) {
            $('#employee').show();  // Show the row if checkbox is checked
        } else {
            $('#employee').hide();  // Hide the row if checkbox is unchecked
        }

        // When the 'Is Employee' checkbox is changed, toggle the row visibility
        $('#is_employee').change(function () {
            if ($(this).prop('checked')) {
                $('#employee').show();  // Show the row if checkbox is checked
            } else {
                $('#employee').hide();  // Hide the row if checkbox is unchecked
            }
        });
    });
</script>

    <script>
        $(document).ready(function() {
            // Agregar nueva fila de feriado
            $('#addHoliday').on('click', function() {
                const holidayRow = `
                <div class="row holiday-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-0">Fecha</label>
                            <input class="form-control" type="date" name="holidays[date][]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <strong>Desde:</strong>
                            <input type="time" class="form-control from" name="holidays[from_time][]">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <strong>Hasta:</strong>
                            <input type="time" class="form-control to" name="holidays[to_time][]">
                            <div class="text-right text-danger removeHoliday" style="cursor:pointer;">Eliminar</div>
                        </div>
                    </div>
                    <input type="hidden" name="holidays[recurring][]" value="0">
                </div>`;
                $('.holidayContainer').append(holidayRow);
            });

            // Eliminar fila de feriado
            $(document).on('click', '.removeHoliday', function() {
                $(this).closest('.holiday-row').remove();
            });
        });
    </script>


@stop
