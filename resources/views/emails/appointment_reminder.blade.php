<h2>Recordatorio de cita - FamySALUD</h2>

@if($kind === 'MANUAL_3H')
  <p>Te recordamos que tu cita es <strong>hoy</strong>.</p>
@else
  <p>Te recordamos que tu cita es <strong>mañana</strong>.</p>
@endif

<p><strong>Fecha:</strong> {{ $data['date'] ?? '—' }}</p>
<p><strong>Hora:</strong> {{ $data['time'] ?? '—' }}</p>

<p>Gracias por confiar en nosotros.</p>