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

            // ✅ Data mínima para la plantilla del correo
            $mailData = [
                // ajusta estos si tus columnas tienen otro nombre
                'date' => $appt->appointment_date ?? $appt->date ?? null,
                'time' => $appt->appointment_time ?? $appt->time ?? null,
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
}