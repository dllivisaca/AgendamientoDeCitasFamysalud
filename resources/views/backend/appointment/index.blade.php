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

        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de la cita</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

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
                            RESTO DEL MODAL (NO BORRAR)
                            Se queda debajo tal cual
                        ========================== --}}
                        <p><strong>Total:</strong> <span id="modalAmount">N/A</span></p>
                        <p><strong>Notas:</strong> <span id="modalNotes">N/A</span></p>

                        <p><strong>Estado actual:</strong> <span id="modalStatusBadgeLegacy">N/A</span></p>

                        <div class="form-group ">
                            <label><strong>Estado:</strong></label>
                            <select name="status" class="form-control" id="modalStatusSelect">
                                <option value="Pending payment">Pendiente de pago</option>
                                <option value="Processing">Procesando</option>
                                <option value="Paid">Pagado</option>
                                <option value="Cancelled">Cancelado</option>
                                <option value="Completed">Completado</option>
                                <option value="On Hold">En espera</option>
                                {{-- <option value="Rescheduled">Rescheduled</option> --}}
                                <option value="No Show">No Show</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" onclick="return confirm('¿Estás seguro que quieres actualizar el estado de la cita?')"
                            class="btn btn-danger">Actualizar estado</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <div class="">
        @if (session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('success') }}</strong>
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

            // Set status select dropdown
            var status = $(this).data('status');
            $('#modalStatusSelect').val(status);

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
            $('#modalPaymentStatusBadge').html(
                `<span class="badge px-2 py-1" style="background-color:#95a5a6;color:white;">N/A</span>`
            );
        });
    </script>
@endsection
