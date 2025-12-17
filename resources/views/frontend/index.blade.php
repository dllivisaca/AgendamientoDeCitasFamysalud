<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ $setting->meta_title }}</title>
      <!-- SEO Meta Tags -->
      <meta name="description" content="{{ $setting->meta_description }}">
      <meta name="keywords" content="{{ $setting->meta_keywords }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css"
        integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
    @if ($setting->header)
        {!! $setting->header !!}
    @endif
</head>

<body>
    <header class="header-section">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="#" id="logo-reset">
                    <img src="{{ asset('img/logo1.png') }}" alt="Logo" class="brand-logo">
                    <!-- <i class="bi bi-calendar-check"></i> AppointEase -->
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('login') }}">Iniciar sesión</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Registrarse</a>
                            </li>
                        @endguest

                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Calendario</a>
                            </li>
                        @endauth

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="booking-container">
            <div class="booking-header">
                <h2><i class="bi bi-calendar-check"></i> Agendamiento de citas</h2>
                <p class="mb-0">Complete el proceso en pocos pasos</p>
            </div>

            <div class="booking-steps position-relative">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-title">Área de atención</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-title">Servicio</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-title">Profesional</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-title">Modalidad, fecha y hora</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-title">Ingreso de datos</div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-title">Pago</div>
                </div>
                <div class="progress-bar-steps">
                    <div class="progress"></div>
                </div>
            </div>

            <div class="booking-content">
                <!-- Step 1: Category Selection -->
                <div class="booking-step active" id="step1">
                    <h3 class="mb-4">Seleccione el área de atención</h3>
                    <div class="row row-cols-1 row-cols-md-3 g-4" id="categories-container">
                        <!-- Categories will be inserted here by jQuery -->
                    </div>
                </div>

                <!-- Step 2: Service Selection -->
                <div class="booking-step" id="step2">
                    <h3 class="mb-4">Seleccione el servicio</h3>
                    <div class="selected-category-name mb-3 fw-bold"></div>
                    <div class="row row-cols-1 row-cols-md-3 g-4" id="services-container">
                        <!-- Services will be loaded dynamically based on category -->
                    </div>
                </div>

                <!-- Step 3: Employee Selection -->
                <div class="booking-step" id="step3">
                    <h3 class="mb-4">Seleccione el profesional</h3>
                    <div class="selected-service-name mb-3 fw-bold"></div>
                    <div class="row row-cols-1 row-cols-md-3 g-4" id="employees-container">
                        <!-- Employees will be loaded dynamically based on service -->
                    </div>
                </div>

                <!-- Step 4: Date and Time Selection -->
                <div class="booking-step" id="step4">
                    <h3 class="mb-4">Seleccione la modalidad, fecha y hora</h3>
                    <div class="selected-employee-name mb-3 fw-bold"></div>

                    <!-- MODALIDAD DE LA CITA -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2">
                            Modalidad de atención:
                        </label>

                        <div class="btn-group w-100" role="group" aria-label="Modalidad">
                            <input type="radio" class="btn-check" name="appointment_mode"
                                id="mode_presencial" value="presencial" checked>
                            <label class="btn btn-outline-primary" for="mode_presencial">
                                <i class="bi bi-geo-alt me-1"></i> Presencial
                            </label>

                            <input type="radio" class="btn-check" name="appointment_mode"
                                id="mode_virtual" value="virtual">
                            <label class="btn btn-outline-primary" for="mode_virtual">
                                <i class="bi bi-camera-video me-1"></i> Virtual
                            </label>
                        </div>

                        <!-- <small class="text-muted d-block mt-2">
                            La modalidad puede influir en la disponibilidad de horarios.
                        </small> -->
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <button class="btn btn-sm btn-outline-secondary" id="prev-month"><i
                                            class="bi bi-chevron-left"></i></button>
                                    <h5 class="mb-0" id="current-month">March 2023</h5>
                                    <button class="btn btn-sm btn-outline-secondary" id="next-month"><i
                                            class="bi bi-chevron-right"></i></button>
                                </div>
                                <div class="card-body">
                                    <table class="table table-calendar">
                                        <thead>
                                            <tr>
                                                <th>Dom</th>
                                                <th>Lun</th>
                                                <th>Mar</th>
                                                <th>Mié</th>
                                                <th>Jue</th>
                                                <th>Vie</th>
                                                <th>Sáb</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendar-body">
                                            <!-- Calendar will be generated dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Turnos disponibles</h5>
                                    <div id="selected-date-display" class="text-muted small"></div>
                                </div>
                                <!-- ALERTA SIEMPRE VISIBLE EN STEP 4 -->
                                <div class="alert alert-info m-3 mb-0" id="urgent-help-banner">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>¿Cita urgente u horario especial?</strong><br>
                                    Los turnos online se habilitan con <b>mínimo 24h</b> de anticipación. 
                                    Para atención hoy u otro horario, contáctenos al <a href="tel:+593939034743">0939034743</a>.
                                </div>
                                <div class="card-body">
                                    <div id="time-slots-container">
                                        <!-- Time slots will be loaded dynamically -->
                                        <div class="text-center text-muted w-100 py-4">
                                            Seleccione una fecha para visualizar los turnos disponibles
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Confirmation -->
                <div class="booking-step" id="step5">
                    <h3 class="mb-4">Ingreso de información</h3>
                    <div class="card">
                        <!-- <div class="card-header bg-light">
                            <h5 class="mb-0">Ingresa los datos del paciente</h5>
                        </div> -->
                        <div class="card-body">
                            <div class="form-section">
                                <h5 class="section-title"><i class="bi bi-person-lines-fill me-2"></i>Datos del paciente</h5>
                                <form id="customer-info-form">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="customer-name" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="customer-name"
                                                name="customer_name"
                                                placeholder="Ej: María José Pérez González"
                                                required
                                                minlength="5"
                                                pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)+$"
                                                title="Debe registrarse al menos un nombre y un apellido."
                                                autocomplete="name"
                                                >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="patient_dob" class="form-label">Fecha de nacimiento<span class="text-danger">*</span></label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="patient_dob"
                                                name="patient_dob"
                                                required
                                                title="Seleccione o escriba la fecha de nacimiento."
                                                >
                                                <small class="text-muted">Formato: día/mes/año</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="doc_type" class="form-label">Tipo de documento<span class="text-danger">*</span></label>
                                            <select class="form-select" id="doc_type" name="doc_type" required>
                                                <option value="cedula" selected>Cédula (Ecuador)</option>
                                                <option value="pasaporte">Pasaporte (Extranjero)</option>
                                            </select>
                                            <small class="text-muted">Para personas con nacionalidad ecuatoriana se utiliza cédula. Para personas extranjeras, pasaporte.
                                            </small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="doc_number" class="form-label">Número de documento<span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="doc_number"
                                                name="doc_number"
                                                required
                                            >
                                            </div>
                                        
                                        <div class="col-md-6">
                                            <label for="customer-email" class="form-label">Correo electrónico<span class="text-danger">*</span></label>
                                            <input
                                                type="email"
                                                class="form-control"
                                                id="customer-email"
                                                name="customer_email"
                                                placeholder="Ej: nombre@gmail.com"
                                                required
                                                minlength="6"
                                                title="Ingrese un correo válido (ej: nombre@gmail.com)."
                                                autocomplete="email"
                                                >
                                        </div>
                                        <div class="col-md-6">
                                            <label for="patient_phone_ui" class="form-label">Número de celular <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control phone-input" id="patient_phone_ui" placeholder="Ej: 991234567" required title="Registre el número de celular sin el prefijo del país. Verifique que el país seleccionado sea el correcto.">
                                            <input type="hidden" id="customer-phone" name="customer_phone">
                                            <div class="form-text">
                                                Para Ecuador, registre el número sin el 0 inicial.
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="patient_address" class="form-label">Dirección<span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="patient_address"
                                                name="patient_address"
                                                placeholder="Ej: Av. Amazonas y Naciones Unidas, edificio X"
                                                required
                                                minlength="6"
                                                title="Debe registrarse una dirección válida que contenga letras; puede incluir números."
                                                autocomplete="street-address"
                                                >
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="customer-notes" class="form-label">Comentario (Opcional)</label>
                                            <textarea
                                                class="form-control"
                                                id="customer-notes"
                                                name="customer_notes"
                                                rows="3"
                                                placeholder="Información adicional relevante para la atención"
                                            ></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="form-section">
                                <h5 class="section-title"><i class="bi bi-receipt me-2"></i>Datos de facturación</h5>
                                <form id="billing-info-form">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="billing-name" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                            <input
                                            type="text"
                                            class="form-control"
                                            id="billing-name"
                                            name="billing_name"
                                            placeholder="Ej: María José Pérez González / Empresa XYZ S.A."
                                            required
                                            minlength="5"
                                            pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9]+(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9]+)+$"
                                            title="Ingrese el nombre para facturación"
                                            autocomplete="name"
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="billing-doc-type" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="billing-doc-type" name="billing_doc_type" required>
                                            <option value="cedula" selected>Cédula (Ecuador)</option>
                                            <option value="ruc">RUC (Ecuador)</option>
                                            <option value="pasaporte">Pasaporte (Extranjero)</option>
                                            </select>
                                            <small class="text-muted">Para personas ecuatorianas se admite Cédula o RUC. Para personas extranjeras, Pasaporte.
                                            </small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="billing-doc-number" class="form-label">Número de documento <span class="text-danger">*</span></label>
                                            <input
                                            type="text"
                                            class="form-control"
                                            id="billing-doc-number"
                                            name="billing_doc_number"
                                            required
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="billing-email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                            <input
                                            type="email"
                                            class="form-control"
                                            id="billing-email"
                                            name="billing_email"
                                            placeholder="Ej: facturacion@gmail.com"
                                            required
                                            minlength="6"
                                            title="Ingrese un correo válido (ej: facturacion@gmail.com)."
                                            autocomplete="email"
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="billing_phone_ui" class="form-label">Número de celular <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control phone-input" id="billing_phone_ui" placeholder="Ej: 991234567" required title="Registre el número de celular sin el prefijo del país. Verifique que el país seleccionado sea el correcto.">
                                            <input type="hidden" id="billing-phone" name="billing_phone">
                                            <div class="form-text">
                                                Para Ecuador, registre el número sin el 0 inicial.
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="billing-address" class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <input
                                            type="text"
                                            class="form-control"
                                            id="billing-address"
                                            name="billing_address"
                                            placeholder="Ej: Av. Amazonas y Naciones Unidas, edificio X"
                                            required
                                            minlength="6"
                                            title="Debe registrarse una dirección válida que contenga letras; puede incluir números."
                                            autocomplete="street-address"
                                            >
                                        </div>
                                        </div>
                            </form>
                            </div>
                          
                            
                            
                            <div class="form-section">
                                <h5 class="section-title"><i class="bi bi-card-checklist me-2"></i>Resumen de la cita</h5>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Área de atención:</div>
                                        <div class="col-md-8" id="summary-category"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Servicio:</div>
                                        <div class="col-md-8" id="summary-service"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Profesional:</div>
                                        <div class="col-md-8" id="summary-employee"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Fecha y hora:</div>
                                        <div class="col-md-8" id="summary-datetime"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Duración:</div>
                                        <div class="col-md-8" id="summary-duration"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Precio:</div>
                                        <div class="col-md-8" id="summary-price"></div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Modalidad:</div>
                                        <div class="col-md-8" id="summary-mode"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="consent_data" name="consent_data" required>
                                <label class="form-check-label" for="consent_data">
                                    Autorizo el uso de los datos personales proporcionados para la gestión de la cita y el envío de información relacionada.
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

            <div class="booking-footer">
                <button class="btn btn-outline-secondary" id="prev-step" disabled>
                    <i class="bi bi-arrow-left"></i> Regresar
                </button>
                <button class="btn btn-primary" id="next-step">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <footer>
        <div class="container pb-2">
            <div class="row text-center">
            <span>Aplicación diseñada y desarrollada por <a target="_blank" href="https://www.daisyllivisaca.com">Daisy Llivisaca</a></span>
            </div>
        </div>
    </footer>

    <!-- Botón flotante Siguiente -->
    <button id="next-step-floating"
            class="btn btn-primary shadow-sm d-none"
            type="button">
        Siguiente <i class="bi bi-arrow-right"></i>
    </button>

    <!-- Success Modal -->
    <div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Booking Confirmed!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Thank You!</h4>
                    <p>Your appointment has been successfully booked.</p>
                    <div class="alert alert-info mt-3">
                        <p class="mb-0">A confirmation email has been sent to your email address.</p>
                    </div>
                    <div class="booking-details mt-4 text-start">
                        <h5>Booking Details:</h5>
                        <div id="modal-booking-details"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const stepTitles = {
            1: "Área de atención · FamySalud",
            2: "Servicio · FamySalud",
            3: "Profesional · FamySalud",
            4: "Modalidad, fecha y hora · FamySalud",
            5: "Datos del paciente · FamySalud",
            6: "Pago · FamySalud"
        };
        $(document).ready(function() {

            const categories = @json($categories);

            // 👇 NUEVO: mes/año actuales + días laborales del empleado
            let currentMonth;      // 0-11
            let currentYear;       // año completo
            let workingWeekdays = null; // [0..6] (0=Dom,1=Lun,...)
            let availableDatesSet = new Set();

            let availableDatesByMonth = {}; // cache: "YYYY-MM" => Set([...])
            let allowedMinYM = null;        // "YYYY-MM" (desde min_allowed)
            let allowedMaxYM = null;        // "YYYY-MM" (hasta max_allowed)

            // Días en español (minúsculas, sin tildes) para lógica/BD
            const diasES = ["domingo","lunes","martes","miercoles","jueves","viernes","sabado"];

            // Devuelve el día de semana en español (sin tildes) a partir de "YYYY-MM-DD"
            function getDiaSemanaES(dateStr) {
                // Evita problemas de zona horaria usando hora local fija
                const d = new Date(dateStr + "T00:00:00");
                return diasES[d.getDay()];
            }

            function fetchAvailableDatesForMonth(month0, year, options = {}) {
                if (!bookingState.selectedEmployee) return;

                const onlyCache = options.onlyCache === true;
                const employeeId = bookingState.selectedEmployee.id;
                const key = ymKey(year, month0);

                // Si ya está en caché, solo actualiza botones y listo
                if (availableDatesByMonth[key]) {
                    if (!onlyCache) {
                        availableDatesSet = availableDatesByMonth[key];
                    }
                    updateMonthNavButtons(currentMonth, currentYear);
                    return;
                }

                // Mientras llega: bloquea flechas (evita clic “rápido”)
                setMonthButtons(false, false);

                // Solo si estamos pintando el mes actual, bloquea celdas (para evitar clics)
                if (!onlyCache) {
                    $("#calendar-body td.calendar-day").each(function () {
                        const $cell = $(this);
                        if (!$cell.hasClass("disabled")) $cell.addClass("disabled");
                    });
                }

                $.ajax({
                    url: `/employees/${employeeId}/available-dates`,
                    data: { month: month0 + 1, year: year },
                    success: function (res) {
                        const dates = res.available_dates || [];
                        const setDates = new Set(dates);

                        // ✅ guarda caché
                        availableDatesByMonth[key] = setDates;

                        // ✅ actualiza rango permitido (min/max)
                        allowedMinYM = parseYMFromDateTime(res.min_allowed);
                        allowedMaxYM = parseYMFromDateTime(res.max_allowed);

                        // ✅ si es el mes que estamos viendo, úsalo para pintar
                        if (!onlyCache) {
                            availableDatesSet = setDates;

                            $("#calendar-body td.calendar-day").each(function () {
                                const $cell = $(this);
                                const dateStr = $cell.data("date");
                                if (!dateStr) return;

                                const lockedByRule = $cell.data("locked-by-rule") === true;
                                if (lockedByRule) return;

                                if (availableDatesSet.has(dateStr)) {
                                    $cell.removeClass("disabled");
                                } else {
                                    $cell.addClass("disabled").removeClass("selected");
                                }
                            });
                        }

                        // ✅ actualiza flechas (con caché ya disponible)
                        updateMonthNavButtons(currentMonth, currentYear);
                    },
                    error: function () {
                        // Si falla, deja todo bloqueado por seguridad
                        updateMonthNavButtons(currentMonth, currentYear);
                    }
                });
            }

            function markDaysWithoutSlots() {
                if (!bookingState.selectedEmployee) return;

                const employeeId = bookingState.selectedEmployee.id;

                // Recorre SOLO los días visibles del calendario
                $("#calendar-body td.calendar-day").each(function () {
                    const $cell = $(this);

                    // si ya está disabled por otras reglas (días no laborables, pasado, etc), no consultes
                    if ($cell.hasClass("disabled")) return;

                    const dateStr = $cell.data("date");
                    if (!dateStr) return;

                    $.ajax({
                        url: `/employees/${employeeId}/availability/${dateStr}`,
                        data: { dia_semana: getDiaSemanaES(dateStr) },
                        success: function (response) {
                            if (!response.available_slots || response.available_slots.length === 0) {
                                $cell.addClass("disabled").removeClass("selected");
                            }
                        },
                        error: function () {
                            // opcional: si falla la consulta, lo deshabilitas por seguridad
                            $cell.addClass("disabled").removeClass("selected");
                        }
                    });
                });
            }

            const container = $('#categories-container'); // Target the container by ID

            let html = '';
            $.each(categories, function(index, category) {
                html += `
            <div class="col">
                <div class="card border h-100 category-card text-center rounded p-2" data-category="${category.id}">
                    <div class="card-body">
                         ${category.image ? `<img class="img-fluid w-25 mb-2" src="uploads/images/category/${category.image}">` : ""}
                        <h5 class="card-title">${category.title}</h5>
                        <p class="card-text">${category.body}</p>
                    </div>
                </div>
            </div>
        `;
            });

            container.html(html); // Insert all generated HTML at once


            const employees = @json($employees);
            // console.log(employees);

            // Booking state
            let bookingState = {
                currentStep: 1,
                selectedCategory: null,
                selectedService: null,
                selectedEmployee: null,
                selectedDate: null,
                selectedTime: null,
                appointmentMode: 'presencial'
            };

            // Initialize the booking system
            updateProgressBar();
            generateCalendar();

            // Step navigation
            $("#next-step").click(function() {
                const currentStep = bookingState.currentStep;

                // Validate current step before proceeding
                if (!validateStep(currentStep)) {
                    return;
                }

                if (currentStep < 5) {
                    goToStep(currentStep + 1);
                } else {
                    // Submit booking
                    if ($("#customer-info-form")[0].checkValidity()) {
                        submitBooking();
                    } else {
                        $("#customer-info-form")[0].reportValidity();
                    }
                }
            });

            $("#prev-step").click(function() {
                if (bookingState.currentStep > 1) {
                    goToStep(bookingState.currentStep - 1);
                }
            });

            // Category selection
            $(document).on("click", ".category-card", function() {
                $(".category-card").removeClass("selected");
                $(this).addClass("selected");

                const categoryId = $(this).data("category");
                // console.log(categoryId);
                bookingState.selectedCategory = categoryId;

                // Reset subsequent selections
                bookingState.selectedService = null;
                bookingState.selectedEmployee = null;
                bookingState.selectedDate = null;
                bookingState.selectedTime = null;

                // Update the service step with services for this category
                updateServicesStep(categoryId);
            });

            // Service selection
            $(document).on("click", ".service-card", function() {
                $(".service-card").removeClass("selected");
                $(this).addClass("selected");

                const serviceId = $(this).data("service");
                const serviceTitle = $(this).find('.card-title').text();
                // const servicePrice = $(this).find('.fw-bold').text().replace('$', '');
                const servicePrice = $(this).find('.fw-bold').text();
                const serviceDuration = $(this).find('.card-text:contains("Duration:")').text().replace(
                    'Duration: ', '');

                // Store the selected service in booking state
                bookingState.selectedService = {
                    id: serviceId,
                    title: serviceTitle,
                    price: servicePrice,
                    duration: serviceDuration
                };

                // Reset subsequent selections
                bookingState.selectedEmployee = null;
                bookingState.selectedDate = null;
                bookingState.selectedTime = null;

                // Clear previous selections UI
                $(".employee-card").removeClass("selected");
                $("#selected-date").text("");
                $("#selected-time").text("");
                $("#employees-container").empty(); // Clear previous employees while loading new ones

                // Show loading state for employees
                $("#employees-container").html(
                    '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );

                // Update the employee step with employees for this service
                updateEmployeesStep(serviceId);

                // Show the employee step immediately (loading will happen inside updateEmployeesStep)
                $("#services-step").addClass("d-none");
                $("#employees-step").removeClass("d-none");
                $(".step-indicator[data-step='services']").removeClass("active current").addClass(
                    "completed");
                $(".step-indicator[data-step='employees']").addClass("active current");
            });

            // Employee selection
            $(document).on("click", ".employee-card", function() {
                $(".employee-card").removeClass("selected");
                $(this).addClass("selected");

                // 1) Asegurar que el id sea numérico
                const employeeId = Number($(this).data("employee"));

                //const employeeId = $(this).data("employee");
                // alert(employeeId);
                //const employee = employees.find(e => e.id === employeeId);
                //const employee = employees.find(e => e.id === Number(employeeId));
                const employee = employees.find(e => Number(e.id) === employeeId);

                if (!employee) {
                    console.error('Empleado no encontrado para id:', employeeId, employees);
                    return;
                }

                bookingState.selectedEmployee = employee;

                availableDatesByMonth = {};
                availableDatesSet = new Set();
                allowedMinYM = null;
                allowedMaxYM = null;
                setMonthButtons(false, false);

                 // 3) Calcular qué días de la semana trabaja
                workingWeekdays = null;
                try {
                    let daysConfig = null;

                    // Si viene como string JSON
                    if (typeof employee.days === 'string' && employee.days.trim() !== '') {
                        daysConfig = JSON.parse(employee.days);

                    // Si ya viene como objeto/array desde Laravel
                    } else if (employee.days && typeof employee.days === 'object') {
                        daysConfig = employee.days;
                    }

                    //Cuidado al editar aquí porque habilita clic  a todos los dias de la semana
                    if (daysConfig) {
                        const map = {
                            domingo: 0,
                            lunes: 1,
                            martes: 2,
                            miercoles: 3,
                            jueves: 4,
                            viernes: 5,
                            sabado: 6
                        };

                        workingWeekdays = [];

                        Object.entries(daysConfig).forEach(([dayName, slots]) => {
                            if (Array.isArray(slots) && slots.length > 0 && map.hasOwnProperty(dayName)) {
                                workingWeekdays.push(map[dayName]);
                            }
                        });
                    }

                    console.log('Días laborales del profesional:', workingWeekdays);
                } catch (e) {
                    console.error('Error al procesar employee.days:', e, employee.days);
                    workingWeekdays = null;
                }

                // 4) Volver a dibujar el calendario con esos días bloqueados
                if (typeof currentMonth !== 'undefined' && typeof currentYear !== 'undefined') {
                    renderCalendar(currentMonth, currentYear);
                }

                // Resetear selecciones posteriores
                bookingState.selectedDate = null;
                bookingState.selectedTime = null;
                $(".calendar-day").removeClass("selected");
                $(".time-slot").removeClass("selected");

                // Mostrar mensaje inicial en los turnos
                $("#time-slots-container").html(`
                    <div class="text-center w-100 py-4">
                        <div class="alert alert-info">
                            <i class="bi bi-calendar-event me-2"></i>
                            Por favor seleccione una fecha para ver los turnos disponibles
                        </div>
                    </div>
                `);
            });
                
            // ================================
            // MODALIDAD DE LA CITA (NO AFECTA HORARIOS)
            // ================================
            $(document).on('change', 'input[name="appointment_mode"]', function () {
                bookingState.appointmentMode = this.value;

                console.log('Modalidad seleccionada:', bookingState.appointmentMode);
            });
              


            // Date selection
            $(document).on("click", ".calendar-day:not(.disabled)", function() {
                $(".calendar-day").removeClass("selected");
                $(this).addClass("selected");

                const date = $(this).data("date");
                bookingState.selectedDate = date;

                // Reset time selection
                bookingState.selectedTime = null;

                // Update time slots based on employee availability
                updateTimeSlots(date);
            });

            // Time slot selection
            $(document).on("click", ".time-slot:not(.disabled)", function() {
                // Retry button (Intentar de nuevo)
                $(document).on('click', '.btn-retry-timeslots', function() {
                    const date = $(this).data('date');
                    updateTimeSlots(date);
                });

                $(".time-slot").removeClass("selected");
                $(this).addClass("selected");

                const time = $(this).data("time");
                bookingState.selectedTime = time;
            });

            // Calendar navigation
            $("#prev-month").click(function() {
                navigateMonth(-1);
            });

            $("#next-month").click(function() {
                navigateMonth(1);
            });

            // Volver al paso 1 al hacer clic en el logo
            $("#logo-reset").on("click", function (e) {
                e.preventDefault();

                if (bookingState.selectedService || bookingState.selectedEmployee) {
                    if (!confirm("Al volver al inicio, se mantendrá la información ingresada. ¿Desea continuar?")) {
                        return;
                    }
                }
                goToStep(1);
            });

            // Functions
            function goToStep(step) {
                // Hide all steps
                $(".booking-step").removeClass("active");

                // Show the target step
                $(`#step${step}`).addClass("active");

                // Update the step indicators
                $(".step").removeClass("active completed");

                for (let i = 1; i <= 5; i++) {
                    if (i < step) {
                        $(`.step[data-step="${i}"]`).addClass("completed");
                    } else if (i === step) {
                        $(`.step[data-step="${i}"]`).addClass("active");
                    }
                }

                // Update the current step
                bookingState.currentStep = step;

                // Update the navigation buttons
                updateNavigationButtons();

                // Update the progress bar
                updateProgressBar();

                // If we're on the confirmation step, update the summary
                if (step === 5) {
                    updateSummary();
                }

                // Scroll to top of booking container
                $(".booking-container")[0].scrollIntoView({
                    behavior: "smooth"
                });

                 if (stepTitles[step]) {
                    document.title = stepTitles[step];
                }

                // Mostrar alerta de cita urgente SOLO en el paso 4
                if (step === 4) {
                    $("#urgent-help-banner").show();
                } else {
                    $("#urgent-help-banner").hide();
                }
            }


            function updateProgressBar() {
                const progress = ((bookingState.currentStep - 1) / 4) * 100;
                $(".progress-bar-steps .progress").css("width", `${progress}%`);
            }


            function updateNavigationButtons() {
                // Enable/disable previous button
                if (bookingState.currentStep === 1) {
                    $("#prev-step").prop("disabled", true);
                } else {
                    $("#prev-step").prop("disabled", false);
                }

                // Update next button text
                if (bookingState.currentStep === 5) {
                    $("#next-step").html('Ir a pagar <i class="bi bi-arrow-right"></i>');
                } else {
                    $("#next-step").html('Siguiente <i class="bi bi-arrow-right"></i>');
                }
            }


            function validateStep(step) {
                switch (step) {
                    case 1:
                        if (!bookingState.selectedCategory) {
                            alert("Por favor selecciona un área de atención");
                            return false;
                        }
                        return true;
                    case 2:
                        if (!bookingState.selectedService) {
                            alert("Por favor selecciona un servicio");
                            return false;
                        }
                        return true;
                    case 3:
                        if (!bookingState.selectedEmployee) {
                            alert("Por favor selecciona a un profesional");
                            return false;
                        }
                        return true;
                    case 4:
                        if (!bookingState.selectedDate) {
                            alert("Por favor selecciona una fecha");
                            return false;
                        }
                        if (!bookingState.selectedTime) {
                            alert("Por favor selecciona un turno");
                            return false;
                        }
                        return true;
                    case 5:
                        const consent = document.getElementById("consent_data");
                        if (!consent || !consent.checked) {
                            alert("Debes autorizar el uso de tus datos personales para continuar.");
                            return false;
                        }
                        return true;
                    default:
                        return true;
                }
            }


            function updateServicesStep(categoryId) {
                // Show loading state if needed
                $("#services-container").html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );

                // Make AJAX request to get services for this category
                $.ajax({
                    url: `/categories/${categoryId}/services`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.services) {
                            const services = response.services;

                            // Update category name display
                            $(".selected-category-name").text(
                                `Área seleccionada: ${services[0]?.category?.title || ''}`);

                            // Clear services container
                            $("#services-container").empty();

                            // Add services with animation delay
                            services.forEach((service, index) => {
                                // Determine the price display
                                let priceDisplay;
                                if (service.sale_price) {
                                    // If sale price exists, show both with strike-through on original price
                                    priceDisplay =
                                        `<span class="text-decoration-line-through text-muted">${service.price}</span> <span class=" fw-bold">Efectivo / Transferencia: ${service.sale_price}</span>`;
                                } else {
                                    // If no sale price, just show regular price normally
                                    priceDisplay =
                                        `<span class="fw-bold">Efectivo / Transferencia: ${service.price}</span>`;
                                }

                                const serviceCard = `
                                    <div class="col animate-slide-in" style="animation-delay: ${index * 100}ms">
                                        <div class="card border h-100 service-card text-center p-2" data-service="${service.id}">
                                            <div class="card-body">
                                                ${service.image ? `<img class="img-fluid rounded mb-2" src="uploads/images/service/${service.image}">` : ""}
                                                <h5 class="card-title mb-1">${service.title}</h5>
                                                <p class="card-text mb-1">${service.excerpt}</p>
                                                <p class="card-text">${priceDisplay}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                $("#services-container").append(serviceCard);
                            });
                        } else {
                            $("#services-container").html(
                                '<div class="col-12 text-center py-5"><p>No services available for this category.</p></div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $("#services-container").html(
                            '<div class="col-12 text-center py-5"><p>Error loading services. Please try again.</p></div>'
                        );
                    }
                });
            }



            function updateEmployeesStep(serviceId) {
                // Show loading state
                $("#employees-container").html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );

                // Make AJAX request to get employees for this service
                $.ajax({
                    url: `/services/${serviceId}/employees`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.employees) {
                            const employees = response.employees;
                            const service = response.service;

                            // Determine the price display
                            let priceDisplay;
                            if (service.sale_price) {
                                // If sale price exists, show both with strike-through on original price
                                priceDisplay =
                                    `<span class="">${service.sale_price}</span>`;
                            } else {
                                // If no sale price, just show regular price normally
                                priceDisplay =
                                    `<span class="fw-bold">${service.price}</span>`;
                            }

                            // Update service name display
                            $(".selected-service-name").html(
                                `Servicio seleccionado: ${service.title} (${bookingState.selectedService.price})`
                                );

                            // Clear employees container
                            $("#employees-container").empty();

                            // Add employees with animation delay
                            employees.forEach((employee, index) => {
                                const employeeCard = `
                                <div class="col animate-slide-in" style="animation-delay: ${index * 100}ms">
                                    <div class="card border h-100 employee-card text-center p-2" data-employee="${employee.id}">
                                        <div class="card-body">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                                ${employee.user.image ?
                                                    `<img src="uploads/images/profile/${employee.user.image}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">` :
                                                    `<i class="bi bi-person text-primary" style="font-size: 2rem;"></i>`
                                                }
                                            </div>
                                            <h5 class="card-title">${employee.user.name}</h5>
                                            <p class="card-text text-muted">${employee.position || 'Profesional'}</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                                $("#employees-container").append(employeeCard);
                            });
                        } else {
                            $("#employees-container").html(
                                '<div class="col-12 text-center py-5"><p>No employees available for this service.</p></div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $("#employees-container").html(
                            '<div class="col-12 text-center py-5"><p>Error loading employees. Please try again.</p></div>'
                        );
                    }
                });
            }

            function generateCalendar() {
                const today = new Date();
                currentMonth = today.getMonth();
                currentYear = today.getFullYear();

                renderCalendar(currentMonth, currentYear);
            }

            function renderCalendar(month, year) {
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDay = firstDay.getDay(); // 0 = Sunday

                // Update month display
                const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                    "Septiembre", "Octubre", "Noviembre", "Diciembre"
                ];
                $("#current-month").text(`${monthNames[month]} ${year}`);

                // Clear calendar
                $("#calendar-body").empty();

                // Build calendar
                let date = 1;
                for (let i = 0; i < 6; i++) {
                    // Create a table row
                    const row = $("<tr></tr>");

                    // Create cells for each day of the week
                    for (let j = 0; j < 7; j++) {
                        if (i === 0 && j < startingDay) {
                            // Empty cells before the first day of the month
                            row.append("<td></td>");
                        } else if (date > daysInMonth) {
                            // Break if we've reached the end of the month
                            break;
                        } else {
                            // Create a cell for this date
                            const today = new Date();
                            const cellDate = new Date(year, month, date);
                            const formattedDate =
                                `${year}-${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;

                            // Check if this date is in the past
                            const isPast = cellDate < new Date(today.setHours(0, 0, 0, 0));

                            // 👇 NUEVO: deshabilitar por disponibilidad semanal
                            let isDisabledBySchedule = false;
                            if (Array.isArray(workingWeekdays) && workingWeekdays.length > 0) {
                                const weekday = cellDate.getDay(); // 0=Dom,1=Lun,...6=Sab
                                if (!workingWeekdays.includes(weekday)) {
                                    isDisabledBySchedule = true;
                                }
                            }

                            // Build classes
                            let classes = 'text-center calendar-day';
                            if (isPast || isDisabledBySchedule) {
                                classes += ' disabled';
                            }

                            // Create the cell
                            const lockedByRule = (isPast || isDisabledBySchedule);
                            const cell = $(
                            `<td class="${classes}" data-date="${formattedDate}" data-locked-by-rule="${lockedByRule}">${date}</td>`
                            );

                            row.append(cell);
                            date++;
                        }
                    }

                    // Add the row to the calendar if it has cells
                    if (row.children().length > 0) {
                        $("#calendar-body").append(row);
                    }
                }
                if (bookingState.selectedEmployee) {
                    fetchAvailableDatesForMonth(month, year);
                }
            }

            function navigateMonth(direction) {
                const currentMonthText = $("#current-month").text();
                setMonthButtons(false, false); // bloquea mientras carga disponibilidad
                const [monthName, year] = currentMonthText.split(" ");

                const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
                    "Septiembre", "Octubre", "Noviembre", "Diciembre"
                ];
                currentMonth  = monthNames.indexOf(monthName);
                currentYear = parseInt(year);

                currentMonth += direction;

                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear  --;
                } else if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }

                renderCalendar(currentMonth, currentYear);
            }


            function updateCalendar() {
                // Update employee name display
                const employee = bookingState.selectedEmployee;
                $(".selected-employee-name").text(`Profesional seleccionado: ${employee.user.name}`);

                // Clear previous selections
                bookingState.selectedDate = null;
                bookingState.selectedTime = null;
                $(".calendar-day").removeClass("selected");
                $(".time-slot").removeClass("selected");

                // Show loading state for time slots
                $("#time-slots-container").html(`
                <div class="text-center w-100 py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            }

            function updateCalendar() {
                // Update employee name display
                const employee = bookingState.selectedEmployee;
                $(".selected-employee-name").text(`Profesional seleccionado: ${employee.user.name}`);

                // Clear previous selections
                bookingState.selectedDate = null;
                bookingState.selectedTime = null;
                $(".calendar-day").removeClass("selected");
                $(".time-slot").removeClass("selected");

                // Show initial state instead of loading spinner
                $("#time-slots-container").html(`
                    <div class="text-center w-100 py-4">
                        <div class="alert alert-info">
                            <i class="bi bi-calendar-event me-2"></i>
                            Por favor selecciona una fecha para ver los turnos disponibles
                        </div>
                    </div>
                `);
            }

            // Formatea una fecha JS a YYYY-MM-DD en horario local
            function formatLocalDate(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function updateMonthNavButtons(month0, year) {
            // Si no hay empleado, no hay navegación
            if (!bookingState.selectedEmployee) {
                setMonthButtons(false, false);
                return;
            }

            // Si todavía no sabemos el rango permitido (min/max), bloquea mientras llega
            if (!allowedMinYM || !allowedMaxYM) {
                setMonthButtons(false, false);
                return;
            }

            const currentYM = ymKey(year, month0);

            // Si solo hay 1 mes en el rango permitido, no hay flechas
            if (allowedMinYM === allowedMaxYM) {
                setMonthButtons(false, false);
                return;
            }

            // Determina meses vecinos
            const prev = prevMonth(year, month0);
            const next = nextMonth(year, month0);
            const prevYM = ymKey(prev.year, prev.month0);
            const nextYM = ymKey(next.year, next.month0);

            // Por rango, solo permitimos movernos entre minYM y maxYM
            // Habilitar prev si hay un mes anterior dentro del rango y con fechas disponibles
            // Habilitar next si hay un mes siguiente dentro del rango y con fechas disponibles
            let prevEnabled = false;
            let nextEnabled = false;

            // Prev: solo si el mes anterior NO está antes del mínimo permitido
            if (prevYM >= allowedMinYM && prevYM <= allowedMaxYM) {
                const cachedPrev = availableDatesByMonth[prevYM];
                if (cachedPrev) {
                    prevEnabled = cachedPrev.size > 0;
                } else {
                    // mientras consulta, lo deja apagado para que no “parpadee”
                    prevEnabled = false;
                    fetchAvailableDatesForMonth(prev.month0, prev.year, { onlyCache: true });
                }
            }

            // Next: solo si el mes siguiente NO está después del máximo permitido
            if (nextYM >= allowedMinYM && nextYM <= allowedMaxYM) {
                const cachedNext = availableDatesByMonth[nextYM];
                if (cachedNext) {
                    nextEnabled = cachedNext.size > 0;
                } else {
                    nextEnabled = false;
                    fetchAvailableDatesForMonth(next.month0, next.year, { onlyCache: true });
                }
            }

            // Regla adicional: si estás en el mes mínimo, no debes retroceder más
            if (currentYM === allowedMinYM) prevEnabled = false;

            // Regla adicional: si estás en el mes máximo, no debes avanzar más
            if (currentYM === allowedMaxYM) nextEnabled = false;

            setMonthButtons(prevEnabled, nextEnabled);
        }

            function ymKey(year, month0) {
                return `${year}-${String(month0 + 1).padStart(2, '0')}`; // month0: 0-11
            }

            function parseYMFromDateTime(dateTimeStr) {
                // "2025-12-16 10:00:00" o "2025-12-16T10:00:00"
                if (!dateTimeStr) return null;
                const d = dateTimeStr.substring(0, 10); // YYYY-MM-DD
                return d.substring(0, 7); // YYYY-MM
            }

            function prevMonth(year, month0) {
                if (month0 === 0) return { year: year - 1, month0: 11 };
                return { year, month0: month0 - 1 };
            }

            function nextMonth(year, month0) {
                if (month0 === 11) return { year: year + 1, month0: 0 };
                return { year, month0: month0 + 1 };
            }

            function setMonthButtons(prevEnabled, nextEnabled) {
                $("#prev-month").prop("disabled", !prevEnabled);
                $("#next-month").prop("disabled", !nextEnabled);
            }

            function updateTimeSlots(selectedDate) {
                if (!selectedDate) {
                    $("#time-slots-container").html(`
                    <div class="text-center w-100 py-4">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No date selected
                        </div>
                    </div>
                `);
                    return;
                }

                const employeeId = bookingState.selectedEmployee.id;
                //const apiDate = new Date(selectedDate).toISOString().split('T')[0];
                const apiDate = selectedDate;
                const dia_semana = getDiaSemanaES(selectedDate); // "lunes", "martes", etc.
                // Show loading state only when actually fetching
                $("#time-slots-container").html(`
                    <div class="text-center w-100 py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Revisando disponibilidad...</div>
                    </div>
                `);

                $.ajax({
                    url: `/employees/${employeeId}/availability/${apiDate}`,
                    data: { dia_semana: dia_semana }, // se lo mandas al backend
                    success: function(response) {
                        $("#time-slots-container").empty();

                        if (response.available_slots.length === 0) {
                            $("#time-slots-container").html(`
                    <div class="text-center py-4">
                        <div class="alert alert-warning">
                            <i class="bi bi-clock-history me-2"></i>
                            No hay turnos disponibles para esta fecha
                        </div>
                        
                    </div>
                `);
                            return;
                        }

                        // Add slot duration info
                        $("#time-slots-container").append(`
                            <div class="slot-info mb-3 w-100">
                                <div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Duración: ${response.slot_duration} minutos
                                    </small>
                                </div>

                                <div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-clock me-1"></i>
                                        Todos los turnos están en hora local de Ecuador (GMT-5)
                                    </small>
                                </div>
                            </div>
                        `);

                        // Add each time slot
                        const $slotsContainer = $("<div class='slots-grid'></div>");
                        response.available_slots.forEach(slot => {
                        // ¿Es hoy esta fecha?
                        const todayStr = formatLocalDate(new Date());
                        const isToday = (selectedDate === todayStr);

                        // ¿Debe deshabilitarse por estar a menos de 3 horas?
                        let disableByTime = false;
                        if (isToday) {
                            disableByTime = isSlotLessThan3HoursAhead(selectedDate, slot.start);
                        }

                        const extraClass = disableByTime ? ' disabled' : '';

                        const slotElement = $(`
                            <div class="time-slot btn btn-outline-primary mb-2${extraClass}"
                                data-start="${slot.start}"
                                data-end="${slot.end}"
                                title="Seleccionar ${slot.display}"
                                data-time="${slot.display}">
                                <i class="bi bi-clock me-1"></i>
                                ${slot.display}
                            </div>
                        `);

                        // Click local: por si quieres mantenerlo
                        slotElement.on('click', function() {
                            if ($(this).hasClass('disabled')) return; // seguridad extra

                            $(".time-slot").removeClass("selected active");
                            $(this).addClass("selected active");
                            bookingState.selectedTime = {
                                start: $(this).data('start'),
                                end: $(this).data('end'),
                                display: $(this).text().trim()
                            };
                            updateBookingSummary && updateBookingSummary();
                        });

                        $slotsContainer.append(slotElement);
                    });
                        $("#time-slots-container").append($slotsContainer);
                    },
                    error: function(xhr) {
                        $("#time-slots-container").html(`
                            <div class="text-center py-4">
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-octagon me-2"></i>
                                    Error al cargar los turnos disponibles
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-2 btn-retry-timeslots" 
                                    data-date="${selectedDate}">
                                <i class="bi bi-arrow-repeat me-1"></i> Intentar de nuevo
                                </button>
                            </div>
                        `);
                    }
                });
            }



            function updateSummary() {
                // Find the selected category
                const selectedCategory = categories.find(cat => cat.id == bookingState.selectedCategory);

                // Update summary with booking details
                $("#summary-category").text(selectedCategory ? selectedCategory.title : 'Not selected');

                // Update service info - using the stored service object
                if (bookingState.selectedService) {
                    $("#summary-service").text(
                        `${bookingState.selectedService.title} (${bookingState.selectedService.price})`);
                    $("#summary-duration").text(`${bookingState.selectedEmployee.slot_duration} minutes`);
                    $("#summary-price").text(bookingState.selectedService.price);
                }

                // Update employee info
                if (bookingState.selectedEmployee) {
                    $("#summary-employee").text(bookingState.selectedEmployee.user.name);
                }

                // Update date/time info
                if (bookingState.selectedDate && bookingState.selectedTime) {
                    const formattedDate = new Date(bookingState.selectedDate).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    $("#summary-datetime").text(
                        `${formattedDate} at ${bookingState.selectedTime.display || bookingState.selectedTime}`);
                }
                $("#summary-mode").text(
                    bookingState.appointmentMode === 'virtual' ? 'Virtual' : 'Presencial'
                );
            }



            // function submitBooking() {

            function submitBooking() {
                // Get form data
                const form = $('#customer-info-form');
                const csrfToken = form.find('input[name="_token"]').val(); // Get CSRF token from form

                // Prepare booking data
                const bookingData = {
                    employee_id: bookingState.selectedEmployee.id,
                    service_id: bookingState.selectedService.id,
                    name: $('#customer-name').val(),
                    email: $('#customer-email').val(),
                    phone: $('#customer-phone').val(),
                    notes: $('#customer-notes').val(),
                    amount: parseFloat(bookingState.selectedService.price.replace(/[^0-9.]/g, '')),
                    booking_date: bookingState.selectedDate,
                    booking_time: bookingState.selectedTime.start || bookingState.selectedTime,
                    status: 'Pending payment',
                    appointment_mode: bookingState.appointmentMode,
                    _token: csrfToken // Include CSRF token in payload
                };

                // Add user_id if authenticated (using JavaScript approach)
                if (typeof currentAuthUser !== 'undefined' && currentAuthUser) {
                    bookingData.user_id = currentAuthUser.id;
                }

                // Show loading state
                const nextBtn = $("#next-step");
                nextBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...'
                );

                // Submit via AJAX
                $.ajax({
                    url: '/bookings',
                    method: 'POST',
                    data: bookingData,
                    success: function(response) {
                        // Update modal with booking details
                        const formattedDate = new Date(bookingState.selectedDate).toLocaleDateString(
                            'en-US', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                        const bookingDetails = `
                                <div class="mb-2"><strong>Customer:</strong> ${$("#customer-name").val()}</div>
                                <div class="mb-2"><strong>Service:</strong> ${bookingState.selectedService.title}</div>
                                <div class="mb-2"><strong>Staff:</strong> ${bookingState.selectedEmployee.user.name}</div>
                                <div class="mb-2"><strong>Date & Time:</strong> ${formattedDate} at ${bookingState.selectedTime.display || bookingState.selectedTime}</div>
                                 <div class="mb-2"><strong>Amount:</strong> ${bookingState.selectedService.price}</div>
                                <div><strong>Reference:</strong> ${response.booking_id || 'BK-' + Math.random().toString(36).substr(2, 8).toUpperCase()}</div>
                            `;

                        $('#modal-booking-details').html(bookingDetails);

                        // Show success modal
                        const successModal = new bootstrap.Modal('#bookingSuccessModal');
                        successModal.show();

                        // Reset form after delay
                        setTimeout(resetBooking, 1000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Agendamiento fallido. Por favor, intente de nuevo.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422) {
                            errorMessage = 'Error de validación: Por favor revise la información.';
                        }

                        alert(errorMessage);
                        nextBtn.prop('disabled', false).html(
                            'Ir a pagar <i class="bi bi-arrow-right"></i>');
                    },
                    complete: function() {
                        // Re-enable button if request fails
                        if (nextBtn.prop('disabled')) {
                            setTimeout(() => {
                                nextBtn.prop('disabled', false).html(
                                    'Ir a pagar <i class="bi bi-arrow-right"></i>');
                            }, 2000);
                        }
                    }
                });
            }

            function resetBooking() {
                // Reset booking state
                bookingState = {
                    currentStep: 1,
                    selectedCategory: null,
                    selectedService: null,
                    selectedEmployee: null,
                    selectedDate: null,
                    selectedTime: null
                };

                // Reset UI
                $(".category-card, .service-card, .employee-card, .calendar-day, .time-slot").removeClass(
                    "selected");
                $("#customer-info-form")[0].reset();

                // Go to first step
                goToStep(1);
            }
            // ================================
            // BOTÓN FLOTANTE "SIGUIENTE"
            // ================================
            const $nextFloating = $("#next-step-floating");
            const nextBtn = document.getElementById("next-step");

            function updateFloatingPosition() {
                // 1) Intentar anclar al título del step activo
                const step = bookingState.currentStep;
                const titleEl = document.querySelector(`#step${step} h3`);

                // 2) Si por alguna razón no existe, usa el stepper como respaldo
                const fallbackEl = document.querySelector(".booking-steps") || document.querySelector(".booking-header");

                const refEl = titleEl || fallbackEl;
                if (!refEl) return;

                const r = refEl.getBoundingClientRect();

                // ✅ Queremos que quede cerca del título (un poco más abajo)
                const extraOffset = 60; // <-- sube/baja aquí (prueba 80 si lo quieres aún más abajo)
                const desiredTop = Math.round(r.top + extraOffset);

                // Evitar que se salga de pantalla
                const maxTop = window.innerHeight - 90;
                const finalTop = Math.max(12, Math.min(desiredTop, maxTop));

                $nextFloating.css("top", finalTop + "px");
            }

            function isElementInViewport(el) {
            if (!el) return false;
            const r = el.getBoundingClientRect();
            return r.top < window.innerHeight && r.bottom > 0;
            }

            function canAdvanceCurrentStep() {
            const step = bookingState.currentStep;

            if (step === 1) return !!bookingState.selectedCategory;
            if (step === 2) return !!bookingState.selectedService;
            if (step === 3) return !!bookingState.selectedEmployee;
            if (step === 4) return !!bookingState.selectedDate && !!bookingState.selectedTime;
            if (step === 5) return document.getElementById("consent_data")?.checked === true;

            return false;
            }

            function updateFloatingNext() {
                const footerVisible = isElementInViewport(nextBtn);
                const canAdvance = canAdvanceCurrentStep();

                if (canAdvance && !footerVisible && !nextBtn.disabled) {
                    $nextFloating.removeClass("d-none");
                    $nextFloating.html($("#next-step").html());

                    // 👇 NUEVO: ajusta la posición según pantalla y stepper
                    updateFloatingPosition();
                } else {
                    $nextFloating.addClass("d-none");
                }
            }

            // Click del botón flotante = click real
            $nextFloating.on("click", function () {
                $("#next-step").trigger("click");
            });

            // Eventos que actualizan visibilidad
            $(window).on("scroll resize", function () {
                updateFloatingNext();
                updateFloatingPosition();
            });

            $(document).on(
                "click",
                ".category-card, .service-card, .employee-card, .calendar-day:not(.disabled), .time-slot:not(.disabled)",
                function () {
                    setTimeout(updateFloatingNext, 0);
                }
            );

            // Hook seguro a goToStep
            const _goToStepOriginal = goToStep;
                goToStep = function (step) {
                _goToStepOriginal(step);
                setTimeout(updateFloatingNext, 0);
            };

            // Primera evaluación
            setTimeout(updateFloatingNext, 0);
        });
    </script>

    <!-- VALIDACIONES PERSONALIZADAS FORM STEP 5 -->
    <script>
      (function () {
        const allowedDomains = [
          "gmail.com", "outlook.com", "hotmail.com", "yahoo.com",
          "live.com", "icloud.com", "proton.me", "protonmail.com"
        ];

        const nameEl = document.getElementById("customer-name");
        const addressEl = document.getElementById("patient_address");
        const emailEl = document.getElementById("customer-email");

        if (!nameEl || !addressEl || !emailEl) return;

        // Nombre: mínimo nombre + apellido, solo letras
        nameEl.addEventListener("input", () => {
          const v = nameEl.value.trim().replace(/\s+/g, " ");
          const ok = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)+$/.test(v);
          nameEl.setCustomValidity(
            ok ? "" : "Debe registrarse al menos un nombre y un apellido."
          );
        });

        // Dirección: NO puede ser solo números
        addressEl.addEventListener("input", () => {
          const v = addressEl.value.trim();
          const hasLetter = /[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/.test(v);
          addressEl.setCustomValidity(
            hasLetter ? "" : "La dirección debe contener letras (no solo números)."
          );
        });

        // Email: dominio permitido
        emailEl.addEventListener("blur", () => {
          const v = emailEl.value.trim().toLowerCase();
          const domain = v.split("@")[1] || "";
          const ok = allowedDomains.includes(domain);
          emailEl.setCustomValidity(
            ok ? "" : "Use un correo con dominio válido (gmail, outlook, hotmail, yahoo, etc.)."
          );
        });
      })();
    </script>

    <script>
        (function () {
            const docType = document.getElementById("doc_type");
            const docNum  = document.getElementById("doc_number");
            if (!docType || !docNum) return;

            function applyDocRules() {
            const type = docType.value;

            if (type === "cedula") {
                docNum.placeholder = "10 dígitos (Ej: 0912345678)";
                docNum.inputMode = "numeric";
                docNum.maxLength = 10;
                docNum.minLength = 10;
                docNum.pattern = "^\\d{10}$";
                docNum.title = "La cédula debe tener exactamente 10 dígitos (solo números).";
            } else {
                docNum.placeholder = "Ej: AB1234567 (sin espacios)";
                docNum.inputMode = "text";
                docNum.maxLength = 15;
                docNum.minLength = 6;
                docNum.pattern = "^[A-Za-z0-9]{6,15}$";
                docNum.title = "El pasaporte debe tener entre 6 y 15 caracteres (letras y/o números), sin espacios.";
            }

            docNum.setCustomValidity("");
            }

            docNum.addEventListener("input", () => {
            docNum.value = docNum.value.replace(/\s+/g, "");
            });

            docType.addEventListener("change", applyDocRules);
            applyDocRules();
        })();
    </script>

    <script>
        (function () {
            const docType = document.getElementById("billing-doc-type");
            const docNum  = document.getElementById("billing-doc-number");
            if (!docType || !docNum) return;

            function applyBillingDocRules() {
            const type = docType.value;

            docNum.value = "";
            docNum.setCustomValidity("");

            if (type === "cedula") {
                docNum.placeholder = "10 dígitos (Ej: 0912345678)";
                docNum.inputMode = "numeric";
                docNum.maxLength = 10;
                docNum.minLength = 10;
                docNum.pattern = "^\\d{10}$";
                docNum.title = "La cédula debe tener exactamente 10 dígitos (solo números).";
            } else if (type === "ruc") {
                docNum.placeholder = "13 dígitos (Ej: 1790012345001)";
                docNum.inputMode = "numeric";
                docNum.maxLength = 13;
                docNum.minLength = 13;
                docNum.pattern = "^\\d{13}$";
                docNum.title = "El RUC debe tener exactamente 13 dígitos (solo números).";
            } else {
                docNum.placeholder = "Ej: AB1234567 (sin espacios)";
                docNum.inputMode = "text";
                docNum.maxLength = 15;
                docNum.minLength = 6;
                docNum.pattern = "^[A-Za-z0-9]{6,15}$";
                docNum.title = "El pasaporte debe tener entre 6 y 15 caracteres (letras y/o números), sin espacios.";
            }
            }

            // quitar espacios siempre (por si pegan con espacio)
            docNum.addEventListener("input", () => {
            docNum.value = docNum.value.replace(/\s+/g, "");
            });

            docType.addEventListener("change", applyBillingDocRules);
            applyBillingDocRules();
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js"></script>

    <script>
        (function () {
            function setupIntlPhone(inputId, hiddenId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            if (!input || !hidden || typeof window.intlTelInput !== "function") return;

            const iti = window.intlTelInput(input, {
                initialCountry: "ec",
                separateDialCode: true,
                preferredCountries: ["ec", "us", "co", "pe", "es"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
            });

            function sync() {
                // E.164: +593991234567
                const number = iti.getNumber();
                hidden.value = number || "";
            }

            input.addEventListener("blur", sync);
            input.addEventListener("change", sync);
            input.addEventListener("keyup", sync);
            input.addEventListener("countrychange", sync);

            // Validación: si no es válido, bloquea (si quieres)
            input.addEventListener("invalid", () => {
                sync();
                if (input.value.trim() && !iti.isValidNumber()) {
                input.setCustomValidity("Ingrese un número de celular válido.");
                } else {
                input.setCustomValidity("");
                }
            });

            input.addEventListener("input", () => input.setCustomValidity(""));
            }

            setupIntlPhone("patient_phone_ui", "customer-phone");
            setupIntlPhone("billing_phone_ui", "billing-phone");
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js"></script>

    <script>
        (function () {
            document.addEventListener("keydown", function (e) {
            // Solo Enter
            if (e.key !== "Enter") return;

            const target = e.target;

            // ❌ No interceptar Enter en textarea
            if (target.tagName === "TEXTAREA") return;

            // ❌ No interceptar botones o submits
            if (
                target.tagName === "BUTTON" ||
                target.type === "submit"
            ) {
                return;
            }

            const nextBtn = document.getElementById("next-step");

            // ❌ Si no existe o está deshabilitado
            if (!nextBtn || nextBtn.disabled) return;

            // Evita submit por defecto
            e.preventDefault();

            // Simula clic en "Siguiente"
            nextBtn.click();
            });
        })();
    </script>

    @if ($setting->footer)
        {!! $setting->footer !!}
    @endif
</body>

</html>