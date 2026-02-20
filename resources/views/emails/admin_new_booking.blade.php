@php
use Carbon\Carbon;

  $title = 'Nueva cita registrada - FamySALUD APP';
  $subtitle = 'Te informamos que una nueva cita ha sido registrada en nuestro sistema';

  $bookingId = $data['booking_id'] ?? null;

  // =========================
  // Datos base
  // =========================
  $ecuTz = 'America/Guayaquil';

  $mode = (string) ($data['mode'] ?? '');
  $modeNorm = trim(mb_strtolower($mode));
  $isVirtual = ($modeNorm === 'virtual');

  $area = $data['area'] ?? '—';
  $service = $data['service'] ?? '—';
  $professional = $data['professional'] ?? '—';

  // Paciente
  $patientName  = $data['patient_full_name'] ?? '—';
  $patientEmail = $data['patient_email'] ?? '—';
  $patientPhone = $data['patient_phone'] ?? '—';
  $patientTz    = $data['patient_timezone'] ?? null;

  // Pago
  $paymentMethod = $data['payment_method'] ?? '—';     // transfer | card
  $paymentStatus = $data['payment_status'] ?? '—';     // pending/unpaid/partial/paid/refunded
  $amount        = $data['amount'] ?? null;

  // Fecha formateada
  $dateStr = $data['date'] ?? null;
  $date = !empty($dateStr)
      ? Carbon::parse($dateStr)->locale('es')->translatedFormat('d M Y')
      : '—';

  // Hora (igual lógica que tus correos)
  $dtEc = null;
  $dtEndEc = null;

  $startsAtRaw = $data['starts_at'] ?? null;
  $endsAtRaw   = $data['ends_at'] ?? null;

  try {
      if (!empty($startsAtRaw)) {
          $dtEc = Carbon::parse($startsAtRaw, $ecuTz);
      } elseif (!empty($data['date']) && !empty($data['time'])) {
          $dtEc = Carbon::parse(($data['date'].' '.$data['time']), $ecuTz);
      }

      if (!empty($endsAtRaw)) {
          $dtEndEc = Carbon::parse($endsAtRaw, $ecuTz);
      } elseif (!empty($data['date']) && !empty($data['end_time'])) {
          $dtEndEc = Carbon::parse(($data['date'].' '.$data['end_time']), $ecuTz);
      }
  } catch (\Throwable $e) {
      $dtEc = null;
      $dtEndEc = null;
  }

  if ($dtEc) {
      $startTxt = $dtEc->locale('es')->translatedFormat('h:i a');
      $endTxt = $dtEndEc ? $dtEndEc->locale('es')->translatedFormat('h:i a') : null;

      $timeEc = $endTxt
          ? "{$startTxt} – {$endTxt} (Ecuador)"
          : "{$startTxt} (Ecuador)";
  } else {
      $timeEc = '—';
  }

  // Hora paciente (solo si es virtual y hay timezone)
  $timePatient = null;
  if ($dtEc && $isVirtual && !empty($patientTz)) {
      try {
          $pStart = $dtEc->copy()->setTimezone($patientTz)->locale('es')->translatedFormat('h:i a');
          $pEnd = $dtEndEc
              ? $dtEndEc->copy()->setTimezone($patientTz)->locale('es')->translatedFormat('h:i a')
              : null;

          $timePatient = $pEnd
              ? "{$pStart} – {$pEnd} ({$patientTz})"
              : "{$pStart} ({$patientTz})";
      } catch (\Throwable $e) {
          $timePatient = null;
      }
  }

  // Labels simples
  $pmLabel = [
      'transfer' => 'Transferencia',
      'card' => 'Tarjeta',
      'cash' => 'Efectivo',
  ][strtolower((string)$paymentMethod)] ?? ($paymentMethod ?: '—');

  $psLabel = [
      'pending' => 'Pendiente',
      'unpaid' => 'No pagado',
      'partial' => 'Parcial',
      'paid' => 'Pagado',
      'refunded' => 'Reembolsado',
  ][strtolower((string)$paymentStatus)] ?? ($paymentStatus ?: '—');

  $amountTxt = ($amount !== null && $amount !== '') ? '$' . number_format((float)$amount, 2, '.', '') : '—';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;padding:24px 12px;">
    <tr>
      <td align="center">
        <!-- Card -->
        <table role="presentation" width="640" cellpadding="0" cellspacing="0"
               style="max-width:640px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
          <!-- Header -->
          <tr>
            <td style="background:#0b1533;padding:18px 22px;">
              <div style="font-size:22px;line-height:28px;font-weight:700;color:#ffffff;">
                {{ $title }}
              </div>
              <div style="margin-top:6px;font-size:13px;line-height:18px;color:#cbd5e1;">
                {{ $subtitle }}
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:22px;">

              @if(!empty($bookingId))
                <div style="font-size:14px;line-height:20px;color:#111827;margin-bottom:12px;">
                  <strong>Código de reserva:</strong> {{ $bookingId }}
                </div>
              @endif

              <!-- Datos de la cita -->
              <div style="font-size:14px;line-height:20px;color:#111827;font-weight:700;">
                Datos de la cita
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                      <strong>Fecha:</strong> {{ $date }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Hora:</strong> {{ $timeEc }}
                    </div>

                    @if(!empty($timePatient))
                      <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                        <strong>Hora (zona horaria del paciente):</strong> {{ $timePatient }}
                      </div>
                    @endif

                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Modalidad:</strong> {{ $modeNorm ? ucfirst($modeNorm) : '—' }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Área de atención:</strong> {{ $area }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Servicio:</strong> {{ $service }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Profesional:</strong> {{ $professional }}
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Datos del paciente -->
              <div style="margin-top:16px;font-size:14px;line-height:20px;color:#111827;font-weight:700;">
                Datos del paciente
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                      <strong>Nombre completo:</strong> {{ $patientName }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Correo:</strong> {{ $patientEmail }}
                    </div>
                    <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                      <strong>Teléfono:</strong> {{ $patientPhone }}
                    </div>

                    @if($isVirtual && !empty($patientTz))
                      <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                        <strong>Zona horaria del paciente:</strong> {{ $patientTz }}
                      </div>
                    @endif
                  </td>
                </tr>
              </table>

              <!-- Pago -->
                <div style="margin-top:16px;font-size:14px;line-height:20px;color:#111827;font-weight:700;">
                Pago
                </div>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                    <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                        <strong>Método:</strong> {{ $pmLabel }}
                    </div>
                    <div style="margin-top:6px;font-size:13px;line-height:18px;color:#6b7280;">
                        El estado y los detalles del pago pueden revisarse desde el panel administrativo.
                    </div>
                    </td>
                </tr>
                </table>

              <div style="margin-top:18px;font-size:14px;line-height:20px;color:#111827;">
                Puedes ubicarla rápidamente en el panel "Todas las citas" usando el <strong>código de reserva</strong>.
              </div>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:14px 22px;border-top:1px solid #e5e7eb;background:#ffffff;">
              <div style="font-size:12px;line-height:18px;color:#94a3b8;text-align:center;">
                FamySALUD en Línea · Este es un correo automático, por favor no responder.
              </div>
            </td>
          </tr>
        </table>

        <div style="height:16px;"></div>
      </td>
    </tr>
  </table>
</body>
</html>