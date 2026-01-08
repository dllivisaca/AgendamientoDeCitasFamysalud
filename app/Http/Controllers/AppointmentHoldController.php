<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use App\Models\AppointmentHold;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentHoldController extends Controller
{
    private function cleanupExpired(): void
    {
        AppointmentHold::where('expires_at', '<', now())->delete();
    }

    // POST /appointment-holds
    public function create(Request $request)
    {
        $this->cleanupExpired();

        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'string', 'max:255'], // start (H:i) o rango
            'appointment_end_time' => ['nullable', 'string', 'max:255'], // end (H:i) opcional
        ]);

        $sessionId = $request->session()->getId();
        $holdMinutes = 20;
        $expiresAt = now()->addMinutes($holdMinutes);

        // Normalizar start/end:
        // - Si viene "4:45 PM - 5:05 PM" lo convertimos a H:i
        // - Si viene start "17:10" y viene appointment_end_time "17:30", lo usamos
        $startTime = $data['appointment_time'];
        $endTime = $data['appointment_end_time'] ?? null;

        // Caso 1: viene en formato rango "4:45 PM - 5:05 PM"
        if (str_contains($startTime, ' - ')) {
            [$s, $e] = array_map('trim', explode(' - ', $startTime, 2));

            // Convertir a 24h si viene con AM/PM
            if (str_contains($s, 'AM') || str_contains($s, 'PM')) {
                $startTime = \Carbon\Carbon::createFromFormat('g:i A', $s)->format('H:i');
                $endTime   = \Carbon\Carbon::createFromFormat('g:i A', $e)->format('H:i');
            } else {
                // Si ya viniera en 24h pero con rango
                $startTime = $s;
                $endTime   = $e;
            }
        } else {
            // Caso 2: viene solo start (ej: "17:10") → aseguramos end si vino aparte
            if ($endTime && (str_contains($endTime, 'AM') || str_contains($endTime, 'PM'))) {
                $endTime = \Carbon\Carbon::createFromFormat('g:i A', trim($endTime))->format('H:i');
            }
        }

        // Reasignar al array para que se guarde consistente
        $data['appointment_time'] = $startTime;
        $data['appointment_end_time'] = $endTime;

        // Duración real del turno (minutos). Recomendado: viene del empleado.
        // Importante: NO incluye break, solo tiempo de atención.
        $employee = Employee::findOrFail($data['employee_id']);
        $slotDuration = (int) ($employee->slot_duration ?? 20);

        // appointment_time viene como "HH:MM"
        $start = Carbon::createFromFormat('H:i', $data['appointment_time']);
        $end = $start->copy()->addMinutes($slotDuration);

        $startHHMM = $start->format('H:i');
        $endHHMM = $end->format('H:i');

        // Forzar que SIEMPRE se guarde el end_time calculado en backend
        $data['appointment_time'] = $startHHMM;
        $data['appointment_end_time'] = $endHHMM;

        // 1) Si ya existe una cita (no cancelada) en ese turno -> no permitir hold
        $existsAppointment = Appointment::where('employee_id', $data['employee_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $startHHMM)
            ->where('status', '!=', 'Cancelled')
            ->exists();

        if ($existsAppointment) {
            return response()->json([
                'ok' => false,
                'message' => 'Ese turno ya no está disponible.',
            ], 409);
        }

        // 2) Crear hold (1 por turno) con índice único uq_hold_slot
        try {
            DB::beginTransaction();

            // Si el hold del turno existe y es de la misma sesión, solo renovamos
            $existing = AppointmentHold::where('employee_id', $data['employee_id'])
                ->where('appointment_date', $data['appointment_date'])
                ->where('appointment_time', $startHHMM)
                ->first();

            if ($existing) {
                if ($existing->session_id !== $sessionId) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'message' => 'Ese turno ya fue reservado temporalmente por otra persona.',
                    ], 409);
                }

                $existing->expires_at = $expiresAt;
                $existing->appointment_time = $data['appointment_time'];
                $existing->appointment_end_time = $data['appointment_end_time'];
                $existing->save();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'hold_id' => $existing->id,
                    'expires_at' => $expiresAt->toDateTimeString(),
                    'renewed' => true,
                ]);
            }

            $created = AppointmentHold::create([
                'employee_id' => $data['employee_id'],
                'service_id' => $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'appointment_end_time' => $data['appointment_end_time'],
                'session_id' => $sessionId,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'hold_id' => $created->id,
                'expires_at' => $expiresAt->toDateTimeString(),
                'renewed' => false,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo reservar el turno. Intenta de nuevo.',
            ], 500);
        }
    }

    // POST /appointment-holds/release
    public function release(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'string', 'max:255'],
        ]);

        $sessionId = $request->session()->getId();

        AppointmentHold::where('employee_id', $data['employee_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->where('session_id', $sessionId)
            ->delete();

        return response()->json(['ok' => true]);
    }

    // helper para validar hold en el momento de crear cita (tarjeta/transfer)
    public static function assertActiveHold(string $sessionId, int $employeeId, string $date, string $time): bool
    {
        AppointmentHold::where('expires_at', '<', now())->delete();

        return AppointmentHold::where('employee_id', $employeeId)
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->exists();
    }

    // DELETE /holds/{id}
    public function destroy(Request $request, int $id)
    {
        $sessionId = $request->session()->getId();

        AppointmentHold::where('id', $id)
            ->where('session_id', $sessionId)
            ->delete();

        return response()->json(['ok' => true]);
    }
}