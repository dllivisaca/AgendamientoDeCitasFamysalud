    <!DOCTYPE html>
    <html lang="es">

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
        <link rel="stylesheet" href="https://cdn.payphonetodoesposible.com/box/v1.1/payphone-payment-box.css">
        <script type="module" src="https://cdn.payphonetodoesposible.com/box/v1.1/payphone-payment-box.js"></script>
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
                                        Contáctenos al <a href="tel:+593939034743">0939034743</a>.
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
                                                <label for="patient_full_name" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="patient_full_name"
                                                    name="patient_full_name"
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
                                                <label for="patient_email" class="form-label">Correo electrónico<span class="text-danger">*</span></label>
                                                <input
                                                    type="email"
                                                    class="form-control"
                                                    id="patient_email"
                                                    name="patient_email"
                                                    placeholder="Ej: nombre@gmail.com"
                                                    required
                                                    minlength="6"
                                                    title="Ingrese un correo válido (ej: nombre@gmail.com)."
                                                    autocomplete="email"
                                                    >
                                            </div>
                                            <div class="col-md-6">
                                                <label for="patient_phone_ui" class="form-label">Número de celular <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control phone-input" id="patient_phone_ui" placeholder="Ej: 991234567" required title="Registre el número de celular sin el prefijo del país. Verifique que el país seleccionado sea el correcto." autocomplete="tel">
                                                <input type="hidden" id="patient_phone" name="patient_phone">
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
                                                <label for="patient_notes" class="form-label">Comentario (Opcional)</label>
                                                <textarea
                                                    class="form-control"
                                                    id="patient_notes"
                                                    name="patient_notes"
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
                                            <!-- ✅ Copiar datos del paciente a facturación -->
                                            <div class="col-12 mb-2" id="billing-same-wrapper">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="billing_same_as_patient">
                                                    <label class="form-check-label" for="billing_same_as_patient">
                                                    Usar los mismos datos del paciente para la facturación
                                                    </label>
                                                </div>
                                                <div class="form-text" id="billing-same-help" style="display:none;">
                                                    Para menores de edad, la facturación debe registrarse a nombre del representante.
                                                </div>
                                            </div>
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
                                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{5,}$"
                                                title="Ingrese el nombre para facturación (persona o empresa)."
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
                                                <input type="tel" class="form-control phone-input" id="billing_phone_ui" placeholder="Ej: 991234567" required title="Registre el número de celular sin el prefijo del país. Verifique que el país seleccionado sea el correcto." autocomplete="tel">
                                                <input type="hidden" id="billing-phone" name="billing-phone">
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
                                    <!-- <div class="summary-item">
                                        <div class="row">
                                            <div class="col-md-4 text-muted">Precio:</div>
                                            <div class="col-md-8" id="summary-price"></div>
                                        </div>
                                    </div> -->
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
                    <!-- Step 6: Payment -->
                    <div class="booking-step" id="step6">
                        <h3 class="mb-4">Pago</h3>

                        <!-- 1) Resumen de la cita (solo lectura) -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-card-checklist me-2"></i>Resumen de la cita</h5>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Área de atención:</div>
                                    <div class="col-md-8" id="pay-summary-category"></div>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Servicio:</div>
                                    <div class="col-md-8" id="pay-summary-service"></div>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Profesional:</div>
                                    <div class="col-md-8" id="pay-summary-employee"></div>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Fecha y hora:</div>
                                    <div class="col-md-8" id="pay-summary-datetime"></div>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Duración:</div>
                                    <div class="col-md-8" id="pay-summary-duration"></div>
                                </div>
                            </div>

                            <div class="summary-item">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Modalidad:</div>
                                    <div class="col-md-8" id="pay-summary-mode"></div>
                                </div>
                            </div>
                        </div>                        

                        <!-- 2) Resumen de pago (siempre visible) -->
                        
                        <div class="form-section">
                            
                            <h5 class="section-title"><i class="bi bi-cash-coin me-2"></i>Resumen de pago</h5>

                            <div class="small text-muted mb-3">
                                El valor final puede variar según el método de pago seleccionado.
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>Precio del servicio (estándar):</span>
                                <strong id="std-price">$0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between d-none" id="discount-row">
                                <span>Descuento por transferencia bancaria:</span>
                                <strong id="discount-amount">-$0.00</strong>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <span class="fs-5">Total a pagar:</span>
                                <strong class="fs-5" id="total-to-pay">$0.00</strong>
                            </div>
                            
                        </div>

                        <!-- 3) Método de pago -->
                        <div class="form-section">
                            
                            <h5 class="section-title"><i class="bi bi-credit-card-2-front me-2"></i>Método de pago</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm_card" value="card">
                                    <label class="form-check-label fw-bold" for="pm_card">Pago con tarjeta (precio estándar)</label>
                                    </div>
                                    <div class="small text-muted mt-2">
                                    Pago inmediato y confirmación automática de la cita.
                                    </div>
                                </div>
                                </div>

                                <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-white">
                                    <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pm_transfer" value="transfer">
                                    <label class="form-check-label fw-bold" for="pm_transfer">Transferencia bancaria (descuento aplicado)</label>
                                    </div>
                                    <div class="small text-muted mt-2">
                                    Obtén un descuento pagando por transferencia bancaria.
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0" id="pm-hint">
                                Seleccione un método de pago para continuar.
                            </div>
                            
                        </div>

                        <!-- 4A) Transferencia -->
                        <div class="form-section" id="transfer-block" style="display:none;">
                            
                            <h5 class="section-title"><i class="bi bi-bank me-2"></i>Transferencia bancaria</h5>

                            <!-- Datos bancarios (pon aquí los reales) -->
                            <div class="alert alert-info">
                                <div><strong>Banco:</strong> TU BANCO</div>
                                <div><strong>Tipo de cuenta:</strong> Ahorros / Corriente</div>
                                <div><strong>Número de cuenta:</strong> 0000000000</div>
                                <div><strong>Titular:</strong> Centro Médico FamySALUD</div>
                                <div><strong>Identificación:</strong> 0000000000</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Banco de origen <span class="text-danger">*</span></label>                               
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="tr_bank"
                                        placeholder="Ej: Banco Guayaquil"
                                        required
                                        minlength="3"
                                        pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]{3,}$"
                                        title="Mínimo 3 caracteres. Solo letras y espacios."
                                        autocomplete="organization"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Titular que realizó el pago <span class="text-danger">*</span></label>                                   
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="tr_holder"
                                        placeholder="Nombre del titular"
                                        required
                                        minlength="5"
                                        pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]{5,}$"
                                        title="Mínimo 5 caracteres. Solo letras y espacios."
                                        autocomplete="name"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de transferencia <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="tr_date"
                                        required
                                        title="No se permiten fechas futuras ni demasiado antiguas."
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">N° de referencia / comprobante <span class="text-danger">*</span></label>                                    
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="tr_ref"
                                        placeholder="Ej: 1234AB"
                                        required
                                        minlength="4"
                                        pattern="^[A-Za-z0-9]{4,}$"
                                        title="Mínimo 4 caracteres. Solo letras y números, sin espacios."
                                        autocomplete="off"
                                    />
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Comprobante (JPG/PNG/PDF) <span class="text-danger">*</span></label>
                                    <div class="form-text" id="tr_file_help">
                                    Formatos permitidos: JPG, PNG o PDF. Tamaño máximo: 5MB.
                                    </div>
                                    <input
                                        type="file"
                                        class="form-control file-input-soft"
                                        id="tr_file"
                                        name="tr_file"
                                        required
                                        accept=".jpg,.jpeg,.png,.pdf"
                                    >                        
                                <div class="form-text">La cita se confirmará una vez validado el pago.</div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- 4B) Tarjeta -->
                        <div class="form-section" id="card-block" style="display:none;">
                            
                            <h5 class="section-title"><i class="bi bi-credit-card me-2"></i>Pago con tarjeta</h5>

                            <div class="alert alert-info">
                                Su pago se procesará de forma segura.
                            </div>

                            <!-- Placeholder de pasarela embebida -->
                            <div class="border rounded p-3 bg-light" id="payphone-container">
                                <div id="pp-button"></div>
                                <div class="small text-muted mt-2" id="pp-help">
                                    Al pagar, usted será redirigido/a para confirmar el resultado.
                                </div>
                            </div>
                            
                        </div>

                        <!-- 5) Botón final -->
                        <div class="form-section d-none" id="pay-action-card">
                                <h5 class="section-title"><i class="bi bi-shield-check me-2"></i>Confirmación</h5>
                                <button class="btn btn-success w-100" id="pay-now" type="button" disabled></button>

                                <div id="terms-container" class="mt-3 d-none">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="accept_terms">
                                        <label class="form-check-label" for="accept_terms">
                                        Acepto los <a href="#" id="open-terms">Términos y condiciones</a>
                                        </label>
                                    </div>
                                </div>

                                <!-- 👇 AGREGA ESTE BLOQUE JUSTO AQUÍ -->
                                <div class="small text-muted mt-3 text-center" id="no-refund-note">
                                    Al continuar con el proceso, usted acepta que los pagos no son reembolsables.
                                    Las citas pueden ser reagendadas según disponibilidad.
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
                        <h5 class="modal-title">¡Cita registrada!</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">¡Gracias!</h4>
                        <p>Su cita se registró correctamente.</p>
                        <div class="alert alert-info mt-3">
                            <p class="mb-0">Le enviamos un correo electrónico con el resumen de su cita</p>
                        </div>
                        <div class="booking-details mt-4 text-start">
                            <h5>Detalles de la cita:</h5>
                            <div id="modal-booking-details"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
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
                async function initPayphoneWithTotal(totalUSD) {
                    try {
                        if (!totalUSD || totalUSD <= 0) return;

                        // Convertir a centavos
                        const amountCents = Math.round(parseFloat(totalUSD) * 100);

                        // Necesitamos el hold_id
                        const holdId = bookingState.hold_id;
                        if (!holdId) {
                        console.warn("No hay hold_id para iniciar PayPhone");
                        return;
                        }

                        // Llamar backend para inicializar intento de pago
                        const res = await fetch("{{ route('payphone.init') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            appointment_hold_id: holdId,
                            amount: parseFloat(totalUSD)
                        })
                        });

                        if (!res.ok) {
                        console.error("PayPhone init falló", await res.text());
                        return;
                        }

                        const cfg = await res.json();

                        const container = document.getElementById("pp-button");
                        if (!container) {
                        console.error("No existe el div #pp-button");
                        return;
                        }

                        container.innerHTML = "";

                        new PPaymentButtonBox({
                        token: cfg.token,          // ⚠️ viene del backend (.env)
                        storeId: cfg.storeId,
                        clientTransactionId: cfg.clientTransactionId,
                        reference: "Cita médica FamySALUD",
                        amount: amountCents,
                        amountWithoutTax: amountCents,
                        currency: "USD",
                        lang: "es",
                        timeZone: -5,
                        }).render("pp-button");

                    } catch (e) {
                        console.error("Error PayPhone:", e);
                    }
                }
                // ================================
                // STEP 6: CONFIRMAR Y AGENDAR
                // ================================
                $("#pay-now").on("click", function () {
                    // Validar datos del paso 5 por seguridad (perfecto mantenerlo)
                    if (!validateStep(5)) {
                        alert("Por favor, verifique la información ingresada antes de continuar.");
                        return;
                    }

                    // Validar step 6 (método + campos si es transferencia)
                    if (!validateStep6()) return;

                    // 🚨 SI ES TARJETA → NO crear cita aquí
                    if (bookingState.paymentMethod === "card") {
                        alert("Para confirmar tu cita, completa el pago con PayPhone.");
                        return;
                    }

                    // ✅ Por ahora: manda a crear la reserva con un status coherente.
                    // Luego conectamos pasarela (tarjeta) y subida real de comprobante al backend.
                    submitBooking();
                });

                const categories = @json($categories);

                // 👇 NUEVO: mes/año actuales + días laborales del empleado
                let currentMonth;      // 0-11
                let currentYear;       // año completo
                let workingWeekdays = null; // [0..6] (0=Dom,1=Lun,...)
                let availableDatesSet = new Set();

                let availableDatesByMonth = {}; // cache: "YYYY-MM" => Set([...])
                let allowedMinYM = null;        // "YYYY-MM" (desde min_allowed)
                let allowedMaxYM = null;        // "YYYY-MM" (hasta max_allowed)

                let allowedMinDate = null;      // "YYYY-MM-DD"
                let allowedMaxDate = null;      // "YYYY-MM-DD"

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

                            allowedMinDate = (res.min_allowed || "").toString().substring(0, 10) || null;
                            allowedMaxDate = (res.max_allowed || "").toString().substring(0, 10) || null;

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
                    appointmentMode: 'presencial',
                    paymentMethod: null,

                    // ✅ HOLD
                    hold_id: null,
                    hold_expires_at: null
                };

                // ================================
                // ✅ HOLD HELPERS (15 minutos)
                // ================================
                const HOLD_TTL_MINUTES = 15;

                // CSRF (ya lo tienes dentro de step5 forms)
                function getCsrfToken() {
                    const t = $('input[name="_token"]').first().val();
                    return t || null;
                }

                function clearHoldState() {
                    bookingState.hold_id = null;
                    bookingState.hold_expires_at = null;
                }

                function releaseHoldIfAny() {
                    if (!bookingState.hold_id) return $.Deferred().resolve().promise();

                    const holdId = bookingState.hold_id;
                    clearHoldState();

                    return $.ajax({
                        url: `/holds/${encodeURIComponent(holdId)}`,
                        method: "DELETE",
                        data: { _token: getCsrfToken() }
                    }).catch(() => {});
                }

                /**
                 * Crea hold en backend para el slot seleccionado.
                 * Si backend dice "ya ocupado", devolvemos false.
                 */
                function createHoldForSelection() {
                    if (!bookingState.selectedEmployee || !bookingState.selectedService || !bookingState.selectedDate || !bookingState.selectedTime?.start) {
                        return $.Deferred().reject("missing_data").promise();
                    }

                    return $.ajax({
                        url: "/holds",
                        method: "POST",
                        dataType: "json",
                        data: {
                        _token: getCsrfToken(),
                        employee_id: bookingState.selectedEmployee.id,
                        service_id: bookingState.selectedService.id,
                        appointment_date: bookingState.selectedDate,
                        appointment_time: bookingState.selectedTime.start,
                        appointment_mode: bookingState.appointmentMode,
                        ttl_minutes: HOLD_TTL_MINUTES
                        }
                    }).then((res) => {
                        if (!res || !res.success) return $.Deferred().reject(res?.message || "hold_failed").promise();

                        bookingState.hold_id = res.hold_id;
                        bookingState.hold_expires_at = res.expires_at || null;
                        return true;
                    });
                }

                // Initialize the booking system
                updateProgressBar();
                generateCalendar();

                // Step navigation
                $("#next-step").click(function() {
                    const currentStep = bookingState.currentStep;

                    // Validar antes de avanzar
                    if (!validateStep(currentStep)) return;

                    // 1 → 2 → 3 → 4 → 5 normal
                    if (currentStep < 5) {
                        goToStep(currentStep + 1);
                        return;
                    }

                    // ✅ NUEVO: 5 → 6 (NO agendar aquí)
                    if (currentStep === 5) {
                        goToStep(6);
                        return;
                    }

                    // (opcional) si quisieras usar next-step también en step6, lo controlas aquí.
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

                    releaseHoldIfAny();

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

                    releaseHoldIfAny();

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

                    releaseHoldIfAny();

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

                    // 1️⃣ Guardar modalidad seleccionada
                    bookingState.appointmentMode = this.value;

                    // 2️⃣ Actualizar mensaje de zona horaria
                    const isVirtual = bookingState.appointmentMode === 'virtual';
                    const userTzLabel = getUserTimeZoneLabel();

                    if (isVirtual) {
                        $("#tz-info-message").html(`
                            <i class="bi bi-clock me-1"></i>
                            Todos los turnos están en su hora local (${userTzLabel})
                        `);
                    } else {
                        $("#tz-info-message").html(`
                            <i class="bi bi-clock me-1"></i>
                            Todos los turnos están en hora local de Ecuador (GMT-5) (zona horaria de Ecuador)
                        `);
                    }

                    // 3️⃣ Si aún no hay fecha seleccionada, detener aquí
                    if (!bookingState.selectedDate) return;

                    // 4️⃣ Reescribir los textos de los turnos visibles
                    const userTz = getUserTimeZone();

                    $(".time-slot").each(function () {
                        const $btn = $(this);

                        const start = $btn.data("start");       // "09:15"
                        const end   = $btn.data("end");         // "09:35"
                        const ecDisplay = $btn.data("display-ec"); // "9:15 AM - 9:35 AM"

                        const newText = isVirtual
                            ? formatRangeInTimeZone(bookingState.selectedDate, start, end, userTz)
                            : ecDisplay;

                        $btn.html(`<i class="bi bi-clock me-1"></i> ${newText}`);
                    });

                    // 5️⃣ Quitar selección previa (seguridad UX)
                    $(".time-slot").removeClass("selected active");
                    bookingState.selectedTime = null;
                });                  

                // Date selection
                $(document).on("click", ".calendar-day:not(.disabled)", function() {
                    $(".calendar-day").removeClass("selected");
                    $(this).addClass("selected");

                    const date = $(this).data("date");
                    bookingState.selectedDate = date;

                    // ✅ si ya había un turno holdeado, lo libero
                    releaseHoldIfAny();

                    // Reset time selection
                    bookingState.selectedTime = null;

                    // Update time slots based on employee availability
                    updateTimeSlots(date);
                });

                // Retry button – se declara UNA SOLA VEZ
                $(document).on('click', '.btn-retry-timeslots', function() {
                        const date = $(this).data('date');
                        updateTimeSlots(date);
                });

                // Time slot selection + HOLD
                $(document).on("click", ".time-slot:not(.disabled)", function() {
                    const $btn = $(this);

                    // Armamos selectedTime con lo que el usuario clickeó
                    const newSelectedTime = {
                        start: $btn.data("start"),
                        end: $btn.data("end"),
                        display: $btn.text().trim(),
                        display_ec: $btn.data("display-ec") || null
                    };

                    // Guardar selección preliminar en state (pero aún NO confirmamos visualmente)
                    bookingState.selectedTime = newSelectedTime;

                    // UI: loading suave en el botón
                    const originalHtml = $btn.html();
                    $(".time-slot").removeClass("selected active"); // quita selección previa

                    $btn.addClass("disabled").html(`<span class="spinner-border spinner-border-sm me-2"></span> Reservando...`);

                    // 1) Si había hold previo, liberarlo primero
                    releaseHoldIfAny().then(() => {

                        // 2) Intentar crear hold
                        return createHoldForSelection();

                    }).then(() => {
                        // ✅ Hold creado: ahora sí seleccionamos el turno
                        $btn.removeClass("disabled").html(originalHtml);
                        $btn.addClass("selected active");

                        updateSummary && updateSummary();
                        setTimeout(() => window.updateFloatingNext && window.updateFloatingNext(), 0);

                    }).catch((err) => {
                        // ❌ No se pudo crear hold: limpiar selección, recargar turnos y avisar
                        bookingState.selectedTime = null;
                        clearHoldState();

                        // recuperar botón
                        $btn.removeClass("disabled").html(originalHtml);

                        alert("Ese turno ya no está disponible. A continuación se mostrarán los turnos disponibles actualizados.");
                        updateTimeSlots(bookingState.selectedDate);
                        setTimeout(() => window.updateFloatingNext && window.updateFloatingNext(), 0);
                    });
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

                    for (let i = 1; i <= 6; i++) {
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

                    if (step === 6) {
                        // ✅ Ocultar botones globales "Siguiente" en paso 6
                        $("#next-step").addClass("d-none");
                        $("#next-step-floating").addClass("d-none");

                        $("#pay-action-card").addClass("d-none").show(); // show() por si quedó un display:none viejo
                        $("#pay-now").prop("disabled", true).text("");

                        // ✅ Reset selección de pago cada vez que entras
                        bookingState.paymentMethod = null;
                        $('input[name="payment_method"]').prop('checked', false);

                        // ✅ (Si ya tienes términos) reset también
                        $("#accept_terms").prop("checked", false);

                        // ✅ Reset botón principal del paso 6
                        $("#pay-now")
                            .prop("disabled", true)
                            .html('Continuar <i class="bi bi-arrow-right"></i>');

                        // ✅ Mostrar hint y ocultar bloques específicos
                        $("#pm-hint").show();
                        $("#card-block").hide();
                        $("#transfer-block").hide();

                        $("#discount-row").addClass("d-none");

                        // ✅ Cargar resumen y UI
                        fillStep6Summary();
                        refreshPaymentUI();
                    } else {
                        // ✅ En pasos 1–5 sí se usa el botón global "Siguiente"
                        $("#next-step").removeClass("d-none");
                        $("#next-step-floating").removeClass("d-none");
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
                    const progress = ((bookingState.currentStep - 1) / 5) * 100;
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
                                alert("Por favor seleccione un área de atención");
                                return false;
                            }
                            return true;
                        case 2:
                            if (!bookingState.selectedService) {
                                alert("Por favor seleccione un servicio");
                                return false;
                            }
                            return true;
                        case 3:
                            if (!bookingState.selectedEmployee) {
                                alert("Por favor seleccione a un profesional");
                                return false;
                            }
                            return true;
                        case 4:
                            if (!bookingState.selectedDate) {
                                alert("Por favor seleccione una fecha");
                                return false;
                            }
                            if (!bookingState.selectedTime) {
                                alert("Por favor seleccione un turno");
                                return false;
                            }
                            if (!bookingState.hold_id) {
                                alert("Por favor seleccione un turno disponible (se reservará temporalmente).");
                                return false;
                            }
                            return true;
                        case 5: {
                            const customerForm = document.getElementById("customer-info-form");
                            const billingForm  = document.getElementById("billing-info-form");

                            // 1) Primero: validación de formularios (esto muestra el primer error real)
                            const okCustomer = customerForm ? customerForm.reportValidity() : false;
                            if (!okCustomer) return false;

                            const okBilling = billingForm ? billingForm.reportValidity() : true;
                            if (!okBilling) return false;

                            // 2) Después: consentimiento
                            const consent = document.getElementById("consent_data");
                            if (!consent || !consent.checked) {
                                alert("Para continuar, debe autorizar el uso de los datos personales.");
                                return false;
                            }

                            return true;
                        }
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
                                $(".selected-service-name").html(`Servicio seleccionado: ${service.title}`);

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

                                // Bloquear días fuera del rango permitido (aunque el mes esté permitido)
                                const isOutOfAllowedRange =
                                    (allowedMinDate && formattedDate < allowedMinDate) ||
                                    (allowedMaxDate && formattedDate > allowedMaxDate);

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
                                if (isPast || isDisabledBySchedule || isOutOfAllowedRange) {
                                    classes += ' disabled';
                                }

                                // Create the cell
                                const lockedByRule = (isPast || isDisabledBySchedule || isOutOfAllowedRange);
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

                    // Show initial state instead of loading spinner
                    $("#time-slots-container").html(`
                        <div class="text-center w-100 py-4">
                            <div class="alert alert-info">
                                <i class="bi bi-calendar-event me-2"></i>
                                Por favor seleccione una fecha para ver los turnos disponibles
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

                function getUserTimeZone() {
                    // Ej: "America/Guayaquil", "America/Chicago"
                    return Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC";
                    }

                    function getUserTimeZoneLabel() {
                    // Intenta sacar abreviación: CST, EST, etc. (si el navegador la provee)
                    try {
                        const tz = getUserTimeZone();
                        const parts = new Intl.DateTimeFormat('en-US', { timeZone: tz, timeZoneName: 'short' })
                        .formatToParts(new Date());
                        const tzPart = parts.find(p => p.type === "timeZoneName");
                        return tzPart?.value || tz;
                    } catch {
                        return getUserTimeZone();
                    }
                    }

                function normalizeTime(t) {
                    // "09:15" -> "09:15:00"
                    return (t && t.length === 5) ? `${t}:00` : t;
                    }

                    function formatRangeInTimeZone(dateStr, startHHMM, endHHMM, timeZone) {
                    const start = normalizeTime(startHHMM);
                    const end   = normalizeTime(endHHMM);

                    // Ecuador GMT-5 como base
                    const startISO = `${dateStr}T${start}-05:00`;
                    const endISO   = `${dateStr}T${end}-05:00`;

                    const fmt = new Intl.DateTimeFormat('en-US', {
                        timeZone,
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    return `${fmt.format(new Date(startISO))} - ${fmt.format(new Date(endISO))}`;
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

                            // Add slot duration info (mensaje zona horaria dinámico)
                            const isVirtual = bookingState.appointmentMode === 'virtual';
                            const userTzLabel = getUserTimeZoneLabel();

                            const tzMessage = isVirtual
                            ? `Todos los turnos están en su hora local (${userTzLabel})`
                            : `Todos los turnos están en hora local de Ecuador (GMT-5)`;

                            $("#time-slots-container").append(`
                                <div class="slot-info mb-3 w-100">
                                    <div>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Duración: ${response.slot_duration} minutos
                                        </small>
                                    </div>

                                    <div>
                                        <small class="text-muted d-block mt-1" id="tz-info-message">
                                            <i class="bi bi-clock me-1"></i>
                                            ${tzMessage}
                                        </small>
                                    </div>
                                </div>
                            `);

                            // Add each time slot
                            const $slotsContainer = $("<div class='slots-grid'></div>");
                            response.available_slots.forEach(slot => {

                                // 🔹 FILTRAR SEGÚN MODALIDAD
                                if (
                                    bookingState.appointmentMode === 'presencial' &&
                                    slot.mode && slot.mode !== 'presencial'
                                ) {
                                    return;
                                }

                                if (
                                    bookingState.appointmentMode === 'virtual' &&
                                    slot.mode && slot.mode !== 'virtual'
                                ) {
                                    return;
                                }

                                const todayStr = formatLocalDate(new Date());
                                const isToday = (selectedDate === todayStr);

                                let disableByTime = false;
                                if (isToday) {
                                    disableByTime = isSlotLessThan3HoursAhead(selectedDate, slot.start);
                                }

                                const extraClass = disableByTime ? ' disabled' : '';

                                const slotElement = $(`
                                    <div class="time-slot btn btn-outline-primary mb-2${extraClass}"
                                        data-start="${slot.start}"
                                        data-end="${slot.end}"
                                        data-display-ec="${slot.display}">
                                        <i class="bi bi-clock me-1"></i>
                                        ${slot.display}
                                    </div>
                                `);

                                $slotsContainer.append(slotElement);
                            });
                            $("#time-slots-container").append($slotsContainer);
                        },
                        error: function(xhr) {
                            console.log("AVAILABILITY ERROR", {
                                status: xhr.status,
                                responseText: xhr.responseText,
                                responseJSON: xhr.responseJSON
                            });

                            const msg =
                                xhr.responseJSON?.message ||
                                (xhr.responseText ? xhr.responseText.slice(0, 200) : "") ||
                                "No se pudo consultar la disponibilidad.";

                            $("#time-slots-container").html(`
                                <div class="text-center py-4">
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-octagon me-2"></i>
                                    Error al cargar los turnos disponibles
                                    <div class="small mt-2"><b>Código:</b> ${xhr.status}</div>
                                    <div class="small text-muted mt-1" style="word-break:break-word;">${msg}</div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-2 btn-retry-timeslots" data-date="${selectedDate}">
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
                        $("#summary-service").text(bookingState.selectedService.title);                      
                        $("#summary-duration").text(`${bookingState.selectedEmployee.slot_duration} minutos`);
                    }

                    // Update employee info
                    if (bookingState.selectedEmployee) {
                        $("#summary-employee").text(bookingState.selectedEmployee.user.name);
                    }

                    // Update date/time info
                    if (bookingState.selectedDate && bookingState.selectedTime) {
                        let formattedDate = new Date(bookingState.selectedDate + "T00:00:00")
                            .toLocaleDateString('es-EC', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                        // 👉 Capitalizar la primera letra del día
                        formattedDate = formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);

                            // “a las” en vez de “at”
                            $("#summary-datetime").text(
                            `${formattedDate} a las ${bookingState.selectedTime.display || bookingState.selectedTime}`
                            );
                    }
                    $("#summary-mode").text(
                        bookingState.appointmentMode === 'virtual' ? 'Virtual' : 'Presencial'
                    );
                }

                function money(n) {
                    const x = Math.round((n + Number.EPSILON) * 100) / 100;
                    return `$${x.toFixed(2)}`;
                    }

                    // En tu app: bookingState.selectedService.price viene como texto tipo "Efectivo / Transferencia: $15.00"
                    function getTransferAmount() {
                    if (!bookingState.selectedService?.price) return 0;
                    const num = parseFloat(String(bookingState.selectedService.price).replace(/[^0-9.]/g, ""));
                    return isNaN(num) ? 0 : num;
                    }

                    function computePaymentFigures() {
                    const transfer = getTransferAmount();      // precio con descuento (transferencia)
                    const standard = transfer * 1.08;          // precio estándar (tarjeta)
                    const discount = standard - transfer;      // descuento por transferencia
                    return { transfer, standard, discount };
                    }

                    function fillStep6Summary() {
                    const selectedCategory = categories.find(cat => cat.id == bookingState.selectedCategory);

                    $("#pay-summary-category").text(selectedCategory ? selectedCategory.title : "");
                    $("#pay-summary-service").text(bookingState.selectedService?.title || "");
                    $("#pay-summary-employee").text(bookingState.selectedEmployee?.user?.name || "");
                    $("#pay-summary-duration").text(
                        bookingState.selectedEmployee?.slot_duration ? `${bookingState.selectedEmployee.slot_duration} minutos` : ""
                    );
                    $("#pay-summary-mode").text(bookingState.appointmentMode === "virtual" ? "Virtual" : "Presencial");

                    if (bookingState.selectedDate && bookingState.selectedTime) {
                        let formattedDate = new Date(bookingState.selectedDate + "T00:00:00")
                        .toLocaleDateString("es-EC", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
                        formattedDate = formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);

                        $("#pay-summary-datetime").text(`${formattedDate} a las ${bookingState.selectedTime.display || ""}`);
                    }
                    }

                    function refreshPaymentUI() {
                        const { transfer, standard, discount } = computePaymentFigures();
                        const method = bookingState.paymentMethod;

                        // Siempre mostramos precio estándar
                        $("#std-price").text(money(standard));

                        if (method === "transfer") {                          
                            $("#discount-row").removeClass("d-none");
                            $("#discount-amount").text(`-${money(discount)}`);
                            $("#total-to-pay").text(money(transfer));
                        } else {                            
                            $("#discount-row").addClass("d-none");
                            $("#discount-amount").text(`-${money(discount)}`); // opcional, por si luego quieres mantenerlo calculado
                            $("#total-to-pay").text(money(standard));
                        }

                        // Bloques de contenido
                        $("#transfer-block").toggle(method === "transfer");
                        $("#card-block").toggle(method === "card");

                        // =========================
                        // BOTÓN FINAL + TÉRMINOS
                        // =========================
                        if (!method) {
                            // 🔴 SIN MÉTODO
                            $("#pm-hint").show();

                            $("#pay-action-card").addClass("d-none").show();

                            $("#terms-container").addClass("d-none");
                            $("#accept_terms").prop("checked", false);
                            $("#pay-now").prop("disabled", true).html("");

                        } else {
                            // 🟢 CON MÉTODO
                            $("#pm-hint").hide();

                            $("#pay-action-card").removeClass("d-none").show();
                            $("#terms-container").removeClass("d-none");

                            if (method === "transfer") {
                                $("#pay-now").html(
                                    'Registrar cita y enviar comprobante <i class="bi bi-check2-circle"></i>'
                                );
                            } else {
                                $("#pay-now").html(
                                    'Pagar y confirmar cita <i class="bi bi-check2-circle"></i>'
                                );
                            }

                            // Habilitar según términos
                            syncPayButtonState();
                        }
                    }

                    function syncPayButtonState() {
                        const hasMethod = !!bookingState.paymentMethod;
                        const accepted = $("#accept_terms").is(":checked");
                        $("#pay-now").prop("disabled", !(hasMethod && accepted));
                    }

                    $(document).on("change", "#accept_terms", function () {
                        syncPayButtonState();
                    });

                    // ================================
                    // TRANSFERENCIA (STEP 6)
                    // ================================
                    (function initTransferDateLimits() {
                        const dateEl = document.getElementById("tr_date");
                        if (!dateEl) return;

                        const formatYMD = (d) => {
                            const yyyy = d.getFullYear();
                            const mm = String(d.getMonth() + 1).padStart(2, "0");
                            const dd = String(d.getDate()).padStart(2, "0");
                            return `${yyyy}-${mm}-${dd}`;
                        };

                        const today = new Date();

                        // Min: 30 días atrás
                        const minDate = new Date(today);
                        minDate.setDate(minDate.getDate() - 30);

                        // Max: 1 día después (buffer por zona horaria)
                        const maxDate = new Date(today);
                        maxDate.setDate(maxDate.getDate() + 1);

                        const min = formatYMD(minDate);
                        const max = formatYMD(maxDate);

                        dateEl.min = min;
                        dateEl.max = max;

                        // Validación extra (por si el navegador no respeta min/max en algún caso)
                        const validateRange = () => {
                            const v = (dateEl.value || "").trim();
                            if (!v) {
                                dateEl.setCustomValidity("");
                                return;
                            }

                            if (v < min || v > max) {
                                dateEl.setCustomValidity(`La fecha debe estar entre ${min} y ${max}.`);
                            } else {
                                dateEl.setCustomValidity("");
                            }
                        };

                        dateEl.addEventListener("change", validateRange);
                        dateEl.addEventListener("input", validateRange);
                    })();

                    function validateTransferFile() {
                        const input = document.getElementById("tr_file");
                        if (!input) return true;

                        const file = input.files && input.files[0];
                        if (!file) {
                            input.setCustomValidity("Adjunta el comprobante (JPG, PNG o PDF).");
                            return false;
                        }

                        const maxBytes = 5 * 1024 * 1024; // 5MB
                        const allowedTypes = ["image/jpeg", "image/png", "application/pdf"];

                        // Validar tamaño
                        if (file.size > maxBytes) {
                            input.setCustomValidity("El archivo es demasiado grande. Máximo permitido: 5MB.");
                            return false;
                        }

                        // Validar tipo real (MIME)
                        if (!allowedTypes.includes(file.type)) {
                            input.setCustomValidity("Formato no permitido. Solo JPG, PNG o PDF.");
                            return false;
                        }

                        input.setCustomValidity("");
                        return true;
                        }

                        // Mostrar mensaje al instante cuando el usuario elige archivo
                        $(document).on("change", "#tr_file", function () {
                        validateTransferFile();
                        this.reportValidity(); // muestra el mensaje si falla
                    });

                    // Valida solo lo mínimo del step6 (luego lo afinamos)
                    function validateStep6() {
                    if (!bookingState.paymentMethod) {
                        alert("Seleccione un método de pago para continuar.");
                        return false;
                    }

                    if (bookingState.paymentMethod === "transfer") {
                        const bankEl   = document.getElementById("tr_bank");
                        const holderEl = document.getElementById("tr_holder");
                        const dateEl   = document.getElementById("tr_date");
                        const refEl    = document.getElementById("tr_ref");
                        const fileEl   = document.getElementById("tr_file");

                        // Limpiezas básicas
                        if (refEl) refEl.value = (refEl.value || "").replace(/\s+/g, "");
                        if (bankEl) bankEl.value = (bankEl.value || "").replace(/\s+/g, " ").trim();
                        if (holderEl) holderEl.value = (holderEl.value || "").replace(/\s+/g, " ").trim();

                        // 1) Validación HTML nativa (required/minlength/pattern)
                        if (bankEl && !bankEl.checkValidity())   { bankEl.reportValidity(); return false; }
                        if (holderEl && !holderEl.checkValidity()){ holderEl.reportValidity(); return false; }
                        if (dateEl && !dateEl.checkValidity())   { dateEl.reportValidity(); return false; }
                        if (refEl && !refEl.checkValidity())     { refEl.reportValidity(); return false; }

                        // 2) Validación de archivo (tamaño + tipo)
                        if (!validateTransferFile()) {
                            fileEl && fileEl.reportValidity();
                            return false;
                        }
                    }

                    // Tarjeta: aquí luego validas con la pasarela (por ahora solo deja pasar)
                    return true;
                }

                // function submitBooking() {
                function submitBooking() {
                    const csrfToken = getCsrfToken();

                    // ✅ Si no hay hold, no intentamos reservar
                    if (!bookingState.hold_id) {
                        alert("El turno ya no está reservado. Seleccione el horario nuevamente.");
                        return;
                    }

                    // ✅ Si no hay END TIME, no enviamos (evita NULL en BD)
                    if (!bookingState.selectedTime || !bookingState.selectedTime.end) {
                        alert("Seleccione un horario válido nuevamente.");
                        return;
                    }

                    // ✅ Forzar sync de teléfonos hidden (E.164) antes de enviar
                    try {
                        const patientIti = window._itiByInputId?.["patient_phone_ui"];
                        const billingIti = window._itiByInputId?.["billing_phone_ui"];

                        if (patientIti && document.getElementById("patient_phone")) {
                            document.getElementById("patient_phone").value = patientIti.getNumber() || "";
                        }

                        // OJO: tu hidden de billing es "billing-phone" (con guion)
                        if (billingIti && document.getElementById("billing-phone")) {
                            document.getElementById("billing-phone").value = billingIti.getNumber() || "";
                        }
                    } catch (e) {
                    console.warn("Phone sync error", e);
                    }

                    const fd = new FormData();

                    // --- DEBUG-FIX: asegurar que estos campos SIEMPRE viajen en el request ---
                    fd.append("_token", csrfToken);

                    // ✅ HOLD
                    fd.append("hold_id", bookingState.hold_id);

                    // Paciente (documento)
                    fd.append("patient_doc_type", $("#doc_type").val() || "");
                    fd.append("patient_doc_number", $("#doc_number").val() || "");

                    // ✅ IDs
                    fd.append("employee_id", bookingState.selectedEmployee.id);
                    fd.append("service_id", bookingState.selectedService.id);

                    // ✅ CITA
                    fd.append("appointment_date", bookingState.selectedDate);
                    fd.append("appointment_time", bookingState.selectedTime.start || bookingState.selectedTime);
                    fd.append("appointment_end_time", bookingState.selectedTime.end || "");
                    fd.append("appointment_mode", bookingState.appointmentMode);

                    // ✅ PACIENTE (estos nombres deben calzar con el controller)
                    fd.append("patient_full_name", $("#patient_full_name").val());
                    fd.append("patient_email", $("#patient_email").val());
                    fd.append("patient_phone", $("#patient_phone").val());
                    fd.append("patient_address", $("#patient_address").val() || "");
                    fd.append("patient_dob", $("#patient_dob").val() || "");
                    fd.append("patient_notes", $("#patient_notes").val() || "");

                    // Facturación (IDs correctos)
                    fd.append("billing_name", $("#billing-name").val() || "");
                    fd.append("billing_doc_type", $("#billing-doc-type").val() || "");
                    fd.append("billing_doc_number", $("#billing-doc-number").val() || "");
                    fd.append("billing_address", $("#billing-address").val() || "");
                    fd.append("billing_email", $("#billing-email").val() || "");
                    fd.append("billing_phone", $("#billing-phone").val() || "");

                    // Consentimiento (ID correcto)
                    fd.append("data_consent", $("#consent_data").is(":checked") ? "1" : "0");

                    // ✅ paymentMethod primero (antes de usarlo)
                    const paymentMethod = (bookingState.paymentMethod || "").toString().trim().toLowerCase();
                    fd.append("payment_method", paymentMethod);
                    console.log("DEBUG paymentMethod:", paymentMethod);

                    // ✅ STATUS + amount
                    fd.append("status", paymentMethod === "transfer" ? "pending_verification" : "pending_payment");

                    const figures = computePaymentFigures();
                    const amt = paymentMethod === "transfer" ? figures.transfer : figures.standard;
                    fd.append("amount", String(isFinite(amt) ? amt : 0));

                    fd.append("amount_standard", String(isFinite(figures.standard) ? figures.standard : 0));
                    fd.append("discount_amount", String(isFinite(figures.discount) ? figures.discount : 0));

                    // ✅ TZ opcional
                    fd.append("patient_timezone", Intl.DateTimeFormat().resolvedOptions().timeZone || "");
                    fd.append("patient_timezone_label", getUserTimeZoneLabel());

                    // ✅ Si es transferencia: adjuntar comprobante (id del input file: tr_file)
                    if (paymentMethod === "transfer") {
                        // archivo
                        const file = document.getElementById("tr_file")?.files?.[0];
                        if (file) fd.append("tr_file", file);

                        // valores (IDs reales con underscore)
                        const trBank   = ($("#tr_bank").val() || "").trim();
                        const trHolder = ($("#tr_holder").val() || "").trim();
                        const trDateV  = ($("#tr_date").val() || "").trim(); // YA viene YYYY-MM-DD por ser type="date"
                        const trRef    = ($("#tr_ref").val() || "").trim();

                        // nombres que espera tu BD/backend
                        fd.append("transfer_bank_origin", trBank);
                        fd.append("transfer_payer_name", trHolder);
                        fd.append("transfer_date", trDateV);
                        fd.append("transfer_reference", trRef);

                        // (opcional) compatibilidad tr_* si tu controller también los usa
                        fd.append("tr_bank", trBank);
                        fd.append("tr_holder", trHolder);
                        fd.append("tr_date", trDateV);
                        fd.append("tr_ref", trRef);
                    }

                    const $btn = $("#pay-now"); // tu botón final
                    const original = $btn.html();
                    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

                    $.ajax({
                        url: "/bookings",
                        method: "POST",
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                        clearHoldState(); // para no volver a intentar liberar hold

                        // mostrar modal OK
                        // Helpers locales (pueden vivir dentro del success sin problema)
                        function prettyDateES(dateStr) {
                        if (!dateStr) return "";
                        const d = new Date(dateStr + "T00:00:00");
                        let s = d.toLocaleDateString("es-EC", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
                        return s.charAt(0).toUpperCase() + s.slice(1);
                        }
                        function onlyHHMM(t) {
                        if (!t) return "";
                        // soporta "14:00" o "14:00:00"
                        return String(t).slice(0, 5);
                        }
                        function formatTimeAMPM(timeStr) {
                            if (!timeStr) return "";

                            // soporta "18:00" o "18:00:00"
                            const [h, m] = timeStr.split(":");
                            let hour = parseInt(h, 10);
                            const minute = m;

                            const ampm = hour >= 12 ? "PM" : "AM";
                            hour = hour % 12;
                            hour = hour === 0 ? 12 : hour;

                            return `${hour}:${minute} ${ampm}`;
                        }

                        function parseISODate(dateStr) {
                            // Espera "YYYY-MM-DD"
                            if (!dateStr) return null;
                            const m = String(dateStr).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                            if (!m) return null;
                            return { y: +m[1], mo: +m[2], d: +m[3] };
                        }

                        // Convierte una fecha+hora interpretada como Ecuador (UTC-5) a un objeto Date (instante real)
                        function ecuadorLocalToDate(dateISO, timeStr) {
                            const d = parseISODate(dateISO);
                            if (!d || !timeStr) return null;

                            const hhmm = String(timeStr).slice(0, 5); // "HH:MM"
                            const [hh, mm] = hhmm.split(":").map(n => parseInt(n, 10));

                            // Ecuador = UTC-5 => UTC = hora_ecuador + 5
                            return new Date(Date.UTC(d.y, d.mo - 1, d.d, hh + 5, mm, 0));
                        }

                        function formatInUserTZ(dateObj, userTimeZone) {
                            if (!dateObj) return "";
                            return new Intl.DateTimeFormat("en-US", {
                                hour: "numeric",
                                minute: "2-digit",
                                hour12: true,
                                timeZone: userTimeZone || undefined
                            }).format(dateObj);
                        }
                        function money(n) {
                        const x = Math.round((Number(n) + Number.EPSILON) * 100) / 100;
                        return `$${x.toFixed(2)}`;
                        }

                        const ap = res.appointment || {};
                        const bookingId = res.booking_id || ap.booking_id || "";
                        const status = ap.status || "";
                        const serviceName = ap.service_name || bookingState?.selectedService?.title || "";
                        const employeeName = ap.employee_name || bookingState?.selectedEmployee?.user?.name || "";
                        const modeTxt = ap.appointment_mode === "virtual" ? "Virtual" : "Presencial";
                        const dateTxt = prettyDateES(ap.appointment_date || bookingState?.selectedDate);
                        const apDate = ap.appointment_date || bookingState?.selectedDate;

                        const startTime = ap.appointment_time || bookingState?.selectedTime?.start;
                        const endTime   = ap.appointment_end_time || bookingState?.selectedTime?.end;

                        const startHHMM = onlyHHMM(startTime);
                        const endHHMM   = onlyHHMM(endTime);

                        const isVirtual = (ap.appointment_mode || "").toLowerCase() === "virtual";

                        // ✅ Hora a mostrar: presencial = Ecuador / virtual = convertida a zona del usuario
                        let timeRangeTxt = "";
                        if (apDate && startHHMM && endHHMM) {
                            timeRangeTxt = isVirtual
                                ? formatRangeInTimeZone(apDate, startHHMM, endHHMM, getUserTimeZone())  // 👈 convierte desde Ecuador (-05)
                                : `${formatTimeAMPM(startHHMM)} - ${formatTimeAMPM(endHHMM)}`;         // 👈 se queda Ecuador
                        } else {
                            // fallback por si faltara algo
                            timeRangeTxt = `${formatTimeAMPM(startTime)} - ${formatTimeAMPM(endTime)}`;
                        }

                        // ✅ Zona horaria a mostrar
                        const tzLabel = isVirtual
                            ? (ap.patient_timezone_label || getUserTimeZoneLabel())
                            : "GMT-5 (Ecuador) (zona horaria de Ecuador)";

                        const payMethod = ap.payment_method || bookingState?.paymentMethod || "";
                        const total = ap.amount ?? null;

                        // Textos de estado más amigables (opcional)
                        const statusNice = ({
                        pending_verification: "Pendiente de verificación",
                        pending_payment: "Pendiente de pago",
                        confirmed: "Confirmada",
                        cancelled: "Cancelada"
                        }[status] || status);

                        $("#modal-booking-details").html(`
                        <div class="mb-2">
                            <strong>Código de reserva:</strong> <span class="badge bg-dark">${bookingId}</span>
                        </div>

                        <div class="mb-3">
                            <strong>Estado:</strong> ${statusNice}
                        </div>

                        <hr>

                        <div class="mb-1"><strong>Servicio:</strong> ${serviceName}</div>
                        <div class="mb-1"><strong>Profesional:</strong> ${employeeName}</div>
                        <div class="mb-1"><strong>Modalidad:</strong> ${modeTxt}</div>

                        <div class="mb-1"><strong>Fecha:</strong> ${dateTxt}</div>
                        <div class="mb-1"><strong>Hora:</strong> ${timeRangeTxt}</div>
                        <div class="mb-1"><strong>Zona horaria:</strong> ${tzLabel}</div>

                        ${total !== null ? `<div class="mb-1"><strong>Total:</strong> ${money(total)}</div>` : ""}

                        ${
                            payMethod === "transfer"
                            ? `
                                <div class="alert alert-info mt-3 mb-0 text-justify">
                                    <div class="fw-bold mb-1">Transferencia bancaria:</div>
                                    <p class="mb-1">
                                        Su cita quedará <b>confirmada</b> una vez validemos el comprobante.
                                    </p>
                                    <p class="mb-0">
                                        Guarde el <b>código de reserva</b> para cualquier consulta.
                                    </p>
                                </div>
                            `
                            : `
                                <div class="alert alert-success mt-3 mb-0">
                                Le enviamos un correo electrónico con el resumen de su cita.
                                </div>
                            `
                        }
                        `);

                        new bootstrap.Modal(document.getElementById("bookingSuccessModal")).show();

                        setTimeout(resetBooking, 800);
                        },
                        error: function (xhr) {

                        if (xhr.status === 409) {
                            alert(xhr.responseJSON?.message || "El turno ya no está disponible (hold expiró).");
                            clearHoldState();
                            bookingState.selectedTime = null;
                            updateTimeSlots(bookingState.selectedDate);
                            goToStep(4);
                            return;
                        }
                        if (xhr.status === 422) {
                            const res = xhr.responseJSON || {};
                            const errors = res.errors || {};
                            const firstKey = Object.keys(errors)[0];
                            const firstMsg =
                                (firstKey && errors[firstKey] && errors[firstKey][0]) ||
                                res.message ||
                                "Validación fallida (422).";

                            console.log("422 VALIDATION", { message: res.message, errors });
                            alert(firstMsg);
                            return;
                        }
                        alert("No se pudo registrar la cita. Intente nuevamente.");
                        },
                        complete: function () {
                        $btn.prop("disabled", false).html(original);
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
                        selectedTime: null,
                        appointmentMode: 'presencial',
                        paymentMethod: null
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
                // if (step === 5) return document.getElementById("consent_data")?.checked === true;
                if (step === 5) {
                    const consentOk = document.getElementById("consent_data")?.checked === true;

                    const customerForm = document.getElementById("customer-info-form");
                    const billingForm  = document.getElementById("billing-info-form");

                    const customerOk = customerForm ? customerForm.checkValidity() : false;
                    const billingOk  = billingForm ? billingForm.checkValidity() : true;

                    // 🔎 DEBUG EXACTO: qué input está bloqueando (y por qué)
                    if (!billingOk && billingForm) {
                        const inv = billingForm.querySelector(":invalid");
                        console.log(
                            "[BILLING INVALID]",
                            inv?.id,
                            inv?.name,
                            inv?.validationMessage,
                            "value:",
                            inv?.value
                        );
                    }

                    if (!customerOk && customerForm) {
                        const inv = customerForm.querySelector(":invalid");
                        console.log(
                            "[CUSTOMER INVALID]",
                            inv?.id,
                            inv?.name,
                            inv?.validationMessage,
                            "value:",
                            inv?.value
                        );
                    }

                    return consentOk && customerOk && billingOk;
                }

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

                window.updateFloatingNext = updateFloatingNext;

                // ✅ Re-evaluar botón flotante cuando el usuario escribe en Step 5
                $(document).on(
                "input change",
                "#customer-info-form input, #customer-info-form select, #customer-info-form textarea," +
                " #billing-info-form input, #billing-info-form select, #billing-info-form textarea," +
                " #consent_data, #billing_same_as_patient",
                function () {
                    setTimeout(updateFloatingNext, 0);
                }
                );

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

                $(document).on("change", 'input[name="payment_method"]', function () {
                    bookingState.paymentMethod = this.value; // 'card' o 'transfer'
                    refreshPaymentUI();

                    // ✅ Paso C: si elige tarjeta, inicializa Payphone con el total real (estándar)
                    if (bookingState.paymentMethod === "card") {
                        const { standard } = computePaymentFigures(); // standard = total real con tarjeta

                        // Limpia el contenedor por si el usuario cambia de método y vuelve
                        $("#pp-button").empty();

                        // Inicializa Payphone (usa tu función/SDK aquí)
                        initPayphoneWithTotal(standard);
                    }
                });
                // Abrir modal de Términos y Condiciones
                $(document).on("click", "#open-terms", function (e) {
                    e.preventDefault();
                    const modal = new bootstrap.Modal(
                        document.getElementById("termsModal")
                    );
                    modal.show();
                });
            });
        </script>

        <!-- VALIDACIONES PERSONALIZADAS FORM STEP 5 -->
        <script>
        (function () {
            const allowedDomains = [
            "gmail.com", "outlook.com", "hotmail.com", "yahoo.com",
            "live.com", "icloud.com", "proton.me", "protonmail.com"
            ];

            const nameEl = document.getElementById("patient_full_name");
            const addressEl = document.getElementById("patient_address");
            const emailEl = document.getElementById("patient_email");

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
                        // ✅ AGREGA ESTAS DOS
                        formatOnDisplay: false,
                        nationalMode: true,

                        preferredCountries: ["ec", "us", "co", "pe", "es"],
                        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
                    });

                    function validateEcuadorLength() {
                        const country = iti.getSelectedCountryData();

                        // Saca solo dígitos del input visible (así no importa si el browser mete espacios)
                        let digits = (input.value || "").replace(/\D/g, "");

                        if (country.iso2 === "ec") {
                            // Si el usuario puso 0 inicial (099...), se lo quitamos
                            if (digits.length >= 10 && digits.startsWith("0")) {
                            digits = digits.slice(1);
                            }

                            if (digits.startsWith("593")) digits = digits.slice(3);

                            // No permitir más de 9 dígitos
                            if (digits.length > 9) {
                            digits = digits.slice(0, 9);
                            }

                            // 🔥 Esto hace que visualmente se vea limpio (sin espacios)
                            if (input.value !== digits) input.value = digits;

                            // Validación exacta
                            if (digits.length !== 9) {
                            input.setCustomValidity("Para Ecuador, el número debe tener exactamente 9 dígitos (sin el 0 inicial).");
                            return false;
                            }
                        }

                        input.setCustomValidity("");
                        return true;
                    }

                    function enforceEcuadorMax9() {
                        const country = iti.getSelectedCountryData();

                        // Solo aplica a Ecuador
                        if (!country || country.iso2 !== "ec") return;

                        // Lo que el usuario escribió en el input (sin símbolos)
                        let digits = (input.value || "").replace(/\D/g, "");

                        // Si por alguna razón el input trae el código país pegado (593xxxxxxxxx), quítalo
                        if (digits.startsWith("593")) digits = digits.slice(3);

                        // Si escriben 0 inicial, lo quitamos
                        if (digits.startsWith("0")) digits = digits.slice(1);

                        // Limitar a 9 dígitos
                        if (digits.length > 9) digits = digits.slice(0, 9);

                        // Re-escribir el input SOLO si cambió (evita parpadeos)
                        if (input.value !== digits) input.value = digits;
                    }

                    window._itiByInputId = window._itiByInputId || {};
                    window._itiByInputId[inputId] = iti;

                    function sync() {
                        // E.164: +593991234567
                        const number = iti.getNumber();
                        hidden.value = number || "";

                        // ✅ avisar que el hidden cambió (para live sync)
                        hidden.dispatchEvent(new Event("input", { bubbles: true }));
                        hidden.dispatchEvent(new Event("change", { bubbles: true }));
                    }

                    input.addEventListener("blur", () => {
                        enforceEcuadorMax9();
                        sync();
                        validateEcuadorLength();
                    });

                    input.addEventListener("keyup", () => {
                        enforceEcuadorMax9();
                        sync();
                        validateEcuadorLength();
                    });

                    input.addEventListener("change", () => {
                        enforceEcuadorMax9();
                        sync();
                        validateEcuadorLength();
                    });

                    input.addEventListener("countrychange", () => {
                        enforceEcuadorMax9();
                        sync();
                        validateEcuadorLength();
                    });

                    // Validación: si no es válido, bloquea (si quieres)
                    input.addEventListener("invalid", () => {
                        sync();

                        if (!validateEcuadorLength()) return;

                        if (input.value.trim() && !iti.isValidNumber()) {
                            input.setCustomValidity("Ingrese un número de celular válido.");
                        } else {
                            input.setCustomValidity("");
                        }
                    });

                    input.addEventListener("input", () => {
                        enforceEcuadorMax9();
                        sync();
                        validateEcuadorLength();
                    });
                }

                setupIntlPhone("patient_phone_ui", "patient_phone");
                setupIntlPhone("billing_phone_ui", "billing-phone");

                (function () {
                    const sameChk   = document.getElementById("billing_same_as_patient");
                    const wrapper   = document.getElementById("billing-same-wrapper");
                    const helpMinor = document.getElementById("billing-same-help");

                    if (!sameChk || !wrapper) return;

                    // 👉 Cambia este ID si tu fecha de nacimiento tiene otro id
                    const dobInput = document.getElementById("patient_dob"); // <-- AJUSTA si aplica

                    // Paciente
                    const pName  = document.getElementById("patient_full_name");   // <-- AJUSTA si aplica
                    const pEmail = document.getElementById("patient_email");  // <-- AJUSTA si aplica
                    const pAddr  = document.getElementById("patient_address");
                    const pPhoneHidden = document.getElementById("patient_phone"); // hidden E164

                    // (Opcional) documento del paciente
                    const pDocType = document.getElementById("doc_type");
                    const pDocNum  = document.getElementById("doc_number");

                    // Facturación
                    const bName  = document.getElementById("billing-name");   // <-- AJUSTA si aplica
                    const bEmail = document.getElementById("billing-email");  // <-- AJUSTA si aplica
                    const bAddr  = document.getElementById("billing-address");// <-- AJUSTA si aplica
                    const bPhoneUI = document.getElementById("billing_phone_ui");
                    const bPhoneHidden = document.getElementById("billing-phone"); // hidden E164

                    // (Opcional) documento de facturación
                    const bDocType = document.getElementById("billing-doc-type");
                    const bDocNum  = document.getElementById("billing-doc-number");

                    // === Helpers visual + readonly ===
                    function setReadonlyStyle(el, isReadonly) {
                    if (!el) return;
                    el.classList.toggle("readonly-field", isReadonly);
                    }

                    function setBillingReadonly(flag) {
                        // Inputs que sí soportan readOnly
                        [bName, bEmail, bAddr, bDocNum, bPhoneUI].forEach(el => {
                            if (!el) return;
                            el.readOnly = flag;
                            el.classList.toggle("readonly-field", flag);
                        });

                        // Select NO soporta readonly → usamos disabled
                        if (bDocType) {
                            bDocType.disabled = flag;
                            bDocType.classList.toggle("readonly-field", flag);
                        }

                        // Tip: si quieres que el dropdown del país (intl-tel-input) no se pueda abrir cuando está bloqueado
                        if (bPhoneUI) {
                            bPhoneUI.disabled = flag; // bloquea 100% interacción
                            bPhoneUI.classList.toggle("readonly-field", flag);
                        }
                    }

                    // Llamar esto cada vez que se marque/desmarque
                    sameChk.addEventListener("change", () => {
                    setBillingReadonly(sameChk.checked);

                    // si tu función actual se llama copyPatientToBilling, llámala aquí
                    // (o tu función que copia/sincroniza)
                    if (sameChk.checked) {
                        copyPatientToBilling?.();
                    }
                    });

                    // Inicial: por si la casilla ya viene marcada por defecto
                    setBillingReadonly(sameChk.checked);

                    // Si no existen algunos IDs, no rompemos nada:
                    function safeVal(el) { return el ? (el.value || "").trim() : ""; }
                    function setVal(el, v) { if (el) el.value = v ?? ""; }

                    function isMinorFromDob(dobStr) {
                        // dobStr esperado: YYYY-MM-DD
                        if (!dobStr) return false; // si no hay DOB, no bloquees
                        const dob = new Date(dobStr + "T00:00:00");
                        if (isNaN(dob.getTime())) return false;

                        const today = new Date();
                        let age = today.getFullYear() - dob.getFullYear();
                        const m = today.getMonth() - dob.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                        return age < 18;
                    }

                    function refreshMinorState() {
                        const minor = isMinorFromDob(dobInput ? dobInput.value : "");
                        if (minor) {
                        sameChk.checked = false;
                        sameChk.disabled = true;
                        if (helpMinor) helpMinor.style.display = "block";
                        } else {
                        sameChk.disabled = false;
                        if (helpMinor) helpMinor.style.display = "none";
                        }
                    }

                    function copyPatientToBilling() {
                        setVal(bName,  safeVal(pName));
                        setVal(bEmail, safeVal(pEmail));
                        setVal(bAddr,  safeVal(pAddr));

                        // Copiar documento del paciente → facturación (opcional pero recomendado)
                        if (pDocType && bDocType) bDocType.value = pDocType.value;
                        if (pDocNum && bDocNum) setVal(bDocNum, safeVal(pDocNum));

                        // Teléfono: copiar SOLO los 9 dígitos (Ecuador) al input visible de facturación
                        const pPhoneUI = document.getElementById("patient_phone_ui");
                        let phoneDigits = (pPhoneUI ? pPhoneUI.value : "").replace(/\D/g, "");

                        // Limpieza extra por si entra 593 o 0
                        if (phoneDigits.startsWith("593")) phoneDigits = phoneDigits.slice(3);
                        if (phoneDigits.startsWith("0")) phoneDigits = phoneDigits.slice(1);

                        // Limitar a 9 dígitos
                        if (phoneDigits.length > 9) phoneDigits = phoneDigits.slice(0, 9);

                        // Setear el input visible (sin 593, sin espacios)
                        if (bPhoneUI) {
                            setVal(bPhoneUI, phoneDigits);

                            // 🔥 importante: disparar eventos para que setupIntlPhone haga sync() y llene billing-phone (E.164)
                            bPhoneUI.dispatchEvent(new Event("input", { bubbles: true }));
                            bPhoneUI.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    }

                    // Cuando marcan/desmarcan
                    sameChk.addEventListener("change", () => {
                        refreshMinorState();
                        if (sameChk.disabled) return;

                        if (sameChk.checked) {
                        copyPatientToBilling();
                        setBillingReadonly(true);
                        } else {
                        setBillingReadonly(false);
                        }
                        // 🔥 ESTA ES LA LÍNEA CLAVE
                        setTimeout(updateFloatingNext, 0);
                    });

                    // Si el usuario edita datos del paciente y el checkbox está marcado, sincroniza en vivo
                    // ✅ Live sync: TODO lo del paciente que debe replicarse mientras el checkbox esté marcado
                    [pName, pEmail, pAddr, pDocType, pDocNum, pPhoneHidden].forEach(el => {
                    if (!el) return;

                    el.addEventListener("input", () => {
                        if (sameChk.checked && !sameChk.disabled) copyPatientToBilling();
                    });

                    el.addEventListener("change", () => {
                        if (sameChk.checked && !sameChk.disabled) copyPatientToBilling();
                    });
                    });

                    // ✅ (PRO) Si cambia documento en FACTURACIÓN, desmarcar checkbox y desbloquear
                    function uncheckSameIfNeeded() {
                        if (!sameChk.checked) return;
                        sameChk.checked = false;
                        setBillingReadonly(false);
                    }

                    [bDocType, bDocNum].forEach(el => {
                        if (!el) return;
                        el.addEventListener("change", uncheckSameIfNeeded);
                        el.addEventListener("input", uncheckSameIfNeeded);
                    });

                    if (dobInput) {
                        dobInput.addEventListener("change", refreshMinorState);
                        dobInput.addEventListener("input", refreshMinorState);
                    }

                    // Estado inicial
                    refreshMinorState();
                })();
            })();
        </script>

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
        <!-- Modal Términos y Condiciones -->
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">
                        Términos y Condiciones
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body terms-body">
                        <p class="small text-muted mb-3">
                        Centro Médico FamySALUD ·
                        <a href="https://www.famysalud.com.ec" target="_blank">
                            www.famysalud.com.ec
                        </a>
                        </p>

                        <h6>1. Confirmación de la cita</h6>
                        <p>
                        La cita se considera confirmada una vez que el pago ha sido procesado correctamente o,
                        en el caso de transferencia bancaria, cuando el comprobante ha sido enviado y validado
                        por el Centro Médico FamySALUD.
                        </p>

                        <h6>2. Pagos y reembolsos</h6>
                        <p>
                        Los pagos realizados no son reembolsables.
                        En caso de no poder asistir a la cita, el paciente podrá solicitar un reagendamiento,
                        sujeto a disponibilidad y a las políticas vigentes del centro médico.
                        </p>

                        <h6>3. Reagendamiento de citas</h6>
                        <p>
                        Las solicitudes de reagendamiento deberán ser gestionadas exclusivamente a través de
                        los canales oficiales del Centro Médico FamySALUD, los cuales se encuentran detallados
                        en nuestra página web oficial:
                        <a href="https://www.famysalud.com.ec" target="_blank">
                            www.famysalud.com.ec
                        </a>.
                        </p>

                        <h6>4. Responsabilidad del paciente</h6>
                        <p>
                        Es responsabilidad del paciente ingresar correctamente sus datos personales,
                        seleccionar adecuadamente el servicio, profesional, fecha y modalidad de la cita.
                        </p>

                        <h6>5. Uso del sistema</h6>
                        <p>
                        Este sistema de agendamiento tiene como finalidad facilitar la reserva de citas médicas.
                        El Centro Médico FamySALUD se reserva el derecho de validar la información proporcionada
                        y de contactar al paciente en caso de ser necesario.
                        </p>

                        <h6>6. Aceptación de los términos</h6>
                        <p>
                        Al marcar la casilla “Acepto los Términos y Condiciones” y continuar con el proceso de pago,
                        el paciente declara haber leído, comprendido y aceptado las condiciones aquí descritas.
                        </p>

                        <div class="alert alert-warning mt-4 text-justify">
                            <strong>Importante:</strong>
                            Los pagos no son reembolsables. Las citas pueden ser reagendadas según disponibilidad.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>