@php
use Carbon\Carbon;

  $title = 'Cita cancelada - FamySALUD';
  $subtitle = 'Tu cita ha sido cancelada ❌';

  $date = !empty($data['date'])
      ? Carbon::parse($data['date'])->locale('es')->translatedFormat('d M Y')
      : '—';

  // =========================
  // Modalidad / Área / Servicio / Zonas horarias
  // =========================
  $ecuTz = 'America/Guayaquil';

  $mode = (string) ($data['mode'] ?? '');
  $modeNorm = trim(mb_strtolower($mode));
  $isVirtual = ($modeNorm === 'virtual'); // 'virtual' | 'presencial'

  $area = $data['area'] ?? '—';
  $service = $data['service'] ?? '—';

  $patientTz = $data['patient_timezone'] ?? null;

  // Datetime base en TZ Ecuador (para convertir si es virtual)
  $dtEc = null;
  $startsAtRaw = $data['starts_at'] ?? null;
  $dtEndEc = null;
  $endsAtRaw = $data['ends_at'] ?? null;

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

  // Hora Ecuador (siempre)
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
            <td style="background:#7f1d1d;padding:18px 22px;">
              <div style="font-size:22px;line-height:28px;font-weight:700;color:#ffffff;">
                {{ $title }}
              </div>
              <div style="margin-top:6px;font-size:13px;line-height:18px;color:#fecaca;">
                {{ $subtitle }}
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:22px;">
              <div style="font-size:16px;line-height:24px;color:#111827;">
                Te informamos que tu cita <strong>ha sido cancelada</strong>.
              </div>

              <div style="margin-top:10px;font-size:14px;line-height:20px;color:#111827;">
                Estos eran los detalles de tu cita:
              </div>

              <!-- Info box (MISMO formato que register) -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-top:16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                      <strong>Fecha:</strong> {{ $date }}
                    </div>

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
                      <strong>Hora:</strong> {{ $timeEc }}
                    </div>

                    @if(!empty($timePatient))
                      <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                        <strong>Hora (tu zona horaria):</strong> {{ $timePatient }}
                      </div>
                    @endif
                  </td>
                </tr>
              </table>

              <div style="margin-top:18px;font-size:14px;line-height:20px;color:#111827;">
                Si necesitas reagendar, puedes comunicarte con nosotros al +593 93 903 4743 📲
              </div>
            </td>
          </tr>

          <!-- Footer (IGUAL al register) -->
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