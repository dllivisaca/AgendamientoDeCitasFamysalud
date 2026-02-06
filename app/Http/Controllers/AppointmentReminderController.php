<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentReminderMail;

class AppointmentReminderController extends Controller
{
    public function sendManual(Request $request, $appointmentId)
    {
        // kind esperado: MANUAL_24H o MANUAL_3H
        $kind = (string) $request->input('kind', '');
        if (!in_array($kind, ['MANUAL_24H', 'MANUAL_3H'], true)) {
            return response()->json(['ok' => false, 'message' => 'kind inválido'], 422);
        }

        // 1) Validar que la cita exista (evita FK error)
        $appt = DB::table('appointments')->where('id', $appointmentId)->first();

        if ($kind === 'MANUAL_24H') {
            // ✅ Tomamos la fecha/hora real de tu cita (ajusta nombres si difieren)
            $dateStr = $appt->appointment_date ?? null;
            $timeStr = $appt->appointment_time ?? null;

            if (!$dateStr || !$timeStr) {
                return response()->json(['ok' => false, 'message' => 'La cita no tiene fecha/hora para validar 24h.'], 422);
            }

            $tz = 'America/Guayaquil';
            $apptDt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateStr.' '.$timeStr.':00', $tz);
            $now = now($tz);

            // ✅ Debe ser mañana (por fecha calendario)
            if (!$apptDt->isTomorrow()) {
                return response()->json(['ok' => false, 'message' => 'El recordatorio 24h solo se puede enviar cuando la cita es mañana.'], 422);
            }

            // ✅ Y además dentro de ventana 24→20h
            $diffHours = $now->diffInMinutes($apptDt, false) / 60;
            if (!($diffHours <= 24 && $diffHours > 20)) {
                return response()->json(['ok' => false, 'message' => 'Fuera de la ventana permitida (24h a 20h).'], 422);
            }
        }

        if (!$appt) {
            return response()->json(['ok' => false, 'message' => 'Cita no encontrada'], 404);
        }

        // 2) Si ya fue enviado ese manual, por ahora bloqueamos duplicados (puedes cambiar esto luego)
        $existing = DB::table('appointment_reminder_logs')
            ->where('appointment_id', $appointmentId)
            ->where('reminder_kind', $kind)
            ->first();

        if ($existing && $existing->status === 'SENT') {
            return response()->json(['ok' => false, 'message' => 'Este recordatorio manual ya fue enviado.'], 409);
        }

        // 3) Upsert base (lazy): crea fila si no existe
        $now = now();
        DB::table('appointment_reminder_logs')->updateOrInsert(
            ['appointment_id' => $appointmentId, 'reminder_kind' => $kind],
            [
                'scheduled_for' => $now,
                'updated_at'    => $now,
                'created_at'    => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        // 4) Registrar intento (incrementa contador)
        DB::table('appointment_reminder_logs')
            ->where('appointment_id', $appointmentId)
            ->where('reminder_kind', $kind)
            ->update([
                'attempt_count'   => DB::raw('attempt_count + 1'),
                'last_attempt_at' => $now,
                'updated_at'      => $now,
            ]);

        // 5) AQUÍ va tu envío real (email/whatsapp/sms)
        //    De momento lo dejamos como "stub" para no romper nada.
        //    En el siguiente paso lo conectamos a tu función real de envío.
        try {
            // ✅ Email real del paciente (tu campo confirmado)
            $toEmail = (string) ($appt->patient_email ?? '');

            if (trim($toEmail) === '') {
                DB::table('appointment_reminder_logs')
                    ->where('appointment_id', $appointmentId)
                    ->where('reminder_kind', $kind)
                    ->update([
                        'status'     => 'FAILED',
                        'last_error' => 'No hay patient_email para enviar recordatorio.',
                        'updated_at' => $now,
                    ]);

                return response()->json([
                    'ok' => false,
                    'message' => 'Esta cita no tiene email del paciente (patient_email).'
                ], 422);
            }

            // ✅ Data para la plantilla del correo (servicio/área + tz)
            $dateStr = $appt->appointment_date ?? $appt->date ?? null;

            // Hora inicio (HH:MM)
            $timeStr = $appt->appointment_time ?? $appt->time ?? null;
            $timeShort = $timeStr ? substr((string) $timeStr, 0, 5) : null;

            // Hora fin (HH:MM)
            $endStr = $appt->appointment_end_time ?? $appt->end_time ?? null;
            $endShort = $endStr ? substr((string) $endStr, 0, 5) : null;

            // Datetimes base interpretados en Ecuador
            $startsAt = ($dateStr && $timeShort) ? ($dateStr . ' ' . $timeShort . ':00') : null;
            $endsAt   = ($dateStr && $endShort)  ? ($dateStr . ' ' . $endShort . ':00')  : null;

            // IDs según lo que me dijiste (service_ID, category_ID)
            $serviceId = $appt->service_ID ?? $appt->service_id ?? null;

            $serviceTitle = null;
            $categoryTitle = null;

            if ($serviceId) {
                $serviceRow = DB::table('services')
                    ->select('title', 'category_ID', 'category_id')
                    ->where('id', $serviceId)
                    ->first();

                if ($serviceRow) {
                    $serviceTitle = $serviceRow->title ?? null;

                    $categoryId = $serviceRow->category_ID ?? $serviceRow->category_id ?? null;

                    if ($categoryId) {
                        $categoryTitle = DB::table('categories')
                            ->where('id', $categoryId)
                            ->value('title');
                    }
                }
            }

            $mailData = [
                'date' => $dateStr,
                'time' => $timeShort,
                'starts_at' => $startsAt,
                'end_time' => $endShort,
                'ends_at' => $endsAt,

                // Campos reales
                'mode' => $appt->appointment_mode ?? null,       // 'presencial' | 'virtual'
                'patient_timezone' => $appt->patient_timezone ?? null,

                // Derivados por lookup
                'service' => $serviceTitle,
                'area' => $categoryTitle,
            ];


            Mail::to($toEmail)->send(new AppointmentReminderMail($mailData, $kind));

            DB::table('appointment_reminder_logs')
                ->where('appointment_id', $appointmentId)
                ->where('reminder_kind', $kind)
                ->update([
                    'status'           => 'SENT',
                    'sent_at'          => $now,
                    'sent_by_admin_id' => auth()->id(),
                    'last_error'       => null,
                    'updated_at'       => $now,
                ]);

            return response()->json([
                'ok' => true,
                'message' => 'Recordatorio enviado por email (Mailtrap).'
            ]);
        } catch (\Throwable $e) {
            DB::table('appointment_reminder_logs')
                ->where('appointment_id', $appointmentId)
                ->where('reminder_kind', $kind)
                ->update([
                    'status'           => 'FAILED',
                    'sent_by_admin_id' => auth()->id(),
                    'last_error'       => $e->getMessage(),
                    'updated_at'       => $now,
                ]);

            return response()->json([
                'ok' => false,
                'message' => 'Falló el envío por email.',
                'error' => $e->getMessage()
            ], 500);
        }

        DB::table('appointment_reminder_logs')
            ->where('appointment_id', $appointmentId)
            ->where('reminder_kind', $kind)
            ->update([
                'status'     => 'FAILED',
                'last_error' => $errorMsg ?: 'Error al enviar',
                'updated_at' => $now,
            ]);

        return response()->json(['ok' => false, 'message' => 'Falló el envío', 'error' => $errorMsg], 500);
    }

    public function manualStatus($appointmentId)
    {
        $rows = DB::table('appointment_reminder_logs')
            ->select('reminder_kind', 'status')
            ->where('appointment_id', $appointmentId)
            ->whereIn('reminder_kind', ['MANUAL_3H', 'MANUAL_24H'])
            ->get()
            ->keyBy('reminder_kind');

        return response()->json([
            'MANUAL_3H' => [
                'exists' => isset($rows['MANUAL_3H']),
                'status' => $rows['MANUAL_3H']->status ?? null,
            ],
            'MANUAL_24H' => [
                'exists' => isset($rows['MANUAL_24H']),
                'status' => $rows['MANUAL_24H']->status ?? null,
            ],
        ]);
    }

    public function ui($appointmentId)
    {
        // Construye UI para 3H y 24H con el mismo patrón de encuesta:
        // - Si MANUAL sent => ocultar
        // - Si AUTO sent => "Reenviar"
        // - Si AUTO failed => "Reintento manual"
        // - Si nada => "Enviar ahora"
        //
        // Nota: la validación de "ventana" (24→20 y 3→1) ya la maneja tu JS por tiempo,
        // aquí solo devolvemos texto/hint y el estado de envíos.

        $kinds = [
            'MANUAL_3H'  => ['AUTO' => 'AUTO_3H',  'label' => '3H'],
            'MANUAL_24H' => ['AUTO' => 'AUTO_24H', 'label' => '24H'],
        ];

        $out = [];

        foreach ($kinds as $manualKind => $meta) {
            $autoKind = $meta['AUTO'];
            $label    = $meta['label'];

            $auto = DB::table('appointment_reminder_logs')
                ->where('appointment_id', $appointmentId)
                ->where('reminder_kind', $autoKind)
                ->orderByDesc('id')
                ->first();

            $manual = DB::table('appointment_reminder_logs')
                ->where('appointment_id', $appointmentId)
                ->where('reminder_kind', $manualKind)
                ->orderByDesc('id')
                ->first();

            // Caso 4/5: si manual fue enviado => ocultar botón (límite alcanzado)
            if ($manual && strtoupper((string)($manual->status ?? '')) === 'SENT') {
                $out[$manualKind] = [
                    'show' => false,
                    'text' => '',
                    'hint' => '',
                ];
                continue;
            }

            $autoStatus = strtoupper((string)($auto->status ?? ''));

            // Caso 2: auto sent y manual no usado
            if ($autoStatus === 'SENT') {
                $out[$manualKind] = [
                    'show' => true,
                    'text' => "Reenviar recordatorio {$label}",
                    'hint' => 'Máximo un reenvío manual permitido.',
                ];
                continue;
            }

            // Caso 3: auto failed y manual no usado
            if ($autoStatus === 'FAILED') {
                $out[$manualKind] = [
                    'show' => true,
                    'text' => "Enviar recordatorio {$label} (reintento manual)",
                    'hint' => 'El envío automático falló.',
                ];
                continue;
            }

            // Caso 1: nada enviado aún (o auto queued / no existe)
            $out[$manualKind] = [
                'show' => true,
                'text' => "Enviar recordatorio {$label} ahora",
                'hint' => 'Este recordatorio está dentro de su ventana de envío.',
            ];
        }

        // Devolver en formato cómodo para el JS
        return response()->json([
            'MANUAL_3H'  => $out['MANUAL_3H'],
            'MANUAL_24H' => $out['MANUAL_24H'],
        ]);
    }
}