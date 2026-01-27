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
    <form id="appointmentStatusForm" method="POST" action="{{ route('appointments.update.status') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="transfer_validation_status_original" id="modalTransferValidationStatusOriginal" value="">
        <input type="hidden" name="appointment_id" id="modalAppointmentId">
        <input type="hidden" name="status" id="modalStatusHidden" value="">
        <input type="hidden" name="transfer_validation_status" id="modalTransferValidationStatusInput" value="">
        <input type="hidden" name="transfer_validation_touched" id="modalTransferValidationTouchedInput" value="0">
        <input type="hidden" name="payment_status" id="modalPaymentStatusHidden" value="">
        <input type="hidden" name="reschedule_date" id="rescheduleDateHidden" value="">
        <input type="hidden" name="reschedule_time" id="rescheduleTimeHidden" value="">
        <input type="hidden" name="reschedule_end_time" id="rescheduleEndTimeHidden" value="">
        <input type="hidden" name="reschedule_reason" id="rescheduleReasonHidden" value="">
        <input type="hidden" name="reschedule_reason_other" id="rescheduleReasonOtherHidden" value="">
        <input type="hidden" name="client_transaction_id" id="modalClientTransactionIdHidden" value="">
        <input type="hidden" name="payment_paid_at" id="modalPaymentPaidAtHidden" value="">
        <input type="hidden" name="payment_notes" id="modalPaymentNotesHidden" value="">
        <input type="hidden" name="amount_paid" id="modalAmountPaidHidden" value="">
        <input type="hidden" name="transfer_validation_notes" id="modalTransferValidationNotesInput" value="">
        <input type="hidden" id="modalPaymentMethodRaw" value="">
        <input type="hidden" name="payment_method" id="modalPaymentMethodHidden" value="">
        <input type="hidden" name="change_reason" id="modalChangeReasonHidden" value="">
        <input type="hidden" name="change_reason_other" id="modalChangeReasonOtherHidden" value="">

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
                                        <i class="fas fa-pen mr-2"></i>Editar
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

                                    <button type="button" class="dropdown-item d-none" id="btnSendReminder3h">
                                        <i class="fas fa-bell mr-2"></i>Enviar recordatorio (3h)
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
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="mb-0 pr-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Modo edición activado</strong>
                                    <div class="small mt-1 text-muted">
                                        Los cambios se guardarán solo al presionar “Guardar cambios”.
                                    </div>
                                </div>
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

                                    {{-- Texto (badge) --}}
                                    <div class="text-dark js-edit-text" id="modalStatusBadge">N/A</div>

                                    {{-- Select (modo edición) --}}
                                    <select class="form-control form-control-sm js-edit-input" id="modalStatusSelect">
                                        <option value="pending_verification">Pendiente de verificación</option>
                                        <option value="pending_payment">Pendiente de pago</option>
                                        <option value="paid">Pagada</option>
                                        <option value="confirmed">Confirmada</option>
                                        <option value="completed">Completada</option>
                                        <option value="no_show">No asistió</option>
                                        <option value="on_hold">En espera</option>
                                        <option value="rescheduled" disabled hidden>Reagendada</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-0">
                                    <div class="small text-muted">Estado del pago</div>

                                    {{-- Texto (badge) --}}
                                    <div class="text-dark js-edit-text" id="modalPaymentStatusBadge">
                                        <span class="badge px-2 py-1" style="background-color:#95a5a6;color:white;">N/A</span>
                                    </div>

                                    {{-- Select (modo edición) --}}
                                    <select class="form-control form-control-sm js-edit-input" id="modalPaymentStatusSelect">
                                        <option value="" disabled selected>Seleccione una opción</option>
                                        <option value="pending">Pendiente</option>
                                        <option value="unpaid">No pagado</option>
                                        <option value="partial">Pagado parcialmente</option> <!-- ✅ NUEVO -->
                                        <option value="paid">Pagado</option>
                                        <option value="refunded">Reembolsado</option>
                                    </select>
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
                                        <div class="text-dark js-edit-text" id="modalPatientFullName">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalPatientFullNameInput" name="patient_full_name" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Tipo de documento</div>
                                        <div class="text-dark js-edit-text" id="modalDocType">N/A</div>
                                        <select class="form-control form-control-sm js-edit-input"
                                                id="modalDocTypeInput" name="patient_doc_type">
                                            <option value="cedula">Cédula</option>
                                            <option value="ruc">RUC</option>
                                            <option value="pasaporte">Pasaporte</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Número de documento</div>
                                        <div class="text-dark js-edit-text" id="modalDocNumber">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalDocNumberInput" name="patient_doc_number" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha de nacimiento</div>
                                        <div class="text-dark js-edit-text" id="modalPatientDobText">N/A</div>
                                        <input type="date" class="form-control form-control-sm js-edit-input" id="modalPatientDobInput" name="patient_dob" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Edad</div>
                                        <div class="text-dark" id="modalPatientAge">N/A</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Correo</div>
                                        <div class="text-dark js-edit-text" id="modalEmail">N/A</div>
                                        <input type="email" class="form-control form-control-sm js-edit-input"
                                            id="modalEmailInput" name="patient_email" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Teléfono</div>
                                        <div class="text-dark js-edit-text" id="modalPhone">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalPhoneInput" name="patient_phone" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Dirección</div>
                                        <div class="text-dark js-edit-text" id="modalAddress">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalAddressInput" name="patient_address" value="">
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted">Zona horaria del paciente</div>
                                        <div class="text-dark js-edit-text" id="modalPatientTimezone">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalPatientTimezoneInput" name="patient_timezone" value=""
                                            placeholder="Ej: America/Guayaquil">
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
                                    
                                    <div class="small text-muted js-edit-input">Notas del paciente (opcional)</div>
                                    <div class="small text-muted js-edit-text">Notas del paciente</div>
                                    <div class="text-dark js-edit-text" id="modalNotes">N/A</div>
                                    <textarea class="form-control form-control-sm js-edit-input"
                                            id="modalNotesInput" name="patient_notes" rows="2"
                                            placeholder="Notas del paciente..."></textarea>
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
                                        <div class="text-dark js-edit-text" id="modalBillingName">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalBillingNameInput" name="billing_name" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Tipo de documento</div>
                                        <div class="text-dark js-edit-text" id="modalBillingDocType">N/A</div>
                                        <select class="form-control form-control-sm js-edit-input"
                                                id="modalBillingDocTypeInput" name="billing_doc_type">
                                            <option value="cedula">Cédula</option>
                                            <option value="ruc">RUC</option>
                                            <option value="pasaporte">Pasaporte</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Número de documento</div>
                                        <div class="text-dark js-edit-text" id="modalBillingDocNumber">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalBillingDocNumberInput" name="billing_doc_number" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Correo de facturación</div>
                                        <div class="text-dark js-edit-text" id="modalBillingEmail">N/A</div>
                                        <input type="email" class="form-control form-control-sm js-edit-input"
                                            id="modalBillingEmailInput" name="billing_email" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Teléfono de facturación</div>
                                        <div class="text-dark js-edit-text" id="modalBillingPhone">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalBillingPhoneInput" name="billing_phone" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Dirección de facturación</div>
                                        <div class="text-dark js-edit-text" id="modalBillingAddress">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalBillingAddressInput" name="billing_address" value="">
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

                                        <div class="text-dark js-edit-text" id="modalPaymentMethodLabel">N/A</div>

                                        <select class="form-control form-control-sm js-edit-input"
                                                id="modalPaymentMethodSelectCard"
                                                name="payment_method">
                                            <option value="transfer">Transferencia</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="cash">Efectivo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Estado del pago</div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalPaymentStatusBadge2">N/A</div>

                                        {{-- Edición --}}
                                        <select class="form-control form-control-sm js-edit-input" id="modalPaymentStatusSelectCard">
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="pending">Pendiente</option>
                                            <option value="unpaid">No pagado</option>
                                            <option value="partial">Pagado parcialmente</option> <!-- ✅ NUEVO -->
                                            <option value="paid">Pagado</option>
                                            <option value="refunded">Reembolsado</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto total a pagar</div>
                                        <div class="text-dark js-edit-text" id="modalPaymentAmount">N/A</div>
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                        class="form-control form-control-sm js-edit-input"
                                        id="modalAmountInput" name="amount" value="" placeholder="0.00">
                                    </div>

                                    {{-- FILA 2 (solo derecha) --}}

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto pagado</div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalPaidAmountText">N/A</div>

                                        {{-- Edición --}}
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalPaidAmountInputCard"
                                            name="amount_paid"
                                            value="" placeholder="0.00">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha del pago</div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalPaymentDate">N/A</div>

                                        {{-- Edición (fecha + hora) --}}
                                        <input type="datetime-local"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalPaymentPaidAtInput"
                                            value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Client Transaction ID <span class="optional-tag text-muted">(opcional)</span></div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalClientTransactionId" style="word-break: break-word; overflow-wrap:anywhere;">N/A</div>

                                        {{-- Edición --}}
                                        <input type="text"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalClientTransactionIdInput"
                                            value=""
                                            placeholder="Ej: 33b7a262-1814-45f2-8076-76d1236f1769">
                                    </div>
                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted js-edit-input">Observaciones de pago (opcional)</div>
                                        <div class="small text-muted js-edit-text">Observaciones de pago</div>

                                        <div class="text-dark js-edit-text" id="modalCardNotesText">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>

                                        <textarea class="form-control form-control-sm js-edit-input"
                                                id="modalCardNotesInput"
                                                name="payment_notes"
                                                rows="2"
                                                placeholder="Ej: POS físico, voucher entregado..."></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOQUE TRANSFERENCIA (stand-by) --}}
                            <div id="paymentTransferBlock" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Método</div>

                                        <div class="text-dark js-edit-text" id="modalTransferMethodLabel">N/A</div>

                                        <select class="form-control form-control-sm js-edit-input"
                                                id="modalPaymentMethodSelectTransfer"
                                                name="payment_method">
                                            <option value="transfer">Transferencia</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="cash">Efectivo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto total a pagar</div>

                                        <div class="text-dark js-edit-text" id="modalTransferAmount">N/A</div>

                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalAmountInputTransfer"
                                            name="amount"
                                            value="" placeholder="0.00">
                                    </div>

                                    {{-- ✅ NUEVO: Monto pagado (debajo, solo columna derecha) --}}
                                    <div class="col-md-6 mb-2 offset-md-6">
                                        <div class="small text-muted">Monto pagado</div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalTransferPaidAmountText">N/A</div>

                                        {{-- Edición --}}
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalPaidAmountInputTransfer"
                                            name="amount_paid"
                                            value="" placeholder="0.00">
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted js-edit-input">Observaciones de pago (opcional)</div>
                                        <div class="small text-muted js-edit-text">Observaciones de pago</div>

                                        <div class="text-dark js-edit-text" id="modalTransferNotesText">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>

                                        <textarea class="form-control form-control-sm js-edit-input"
                                                id="modalTransferNotesInput"
                                                name="payment_notes"
                                                rows="2"
                                                placeholder="Ej: Comprobante enviado por WhatsApp..."></textarea>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <div class="small text-muted font-weight-bold">Datos de la transferencia</div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Banco de origen</div>
                                        <div class="text-dark js-edit-text" id="modalTransferBankOrigin">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalTransferBankOriginInput" name="transfer_bank_origin" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Nombre del titular</div>
                                        <div class="text-dark js-edit-text" id="modalTransferPayerName">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalTransferPayerNameInput" name="transfer_payer_name" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha de la transferencia</div>
                                        <div class="text-dark js-edit-text" id="modalTransferDate">N/A</div>
                                        <input type="date" class="form-control form-control-sm js-edit-input"
                                            id="modalTransferDateInput" name="transfer_date" value="">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted js-edit-input">Número de referencia (opcional)</div>
                                        <div class="small text-muted js-edit-text">Número de referencia</div>
                                        <div class="text-dark js-edit-text" id="modalTransferReference">N/A</div>
                                        <input type="text" class="form-control form-control-sm js-edit-input"
                                            id="modalTransferReferenceInput" name="transfer_reference" value="">
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted js-edit-input">Comprobante (opcional)</div>
                                        <div class="small text-muted js-edit-text">Comprobante</div>

                                        <div class="text-dark js-edit-text" id="modalTransferReceipt">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>

                                        <div class="js-edit-input">
                                            <input type="file"
                                                class="form-control form-control-sm"
                                                id="modalTransferReceiptFile"
                                                name="tr_file"
                                                accept="image/*,application/pdf">
                                            <small class="text-muted d-block mt-1">
                                                Si adjunta un nuevo comprobante, se reemplazará el comprobante actual.
                                            </small>
                                        </div>
                                    </div>

                                    {{-- =========================
                                        SUBSECCIÓN: Validación de transferencia (solo admin / solo transfer)
                                    ========================== --}}
                                    <div class="col-md-12 mt-3" id="transferValidationSection">
                                        <div class="small text-muted font-weight-bold">Validación de transferencia</div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <div class="small text-muted">Estado de validación</div>

                                        {{-- ✅ Lectura: solo texto --}}
                                        <div class="text-dark js-edit-text" id="modalTransferValidationText">Sin revisar</div>

                                        {{-- ✅ Edición: dropdown --}}
                                        <select class="form-control form-control-sm w-100 js-edit-input" id="modalTransferValidationSelect">
                                            <option value="">Sin revisar</option>
                                            <option value="validated">Validada</option>
                                            <option value="rejected">Rechazada</option>
                                        </select>
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
                                            Observaciones de validación
                                            <span id="transferNotesOptional" class="text-muted js-edit-input" style="display:none;">(opcional)</span>
                                            <span id="transferNotesRequired" class="text-danger js-edit-input" style="display:none;">(obligatorias)</span>
                                        </div>

                                        <!-- ✅ Lectura: texto NO editable -->
                                        <div class="text-dark js-edit-text" id="modalTransferValidationNotesText">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>

                                        <!-- ✅ Edición: textarea -->
                                        <textarea class="form-control form-control-sm js-edit-input"
                                                id="modalTransferValidationNotes"
                                                rows="2"
                                                placeholder="Ej: Escribe una observación..."
                                                disabled></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOQUE EFECTIVO --}}
                            <div id="paymentCashBlock" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Método</div>

                                        <div class="text-dark js-edit-text" id="modalCashMethodLabel">Efectivo</div>

                                        <select class="form-control form-control-sm js-edit-input"
                                                id="modalPaymentMethodSelectCash"
                                                name="payment_method">
                                            <option value="transfer">Transferencia</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="cash">Efectivo</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Monto total a pagar</div>

                                        <div class="text-dark js-edit-text" id="modalCashAmount">N/A</div>

                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalAmountInputCash"
                                            name="amount"
                                            value="" placeholder="0.00">
                                    </div>

                                    {{-- ✅ NUEVO: Monto pagado (debajo, solo columna derecha) --}}
                                    <div class="col-md-6 mb-2 offset-md-6">
                                        <div class="small text-muted">Monto pagado</div>

                                        {{-- Lectura --}}
                                        <div class="text-dark js-edit-text" id="modalCashPaidAmountText">N/A</div>

                                        {{-- Edición --}}
                                        <input type="text" inputmode="decimal" autocomplete="off"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalPaidAmountInputCash"
                                            name="amount_paid"
                                            value="" placeholder="0.00">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="small text-muted">Fecha del pago</div>

                                        <div class="text-dark js-edit-text" id="modalCashPaidAtText">N/A</div>

                                        <input type="datetime-local"
                                            class="form-control form-control-sm js-edit-input"
                                            id="modalCashPaidAtInput"
                                            value="">
                                    </div>

                                    <div class="col-md-12 mb-0">
                                        <div class="small text-muted js-edit-input">Observaciones de pago (opcional)</div>
                                        <div class="small text-muted js-edit-text">Observaciones de pago</div>

                                        <div class="text-dark js-edit-text" id="modalCashNotesText">
                                            <span class="text-muted font-italic small">N/A</span>
                                        </div>

                                        <textarea class="form-control form-control-sm js-edit-input"
                                                id="modalCashNotesInput"
                                                rows="2"
                                                placeholder="Ej: Pago recibido en recepción / Regularización de transferencia rechazada..."></textarea>
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
                            class="btn btn-danger js-edit-input">Guardar cambios</button>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- ✅ Modal: Motivo del cambio -->
    <div class="modal fade" id="changeReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-change-reason">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Motivo del cambio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="changeReasonHelp" class="small text-muted mb-2"></div>

                    <div class="form-group mb-2">
                        <label class="small text-muted mb-1">
                            Selecciona un motivo
                            <span id="changeReasonRequiredTag" class="text-danger" style="display:none;">(obligatorio)</span>
                            <span id="changeReasonOptionalTag" class="text-muted" style="display:none;">(opcional)</span>
                        </label>

                        <select class="form-control form-control-sm" id="changeReasonSelect">
                            <option value="">Seleccione una opción</option>
                            <option value="typo">Error de tipeo</option>
                            <option value="patient_update">Información actualizada por el paciente</option>
                            <option value="admin_adjustment">Ajuste administrativo</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>

                    <div class="form-group mb-0" id="changeReasonOtherWrapper" style="display:none;">
                        <label class="small text-muted mb-1">Especifica (opcional)</label>
                        <textarea class="form-control form-control-sm" id="changeReasonOtherText"
                                rows="2" maxlength="180"
                                placeholder="Escribe un motivo breve..."></textarea>
                        <div class="small text-muted mt-1">Máx. 180 caracteres.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-primary" id="btnConfirmChangeReason">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Modal Wizard: Reagendar (2 pasos) -->
    <div class="modal fade" id="rescheduleWizardModal" tabindex="-1" role="dialog" aria-labelledby="rescheduleWizardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="rescheduleWizardModalLabel">Reagendar cita</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Paso 1 --}}
                    <div id="rescheduleStep1">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <div class="small text-muted">Profesional</div>
                                    <div id="rescheduleEmployeeText">N/A</div>
                                </div>

                                <div class="mb-2">
                                    <div class="small text-muted">Área de atención</div>
                                    <div id="rescheduleAreaText">N/A</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <div class="small text-muted">Servicio</div>
                                    <div id="rescheduleServiceText">N/A</div>
                                </div>

                                <div class="mb-2">
                                    <div class="small text-muted">Modalidad</div>
                                    <div id="rescheduleModeText">N/A</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted mb-1">Selecciona una fecha</label>
                                <input type="hidden" id="rescheduleDateInput" name="reschedule_date" value="">

                                <div class="card mb-4" id="reschedule-calendar-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="reschedule-prev-month">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>

                                        <h5 class="mb-0" id="reschedule-current-month">Enero 2026</h5>

                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="reschedule-next-month">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>

                                    <div class="card-body">
                                        <table class="table table-calendar mb-0">
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

                                            <tbody id="reschedule-calendar-body">
                                                <!-- Calendar will be generated dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="small text-muted mt-1" id="rescheduleOldText">Antes: N/A</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <!-- TÍTULO FUERA DE LA CARD (alineado con “Selecciona una fecha”) -->
                                <label class="small text-muted mb-1">Turnos disponibles</label>

                                <div class="card">
                                    <div class="card-body">
                                        <!-- INFO ZONA HORARIA (como paciente) -->
                                        <div class="d-flex align-items-center text-muted small mb-3" id="rescheduleTimezoneInfo">
                                            <i class="bi bi-clock me-2"></i>
                                            Todos los turnos están en hora local de Ecuador (GMT-5)
                                        </div>

                                        <!-- CONTENIDO (reusa tus IDs para no romper JS) -->
                                        <div id="rescheduleSlotsWrap">
                                            <div class="text-center text-muted w-100 py-4" id="rescheduleSlotsHint">
                                                Selecciona una fecha para visualizar los turnos disponibles
                                            </div>

                                            <!-- aquí tu JS debe inyectar los botones de turnos -->
                                            <div id="rescheduleSlots" class="slots-grid"></div>
                                        </div>

                                        <div class="small text-danger mt-2 d-none" id="rescheduleSlotsError"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="small text-muted mb-1">
                                Motivo de reagendamiento <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="rescheduleReasonSelect" required>
                                <option value="">Seleccione una opción</option>
                                <option value="patient_requested">Paciente pidió</option>
                                <option value="doctor_requested">Doctor pidió</option>
                                <option value="admin_requested">Admin</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>

                        <div class="mb-2 d-none" id="rescheduleReasonOtherWrap">
                            <label class="small text-muted mb-1">Especifica (opcional)</label>
                            <input type="text" class="form-control" id="rescheduleReasonOtherInput" maxlength="180" placeholder="Escribe el motivo...">
                        </div>
                    </div>

                    {{-- Paso 2 --}}
                    <div id="rescheduleStep2" class="d-none">
                        <div class="mb-3">
                            <div class="small text-muted">Resumen</div>
                            <div class="border rounded p-3">
                                <div class="mb-2"><strong>Antes:</strong> <span id="rescheduleConfirmBefore">N/A</span></div>
                                <div><strong>Nuevo:</strong> <span id="rescheduleConfirmAfter">N/A</span></div>
                            </div>
                        </div>

                        <div class="small text-muted">
                            Al confirmar, la cita se actualizará y se guardará auditoría del cambio.
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="rescheduleBackBtn">Atrás</button>
                    <button type="button" class="btn btn-primary" id="rescheduleNextBtn" disabled>Siguiente</button>
                    <button type="button" class="btn btn-primary d-none" id="rescheduleConfirmBtn">Confirmar reagendamiento</button>
                </div>

            </div>
        </div>
    </div>

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
        <div id="jsFlashContainer"></div>
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
                                <div class="table-responsive w-100" style="overflow-x:auto; -webkit-overflow-scrolling: touch;">
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
                                                <th style="width: 140px;" class="text-center">
                                                    Acción
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $statusColors = [
                                                    // ✅ NUEVOS (los que sí quieres)
                                                    'pending_verification' => '#7f8c8d',
                                                    'pending_payment' => '#f39c12',
                                                    'paid' => '#2ecc71',
                                                    'confirmed' => '#3498db',
                                                    'completed' => '#008000',
                                                    'canceled' => '#ff0000',
                                                    'rescheduled' => '#f1c40f',
                                                    'no_show' => '#e67e22',
                                                    'on_hold' => '#95a5a6',
                                                ];

                                                $statusLabels = [
                                                    // ✅ NUEVOS
                                                    'pending_verification' => 'Pendiente de verificación',
                                                    'pending_payment'=> 'Pendiente de pago',
                                                    'paid' => 'Pagada',
                                                    'confirmed' => 'Confirmada',
                                                    'completed' => 'Completada',
                                                    'canceled' => 'Cancelada',
                                                    'rescheduled' => 'Reagendada',
                                                    'no_show' => 'No asistió',
                                                    'on_hold' => 'En espera',
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
                                                    <td class="appt-date">
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d M Y') }}
                                                    </td>
                                                    <td class="appt-time">
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
                                                                'confirmed' => '#3498db',
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
                                                                'confirmed' => 'Confirmada',
                                                                'completed' => 'Completada',
                                                                'on_hold' => 'En espera',
                                                                'rescheduled' => 'Reagendada',
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
                                                    <td class="text-center" style="white-space: nowrap;">
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
                                                            data-patient-age="{{ !empty($appointment->patient_dob) ? \Carbon\Carbon::parse($appointment->patient_dob)->age : '' }}"
                                                            data-patient-dob="{{ $appointment->patient_dob ?? '' }}"
                                                            data-address="{{ $appointment->patient_address }}"
                                                            data-timezone="{{ $appointment->patient_timezone }}"
                                                            data-timezone-label="{{ $appointment->patient_timezone_label }}"
                                                            data-employee="{{ $appointment->employee->user->name }}"
                                                            data-employee-id="{{ $appointment->employee_id }}"
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
                                                            data-payment-paid-at="{{ $appointment->payment_paid_at ?? '' }}"
                                                            data-payment-notes="{{ $appointment->payment_notes ?? '' }}"
                                                            data-transfer-bank-origin="{{ $appointment->transfer_bank_origin ?? '' }}"
                                                            data-transfer-payer-name="{{ $appointment->transfer_payer_name ?? '' }}"
                                                            data-transfer-date="{{ $appointment->transfer_date ?? '' }}"
                                                            data-transfer-reference="{{ $appointment->transfer_reference ?? '' }}"
                                                            data-transfer-receipt-path="{{ $appointment->transfer_receipt_path ?? '' }}"
                                                            data-transfer-validation-status="{{ $appointment->transfer_validation_status ?? '' }}"
                                                            data-transfer-validated-at="{{ $appointment->transfer_validated_at ?? '' }}"
                                                            data-transfer-validated-by="{{ optional($appointment->transferValidatedBy)->name ?? '' }}"
                                                            data-transfer-validation-notes="{{ $appointment->transfer_validation_notes ?? '' }}"
                                                            data-paid-amount="{{ $appointment->amount_paid ?? '' }}"
                                                            data-created-at="{{ $appointment->created_at }}"
                                                            data-status="{{ $appointment->status }}">Ver detalles</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

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

    /* ====== Edit mode: texto vs inputs ====== */
    .js-edit-input { display:none !important; }
    .js-edit-text  { display:block; }

    /* ✅ Tag (opcional) solo en modo edición (pegado al texto como "Observaciones (opcional)") */
    .optional-tag{ display:none !important; margin-left:6px; float:none !important; }
    body.appt-edit-mode .optional-tag{ display:inline !important; float:none !important; }

    body.appt-edit-mode .js-edit-input { display:block !important; }
    body.appt-edit-mode .js-edit-text  { display:none; }

    /* ====== Quick Transfer mode: solo editar validación ====== */
    body.appt-quick-transfer-mode .js-edit-input { 
    display: none !important; 
    }
    body.appt-quick-transfer-mode .js-edit-text { 
    display: block !important; 
    }

    /* ✅ No duplicar "Sin revisar" arriba del dropdown */
    body.appt-edit-mode #modalTransferValidationText,
    body.appt-quick-transfer-mode #modalTransferValidationText{
        display: none !important;
    }

    /* ✅ No duplicar textos de lectura dentro de Validación cuando se edita */
    body.appt-edit-mode #appointmentModal #modalTransferValidationText,
    body.appt-quick-transfer-mode #appointmentModal #modalTransferValidationText{
        display:none !important;
    }

    body.appt-edit-mode #appointmentModal #modalTransferValidationNotesText,
    body.appt-quick-transfer-mode #appointmentModal #modalTransferValidationNotesText{
        display:none !important;
    }

    /* ✅ Excepciones: lo único editable/visible en quick transfer */
    body.appt-quick-transfer-mode #modalTransferValidationSelect,
    body.appt-quick-transfer-mode #transferValidationHelperText,
    body.appt-quick-transfer-mode #transferValidationSection .js-edit-input,
    /* ✅ AÑADE ESTAS 2: (opcional) y (obligatorias) */
    body.appt-quick-transfer-mode #transferValidationNotesWrapper small,
    body.appt-quick-transfer-mode #btnSaveChanges {
        display: block !important;
    }

    /* ✅ En quick transfer: por defecto, NO mostrar observaciones */
    body.appt-quick-transfer-mode #transferValidationNotesWrapper {
        display: none !important;
    }

    /* ✅ En quick transfer: SOLO mostrar observaciones cuando hay estado (validated/rejected) */
    body.appt-quick-transfer-mode.transfer-notes-visible #transferValidationNotesWrapper,
    body.appt-quick-transfer-mode.transfer-notes-visible #modalTransferValidationNotes {
        display: block !important;
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

    /* 🔧 Fix: Header "Observaciones..." en una sola línea */
    #transferValidationNotesWrapper > div.small.text-muted{
        width: 100% !important;
        display: flex !important;
        align-items: baseline;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    /* ✅ Control estable de (opcional)/(obligatorias) por clases en <body> */
    body.appt-edit-mode #transferNotesOptional,
    body.appt-edit-mode #transferNotesRequired{
        display: none !important;     /* por defecto ocultos en edición */
        margin-left: 6px;
    }

    body.appt-edit-mode.transfer-notes-opt #transferNotesOptional{
        display: inline !important;
    }

    body.appt-edit-mode.transfer-notes-req #transferNotesRequired{
        display: inline !important;
    }

    /* Modal de motivo del cambio: más angosto que el principal */
    .modal-change-reason {
        max-width: 560px; /* ajusta: 480–600 suele verse bien */
    }

    /* ✅ Fix stacking: backdrop visible cuando se abre el 2do modal (Bootstrap 4) */
    #changeReasonModal{
        z-index: 1060 !important; /* por encima de appointmentModal (1050) */
    }

    .modal-backdrop.change-reason-backdrop{
        z-index: 1055 !important; /* entre ambos modals */
        background-color: rgba(0, 0, 0, 0.80) !important;
    }

    /* (Opcional) desactivar clics en el modal de detalles mientras el motivo esté abierto */
    body.change-reason-open #appointmentModal {
        pointer-events: none;
    }

    /* Backdrop más oscuro para el motivo */
    .modal-backdrop.change-reason-backdrop {
        background-color: rgba(0, 0, 0, 0.80) !important;
    }

    /* ✅ Cuando se abre un modal hijo (wizard), oscurecer el modal de detalles SIN blur */
    #appointmentModal.appt-dimmed .modal-dialog {
        opacity: 0.35;
        pointer-events: none;
    }

    /* =========================
    Calendar (Reagendar) - Centrado header + tabla
    ========================= */

    /* Header: que las flechas queden simétricas y el mes centrado */
    #reschedule-calendar-card .card-header{
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px;
    }

    #reschedule-calendar-card #reschedule-current-month{
    flex: 1 !important;
    text-align: center !important;
    margin: 0 !important;
    }

    /* Botones: mismo ancho para que el título quede centrado de verdad */
    #reschedule-calendar-card #reschedule-prev-month,
    #reschedule-calendar-card #reschedule-next-month{
    width: 40px;
    flex: 0 0 40px;
    padding-left: 0 !important;
    padding-right: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    }

    /* Tabla: columnas uniformes y todo centrado */
    #reschedule-calendar-card .table-calendar{
    width: 100% !important;
    table-layout: fixed !important;
    }

    #reschedule-calendar-card .table-calendar th,
    #reschedule-calendar-card .table-calendar td{
    text-align: center !important;
    vertical-align: middle !important;
    }

    /* Opcional: que los headings no “corran” por padding distinto */
    #reschedule-calendar-card .table-calendar thead th{
    padding: .9rem .25rem !important;
    }

    #reschedule-calendar-card .table-calendar tbody td{
    padding: .9rem .25rem !important;
    }

    #reschedule-calendar-body .calendar-day { cursor: pointer; }

    #reschedule-calendar-body .calendar-day.disabled{
        cursor: not-allowed;
        opacity: .35;
        pointer-events: none;
    }

    #reschedule-calendar-body .calendar-day.selected {
        border-radius: 8px;
        font-weight: 700;
    }

    #reschedule-calendar-body td.disabled{
        cursor: not-allowed;
        opacity: .45;
    }

    /* Flecha atrás deshabilitada: sin hover, sin click, sin efecto */
    #reschedule-prev-month.disabled,
    #reschedule-prev-month:disabled {
        pointer-events: none !important;
        cursor: not-allowed !important;
        opacity: 0.45 !important;
    }

    /* Si por Bootstrap/estilos se pinta al hover, lo anulamos */
    #reschedule-prev-month.disabled:hover,
    #reschedule-prev-month:disabled:hover,
    #reschedule-prev-month.disabled:focus,
    #reschedule-prev-month:disabled:focus,
    #reschedule-prev-month.disabled:active,
    #reschedule-prev-month:disabled:active {
        background-color: inherit !important;
        border-color: inherit !important;
        box-shadow: none !important;
        color: inherit !important;
    }

    /* ✅ Evita que el card se “abra” por culpa de la tabla */
    .card .card-body{
    overflow-x: auto !important;
    }

    /* ✅ Asegura que el wrapper sí genere scroll */
    .card .table-responsive{
    overflow-x: auto !important;
    width: 100% !important;
    }

    /* ✅ A veces el ancho se rompe por estilos de AdminLTE/DataTables */
    #myTable{
    width: 100% !important;
    }

    div.dataTables_wrapper {
        width: 100%;
    }

        div.dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    /* ✅ Fix: header y body se comportan igual en responsive */
    table.dataTable,
    table.dataTable thead,
    table.dataTable tbody,
    table.dataTable th,
    table.dataTable td {
    box-sizing: border-box;
    }

    /* ✅ Permitir que los títulos NO se queden “estáticos” */
    table.dataTable thead th {
    white-space: normal !important;   /* permite salto de línea */
    word-break: break-word;
    }

    /* ✅ Que el cuerpo no “encoga” distinto al header */
    table.dataTable td {
    white-space: normal;              /* si quieres NO wrap, cambia a nowrap */
    word-break: break-word;
    }

    /* ✅ Layout consistente para que columnas no se descuadren */
    table.dataTable {
    width: 100% !important;
    table-layout: fixed;              /* clave para que header/body calculen igual */
    }

    /* Columna índice (#) */
    #myTable th:first-child,
    #myTable td:first-child {
        width: 48px !important;
        min-width: 48px;
        max-width: 48px;
        text-align: center;
        white-space: nowrap;
    }

    /* Columna Paciente */
    #myTable th:nth-child(2),
    #myTable td:nth-child(2) {
        min-width: 200px;
        white-space: normal;
    }

    /* Columna Estado */
    #myTable th:nth-child(9),
    #myTable td:nth-child(9) {
        min-width: 160px;
        text-align: center;
        white-space: nowrap;
    }

    /* Columna Acción */
    #myTable th:nth-child(10),
    #myTable td:nth-child(10) {
        min-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    /* Espacio visual entre Estado y Acción */
    #myTable td:nth-child(9) {
        padding-right: 12px;
    }

    #myTable td:nth-child(10) {
        padding-left: 12px;
    }

    /* Evita que el badge de Estado se sobreponga con Acción */
    #myTable td:nth-child(9) {
        overflow: hidden;          /* corta lo que se salga */
    }

    #myTable td:nth-child(9) .badge,
    #myTable td:nth-child(9) .btn,
    #myTable td:nth-child(9) span {
        display: inline-block;
        max-width: 100%;
        white-space: normal;       /* permite que "Pendiente de verificación" baje */
        word-break: break-word;
    }

    #myTable td:nth-child(9),
    #myTable td:nth-child(10) {
        padding-left: 14px !important;
        padding-right: 14px !important;
    }
</style>
@stop

@section('js')

    {{-- hide notifcation --}}
    <script>
        $(document).ready(function() {
            $(".alert").not("#editModeBanner").delay(6000).slideUp(300);
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
        // ============================
        // ✅ HELPERS GLOBALES (para que Cancelar edición funcione)
        // ============================

        window.__force2Decimals = function (raw) {
            let s = String(raw ?? '').trim().replace(',', '.');
            if (s === '') return '';
            const n = Number(s);
            if (!isFinite(n)) return s;
            return n.toFixed(2);
        };

        window.__syncAmountAll = function (val) {
            $('#modalAmountInput').val(val);
            $('#modalAmountInputTransfer').val(val);
            $('#modalAmountInputCash').val(val);
        };

        window.__syncPaidAmountAll = function (val) {
            $('#modalPaidAmountInputCard').val(val);
            $('#modalPaidAmountInputTransfer').val(val);
            $('#modalPaidAmountInputCash').val(val);
            $('#modalAmountPaidHidden').val(val);
        };

        // ✅ Badge global (lo usas en __restoreFromSnapshot)
        window.paymentStatusBadge = function (status) {
            const s = String(status || '').trim().toLowerCase();

            const colors = {
                unpaid: '#95a5a6',
                pending: '#f39c12',
                partial: '#3498db',   // ✅ NUEVO (elige el color que prefieras)
                paid: '#2ecc71',
                refunded: '#9b59b6',
            };

            const labels = {
                unpaid: 'No pagado',
                pending: 'Pendiente',
                partial: 'Pagado parcialmente', // ✅ NUEVO
                paid: 'Pagado',
                refunded: 'Reembolsado',
            };

            const key = s || 'na';
            const color = colors[key] || '#95a5a6';
            const label = labels[key] || (status ? String(status) : 'N/A');

            return `<span class="badge px-2 py-1" style="background-color:${color};color:white;">${label}</span>`;
        };

        // ✅ UI método pago global (la usas en __enterEditModeUI y __exitEditModeUI)
        window.__setPaymentMethodUI = function (newPm) {
            const pm = String(newPm || '').trim().toLowerCase();

            // Guardar método actual (draft)
            $('#modalPaymentMethodRaw').val(pm);

            // Mostrar bloques
            if (pm === 'card') {
                $('#paymentSectionWrapper').show();
                $('#paymentCardBlock').show();
                $('#paymentTransferBlock').hide();
                $('#paymentCashBlock').hide();
            } else if (pm === 'transfer') {
                $('#paymentSectionWrapper').show();
                $('#paymentTransferBlock').show();
                $('#paymentCardBlock').hide();
                $('#paymentCashBlock').hide();
            } else if (pm === 'cash') {
                $('#paymentSectionWrapper').show();
                $('#paymentCashBlock').show();
                $('#paymentCardBlock').hide();
                $('#paymentTransferBlock').hide();
            } else {
                $('#paymentSectionWrapper').hide();
                $('#paymentCardBlock').hide();
                $('#paymentTransferBlock').hide();
                $('#paymentCashBlock').hide();
            }

            // Set selects (los 3) para reflejar el método
            $('#modalPaymentMethodSelectCard').val(pm || 'card');
            $('#modalPaymentMethodSelectTransfer').val(pm || 'transfer');
            $('#modalPaymentMethodSelectCash').val(pm || 'cash');

            // Deshabilitar inputs del bloque oculto (evita duplicados)
            const isCard = (pm === 'card');
            const isTransfer = (pm === 'transfer');
            const isCash = (pm === 'cash');

            $('#paymentCardBlock :input').prop('disabled', !isCard);
            $('#paymentTransferBlock :input').prop('disabled', !isTransfer);
            $('#paymentCashBlock :input').prop('disabled', !isCash);

            // Re-habilitar selects del bloque visible
            if (isCard) $('#modalPaymentMethodSelectCard').prop('disabled', false);
            if (isTransfer) $('#modalPaymentMethodSelectTransfer').prop('disabled', false);
            if (isCash) $('#modalPaymentMethodSelectCash').prop('disabled', false);
        };

        // ✅ Regla: "Pendiente de verificación" SOLO puede existir si método = transferencia
        function __applyAppointmentStatusOptionsByPaymentMethod(pmRaw) {
            const pm = String(pmRaw || '').trim().toLowerCase();

            const $status = $('#modalStatusSelect');
            if (!$status.length) return;

            // Reset: habilitar/mostrar todo
            $status.find('option').prop('disabled', false).show();

            // Si NO es transferencia => ocultar/deshabilitar pending_verification
            if (pm !== 'transfer') {
                const $pv = $status.find('option[value="pending_verification"]');
                if ($pv.length) {
                    $pv.prop('disabled', true).hide();
                }

                // Si quedó seleccionado por inconsistencia, muévelo a "En espera" (on_hold)
                const current = String($status.val() || '').trim().toLowerCase();
                if (current === 'pending_verification') {
                    $status.val('on_hold').trigger('change');
                }
            }
        }

        // ✅ Limpiar draft global (la usas al cambiar método en modo edición)
        window.__clearPaymentDraftFields = function (pm) {
            const method = String(pm || '').trim().toLowerCase();

            window.__syncAmountAll('');
            window.__syncPaidAmountAll('');

            // ❌ NO borrar estado de pago al cambiar método
            // $('#modalPaymentStatusSelect').val('');
            // $('#modalPaymentStatusSelectCard').val('');
            // $('#modalPaymentStatusHidden').val('');

            // $('#modalPaymentStatusBadge').html(window.paymentStatusBadge(''));
            // $('#modalPaymentStatusBadge2').html(window.paymentStatusBadge(''));

            // Tarjeta
            $('#modalClientTransactionIdInput').val('');
            $('#modalPaymentPaidAtInput').val('');
            $('#modalClientTransactionIdHidden').val('');
            $('#modalPaymentPaidAtHidden').val('');

            // Transfer
            $('#modalTransferBankOriginInput').val('');
            $('#modalTransferPayerNameInput').val('');
            $('#modalTransferDateInput').val('');
            $('#modalTransferReferenceInput').val('');
            $('#modalTransferReceiptFile').val('');

            $('#modalTransferValidationSelect').val('');
            $('#modalTransferValidationNotes').val('');
            $('#modalTransferValidationStatusInput').val('');
            $('#modalTransferValidationNotesInput').val('');
            $('#transferValidationNotesWrapper').hide();
            $('#transferNotesRequired').hide();
            $('#transferNotesOptional').hide();

            // Cash
            $('#modalCashPaidAtInput').val('');
            $('#modalCashNotesInput').val('');
            $('#modalCashPaidAtHidden').val('');
            $('#modalCashNotesHidden').val('');

            $('#modalAmountPaidHidden').val('');
            $('#modalPaymentMethodRaw').val(method);
        };
        $(document).on('click', '.view-appointment-btn', function() {
            // ✅ RESET DURO: al abrir, limpia cualquier "draft" viejo (evita glitch de valores fantasma)
            $('#btnSaveChanges').prop('disabled', true);
            window.__apptIsEditMode = false;
            $('body').removeClass('appt-edit-mode appt-quick-transfer-mode transfer-notes-visible transfer-notes-opt transfer-notes-req');

            // Limpia campos de pago que suelen quedarse pegados aunque la sección esté oculta
            $('#modalPaymentMethodRaw').val('');
            $('#modalAmountPaidHidden').val('');
            $('#modalPaidAmountInputCard').val('');
            $('#modalPaidAmountInputTransfer').val('');
            $('#modalPaidAmountInputCash').val('');

            // Limpia también amount por si acaso
            $('#modalAmountInput').val('');
            $('#modalAmountInputTransfer').val('');
            $('#modalAmountInputCash').val('');

            // Limpia card extras
            $('#modalClientTransactionIdInput').val('');
            $('#modalPaymentPaidAtInput').val('');
            $('#modalClientTransactionIdHidden').val('');
            $('#modalPaymentPaidAtHidden').val('');

            // Limpia cash extras
            $('#modalCashPaidAtInput').val('');
            $('#modalCashPaidAtHidden').val('');
            $('#modalCashNotesInput').val('');
            $('#modalCashNotesHidden').val('');

            // Limpia notes de pago (por si el draft quedó)
            $('#modalCardNotesInput').val('');
            $('#modalTransferNotesInput').val('');
            $('#modalPaymentNotesHidden').val('');

            window.__transferValidationTouched = false;
            $('#modalTransferValidationTouchedInput').val('0');
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

            // ✅ Contexto para reagendar (lo usará el wizard)
            window.__rescheduleContext = {
                appointment_id: $(this).data('id'),
                employee_id: $(this).data('employee-id'),
                area_text: $(this).data('area'),
                service_text: $(this).data('service'),
                employee_text: $(this).data('employee'),
                old_date: $(this).data('date'),
                old_start_time: $(this).data('start-time'),
                old_end_time: $(this).data('end-time'),
                appointment_mode: $(this).data('appointment-mode'),
            };

            // ===== SECCIÓN 2: Datos del paciente =====
            $('#modalPatientFullName').text($(this).data('name') || 'N/A');

            // Estos quedan en N/A hasta que los conectes con data-* reales
            const docType = $(this).data('doc-type');

            const patientAgeRaw = $(this).data('patient-age');
            const patientDobRaw = $(this).data('patient-dob');
            let patientAgeFinal = 'N/A';

            if (patientAgeRaw !== null && patientAgeRaw !== undefined && String(patientAgeRaw).trim() !== '') {
                const n = Number(String(patientAgeRaw).trim());
                if (isFinite(n)) patientAgeFinal = `${n} años`;
            }

            $('#modalPatientAge').text(patientAgeFinal);

            // ✅ Fecha de nacimiento (DOB): texto bonito + input date (YYYY-MM-DD)
            let dobTextFinal = 'N/A';
            let dobInputVal = '';

            if (patientDobRaw !== null && patientDobRaw !== undefined && String(patientDobRaw).trim() !== '') {
            const raw = String(patientDobRaw).trim();

            // Tomar solo "YYYY-MM-DD" aunque venga con hora "YYYY-MM-DD HH:MM:SS"
            const datePart = raw.split(' ')[0];

            // Validar formato y formatear bonito
            if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                dobInputVal = datePart;

                const [yy, mm, dd] = datePart.split('-').map(Number);
                const dObj = new Date(yy, (mm || 1) - 1, dd || 1); // sin UTC shift

                dobTextFinal = dObj.toLocaleDateString('es-EC', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
                });
            } else {
                // fallback si viene raro
                dobTextFinal = raw;
                dobInputVal = '';
            }
            }

            $('#modalPatientDobText').text(dobTextFinal);
            $('#modalPatientDobInput').val(dobInputVal);

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
            const paymentStatusRaw = $(this).data('payment-status');          // pending|paid|refunded
            const amountRaw = $(this).data('amount');
            const paymentPaidAtRaw = $(this).data('payment-paid-at');         // NUEVO: datetime real (BD)
            const paymentNotesRaw = $(this).data('payment-notes');

            function __toDatetimeLocalValue(dateRaw) {
                if (!dateRaw || String(dateRaw).trim() === '') return '';
                const d = new Date(String(dateRaw));
                if (isNaN(d.getTime())) return '';
                const pad = (n) => String(n).padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            }

            function __formatNiceDateTime(dateRaw) {
                if (!dateRaw || String(dateRaw).trim() === '') return 'N/A';
                const d = new Date(String(dateRaw));
                if (isNaN(d.getTime())) return String(dateRaw);
                const datePart = d.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });
                const timePart = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                return `${datePart} · ${timePart}`;
            }

            // ✅ Fecha del pago (prioridad: payment_paid_at, fallback: createdAtFinal)
            const paymentDateFinal = (paymentPaidAtRaw && String(paymentPaidAtRaw).trim() !== '')
                ? __formatNiceDateTime(paymentPaidAtRaw)
                : (createdAtFinal || 'N/A');

            // Reset visual
            $('#paymentSectionWrapper').hide();
            $('#paymentCardBlock').hide();
            $('#paymentTransferBlock').hide();

            // Helpers para labels/badges
            function paymentMethodLabel(method) {
                const m = String(method || '').trim().toLowerCase();
                if (m === 'card') return 'Tarjeta';
                if (m === 'transfer') return 'Transferencia';
                if (m === 'cash') return 'Efectivo';
                return m ? (m.charAt(0).toUpperCase() + m.slice(1)) : 'N/A';
            }

            function paymentStatusBadge(status) {
                const s = String(status || '').trim().toLowerCase();

                // Ajusta aquí a tus estados reales si los tienes definidos
                const colors = {
                    unpaid: '#95a5a6',
                    pending: '#f39c12',
                    partial: '#3498db',   // ✅ NUEVO (elige el color que prefieras)
                    paid: '#2ecc71',
                    refunded: '#9b59b6',
                };

                const labels = {
                    unpaid: 'No pagado',
                    pending: 'Pendiente',
                    partial: 'Pagado parcialmente', // ✅ NUEVO
                    paid: 'Pagado',
                    refunded: 'Reembolsado',
                };

                const key = s || 'na';
                const color = colors[key] || '#95a5a6';
                const label = labels[key] || (status ? String(status) : 'N/A');

                return `<span class="badge px-2 py-1" style="background-color:${color};color:white;">${label}</span>`;
            }

            // ✅ Datos de transferencia disponibles SIEMPRE (evita undefined fuera del if)
            const tBankOrigin = $(this).data('transfer-bank-origin') || '';
            const tPayerName  = $(this).data('transfer-payer-name') || '';
            const tDateRaw    = $(this).data('transfer-date') || '';
            const tReference  = $(this).data('transfer-reference') || '';
            const tReceiptPath = $(this).data('transfer-receipt-path') || '';

            function __setPaymentMethodUI(newPm) {
                const pm = String(newPm || '').trim().toLowerCase();

                // Guardar método real
                $('#modalPaymentMethodRaw').val(pm);

                // Mostrar bloques
                if (pm === 'card') {
                    $('#paymentSectionWrapper').show();
                    $('#paymentCardBlock').show();
                    $('#paymentTransferBlock').hide();
                    $('#paymentCashBlock').hide();
                } else if (pm === 'transfer') {
                    $('#paymentSectionWrapper').show();
                    $('#paymentTransferBlock').show();
                    $('#paymentCardBlock').hide();
                    $('#paymentCashBlock').hide();
                } else if (pm === 'cash') {
                    $('#paymentSectionWrapper').show();
                    $('#paymentCashBlock').show();
                    $('#paymentCardBlock').hide();
                    $('#paymentTransferBlock').hide();
                } else {
                    $('#paymentSectionWrapper').hide();
                    $('#paymentCardBlock').hide();
                    $('#paymentTransferBlock').hide();
                    $('#paymentCashBlock').hide();
                }

                // ✅ Set selects (los dos) para que reflejen el método
                $('#modalPaymentMethodSelectCard').val(pm || 'card');
                $('#modalPaymentMethodSelectTransfer').val(pm || 'transfer');
                $('#modalPaymentMethodSelectCash').val(pm || 'cash');

                // ✅ Importantísimo: deshabilitar inputs del bloque oculto para no mandar duplicados
                const isCard = (pm === 'card');
                const isTransfer = (pm === 'transfer');
                const isCash = (pm === 'cash');

                $('#paymentCardBlock :input').prop('disabled', !isCard);
                $('#paymentTransferBlock :input').prop('disabled', !isTransfer);
                $('#paymentCashBlock :input').prop('disabled', !isCash);

                // ✅ Re-habilitar los selects de método del bloque visible (por si quedaron disabled)
                if (isCard) {
                    $('#modalPaymentMethodSelectCard').prop('disabled', false);
                } else {
                    $('#modalPaymentMethodSelectTransfer').prop('disabled', false);
                }

                // ✅ Si estamos en modo edición, refrescar asteriscos y botón guardar
                if (window.__apptIsEditMode) {
                    __toggleRequiredAsterisks(true);
                    __updateSaveButtonState();
                }
            }

            // ✅ NUEVO: limpiar campos del método NUEVO (solo draft / edición)
            function __clearPaymentDraftFields(pm) {
                const method = String(pm || '').trim().toLowerCase();

                // 1) Limpia inputs comunes (monto total + monto pagado) en TODOS
                __syncAmountAll('');
                __syncPaidAmountAll('');

                // 2) Limpia estado de pago (arriba + tarjeta + badges)
                //    Deja selects en vacío visualmente (pendiente si quieres), pero lo más "limpio" es vacío.
                $('#modalPaymentStatusSelect').val('');          // top
                $('#modalPaymentStatusSelectCard').val('');      // card
                $('#modalPaymentStatusHidden').val('');

                // Opcional: badges a N/A en edición (no asusta porque en edición se ven inputs)
                $('#modalPaymentStatusBadge').html(paymentStatusBadge(''));
                $('#modalPaymentStatusBadge2').html(paymentStatusBadge(''));

                // 3) Limpia tarjeta
                $('#modalClientTransactionIdInput').val('');
                $('#modalPaymentPaidAtInput').val('');
                $('#modalClientTransactionIdHidden').val('');
                $('#modalPaymentPaidAtHidden').val('');

                // 4) Limpia transferencia
                $('#modalTransferBankOriginInput').val('');
                $('#modalTransferPayerNameInput').val('');
                $('#modalTransferDateInput').val('');
                $('#modalTransferReferenceInput').val('');
                $('#modalTransferReceiptFile').val(''); // limpia selección de archivo

                // Validación de transferencia (draft)
                $('#modalTransferValidationSelect').val('');
                $('#modalTransferValidationNotes').val('');
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
                $('#transferValidationNotesWrapper').hide();
                $('#transferNotesRequired').hide();
                $('#transferNotesOptional').hide();

                // 5) Limpia efectivo
                $('#modalCashPaidAtInput').val('');
                $('#modalCashNotesInput').val('');
                $('#modalCashPaidAtHidden').val('');
                $('#modalCashNotesHidden').val('');

                // 6) Limpia hidden de monto pagado
                $('#modalAmountPaidHidden').val('');

                // 7) IMPORTANTE: el método seleccionado queda como "raw" (draft)
                $('#modalPaymentMethodRaw').val(method);
            }

            const pm = String(paymentMethodRaw || '').trim().toLowerCase();
            __setPaymentMethodUI(pm);
            __applyAppointmentStatusOptionsByPaymentMethod(pm);
            __setQuickValidateVisibility(pm);

            // ✅ Aplicar regla de notas según estado actual (por si está vacío)
            $('#modalTransferValidationSelect').trigger('change');

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

                // ✅ Notes (Tarjeta)
                if (paymentNotesRaw && String(paymentNotesRaw).trim() !== '') {
                    $('#modalCardNotesText').text(String(paymentNotesRaw));
                    $('#modalCardNotesInput').val(String(paymentNotesRaw));
                } else {
                    $('#modalCardNotesText').html('<span class="text-muted font-italic small">N/A</span>');
                    $('#modalCardNotesInput').val('');
                }

                // ✅ Monto pagado (desde BD: appointments.paid_amount)
                const paidAmountRaw = $(this).data('paid-amount');

                let paidAmountVal = '';
                let paidAmountText = 'N/A';

                if (paidAmountRaw !== null && paidAmountRaw !== undefined && String(paidAmountRaw).trim() !== '') {
                    const n = Number(String(paidAmountRaw).trim().replace(',', '.'));
                    if (isFinite(n)) {
                        paidAmountVal = n.toFixed(2);          // para input (sin $)
                        paidAmountText = `$${paidAmountVal}`;  // para lectura (con $)
                    } else {
                        // fallback si viene raro
                        paidAmountVal = String(paidAmountRaw).trim();
                        paidAmountText = String(paidAmountRaw).trim();
                    }
                }

                $('#modalPaidAmountText').text(paidAmountText);
                $('#modalPaidAmountInputCard').val(paidAmountVal);

                // ✅ Hidden (IMPORTANTE para no mandar vacío si no editas)
                $('#modalAmountPaidHidden').val(paidAmountVal);

                // ✅ Precargar inputs editables (tarjeta)
                $('#modalClientTransactionIdInput').val(clientTxIdRaw ? String(clientTxIdRaw) : '');

                // datetime-local input (payment_paid_at)
                $('#modalPaymentPaidAtInput').val(__toDatetimeLocalValue(paymentPaidAtRaw));

                // ✅ Sincronizar estado del pago (arriba + tarjeta + hidden + badges)
                __syncPaymentStatusEverywhere(paymentStatusRaw, 'init');

                // ✅ Precargar hiddens para submit (tarjeta)
                $('#modalClientTransactionIdHidden').val(clientTxIdRaw ? String(clientTxIdRaw) : '');
                $('#modalPaymentPaidAtHidden').val(__toDatetimeLocalValue(paymentPaidAtRaw));

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

                // ✅ Texto modo lectura (Validación)
                (function () {
                    const labels = {
                        '': 'Sin revisar',
                        'validated': 'Validada',
                        'rejected': 'Rechazada'
                    };
                    $('#modalTransferValidationText').text(labels[vStatus] ?? 'Sin revisar');
                })();

                // Reset UI
                $('#modalTransferValidationSelect').val('');
                $('#modalTransferValidationSelect').data('prev', '');
                $('#modalTransferValidationNotes').val('');
                $('#transferValidationNotesWrapper').hide();
                $('#transferNotesRequired').hide();
                $('#transferNotesOptional').hide();
                $('#transferValidationMeta').hide();

                // Si YA EXISTE una validación previa
                if (vStatus) {

                    // 1️⃣ Select
                    $('#modalTransferValidationSelect').val(vStatus);
                    $('#modalTransferValidationSelect').data('prev', vStatus);

                    // 2️⃣ Notes (lectura + edición)
                    if (validationNotes && String(validationNotes).trim() !== '') {
                        $('#modalTransferValidationNotes').val(validationNotes);
                        $('#modalTransferValidationNotesText').text(String(validationNotes));
                        $('#transferValidationNotesWrapper').show();
                    } else {
                        $('#modalTransferValidationNotes').val('');
                        $('#modalTransferValidationNotesText').html('<span class="text-muted font-italic small">N/A</span>');
                        $('#transferValidationNotesWrapper').hide();
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

                // ✅ Notes (Transferencia)
                if (paymentNotesRaw && String(paymentNotesRaw).trim() !== '') {
                    $('#modalTransferNotesText').text(String(paymentNotesRaw));
                    $('#modalTransferNotesInput').val(String(paymentNotesRaw));
                } else {
                    $('#modalTransferNotesText').html('<span class="text-muted font-italic small">N/A</span>');
                    $('#modalTransferNotesInput').val('');
                }

                // ✅ Monto pagado (desde BD: data-paid-amount)
                const paidAmountRaw = $(this).data('paid-amount');

                let paidAmountVal = '';
                let paidAmountText = 'N/A';

                if (paidAmountRaw !== null && paidAmountRaw !== undefined && String(paidAmountRaw).trim() !== '') {
                    const n = Number(String(paidAmountRaw).trim().replace(',', '.'));
                    if (isFinite(n)) {
                        paidAmountVal = n.toFixed(2);
                        paidAmountText = `$${paidAmountVal}`;
                    } else {
                        paidAmountVal = String(paidAmountRaw).trim();
                        paidAmountText = String(paidAmountRaw).trim();
                    }
                }

                $('#modalTransferPaidAmountText').text(paidAmountText);
                $('#modalPaidAmountInputTransfer').val(paidAmountVal);

                // ✅ Hidden (para backend aunque no edites)
                $('#modalAmountPaidHidden').val(paidAmountVal);

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

            } else if (pm === 'cash') {
                $('#paymentSectionWrapper').show();
                $('#paymentCashBlock').show();

                // Labels
                $('#modalCashMethodLabel').text('Efectivo');

                const amountText =
                    amountRaw !== null && amountRaw !== undefined && amountRaw !== ''
                        ? `$${parseFloat(amountRaw).toFixed(2)}`
                        : 'N/A';

                $('#modalCashAmount').text(amountText);

                // ✅ Monto pagado (desde BD: data-paid-amount)
                const paidAmountRaw = $(this).data('paid-amount');

                let paidAmountVal = '';
                let paidAmountText = 'N/A';

                if (paidAmountRaw !== null && paidAmountRaw !== undefined && String(paidAmountRaw).trim() !== '') {
                    const n = Number(String(paidAmountRaw).trim().replace(',', '.'));
                    if (isFinite(n)) {
                        paidAmountVal = n.toFixed(2);
                        paidAmountText = `$${paidAmountVal}`;
                    } else {
                        paidAmountVal = String(paidAmountRaw).trim();
                        paidAmountText = String(paidAmountRaw).trim();
                    }
                }

                $('#modalCashPaidAmountText').text(paidAmountText);
                $('#modalPaidAmountInputCash').val(paidAmountVal);

                // ✅ Hidden (para backend aunque no edites)
                $('#modalAmountPaidHidden').val(paidAmountVal);

                // cash_paid_at desde data-*
                const cashPaidAtRaw = $(this).data('payment-paid-at'); // viene de appointments.payment_paid_at

                // Texto bonito
                let cashPaidAtText = 'N/A';
                if (cashPaidAtRaw && String(cashPaidAtRaw).trim() !== '') {
                    const d = new Date(String(cashPaidAtRaw));
                    if (!isNaN(d.getTime())) {
                        const datePart = d.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });
                        const timePart = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                        cashPaidAtText = `${datePart} · ${timePart}`;
                    } else {
                        cashPaidAtText = String(cashPaidAtRaw);
                    }
                }
                $('#modalCashPaidAtText').text(cashPaidAtText);

                // Input datetime-local (formato YYYY-MM-DDTHH:MM)
                // Si no hay cash_paid_at, lo dejamos vacío (y cuando edites, tú lo pones)
                if (cashPaidAtRaw && String(cashPaidAtRaw).trim() !== '') {
                    const d = new Date(String(cashPaidAtRaw));
                    if (!isNaN(d.getTime())) {
                        const pad = (n) => String(n).padStart(2, '0');
                        const localVal = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                        $('#modalCashPaidAtInput').val(localVal);
                    } else {
                        $('#modalCashPaidAtInput').val('');
                    }
                } else {
                    $('#modalCashPaidAtInput').val('');
                }

                // Notes (si no tienes columna todavía, esto quedará N/A siempre)
                const cashNotesRaw = $(this).data('payment-notes');
                if (cashNotesRaw && String(cashNotesRaw).trim() !== '') {
                    $('#modalCashNotesText').text(String(cashNotesRaw));
                    $('#modalCashNotesInput').val(String(cashNotesRaw));
                } else {
                    $('#modalCashNotesText').html(`<span class="text-muted font-italic small">N/A</span>`);
                    $('#modalCashNotesInput').val('');
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
                .replace(/\s+/g, '_')
                .replace('cancelled', 'canceled'); // ✅ fuerza tu formato en BD

            // Colores por status normalizado
            const statusColors = {
                // ✅ nuevos
                pending_verification: '#7f8c8d',
                paid: '#2ecc71',
                confirmed: '#3498db',
                completed: '#008000',
                canceled: '#ff0000',
                rescheduled: '#f1c40f',
                no_show: '#e67e22',
                on_hold: '#95a5a6',
            };

            const statusLabels = {
                // ✅ nuevos
                pending_verification: 'Pendiente de verificación',
                paid: 'Pagada',
                confirmed: 'Confirmada',
                completed: 'Completada',
                canceled: 'Cancelada',
                rescheduled: 'Reagendada',
                no_show: 'No asistió',
                on_hold: 'En espera',
            };

            const badgeColor = statusColors[normalizedStatus] || '#7f8c8d';
            const badgeLabel = statusLabels[normalizedStatus] || 'Estado desconocido';

            const badgeHtml = `<span class="badge px-2 py-1" style="background-color: ${badgeColor}; color: white;">${badgeLabel}</span>`;

            $('#modalStatusBadge').html(badgeHtml);
            $('#modalStatusBadgeLegacy').html(badgeHtml);

            // Por ahora, estado del pago queda N/A hasta que lo conectemos a tus campos reales
            $('#modalPaymentStatusBadge').html(window.paymentStatusBadge(paymentStatusRaw));

            // ============================
            // ✅ Pre-cargar selects de estados (Resumen)
            // ============================

            // Estado cita (usa el normalizedStatus que ya armaste)
            $('#modalStatusSelect').val(normalizedStatus || 'pending_verification');
            __applyPaymentOptionsByAppointmentStatus(normalizedStatus);

            // Estado pago (normaliza para que matchee opciones)
            const pStat = String(paymentStatusRaw || '').trim().toLowerCase();
            $('#modalPaymentStatusSelect').val(pStat);

            // ✅ Hiddens que se envían al backend
            $('#modalStatusHidden').val(normalizedStatus || 'pending_verification');
            $('#modalPaymentStatusHidden').val(pStat);
            $('#modalPaymentStatusSelectCard').val(pStat);
            $('#modalPaymentStatusSelect').data('last_valid', pStat);
            $('#modalPaymentStatusSelectCard').data('last_valid', pStat);
            $('#modalPaymentStatusBadge2').html(window.paymentStatusBadge(pStat));

            // ============================
            // ✅ Cambios en selects de estado (Resumen)
            // ============================
            $(document).off('change.apptStatusSelects'); // evita duplicados si recargas scripts
            $(document).on('change.apptStatusSelects', '#modalStatusSelect', function () {
                const v = String($(this).val() || '').trim().toLowerCase();

                // hidden que se envía
                $('#modalStatusHidden').val(v);
                // ✅ aplicar regla: si está on_hold, limitar estados de pago
                __applyPaymentOptionsByAppointmentStatus(v);

                // refrescar badge visible
                const statusColors = {
                    pending_payment: '#f39c12',
                    processing: '#3498db',
                    paid: '#2ecc71',
                    cancelled: '#ff0000',
                    confirmed: '#3498db',
                    completed: '#008000',
                    on_hold: '#95a5a6',
                    rescheduled: '#f1c40f',
                    no_show: '#e67e22',
                    pending_verification: '#7f8c8d',
                };

                const statusLabels = {
                    pending_payment: 'Pendiente de pago',
                    processing: 'Procesando',
                    paid: 'Pagada',
                    cancelled: 'Cancelada',
                    confirmed: 'Confirmada',
                    completed: 'Completada',
                    on_hold: 'En espera',
                    rescheduled: 'Reagendada',
                    no_show: 'No asistió',
                    pending_verification: 'Pendiente de verificación',
                };

                const c = statusColors[v] || '#7f8c8d';
                const l = statusLabels[v] || 'Estado desconocido';

                $('#modalStatusBadge').html(`<span class="badge px-2 py-1" style="background-color:${c}; color:white;">${l}</span>`);

                __updateSaveButtonState();
            });

            function __syncPaymentStatusEverywhere(v, source) {
                const val = String(v || '').trim().toLowerCase();

                // Hidden que se envía
                $('#modalPaymentStatusHidden').val(val);

                // Selects (arriba + tarjeta)
                if (source !== 'top')  $('#modalPaymentStatusSelect').val(val);
                if (source !== 'card') $('#modalPaymentStatusSelectCard').val(val);

                // Badges (arriba + tarjeta)
                $('#modalPaymentStatusBadge').html(window.paymentStatusBadge(val));
                $('#modalPaymentStatusBadge2').html(window.paymentStatusBadge(val));

                __updateSaveButtonState();
            }

            function __applyPaymentOptionsByAppointmentStatus(apptStatus) {
                const s = String(apptStatus || '').trim().toLowerCase();

                // defaults: todo habilitado
                const $p1 = $('#modalPaymentStatusSelect');      // Resumen
                const $p2 = $('#modalPaymentStatusSelectCard');  // Tarjeta

                // ✅ Reset: todo visible y habilitado (NO ocultamos nada)
                $p1.find('option').prop('disabled', false).show();
                $p2.find('option').prop('disabled', false).show();

                // ✅ Regla #1: si la cita está EN ESPERA (on_hold)
                if (s === 'on_hold') {
                    const allowed = new Set(['unpaid', 'pending', 'partial', 'paid']);

                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    // deshabilitar lo no permitido en ambos selects
                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #2: si la cita está PENDIENTE DE VERIFICACIÓN (pending_verification)
                // Solo permitir: pending (por defecto) y partial
                if (s === 'pending_verification') {
                    const allowed = new Set(['pending', 'partial']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #3: si la cita está PENDIENTE DE PAGO (pending_payment)
                // Solo permitir: unpaid y partial
                if (s === 'pending_payment') {
                    const allowed = new Set(['unpaid', 'partial']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #4: si la cita está PAGADA (paid)
                // Solo permitir: paid
                if (s === 'paid') {
                    const allowed = new Set(['paid']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #5: si la cita está CONFIRMADA (confirmed)
                // Solo permitir: unpaid, partial, paid
                if (s === 'confirmed') {
                    const allowed = new Set(['unpaid', 'partial', 'paid']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #6: si la cita está COMPLETADA (completed)
                // Solo permitir: paid y refunded
                if (s === 'completed') {
                    const allowed = new Set(['paid', 'refunded']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Regla #7: si la cita está NO ASISTIÓ (no_show)
                // Solo permitir: unpaid, partial, paid, refunded
                if (s === 'no_show') {
                    const allowed = new Set(['unpaid', 'partial', 'paid', 'refunded']);
                    // ✅ Regla global: si NO es transferencia, "pending" NO se permite jamás
                    const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                    const isTransfer = (pm === 'transfer');
                    if (!isTransfer) {
                        allowed.delete('pending');
                    }

                    [$p1, $p2].forEach($sel => {
                        $sel.find('option').each(function () {
                            const v = String($(this).val() || '').trim().toLowerCase();
                            if (!allowed.has(v)) {
                                $(this).prop('disabled', true); // ✅ solo deshabilita, NO ocultes
                            }
                        });
                    });

                    // si el valor actual no es permitido, dejar SIN selección (placeholder)
                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (!allowed.has(current)) {
                        __syncPaymentStatusEverywhere('', 'rule'); // deja placeholder + hidden vacío + badges N/A
                    }
                }

                // ✅ Guard final: "pending" SOLO puede existir si el método es transferencia
                const pmNow = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                if (pmNow !== 'transfer') {
                    const $p1Pending = $p1.find('option[value="pending"]');
                    const $p2Pending = $p2.find('option[value="pending"]');

                    const current = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    if (current === 'pending') {
                        __syncPaymentStatusEverywhere('', 'rule'); 
                    }

                    // Ocultar/deshabilitar pending en ambos selects
                    if ($p1Pending.length) $p1Pending.prop('disabled', true).hide();
                    if ($p2Pending.length) $p2Pending.prop('disabled', true).hide();
                }

                // ✅ Guardar lista "allowed" en data() según opciones habilitadas
                // (Esto evita que el change del estado del pago compare contra un allowed vacío)
                const allowedNow = [];
                $p1.find('option').each(function () {
                    const v = String($(this).val() || '').trim().toLowerCase();
                    if (!v) return; // placeholder (selecciona una opción)
                    if ($(this).prop('disabled')) return;
                    allowedNow.push(v);
                });

                // Guardamos en ambos selects (resumen y tarjeta) para que la validación funcione en el front
                [$p1, $p2].forEach($sel => $sel.data('allowed', allowedNow));
            }


            $(document).on('change.apptStatusSelects', '#modalPaymentStatusSelect', function () {
                const $sel = $(this);
                const next = String($sel.val() || '').trim().toLowerCase();

                // allowed calculado por estado de cita (y método)
                const allowedArr = $sel.data('allowed') || [];
                const allowed = new Set(allowedArr.map(x => String(x).trim().toLowerCase()));

                // si aún no hay allowed (por timing), lo recalculamos
                if (!allowed.size) {
                    __applyPaymentOptionsByAppointmentStatus($('#modalStatusSelect').val());
                }

                // ✅ Releer allowed después de recalcular (ya quedó guardado en data('allowed'))
                const allowedArr2 = $sel.data('allowed') || [];
                const allowed2 = new Set(allowedArr2.map(x => String(x).trim().toLowerCase()));

                if (!allowed2.has(next)) {
                    const last = String($sel.data('last_valid') || $('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    $sel.val(last);

                    alert('Ese estado de pago no es válido para el estado actual de la cita. Cambia primero el estado de la cita.');

                    return;
                }

                // ✅ válido: sincronizamos y guardamos como last_valid
                $sel.data('last_valid', next);
                __syncPaymentStatusEverywhere(next, 'top');
            });

            $(document).on('change.apptStatusSelects', '#modalPaymentStatusSelectCard', function () {
                const $sel = $(this);
                const next = String($sel.val() || '').trim().toLowerCase();

                // allowed calculado por estado de cita (y método)
                const allowedArr = $sel.data('allowed') || [];
                const allowed = new Set(allowedArr.map(x => String(x).trim().toLowerCase()));

                // si aún no hay allowed (por timing), lo recalculamos
                if (!allowed.size) {
                    __applyPaymentOptionsByAppointmentStatus($('#modalStatusSelect').val());
                }

                const allowedArr2 = $sel.data('allowed') || [];
                const allowed2 = new Set(allowedArr2.map(x => String(x).trim().toLowerCase()));

                if (!allowed2.has(next)) {
                    const last = String($sel.data('last_valid') || $('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
                    $sel.val(last);

                    alert('Ese estado de pago no es válido para el estado actual de la cita. Cambia primero el estado de la cita.');

                    return;
                }

                // ✅ válido: sincronizamos y guardamos como last_valid
                $sel.data('last_valid', next);
                __syncPaymentStatusEverywhere(next, 'card');
            });

            // ============================
            // ✅ Pre-cargar inputs editables desde data-*
            // ============================
            $('#modalPatientFullNameInput').val($(this).data('name') || '');
            $('#modalDocTypeInput').val(String($(this).data('doc-type') || '').trim().toLowerCase());
            $('#modalDocNumberInput').val($(this).data('doc-number') || '');
            $('#modalPatientDobInput').val((patientDobRaw ? String(patientDobRaw).trim().split(' ')[0] : ''));
            $('#modalEmailInput').val($(this).data('email') || '');
            $('#modalPhoneInput').val($(this).data('phone') || '');
            $('#modalAddressInput').val($(this).data('address') || '');

            const tzOnly = (tz ? String(tz).replace('-', '/') : '');
            $('#modalPatientTimezoneInput').val(tzOnly || '');

            $('#modalNotesInput').val($(this).data('notes') || '');

            $('#modalBillingNameInput').val(billingName === 'N/A' ? '' : billingName);
            $('#modalBillingDocTypeInput').val(String(billingDocType || '').trim().toLowerCase());
            $('#modalBillingDocNumberInput').val(billingDocNumber === 'N/A' ? '' : billingDocNumber);
            $('#modalBillingEmailInput').val(billingEmail === 'N/A' ? '' : billingEmail);
            $('#modalBillingPhoneInput').val(billingPhone === 'N/A' ? '' : billingPhone);
            $('#modalBillingAddressInput').val(billingAddress === 'N/A' ? '' : billingAddress);

            // ✅ Amount editable (card + transfer)
            let amt = '';
            if (amountRaw !== null && amountRaw !== undefined && amountRaw !== '') {
                amt = parseFloat(amountRaw).toFixed(2);
            }
            $('#modalAmountInput').val(amt);
            $('#modalAmountInputTransfer').val(amt);
            $('#modalAmountInputCash').val(amt);

            // ✅ asegurar formato final consistente
            __syncAmountAll(__force2Decimals(amt));

            function __force2Decimals(raw) {
                // Normaliza coma -> punto y limpia espacios
                let s = String(raw ?? '').trim().replace(',', '.');

                // Si está vacío, no fuerces nada
                if (s === '') return '';

                // Convertir a número
                const n = Number(s);

                // Si es inválido, lo dejamos tal cual (para no romper UX)
                if (!isFinite(n)) return s;

                // Forzar 2 decimales SIEMPRE (15 -> 15.00)
                return n.toFixed(2);
            }

            function __syncAmountAll(formattedVal) {
                $('#modalAmountInput').val(formattedVal);
                $('#modalAmountInputTransfer').val(formattedVal);
                $('#modalAmountInputCash').val(formattedVal);
            }

            // 1) Mientras escribe: permitir dígitos + (.) o (,). No formatear aquí.
            $(document).off('input.syncAmount');
            $(document).on('input.syncAmount', '#modalAmountInput, #modalAmountInputTransfer, #modalAmountInputCash', function () {
                let raw = String($(this).val() ?? '');

                // permitir solo 0-9, punto y coma
                raw = raw.replace(/[^\d.,]/g, '');

                // permitir solo UN separador decimal (el primero que aparezca)
                const idxDot = raw.indexOf('.');
                const idxComma = raw.indexOf(',');
                let sepIndex = -1;
                if (idxDot >= 0 && idxComma >= 0) sepIndex = Math.min(idxDot, idxComma);
                else sepIndex = (idxDot >= 0 ? idxDot : idxComma);

                if (sepIndex >= 0) {
                    const before = raw.slice(0, sepIndex + 1);
                    const after = raw.slice(sepIndex + 1).replace(/[.,]/g, '');
                    raw = before + after;
                }

                // actualizar el que estás editando sin pelear
                $(this).val(raw);

                // sincronizar a los otros dos
                __syncAmountAll(raw);
                __updateSaveButtonState();
            });

            // 2) Al salir del input (blur): asegura que quede 15.00 sí o sí
            $(document).off('blur.syncAmount');
            $(document).on('blur.syncAmount', '#modalAmountInput, #modalAmountInputTransfer, #modalAmountInputCash', function () {
                const formatted = __force2Decimals($(this).val());
                __syncAmountAll(formatted);
                __updateSaveButtonState();
            });

            function __syncPaidAmountAll(formattedVal) {
                $('#modalPaidAmountInputCard').val(formattedVal);
                $('#modalPaidAmountInputTransfer').val(formattedVal);
                $('#modalPaidAmountInputCash').val(formattedVal);
                $('#modalAmountPaidHidden').val(formattedVal); // hidden siempre actualizado
            }

            // 1) Mientras escribe: permitir dígitos + (.) o (,). No formatear aquí.
            $(document).off('input.syncPaidAmount');
            $(document).on('input.syncPaidAmount', '#modalPaidAmountInputCard, #modalPaidAmountInputTransfer, #modalPaidAmountInputCash', function () {
                let raw = String($(this).val() ?? '');

                raw = raw.replace(/[^\d.,]/g, '');

                const idxDot = raw.indexOf('.');
                const idxComma = raw.indexOf(',');
                let sepIndex = -1;
                if (idxDot >= 0 && idxComma >= 0) sepIndex = Math.min(idxDot, idxComma);
                else sepIndex = (idxDot >= 0 ? idxDot : idxComma);

                if (sepIndex >= 0) {
                    const before = raw.slice(0, sepIndex + 1);
                    const after = raw.slice(sepIndex + 1).replace(/[.,]/g, '');
                    raw = before + after;
                }

                $(this).val(raw);

                __syncPaidAmountAll(raw);
                __updateSaveButtonState();
            });

            // 2) Al salir (blur) asegura 2 decimales
            $(document).off('blur.syncPaidAmount');
            $(document).on('blur.syncPaidAmount', '#modalPaidAmountInputCard, #modalPaidAmountInputTransfer, #modalPaidAmountInputCash', function () {
                const formatted = __force2Decimals($(this).val());
                __syncPaidAmountAll(formatted);
                __updateSaveButtonState();
            });

            // ✅ B4: Cambiar método (card/transfer) y refrescar UI
            $(document).off('change.paymentMethodSelect');

            $(document).on('change.paymentMethodSelect', '#modalPaymentMethodSelectCard, #modalPaymentMethodSelectTransfer, #modalPaymentMethodSelectCash', function () {
                const pm = String($(this).val() || '').trim().toLowerCase();
                window.__setPaymentMethodUI(pm);
                __applyAppointmentStatusOptionsByPaymentMethod(pm);
                __setQuickValidateVisibility(pm);
                __applyPaymentOptionsByAppointmentStatus($('#modalStatusSelect').val());

                if (window.__apptIsEditMode) {
                    window.__clearPaymentDraftFields(pm);

                    // ✅ Forzar repintado de asteriscos luego de mostrar/ocultar bloques
                    setTimeout(function () {
                        __toggleRequiredAsterisks(true);
                    }, 0);
                }

                __updateSaveButtonState();
            });

            // Transfer editable
            $('#modalTransferBankOriginInput').val(tBankOrigin ? String(tBankOrigin) : '');
            $('#modalTransferPayerNameInput').val(tPayerName ? String(tPayerName) : '');
            $('#modalTransferReferenceInput').val(tReference ? String(tReference) : '');

            // date input: YYYY-MM-DD
            if (tDateRaw && String(tDateRaw).trim() !== '') {
                const s = String(tDateRaw).trim();
                const datePart = s.split(' ')[0];
                if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                    $('#modalTransferDateInput').val(datePart);
                } else {
                    $('#modalTransferDateInput').val('');
                }
            } else {
                $('#modalTransferDateInput').val('');
            }

            // ✅ Si no es tarjeta, NO tocar campos de tarjeta en backend
            if (pm !== 'card') {
                $('#modalClientTransactionIdHidden').val('');
                $('#modalPaymentPaidAtHidden').val('');
            }

            // ✅ Snapshot final (BD) para habilitar Guardar cambios solo si hay cambios reales
            __setSnapshotFromCurrent();
        });
    </script>

    <script>
        // ✅ Cambios en validación de transferencia (solo aplica si el bloque existe)
        $(document).on('change', '#modalTransferValidationSelect', function () {
            window.__transferValidationTouched = true;
            $('#modalTransferValidationTouchedInput').val('1');
            const v = String($(this).val() || '').trim().toLowerCase();

            const $sel = $(this);
            const prev = String($sel.data('prev') || '').trim().toLowerCase();

            // ✅ Si cambia entre Validada <-> Rechazada: limpiar observaciones (queda en blanco)
            const switchedBetweenValidatedRejected =
                (prev === 'validated' && v === 'rejected') ||
                (prev === 'rejected' && v === 'validated');

            if (switchedBetweenValidatedRejected) {
                $('#modalTransferValidationNotes').val(''); // textarea (edición)
                $('#modalTransferValidationNotesText').html('<span class="text-muted font-italic small">N/A</span>'); // lectura
                $('#modalTransferValidationNotesInput').val(''); // hidden (por si acaso)
            }

            // ✅ Guardar el valor actual como "prev" para la próxima vez
            $sel.data('prev', v);

            const $notes = $('#modalTransferValidationNotes');

            // Placeholders dinámicos
            const phValidated = 'Ej: OK, confirmado en el banco.';
            const phRejected  = 'Ej: El comprobante no coincide con el monto / Falta referencia.';

            if (v === 'validated' || v === 'rejected') {
                $('body').addClass('transfer-notes-visible');
                $('#transferValidationNotesWrapper').show();

                // ✅ Habilitar textarea SOLO cuando hay estado
                $notes.prop('disabled', false);

                // ✅ Reset clases de labels
                $('body').removeClass('transfer-notes-opt transfer-notes-req');

                if (v === 'rejected') {
                    $('body').addClass('transfer-notes-req');
                    $notes.attr('placeholder', phRejected);
                } else {
                    $('body').addClass('transfer-notes-opt');
                    $notes.attr('placeholder', phValidated);
                }

            } else {
                $('body').removeClass('transfer-notes-visible transfer-notes-opt transfer-notes-req');

                // ✅ Sin revisar => NO permitir notas
                $('#transferValidationNotesWrapper').hide();

                // ✅ limpiar + deshabilitar
                $notes.val('');
                $notes.prop('disabled', true);
                $notes.attr('placeholder', 'Ej: Escribe una observación...');
            }

            __updateSaveButtonState();
        });

        function showFlash(type, message) {
            const safeMsg = String(message || '').trim() || 'Cambios guardados correctamente.';

            // Si existe un contenedor, úsalo; si no, lo crea arriba de la página
            let $c = $('#jsFlashContainer');
            if (!$c.length) {
                $('body').prepend('<div id="jsFlashContainer" style="position:fixed;top:15px;left:15px;right:15px;z-index:9999;"></div>');
                $c = $('#jsFlashContainer');
            }

            $c.html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${safeMsg}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);

            // Scroll suave hacia el mensaje (opcional)
            try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (e) {}

            // auto-ocultar en 4s (opcional)
            setTimeout(() => {
                try { $c.find('.alert').alert('close'); } catch(e) {}
            }, 4000);
        }

        // ✅ Antes de enviar el form: valida reglas y llena hidden inputs
        $(document).on('submit', '#appointmentStatusForm', function (e) {
            // ============================
            // ✅ Motivo del cambio (modal)
            // - Paciente + Facturación: opcional
            // - Pago (transfer/card/cash): obligatorio
            // - NO aplica para: patient_notes / payment_notes
            // - NO aplica para validación de transferencia
            // ============================
            if (!window.__changeReasonBypassSubmit) {
                const snap = window.__apptModalSnapshot || null;
                if (snap && window.__apptIsEditMode) {
                    const current = __getCurrentEditableState();

                    // Keys que NO deben disparar modal (según tu regla)
                    const excludedKeys = new Set([
                        'patient_notes',          // Notas del paciente
                        'payment_notes',          // Observaciones de pago
                        'transfer_validation_status',
                        'transfer_validation_notes'
                    ]);

                    // Paciente + Facturación (opcional)
                    const patientBillingKeys = new Set([
                        'patient_full_name',
                        'patient_doc_type',
                        'patient_doc_number',
                        'patient_dob',
                        'patient_email',
                        'patient_phone',
                        'patient_address',
                        'patient_timezone',

                        'billing_name',
                        'billing_doc_type',
                        'billing_doc_number',
                        'billing_email',
                        'billing_phone',
                        'billing_address'
                    ]);

                    // Pago (obligatorio)
                    // Incluye método + montos + fechas + datos de transferencia + comprobante
                    // (NO incluye payment_notes)
                    const paymentKeys = new Set([
                        'pmRaw',
                        'payment_status',
                        'amount',
                        'amount_paid',
                        'payment_paid_at',

                        'transfer_bank_origin',
                        'transfer_payer_name',
                        'transfer_date',
                        'transfer_reference',
                        'transfer_receipt_file_selected'
                    ]);

                    // Detectar qué cambió (comparando snapshot vs current)
                    const changedKeys = [];
                    for (const k of Object.keys(current)) {
                        if (excludedKeys.has(k)) continue;

                        if (__norm(current[k]) !== __norm(snap[k])) {
                            changedKeys.push(k);
                        }
                    }

                    const changedPatientBilling = changedKeys.some(k => patientBillingKeys.has(k));
                    const changedPayment = changedKeys.some(k => paymentKeys.has(k));

                    // Si NO cambió nada “relevante”, NO mostramos modal
                    if (changedPatientBilling || changedPayment) {
                        const mandatory = changedPayment; // pago manda (obligatorio)

                        // Reset valores del modal
                        $('#changeReasonSelect').val('');
                        $('#changeReasonOtherText').val('');
                        $('#changeReasonOtherWrapper').hide();

                        // Pintar etiquetas
                        $('#changeReasonRequiredTag').toggle(mandatory);
                        $('#changeReasonOptionalTag').toggle(!mandatory);

                        // Mensajito
                        if (mandatory) {
                            $('#changeReasonHelp').text('Estás cambiando información de pago. Debes seleccionar un motivo para continuar.');
                        } else {
                            $('#changeReasonHelp').text('Estás cambiando datos del paciente / facturación. Seleccionar un motivo es opcional.');
                        }

                        // Guardar si es obligatorio para validación del botón
                        window.__changeReasonIsMandatory = mandatory;

                        // Detener submit y abrir modal
                        e.preventDefault();
                        $('body').addClass('change-reason-open');
                        $('#changeReasonModal').modal('show');
                        setTimeout(() => {
                            const $bd = $('.modal-backdrop').last();
                            $bd.addClass('change-reason-backdrop')
                            .css('z-index', '1055');

                            $('#changeReasonModal').css('z-index', '1060');
                        }, 0);
                        return false;
                    }
                }
            }
            console.log('================= SUBMIT appointmentStatusForm =================');
            console.log('[patient_full_name]', $('#modalPatientFullNameInput').val());
            console.log('[patient_doc_type]', $('#modalDocTypeInput').val());
            console.log('[patient_doc_number]', $('#modalDocNumberInput').val());
            console.log('[patient_dob]', $('#modalPatientDobInput').val());
            console.log('[patient_email]', $('#modalEmailInput').val());
            console.log('[patient_phone]', $('#modalPhoneInput').val());
            console.log('[patient_address]', $('#modalAddressInput').val());
            console.log('[patient_timezone]', $('#modalPatientTimezoneInput').val());
            console.log('[FORM action]', $('#appointmentStatusForm').attr('action'));
            console.log('[FORM method]', $('#appointmentStatusForm').attr('method'));
            console.log('[appointment_id]', $('#modalAppointmentId').val());
            console.log('[status hidden]', $('#modalStatusHidden').val());
            console.log('[pmRaw hidden]', $('#modalPaymentMethodRaw').val());
            console.log('[payment_status hidden]', $('#modalPaymentStatusHidden').val());
            console.log('[payment_method hidden name]', $('#modalPaymentMethodHidden').val());
            console.log('[select validation]', $('#modalTransferValidationSelect').val());
            console.log('[notes textarea]', $('#modalTransferValidationNotes').val());
           
            console.log('===============================================================');
            const pmRaw = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
            $('#modalPaymentMethodHidden').val(pmRaw);
            const isTransfer = (pmRaw === 'transfer');
            const isCash = (pmRaw === 'cash');

            const isCard = (pmRaw === 'card');

            // ✅ Bloquear si el estado del pago quedó sin selección (placeholder)
            const paymentHidden = String($('#modalPaymentStatusHidden').val() || '').trim().toLowerCase();
            if (!paymentHidden) {
                e.preventDefault();
                alert('Debes seleccionar un estado del pago válido antes de guardar.');
                return false;
            }

            function __normalizeMoneyToFixed2(val) {
                let s = String(val ?? '').trim();

                if (s === '') return '';

                // coma -> punto (16,1 => 16.1)
                s = s.replace(/\s+/g, '').replace(',', '.');

                // por si alguien pegó símbolos
                s = s.replace(/[^0-9.]/g, '');

                // si hay más de un punto, une lo que sobra
                const parts = s.split('.');
                if (parts.length > 2) {
                    s = parts[0] + '.' + parts.slice(1).join('');
                }

                const n = Number(s);
                if (!isFinite(n)) return '';

                return n.toFixed(2);
            }

            // ✅ Normalizar "amount" ANTES de enviar (solo al guardar)
            if (isCard) {
                const fixed = __normalizeMoneyToFixed2($('#modalAmountInput').val());
                __syncAmountAll(fixed);
            }
            if (isTransfer) {
                const fixed = __normalizeMoneyToFixed2($('#modalAmountInputTransfer').val());
                __syncAmountAll(fixed);
            }
            if (isCash) {
                const fixed = __normalizeMoneyToFixed2($('#modalAmountInputCash').val());
                __syncAmountAll(fixed);
            }

            // ✅ Tarjeta: mandar client_transaction_id + payment_paid_at
            if (isCard) {
                const tx = String($('#modalClientTransactionIdInput').val() || '').trim();
                const paidAt = String($('#modalPaymentPaidAtInput').val() || '').trim(); // datetime-local

                const paidAmountFixed = __normalizeMoneyToFixed2($('#modalPaidAmountInputCard').val());
                $('#modalPaidAmountInputCard').val(paidAmountFixed);
                $('#modalAmountPaidHidden').val(paidAmountFixed);

                $('#modalClientTransactionIdHidden').val(tx);
                $('#modalPaymentPaidAtHidden').val(paidAt);
            } // ✅ Transferencia: normalizar monto pagado también
            if (isTransfer) {
                const paidAmountFixed = __normalizeMoneyToFixed2($('#modalPaidAmountInputTransfer').val());
                $('#modalPaidAmountInputTransfer').val(paidAmountFixed);
                $('#modalAmountPaidHidden').val(paidAmountFixed);
            } else if (!isCard) {
                // Si NO es transferencia y TAMPOCO es tarjeta, limpiamos
                $('#modalClientTransactionIdHidden').val('');
                $('#modalPaymentPaidAtHidden').val('');
            }

            // ✅ Payment notes: tomar del método activo (card/transfer/cash) y mandarlo SIEMPRE
            const pmNow = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();

            let paymentNotesNow = '';
            if (pmNow === 'cash') {
                paymentNotesNow = String($('#modalCashNotesInput').val() || '').trim();
            } else if (pmNow === 'card') {
                paymentNotesNow = String($('#modalCardNotesInput').val() || '').trim();
            } else if (pmNow === 'transfer') {
                paymentNotesNow = String($('#modalTransferNotesInput').val() || '').trim();
            }

            $('#modalPaymentNotesHidden').val(paymentNotesNow);

            // ✅ Si NO es cash, limpiamos cash_paid_at para que backend lo ponga NULL
            if (!isCash) {
                $('#modalCashPaidAtHidden').val('');
            }

            // ✅ Caso EFECTIVO: fuerza reglas
            if (isCash) {
                const cashPaidAt = String($('#modalCashPaidAtInput').val() || '').trim(); // datetime-local
                const cashNotes = String($('#modalCashNotesInput').val() || '').trim();

                // ✅ Normalizar monto pagado (cash)
                const paidAmountFixed = __normalizeMoneyToFixed2($('#modalPaidAmountInputCash').val());
                $('#modalPaidAmountInputCash').val(paidAmountFixed);
                $('#modalAmountPaidHidden').val(paidAmountFixed);

                // cash_paid_at NO puede ser vacío
                if (!cashPaidAt) {
                    e.preventDefault();
                    alert('Para marcar como "Efectivo", debes registrar la fecha del pago.');
                    return false;
                }

                // Mandar al backend
                $('#modalCashPaidAtHidden').val(cashPaidAt);
                $('#modalPaymentPaidAtHidden').val(cashPaidAt);
                $('#modalPaymentNotesHidden').val(cashNotes);

                // Transfer stuff vacío
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');

                return true;
            }

            // ✅ Caso NO transferencia: no mandamos validación transfer
            if (!isTransfer) {
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
                return true;
            }

            const v = String($('#modalTransferValidationSelect').val() || '').trim().toLowerCase();
            const notes = String($('#modalTransferValidationNotes').val() || '').trim();

            // ✅ Comparar contra lo que vino de BD al abrir el modal (snapshot)
            const snap = window.__apptModalSnapshot || {};
            const origV = String(snap.transfer_validation_status || '').trim().toLowerCase();
            const origNotes = String(snap.transfer_validation_notes || '').trim();

            // ✅ Si NO cambió nada, NO enviamos validación (para que NO se actualice validated_at)
            const validationChanged = (v !== origV) || (notes !== origNotes);

            if (!validationChanged) {
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
                // (opcional) también resetea el flag
                $('#modalTransferValidationTouchedInput').val('0');
                window.__transferValidationTouched = false;
                return true;
            }

            // ✅ Si cambió, recién mandamos al backend
            $('#modalTransferValidationStatusInput').val(v);       // "" | validated | rejected
            $('#modalTransferValidationNotesInput').val(notes);

            // ✅ Si está vacío => no tocar backend (tu regla actual)
            if (!v) {
                $('#modalTransferValidationStatusInput').val('');
                $('#modalTransferValidationNotesInput').val('');
                return true;
            }

            // ✅ Rechazada requiere notas
            if (v === 'rejected' && notes === '') {
                e.preventDefault();
                alert('Para marcar como "Rechazada", debes escribir una observación.');
                return false;
            }

            // ✅ Enviar por AJAX para poder mostrar el mensaje verde sin depender del reload
            e.preventDefault();

            const form = this;
            const fd = new FormData(form);

            // (Opcional) Deshabilitar botón para evitar doble click
            const $btn = $('#rescheduleConfirmBtn');
            if ($btn.length) $btn.prop('disabled', true);

            fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            })
            .then(async (resp) => {
                const ct = (resp.headers.get('content-type') || '').toLowerCase();
                let data = null;

                if (ct.includes('application/json')) {
                    data = await resp.json().catch(() => null);
                }

                if (!resp.ok) {
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'No se pudo guardar.';
                    throw new Error(msg);
                }

                showFlash('success', (data && data.message) ? data.message : 'Cambios guardados correctamente.');

                // ✅ Si tienes modal, ciérralo (si aplica)
                try { $('#appointmentModal').modal('hide'); } catch(e) {}
                try { $('#rescheduleModal').modal('hide'); } catch(e) {}

                // ✅ Recargar para ver tabla actualizada (recomendado)
                window.location.reload();
            })
            .catch((err) => {
                showFlash('danger', err.message || 'Error al guardar.');
            })
            .finally(() => {
                if ($btn.length) $btn.prop('disabled', false);
            });

            return false;
        });

        // ============================
        // ✅ Modal motivo del cambio - handlers
        // ============================
        $(document).on('change', '#changeReasonSelect', function () {
            const v = String($(this).val() || '').trim();

            if (v === 'other') {
                $('#changeReasonOtherWrapper').show();
            } else {
                $('#changeReasonOtherWrapper').hide();
                $('#changeReasonOtherText').val('');
            }
        });

        $(document).on('click', '#btnConfirmChangeReason', function () {
            const reason = String($('#changeReasonSelect').val() || '').trim();
            const otherText = String($('#changeReasonOtherText').val() || '').trim();

            const mandatory = !!window.__changeReasonIsMandatory;

            // Si es obligatorio, exige motivo
            if (mandatory && !reason) {
                alert('Debes seleccionar un motivo para continuar.');
                return;
            }

            // Si eligió "Otro" y es obligatorio, exige texto breve
            if (mandatory && reason === 'other' && !otherText) {
                alert('Por favor, especifica el motivo en “Otro”.');
                return;
            }

            // Guardar a hidden inputs para backend
            $('#modalChangeReasonHidden').val(reason);
            $('#modalChangeReasonOtherHidden').val(reason === 'other' ? otherText : '');

            // Cerrar modal y reintentar submit una sola vez
            window.__changeReasonBypassSubmit = true;
            $('#changeReasonModal').modal('hide');

            // ✅ Guardar lo que se eligió para refrescar UI sin recargar
            window.__pendingRescheduleUi = {
            appointment_id: ctx.appointment_id,
            date: dateStr,          // "YYYY-MM-DD"
            start: sel.start,       // "HH:MM"
            end: sel.end || ''      // "HH:MM"
            };

            // Re-disparar submit
            $('#appointmentStatusForm')[0].submit();

            // ✅ Reset: para que no se quede bypass encendido para futuros submits
            setTimeout(() => { window.__changeReasonBypassSubmit = false; }, 0);
        });

        // Al cerrar el modal (cancelar), limpiamos bypass para que no quede sucio
        $('#changeReasonModal').on('hidden.bs.modal', function () {
            window.__changeReasonBypassSubmit = false;
            $('body').removeClass('change-reason-open');
            $('.modal-backdrop').removeClass('change-reason-backdrop');
            $('#changeReasonModal').css('z-index', '');
            $('.modal-backdrop').css('z-index', '');
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

        $('#appointmentModal').on('hidden.bs.modal', function () {
            // ✅ mata cualquier draft no guardado
            $('#btnSaveChanges').prop('disabled', true);
            window.__apptIsEditMode = false;

            $('body').removeClass('appt-edit-mode appt-quick-transfer-mode transfer-notes-visible transfer-notes-opt transfer-notes-req');

            $('#modalPaymentMethodRaw').val('');
            $('#modalAmountPaidHidden').val('');
            $('#modalPaidAmountInputCard').val('');
            $('#modalPaidAmountInputTransfer').val('');
            $('#modalPaidAmountInputCash').val('');

            $('#modalAmountInput').val('');
            $('#modalAmountInputTransfer').val('');
            $('#modalAmountInputCash').val('');

            $('#modalClientTransactionIdInput').val('');
            $('#modalPaymentPaidAtInput').val('');
            $('#modalClientTransactionIdHidden').val('');
            $('#modalPaymentPaidAtHidden').val('');

            $('#modalCashPaidAtInput').val('');
            $('#modalCashPaidAtHidden').val('');
            $('#modalCashNotesInput').val('');
            $('#modalCashNotesHidden').val('');

            $('#modalCardNotesInput').val('');
            $('#modalTransferNotesInput').val('');
            $('#modalPaymentNotesHidden').val('');

            // ✅ Reset motivo del cambio
            $('#modalChangeReasonHidden').val('');
            $('#modalChangeReasonOtherHidden').val('');
            window.__changeReasonBypassSubmit = false;
            window.__changeReasonIsMandatory = false;

            // ✅ snapshot fuera, para que el siguiente open lo regenere desde BD
            window.__apptModalSnapshot = null;
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

            return {
                pmRaw,

                appointment_status: __norm($('#modalStatusSelect').val()).toLowerCase(),
                payment_status: __norm($('#modalPaymentStatusSelect').val()).toLowerCase(),

                client_transaction_id: __norm($('#modalClientTransactionIdInput').val()),
                payment_paid_at: __norm($('#modalPaymentPaidAtInput').val()),
                payment_notes: __norm(
                    (String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase() === 'cash')
                        ? $('#modalCashNotesInput').val()
                        : (String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase() === 'card')
                            ? $('#modalCardNotesInput').val()
                            : $('#modalTransferNotesInput').val()
                ),
                patient_full_name: __norm($('#modalPatientFullNameInput').val()),
                patient_doc_type: __norm($('#modalDocTypeInput').val()).toLowerCase(),
                patient_doc_number: __norm($('#modalDocNumberInput').val()),
                patient_email: __norm($('#modalEmailInput').val()),
                patient_phone: __norm($('#modalPhoneInput').val()),
                patient_address: __norm($('#modalAddressInput').val()),
                patient_timezone: __norm($('#modalPatientTimezoneInput').val()),
                patient_notes: __norm($('#modalNotesInput').val()),

                billing_name: __norm($('#modalBillingNameInput').val()),
                billing_doc_type: __norm($('#modalBillingDocTypeInput').val()).toLowerCase(),
                billing_doc_number: __norm($('#modalBillingDocNumberInput').val()),
                patient_dob: __norm($('#modalPatientDobInput').val()),
                billing_email: __norm($('#modalBillingEmailInput').val()),
                billing_phone: __norm($('#modalBillingPhoneInput').val()),
                billing_address: __norm($('#modalBillingAddressInput').val()),

                amount: __norm($('#modalAmountInput').val()),
                amount_paid: __norm($('#modalPaidAmountInputCard').val() || $('#modalPaidAmountInputTransfer').val() || $('#modalPaidAmountInputCash').val()),

                transfer_bank_origin: __norm($('#modalTransferBankOriginInput').val()),
                transfer_payer_name: __norm($('#modalTransferPayerNameInput').val()),
                transfer_date: __norm($('#modalTransferDateInput').val()),
                transfer_reference: __norm($('#modalTransferReferenceInput').val()),
                transfer_receipt_file_selected: ($('#modalTransferReceiptFile').val() || '') !== '',

                transfer_validation_status: __norm($('#modalTransferValidationSelect').val()).toLowerCase(),
                transfer_validation_notes: __norm($('#modalTransferValidationNotes').val()),
            };
        }

        function __hasRealChanges() {
            if (!window.__apptModalSnapshot) return false;

            // ✅ Solo habilitar si estás en modo edición
            if (!window.__apptIsEditMode) return false;

            const current = __getCurrentEditableState();
            const snap = window.__apptModalSnapshot;

            const keys = Object.keys(current);
            for (const k of keys) {
                if (__norm(current[k]) !== __norm(snap[k])) return true;
            }
            return false;
        }

        function __hasChangedSinceSnapshot() {
            return __hasRealChanges();
        }

        function __isFilled(sel) {
            const $el = $(sel);
            if (!$el.length) return false;

            const v = String($el.val() ?? '').trim();

            // selects con "Seleccione una opción" suelen quedar ""
            return v !== '';
        }

        function __allFilled(selectors) {
            for (const sel of selectors) {
                if (!__isFilled(sel)) return false;
            }
            return true;
        }

        function __getPaymentMethodNow() {
            // Tu UI usa distintos selects según bloque.
            // Priorizamos el que esté visible.
            const $card = $('#modalPaymentMethodSelectCard:visible');
            const $tr   = $('#modalPaymentMethodSelectTransfer:visible');
            const $cash = $('#modalPaymentMethodSelectCash:visible');

            const v =
                ($card.length ? $card.val() : '') ||
                ($tr.length ? $tr.val() : '') ||
                ($cash.length ? $cash.val() : '');

            return String(v || '').trim().toLowerCase(); // card|transfer|cash
        }

        function __requiredOk() {
            // 1) siempre obligatorios
            const always = [
                '#modalStatusSelect',
                '#modalPaymentStatusSelect',

                // paciente
                '#modalPatientFullNameInput',
                '#modalDocTypeInput',
                '#modalDocNumberInput',
                '#modalPatientDobInput',
                '#modalEmailInput',
                '#modalPhoneInput',
                '#modalAddressInput',
                '#modalPatientTimezoneInput',

                // billing
                '#modalBillingNameInput',
                '#modalBillingDocTypeInput',
                '#modalBillingDocNumberInput',
                '#modalBillingEmailInput',
                '#modalBillingPhoneInput',
                '#modalBillingAddressInput',
            ];

            if (!__allFilled(always)) return false;

            // 2) según método de pago
            const pm = __getPaymentMethodNow();

            if (pm === 'card') {
                const cardReq = [
                    '#modalPaymentMethodSelectCard',
                    '#modalPaymentStatusSelectCard', // ✅ NUEVO: Estado del pago (sección info de pago)
                    '#modalAmountInput',
                    '#modalPaidAmountInputCard',
                    '#modalPaymentPaidAtInput',
                    // ✅ client_transaction_id NO obligatorio
                    // ✅ patient_notes NO obligatorio
                ];
                return __allFilled(cardReq);
            }

            if (pm === 'transfer') {
                const trReq = [
                    '#modalPaymentMethodSelectTransfer',
                    '#modalAmountInputTransfer',
                    '#modalPaidAmountInputTransfer',
                    '#modalTransferBankOriginInput',
                    '#modalTransferPayerNameInput',
                    '#modalTransferDateInput',
                ];
                return __allFilled(trReq);
            }

            if (pm === 'cash') {
                const cashReq = [
                    '#modalPaymentMethodSelectCash',
                    '#modalAmountInputCash',
                    '#modalPaidAmountInputCash',
                    '#modalCashPaidAtInput',
                    // cash notes lo puedes dejar opcional
                ];
                return __allFilled(cashReq);
            }

            // Si no hay método seleccionado, no habilites
            return false;
        }

        function __updateSaveButtonState() {
            if (!window.__apptIsEditMode) return;

            const hasChanges = __hasRealChanges();
            const requiredOk = __requiredOk();

            $('#btnSaveChanges').prop('disabled', !(hasChanges && requiredOk));
        }

        function __applyAsteriskToLabel(inputSelector) {
            const $input = $(inputSelector);
            if (!$input.length) return;

            const $wrap = $input.closest('.mb-2, .mb-0, .col-md-6, .col-md-12');
            const $label = $wrap.find('.small.text-muted').first();
            if (!$label.length) return;

            // evita duplicados
            if ($label.find('.js-req-asterisk').length) return;

            $label.append(' <span class="js-req-asterisk text-danger">*</span>');
        }

        function __removeAllAsterisks() {
            $('.js-req-asterisk').remove();
        }

        function __toggleRequiredAsterisks(show) {
            __removeAllAsterisks();
            if (!show) return;

            // mismos obligatorios que validas
            const pm = __getPaymentMethodNow();

            const always = [
                '#modalStatusSelect',
                '#modalPaymentStatusSelect',
                '#modalPatientFullNameInput',
                '#modalDocTypeInput',
                '#modalDocNumberInput',
                '#modalPatientDobInput',
                '#modalEmailInput',
                '#modalPhoneInput',
                '#modalAddressInput',
                '#modalPatientTimezoneInput',
                '#modalBillingNameInput',
                '#modalBillingDocTypeInput',
                '#modalBillingDocNumberInput',
                '#modalBillingEmailInput',
                '#modalBillingPhoneInput',
                '#modalBillingAddressInput',
            ];

            always.forEach(__applyAsteriskToLabel);

            if (pm === 'card') {
                ['#modalPaymentMethodSelectCard', '#modalPaymentStatusSelectCard', '#modalAmountInput', '#modalPaidAmountInputCard', '#modalPaymentPaidAtInput']
                    .forEach(__applyAsteriskToLabel);
            } else if (pm === 'transfer') {
                ['#modalPaymentMethodSelectTransfer', '#modalAmountInputTransfer', '#modalPaidAmountInputTransfer',
                '#modalTransferBankOriginInput', '#modalTransferPayerNameInput', '#modalTransferDateInput'
                ].forEach(__applyAsteriskToLabel);
            } else if (pm === 'cash') {
                ['#modalPaymentMethodSelectCash', '#modalAmountInputCash', '#modalPaidAmountInputCash', '#modalCashPaidAtInput']
                    .forEach(__applyAsteriskToLabel);
            }
        }

        function __setSnapshotFromCurrent() {
            // Guardar estado inicial (lo que vino de BD y ya pintaste en el modal)
            window.__apptModalSnapshot = __getCurrentEditableState();
            __updateSaveButtonState(); // normalmente lo deja disabled
        }

        // ✅ NUEVO: restaurar inputs + hiddens desde snapshot (lo que vino de BD al abrir)
        function __restoreFromSnapshot(snap) {
            if (!snap) return;

            // Método
            $('#modalPaymentMethodRaw').val(String(snap.pmRaw || '').toLowerCase());

            // Status / payment status (selects)
            $('#modalStatusSelect').val(String(snap.appointment_status || ''));
            $('#modalPaymentStatusSelect').val(String(snap.payment_status || ''));
            $('#modalPaymentStatusSelectCard').val(String(snap.payment_status || ''));

            // Hiddens relacionados
            $('#modalStatusHidden').val(String(snap.appointment_status || ''));
            $('#modalPaymentStatusHidden').val(String(snap.payment_status || ''));

            // Tarjeta
            $('#modalClientTransactionIdInput').val(String(snap.client_transaction_id || ''));
            $('#modalPaymentPaidAtInput').val(String(snap.payment_paid_at || ''));
            $('#modalClientTransactionIdHidden').val(String(snap.client_transaction_id || ''));
            $('#modalPaymentPaidAtHidden').val(String(snap.payment_paid_at || ''));

            // Montos (restaura exactamente lo que había)
            __syncAmountAll(String(snap.amount || ''));
            __syncPaidAmountAll(String(snap.amount_paid || ''));

            // Transfer (campos)
            $('#modalTransferBankOriginInput').val(String(snap.transfer_bank_origin || ''));
            $('#modalTransferPayerNameInput').val(String(snap.transfer_payer_name || ''));
            $('#modalTransferDateInput').val(String(snap.transfer_date || ''));
            $('#modalTransferReferenceInput').val(String(snap.transfer_reference || ''));

            // Transfer (validación)
            $('#modalTransferValidationSelect').val(String(snap.transfer_validation_status || ''));
            $('#modalTransferValidationSelect').data('prev', String(snap.transfer_validation_status || '').toLowerCase());
            $('#modalTransferValidationNotes').val(String(snap.transfer_validation_notes || ''));
            $('#modalTransferValidationStatusInput').val(String(snap.transfer_validation_status || ''));
            $('#modalTransferValidationNotesInput').val(String(snap.transfer_validation_notes || ''));

            // ✅ REPINTAR MODO LECTURA: Estado de validación + texto de observaciones
            (function () {
                const vStatus = String(snap.transfer_validation_status || '').trim().toLowerCase();

                const labels = {
                    '': 'Sin revisar',
                    'validated': 'Validada',
                    'rejected': 'Rechazada'
                };

                // Estado (modo lectura)
                $('#modalTransferValidationText').text(labels[vStatus] ?? 'Sin revisar');

                // Observaciones (modo lectura)
                const notes = String(snap.transfer_validation_notes || '').trim();
                if (notes !== '') {
                    $('#modalTransferValidationNotesText').text(notes);
                } else {
                    $('#modalTransferValidationNotesText').html('<span class="text-muted font-italic small">N/A</span>');
                }
            })();

            const __v = String(snap.transfer_validation_status || '').trim().toLowerCase();

            if (__v === 'validated' || __v === 'rejected') {
                $('#transferValidationNotesWrapper').show();

                if (__v === 'rejected') {
                    $('#transferNotesRequired').show();
                    $('#transferNotesOptional').hide();
                } else {
                    $('#transferNotesRequired').hide();
                    $('#transferNotesOptional').show();
                }
            } else {
                $('#transferValidationNotesWrapper').hide();
                $('#transferNotesRequired').hide();
                $('#transferNotesOptional').hide();
            }

            // Cash
            $('#modalCashNotesInput').val(String(snap.payment_notes || ''));
            $('#modalPaymentNotesHidden').val(String(snap.payment_notes || ''));

            // Monto pagado hidden
            $('#modalAmountPaidHidden').val(String(snap.amount_paid || ''));

            // ✅ Re-pintar badges (arriba + tarjeta) según el payment_status restaurado
            const ps = String(snap.payment_status || '').toLowerCase();
            $('#modalPaymentStatusBadge').html(paymentStatusBadge(ps));
            $('#modalPaymentStatusBadge2').html(paymentStatusBadge(ps));

            // ✅ Re-pintar badge del ESTADO DE LA CITA (modo lectura) según snapshot
            (function () {
                const st = String(snap.appointment_status || '').trim().toLowerCase();

                const statusColors = {
                    pending_payment: '#f39c12',
                    processing: '#3498db',
                    paid: '#2ecc71',
                    confirmed: '#3498db',
                    completed: '#008000',
                    canceled: '#ff0000',
                    cancelled: '#ff0000',
                    rescheduled: '#f1c40f',
                    no_show: '#e67e22',
                    on_hold: '#95a5a6',
                    pending_verification: '#7f8c8d',
                };

                const statusLabels = {
                    pending_payment: 'Pendiente de pago',
                    processing: 'Procesando',
                    paid: 'Pagada',
                    confirmed: 'Confirmada',
                    completed: 'Completada',
                    canceled: 'Cancelada',
                    cancelled: 'Cancelada',
                    rescheduled: 'Reagendada',
                    no_show: 'No asistió',
                    on_hold: 'En espera',
                    pending_verification: 'Pendiente de verificación',
                };

                const c = statusColors[st] || '#7f8c8d';
                const l = statusLabels[st] || 'Estado desconocido';

                const html = `<span class="badge px-2 py-1" style="background-color:${c}; color:white;">${l}</span>`;
                $('#modalStatusBadge').html(html);
                $('#modalStatusBadgeLegacy').html(html);
            })();
        }

        // ✅ Recalcular cuando el admin cambia select o escribe notas
        $(document).on('change', '#modalTransferValidationSelect', function () {
            __updateSaveButtonState();
        });

        $(document).on('input', '#modalTransferValidationNotes', function () {
            window.__transferValidationTouched = true;
            $('#modalTransferValidationTouchedInput').val('1');
            __updateSaveButtonState();
        });

        $(document).on('input change', '#modalPatientFullNameInput,#modalDocTypeInput,#modalPatientDobInput,#modalDocNumberInput,#modalEmailInput,#modalPhoneInput,#modalAddressInput,#modalPatientTimezoneInput,#modalNotesInput,#modalBillingNameInput,#modalBillingDocTypeInput,#modalBillingDocNumberInput,#modalBillingEmailInput,#modalBillingPhoneInput,#modalBillingAddressInput,#modalAmountInput,#modalTransferBankOriginInput,#modalTransferPayerNameInput,#modalTransferDateInput,#modalTransferReferenceInput,#modalStatusSelect,#modalPaymentStatusSelect,#modalTransferReceiptFile,#modalAmountInputCash,#modalCashPaidAtInput,#modalCashNotesInput,#modalPaymentMethodSelectCash,#modalClientTransactionIdInput,#modalPaymentPaidAtInput,#modalPaymentStatusSelectCard,#modalPaidAmountInputCard,#modalPaidAmountInputTransfer,#modalPaidAmountInputCash', function () {
            __updateSaveButtonState();
        });

        $(document).on('input', '#modalCashNotesInput', function () {
            $('#modalCashNotesHidden').val(String($(this).val() || ''));
            __updateSaveButtonState();
        });

        // ✅ Al cerrar modal, limpiar snapshot y deshabilitar el botón
        $('#appointmentModal').on('hidden.bs.modal', function () {
            window.__apptModalSnapshot = null;
            $('#btnSaveChanges').prop('disabled', true);
        });

        function __applyPendingRescheduleUi() {
            const p = window.__pendingRescheduleUi;
            if (!p || !p.appointment_id) return;

            // 1) Actualizar los data-* del botón "Ver detalles" de esa cita
            const $btn = $(`.view-appointment-btn[data-id="${p.appointment_id}"]`);
            if ($btn.length) {

                // ✅ 1) Actualiza atributos HTML
                $btn.attr('data-date', p.date);
                $btn.attr('data-start-time', p.start);
                $btn.attr('data-end-time', p.end);
                $btn.attr('data-start', `${p.date} ${p.start}`);

                // ✅ 2) MATA el caché de jQuery .data() (clave para que el modal lea lo nuevo)
                $btn.removeData('date');
                $btn.removeData('startTime');  // data-start-time => startTime
                $btn.removeData('endTime');    // data-end-time   => endTime
                $btn.removeData('start');

                // ✅ 3) (Opcional pero recomendado) setea también el cache nuevo
                $btn.data('date', p.date);
                $btn.data('startTime', p.start);
                $btn.data('endTime', p.end);
                $btn.data('start', `${p.date} ${p.start}`);
            }

            // 2) Actualizar la fila de la tabla (recomendado: agregar clases/ids, ver paso 3)
            const $row = $btn.closest('tr');
            if ($row.length) {
                // OJO: aquí depende de tu estructura exacta de columnas.
                // Te dejo la forma robusta (ver paso 3 para poner clases y que sea exacto).
                const dateTxt = new Intl.DateTimeFormat('es-EC', { day:'2-digit', month:'short', year:'numeric' })
                .format(new Date(p.date + "T00:00:00"));

                const to12h = (hhmm) => {
                const [hh, mm] = String(hhmm).slice(0,5).split(':').map(Number);
                const t = new Date(2000,0,1,hh,mm,0);
                return new Intl.DateTimeFormat('en-US',{hour:'numeric',minute:'2-digit',hour12:true}).format(t);
                };

                const timeTxt = `${to12h(p.start)} - ${to12h(p.end)}`;

                // Si NO tienes clases en los <td>, esto puede variar.
                // Mejor aplica el paso 3 abajo para hacerlo exacto.
                $row.find('td.appt-date').text(dateTxt);
                $row.find('td.appt-time').text(timeTxt);
            }

            // 3) Si el modal está abierto ahora mismo, refrescar lo que muestra también
            // (muchas veces tu modal usa #modalDateTime / #modalDateTime2, etc.)
            // Si esos IDs existen en tu modal, actualízalos aquí:
            if ($('#appointmentModal').hasClass('show')) {
                const dateTxt = new Intl.DateTimeFormat('es-EC', { day:'2-digit', month:'short', year:'numeric' })
                .format(new Date(p.date + "T00:00:00"));

                const to12h = (hhmm) => {
                const [hh, mm] = String(hhmm).slice(0,5).split(':').map(Number);
                const t = new Date(2000,0,1,hh,mm,0);
                return new Intl.DateTimeFormat('en-US',{hour:'numeric',minute:'2-digit',hour12:true}).format(t);
                };

                $('#modalDateTime').text(dateTxt); // si tu modal usa ese id
                $('#modalDateTime2').text(`${to12h(p.start)} - ${to12h(p.end)}`); // si tu modal usa ese id
            }

            // limpiar
            window.__pendingRescheduleUi = null;
            }
    </script>

    <script>
        // ============================
        // ✅ UI: Modo lectura / Modo edición (solo UI por ahora)
        // ============================
        window.__apptIsEditMode = false;

        function __setQuickValidateVisibility(pm) {
            const p = String(pm || '').trim().toLowerCase();
            $('#btnQuickValidateTransfer').toggle(p === 'transfer');
        }

        function __applyQuickTransferLock(active) {
            const $body = $('body');

            if (active) {
                $body.addClass('appt-quick-transfer-mode');

                // ✅ Deshabilitar todos los controles del body del modal (pero NO hidden)
                $('#appointmentModal .modal-body :input')
                    .not('input[type="hidden"]')
                    .prop('disabled', true);

                // ✅ Re-habilitar solo validación
                $('#modalTransferValidationSelect').prop('disabled', false);
                $('#modalTransferValidationNotes').prop('disabled', false);

            } else {
                $body.removeClass('appt-quick-transfer-mode');

                // ✅ Re-habilitar todo (el método de pago UI ya vuelve a setear disabled/enabled por bloque)
                $('#appointmentModal .modal-body :input')
                    .not('input[type="hidden"]')
                    .prop('disabled', false);

                // ✅ Re-aplicar la lógica normal de bloques (card/transfer/cash)
                if (window.__setPaymentMethodUI) {
                    window.__setPaymentMethodUI($('#modalPaymentMethodRaw').val());
                }
            }
        }

        function __enterEditModeUI(mode = 'edit') {
            window.__apptIsEditMode = true;

            $('body').addClass('appt-edit-mode');

            $('#editModeBanner').show();

            const currentStatus = String($('#modalStatusHidden').val() || '').trim().toLowerCase();
            if (currentStatus === 'rescheduled') {
                $('#modalStatusSelect').prop('disabled', true);
            } else {
                $('#modalStatusSelect').prop('disabled', false);
            }

            $('#btnCancelEditMode').show();
            $('#apptModeBadge').show();

            if (mode === 'quick_transfer') {
                // Banner corto
                $('#editModeBannerLong').hide();
                $('#editModeBannerShort').show();
                $('#editModeBannerRightHint').hide();
            } else {
                // Banner normal (largo)
                $('#editModeBannerLong').show();
                $('#editModeBannerShort').hide();
                $('#editModeBannerRightHint').show();
            }
            __setPaymentMethodUI($('#modalPaymentMethodRaw').val());

            __toggleRequiredAsterisks(true);
            __applyQuickTransferLock(mode === 'quick_transfer');

            // ✅ Asegurar snapshot (si por alguna razón no existe todavía)
            if (!window.__apptModalSnapshot) {
                __setSnapshotFromCurrent();
            }

            // ✅ Refrescar estado del botón al entrar a edición
            __updateSaveButtonState();

            // ✅ Si ya viene "Rechazada" o "Validada", mostrar cajón de observaciones al entrar a edición
            setTimeout(function () {
                const pm = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
                if (pm === 'transfer') {
                    $('#modalTransferValidationSelect').trigger('change');
                }
            }, 0);

            // ✅ Inicializar snapshot al entrar en modo edición
            __setSnapshotFromCurrent();

            // ✅ Evaluar botón inmediatamente
            __updateSaveButtonState();
        }

        function __exitEditModeUI() {
            __toggleRequiredAsterisks(false);
            // ✅ Antes de salir, restaurar el estado original (snapshot) para evitar NA/valores raros
            if (window.__apptModalSnapshot) {
                __restoreFromSnapshot(window.__apptModalSnapshot);
                __setPaymentMethodUI(window.__apptModalSnapshot.pmRaw); // vuelve al método real de BD
                __updateSaveButtonState(); // normalmente queda disabled
            }

            window.__apptIsEditMode = false;

            $('body').removeClass('appt-edit-mode');

            $('#editModeBanner').hide();
            $('#btnCancelEditMode').hide();
            $('#apptModeBadge').hide();

            // Reset banner a modo normal
            $('#editModeBannerLong').show();
            $('#editModeBannerShort').hide();
            $('#editModeBannerRightHint').show();

            __applyQuickTransferLock(false);
        }

        // Click: Acciones -> Editar
        $(document).on('click', '#btnEnterEditMode', function () {
            __enterEditModeUI();
            $('#apptActionsDropdown').dropdown('hide');
        });

        // Click: Acciones -> Solo validar transferencia (atajo)
        $(document).on('click', '#btnQuickValidateTransfer', function () {
            __enterEditModeUI('quick_transfer');
            $('#apptActionsDropdown').dropdown('hide');

            const pmRaw = String($('#modalPaymentMethodRaw').val() || '').trim().toLowerCase();
            if (pmRaw !== 'transfer') {
                alert('Esta cita no es por transferencia. No hay validación de transferencia para revisar.');
                return;
            }

            // Scroll suave a la sección de validación
            setTimeout(function () {
                const el = document.getElementById('transferValidationSection');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 150);
        });

        // Click: Cancelar edición
        $(document).on('click', '#btnCancelEditMode', function () {
            __exitEditModeUI();
        });

        function __toggleReminder3hActionByTime($btn) {
            const $action = $('#btnSendReminder3h');
            if (!$action.length) return;

            const dateStr = String($btn.attr('data-date') || '').trim();        // YYYY-MM-DD
            const timeStr = String($btn.attr('data-start-time') || '').trim(); // HH:MM o HH:MM:SS

            if (!dateStr || !timeStr) {
                $action.addClass('d-none');
                return;
            }

            const hhmm = timeStr.slice(0, 5);

            const [y, m, d] = dateStr.split('-').map(Number);
            const [hh, mm] = hhmm.split(':').map(Number);

            if (!y || !m || !d || isNaN(hh) || isNaN(mm)) {
                $action.addClass('d-none');
                return;
            }

            const apptDt = new Date(y, m - 1, d, hh, mm, 0, 0);
            const now = new Date();

            const diffMs = apptDt.getTime() - now.getTime();
            const diffHours = diffMs / (1000 * 60 * 60);

            // ✅ regla: futura y ≤ 3 horas
            const shouldShow = diffHours >= 0 && diffHours <= 3;

            $action.toggleClass('d-none', !shouldShow);
        }

        // Al abrir modal: siempre volver a solo lectura
        $(document).on('click', '.view-appointment-btn', function() {
            __exitEditModeUI();
            __toggleReminder3hActionByTime($(this)); // ✅ NUEVO: mostrar/ocultar acción 3h
        });

        // Al cerrar modal: resetear
        $('#appointmentModal').on('hidden.bs.modal', function () {
            __exitEditModeUI();
        });

        // ============================
        // ✅ Reagendar: abrir wizard + preparar UI
        // ============================
        window.__rescheduleSelected = null;

        // ============================
        // ✅ HOLDs para Reagendar (appointment_holds)
        // ============================
        window.__rescheduleHoldId = null;

        function __csrfToken() {
            return $('meta[name="csrf-token"]').attr('content') || '';
        }

        function __formatRescheduleSummary(dateStr, startHHMM, endHHMM) {
            // dateStr: "2026-01-27"
            // startHHMM/endHHMM: "09:15" / "09:25"

            const [y, m, d] = dateStr.split('-').map(Number);
            const dt = new Date(y, m - 1, d);

            // "10 ene 2026" (en Ecuador, español)
            const datePart = new Intl.DateTimeFormat('es-EC', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            }).format(dt);

            const to12h = (hhmm) => {
                const [hh, mm] = hhmm.split(':').map(Number);
                const t = new Date(2000, 0, 1, hh, mm, 0);
                return new Intl.DateTimeFormat('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
                }).format(t); // e.g. "3:30 PM"
            };

            return `${datePart} ${to12h(startHHMM)} - ${to12h(endHHMM)}`;
        }

        // Libera el hold actual (si existe)
        async function __releaseRescheduleHold() {
            if (!window.__rescheduleHoldId) return;

            try {
                await fetch('/appointment-holds/release', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': __csrfToken()
                    },
                    body: JSON.stringify({ hold_id: window.__rescheduleHoldId })
                });
            } catch (e) {
                console.warn('Release hold failed:', e);
            } finally {
                window.__rescheduleHoldId = null;
            }
        }

        function __formatRescheduleSummary(dateStr, startHHMM, endHHMM) {
            const [y, m, d] = dateStr.split('-').map(Number);
            const dt = new Date(y, m - 1, d);

            // "10 ene 2026"
            const datePart = new Intl.DateTimeFormat('es-EC', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            }).format(dt);

            const to12h = (hhmm) => {
                const [hh, mm] = hhmm.split(':').map(Number);
                const t = new Date(2000, 0, 1, hh, mm);
                return new Intl.DateTimeFormat('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }).format(t);
            };

            if (!startHHMM) return datePart;

            return `${datePart} ${to12h(startHHMM)} - ${to12h(endHHMM)}`;
        }

        // Crea un hold en BD al seleccionar turno
        async function __createRescheduleHold(ctx, dateStr, start, end) {
            // ctx debe traer appointment_id y employee_id (tú ya lo usas arriba)
            const payload = {
                appointment_id: ctx.appointment_id,
                employee_id: ctx.employee_id,
                appointment_date: dateStr,
                appointment_time: start,
                appointment_end_time: end,
                is_admin: true
            };

            const res = await fetch('/appointment-holds', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': __csrfToken()
                },
                body: JSON.stringify(payload)
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const data = await res.json();

            // Esperado: { hold_id: 123 } (ajústalo si tu backend devuelve otro nombre)
            if (!data || !data.hold_id) throw new Error('No hold_id in response');

            window.__rescheduleHoldId = data.hold_id;
        }

        // URL para cargar horas disponibles (ajústala a tu ruta real si ya la tienes)
        window.__RESCHEDULE_SLOTS_URL = window.__RESCHEDULE_SLOTS_URL || '/appointments/reschedule/slots';

        function __rescheduleResetWizard() {
            window.__rescheduleSelectedDate = null;
            window.__rescheduleSelectedSlot = null;

            // Step 1 visible, Step 2 oculto
            $('#rescheduleStep1').removeClass('d-none');
            $('#rescheduleStep2').addClass('d-none');

            // Botones
            $('#rescheduleBackBtn').prop('disabled', true);
            $('#rescheduleNextBtn').prop('disabled', true).removeClass('d-none');
            $('#rescheduleConfirmBtn').addClass('d-none');

            // Inputs
            $('#rescheduleDateInput').val('');
            $('#rescheduleReasonSelect').val('');
            $('#rescheduleReasonOtherInput').val('');
            $('#rescheduleReasonOtherWrap').addClass('d-none');

            // Slots UI
            $('#rescheduleSlots').empty();
            $('#rescheduleSlotsHint').show();
            $('#rescheduleSlotsError').addClass('d-none').text('');
        }

        function formatDateES(dateStr) {
            if (!dateStr) return 'N/A';

            const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

            const parts = dateStr.split('-'); // YYYY-MM-DD
            if (parts.length !== 3) return dateStr;

            const year = parts[0];
            const monthIndex = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);

            return `${day} ${months[monthIndex]} ${year}`;
        }

        function capitalizeFirstLetter(text) {
            if (!text) return 'N/A';
            text = String(text).trim();
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        function validateRescheduleStep1() {
            const date = String(window.__rescheduleSelectedDate || $('#rescheduleDateInput').val() || '').trim();
            const slotSelected = window.__rescheduleSelectedSlot && window.__rescheduleSelectedSlot.start;
            const reason = String($('#rescheduleReasonSelect').val() || '').trim();

            const isValid = date && slotSelected && reason;

            $('#rescheduleNextBtn').prop('disabled', !isValid);
        }

        function __renderRescheduleSlots(slots) {
            $('#rescheduleSlots').empty();

            if (!Array.isArray(slots) || slots.length === 0) {
                $('#rescheduleSlotsHint').show().text('No hay horas disponibles para esa fecha.');
                return;
            }

            $('#rescheduleSlotsHint').hide();

            slots.forEach(function (s) {
                const start = String(s.start || '').trim();
                const end = String(s.end || '').trim();
                const label = String(
                    s.label || s.display || (start && end ? (start + ' - ' + end) : start)
                ).trim();

                const $btn = $(`
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2 js-reschedule-slot"
                            data-start="${start}" data-end="${end}">
                        ${label}
                    </button>
                `);

                $('#rescheduleSlots').append($btn);
            });
        }

        async function __loadRescheduleSlots(employeeId, dateStr) {
            // UI reset
            $('#rescheduleSlotsError').addClass('d-none').text('');
            $('#rescheduleSlotsHint').show().text('Cargando horas disponibles...');
            $('#rescheduleSlots').empty();

            const base = String(window.__RESCHEDULE_SLOTS_URL || '').replace(/\/+$/,''); // sin slash final
            const url = `${base}/${encodeURIComponent(employeeId)}/${encodeURIComponent(dateStr)}`;

            try {
                const res = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();

                // Espera: { slots: [ {start,end,label}, ... ] } o directamente [ ... ]
                const slots = Array.isArray(data)
                    ? data
                    : (data.slots || data.available_slots || []);

                __renderRescheduleSlots(slots);

            } catch (e) {
                console.error('Reschedule slots error:', e);
                $('#rescheduleSlotsHint').hide();
                $('#rescheduleSlotsError').removeClass('d-none').text('No se pudieron cargar las horas disponibles.');
            }
        }

        // Click: Acciones -> Reagendar
        $(document).on('click', '#btnReagendar', function () {
            $('#apptActionsDropdown').dropdown('hide');

            const ctx = window.__rescheduleContext || null;
            if (!ctx || !ctx.appointment_id || !ctx.employee_id) {
                alert('No se pudo cargar el contexto para reagendar. Abre la cita de nuevo con “Ver detalles”.');
                return;
            }

            __rescheduleResetWizard();

            // Textos
            $('#rescheduleEmployeeText').text(ctx.employee_text || 'N/A');
            $('#rescheduleAreaText').text(ctx.area_text || 'N/A');
            $('#rescheduleServiceText').text(ctx.service_text || 'N/A');
            $('#rescheduleModeText').text(capitalizeFirstLetter(ctx.appointment_mode)); 

            const formattedDate = formatDateES(ctx.old_date);

            function toAmPmSafe(t) {
                if (!t) return '';
                const s = String(t).trim();
                const hhmm = s.includes(':') ? s.slice(0,5) : s; // "15:30:00" -> "15:30"
                const [hh, mm] = hhmm.split(':');
                const h = parseInt(hh, 10);
                const m = parseInt(mm, 10);
                if (Number.isNaN(h) || Number.isNaN(m)) return hhmm;

                const ampm = h >= 12 ? 'PM' : 'AM';
                let h12 = h % 12;
                if (h12 === 0) h12 = 12;

                // sin cero a la izquierda, tipo 3:30 PM
                return `${h12}:${String(m).padStart(2,'0')} ${ampm}`;
            }

            const beforeTxt = (ctx.old_date && ctx.old_start_time)
                ? `${formattedDate} ${toAmPmSafe(ctx.old_start_time)}${ctx.old_end_time ? (' - ' + toAmPmSafe(ctx.old_end_time)) : ''}`
                : 'N/A';

            $('#rescheduleOldText').text('Antes: ' + beforeTxt);
            $('#rescheduleConfirmBefore').text(beforeTxt);
            $('#rescheduleConfirmAfter').text('N/A');

            // Abrir wizard encima sin glitches: cerrar el modal de detalles primero
            $('#appointmentModal').modal('hide');

            // Cuando cierre, abrir el wizard
            $('#appointmentModal').one('hidden.bs.modal', function () {
                $('#rescheduleWizardModal').modal('show');
            });
        });

        // Mostrar/ocultar “Otro” motivo
        $(document).on('change', '#rescheduleReasonSelect', function () {
            const v = String($(this).val() || '').trim();
            if (v === 'other') {
                $('#rescheduleReasonOtherWrap').removeClass('d-none');
            } else {
                $('#rescheduleReasonOtherWrap').addClass('d-none');
                $('#rescheduleReasonOtherInput').val('');
            }

            validateRescheduleStep1();
        });

        // Al elegir fecha => cargar slots
        $(document).on('change', '#rescheduleDateInput', async function () {
            const ctx = window.__rescheduleContext || null;
            const dateStr = String($(this).val() || '').trim();

            // Reset selección
            window.__rescheduleSelectedSlot = null;
            validateRescheduleStep1();

            // ✅ Si ya había un hold tomado por un turno anterior, lo liberamos
            await __releaseRescheduleHold();

            // ✅ reset selección de slot
            window.__rescheduleSelectedSlot = null;

            validateRescheduleStep1();

            if (!ctx || !ctx.employee_id || !dateStr) return;

            await __loadRescheduleSlots(ctx.employee_id, dateStr);
        });

        // Click en un slot => seleccionar
        $(document).on('click', '.js-reschedule-slot', async function () {
            $('.js-reschedule-slot').removeClass('active');
            $(this).addClass('active');

            const start = String($(this).data('start') || '').trim();
            const end = String($(this).data('end') || '').trim();

            window.__rescheduleSelectedSlot = { start, end };

            // ✅ si por alguna razón el input de fecha no está seteado, no sigas
            const dateStrNow = String($('#rescheduleDateInput').val() || '').trim();
            if (!dateStrNow) {
                alert('Primero selecciona una fecha.');
                window.__rescheduleSelectedSlot = null;
                $(this).removeClass('active');
                return;
            }

            // ✅ Crear HOLD en BD al seleccionar turno
            try {
                const ctx = window.__rescheduleContext || null;
                const dateStr = __getRescheduleDateStr();

                if (!ctx || !ctx.appointment_id || !ctx.employee_id || !dateStr || !start) {
                    throw new Error('Missing reschedule context/date/slot');
                }

                // Si ya había un hold anterior (porque cambió de turno), liberarlo primero
                await __releaseRescheduleHold();

                // Crear el nuevo hold
                await __createRescheduleHold(ctx, dateStr, start, end);

            } catch (e) {
                console.error('Hold create error:', e);
                alert('No se pudo reservar ese turno. Intenta con otro horario.');

                // UI revert
                $(this).removeClass('active');
                window.__rescheduleSelectedSlot = null;
                validateRescheduleStep1();
                return;
            }

            // Habilitar siguiente
           validateRescheduleStep1();

            // Preview “Nuevo”
            const dateStr = String(window.__rescheduleSelectedDate || $('#rescheduleDateInput').val() || '').trim();

            // ✅ sincroniza el input si por alguna razón está vacío
            if (dateStr && !String($('#rescheduleDateInput').val() || '').trim()) {
                $('#rescheduleDateInput').val(dateStr);
            }

            const afterTxt = (dateStr && start && end)
                ? __formatRescheduleSummary(dateStr, start, end)
                : 'N/A';

            $('#rescheduleConfirmAfter').text(afterTxt);
        });

        // Botón Atrás
        $(document).on('click', '#rescheduleBackBtn', function () {
            $('#rescheduleStep2').addClass('d-none');
            $('#rescheduleStep1').removeClass('d-none');

            $('#rescheduleBackBtn').prop('disabled', true);
            $('#rescheduleNextBtn').removeClass('d-none');
            $('#rescheduleConfirmBtn').addClass('d-none');
        });

        function __getRescheduleDateStr() {
            const d = String(window.__rescheduleSelectedDate || $('#rescheduleDateInput').val() || '').trim();

            // Mantener el input sincronizado si vino del calendario
            if (d && !String($('#rescheduleDateInput').val() || '').trim()) {
                $('#rescheduleDateInput').val(d);
            }

            return d;
        }

        // Botón Siguiente (paso 1 => paso 2)
        $(document).on('click', '#rescheduleNextBtn', function () {
            const dateStr = __getRescheduleDateStr();
            const sel = window.__rescheduleSelectedSlot;

            if (!dateStr || !sel || !sel.start) return;

            $('#rescheduleStep1').addClass('d-none');
            $('#rescheduleStep2').removeClass('d-none');

            $('#rescheduleBackBtn').prop('disabled', false);
            $('#rescheduleNextBtn').addClass('d-none');
            $('#rescheduleConfirmBtn').removeClass('d-none');
        });

        // Confirmar reagendamiento => set hidden inputs + submit
        $(document).on('click', '#rescheduleConfirmBtn', function () {
            const ctx = window.__rescheduleContext || null;
            const dateStr = __getRescheduleDateStr();
            const sel = window.__rescheduleSelectedSlot;

            if (!ctx || !ctx.appointment_id || !dateStr || !sel || !sel.start) return;

            $('#rescheduleDateInput').val(dateStr);

            const reason = String($('#rescheduleReasonSelect').val() || '').trim();
            const reasonOther = String($('#rescheduleReasonOtherInput').val() || '').trim();

            // ✅ Hidden inputs (backend)
            $('#modalAppointmentId').val(ctx.appointment_id);

            $('#rescheduleDateHidden').val(dateStr);
            $('#rescheduleTimeHidden').val(sel.start);
            $('#rescheduleEndTimeHidden').val(sel.end || '');

            $('#rescheduleReasonHidden').val(reason);
            $('#rescheduleReasonOtherHidden').val(reason === 'other' ? reasonOther : '');

            // Opcional: marcar estado como rescheduled si tu backend lo espera
            $('#modalStatusHidden').val('rescheduled');

            // Reagendar no debería tocar pagos
            $('#modalAmountPaidHidden').val('');
            $('#modalPaymentPaidAtHidden').val('');
            $('#modalClientTransactionIdHidden').val('');

            $('#modalAmountPaidHidden').val(String($('#modalAmountPaidHidden').val() || '0').trim());

            // ✅ Guardar payload para actualizar UI sin recargar (tabla + botón + modal)
            window.__pendingRescheduleUi = {
                appointment_id: ctx.appointment_id,
                date: dateStr,            // "YYYY-MM-DD"
                start: sel.start,         // "HH:MM"
                end: sel.end || ''        // "HH:MM"
            };

            // Cerrar wizard y enviar
            $('#rescheduleWizardModal').modal('hide');

            // ✅ Reagendar NO debe tocar nada de pago (evita que se sobreescriba con "" / null / 0)
            $('#modalPaymentMethodHidden').prop('disabled', true);
            $('#modalPaymentStatusHidden').prop('disabled', true);
            $('#modalAmountPaidHidden').prop('disabled', true);
            $('#modalPaymentPaidAtHidden').prop('disabled', true);
            $('#modalClientTransactionIdHidden').prop('disabled', true);
            $('#modalPaymentNotesHidden').prop('disabled', true);

            // ✅ Enviar por AJAX para mostrar flash sin recargar
            (async () => {
                const form = document.getElementById('appointmentStatusForm');
                const fd = new FormData(form);

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': __csrfToken(),
                        },
                        body: fd,
                    });

                    const data = await res.json().catch(() => null);

                    if (res.ok && data && data.success) {
                        showFlash('success', data.message || 'Cambios guardados correctamente.');

                        // ✅ APLICAR CAMBIO DE FECHA/HORA EN UI SIN RECARGAR
                        if (typeof __applyPendingRescheduleUi === 'function') {
                            __applyPendingRescheduleUi();
                        }
                    } else {
                        showFlash(
                            'danger',
                            (data && data.message)
                                ? data.message
                                : ('No se pudo reagendar (HTTP ' + res.status + ').')
                        );
                    }
                } catch (e) {
                    showFlash('danger', 'Error de red. No se pudo reagendar.');
                } finally {
                    // ✅ Rehabilitar lo que deshabilitaste
                    $('#modalPaymentMethodHidden').prop('disabled', false);
                    $('#modalPaymentStatusHidden').prop('disabled', false);
                    $('#modalAmountPaidHidden').prop('disabled', false);
                    $('#modalPaymentPaidAtHidden').prop('disabled', false);
                    $('#modalClientTransactionIdHidden').prop('disabled', false);
                    $('#modalPaymentNotesHidden').prop('disabled', false);
                }
            })();
        });

        // Al cerrar wizard, reabrir modal de detalles si quieres seguir viendo info
        $('#rescheduleWizardModal').on('hidden.bs.modal', async function () {
            // ✅ Si el usuario cerró sin confirmar, liberar el hold
            await __releaseRescheduleHold();

            // Limpieza simple
            __rescheduleResetWizard();
        });

        $('#btnConfirmarCita').on('click', async function () {
            const apptId = $('#appointmentDetailsModal').data('appointment-id');

            if (!apptId) {
                alert('No se encontró el ID de la cita en el modal.');
                return;
            }

            try {
                const res = await fetch(`/appointments/${apptId}/confirm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                alert(data.message || 'No se pudo confirmar la cita.');
                return;
                }

                alert(data.message || 'Cita confirmada correctamente.');

                // opcional: refrescar tabla o re-cargar datos del modal
                // location.reload();
            } catch (e) {
                console.error(e);
                alert('Error inesperado al confirmar la cita.');
            }
        });

        // (Opcional) Por ahora: estos botones solo muestran alerta placeholder
        $(document).on('click', '#btnNoAsistio,#btnCancelarCita,#btnVerHistorial,#btnSendReminder3h', function(){
            alert('Acción pendiente de implementar (solo UI en este paso).');
            $('#apptActionsDropdown').dropdown('hide');
        });

        // ================================
        // CALENDARIO (UI) PARA REAGENDAR
        // ================================
        (function () {

        const monthNames = [
            "Enero","Febrero","Marzo","Abril","Mayo","Junio",
            "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
        ];

        let resMonth = (new Date()).getMonth();   // 0-11
        let resYear  = (new Date()).getFullYear(); // 2026 etc.

        function generateRescheduleCalendar() {
            $("#reschedule-current-month").text(`${monthNames[resMonth]} ${resYear}`);
            $("#reschedule-calendar-body").empty();

            const firstDay = new Date(resYear, resMonth, 1).getDay(); // 0=Dom
            const daysInMonth = new Date(resYear, resMonth + 1, 0).getDate();

            let date = 1;

            for (let i = 0; i < 6; i++) {
            const row = $("<tr></tr>");

            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < firstDay) {
                row.append(`<td class="text-center py-2"></td>`);
                } else if (date > daysInMonth) {
                row.append(`<td class="text-center py-2"></td>`);
                } else {

                    const today = new Date();
                    today.setHours(0,0,0,0);

                    const cellDate = new Date(resYear, resMonth, date);
                    cellDate.setHours(0,0,0,0);

                    const dateISO = `${resYear}-${String(resMonth+1).padStart(2,'0')}-${String(date).padStart(2,'0')}`;
                    const isPastOrToday = (cellDate < today);

                    row.append(`
                        <td class="text-center py-2 calendar-day ${isPastOrToday ? 'disabled text-muted' : ''}" data-date="${dateISO}">
                        ${date}
                        </td>
                    `);

                    date++;
                }
            }

            $("#reschedule-calendar-body").append(row);

            if (date > daysInMonth) break;
            }
        }

        function updateReschedulePrevArrow() {

            const today = new Date();
            const minMonth = today.getMonth();
            const minYear  = today.getFullYear();

            const isMinMonth = (resYear === minYear && resMonth === minMonth);

            const $prevBtn = $('#reschedule-prev-month');

            if (isMinMonth) {
                $prevBtn.prop('disabled', true)
                    .addClass('disabled')
                    .css({ opacity: 0.4, cursor: 'not-allowed', pointerEvents: 'none' });
            } else {
                $prevBtn.prop('disabled', false)
                    .removeClass('disabled')
                    .css({ opacity: 1, cursor: 'pointer', pointerEvents: 'auto' });
            }
        }

        // Navegación
        $(document).on("click", "#reschedule-prev-month", function (e) {

            const today = new Date();
            const minMonth = today.getMonth();
            const minYear  = today.getFullYear();

            // ⛔ Si ya estamos en el mes mínimo, no hacer nada
            if (resYear === minYear && resMonth === minMonth) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            resMonth--;
            if (resMonth < 0) {
                resMonth = 11;
                resYear--;
            }

            generateRescheduleCalendar();
            updateReschedulePrevArrow();
        });

        $(document).on("click", "#reschedule-next-month", function () {
            resMonth++;
            if (resMonth > 11) { resMonth = 0; resYear++; }
            generateRescheduleCalendar();
            updateReschedulePrevArrow();
        });

        // Click en un día del calendario
        $(document).on("click", "#reschedule-calendar-body .calendar-day", async function () {
            const dateStr = String($(this).data("date") || "").trim();
            if (!dateStr) return;

            // Política admin: solo futuro (sin 24h, sin sábado)
            const today = new Date();
            today.setHours(0,0,0,0);

            const picked = new Date(dateStr + "T00:00:00");

            // ✅ Bloquear solo fechas pasadas (HOY sí se permite)
            if (picked < today) {
                $('#rescheduleSlotsHint').show().text('Selecciona una fecha desde hoy.');
                $('#rescheduleSlots').empty();
                return;
            }

            // UI: marcar seleccionado
            $("#reschedule-calendar-body .calendar-day").removeClass("active selected");
            $(this).addClass("active selected");

            // Guardar fecha seleccionada (NO pisa el slot)
            window.__rescheduleSelectedDate = dateStr;

            // Sincroniza la fecha al input real (para que valide y para que el confirm use esa fecha)
            $('#rescheduleDateInput').val(dateStr).trigger('change');

            // Actualizar resumen "después" (solo fecha por ahora; la hora se setea cuando elijas slot)
            $('#rescheduleConfirmAfter').text(dateStr);

            // Cargar turnos
            const ctx = window.__rescheduleContext || null;
            if (!ctx || !ctx.employee_id) {
                $('#rescheduleSlotsHint').show().text('No se pudo detectar el doctor/empleado.');
                return;
            }

            await __loadRescheduleSlots(ctx.employee_id, dateStr);
        });

        // IMPORTANTE: generar cuando el modal ya abrió (si lo haces antes, a veces no pinta)
        const modalEl = document.getElementById("rescheduleWizardModal");
        if (modalEl) {
            modalEl.addEventListener("shown.bs.modal", function () {
            generateRescheduleCalendar();
            updateReschedulePrevArrow();
            });
        }
        generateRescheduleCalendar();
        updateReschedulePrevArrow();

        })();
    </script>
@endsection