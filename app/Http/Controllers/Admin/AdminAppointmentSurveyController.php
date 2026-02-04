<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminAppointmentSurveyController extends Controller
{
    public function status(Appointment $appointment)
    {
        // 1) Solo aplica si está completed y tiene completed_at
        if (strtolower((string) $appointment->status) !== 'completed' || empty($appointment->completed_at)) {
            return response()->json([
                'success' => true,
                'can_show_button' => false,
                'button_text' => 'Enviar encuesta',
                'can_send_now' => false,
                'reason' => 'La cita aún no está completada.',
            ]);
        }

        // 2) Si ya se envió manual, se oculta (manual solo 1 vez)
        $manualSent = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'manual')
            ->where('status', 'sent')
            ->exists();

        if ($manualSent) {
            return response()->json([
                'success' => true,
                'can_show_button' => false,
                'button_text' => 'Reenviar encuesta',
                'can_send_now' => false,
                'reason' => '',
            ]);
        }

        // 3) Si se envió auto, el texto cambia a "Reenviar"
        $autoSent = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'auto')
            ->where('status', 'sent')
            ->exists();

        $buttonText = $autoSent ? 'Reenviar encuesta' : 'Enviar encuesta';

        // 4) Regla 2 horas: se muestra siempre, pero se deshabilita hasta que cumpla
        $canAfter = $appointment->completed_at->copy()->addHours(2);

        if (now()->lt($canAfter)) {
            return response()->json([
                'success' => true,
                'can_show_button' => true,
                'button_text' => $buttonText,
                'can_send_now' => false,
                'reason' => 'Disponible 2 horas después de completar la cita.',
            ]);
        }

        // 5) Ya puede enviar
        return response()->json([
            'success' => true,
            'can_show_button' => true,
            'button_text' => $buttonText,
            'can_send_now' => true,
            'reason' => '',
        ]);
    }

    public function sendManual(Request $request, Appointment $appointment)
    {
        // Solo después de 2 horas
        if (strtolower((string) $appointment->status) !== 'completed' || empty($appointment->completed_at)) {
            return response()->json(['success' => false, 'message' => 'Esta cita aún no está completada.'], 409);
        }

        $canAfter = $appointment->completed_at->copy()->addHours(2);
        if (now()->lt($canAfter)) {
            return response()->json(['success' => false, 'message' => 'Disponible 2 horas después de completar la cita.'], 409);
        }

        // Manual solo 1 vez
        $manualSent = DB::table('appointment_survey_emails')
            ->where('appointment_id', $appointment->id)
            ->where('type', 'manual')
            ->where('status', 'sent')
            ->exists();

        if ($manualSent) {
            return response()->json(['success' => false, 'message' => 'La encuesta ya fue reenviada 1 vez.'], 409);
        }

        $to = trim((string) ($appointment->patient_email ?? ''));
        if ($to === '') {
            return response()->json(['success' => false, 'message' => 'La cita no tiene email de paciente.'], 422);
        }

        $surveyUrl = trim((string) config('services.surveys.google_form_url'));
        if ($surveyUrl === '') {
            return response()->json(['success' => false, 'message' => 'Falta configurar la URL del Google Form.'], 500);
        }

        // Crear registro queued
        $id = DB::table('appointment_survey_emails')->insertGetId([
            'appointment_id' => $appointment->id,
            'to_email' => $to,
            'type' => 'manual',
            'attempt_number' => 1,
            'status' => 'queued',
            'sent_at' => null,
            'error_message' => null,
            'sent_by_admin_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Email simple (sin mailable todavía)
            Mail::raw(
                "Hola {$appointment->patient_full_name},\n\nGracias por tu visita. ¿Nos ayudas con una encuesta rápida?\n{$surveyUrl}\n\n¡Gracias!",
                function ($msg) use ($to) {
                    $msg->to($to)->subject('Encuesta de satisfacción');
                }
            );

            DB::table('appointment_survey_emails')->where('id', $id)->update([
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Encuesta enviada.']);
        } catch (\Throwable $e) {
            DB::table('appointment_survey_emails')->where('id', $id)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo enviar la encuesta.'], 500);
        }
    }
}