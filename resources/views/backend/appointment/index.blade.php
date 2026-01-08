@extends('adminlte::page')

@section('title', 'Todas las citas · FamySalud')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Todas las citas</h1>
        </div>

    </div>
@stop

@section('content')
    <!-- Modal -->
    <form id="appointmentStatusForm" method="POST" action="{{ route('appointments.update.status') }}">
        @csrf
        <input type="hidden" name="appointment_id" id="modalAppointmentId">
        <input type="hidden" name="status" id="modalStatusHidden" value="">
        <input type="hidden" name="transfer_validation_status" id="modalTransferValidationStatusInput" value="">
        <input type="hidden" name="transfer_validation_notes" id="modalTransferValidationNotesInput" value="">
        <input type="hidden" id="modalPaymentMethodRaw" value="">

        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="modal-title mb-0">Detalles de la cita</h5>

                            {{-- ✅ Subtítulo informativo: Código de reserva --}}
                            <div class="small text-muted mt-1">
                                Código de reserva: <strong id="modalBookingCode">N/A</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            {{-- ✅ Indicador sutil de modo (solo UI) --}}
                            <span id="apptModeBadge" class="badge badge-light mr-2" style="display:none;">
                                Editando
                            </span>

                            {{-- ✅ Dropdown Acciones --}}
                            <div class="dropdown mr-2">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle"
                                        type="button"
                                        id="apptActionsDropdown"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                    Acciones
                                </button>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="apptActionsDropdown">
                                    <button type="button" class="dropdown-item" id="btnEnterEditMode">
                                        <i class="fas fa-pen mr-2"></i>Editar datos
                                    </button>

                                    <div class="dropdown-divider"></div>

                                    <button type="button" class="dropdown-item" id="btnReagendar">
                                        <i class="fas fa-calendar-alt mr-2"></i>Reagendar
                                    </button>

                                    <button type="button" class="dropdown-item" id="btnConfirmarCita">
                                        <i class="fas fa-check-circle mr-2"></i>Confirmar cita
                                    </button>

                                    <button type="button" class="dropdown-item" id="btnNoAsistio">
                                        <i class="fas fa-user-times mr-2"></i>Marcar como no asistida
                                    </button>

                                    <button type="button" class="dropdown-item text-danger" id="btnCancelarCita">
                                        <i class="fas fa-ban mr-2"></i>Cancelar cita
                                    </button>

                                    <div class="dropdown-divider"></div>

                                    <button type="button" class="dropdown-item" id="btnVerHistorial">
                                        <i class="fas fa-history mr-2"></i>Ver historial de cambios
                                    </button>
                                </div>
                            </div>

                            {{-- ✅ Cerrar modal --}}
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body">
                        {{-- ✅ Banner: modo edición (solo UI) --}}
                        <div id="editModeBanner" class="alert alert-warning py-2 mb-3" style="display:none;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mb-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Modo edición:</strong> estás editando datos de la cita.
                                </div>
                                <small class="text-muted">No se guardará nada hasta que presiones “Guardar cambios”.</small>
                            </div>
                        </div>

                        {{-- =========================
                            SECCIÓN 1 (NO COLAPSABLE)
                            Resumen de la cita (2 columnas)
                        ========================== --}}
                        <div class="p-3 mb-3 rounded border bg-light">
                            <h6 class="mb-3 font-weight-bold text-primary">Resumen de la cita</h6>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Paciente</div>
                                    <div class="text-dark" id="modalAppointmentName">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Profesional</div>
                                    <div class="text-dark" id="modalStaff">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Área de atención</div>
                                    <div class="text-dark" id="modalArea">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Servicio</div>
                                    <div class="text-dark" id="modalService">N/A</div>
                                </div>

                                <div class="col-md-12 mb-2">
                                    <div class="small text-muted">Fecha y hora de la cita</div>
                                    <div class="text-dark" id="modalDateTime">N/A</div>
                                </div>

                                <div class="col-md-6 mb-0">
                                    <div class="small text-muted">Estado de la cita</div>
                                    <div class="text-dark" id="modalStatusBadge">N/A</div>
                                </div>

                                <div class="col-md-6 mb-0">
                                    <div class="small text-muted">Estado del pago</div>
                                    <div class="text-dark" id="modalPaymentStatusBadge">
                                        <span class="badge px-2 py-1" style="background-color:#95a5a6;color:white;">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- =========================
                            SECCIÓN 2 (COLAPSABLE - ABIERTA POR DEFECTO)
                            Datos del paciente
                        ========================== --}}
                        <div class="p-3 mb-3 rounded border bg-light">
                            <a class="d-flex align-items-center justify-content-between text-decoration-none"
                            data-toggle="collapse"
                            href="#collapsePatientData"
                            role="button"
                            aria-expanded="true"
                            aria-controls="collapsePatientData">
                                <h6 class="mb-0 font-weight-bold text-primary">Datos del paciente</h6>
                                <span class="text-muted"><i class="fas fa-chevron-down"></i></span>
                            </a>

                            <div class="collapse show mt-3" id="collapsePatientData">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Nombre del paciente</div>
                                        <div class="text-dark" id="modalPatientFullName">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Tipo de documento</div>
                                        <div class="text-dark" id="modalDocType">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Número de documento</div>
                                        <div class="text-dark" id="modalDocNumber">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Correo</div>
                                        <div class="text-dark" id="modalEmail">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Teléfono</div>
                                        <div class="text-dark" id="modalPhone">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Dirección</div>
                                        <div class="text-dark" id="modalAddress">N/A</div>
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted">Zona horaria del paciente</div>
                                        <div class="text-dark" id="modalPatientTimezone">N/A</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- =========================
                            SECCIÓN 3 (NO COLAPSABLE)
                            Detalles de la cita (2 columnas)
                        ========================== --}}
                        <div class="p-3 mb-3 rounded border bg-light">
                            <h6 class="mb-3 font-weight-bold text-primary">Detalles de la cita</h6>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Modalidad de la cita</div>
                                    <div class="text-dark" id="modalAppointmentMode">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Fecha y hora de la cita</div>
                                    <div class="text-dark" id="modalDateTime2">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Registrada el</div>
                                    <div class="text-dark" id="modalCreatedAt">N/A</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Notas del paciente</div>
                                    <div class="text-dark" id="modalNotes">N/A</div>
                                </div>
                            </div>
                        </div>

                        {{-- =========================
                            SECCIÓN 4 (COLAPSABLE)
                            Datos de facturación (2 columnas)
                            Abierta por defecto si es distinta al paciente
                        ========================== --}}
                        <div class="p-3 mb-3 rounded border bg-light">
                            <a class="d-flex align-items-center justify-content-between text-decoration-none"
                            data-toggle="collapse"
                            href="#collapseBillingData"
                            role="button"
                            aria-expanded="false"
                            aria-controls="collapseBillingData">
                                <h6 class="mb-0 font-weight-bold text-primary">Datos de facturación</h6>
                                <span class="text-muted"><i class="fas fa-chevron-down"></i></span>
                            </a>

                            {{-- Texto sutil cuando facturación = paciente --}}
                            <div id="modalBillingSameNote" class="small text-muted font-italic mt-1" style="display:none;">
                                Se usaron los mismos datos del paciente
                            </div>

                            <div class="collapse mt-3" id="collapseBillingData">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Nombre para facturación</div>
                                        <div class="text-dark" id="modalBillingName">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Tipo de documento</div>
                                        <div class="text-dark" id="modalBillingDocType">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Número de documento</div>
                                        <div class="text-dark" id="modalBillingDocNumber">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Correo de facturación</div>
                                        <div class="text-dark" id="modalBillingEmail">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Teléfono de facturación</div>
                                        <div class="text-dark" id="modalBillingPhone">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Dirección de facturación</div>
                                        <div class="text-dark" id="modalBillingAddress">N/A</div>
                                    </div>

                                    <!-- <div class="col-md-12 mb-0">
                                        <div class="small text-muted">Zona horaria de facturación</div>
                                        <div class="text-dark" id="modalBillingTimezone">N/A</div>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        {{-- =========================
                            SECCIÓN 5 (DINÁMICA)
                            Información de pago (2 columnas)
                            Cambia según método: card / transfer
                        ========================== --}}
                        <div class="p-3 mb-3 rounded border bg-light" id="paymentSectionWrapper" style="display:none;">
                            <h6 class="mb-3 font-weight-bold text-primary">Información de pago</h6>

                            {{-- BLOQUE TARJETA --}}
                            <div id="paymentCardBlock" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Método</div>
                                        <div class="text-dark" id="modalPaymentMethodLabel">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Client Transaction ID</div>
                                        {{-- OJO: es largo, por eso usamos estilo wrap --}}
                                        <div class="text-dark" id="modalClientTransactionId" style="word-break: break-all;">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto</div>
                                        <div class="text-dark" id="modalPaymentAmount">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha del pago</div>
                                        <div class="text-dark" id="modalPaymentDate">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-0">
                                        <div class="small text-muted">Estado del pago</div>
                                        <div class="text-dark" id="modalPaymentStatusBadge2">N/A</div>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOQUE TRANSFERENCIA (stand-by) --}}
                            <div id="paymentTransferBlock" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Método</div>
                                        <div class="text-dark" id="modalTransferMethodLabel">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto</div>
                                        <div class="text-dark" id="modalTransferAmount">N/A</div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <div class="small text-muted font-weight-bold">Datos de la transferencia</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Banco de origen</div>
                                        <div class="text-dark" id="modalTransferBankOrigin">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Nombre del titular</div>
                                        <div class="text-dark" id="modalTransferPayerName">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha de la transferencia</div>
                                        <div class="text-dark" id="modalTransferDate">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Número de referencia</div>
                                        <div class="text-dark" id="modalTransferReference">N/A</div>
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted">Comprobante</div>
                                        <div class="text-dark" id="modalTransferReceipt">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>
                                    </div>

                                    {{-- =========================
                                        SUBSECCIÓN: Validación de transferencia (solo admin / solo transfer)
                                    ========================== --}}
                                    <div class="col-md-12 mt-3">
                                        <div class="small text-muted font-weight-bold">Validación de transferencia</div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <div class="small text-muted">Estado de validación</div>
                                        <select class="form-control form-control-sm w-100" id="modalTransferValidationSelect">
                                            <option value="">Sin revisar</option>
                                            <option value="validated">Validada</option>
                                            <option value="rejected">Rechazada</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            “Validada” marcará la cita como "Pagada". “Rechazada” pasará la cita a "En espera".
                                        </small>
                                    </div>

                                    <div class="col-md-12 mb-2" id="transferValidationMeta" style="display:none;">
                                        <div class="small text-muted">Última validación</div>
                                        <div class="text-dark">
                                            <span id="modalTransferValidatedAt">N/A</span>
                                            <span class="text-muted">·</span>
                                            <span id="modalTransferValidatedBy">N/A</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-0" id="transferValidationNotesWrapper" style="display:none;">
                                        <div class="small text-muted">
                                            Observaciones
                                            <span id="transferNotesOptional" class="text-muted" style="display:none;">(opcional)</span>
                                            <span id="transferNotesRequired" class="text-danger" style="display:none;">(obligatorias)</span>
                                        </div>

                                        <textarea class="form-control form-control-sm" id="modalTransferValidationNotes" rows="2"
                                            placeholder="Ej: Escribe una observación..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                       
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" id="btnCancelEditMode" style="display:none;">
                            Cancelar edición
                        </button>

                        <button type="submit" id="btnSaveChanges" disabled
                            onclick="return confirm('¿Estás seguro que quieres guardar los cambios?')"
                            class="btn btn-danger">Guardar cambios</button>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- ✅ Modal: Vista rápida del comprobante -->
    <div class="modal fade" id="transferReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-receipt" role="document">
        <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Comprobante de transferencia</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">
            <div id="receiptLoading" class="text-center py-3" style="display:none;">
            <span class="text-muted">Cargando comprobante...</span>
            </div>

            <div id="receiptError" class="alert alert-danger" style="display:none;">
            No se pudo cargar el comprobante.
            </div>

            <div id="receiptViewer">
                <img id="receiptImg" src="" alt="Comprobante">
                <iframe id="receiptPdf" src="" title="Comprobante PDF"></iframe>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-success" id="receiptDownloadBtn">
                Descargar
            </button>

            <a href="#" target="_blank" id="receiptOpenNewTab" class="btn btn-primary">
                Pantalla completa
            </a>

            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cerrar
            </button>
        </div>

        </div>
    </div>
    </div>

    <div class="">
        @if (session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('error') }}</strong>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <strong>Errores:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Content Header (Page header) -->
        <!-- Content Header (Page header) -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card py-2 px-2">

                            <div class="card-body p-0">
                                <table id="myTable" class="table table-striped projects ">
                                    <thead>
                                        <tr>
                                            <th style="width: 1%">
                                                #
                                            </th>
                                            <th style="width: 15%">
                                                Paciente
                                            </th>
                                            {{-- 
                                            <th style="width: 15%">
                                                Correo
                                            </th>
                                            --}}
                                            <th style="width: 10%">
                                                Teléfono
                                            </th>
                                            <th style="width: 10%">
                                                Profesional
                                            </th>
                                            <th style="width: 12%">
                                                Área
                                            </th>

                                            <th style="width: 10%">
                                                Servicio
                                            </th>
                                            <th style="width: 10%">
                                                Fecha
                                            </th>
                                            <th style="width: 10%">
                                                Hora
                                            </th>


                                            <th style="width: 15%" class="text-center">
                                                Estado
                                            </th>
                                            <th style="width: 18%">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $statusColors = [
                                                'Pending payment' => '#f39c12',
                                                'Processing' => '#3498db',
                                                'Paid' => '#2ecc71',
                                                'Cancelled' => '#ff0000',
                                                'Completed' => '#008000',
                                                'On Hold' => '#95a5a6',
                                                'Rescheduled' => '#f1c40f',
                                                'No Show' => '#e67e22',
                                            ];

                                            $statusLabels = [
                                                'Pending payment' => 'Pendiente de pago',
                                                'Processing' => 'Procesando',
                                                'Paid' => 'Pagada',
                                                'Cancelled' => 'Cancelado',
                                                'Completed' => 'Completado',
                                                'On Hold' => 'En espera',
                                                'Rescheduled' => 'Reprogramado',
                                                'No Show' => 'No asistió',
                                                'pending_verification' => 'Pendiente de verificación',
                                            ];
                                        @endphp
                                        @foreach ($appointments as $appointment)
                                            <tr>
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td>
                                                    <a>
                                                        {{ $appointment->patient_full_name }}
                                                    </a>

                                                    <div class="small text-muted">
                                                        <span class="font-weight-bold">
                                                            {{ $appointment->booking_id ?? ('FS-' . $appointment->id) }}
                                                        </span>
                                                    </div>
                                                    {{-- 
                                                    <br>
                                                    <small>
                                                        {{ $appointment->created_at->format('d M Y') }}
                                                    </small>
                                                    --}}
                                                </td>
                                                {{--
                                                <td>
                                                    {{ $appointment->patient_email }}
                                                </td>
                                                --}}
                                                <td>
                                                    {{ $appointment->patient_phone }}
                                                </td>
                                                <td>
                                                    {{ $appointment->employee->user->name }}
                                                </td>
                                                <td>
                                                    {{ $appointment->service->category->title ?? 'NA' }}
                                                </td>
                                                <td>
                                                    {{ $appointment->service->title ?? 'NA' }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d M Y') }}
                                                </td>
                                                <td>
                                                    {{ 
                                                        \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A')
                                                    }}
                                                    -
                                                    {{
                                                        \Carbon\Carbon::parse($appointment->appointment_end_time)->format('g:i A')
                                                    }}
                                                </td>
                                                <td>
                                                    @php
                                                        $rawStatus = $appointment->status;

                                                        // Normalizar el status para evitar problemas de mayúsculas/minúsculas
                                                        $status = strtolower(str_replace(' ', '_', $rawStatus));

                                                        $statusColors = [
                                                            'pending_payment' => '#f39c12',
                                                            'processing' => '#3498db',
                                                            'paid' => '#2ecc71',
                                                            'cancelled' => '#ff0000',
                                                            'completed' => '#008000',
                                                            'on_hold' => '#95a5a6',
                                                            'rescheduled' => '#f1c40f',
                                                            'no_show' => '#e67e22',
                                                            'pending_verification' => '#7f8c8d',
                                                        ];

                                                        $statusLabels = [
                                                            'pending_payment' => 'Pendiente de pago',
                                                            'processing' => 'Procesando',
                                                            'paid' => 'Pagada',
                                                            'cancelled' => 'Cancelada',
                                                            'completed' => 'Completada',
                                                            'on_hold' => 'En espera',
                                                            'rescheduled' => 'Reprogramada',
                                                            'no_show' => 'No asistió',
                                                            'pending_verification' => 'Pendiente de verificación',
                                                        ];

                                                        $color = $statusColors[$status] ?? '#7f8c8d';
                                                        $label = $statusLabels[$status] ?? 'Estado desconocido';
                                                    @endphp
                                                    <span class="badge px-2 py-1"
                                                        style="background-color: {{ $color }}; color: white;">
                                                        {{ $label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm py-0 px-1 view-appointment-btn"
                                                        data-toggle="modal" data-target="#appointmentModal"
                                                        data-id="{{ $appointment->id }}"
                                                        data-booking-code="{{ $appointment->booking_id ?? ('FS-' . $appointment->id) }}"
                                                        data-name="{{ $appointment->patient_full_name }}"
                                                        data-area="{{ $appointment->service->category->title ?? 'No definida' }}"
                                                        data-service="{{ 
                                                        $appointment->service->title ?? 'MA' }}"
                                                        data-email="{{ $appointment->patient_email }}"
                                                        data-phone="{{ $appointment->patient_phone }}"
                                                        data-doc-type="{{ $appointment->patient_doc_type }}"
                                                        data-doc-number="{{ $appointment->patient_doc_number }}"
                                                        data-address="{{ $appointment->patient_address }}"
                                                        data-timezone="{{ $appointment->patient_timezone }}"
                                                        data-timezone-label="{{ $appointment->patient_timezone_label }}"
                                                        data-employee="{{ $appointment->employee->user->name }}"
                                                        data-date="{{ $appointment->appointment_date }}"
                                                        data-start-time="{{ $appointment->appointment_time }}"
                                                        data-end-time="{{ $appointment->appointment_end_time }}"
                                                        data-start="{{ $appointment->appointment_date . ' ' . $appointment->appointment_time }}"
                                                        data-amount="{{ $appointment->amount }}"
                                                        data-notes="{{ $appointment->patient_notes }}"
                                                        data-appointment-mode="{{ $appointment->appointment_mode }}"
                                                        data-billing-name="{{ $appointment->billing_name ?? '' }}"
                                                        data-billing-doc-type="{{ $appointment->billing_doc_type ?? '' }}"
                                                        data-billing-doc-number="{{ $appointment->billing_doc_number ?? '' }}"
                                                        data-billing-email="{{ $appointment->billing_email ?? '' }}"
                                                        data-billing-phone="{{ $appointment->billing_phone ?? '' }}"
                                                        data-billing-address="{{ $appointment->billing_address ?? '' }}"
                                                        data-billing-timezone="{{ $appointment->billing_timezone ?? '' }}"
                                                        data-billing-timezone-label="{{ $appointment->billing_timezone_label ?? '' }}"
                                                        data-payment-method="{{ $appointment->payment_method ?? '' }}"
                                                        data-client-transaction-id="{{ $appointment->client_transaction_id ?? '' }}"
                                                        data-payment-status="{{ $appointment->payment_status ?? '' }}"
                                                        data-transfer-bank-origin="{{ $appointment->transfer_bank_origin ?? '' }}"
                                                        data-transfer-payer-name="{{ $appointment->transfer_payer_name ?? '' }}"
                                                        data-transfer-date="{{ $appointment->transfer_date ?? '' }}"
                                                        data-transfer-reference="{{ $appointment->transfer_reference ?? '' }}"
                                                        data-transfer-receipt-path="{{ $appointment->transfer_receipt_path ?? '' }}"
                                                        data-transfer-validation-status="{{ $appointment->transfer_validation_status ?? '' }}"
                                                        data-transfer-validated-at="{{ $appointment->transfer_validated_at ?? '' }}"
                                                        data-transfer-validated-by="{{ optional($appointment->transferValidatedBy)->name ?? '' }}"
                                                        data-transfer-validation-notes="{{ $appointment->transfer_validation_notes ?? '' }}"
                                                        data-created-at="{{ $appointment->created_at }}"
                                                        data-status="{{ $appointment->status }}">Ver detalles</button>
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
    #editModeBanner{
        border-left: 4px solid #f39c12;
    }
  /* ✅ Modal de comprobante: tamaño fijo al viewport (no gigante) */
    .modal-dialog.modal-receipt{
    max-width: 900px;
    width: calc(100% - 2rem);
    margin: 1rem auto;
    }

    /* ✅ El modal ocupa alto fijo del viewport (esto sí "amarra" el flex) */
    #transferReceiptModal .modal-content{
    height: calc(100vh - 2rem);     /* <-- antes era max-height */
    display: flex;
    flex-direction: column;
    }

    /* ✅ IMPORTANTE en flex: permite que el body se encoja y calcule bien */
    #transferReceiptModal .modal-body{
    flex: 1 1 auto;
    min-height: 0;                 /* <-- clave */
    overflow: hidden;              /* el scroll lo maneja el visor */
    padding: 12px;
    }

    /* ✅ Visor interno ocupa todo el body */
    #receiptViewer{
    width: 100%;
    height: 100%;
    min-height: 0;                 /* <-- clave */
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    }

    /* ✅ Imagen: SIEMPRE encaja completa */
    #receiptViewer img{
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    display: none;
    border-radius: 6px;
    }

    /* ✅ PDF: ocupa todo el visor */
    #receiptViewer iframe{
    width: 100%;
    height: 100%;
    border: 0;
    display: none;
    border-radius: 6px;
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
                responsive: true,
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
        $(document).on('click', '.view-appointment-btn', function() {
            // Set modal fields
            $('#modalAppointmentId').val($(this).data('id'));
            // ✅ Código de reserva (booking_id)
            $('#modalBookingCode').text($(this).data('booking-code') || 'N/A');
            $('#modalAppointmentName').text($(this).data('name'));
            $('#modalArea').text($(this).data('area'));
            $('#modalService').text($(this).data('service'));
            $('#modalEmail').text($(this).data('email'));
            $('#modalPhone').text($(this).data('phone'));
            $('#modalStaff').text($(this).data('employee'));

            // ===== SECCIÓN 2: Datos del paciente =====
            $('#modalPatientFullName').text($(this).data('name') || 'N/A');

            // Estos quedan en N/A hasta que los conectes con data-* reales
            const docType = $(this).data('doc-type');
            const docNumber = $(this).data('doc-number');
            const address = $(this).data('address');

            const tz = $(this).data('timezone');
            const tzLabel = $(this).data('timezone-label');

            // Formatear tipo de documento: Cédula / Pasaporte / RUC
            let docTypeFinal = 'N/A';

            if (docType && String(docType).trim() !== '') {
                const raw = String(docType).trim();

                // Normalizar para comparar (sin tildes y en minúsculas)
                const normalized = raw
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, ''); // quita tildes

                if (normalized === 'cedula') {
                    docTypeFinal = 'Cédula';
                } else if (normalized === 'ruc') {
                    docTypeFinal = 'RUC';
                } else {
                    // Capitalizar cada palabra (por si viene "pasaporte" o "pasaporte diplomático")
                    docTypeFinal = normalized
                        .split(/\s+/)
                        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
                        .join(' ');
                }
            }

            $('#modalDocType').text(docTypeFinal);
            $('#modalDocNumber').text(docNumber ? String(docNumber) : 'N/A');
            $('#modalAddress').text(address ? String(address) : 'N/A');

            // Timezone: "America-Bogota" -> "America/Bogota" y concatenar "(GMT-5)"
            let tzFormatted = tz ? String(tz).replace('-', '/') : '';
            let tzFinal = 'N/A';

            if (tzFormatted && tzLabel) {
                tzFinal = `${tzFormatted} (${tzLabel})`;
            } else if (tzFormatted) {
                tzFinal = tzFormatted;
            } else if (tzLabel) {
                tzFinal = `(${tzLabel})`;
            }

            $('#modalPatientTimezone').text(tzFinal);
            // Fecha y horas
            const date = $(this).data('date');
            const startTime = $(this).data('start-time');
            const endTime = $(this).data('end-time');

            // ✅ Parsear YYYY-MM-DD sin que se corra por zona horaria
            let formattedDate = 'N/A';
            if (date) {
                const [y, m, d] = String(date).split('-').map(Number);
                const dateObj = new Date(y, (m || 1) - 1, d || 1); // local (sin UTC shift)
                formattedDate = dateObj.toLocaleDateString('es-EC', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            // Función para hora AM/PM
            function formatTime(time) {
                const [hours, minutes] = time.split(':');
                const dateObj = new Date();
                dateObj.setHours(hours, minutes);
                return dateObj.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }

            const formattedTime =
                `${formatTime(startTime)} – ${formatTime(endTime)}`;

            $('#modalDateTime').text(`${formattedDate} · ${formattedTime}`);

            // ===== SECCIÓN 3: Detalles de la cita =====

            // Modalidad (presencial / virtual)
            const apptModeRaw = $(this).data('appointment-mode');
            let apptMode = 'N/A';
            if (apptModeRaw && String(apptModeRaw).trim() !== '') {
                const m = String(apptModeRaw).trim().toLowerCase();
                if (m === 'virtual' || m === 'online') apptMode = 'Virtual';
                else if (m === 'presencial' || m === 'in_person' || m === 'in-person') apptMode = 'Presencial';
                else apptMode = m.charAt(0).toUpperCase() + m.slice(1);
            }
            $('#modalAppointmentMode').text(apptMode);

            // Reusar el mismo “Fecha y hora de la cita” en Sección 3
            $('#modalDateTime2').text(`${formattedDate} · ${formattedTime}`);

            // Fecha/hora de registro (auditoría)
            const createdAtRaw = $(this).data('created-at');
            let createdAtFinal = 'N/A';

            if (createdAtRaw && String(createdAtRaw).trim() !== '') {
                const dt = new Date(String(createdAtRaw));
                if (!isNaN(dt.getTime())) {
                    const datePart = dt.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });
                    const timePart = dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                    createdAtFinal = `${datePart} · ${timePart}`;
                } else {
                    createdAtFinal = String(createdAtRaw); // fallback
                }
            }
            $('#modalCreatedAt').text(createdAtFinal);

            // ===== Helpers =====
            function normalizeValue(v) {
                return String(v || '')
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')  // quita tildes
                    .replace(/\s+/g, ' ');
            }

            function formatDocTypeLabel(docType) {
                let out = 'N/A';
                if (docType && String(docType).trim() !== '') {
                    const raw = String(docType).trim();
                    const normalized = raw
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '');

                    if (normalized === 'cedula') out = 'Cédula';
                    else if (normalized === 'ruc') out = 'RUC';
                    else {
                        out = normalized
                            .split(/\s+/)
                            .map(w => w.charAt(0).toUpperCase() + w.slice(1))
                            .join(' ');
                    }
                }
                return out;
            }

            function formatTimezone(tz, tzLabel) {
                let tzFormatted = tz ? String(tz).replace('-', '/') : '';
                let final = 'N/A';

                if (tzFormatted && tzLabel) final = `${tzFormatted} (${tzLabel})`;
                else if (tzFormatted) final = tzFormatted;
                else if (tzLabel) final = `(${tzLabel})`;

                return final;
            }

            // ===== SECCIÓN 4: Datos de facturación =====

            // Paciente (ya existen en tu modal / data-*)
            const pName = $(this).data('name') || '';
            const pEmail = $(this).data('email') || '';
            const pPhone = $(this).data('phone') || '';
            const pDocTypeRaw = $(this).data('doc-type') || '';
            const pDocNumber = $(this).data('doc-number') || '';
            const pAddress = $(this).data('address') || '';
            const pTz = $(this).data('timezone') || '';
            const pTzLabel = $(this).data('timezone-label') || '';

            const patientTzFinal = formatTimezone(pTz, pTzLabel);

            // Facturación (data-*)
            const bNameRaw = $(this).data('billing-name');
            const bDocTypeRaw = $(this).data('billing-doc-type');
            const bDocNumberRaw = $(this).data('billing-doc-number');
            const bEmailRaw = $(this).data('billing-email');
            const bPhoneRaw = $(this).data('billing-phone');
            const bAddressRaw = $(this).data('billing-address');
            const bTzRaw = $(this).data('billing-timezone');
            const bTzLabelRaw = $(this).data('billing-timezone-label');

            // Si facturación viene vacío, mostramos paciente (pero igual se mantiene sección separada)
            const billingName = (bNameRaw && String(bNameRaw).trim() !== '') ? String(bNameRaw) : String(pName || 'N/A');
            const billingDocType = (bDocTypeRaw && String(bDocTypeRaw).trim() !== '') ? String(bDocTypeRaw) : String(pDocTypeRaw || '');
            const billingDocNumber = (bDocNumberRaw && String(bDocNumberRaw).trim() !== '') ? String(bDocNumberRaw) : String(pDocNumber || 'N/A');
            const billingEmail = (bEmailRaw && String(bEmailRaw).trim() !== '') ? String(bEmailRaw) : String(pEmail || 'N/A');
            const billingPhone = (bPhoneRaw && String(bPhoneRaw).trim() !== '') ? String(bPhoneRaw) : String(pPhone || 'N/A');
            const billingAddress = (bAddressRaw && String(bAddressRaw).trim() !== '') ? String(bAddressRaw) : String(pAddress || 'N/A');

            const billingTzFinal = (() => {
                const hasBtz = bTzRaw && String(bTzRaw).trim() !== '';
                const hasBtzLabel = bTzLabelRaw && String(bTzLabelRaw).trim() !== '';
                if (hasBtz || hasBtzLabel) return formatTimezone(bTzRaw, bTzLabelRaw);
                return patientTzFinal || 'N/A';
            })();

            // Pintar en el modal
            $('#modalBillingName').text(billingName || 'N/A');
            $('#modalBillingDocType').text(formatDocTypeLabel(billingDocType));
            $('#modalBillingDocNumber').text(billingDocNumber || 'N/A');
            $('#modalBillingEmail').text(billingEmail || 'N/A');
            $('#modalBillingPhone').text(billingPhone || 'N/A');
            $('#modalBillingAddress').text(billingAddress || 'N/A');
            $('#modalBillingTimezone').text(billingTzFinal || 'N/A');

            // Determinar si facturación es distinta al paciente
            const billingFieldsFilled =
                (bNameRaw && String(bNameRaw).trim() !== '') ||
                (bDocTypeRaw && String(bDocTypeRaw).trim() !== '') ||
                (bDocNumberRaw && String(bDocNumberRaw).trim() !== '') ||
                (bEmailRaw && String(bEmailRaw).trim() !== '') ||
                (bPhoneRaw && String(bPhoneRaw).trim() !== '') ||
                (bAddressRaw && String(bAddressRaw).trim() !== '') ||
                (bTzRaw && String(bTzRaw).trim() !== '') ||
                (bTzLabelRaw && String(bTzLabelRaw).trim() !== '');

            let isDifferent = false;

            if (billingFieldsFilled) {
                // Compara campo a campo (normalizado)
                if (normalizeValue(billingName) !== normalizeValue(pName)) isDifferent = true;
                if (normalizeValue(formatDocTypeLabel(billingDocType)) !== normalizeValue(formatDocTypeLabel(pDocTypeRaw))) isDifferent = true;
                if (normalizeValue(billingDocNumber) !== normalizeValue(pDocNumber)) isDifferent = true;
                if (normalizeValue(billingEmail) !== normalizeValue(pEmail)) isDifferent = true;
                if (normalizeValue(billingPhone) !== normalizeValue(pPhone)) isDifferent = true;
                if (normalizeValue(billingAddress) !== normalizeValue(pAddress)) isDifferent = true;
                if (normalizeValue(billingTzFinal) !== normalizeValue(patientTzFinal)) isDifferent = true;
            }

            // Mostrar nota y abrir/cerrar colapso según regla UX
            if (isDifferent) {
                $('#modalBillingSameNote').hide();
                $('#collapseBillingData').collapse('show'); // abierta por defecto si es distinta
            } else {
                $('#modalBillingSameNote').show();          // “Se usaron los mismos datos del paciente”
                $('#collapseBillingData').collapse('hide'); // cerrada por defecto si es igual
            }

            // ===== SECCIÓN 5: Información de pago (DINÁMICA) =====
            const paymentMethodRaw = $(this).data('payment-method');          // "card" | "transfer"
            // ✅ Guardar método real (según BD) para que el submit detecte bien
            $('#modalPaymentMethodRaw').val(paymentMethodRaw ? String(paymentMethodRaw).trim().toLowerCase() : '');
            console.log('--- OPEN MODAL ---');
            console.log('[appointment_id]', $(this).data('id'));
            console.log('[payment-method data-*]', paymentMethodRaw);
            console.log('[pmRaw hidden now]', $('#modalPaymentMethodRaw').val());
            console.log('------------------');
            const clientTxIdRaw = $(this).data('client-transaction-id');      // largo
            const paymentStatusRaw = $(this).data('payment-status');          // lo que tengas guardado
            const amountRaw = $(this).data('amount');                         // ya existe
            // Fecha del pago: reusamos createdAtFinal (porque pago=cita)
            const paymentDateFinal = createdAtFinal || 'N/A';

            // Reset visual
            $('#paymentSectionWrapper').hide();
            $('#paymentCardBlock').hide();
            $('#paymentTransferBlock').hide();

            // Helpers para labels/badges
            function paymentMethodLabel(method) {
                const m = String(method || '').trim().toLowerCase();
                if (m === 'card') return 'Tarjeta';
                if (m === 'transfer') return 'Transferencia';
                return m ? (m.charAt(0).toUpperCase() + m.slice(1)) : 'N/A';
            }

            function paymentStatusBadge(status) {
                const s = String(status || '').trim().toLowerCase();

                // Ajusta aquí a tus estados reales si los tienes definidos
                const colors = {
                    paid: '#2ecc71',
                    pending: '#f39c12',
                    processing: '#3498db',
                    rejected: '#e74c3c',
                    failed: '#e74c3c',
                    cancelled: '#95a5a6',
                    na: '#95a5a6',
                };

                const labels = {
                    paid: 'Pagado',
                    pending: 'Pendiente',
                    processing: 'Procesando',
                    rejected: 'Rechazado',
                    failed: 'Fallido',
                    cancelled: 'Cancelado',
                    na: 'N/A',
                };

                const key = s || 'na';
                const color = colors[key] || '#95a5a6';
                const label = labels[key] || (status ? String(status) : 'N/A');

                return `<span class="badge px-2 py-1" style="background-color:${color};color:white;">${label}</span>`;
            }

            const pm = String(paymentMethodRaw || '').trim().toLowerCase();

            if (pm === 'card') {
                $('#paymentSectionWrapper').show();
                $('#paymentCardBlock').show();

                $('#modalPaymentMethodLabel').text(paymentMethodLabel(pm));
                $('#modalClientTransactionId').text(clientTxIdRaw ? String(clientTxIdRaw) : 'N/A');

                const amountText =
                    amountRaw !== null && amountRaw !== undefined && amountRaw !== ''
                        ? `$${parseFloat(amountRaw).toFixed(2)}`
                        : 'N/A';

                $('#modalPaymentAmount').text(amountText);
                $('#modalPaymentDate').text(paymentDateFinal);
                $('#modalPaymentStatusBadge2').html(paymentStatusBadge(paymentStatusRaw));

            } else if (pm === 'transfer') {
                $('#paymentSectionWrapper').show();
                $('#paymentTransferBlock').show();

                // ===== SUBSECCIÓN: Validación de transferencia (solo transfer) =====

                // Leer data-* desde el botón
                const validationStatus = $(this).attr('data-transfer-validation-status');
                const validatedAtRaw   = $(this).attr('data-transfer-validated-at');
                const validatedByName  = $(this).attr('data-transfer-validated-by');
                const validationNotes  = $(this).attr('data-transfer-validation-notes');

                const vStatus = String(validationStatus || '').trim().toLowerCase();

                // Reset UI
                $('#modalTransferValidationSelect').val('');
                $('#modalTransferValidationNotes').val('');
                $('#transferValidationNotesWrapper').hide();
                $('#transferNotesRequired').hide();
                $('#transferNotesOptional').hide();
                $('#transferValidationMeta').hide();

                // Si YA EXISTE una validación previa
                if (vStatus) {

                    // 1️⃣ Select
                    $('#modalTransferValidationSelect').val(vStatus);

                    // 2️⃣ Notes
                    if (validationNotes) {
                        $('#modalTransferValidationNotes').val(validationNotes);
                        $('#transferValidationNotesWrapper').show();
                    }

                    // 3️⃣ Meta (fecha + usuario)
                    if (validatedAtRaw || validatedByName) {
                        $('#transferValidationMeta').show();

                        let formattedValidatedAt = 'N/A';
                        if (validatedAtRaw) {
                            const d = new Date(validatedAtRaw);
                            if (!isNaN(d.getTime())) {
                                const datePart = d.toLocaleDateString('es-EC', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                });
                                const timePart = d.toLocaleTimeString('en-US', {
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                });
                                formattedValidatedAt = `${datePart} · ${timePart}`;
                            }
                        }

                        $('#modalTransferValidatedAt').text(formattedValidatedAt);
                        $('#modalTransferValidatedBy').text(validatedByName || 'Sistema');
                    }
                }

                $('#modalTransferMethodLabel').text(paymentMethodLabel(pm));

                const amountText =
                    amountRaw !== null && amountRaw !== undefined && amountRaw !== ''
                        ? `$${parseFloat(amountRaw).toFixed(2)}`
                        : 'N/A';

                $('#modalTransferAmount').text(amountText);

                // ===== Datos de la transferencia (appointments.*) =====
                const tBankOrigin = $(this).data('transfer-bank-origin');
                const tPayerName = $(this).data('transfer-payer-name');
                const tDateRaw = $(this).data('transfer-date');
                const tReference = $(this).data('transfer-reference');
                const tReceiptPath = $(this).data('transfer-receipt-path');

                // Banco / Titular / Referencia
                $('#modalTransferBankOrigin').text(tBankOrigin ? String(tBankOrigin) : 'N/A');
                $('#modalTransferPayerName').text(tPayerName ? String(tPayerName) : 'N/A');
                $('#modalTransferReference').text(tReference ? String(tReference) : 'N/A');

                // Fecha (solo fecha, sin hora)
                let transferDateFinal = 'N/A';
                if (tDateRaw && String(tDateRaw).trim() !== '') {
                    const s = String(tDateRaw).trim();

                    // Toma solo la parte de fecha (por si viene "YYYY-MM-DD HH:MM:SS")
                    const datePart = s.split(' ')[0]; // "2025-12-12"

                    // Si datePart es "YYYY-MM-DD", formateamos bonito
                    if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                        const [yy, mm, dd] = datePart.split('-').map(Number);
                        const dObj = new Date(yy, (mm || 1) - 1, dd || 1); // sin UTC shift
                        transferDateFinal = new Intl.DateTimeFormat('es-EC', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }).format(dObj).toLowerCase();
                    } else {
                        // Si viene rarito, igual mostramos solo lo que haya como "datePart"
                        transferDateFinal = datePart || s;
                    }
                }
                $('#modalTransferDate').text(transferDateFinal);

                // Comprobante (link/botón)
                if (tReceiptPath && String(tReceiptPath).trim() !== '') {
                    const appointmentId = $(this).data('id'); 
                    const protectedUrl = `/admin/appointments/${appointmentId}/transfer-receipt`;
                    const openViewUrl = `/admin/appointments/${appointmentId}/transfer-receipt/view`;

                    // ✅ Detectar tipo por el path REAL guardado (no por la URL protegida)
                    const path = String(tReceiptPath).trim().toLowerCase();
                    const fileType = path.endsWith('.pdf') ? 'pdf' : 'image';

                    const bookingCode = String($(this).data('booking-code') || $(this).data('booking_id') || `FS-${appointmentId}`).trim();

                    $('#modalTransferReceipt').html(
                    `<button type="button"
                        class="btn btn-outline-primary btn-sm js-open-receipt-modal"
                        data-url="${protectedUrl}"
                        data-open-url="${openViewUrl}"
                        data-filetype="${fileType}"
                        data-booking-code="${bookingCode}">
                        Ver comprobante
                    </button>`
                    );
                    } else {
                    $('#modalTransferReceipt').html(
                        `<span class="text-muted font-italic small">N/A</span>`
                    );
                }

            } else {
                // Si no hay método, ocultamos Sección 5 (no mostramos basura)
                $('#paymentSectionWrapper').hide();
            }

            const amount = $(this).data('amount');

            $('#modalAmount').text(
                amount !== null && amount !== undefined && amount !== ''
                    ? `$${parseFloat(amount).toFixed(2)}`
                    : 'N/A'
            );
            const notes = $(this).data('notes');

            if (notes && notes.trim() !== '') {
                $('#modalNotes')
                    .text(notes)
                    .removeClass('text-muted font-italic small');
            } else {
                $('#modalNotes')
                    .text('No se registraron notas')
                    .addClass('text-muted font-italic small');
            }

            // ✅ Guardar status real en hidden (sin dropdown)
            const statusRaw = $(this).data('status');
            $('#modalStatusHidden').val(statusRaw ? String(statusRaw).trim() : 'Pending payment');

            // Set status badge (EN -> ES, sin guiones bajos)
            let rawStatus = $(this).data('status');

            // Normaliza: "Paid" -> "paid", "Pending payment" -> "pending_payment", "pending_verification" se queda igual
            let normalizedStatus = String(rawStatus || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '_');

            // Colores por status normalizado
            const statusColors = {
                pending_payment: '#f39c12',
                processing: '#3498db',
                paid: '#2ecc71',
                cancelled: '#ff0000',
                completed: '#008000',
                on_hold: '#95a5a6',
                rescheduled: '#f1c40f',
                no_show: '#e67e22',
                pending_verification: '#7f8c8d',
            };

            // Etiquetas en español por status normalizado
            const statusLabels = {
                pending_payment: 'Pendiente de pago',
                processing: 'Procesando',
                paid: 'Pagada',
                cancelled: 'Cancelada',
                completed: 'Completada',
                on_hold: 'En espera',
                rescheduled: 'Reprogramada',
                no_show: 'No asistió',
                pending_verification: 'Pendiente de verificación',
            };

            const badgeColor = statusColors[normalizedStatus] || '#7f8c8d';
            const badgeLabel = statusLabels[normalizedStatus] || 'Estado desconocido';

            const badgeHtml = `<span class="badge px-2 py-1" style="background-color: ${badgeColor}; color: white;">${badgeLabel}</span>`;

            $('#modalStatusBadge').html(badgeHtml);
            $('#modalStatusBadgeLegacy').html(badgeHtml);

            // Por ahora, estado del pago queda N/A hasta que lo conectemos a tus campos reales
            $('#modalPaymentStatusBadge').html(paymentStatusBadge(paymentStatusRaw));

            // ✅ Snapshot final (BD) para habilitar Guardar cambios solo si hay cambios reales
            __setSnapshotFromCurrent();
        });
    </script>

    <script>
        // ✅ Cambios en validación de transferencia (solo aplica si el bloque existe)
        $(document).on('change', '#modalTransferValidationSelect', function () {
            const v = String($(this).val() || '').trim().toLowerCase();

            const $notes = $('#modalTransferValidationNotes');

            // Placeholders dinámicos
            const phValidated = 'Ej: OK, confirmado en el banco.';
            const phRejected  = 'Ej: El comprobante no coincide con el monto / Falta referencia.';

            if (v === 'validated' || v === 'rejected') {
                $('#transferValidationNotesWrapper').show();

                if (v === 'rejected') {
                    // Rechazada => obligatorio + placeholder "rechazo"
                    $('#transferNotesRequired').show();
                    $('#transferNotesOptional').hide();
                    $notes.attr('placeholder', phRejected);
                } else {
                    // Validada => opcional + placeholder "ok banco"
                    $('#transferNotesRequired').hide();
                    $('#transferNotesOptional').show();
                    $notes.attr('placeholder', phValidated);
                }

            } else {
                // Sin revisar => ocultar todo y limpiar
                $('#transferValidationNotesWrapper').hide();
                $('#transferNotesRequired').hide();
                $('#transferNotesOptional').hide();
                $('#transferNotesOptional').hide();
                $('#modalTransferValidationNotes').attr('placeholder', 'Ej: Escribe una observación...');
                $notes.val('');
                $notes.attr('placeholder', 'Ej: Escribe una observación...');
            }
        });

        // ✅ Antes de enviar el form: valida reglas y llena hidden inputs
        $(document).on('submit', '#appointmentStatusForm', function (e) {
            console.log('================= SUBMIT appointmentStatusForm =================');
            console.log('[FORM action]', $('#appointmentStatusForm').attr('action'));
            console.log('[FORM method]', $('#appointmentStatusForm').attr('method'));
            console.log('[appointment_id]', $('#modalAppointmentId').val());
            console.log('[status hidden]', $('#modalStatusHidden').val());
            console.log('[pmRaw hidden]', $('#modalPaymentMethodRaw').val());
            console.log('[select validation]', $('#modalTransferValidationSelect').val());
            console.log('[notes textarea]', $('#modalTransferValidationNotes').val());
           
            console.log('===============================================================');
            // ✅ Usa el valor real de BD: "transfer" | "card"
            const pmRaw = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
            const isTransfer = (pmRaw === 'transfer');

            if (!isTransfer) {
                // Si no es transferencia, NO mandamos nada extra
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
                return true;
            }

            const v = String($('#modalTransferValidationSelect').val() || '').trim().toLowerCase();
            const notes = String($('#modalTransferValidationNotes').val() || '').trim();

             // ✅ Llenar hidden inputs para backend
            $('#modalTransferValidationStatusInput').val(v);   // "" | validated | rejected
            $('#modalTransferValidationNotesInput').val(notes);

            if (v === 'validated') {
                $('#modalStatusHidden').val('paid');
            } else if (v === 'rejected') {
                $('#modalStatusHidden').val('on_hold');
            } else {
                // ✅ Sin revisar => volver a pendiente de verificación
                $('#modalStatusHidden').val('pending_verification');

                // ✅ Asegurar que se envíe vacío al backend (para que él lo convierta a NULL)
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
            }

            // Rechazada requiere notas
            if (v === 'rejected' && notes === '') {
                e.preventDefault();
                alert('Para marcar como "Rechazada", debes escribir una observación.');
                return false;
            }
            console.log('[hidden transfer_validation_status]', $('#modalTransferValidationStatusInput').val());
            console.log('[hidden transfer_validation_notes]', $('#modalTransferValidationNotesInput').val());
           
            return true;
        });
    </script>

    <script>
        // ✅ Abrir modal del comprobante (delegado)
        $(document).on('click', '.js-open-receipt-modal', function () {
            const url = $(this).data('url');
            const fileType = String($(this).data('filetype') || '').toLowerCase();
            const isPdf = (fileType === 'pdf');

            // ✅ Reset UI SIEMPRE
            $('#receiptError').hide();
            $('#receiptLoading').show();

            // ✅ Reset visor
            $('#receiptPdf').hide().attr('src', 'about:blank');

            // ✅ Matar handlers viejos y resetear IMG
            const $img = $('#receiptImg');
            $img.off('load error');                 // <--- clave
            $img.hide().attr('src', '');

            // Botones
            const openUrl = $(this).data('open-url') || url;
            $('#receiptOpenNewTab').attr('href', openUrl);
            $('#receiptDownloadBtn').data('url', url);
            const bookingCode = String($(this).data('bookingcode') || $(this).data('booking-code') || '').trim();
            $('#receiptDownloadBtn').data('booking-code', bookingCode);

            if (isPdf) {
                // ✅ PDF: nunca mostrar error (solo ocultarlo)
                $('#receiptError').hide();
                $('#receiptLoading').hide();
                $('#receiptPdf').attr('src', url).show();
            } else {
                // ✅ IMG: attach handlers nuevos
                $img.on('load', function () {
                $('#receiptError').hide();
                $('#receiptLoading').hide();
                $img.show();
                });

                $img.on('error', function () {
                $('#receiptLoading').hide();
                $('#receiptError').show();
                });

                $img.attr('src', url);
            }

            $('#transferReceiptModal').modal('show');
        });

        $('#transferReceiptModal').on('hidden.bs.modal', function () {
            // ✅ limpiar estado visual para la próxima apertura
            $('#receiptError').hide();
            $('#receiptLoading').hide();
            $('#receiptImg').hide().attr('src', '');
            $('#receiptPdf').hide().attr('src', 'about:blank');

            if ($('#appointmentModal').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });
    </script>

    <script>
        // ✅ Descargar comprobante desde el modal (usa la misma URL protegida)
        $(document).on('click', '#receiptDownloadBtn', async function () {
            const url = $(this).data('url');
            if (!url) return;

            const $btn = $(this);
            const oldText = $btn.text();

            try {
                $btn.prop('disabled', true).text('Descargando...');

                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const blob = await res.blob();
                const blobUrl = window.URL.createObjectURL(blob);

                // extensión según MIME
                const ext = (blob.type && blob.type.includes('pdf')) ? 'pdf'
                    : (blob.type && blob.type.includes('png')) ? 'png'
                    : (blob.type && blob.type.includes('jpeg')) ? 'jpg'
                    : 'bin';

                // nombre del archivo (simple y útil)
                const bookingCode = String($(this).data('booking-code') || '').trim();
                const safeCode = bookingCode ? bookingCode.replace(/[^\w\-]+/g, '-') : 'SIN_CODIGO';

                const fileName = `comprobante_transferencia_${safeCode}.${ext}`;

                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();

                window.URL.revokeObjectURL(blobUrl);

            } catch (err) {
                console.error('Download error:', err);
                alert('No se pudo descargar el comprobante. Prueba con "Pantalla completa" y descarga desde la pestaña.');
            } finally {
                $btn.prop('disabled', false).text(oldText);
            }
        });
    </script>

    <script>
        // ============================
        // ✅ Guardar cambios: solo se habilita si hay cambios reales
        // ============================
        window.__apptModalSnapshot = null;

        function __norm(v) {
            return String(v ?? '').trim(); // comparación exacta pero sin espacios
        }

        function __getCurrentEditableState() {
            const pmRaw = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();

            // Solo hay "cambios guardables" si es transferencia
            if (pmRaw !== 'transfer') {
                return { pmRaw, transfer_validation_status: '', transfer_validation_notes: '' };
            }

            const v = __norm($('#modalTransferValidationSelect').val()).toLowerCase(); // '' | validated | rejected
            const notes = __norm($('#modalTransferValidationNotes').val());            // texto

            return {
                pmRaw,
                transfer_validation_status: v,
                transfer_validation_notes: notes
            };
        }

        function __hasRealChanges() {
            if (!window.__apptModalSnapshot) return false;

            const current = __getCurrentEditableState();
            const snap = window.__apptModalSnapshot;

            // Si no es transferencia, nunca habilitar (no hay nada editable que guardar)
            if (current.pmRaw !== 'transfer') return false;

            // Comparar valores editables reales
            if (__norm(current.transfer_validation_status) !== __norm(snap.transfer_validation_status)) return true;
            if (__norm(current.transfer_validation_notes)  !== __norm(snap.transfer_validation_notes))  return true;

            return false;
        }

        function __updateSaveButtonState() {
            const enable = __hasRealChanges();
            $('#btnSaveChanges').prop('disabled', !enable);
        }

        function __setSnapshotFromCurrent() {
            // Guardar estado inicial (lo que vino de BD y ya pintaste en el modal)
            window.__apptModalSnapshot = __getCurrentEditableState();
            __updateSaveButtonState(); // normalmente lo deja disabled
        }

        // ✅ Recalcular cuando el admin cambia select o escribe notas
        $(document).on('change', '#modalTransferValidationSelect', function () {
            __updateSaveButtonState();
        });

        $(document).on('input', '#modalTransferValidationNotes', function () {
            __updateSaveButtonState();
        });

        // ✅ Al cerrar modal, limpiar snapshot y deshabilitar el botón
        $('#appointmentModal').on('hidden.bs.modal', function () {
            window.__apptModalSnapshot = null;
            $('#btnSaveChanges').prop('disabled', true);
        });
    </script>

    <script>
        // ============================
        // ✅ UI: Modo lectura / Modo edición (solo UI por ahora)
        // ============================
        window.__apptIsEditMode = false;

        function __enterEditModeUI() {
            window.__apptIsEditMode = true;

            $('#editModeBanner').show();
            $('#btnCancelEditMode').show();
            $('#apptModeBadge').show();

            // Por ahora NO convertimos campos a inputs (eso viene en el siguiente paso),
            // solo marcamos el estado visual.
        }

        function __exitEditModeUI() {
            window.__apptIsEditMode = false;

            $('#editModeBanner').hide();
            $('#btnCancelEditMode').hide();
            $('#apptModeBadge').hide();

            // Por ahora NO revertimos nada más (porque no hay inputs aún).
        }

        // Click: Acciones -> Editar datos
        $(document).on('click', '#btnEnterEditMode', function () {
            __enterEditModeUI();
            // Cerrar dropdown
            $('#apptActionsDropdown').dropdown('hide');
        });

        // Click: Cancelar edición
        $(document).on('click', '#btnCancelEditMode', function () {
            __exitEditModeUI();
        });

        // Al abrir modal: siempre volver a solo lectura
        $(document).on('click', '.view-appointment-btn', function() {
            __exitEditModeUI();
        });

        // Al cerrar modal: resetear
        $('#appointmentModal').on('hidden.bs.modal', function () {
            __exitEditModeUI();
        });

        // (Opcional) Por ahora: estos botones solo muestran alerta placeholder
        $(document).on('click', '#btnReagendar,#btnConfirmarCita,#btnNoAsistio,#btnCancelarCita,#btnVerHistorial', function(){
            alert('Acción pendiente de implementar (solo UI en este paso).');
            $('#apptActionsDropdown').dropdown('hide');
        });
    </script>
@endsection
