<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ReminderLog
{
    /**
     * Crea o actualiza (1 fila por appointment_id + reminder_kind).
     * Útil para: AUTO_24H, AUTO_3H, MANUAL_24H, MANUAL_3H
     */
    public static function upsert(int $appointmentId, string $kind, array $data = []): void
    {
        $now = now();

        // Garantiza que nunca se creen duplicados gracias al UNIQUE (appointment_id, reminder_kind)
        DB::table('appointment_reminder_logs')->updateOrInsert(
            [
                'appointment_id' => $appointmentId,
                'reminder_kind'  => $kind,
            ],
            array_merge([
                'updated_at' => $now,
            ], $data)
        );
    }

    /**
     * Registra un intento (incrementa attempt_count y last_attempt_at).
     */
    public static function recordAttempt(int $appointmentId, string $kind): void
    {
        $now = now();

        // Asegura que exista la fila primero
        self::upsert($appointmentId, $kind, [
            'status'     => DB::raw("COALESCE(status, 'PENDING')"),
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
        ]);

        // Incrementa el contador y guarda timestamp
        DB::table('appointment_reminder_logs')
            ->where('appointment_id', $appointmentId)
            ->where('reminder_kind', $kind)
            ->update([
                'attempt_count'   => DB::raw('attempt_count + 1'),
                'last_attempt_at' => $now,
                'updated_at'      => $now,
            ]);
    }

    /**
     * Marca como enviado exitoso.
     */
    public static function markSent(int $appointmentId, string $kind, ?string $providerMessageId = null): void
    {
        $now = now();

        self::upsert($appointmentId, $kind, [
            'status'              => 'SENT',
            'sent_at'             => $now,
            'provider_message_id' => $providerMessageId,
            'last_error'          => null,
            'updated_at'          => $now,
            'created_at'          => DB::raw('COALESCE(created_at, NOW())'),
        ]);
    }

    /**
     * Marca como fallido.
     */
    public static function markFailed(int $appointmentId, string $kind, string $errorMessage): void
    {
        $now = now();

        self::upsert($appointmentId, $kind, [
            'status'     => 'FAILED',
            'last_error' => $errorMessage,
            'updated_at' => $now,
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
        ]);
    }

    /**
     * Para guardar/actualizar scheduled_for (cuando calculas 24h / 3h).
     */
    public static function setScheduledFor(int $appointmentId, string $kind, $scheduledFor): void
    {
        self::upsert($appointmentId, $kind, [
            'scheduled_for' => $scheduledFor,
            'updated_at'    => now(),
            'created_at'    => DB::raw('COALESCE(created_at, NOW())'),
        ]);
    }
}