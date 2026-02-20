@php
use Carbon\Carbon;

  $title = 'Cita médica reagendada - FamySALUD';
  $subtitle = 'Hemos actualizado tu cita 🔄';

  // =========================
  // Helpers: formateo con misma lógica
  // =========================
  $ecuTz = 'America/Guayaquil';

  $mode = (string) ($data['mode'] ?? '');
  $modeNorm = trim(mb_strtolower($mode));
  $isVirtual = ($modeNorm === 'virtual');

  $area = $data['area'] ?? '—';
  $service = $data['service'] ?? '—';

  $patientTz = $data['patient_timezone'] ?? null;

  $fmtBlock = function (?string $dateStr, ?string $timeStr, ?string $endStr) use ($ecuTz, $isVirtual, $patientTz) {

      $dateTxt = !empty($dateStr)
          ? Carbon::parse($dateStr)->locale('es')->translatedFormat('d M Y')
          : '—';

      // Datetimes base en TZ Ecuador
      $dtEc = null;
      $dtEndEc = null;

      try {
          if (!empty($dateStr) && !empty($timeStr)) {
              $dtEc = Carbon::parse(($dateStr.' '.$timeStr), $ecuTz);
          }
          if (!empty($dateStr) && !empty($endStr)) {
              $dtEndEc = Carbon::parse(($dateStr.' '.$endStr), $ecuTz);
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

      // Hora paciente (solo virtual + TZ)
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

      return [
          'dateTxt' => $dateTxt,
          'timeEc' => $timeEc,
          'timePatient' => $timePatient,
      ];
  };

  // =========================
  // Datos ANTES y DESPUÉS
  // =========================
  $before = $fmtBlock(
      $data['before_date'] ?? null,
      $data['before_time'] ?? null,
      $data['before_end_time'] ?? null
  );

  $after = $fmtBlock(
      $data['after_date'] ?? null,
      $data['after_time'] ?? null,
      $data['after_end_time'] ?? null
  );
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
              <div style="font-size:16px;line-height:24px;color:#111827;">
                Tu cita ha sido <strong>reagendada</strong>.
              </div>

              <div style="margin-top:10px;font-size:14px;line-height:20px;color:#111827;">
                A continuación te mostramos los detalles <strong>anteriores</strong> y los <strong>nuevos</strong>.
              </div>

              <!-- BEFORE -->
              <div style="margin-top:16px;font-size:14px;line-height:20px;color:#111827;font-weight:700;">
                Detalles anteriores
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                      <strong>Fecha:</strong> {{ $before['dateTxt'] }}
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
                      <strong>Hora:</strong> {{ $before['timeEc'] }}
                    </div>

                    @if(!empty($before['timePatient']))
                      <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                        <strong>Hora (tu zona horaria):</strong> {{ $before['timePatient'] }}
                      </div>
                    @endif
                  </td>
                </tr>
              </table>

              <!-- AFTER -->
              <div style="margin-top:18px;font-size:14px;line-height:20px;color:#111827;font-weight:700;">
                Detalles nuevos
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="margin-top:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                  <td style="padding:14px 16px;">
                    <div style="font-size:14px;line-height:20px;color:#111827;">
                      <strong>Fecha:</strong> {{ $after['dateTxt'] }}
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
                      <strong>Hora:</strong> {{ $after['timeEc'] }}
                    </div>

                    @if(!empty($after['timePatient']))
                      <div style="margin-top:6px;font-size:14px;line-height:20px;color:#111827;">
                        <strong>Hora (tu zona horaria):</strong> {{ $after['timePatient'] }}
                      </div>
                    @endif
                  </td>
                </tr>
              </table>

              <div style="margin-top:18px;font-size:14px;line-height:20px;color:#111827;">
                Gracias por confiar en FamySALUD 💙
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