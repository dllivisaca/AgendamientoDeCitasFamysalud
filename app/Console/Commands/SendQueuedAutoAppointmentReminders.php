<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentReminderMail;

class SendQueuedAutoAppointmentReminders extends Command
{
    protected $signature = 'reminders:send-queued-auto {--limit=50} {--delay_ms=800}';
    protected $description = 'Envía recordatorios AUTO_24H y AUTO_3H encolados en appointment_reminder_logs';

    public function handle()
    {
        if (!filter_var(env('ENABLE_AUTO_REMINDERS', false), FILTER_VALIDATE_BOOL)) {
            $this->info('Auto reminders disabled (ENABLE_AUTO_REMINDERS=false).');
            return 0;
        }

        $limit = (int) ($this->option('limit') ?: env('AUTO_REMINDERS_SEND_LIMIT', 1));
        if ($limit <= 0) $limit = 1;

        $delayMs = (int) ($this->option('delay_ms') ?: env('AUTO_REMINDERS_DELAY_MS', 2000));
        if ($delayMs < 0) $delayMs = 0;

        $now = now();

        $rows = DB::table('appointment_reminder_logs as arl')
            ->whereIn('arl.reminder_kind', ['AUTO_24H', 'AUTO_3H'])
            ->where('arl.status', 'QUEUED')
            ->where('arl.scheduled_for', '<=', $now)
            ->orderBy('arl.id')
            ->limit($limit)
            ->get([
                'arl.id',
                'arl.appointment_id',
                'arl.reminder_kind',
                'arl.attempt_count',
            ]);

        if ($rows->isEmpty()) {
            $this->info('No queued auto reminders.');
            return 0;
        }

        $processed = 0;

        foreach ($rows as $row) {
            $processed++;

            // 1) La cita debe existir
            $appt = DB::table('appointments')
                ->where('id', $row->appointment_id)
                ->first(['id', 'status', 'patient_email', 'patient_full_name']);

            if (!$appt) {
                DB::table('appointment_reminder_logs')
                    ->where('id', $row->id)
                    ->update([
                        'status'     => 'FAILED',
                        'last_error' => 'Appointment not found.',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            // 2) Si la cita está cancelada -> SKIPPED
            $statusNow = strtolower(trim((string) ($appt->status ?? '')));
            if (in_array($statusNow, ['cancelled', 'canceled'], true)) {
                DB::table('appointment_reminder_logs')
                    ->where('id', $row->id)
                    ->update([
                        'status'     => 'SKIPPED',
                        'last_error' => 'Skipped: appointment cancelled.',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            // 3) Si ya existe un MANUAL sent del mismo tipo => SKIPPED (para no duplicar)
            $manualKind = ($row->reminder_kind === 'AUTO_24H') ? 'MANUAL_24H' : 'MANUAL_3H';

            $manualSent = DB::table('appointment_reminder_logs')
                ->where('appointment_id', $row->appointment_id)
                ->where('reminder_kind', $manualKind)
                ->where('status', 'SENT')
                ->exists();

            if ($manualSent) {
                DB::table('appointment_reminder_logs')
                    ->where('id', $row->id)
                    ->update([
                        'status'     => 'SKIPPED',
                        'last_error' => "Skipped: {$manualKind} already sent.",
                        'updated_at' => $now,
                    ]);
                continue;
            }

            // 4) Preparar attempt_count
            DB::table('appointment_reminder_logs')
                ->where('id', $row->id)
                ->update([
                    'attempt_count'   => ((int) $row->attempt_count) + 1,
                    'last_attempt_at' => $now,
                    'updated_at'      => $now,
                ]);

            // 5) ✅ Enviar usando tu lógica existente
            try {
                // 👇 AQUÍ conectamos tu envío real (email que ya existe)
                // Por ahora, dejamos un placeholder que NO “finja” envío:
                $this->sendReminderEmail($row->appointment_id, $row->reminder_kind);

                DB::table('appointment_reminder_logs')
                    ->where('id', $row->id)
                    ->update([
                        'status'     => 'SENT',
                        'sent_at'    => $now,
                        'last_error' => null,
                        'updated_at' => $now,
                    ]);

            } catch (\Throwable $e) {
                $error = substr($e->getMessage(), 0, 2000);
                $isRateLimit = stripos($error, 'Too many emails per second') !== false;

                DB::table('appointment_reminder_logs')
                    ->where('id', $row->id)
                    ->update([
                        'status'     => $isRateLimit ? 'QUEUED' : 'FAILED',
                        'last_error' => $error,
                        'updated_at' => $now,
                    ]);

                if ($isRateLimit) {
                    $this->warn('Rate limit detectado (Mailtrap). Se detiene la corrida para reintentar luego.');
                    break;
                }
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $this->info("Evaluated queued rows: {$processed}");
        return 0;
    }

    private function sendReminderEmail(int $appointmentId, string $kind): void
    {
        if (!in_array($kind, ['AUTO_24H', 'AUTO_3H'], true)) {
            throw new \Exception("kind inválido para auto: {$kind}");
        }

        $appt = DB::table('appointments')->where('id', $appointmentId)->first();
        if (!$appt) {
            throw new \Exception('Cita no encontrada');
        }

        // ✅ Validación de ventana (igual lógica que tu controller)
        $tz = 'America/Guayaquil';
        $dateStr = $appt->appointment_date ?? $appt->date ?? null;
        $timeStr = $appt->appointment_time ?? $appt->time ?? null;

        if (!$dateStr || !$timeStr) {
            throw new \Exception('La cita no tiene appointment_date/appointment_time');
        }

        $apptDt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateStr.' '.$timeStr.':00', $tz);
        $nowTz  = now($tz);

        $diffHours = $nowTz->diffInMinutes($apptDt, false) / 60;

        if ($kind === 'AUTO_24H') {
            // Debe ser mañana + 24→20h
            if (!$apptDt->isTomorrow()) {
                throw new \Exception('Fuera de regla: AUTO_24H solo si la cita es mañana.');
            }
            if (!($diffHours <= 24 && $diffHours > 20)) {
                throw new \Exception('Fuera de ventana AUTO_24H (24h a 20h).');
            }
        } else { // AUTO_3H
            if (!($diffHours <= 3 && $diffHours > 1)) {
                throw new \Exception('Fuera de ventana AUTO_3H (3h a 1h).');
            }
        }

        // ✅ Email destino
        $toEmail = (string) ($appt->patient_email ?? '');
        if (trim($toEmail) === '') {
            throw new \Exception('No hay patient_email para enviar recordatorio.');
        }

        // ✅ Data para plantilla (copiado de tu controller)
        $timeShort = $timeStr ? substr((string) $timeStr, 0, 5) : null;

        $endStr = $appt->appointment_end_time ?? $appt->end_time ?? null;
        $endShort = $endStr ? substr((string) $endStr, 0, 5) : null;

        $startsAt = ($dateStr && $timeShort) ? ($dateStr . ' ' . $timeShort . ':00') : null;
        $endsAt   = ($dateStr && $endShort)  ? ($dateStr . ' ' . $endShort . ':00')  : null;

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

            'mode' => $appt->appointment_mode ?? null,
            'patient_timezone' => $appt->patient_timezone ?? null,

            'service' => $serviceTitle,
            'area' => $categoryTitle,
        ];

        Mail::to($toEmail)->send(new AppointmentReminderMail($mailData, $kind));
    }
}