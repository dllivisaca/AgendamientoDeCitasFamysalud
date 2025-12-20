@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
@php
     $appointments = Appointment::whereMonth('appointment_date', Carbon::now()->month)
            ->whereYear('appointment_date', Carbon::now()->year)
            ->where('status', 'Confirmed') // Only show confirmed appointments
            ->get();
@endphp

    <p>Welcome to this beautiful admin panel.</p>
    <div id="calendar"></div>

<script>
    $(document).ready(function() {
        $('#calendar').fullCalendar({
            events: [
                @foreach($appointments as $appointment)
                    {
                        title: '{{ $appointment->patient_full_name }} - {{ optional($appointment->service)->name }}',
                        start: '{{ $appointment->appointment_date }}T{{ $appointment->appointment_time }}',
                        end: '{{ $appointment->appointment_date }}T{{ \Carbon\Carbon::parse($appointment->appointment_time)->addHours(1)->format('H:i') }}',
                        description: '{{ $appointment->patient_notes }}',
                        color: 'green',
                    },
                @endforeach
            ],
            eventClick: function(event) {
                alert(event.title);
            },
        });
    });
</script>

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.css" rel="stylesheet">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>
@stop