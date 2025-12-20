<?php

namespace App\Http\Controllers;

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
            'appointment_time' => ['required', 'string', 'max:255'],
        ]);

        $sessionId = $request->session()->getId();
        $holdMinutes = 15;
        $expiresAt = now()->addMinutes($holdMinutes);

        // 1) Si ya existe una cita (no cancelada) en ese turno -> no permitir hold
        $existsAppointment = Appointment::where('employee_id', $data['employee_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
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
                ->where('appointment_time', $data['appointment_time'])
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