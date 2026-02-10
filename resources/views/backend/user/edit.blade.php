@extends('adminlte::page')

@section('title', 'Editar Usuario · FamySalud')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 class="m-0">Editar {{ $user->name }}</h1>
                @php
                    $isAdminUser = $user->roles->contains('name', 'admin');
                @endphp
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Editar usuario</li>
                </ol>
            </div>
        </div>
    </div>
@endsection

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


                <form action="{{ route('user.update', $user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
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
                                                name="name" value="{{ old('name', $user->name) }}"
                                                placeholder="Nombre completo">
                                        </div>
                                        @error('name')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label class="my-0">Correo electrónico</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope">
                                                    </i>
                                                </span>
                                            </div>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email', $user->email) }}" placeholder="Correo electrónico">
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
                                                name="phone" value="{{ old('phone', $user->phone) }}"
                                                placeholder="Número de teléfono">
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
                                                class="form-control @error('password') is-invalid @enderror" name="password"
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
                                                name="password_confirmation" placeholder="Confirmar contraseña">
                                        </div>
                                        @error('password_confirmation')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12 mb-3 select2-primary">
                                    <label class="my-0"><i class="fas fa-user-lock"></i> Rol del usuario</label>

                                    @if ($isAdminUser)
                                        {{-- Admin: rol fijo, sin permitir cambios --}}
                                        <div class="form-control d-flex align-items-center" style="background:#f4f6f9;">
                                            <i class="fas fa-shield-alt mr-2"></i> Administrador
                                        </div>
                                        <small class="text-muted">Este rol no se puede modificar.</small>
                                    @else
                                        {{-- No admin: rol editable --}}
                                        <select name="roles[]"
                                            class="form-control select2 @error('roles[]') is-invalid @enderror"
                                            data-placeholder="Selecciona un rol" multiple>
                                            <option value="employee"
                                                @if ($user->roles->contains('name', 'employee') || in_array('employee', old('roles', []))) selected @endif>
                                                Profesional
                                            </option>
                                        </select>

                                        @error('roles')
                                            <small class="text-danger"><strong>{{ $message }}</strong></small>
                                        @enderror
                                    @endif
                                </div>

                                <div class="row pt-3 pl-md-2">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                {{-- Hidden field to ensure "0" is submitted if checkbox is unchecked --}}
                                                <input type="hidden" name="status" value="0">

                                                {{-- Actual checkbox --}}
                                                <input type="checkbox" class="custom-control-input" id="status"
                                                    name="status" value="1"
                                                    {{ old('status', $user->status) ? 'checked' : '' }}>

                                                <label class="custom-control-label" for="status">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                            </div>
                        </div>
                    </div>

                    @if (!$isAdminUser)
                        <div class="row pt-3 pl-md-2">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <!-- If user has the employee or moderator role, check the checkbox -->
                                        <input type="checkbox" class="custom-control-input" id="is_employee"
                                            name="is_employee" @if ($user->employee == true) checked @endif>
                                        <label class="custom-control-label" for="is_employee">Es Profesional</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (!$isAdminUser)
                        <div id="employee" class="row pl-md-2 pb-5">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr>
                                        <div class="mb-3">
                                            <h4 class="mb-0">Sólo para Profesionales  </h4>
                                            <small class="text-muted"> Llena estos detalles sólo cuando agregas a un profesional </small>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12 mb-3 select2-dark">
                                                <label for="service_id" class="my-0">
                                                    <i class="fas fa-id-card"></i>  Selecciona servicio(s)
                                                </label>
                                                <small class="text-muted"> Vincula a los profesionales con sus servicios asignados</small>

                                                <select class="form-control servicesSelect2 @error('service[]') is-invalid @enderror"
                                                    name="service[]" data-placeholder="Selecciona servicio(s)" id="service"
                                                    multiple>
                                                    @foreach ($services as $service)
                                                        <option value="{{ $service->id }}"
                                                            {{ $user->employee && $user->employee->services->contains('id', $service->id) ? 'selected' : '' }}>
                                                            {{ $service->title }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                @error('service')
                                                    <small class="text-danger"><strong>{{ $message }}</strong></small>
                                                @enderror
                                            </div>

                                            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                                                <label for="slot_duration" class="my-0">
                                                    <i class="fas fa-stopwatch"></i> Duración del/de los servicio(s)
                                                </label>
                                                <small class="text-muted">  Crea bloques de turnos según la duración de tiempo que prefieras.</small>

                                                <select class="form-control @error('slot_duration') is-invalid @enderror"
                                                    name="slot_duration" id="slot_duration">
                                                    <option value=""
                                                        {{ old('slot_duration', optional($user->employee)->slot_duration) == '' ? 'selected' : '' }}>
                                                        Selecciona la duración
                                                    </option>

                                                    @foreach ($steps as $stepValue)
                                                        <option value="{{ $stepValue }}"
                                                            {{ old('slot_duration', optional($user->employee)->slot_duration) == $stepValue ? 'selected' : '' }}>
                                                            {{ $stepValue }} minutos
                                                        </option>
                                                    @endforeach
                                                </select>


                                                @error('slot_duration')
                                                    <small class="text-danger"><strong>{{ $message }}</strong></small>
                                                @enderror
                                            </div>


                                            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                                                <label for="break_duration" class="my-0">
                                                    <i class="fas fa-coffee"></i>  Tiempo de preparación o descanso
                                                </label>
                                                <small class="text-muted">  Descanso entre una cita y otra</small>

                                                <select class="form-control @error('break_duration') is-invalid @enderror"
                                                    name="break_duration" id="break_duration">
                                                    <option value=""
                                                        {{ old('break_duration', optional($user->employee)->break_duration) == '' ? 'selected' : '' }}>
                                                        Sin descanso
                                                    </option>

                                                    @foreach ($breaks as $breakValue)
                                                        <option value="{{ $breakValue }}"
                                                            {{ old('break_duration', optional($user->employee)->break_duration) == $breakValue ? 'selected' : '' }}>
                                                            {{ $breakValue }}
                                                        </option>
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
                                                    Selecciona los días y horarios, con la opción de agregar múltiples franjas horarias en un mismo día, por ejemplo: 9 AM–12 PM y 4 PM–8 PM 
                                                </small>
                                            </div>

                                            <div class="col-md-12">
                                                @foreach ($days as $dayKey => $dayLabel)
                                                    @php
                                                        $daySlots = old("days.$dayKey") ?? ($employeeDays[$dayKey] ?? []);
                                                        $daySlots = is_array($daySlots) ? array_values($daySlots) : [];
                                                    @endphp

                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox"
                                                                    class="custom-control-input"
                                                                    id="{{ $dayKey }}"
                                                                    {{ !empty($daySlots) ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="{{ $dayKey }}">
                                                                    {{ $dayLabel }}
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <strong>Desde:</strong>
                                                            <input type="time"
                                                                class="form-control"
                                                                name="days[{{ $dayKey }}][]"
                                                                id="{{ $dayKey }}From"
                                                                value="{{ $daySlots[0] ?? '' }}">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <strong>Hasta:</strong>
                                                            <input type="time"
                                                                class="form-control"
                                                                name="days[{{ $dayKey }}][]"
                                                                id="{{ $dayKey }}To"
                                                                value="{{ $daySlots[1] ?? '' }}">

                                                            <div id="{{ $dayKey }}AddMore"
                                                                class="text-right text-primary d-none"
                                                                style="cursor:pointer;">
                                                                Agregar más
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- ✅ Render de franjas adicionales (2 en adelante) --}}
                                                    @if (count($daySlots) > 2)
                                                        @for ($i = 2; $i < count($daySlots); $i += 2)
                                                            <div class="row additional-{{ $dayKey }}">
                                                                <div class="col-md-2"></div>

                                                                <div class="col-md-4">
                                                                    <strong>Desde:</strong>
                                                                    <input type="time"
                                                                        class="form-control"
                                                                        name="days[{{ $dayKey }}][]"
                                                                        value="{{ $daySlots[$i] ?? '' }}">
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <strong>Hasta:</strong>
                                                                    <input type="time"
                                                                        class="form-control"
                                                                        name="days[{{ $dayKey }}][]"
                                                                        value="{{ $daySlots[$i+1] ?? '' }}">

                                                                    <div class="text-right text-danger remove-field" style="cursor:pointer;">
                                                                        Eliminar
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endfor
                                                    @endif
                                                @endforeach
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
                                                        // Get holidays from old input or database
                                                        $holidaysInput = old('holidays.date', []);
                                                        //$dbHolidays = $user->employee->holidays ?? [];
                                                        $dbHolidays = optional($user->employee)->holidays ?? [];
                                                        $holidaysToDisplay = !empty($holidaysInput)
                                                            ? $holidaysInput
                                                            : $dbHolidays;
                                                    @endphp

                                                    @forelse($holidaysToDisplay as $index => $holidayItem)
                                                        @php
                                                            // Determine if we're using old input or database data
                                                            $usingOldInput = !empty($holidaysInput);

                                                            if ($usingOldInput) {
                                                                $holiday = null;

                                                                $date = old("holidays.date.$index", '');

                                                                $fromTime  = old("holidays.from_time.$index", '');
                                                                $toTime    = old("holidays.to_time.$index", '');
                                                                $recurring = old("holidays.recurring.$index", 0);
                                                            } else {
                                                                $holiday = $holidayItem;

                                                                $date = $holiday->date ?? '';

                                                                if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                                                    try {
                                                                        $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                                                    } catch (\Exception $e) {
                                                                        $date = '';
                                                                    }
                                                                }

                                                                $hours0 = '';
                                                                if ($holiday && !empty($holiday->hours) && isset($holiday->hours[0])) {
                                                                    $hours0 = $holiday->hours[0];
                                                                }

                                                                $parts = $hours0 ? explode('-', $hours0) : ['', ''];

                                                                $fromTime  = old("holidays.from_time.$index", $parts[0] ?? '');
                                                                $toTime    = old("holidays.to_time.$index",   $parts[1] ?? '');
                                                                $recurring = old("holidays.recurring.$index", $holiday->recurring ?? 0);
                                                            }
                                                        @endphp
                                                        <div class="row holiday-row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="mb-0">Fecha</label>
                                                                    <input class="form-control" type="date"
                                                                        name="holidays[date][]" value="{{ $date }}"
                                                                        required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <strong>Desde:</strong>
                                                                    <input type="time" class="form-control from"
                                                                        name="holidays[from_time][]"
                                                                        value="{{ $fromTime }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <strong>Hasta:</strong>
                                                                    <input type="time" class="form-control to"
                                                                        name="holidays[to_time][]"
                                                                        value="{{ $toTime }}">
                                                                    <div class="text-right text-danger removeHoliday"
                                                                        style="cursor:pointer;">
                                                                        Eliminar
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="holidays[recurring][]"
                                                                value="{{ $recurring }}">
                                                        </div>
                                                    @empty
                                                        <p>No se encontraron feriados para este usuario. Haz clic en “Agregar feriado” para crear uno.
                                                        </p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    {{-- <div class="col-xs-12 col-sm-12 col-md-12 pt-2 pl-md-3">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to update this user?')">Update user</button>
                    </div> --}}
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 pt-2 pl-md-3">
                <button type="submit" class="btn btn-danger"
                    onclick="return confirm('¿Estás seguro de actualizar este usuario?')">Actualizar usuario
                </button>
            </div>
        </form>
    </div>
</div>
</div>

@endsection

@section('css')
    <style>
        .remove-field { cursor: pointer; }
    </style>
@endsection

@section('js')

    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true,
                search: true,
                maximumSelectionLength: 1,
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando...",
                    loadingMore: () => "Cargando más resultados...",
                    inputTooShort: () => "Escribe más caracteres...",
                    errorLoading: () => "No se pudieron cargar los resultados",
                    maximumSelected: () => "Solo puedes seleccionar 1 opción",
                    removeAllItems: () => "Eliminar todas las selecciones"
                }
            });
        });
    </script>

    <script>
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.servicesSelect2').select2({
                allowClear: true,
                search: true,
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando...",
                    loadingMore: () => "Cargando más resultados...",
                    inputTooShort: () => "Escribe más caracteres...",
                    errorLoading: () => "No se pudieron cargar los resultados",
                    removeAllItems: () => "Eliminar todas las selecciones"
                }
                //maximumSelectionLength: 1
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function toggleDayFields(dayId) {
                var isChecked = $('#' + dayId).prop('checked');
                $('#' + dayId + 'From, #' + dayId + 'To').prop('disabled', !isChecked);

                // ✅ también afecta inputs de filas extra ya renderizadas
                $('.additional-' + dayId + ' input').prop('disabled', !isChecked);

                if (isChecked) {
                    $('#' + dayId + 'AddMore').removeClass('d-none');
                } else {
                    $('#' + dayId + 'AddMore').addClass('d-none');
                    $('.additional-' + dayId).remove();
                }
            }

            function addMoreFields(dayId) {
                // Clone the original row for the specific day
                var originalRow = $('#' + dayId + 'AddMore').closest('.row');
                var clonedRow = originalRow.clone();

                // Reset the values in the cloned row (but don't enable the fields yet)
                clonedRow.find('input').each(function() {
                    $(this).val(''); // Clear the value
                });

                // Replace the col-md-2 section with a blank div for the cloned row
                clonedRow.find('.col-md-2').replaceWith('<div class="col-md-2"></div>');

                // Update "Add More" to "Remove" for the cloned row
                clonedRow.find(`#${dayId}AddMore`).text('Eliminar').attr('id', '').addClass(
                    'remove-field text-danger');

                // Add a unique class to the cloned row for targeting specific day rows
                clonedRow.addClass('additional-' + dayId);

                // Append the cloned row after the original row or the last cloned row
                if (originalRow.closest('.row').siblings('.additional-' + dayId).length === 0) {
                    originalRow.after(clonedRow);
                } else {
                    originalRow.closest('.row').siblings('.additional-' + dayId).last().after(clonedRow);
                }
            }

            // Remove cloned rows
            $(document).on('click', '.remove-field', function() {
                $(this).closest('.row').remove();
            });

            // Bind change and add-more events to all days
            ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'].forEach(function(day) {
                $('#' + day).on('change', function() {
                    toggleDayFields(day);
                }).trigger('change');

                $('#' + day + 'AddMore').on('click', function() {
                    addMoreFields(day);
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Initially hide the row with id 'employee' when the page loads
            // Check if the checkbox is checked on page load and toggle visibility accordingly
            if ($('#is_employee').prop('checked')) {
                $('#employee').show(); // Show the row if checkbox is checked
            } else {
                $('#employee').hide(); // Hide the row if checkbox is unchecked
            }

            // When the 'Is Employee' checkbox is changed, toggle the row visibility
            $('#is_employee').change(function() {
                if ($(this).prop('checked')) {
                    $('#employee').show(); // Show the row if checkbox is checked
                } else {
                    $('#employee').hide(); // Hide the row if checkbox is unchecked
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Add new holiday row
            $('#addHoliday').click(function() {
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

            // Remove holiday row
            $(document).on('click', '.removeHoliday', function() {
                $(this).closest('.holiday-row').remove();
            });
        });
    </script>

@endsection