<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class QueueAutoAppointmentReminders extends Command
{
    protected $signature = 'reminders:queue-auto {--window=10} {--limit=500}';
    protected $description = 'Encola recordatorios AUTO_24H y AUTO_3H en appointment_reminder_logs (status QUEUED)';

    public function handle()
    {
        if (!filter_var(env('ENABLE_AUTO_REMINDERS', false), FILTER_VALIDATE_BOOL)) {
            $this->info('Auto reminders disabled (ENABLE_AUTO_REMINDERS=false).');
            return 0;
        }

        $window = (int) $this->option('window');
        if ($window <= 0) $window = 10;

        $limit = (int) $this->option('limit');
        if ($limit <= 0) $limit = 500;

        $now = now();

        // Detectar cómo tu tabla "appointments" guarda la fecha/hora (sin adivinar a ciegas)
        $appointmentsTable = 'appointments';

        $hasScheduledAt = Schema::hasColumn($appointmentsTable, 'scheduled_at');
        $hasStartAt     = Schema::hasColumn($appointmentsTable, 'start_at');
        $hasStartsAt    = Schema::hasColumn($appointmentsTable, 'starts_at');

        $hasDate = Schema::hasColumn($appointmentsTable, 'appointment_date');
        $hasTime = Schema::hasColumn($appointmentsTable, 'appointment_time');

        $datetimeMode = null; // 'single' o 'split'
        $dtColumn = null;

        if ($hasScheduledAt) {
            $datetimeMode = 'single';
            $dtColumn = 'scheduled_at';
        } elseif ($hasStartAt) {
            $datetimeMode = 'single';
            $dtColumn = 'start_at';
        } elseif ($hasStartsAt) {
            $datetimeMode = 'single';
            $dtColumn = 'starts_at';
        } elseif ($hasDate && $hasTime) {
            $datetimeMode = 'split';
        } else {
            $this->error("No se encontró columna datetime en appointments. Se esperaba scheduled_at/start_at/starts_at o (appointment_date + appointment_time).");
            return 1;
        }

        $targets = [
            // kind => [fromHoursAhead, toHoursAhead]
            // "faltan entre 24h y 20h" => citas entre now+20h y now+24h
            'AUTO_24H' => [20, 24],

            // "faltan entre 3h y 1h" => citas entre now+1h y now+3h
            'AUTO_3H'  => [1, 3],
        ];

        $totalQueued = 0;

        foreach ($targets as $kind => $range) {

            [$fromHours, $toHours] = $range;

            // Ventana por rango de horas restantes (como acordaron)
            $from = Carbon::parse($now)->addHours($fromHours); // límite inferior (más cercano)
            $to   = Carbon::parse($now)->addHours($toHours);   // límite superior (más lejano)

            // scheduled_for: lo ponemos "ya" para que el sender lo mande en cuanto lo detecte
            $target = Carbon::parse($now);

            // Query citas dentro de la ventana
            $q = DB::table("$appointmentsTable as a")->select('a.id');

            if ($datetimeMode === 'single') {
                $q->whereBetween("a.$dtColumn", [$from->toDateTimeString(), $to->toDateTimeString()]);
            } else {
                // split: appointment_date + appointment_time
                // Construye datetime como string YYYY-MM-DD HH:MM:SS
                $q->whereRaw("STR_TO_DATE(CONCAT(a.appointment_date,' ',a.appointment_time), '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?", [
                    $from->toDateTimeString(),
                    $to->toDateTimeString(),
                ]);
            }

            // Filtros "seguros" (ajusta si tus status son distintos)
            if (Schema::hasColumn($appointmentsTable, 'deleted_at')) {
                $q->whereNull('a.deleted_at');
            }

            if (Schema::hasColumn($appointmentsTable, 'status')) {
                $q->whereNotIn('a.status', ['cancelled', 'canceled']);
            }

            $appts = $q->orderBy('a.id')->limit($limit)->get();

            if ($appts->isEmpty()) {
                $this->info("[$kind] No hay citas en ventana.");
                continue;
            }

            $queued = 0;

            foreach ($appts as $appt) {

                // Evitar duplicados: si ya existe log para ese appointment_id + reminder_kind, no insertar
                $exists = DB::table('appointment_reminder_logs')
                    ->where('appointment_id', $appt->id)
                    ->where('reminder_kind', $kind)
                    ->exists();

                if ($exists) continue;

                DB::table('appointment_reminder_logs')->insert([
                    'appointment_id'      => $appt->id,
                    'reminder_kind'       => $kind,
                    'scheduled_for'       => $target->toDateTimeString(),
                    'status'              => 'QUEUED',
                    'attempt_count'       => 0,
                    'last_attempt_at'     => null,
                    'sent_at'             => null,
                    'sent_by_admin_id'    => null,
                    'provider_message_id' => null,
                    'last_error'          => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);

                $queued++;
            }

            $this->info("[$kind] encolados: $queued (de {$appts->count()} candidatos)");
            $totalQueued += $queued;
        }

        $this->info("TOTAL encolados: $totalQueued");
        return 0;
    }
}