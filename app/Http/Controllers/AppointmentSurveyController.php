<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentSurveyMail;

class AppointmentSurveyController extends Controller
{
    public function ui(Appointment $appointment)
    {
        // Solo si está completed
        if (($appointment->status ?? '') !== 'completed') {
            return response()->json(['show' => false]);
        }

        $auto = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'auto')
            ->orderByDesc('id')
            ->first();

        $manual = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'manual')
            ->orderByDesc('id')
            ->first();

        // Caso 4: ya hubo manual exitoso => oculto (límite alcanzado)
        if ($manual && ($manual->status === 'sent')) {
            return response()->json(['show' => false]);
        }

        $autoStatus = $auto->status ?? null;

        // Caso 3: auto sent y manual aún no usado
        if ($autoStatus === 'sent') {
            return response()->json([
                'show' => true,
                'text' => 'Reenviar encuesta',
                'hint' => 'Máximo 1 reenvío manual permitido.',
                'mode' => 'resend',
            ]);
        }

        // Caso 2: auto failed y no hubo manual sent
        if ($autoStatus === 'failed') {
            return response()->json([
                'show' => true,
                'text' => 'Enviar encuesta (reintento manual)',
                'hint' => 'El envío automático falló.',
                'mode' => 'retry',
            ]);
        }

        // Caso 1: no hay envíos aún (o auto queued)
        return response()->json([
            'show' => true,
            'text' => 'Enviar encuesta ahora',
            'hint' => 'El sistema la enviará automáticamente a las 2h si no la envías ahora.',
            'mode' => 'send',
        ]);
    }

    public function sendManual(Appointment $appointment, Request $request)
    {
        if (($appointment->status ?? '') !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'La encuesta solo se puede enviar si la cita está completada.'
            ], 422);
        }

        $to = trim((string) ($appointment->patient_email ?? ''));
        if ($to === '') {
            return response()->json([
                'success' => false,
                'message' => 'La cita no tiene email del paciente.'
            ], 422);
        }

        // Regla: 1 auto + 1 manual máximo (manual solo una vez exitosa)
        $manualSentExists = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'manual')
            ->where('status', 'sent')
            ->exists();

        if ($manualSentExists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya se realizó el reenvío manual de la encuesta (límite alcanzado).'
            ], 422);
        }

        // Creamos o reutilizamos una fila manual "queued"
        $manualRow = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'manual')
            ->orderByDesc('id')
            ->first();

        if (!$manualRow) {
            DB::table('appointment_survey_emails')->insert([
                'appointment_id'   => $appointment->id,
                'to_email'         => $to,
                'type'             => 'manual',
                'attempt_number'   => 1,
                'status'           => 'queued',
                'sent_at'          => null,
                'error_message'    => null,
                'sent_by_admin_id' => Auth::id(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $manualRowId = DB::getPdo()->lastInsertId();
        } else {
            // Si existe pero estaba failed/queued, lo reusamos y lo marcamos queued otra vez (SIN crear nueva fila)
            $manualRowId = $manualRow->id;
            $attempt = (int) ($manualRow->attempt_number ?? 1);
            $attempt = max(1, $attempt);

            DB::table('appointment_survey_emails')
                ->where('id', $manualRowId)
                ->update([
                    'to_email'         => $to,
                    'status'           => 'queued',
                    'attempt_number'   => $attempt, // lo dejamos (o lo subes si quieres)
                    'error_message'    => null,
                    'sent_by_admin_id' => Auth::id(),
                    'updated_at'       => now(),
                ]);
        }

        // ✅ Enviar email
        try {
            $surveyUrl = config('app.appointment_survey_url') ?? env('APPOINTMENT_SURVEY_URL');

            // Envío simple (si ya tienes un Mailable propio, lo reemplazamos luego)
            Mail::to($to)->send(new AppointmentSurveyMail(
                $appointment->patient_full_name,
                $surveyUrl
            ));

            DB::table('appointment_survey_emails')
                ->where('id', $manualRowId)
                ->update([
                    'status'     => 'sent',
                    'sent_at'    => now(),
                    'updated_at' => now(),
                ]);
            
            // ✅ Si existía auto queued, lo cancelamos porque ya se envió manual
            DB::table('appointment_survey_emails')
                ->where('appointment_id', $appointment->id)
                ->where('type', 'auto')
                ->where('status', 'queued')
                ->update([
                    'status'        => 'failed',
                    'error_message' => 'Skipped: manual already sent',
                    'updated_at'    => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Encuesta enviada correctamente.',
            ]);

        } catch (\Throwable $e) {

            DB::table('appointment_survey_emails')
                ->where('id', $manualRowId)
                ->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at'    => now(),
                ]);

            return response()->json([
                'success' => false,
                'message' => 'Falló el envío de la encuesta.',
            ], 500);
        }
    }
}