@extends('adminlte::page')

@section('title', 'Panel de inicio · FamySalud')

@section('content_header')
    <h1>Citas</h1>
    @if (session('success'))
        <div class="alert alert-success alert-dismissable mt-2">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>{{ session('success') }}</strong>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@stop

@section('content')


    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-sm-12">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER DETALLES DE LA CITA AL HACER CLIC -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

            <div class="modal-header d-flex align-items-start justify-content-between">
                <div>
                <h5 class="modal-title mb-0">Detalles de la cita</h5>
                <div class="small text-muted mt-1">
                    Código de reserva: <strong id="modalBookingCode">N/A</strong>
                </div>
                </div>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- Resumen -->
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

                <!-- Datos del paciente (colapsable) -->
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
                        <div class="small text-muted">Fecha de nacimiento</div>
                        <div class="text-dark" id="modalPatientDobText">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Edad</div>
                        <div class="text-dark" id="modalPatientAge">N/A</div>
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

                <!-- Detalles de la cita -->
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

                <!-- Facturación (colapsable) -->
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
                    </div>
                </div>
                </div>

                <!-- Pago (dinámico) -->
                <div class="p-3 mb-3 rounded border bg-light" id="paymentSectionWrapper" style="display:none;">
                <h6 class="mb-3 font-weight-bold text-primary">Información de pago</h6>

                <!-- TARJETA (READ-ONLY) -->
                <div id="paymentCardBlock" style="display:none;">
                    <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Método</div>
                        <div class="text-dark" id="modalPaymentMethodLabel">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Estado del pago</div>
                        <div class="text-dark" id="modalPaymentStatusBadge2">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Monto total a pagar</div>
                        <div class="text-dark" id="modalPaymentAmount">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Monto pagado</div>
                        <div class="text-dark" id="modalPaidAmountText">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Fecha del pago</div>
                        <div class="text-dark" id="modalPaymentDate">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Client Transaction ID</div>
                        <div class="text-dark" id="modalClientTransactionId" style="word-break: break-word; overflow-wrap:anywhere;">N/A</div>
                    </div>

                    <div class="col-md-12 mb-0">
                        <div class="small text-muted">Observaciones de pago</div>
                        <div class="text-dark" id="modalCardNotesText">
                        <span class="text-muted font-italic small">N/A</span>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- TRANSFERENCIA (READ-ONLY) -->
                <div id="paymentTransferBlock" style="display:none;">
                    <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Método</div>
                        <div class="text-dark" id="modalTransferMethodLabel">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Monto total a pagar</div>
                        <div class="text-dark" id="modalTransferAmount">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2 offset-md-6">
                        <div class="small text-muted">Monto pagado</div>
                        <div class="text-dark" id="modalTransferPaidAmountText">N/A</div>
                    </div>

                    <div class="col-md-12 mb-0">
                        <div class="small text-muted">Observaciones de pago</div>
                        <div class="text-dark" id="modalTransferNotesText">
                        <span class="text-muted font-italic small">N/A</span>
                        </div>
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

                    <div class="col-md-12 mt-3">
                        <div class="small text-muted font-weight-bold">Validación de transferencia</div>
                    </div>

                    <div class="col-md-12 mb-2">
                        <div class="small text-muted">Estado de validación</div>
                        <div class="text-dark" id="modalTransferValidationText">Sin revisar</div>
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
                        <div class="small text-muted">Observaciones de validación</div>
                        <div class="text-dark" id="modalTransferValidationNotesText">
                        <span class="text-muted font-italic small">N/A</span>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- EFECTIVO (READ-ONLY) -->
                <div id="paymentCashBlock" style="display:none;">
                    <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Método</div>
                        <div class="text-dark" id="modalCashMethodLabel">Efectivo</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Monto total a pagar</div>
                        <div class="text-dark" id="modalCashAmount">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2 offset-md-6">
                        <div class="small text-muted">Monto pagado</div>
                        <div class="text-dark" id="modalCashPaidAmountText">N/A</div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Fecha del pago</div>
                        <div class="text-dark" id="modalCashPaidAtText">N/A</div>
                    </div>

                    <div class="col-md-12 mb-0">
                        <div class="small text-muted">Observaciones de pago</div>
                        <div class="text-dark" id="modalCashNotesText">
                        <span class="text-muted font-italic small">N/A</span>
                        </div>
                    </div>
                    </div>
                </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn btn-success" id="receiptDownloadBtn">Descargar</button>

                <a href="#" target="_blank" id="receiptOpenNewTab" class="btn btn-primary">
                Pantalla completa
                </a>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>

            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" />
    <style>
        #calendar {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .fc-toolbar h2 {
            font-size: 1.2em;
        }

        /* DAILY VIEW OPTIMIZATIONS */
        .fc-agendaDay-view .fc-time-grid-container {
            height: auto !important;
        }

        .fc-agendaDay-view .fc-event {
            margin: 1px 2px;
            border-radius: 3px;
        }

        .fc-agendaDay-view .fc-event.short-event {
            height: 30px;
            font-size: 0.85em;
            padding: 2px;
        }

        .fc-agendaDay-view .fc-event .fc-content {
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fc-agendaDay-view .fc-time {
            width: 50px !important;
        }

        .fc-agendaDay-view .fc-time-grid {
            min-height: 600px !important;
        }

        .fc-agendaDay-view .fc-event.fc-short-event {
            height: 35px;
            font-size: 0.85em;
        }

        .fc-agendaDay-view .fc-time {
            width: 70px !important;
            padding: 0 10px;
        }

        .fc-agendaDay-view .fc-axis {
            width: 70px !important;
        }

        .fc-agendaDay-view .fc-content-skeleton {
            padding-bottom: 5px;
        }

        .fc-agendaDay-view .fc-slats tr {
            height: 40px;
        }

        .fc-event {
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .fc-event:hover {
            opacity: 1;
            z-index: 1000 !important;
        }

        /* ===== Capitalizar textos del calendario ===== */

        /* Mes y año (ej: enero 2026 → Enero 2026) */
        .fc-center h2 {
        text-transform: capitalize;
        }

        /* Días de la semana (lun. → Lun.) */
        .fc-day-header {
        text-transform: capitalize;
        }

        /* ===== Centrar texto del footer ===== */
        .main-footer {
        text-align: center !important;
        }

        /* ===============================
        MEJORAR LEGIBILIDAD EN VISTA DÍA
        =============================== */

        /* Evento en agendaDay */
        .fc-agendaDay-view .fc-event {
            padding: 6px 8px !important;
            font-size: 0.9rem !important;
            line-height: 1.3 !important;
            border-radius: 6px;
        }

        /* Contenido del evento */
        .fc-agendaDay-view .fc-event .fc-content {
            white-space: normal !important;   /* permite saltos de línea */
            overflow: visible !important;
        }

        /* Hora del evento */
        .fc-agendaDay-view .fc-event .fc-time {
            font-weight: 600;
            margin-right: 6px;
        }

        /* Título del evento */
        .fc-agendaDay-view .fc-event .fc-title {
            display: block;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Evita que se vea "aplastado" verticalmente */
        .fc-agendaDay-view .fc-time-grid-event {
            min-height: 42px;
        }

        /* ✅ Modal de comprobante: tamaño fijo al viewport (no gigante) */
        .modal-dialog.modal-receipt{
        max-width: 900px;
        width: calc(100% - 2rem);
        margin: 1rem auto;
        }

        /* ✅ El modal ocupa alto fijo del viewport (esto sí "amarra" el flex) */
        #transferReceiptModal .modal-content{
        height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        }

        /* ✅ IMPORTANTE en flex: permite que el body se encoja y calcule bien */
        #transferReceiptModal .modal-body{
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        padding: 12px;
        }

        /* ✅ Visor interno ocupa todo el body */
        #receiptViewer{
        width: 100%;
        height: 100%;
        min-height: 0;
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

    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/es.js"></script>

    <script>
        /**
         * Modal Detalles (READ-ONLY)
         * Se llama con: openAppointmentModalReadOnly(data)
         */
        (function () {

        function normalizeValue(v) {
            return String(v ?? '').trim().toLowerCase();
        }

        function formatDocTypeLabel(v) {
            const s = String(v || '').trim().toLowerCase();
            if (!s) return 'N/A';
            if (s === 'cedula') return 'Cédula';
            if (s === 'ruc') return 'RUC';
            if (s === 'pasaporte') return 'Pasaporte';
            return v;
        }

        function fmtMoney(n) {
            if (n === null || n === undefined || String(n).trim() === '') return 'N/A';
            const x = Number(String(n).trim().replace(',', '.'));
            if (!isFinite(x)) return String(n);
            return `$${x.toFixed(2)}`;
        }

        function fmtNiceDateTime(dateRaw) {
            if (!dateRaw || String(dateRaw).trim() === '') return 'N/A';
            const d = new Date(String(dateRaw).replace(' ', 'T'));
            if (isNaN(d.getTime())) return String(dateRaw);
            const datePart = d.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });
            const timePart = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            return `${datePart} · ${timePart}`;
        }

        function statusBadge(statusRaw) {
            let normalized = String(statusRaw || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace('cancelled', 'canceled');

            const colors = {
            pending_verification: '#7f8c8d',
            pending_payment: '#f39c12',
            paid: '#2ecc71',
            confirmed: '#3498db',
            completed: '#008000',
            canceled: '#ff0000',
            rescheduled: '#f1c40f',
            no_show: '#e67e22',
            on_hold: '#95a5a6',
            };

            const labels = {
            pending_verification: 'Pendiente de verificación',
            pending_payment: 'Pendiente de pago',
            paid: 'Pagada',
            confirmed: 'Confirmada',
            completed: 'Completada',
            canceled: 'Cancelada',
            rescheduled: 'Reagendada',
            no_show: 'No asistió',
            on_hold: 'En espera',
            };

            const color = colors[normalized] || '#7f8c8d';
            const label = labels[normalized] || (statusRaw ? String(statusRaw) : 'N/A');
            return `<span class="badge px-2 py-1" style="background-color:${color};color:white;">${label}</span>`;
        }

        function paymentStatusBadge(status) {
            const s = String(status || '').trim().toLowerCase();
            const colors = {
            unpaid: '#95a5a6',
            pending: '#f39c12',
            partial: '#3498db',
            paid: '#2ecc71',
            refunded: '#9b59b6',
            };
            const labels = {
            unpaid: 'No pagado',
            pending: 'Pendiente',
            partial: 'Pagado parcialmente',
            paid: 'Pagado',
            refunded: 'Reembolsado',
            };
            const key = s || 'na';
            const color = colors[key] || '#95a5a6';
            const label = labels[key] || (status ? String(status) : 'N/A');
            return `<span class="badge px-2 py-1" style="background-color:${color};color:white;">${label}</span>`;
        }

        function paymentMethodLabel(m) {
            const s = String(m || '').trim().toLowerCase();
            if (s === 'card') return 'Tarjeta';
            if (s === 'transfer') return 'Transferencia';
            if (s === 'cash') return 'Efectivo';
            return s ? (s.charAt(0).toUpperCase() + s.slice(1)) : 'N/A';
        }

        function setPaymentUI(data) {
            const pm = String(data.payment_method || '').trim().toLowerCase();

            $('#paymentSectionWrapper').hide();
            $('#paymentCardBlock, #paymentTransferBlock, #paymentCashBlock').hide();

            // Siempre pinta el badge de estado pago (aunque no haya método)
            $('#modalPaymentStatusBadge').html(paymentStatusBadge(data.payment_status));

            if (!pm) return;

            $('#paymentSectionWrapper').show();

            if (pm === 'card') {
            $('#paymentCardBlock').show();

            $('#modalPaymentMethodLabel').text(paymentMethodLabel(pm));
            $('#modalPaymentStatusBadge2').html(paymentStatusBadge(data.payment_status));
            $('#modalPaymentAmount').text(fmtMoney(data.amount));
            $('#modalPaidAmountText').text(fmtMoney(data.amount_paid));
            $('#modalPaymentDate').text(fmtNiceDateTime(data.payment_paid_at));

            const ctx = String(data.client_transaction_id || '').trim();
            $('#modalClientTransactionId').html(
                ctx ? ctx : '<span class="text-muted font-italic small">No se registró Client Transaction ID</span>'
            );

            const notes = String(data.payment_notes || '').trim();
            $('#modalCardNotesText').html(
                notes
                    ? notes
                    : '<span class="text-muted font-italic small">No se registraron observaciones</span>'
            );
            }

            if (pm === 'transfer') {
            $('#paymentTransferBlock').show();

            $('#modalTransferMethodLabel').text(paymentMethodLabel(pm));
            $('#modalTransferAmount').text(fmtMoney(data.amount));
            $('#modalTransferPaidAmountText').text(fmtMoney(data.amount_paid));

            const notes = String(data.payment_notes || '').trim();
            $('#modalTransferNotesText').html(
                notes
                    ? notes
                    : '<span class="text-muted font-italic small">No se registraron observaciones</span>'
            );

            $('#modalTransferBankOrigin').text(data.transfer_bank_origin || 'N/A');
            $('#modalTransferPayerName').text(data.transfer_payer_name || 'N/A');
            if (data.transfer_date) {
                const m = moment(data.transfer_date);
                $('#modalTransferDate').text(
                    m.isValid()
                        ? m.locale('es')
                            .format('DD MMM YYYY')
                            .replace('.', '')   // 👈 quita el punto del mes
                        : 'N/A'
                );
            } else {
                $('#modalTransferDate').text('N/A');
            }
            $('#modalTransferReference').text(data.transfer_reference || 'N/A');

            const tReceiptPath = String(data.transfer_receipt_path || '').trim();

            if (tReceiptPath !== '') {
                const appointmentId = String(data.appointment_id || '').trim();
                const protectedUrl = `/admin/appointments/${appointmentId}/transfer-receipt`;
                const openViewUrl  = `/admin/appointments/${appointmentId}/transfer-receipt/view`;

                const pathLower = tReceiptPath.toLowerCase();
                const fileType = pathLower.endsWith('.pdf') ? 'pdf' : 'image';

                const bookingCode = String(data.booking_code || `FS-${appointmentId}`).trim();

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
                    '<span class="text-muted font-italic small">No se registró comprobante</span>'
                );
            }

            const vStatus = String(data.transfer_validation_status || '').trim().toLowerCase();
            const vAtRaw = String(data.transfer_validated_at || '').trim();
            const vById = String(data.transfer_validated_by || '').trim();
            const vByName = String(data.transfer_validated_by_name || '').trim();

            // ✅ Formato: "27 ene 2026 · 11:16 AM"
            let vAtLabel = '';
            if (vAtRaw) {
                const m = moment(vAtRaw);
                vAtLabel = m.isValid()
                    ? m.locale('es').format('DD MMM YYYY · h:mm A').replace('.', '') // quita "ene."
                    : vAtRaw;
            }

            const vByLabel = vByName || vById || '';
            const vNotes = String(data.transfer_validation_notes || '').trim();

            let vLabel = 'Sin revisar';
            if (vStatus === 'validated') vLabel = 'Validada';
            if (vStatus === 'rejected') vLabel = 'Rechazada';

            $('#modalTransferValidationText').text(vLabel);

            if (vAtLabel || vByLabel) {
                $('#transferValidationMeta').show();
                $('#modalTransferValidatedAt').text(vAtLabel || 'N/A');
                $('#modalTransferValidatedBy').text(vByLabel || 'N/A');
            } else {
                $('#transferValidationMeta').hide();
            }

            if (vNotes) {
                $('#transferValidationNotesWrapper').show();
                $('#modalTransferValidationNotesText').text(vNotes);
            } else {
                $('#transferValidationNotesWrapper').hide();
                $('#modalTransferValidationNotesText').html('<span class="text-muted font-italic small">N/A</span>');
            }
            }

            if (pm === 'cash') {
            $('#paymentCashBlock').show();

            $('#modalCashMethodLabel').text('Efectivo');
            $('#modalCashAmount').text(fmtMoney(data.amount));
            $('#modalCashPaidAmountText').text(fmtMoney(data.amount_paid));
            $('#modalCashPaidAtText').text(fmtNiceDateTime(data.payment_paid_at));

            const notes = String(data.payment_notes || '').trim();
            $('#modalCashNotesText').html(
                notes
                    ? notes
                    : '<span class="text-muted font-italic small">No se registraron observaciones</span>'
            );
            }
        }

        function setBillingUX(data, patient) {
            const billing = {
            name: data.billing_name || '',
            doc_type: data.billing_doc_type || '',
            doc_number: data.billing_doc_number || '',
            email: data.billing_email || '',
            phone: data.billing_phone || '',
            address: data.billing_address || '',
            };

            $('#modalBillingName').text(billing.name || 'N/A');
            $('#modalBillingDocType').text(formatDocTypeLabel(billing.doc_type));
            $('#modalBillingDocNumber').text(billing.doc_number || 'N/A');
            $('#modalBillingEmail').text(billing.email || 'N/A');
            $('#modalBillingPhone').text(billing.phone || 'N/A');
            $('#modalBillingAddress').text(billing.address || 'N/A');

            const hasAny =
            normalizeValue(billing.name) ||
            normalizeValue(billing.doc_type) ||
            normalizeValue(billing.doc_number) ||
            normalizeValue(billing.email) ||
            normalizeValue(billing.phone) ||
            normalizeValue(billing.address);

            let isDifferent = false;

            if (hasAny) {
            if (normalizeValue(billing.name) !== normalizeValue(patient.name)) isDifferent = true;
            if (normalizeValue(formatDocTypeLabel(billing.doc_type)) !== normalizeValue(formatDocTypeLabel(patient.doc_type))) isDifferent = true;
            if (normalizeValue(billing.doc_number) !== normalizeValue(patient.doc_number)) isDifferent = true;
            if (normalizeValue(billing.email) !== normalizeValue(patient.email)) isDifferent = true;
            if (normalizeValue(billing.phone) !== normalizeValue(patient.phone)) isDifferent = true;
            if (normalizeValue(billing.address) !== normalizeValue(patient.address)) isDifferent = true;
            }

            if (isDifferent) {
            $('#modalBillingSameNote').hide();
            $('#collapseBillingData').collapse('show');
            } else {
            $('#modalBillingSameNote').show();
            $('#collapseBillingData').collapse('hide');
            }
        }

        function fillModal(data) {
            $('#modalBookingCode').text(data.booking_code || 'N/A');

            $('#modalAppointmentName').text(data.patient_name || data.title || 'N/A');
            $('#modalStaff').text(data.employee_name || data.staff || 'N/A');
            $('#modalArea').text(data.area_name || data.area || 'N/A');
            $('#modalService').text(data.service_name || data.service_title || 'N/A');
            $('#modalDateTime').text(data.date_time_label || 'N/A');

            $('#modalStatusBadge').html(statusBadge(data.status));

            $('#modalPatientFullName').text(data.patient_full_name || 'N/A');
            $('#modalDocType').text(formatDocTypeLabel(data.patient_doc_type));
            $('#modalDocNumber').text(data.patient_doc_number || 'N/A');
            $('#modalPatientDobText').text(data.patient_dob || 'N/A');
            $('#modalPatientAge').text(data.patient_age || 'N/A');
            $('#modalEmail').text(data.patient_email || data.email || 'N/A');
            $('#modalPhone').text(data.patient_phone || data.phone || 'N/A');
            $('#modalAddress').text(data.patient_address || 'N/A');
            (function () {
                const tz = String(data.patient_timezone || '').trim();

                $('#modalPatientTimezone').html(
                    tz
                        ? tz
                        : '<span class="text-muted font-italic small">No se registró zona horaria</span>'
                );
            })();

            $('#modalAppointmentMode').text(
                data.appointment_mode
                    ? data.appointment_mode.charAt(0).toUpperCase() + data.appointment_mode.slice(1)
                    : 'N/A'
            );
            $('#modalDateTime2').text(data.date_time_label || 'N/A');
            $('#modalCreatedAt').text(data.created_at_label || 'N/A');

            const notes = String(data.patient_notes || data.notes || data.description || '').trim();
            $('#modalNotes').html(
            notes ? notes : '<span class="text-muted font-italic small">No se registraron notas</span>'
            );

            setBillingUX(
            data,
            {
                name: data.patient_full_name || '',
                doc_type: data.patient_doc_type || '',
                doc_number: data.patient_doc_number || '',
                email: data.patient_email || '',
                phone: data.patient_phone || '',
                address: data.patient_address || '',
            }
            );

            setPaymentUI(data);

            $('#appointmentModal').modal('show');
        }

        window.openAppointmentModalReadOnly = function (data) {
            fillModal(data || {});
        };

        })();
    </script>

    <script>
        $(document).ready(function() {
            // Initialize toasts first
            // $('.toast').toast({
            //     delay: 5000
            // });

            var calendarEvents = @json($appointments ?? []);
            console.log('[Calendar] events =', calendarEvents);

            // Initialize calendar
            $('#calendar').fullCalendar({
                locale: 'es',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaDay'
                },
                defaultView: 'month',
                timeFormat: 'h:mm A',
                displayEventTime: true,
                editable: false,
                slotDuration: '00:30:00',
                minTime: '06:00:00',
                maxTime: '22:00:00',
                events: calendarEvents,
                eventRender: function(event, element) {

                    var statusColors = {
                        'pending_verification': '#7f8c8d',
                        'pending_payment':      '#f39c12',
                        'paid':                 '#2ecc71',
                        'confirmed':            '#3498db',
                        'completed':            '#008000',
                        'canceled':             '#ff0000',
                        'rescheduled':          '#f1c40f',
                        'no_show':              '#e67e22',
                        'on_hold':              '#95a5a6',
                    };

                    var s = (event.status || '').toString().trim().toLowerCase().replace(/[\s-]+/g, '_');
                    var c = statusColors[s] || '#7f8c8d';

                    element.css({
                        'background-color': c,
                        'border-color': c,
                        'color': '#fff'
                    });

                    if ($.fn.tooltip) {
                        element.tooltip({
                            title: event.description || event.notes || 'Sin notas',
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                },
                eventClick: function(calEvent, jsEvent, view) {
                    const data = {
                        appointment_id: calEvent.id || '',
                        booking_code: calEvent.booking_code || calEvent.booking_id || 'N/A',

                        patient_name: calEvent.name || (calEvent.title ? String(calEvent.title).split(' - ')[0] : '') || 'N/A',
                        service_name: calEvent.service_title || (calEvent.title ? String(calEvent.title).split(' - ')[1] : '') || 'N/A',
                        employee_name: calEvent.staff || 'N/A',
                        area_name: calEvent.area_name || 'N/A',

                        status: calEvent.status || '',

                        // Fecha / hora para los labels del modal
                        date_time_label: (function () {
                            if (!calEvent.start) return 'N/A';

                            const mStart = moment(calEvent.start).locale('es');
                            const datePart = mStart.format('DD MMM YYYY'); // ej: 06 feb 2026
                            const startTime = mStart.format('h:mm A');

                            // FullCalendar debería traer calEvent.end; si no, intentamos con appointment_end_time
                            let endTime = '';
                            if (calEvent.end) {
                                endTime = moment(calEvent.end).locale('es').format('h:mm A');
                            } else if (calEvent.appointment_end_time) {
                                // si te llega "11:35:00" o "11:35"
                                endTime = moment(datePart + ' ' + String(calEvent.appointment_end_time).trim(), 'DD MMM YYYY HH:mm:ss', true).isValid()
                                    ? moment(datePart + ' ' + String(calEvent.appointment_end_time).trim(), 'DD MMM YYYY HH:mm:ss').format('h:mm A')
                                    : String(calEvent.appointment_end_time).trim();
                            }

                            return endTime ? `${datePart} · ${startTime} – ${endTime}` : `${datePart} · ${startTime}`;
                        })(),
                        created_at_label: calEvent.created_at ? moment(calEvent.created_at).locale('es').format('D [de] MMM YYYY · h:mm A') : 'N/A',

                        appointment_mode: calEvent.appointment_mode || 'N/A',

                        // Paciente
                        patient_full_name: calEvent.patient_full_name || calEvent.name || 'N/A',
                        patient_doc_type: calEvent.patient_doc_type || '',
                        patient_doc_number: calEvent.patient_doc_number || '',
                        patient_dob: calEvent.patient_dob_label || calEvent.patient_dob || '',
                        patient_age: calEvent.patient_age || '',
                        patient_email: calEvent.email || '',
                        patient_phone: calEvent.phone || '',
                        patient_address: calEvent.patient_address || '',
                        patient_timezone: calEvent.patient_timezone || '',

                        patient_notes: calEvent.notes || calEvent.description || '',

                        // Facturación
                        billing_name: calEvent.billing_name || '',
                        billing_doc_type: calEvent.billing_doc_type || '',
                        billing_doc_number: calEvent.billing_doc_number || '',
                        billing_email: calEvent.billing_email || '',
                        billing_phone: calEvent.billing_phone || '',
                        billing_address: calEvent.billing_address || '',

                        // Pago
                        payment_method: calEvent.payment_method || '',
                        payment_status: calEvent.payment_status || '',
                        payment_paid_at: calEvent.payment_paid_at || '',
                        payment_notes: calEvent.payment_notes || '',
                        amount: calEvent.amount || '',
                        amount_paid: calEvent.amount_paid || '',
                        client_transaction_id: calEvent.client_transaction_id || '',

                        // Transfer extras (si los tienes)
                        transfer_bank_origin: calEvent.transfer_bank_origin || '',
                        transfer_payer_name: calEvent.transfer_payer_name || '',
                        transfer_date: calEvent.transfer_date || '',
                        transfer_reference: calEvent.transfer_reference || '',
                        transfer_receipt_path: calEvent.transfer_receipt_path || '',
                        transfer_validation_status: calEvent.transfer_validation_status || '',
                        transfer_validated_at: calEvent.transfer_validated_at || '',
                        transfer_validated_by: calEvent.transfer_validated_by || '',
                        transfer_validated_by_name: calEvent.transfer_validated_by_name || '',
                        transfer_validation_notes: calEvent.transfer_validation_notes || '',
                    };

                    openAppointmentModalReadOnly(data);
                }
            });

            // Single form submission handler



        });

        // ✅ Abrir modal del comprobante (delegado) - versión "la que sí funciona"
        $(document).on('click', '.js-open-receipt-modal', function () {
            const url = $(this).data('url'); // ✅ SIEMPRE el protegido: /transfer-receipt
            const fileType = String($(this).data('filetype') || '').toLowerCase();
            const isPdf = (fileType === 'pdf');

            // ✅ Reset UI SIEMPRE
            $('#receiptError').hide();
            $('#receiptLoading').show();

            // ✅ Reset visor
            $('#receiptPdf').hide().attr('src', 'about:blank');

            // ✅ Matar handlers viejos y resetear IMG
            const $img = $('#receiptImg');
            $img.off('load error');
            $img.hide().attr('src', '');

            // Botones
            const openUrl = $(this).data('open-url') || url; // ✅ /view para pantalla completa
            $('#receiptOpenNewTab').attr('href', openUrl);
            $('#receiptDownloadBtn').data('url', url);

            const bookingCode = String($(this).data('bookingcode') || $(this).data('booking-code') || '').trim();
            $('#receiptDownloadBtn').data('booking-code', bookingCode);

            if (isPdf) {
                // ✅ PDF: no mostrar error (solo ocultarlo)
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

        // ✅ Descargar comprobante desde el modal (usa la URL protegida)
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

                const ext = (blob.type && blob.type.includes('pdf')) ? 'pdf'
                    : (blob.type && blob.type.includes('png')) ? 'png'
                    : (blob.type && blob.type.includes('jpeg')) ? 'jpg'
                    : 'bin';

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
        console.log('🔥 JS DEL CALENDARIO CARGADO');
        $(document).ready(function() {
            $(".alert").delay(2000).slideUp(300);
        });
    </script>


@stop
