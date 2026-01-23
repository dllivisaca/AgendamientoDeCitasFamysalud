<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentHold;
use App\Models\Employee;
use Spatie\OpeningHours\OpeningHours;
use Carbon\Carbon;

class AdminAvailabilityController extends Controller
{
    /**
     * Admin: slots disponibles para un Employee en una fecha.
     * Reglas Admin:
     * - Solo fechas futuras (>= hoy)
     * - SIN regla de 24 horas
     * - SIN regla de "hasta próximo sábado"
     */
    public function getEmployeeAvailability(Employee $employee, $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        // ✅ Solo futuras (desde hoy)
        if ($date->lt(now()->startOfDay())) {
            return response()->json([
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'available_slots' => [],
                'slot_duration' => $employee->slot_duration,
                'break_duration' => $employee->break_duration,
                'message' => 'Fecha pasada no permitida para reagendar (admin).',
            ]);
        }

        if (!$employee->slot_duration) {
            return response()->json(['error' => 'Slot duration not set for this employee'], 400);
        }

        try {
            // ---- OpeningHours config (igual que paciente) ----
            $formatTimeRange = function ($timeRange) {
                if (str_contains($timeRange, 'AM') || str_contains($timeRange, 'PM')) {
                    $timeRange = str_replace([' AM', ' PM', ' '], '', $timeRange);
                }

                $times = explode('-', $timeRange);
                $formattedTimes = array_map(function ($time) {
                    $parts = explode(':', $time);
                    $hours = str_pad(trim($parts[0]), 2, '0', STR_PAD_LEFT);
                    return $hours . ':' . $parts[1];
                }, $times);

                return implode('-', $formattedTimes);
            };

            $holidaysExceptions = $employee->holidays->mapWithKeys(function ($holiday) use ($formatTimeRange) {
                $hours = !empty($holiday->hours)
                    ? collect($holiday->hours)->map(function ($timeRange) use ($formatTimeRange) {
                        return $formatTimeRange($timeRange);
                    })->toArray()
                    : [];

                return [$holiday->date => $hours];
            })->toArray();

            $daysInEnglish = $this->mapSpanishDaysToEnglish($employee->days ?? []);

            $openingHours = OpeningHours::create(array_merge(
                $daysInEnglish,
                ['exceptions' => $holidaysExceptions]
            ));

            $availableRanges = $openingHours->forDate($date);

            if ($availableRanges->isEmpty()) {
                return response()->json(['available_slots' => []]);
            }

            $slots = $this->generateTimeSlotsAdmin(
                $availableRanges,
                $employee->slot_duration,
                $employee->break_duration ?? 0,
                $date,
                $employee->id
            );

            return response()->json([
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'available_slots' => $slots,
                'slot_duration' => $employee->slot_duration,
                'break_duration' => $employee->break_duration,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing availability: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: fechas del mes que tienen al menos 1 slot disponible.
     * Reglas Admin:
     * - Solo futuras (>= hoy)
     * - SIN regla de 24 horas
     * - SIN regla de "hasta próximo sábado"
     */
    public function getEmployeeAvailableDates(Request $request, Employee $employee)
    {
        $month = (int) $request->query('month'); // 1-12
        $year  = (int) $request->query('year');  // 2025 etc.

        if (!$month || !$year) {
            return response()->json(['error' => 'month y year son requeridos'], 400);
        }

        if (!$employee->slot_duration) {
            return response()->json(['error' => 'Slot duration not set for this employee'], 400);
        }

        // ✅ solo futuras desde hoy (inicio del día)
        $minAllowedDate = now()->startOfDay();

        // (Opcional) limpiar holds expirados, si decides usarlos también en admin
        AppointmentHold::where('expires_at', '<', now())->delete();

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // recortar: no devolver días pasados
        if ($end->lt($minAllowedDate)) {
            return response()->json([
                'available_dates' => [],
                'min_allowed' => $minAllowedDate->toDateTimeString(),
            ]);
        }

        $start = $start->lt($minAllowedDate) ? $minAllowedDate->copy()->startOfDay() : $start;

        try {
            $daysInEnglish = $this->mapSpanishDaysToEnglish($employee->days ?? []);

            $formatTimeRange = function ($timeRange) {
                if (str_contains($timeRange, 'AM') || str_contains($timeRange, 'PM')) {
                    $timeRange = str_replace([' AM', ' PM', ' '], '', $timeRange);
                }

                $times = explode('-', $timeRange);
                $formattedTimes = array_map(function ($time) {
                    $parts = explode(':', $time);
                    $hours = str_pad(trim($parts[0]), 2, '0', STR_PAD_LEFT);
                    return $hours . ':' . $parts[1];
                }, $times);

                return implode('-', $formattedTimes);
            };

            $holidaysExceptions = $employee->holidays->mapWithKeys(function ($holiday) use ($formatTimeRange) {
                $hours = !empty($holiday->hours)
                    ? collect($holiday->hours)->map(function ($timeRange) use ($formatTimeRange) {
                        return $formatTimeRange($timeRange);
                    })->toArray()
                    : [];

                return [$holiday->date => $hours];
            })->toArray();

            $openingHours = OpeningHours::create(array_merge(
                $daysInEnglish,
                ['exceptions' => $holidaysExceptions]
            ));

            // ---- citas existentes del rango ----
            $existingAppointments = Appointment::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->where('employee_id', $employee->id)
                ->whereNotIn('status', ['Cancelled'])
                ->get(['appointment_date', 'appointment_time', 'appointment_end_time']);

            // ✅ Admin: por defecto NO usar holds (son del flujo paciente)
            $useHoldsInAdmin = true;

            $activeHolds = collect();
            if ($useHoldsInAdmin) {
                $activeHolds = AppointmentHold::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                    ->where('employee_id', $employee->id)
                    ->where('expires_at', '>', now())
                    ->get(['appointment_date', 'appointment_time', 'appointment_end_time']);
            }

            $bookedByDate = [];
            $slotDuration = (int) $employee->slot_duration;

            foreach ($existingAppointments as $appt) {
                $raw = trim((string) $appt->appointment_time);

                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) !== 2) continue;

                    $dateKey = ($appt->appointment_date instanceof \Carbon\Carbon)
                        ? $appt->appointment_date->toDateString()
                        : substr((string)$appt->appointment_date, 0, 10);

                    $bookedByDate[$dateKey][] = [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                    continue;
                }

                try {
                    $rawStart = substr(trim((string)$raw), 0, 5);
                    $apptStart = Carbon::createFromFormat('H:i', $rawStart);

                    $endRaw = trim((string) ($appt->appointment_end_time ?? ''));
                    $endRaw = $endRaw ? substr($endRaw, 0, 5) : '';

                    $apptEnd = $endRaw
                        ? Carbon::createFromFormat('H:i', $endRaw)
                        : $apptStart->copy()->addMinutes($slotDuration);

                    $bookedByDate[$appt->appointment_date][] = [
                        'start' => $apptStart->format('H:i'),
                        'end'   => $apptEnd->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    continue;
                }
            }

            foreach ($activeHolds as $hold) {
                $raw = trim((string) $hold->appointment_time);

                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) !== 2) continue;

                    $bookedByDate[$hold->appointment_date][] = [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                    continue;
                }

                try {
                    $rawStart = substr(trim((string)$raw), 0, 5);
                    $holdStart = Carbon::createFromFormat('H:i', $rawStart);

                    $endRaw = trim((string) ($hold->appointment_end_time ?? ''));
                    $endRaw = $endRaw ? substr($endRaw, 0, 5) : '';

                    $holdEnd = $endRaw
                        ? Carbon::createFromFormat('H:i', $endRaw)
                        : $holdStart->copy()->addMinutes($slotDuration);

                    $bookedByDate[$hold->appointment_date][] = [
                        'start' => $holdStart->format('H:i'),
                        'end'   => $holdEnd->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    continue;
                }
            }

            $availableDates = [];
            $cursor = $start->copy()->startOfDay();

            while ($cursor->lte($end)) {
                $dateStr = $cursor->toDateString();

                // ✅ solo futuras desde hoy
                if ($cursor->lt($minAllowedDate)) {
                    $cursor->addDay();
                    continue;
                }

                $ranges = $openingHours->forDate($cursor);
                if ($ranges->isEmpty()) {
                    $cursor->addDay();
                    continue;
                }

                $bookedSlots = $bookedByDate[$dateStr] ?? [];

                if ($this->hasAnyAvailableSlotAdmin(
                    $ranges,
                    $employee->slot_duration,
                    $employee->break_duration ?? 0,
                    $cursor,
                    $bookedSlots
                )) {
                    $availableDates[] = $dateStr;
                }

                $cursor->addDay();
            }

            return response()->json([
                'available_dates' => $availableDates,
                'min_allowed' => $minAllowedDate->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin slots generator:
     * - no minAllowed 24h
     * - solo evita horas pasadas si la fecha es hoy
     */
    protected function generateTimeSlotsAdmin($availableRanges, $slotDuration, $breakDuration, Carbon $date, $employeeId)
    {
        $slots = [];
        $now = now();
        $isToday = $date->isToday();

        $existingAppointments = Appointment::where('appointment_date', $date->toDateString())
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['Cancelled'])
            ->get(['appointment_time', 'appointment_end_time']);

        // ✅ Admin: SÍ usar holds para no mostrar turnos tomados temporalmente por pacientes
        $useHoldsInAdmin = true;

        $activeHolds = collect();
        if ($useHoldsInAdmin) {
            $activeHolds = AppointmentHold::where('appointment_date', $date->toDateString())
                ->where('employee_id', $employeeId)
                ->where('expires_at', '>', now())
                ->get(['appointment_time', 'appointment_end_time']);
        }

        $holdSlots = $activeHolds->map(function ($hold) use ($slotDuration) {
            $raw = trim((string) $hold->appointment_time);

            if (str_contains($raw, ' - ')) {
                $times = explode(' - ', $raw);
                if (count($times) === 2) {
                    return [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                }
            }

            try {
                $rawStart = substr(trim((string)$raw), 0, 5);
                $start = Carbon::createFromFormat('H:i', $rawStart);

                $endRaw = trim((string) ($hold->appointment_end_time ?? ''));
                $endRaw = $endRaw ? substr($endRaw, 0, 5) : '';

                $end = $endRaw
                    ? Carbon::createFromFormat('H:i', $endRaw)
                    : $start->copy()->addMinutes((int) $slotDuration);

                return [
                    'start' => $start->format('H:i'),
                    'end'   => $end->format('H:i'),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->values()->toArray();

        $bookedSlots = array_merge(
            $existingAppointments->map(function ($appointment) use ($slotDuration) {
                $raw = trim((string) $appointment->appointment_time);

                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) === 2) {
                        return [
                            'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                            'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                        ];
                    }
                }

                try {
                    $rawStart = substr(trim((string)$raw), 0, 5);
                    $start = Carbon::createFromFormat('H:i', $rawStart);

                    $endRaw = trim((string) ($appointment->appointment_end_time ?? ''));
                    $endRaw = $endRaw ? substr($endRaw, 0, 5) : '';

                    $end = $endRaw
                        ? Carbon::createFromFormat('H:i', $endRaw)
                        : $start->copy()->addMinutes((int) $slotDuration);

                    return [
                        'start' => $start->format('H:i'),
                        'end'   => $end->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    return null;
                }
            })->filter()->values()->toArray(),
            $holdSlots
        );

        foreach ($availableRanges as $range) {
            $start = Carbon::parse($date->toDateString() . ' ' . $range->start()->format('H:i'));
            $end   = Carbon::parse($date->toDateString() . ' ' . $range->end()->format('H:i'));

            // si es hoy y el rango ya pasó, saltar
            if ($isToday && $end->lte($now)) {
                continue;
            }

            $currentSlotStart = clone $start;

            $cycleMinutes = (int) $slotDuration + (int) $breakDuration;
            $anchor = $start->copy();

            $snapToCycle = function (Carbon $candidate) use ($anchor, $cycleMinutes) {
                $candidate = $candidate->copy()->second(0);

                if ($candidate->lte($anchor)) {
                    return $anchor->copy();
                }

                $diff = $anchor->diffInMinutes($candidate);
                $rem = $diff % $cycleMinutes;

                if ($rem !== 0) {
                    $candidate->addMinutes($cycleMinutes - $rem);
                }

                return $candidate->second(0);
            };

            // si es hoy, no ofrecer horas pasadas
            if ($isToday && $currentSlotStart->lt($now)) {
                $currentSlotStart = $snapToCycle($now);
            }

            while ($currentSlotStart->copy()->addMinutes($slotDuration)->lte($end)) {
                $slotEnd = $currentSlotStart->copy()->addMinutes($slotDuration);

                $isAvailable = true;
                foreach ($bookedSlots as $bookedSlot) {
                    $bookedStart = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['start']);
                    $bookedEnd   = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['end']);

                    if ($currentSlotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                        $isAvailable = false;
                        break;
                    }
                }

                if ($isAvailable) {
                    $slots[] = [
                        'start' => $currentSlotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'display' => $currentSlotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    ];
                }

                $currentSlotStart->addMinutes($slotDuration + $breakDuration);

                if ($currentSlotStart->copy()->addMinutes($slotDuration)->gt($end)) {
                    break;
                }
            }
        }

        return $slots;
    }

    /**
     * Admin: determina si existe al menos 1 slot disponible en un día (sin minAllowed 24h).
     */
    protected function hasAnyAvailableSlotAdmin($availableRanges, $slotDuration, $breakDuration, Carbon $date, array $bookedSlots): bool
    {
        $now = now();
        $isToday = $date->isToday();

        foreach ($availableRanges as $range) {
            $start = Carbon::parse($date->toDateString() . ' ' . $range->start()->format('H:i'));
            $end   = Carbon::parse($date->toDateString() . ' ' . $range->end()->format('H:i'));

            if ($isToday && $end->lte($now)) continue;

            $cycleMinutes = (int) $slotDuration + (int) $breakDuration;
            $anchor = $start->copy();

            $snapToCycle = function (Carbon $candidate) use ($anchor, $cycleMinutes) {
                $candidate = $candidate->copy()->second(0);

                if ($candidate->lte($anchor)) {
                    return $anchor->copy();
                }

                $diff = $anchor->diffInMinutes($candidate);
                $rem = $diff % $cycleMinutes;

                if ($rem !== 0) {
                    $candidate->addMinutes($cycleMinutes - $rem);
                }

                return $candidate->second(0);
            };

            $current = $start->copy();

            if ($isToday && $current->lt($now)) {
                $current = $snapToCycle($now);
            }

            while ($current->copy()->addMinutes($slotDuration)->lte($end)) {
                $slotEnd = $current->copy()->addMinutes($slotDuration);

                $conflict = false;
                foreach ($bookedSlots as $b) {
                    $bStart = Carbon::parse($date->toDateString() . ' ' . $b['start']);
                    $bEnd   = Carbon::parse($date->toDateString() . ' ' . $b['end']);

                    if ($current->lt($bEnd) && $slotEnd->gt($bStart)) {
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    return true;
                }

                $current->addMinutes($slotDuration + $breakDuration);

                if ($current->copy()->addMinutes($slotDuration)->gt($end)) break;
            }
        }

        return false;
    }

    private function normalizeDayKey(string $day): string
    {
        $day = mb_strtolower(trim($day), 'UTF-8');

        $noAccents = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $day);
        if ($noAccents !== false) {
            $day = $noAccents;
        }

        $day = preg_replace('/[^a-z]/', '', $day);

        return $day;
    }

    private function mapSpanishDaysToEnglish(array $days): array
    {
        $map = [
            'lunes' => 'monday',
            'martes' => 'tuesday',
            'miercoles' => 'wednesday',
            'jueves' => 'thursday',
            'viernes' => 'friday',
            'sabado' => 'saturday',
            'domingo' => 'sunday',
        ];

        $out = [];

        foreach ($days as $dayKey => $ranges) {
            $normalized = $this->normalizeDayKey($dayKey);

            if (!isset($map[$normalized])) {
                continue;
            }

            $out[$map[$normalized]] = $ranges;
        }

        return $out;
    }
}