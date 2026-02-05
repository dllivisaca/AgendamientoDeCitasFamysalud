<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendQueuedSurveyEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveys:send-queued-auto {--limit=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        if ($limit <= 0) $limit = 50;

        $now = now();

        $rows = \Illuminate\Support\Facades\DB::table('appointment_survey_emails as ase')
            ->where('ase.type', 'auto')
            ->where('ase.status', 'queued')
            ->orderBy('ase.id')
            ->limit($limit)
            ->get([
                'ase.id',
                'ase.appointment_id',
                'ase.to_email',
            ]);

        if ($rows->isEmpty()) {
            $this->info('No queued auto survey emails.');
            return 0;
        }

        $processed = 0;

        foreach ($rows as $row) {
            $processed++;

            // 1) Cita debe existir + estar completed + tener completed_at
            $appt = \Illuminate\Support\Facades\DB::table('appointments')
                ->where('id', $row->appointment_id)
                ->first(['id', 'status', 'completed_at', 'patient_full_name', 'patient_email']);

            if (!$appt) {
                \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                    ->where('id', $row->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'Appointment not found.',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $statusNow = strtolower(trim((string) ($appt->status ?? '')));
            if ($statusNow !== 'completed' || empty($appt->completed_at)) {
                // Si ya no está completed (o no tiene completed_at), NO enviamos.
                // Lo dejamos en queued para que vuelva a intentar si algún día regresa a completed.
                continue;
            }

            // 2) Deben haber pasado 2 horas desde completed_at
            try {
                $completedAt = \Illuminate\Support\Carbon::parse($appt->completed_at);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                    ->where('id', $row->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'Invalid completed_at format.',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            if ($now->lt($completedAt->copy()->addHours(2))) {
                continue;
            }

            // 3) Si ya existe cualquier "sent" (auto o manual) para esta cita -> no enviar
            $alreadySent = \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                ->where('appointment_id', $row->appointment_id)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySent) {
                // No marcamos failed; simplemente no enviamos.
                continue;
            }

            // 4) Debe haber email destino
            $to = trim((string) ($row->to_email ?? ''));
            if ($to === '') {
                $to = trim((string) ($appt->patient_email ?? ''));
            }
            if ($to === '') {
                \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                    ->where('id', $row->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'Missing recipient email.',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            // 5) Intentar enviar (Mail::raw para prueba mínima; si falla, lo marcamos failed)
            try {
                $patientName = trim((string) ($appt->patient_full_name ?? ''));
                $subject = 'Encuesta de satisfacción - FamySALUD';

                \Illuminate\Support\Facades\Mail::raw(
                    "Hola {$patientName},\n\nGracias por tu visita. Por favor responde esta encuesta.\n\n— FamySALUD",
                    function ($message) use ($to, $subject) {
                        $message->to($to)->subject($subject);
                    }
                );

                \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                    ->where('id', $row->id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => $now,
                        'error_message' => null,
                        'updated_at' => $now,
                    ]);

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\DB::table('appointment_survey_emails')
                    ->where('id', $row->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => substr($e->getMessage(), 0, 2000),
                        'updated_at' => $now,
                    ]);
            }
        }

        $this->info("Processed rows: {$processed}");
        return 0;
    }
}
