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

    <!-- Appointment Modal -->
    <form id="appointmentStatusForm" method="POST" action="{{ route('dashboard.update.status') }}"
        onsubmit="return confirm('Are you sure you want to update the booking status?')">

        @csrf
        <input type="hidden" name="appointment_id" id="modalAppointmentId">

        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Appointment Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Client:</strong> <span id="modalAppointmentName">N/A</span></p>
                        <p><strong>Service:</strong> <span id="modalService">N/A</span></p>
                        <p><strong>Email:</strong> <span id="modalEmail">N/A</span></p>
                        <p><strong>Phone:</strong> <span id="modalPhone">N/A</span></p>
                        <p><strong>Staff:</strong> <span id="modalStaff">N/A</span></p>
                        <p><strong>Date & Time:</strong> <span id="modalStartTime">N/A</span></p>
                        {{-- <p><strong>End:</strong> <span id="modalEndTime">N/A</span></p> --}}
                        <p><strong>Amount:</strong> <span id="modalAmount">N/A</span></p>
                        <p><strong>Notes:</strong> <span id="modalNotes">N/A</span></p>
                        <p><strong>Current Status:</strong> <span id="modalStatusBadge">N/A</span></p>

                        <div class="form-group">
                            <label><strong>Change Status:</strong></label>
                            <select name="status" class="form-control" id="modalStatusSelect">
                                <option value="pending_verification">pending_verification</option>
                                <option value="pending_payment">pending_payment</option>
                                <option value="paid">paid</option>
                                <option value="confirmed">confirmed</option>
                                <option value="completed">completed</option>
                                <option value="canceled">canceled</option>
                                <option value="rescheduled">rescheduled</option>
                                <option value="no_show">no_show</option>
                                <option value="on_hold">on_hold</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Update Status</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
    </style>
@stop

@section('js')

    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/es.js"></script>


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
    // Populate modal with event data
    $('#modalAppointmentId').val(calEvent.id);
    $('#modalAppointmentName').text(calEvent.name || calEvent.title.split(' - ')[0] || 'N/A');
    $('#modalService').text(calEvent.service_title || calEvent.title.split(' - ')[1] || 'N/A');
    $('#modalEmail').text(calEvent.email || 'N/A');
    $('#modalPhone').text(calEvent.phone || 'N/A');
    $('#modalStaff').text(calEvent.staff || 'N/A');
    $('#modalAmount').text(calEvent.amount || 'N/A');
    $('#modalNotes').text(calEvent.description || calEvent.notes || 'N/A');
    $('#modalStartTime').text(
        calEvent.start
            ? moment(calEvent.start).locale('es').format('D [de] MMMM [de] YYYY HH:mm')
            : 'N/A'
    );

    // Get the status from the calendar event
    var status = calEvent.status || 'pending_verification';
    $('#modalStatusSelect').val(status);

    // Set status badge
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

    var s = (status || '').toString().trim().toLowerCase().replace(/[\s-]+/g, '_');
    var badgeColor = statusColors[s] || '#7f8c8d';
    $('#modalStatusBadge').html(
        `<span class="badge px-2 py-1" style="background-color: ${badgeColor}; color: white;">${status}</span>`
    );

    $('#appointmentModal').modal('show');
}
            });

            // Single form submission handler



        });
    </script>

    <script>
        console.log('🔥 JS DEL CALENDARIO CARGADO');
        $(document).ready(function() {
            $(".alert").delay(2000).slideUp(300);
        });
    </script>


@stop
