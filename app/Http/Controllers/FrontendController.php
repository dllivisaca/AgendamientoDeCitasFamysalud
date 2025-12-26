<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppointmentHold;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Appointment;
use Spatie\OpeningHours\OpeningHours;
use Carbon\Carbon;
use Illuminate\Support\Number;
use View;


class FrontendController extends Controller
{

    public function __construct()
    {
        $setting = Setting::firstOrFail();
        view::share('setting',$setting);
    }

    public function index()
    {
        $categories = Category::with([
            'services' => function($query) {
                $query->where('status', 1) // Only active services
                    ->with('employees'); // Load all employees for each service
            }
        ])->where('status', 1)->get();

        $employees = Employee::with('services')->with('user')->get();

        return view('frontend.index', compact('categories','employees'));
    }


    public function getServices(Request $request, Category $category)
    {
        $setting = Setting::firstOrFail();

        $services = $category->services()
            ->where('status', 1)
            ->with('category')
            ->get()
            ->map(function ($service) use ($setting) {
                if (isset($service->price)) {
                    $service->price = Number::currency($service->price, $setting->currency);
                }

                if (isset($service->sale_price)) {
                    $service->sale_price = Number::currency($service->sale_price, $setting->currency);
                }

                return $service;
            });

        return response()->json([
            'success' => true,
            'services' => $services
        ]);
    }


    public function getEmployees(Request $request, Service $service)
    {
        $employees = $service->employees()
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            })
            ->with('user') // Eager load user details
            ->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employees available for this service'
            ]);
        }

        return response()->json([
            'success' => true,
            'employees' => $employees,
            'service' => $service
        ]);
    }




    public function getEmployeeAvailability(Employee $employee, $date = null)
    {
        // Use current date if not provided
        $date = $date ? Carbon::parse($date) : now();

        $now = now();

        AppointmentHold::where('expires_at', '<', now())->delete();

        // ✅ Máximo permitido: próximo sábado (si hoy es sábado, será el sábado siguiente)
        $maxAllowedDate = $now->copy()->next(Carbon::SATURDAY)->endOfDay();

        // Si piden una fecha más allá del máximo permitido, no devolvemos slots
        if ($date->gt($maxAllowedDate)) {
            return response()->json([
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'available_slots' => [],
                'slot_duration' => $employee->slot_duration,
                'break_duration' => $employee->break_duration,
                'message' => 'Fecha fuera del rango permitido',
                'max_allowed_date' => $maxAllowedDate->toDateString(),
            ]);
        }

        // Validate slot duration exists
        if (!$employee->slot_duration) {
            return response()->json(['error' => 'Slot duration not set for this employee'], 400);
        }

        try {
            // Function to ensure proper time formatting
            $formatTimeRange = function ($timeRange) {
                // Handle appointment format (e.g., "06:00 AM - 06:30 AM")
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

            // Process holidays exceptions
            $holidaysExceptions = $employee->holidays->mapWithKeys(function ($holiday) use ($formatTimeRange) {
                $hours = !empty($holiday->hours)
                    ? collect($holiday->hours)->map(function ($timeRange) use ($formatTimeRange) {
                        return $formatTimeRange($timeRange);
                    })->toArray()
                    : [];

                return [$holiday->date => $hours];
            })->toArray();

            // using spatie opening hours package to process data and expections
            /* $openingHours = OpeningHours::create(array_merge(
                $employee->days,
                ['exceptions' => $holidaysExceptions]
            )); */

            $daysInEnglish = $this->mapSpanishDaysToEnglish($employee->days ?? []);

            $openingHours = OpeningHours::create(array_merge(
                $daysInEnglish,
                ['exceptions' => $holidaysExceptions]
            ));

            // Get available time ranges for the requested date
            $availableRanges = $openingHours->forDate($date);

            // If no availability for this date
            if ($availableRanges->isEmpty()) {
                return response()->json(['available_slots' => []]);
            }

            // Generate time slots - NOW PASSING THE EMPLOYEE ID
            $slots = $this->generateTimeSlots(
                $availableRanges,
                $employee->slot_duration,
                $employee->break_duration ?? 0,
                $date,
                $employee->id  // This is the crucial addition
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


    protected function generateTimeSlots($availableRanges, $slotDuration, $breakDuration, $date, $employeeId)
    {
        $slots = [];
        $now = now();
        $minAllowed = $now->copy()->addHours(24);
        $isToday = $date->isToday();

        // Get existing appointments for this date and employee
        $existingAppointments = Appointment::where('appointment_date', $date->toDateString())
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', ['Cancelled'])
            ->get(['appointment_time', 'appointment_end_time']);

        $activeHolds = AppointmentHold::where('appointment_date', $date->toDateString())
            ->where('employee_id', $employeeId)
            ->where('expires_at', '>', now())
            ->get(['appointment_time', 'appointment_end_time']);

        $holdSlots = $activeHolds->map(function ($hold) use ($slotDuration) {
            $raw = trim((string) $hold->appointment_time);

            // Caso A: viene como rango "8:00 AM - 8:20 AM"
            if (str_contains($raw, ' - ')) {
                $times = explode(' - ', $raw);
                if (count($times) === 2) {
                    return [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                }
            }

            // Caso B: viene como "08:00" (HH:MM)
            // En este caso asumimos que el hold bloquea un slot completo ($slotDuration)
            try {
                $start = Carbon::createFromFormat('H:i', $raw);

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
                // Si algo raro llega, no romper el endpoint: ignorar hold inválido
                return null;
            }
        })->filter()->values()->toArray();

        $bookedSlots = array_merge(
            $existingAppointments->map(function ($appointment) use ($slotDuration) {
                $raw = trim((string) $appointment->appointment_time);

                // Caso viejo: "8:00 AM - 8:20 AM"
                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) === 2) {
                        return [
                            'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                            'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                        ];
                    }
                }

                // Caso nuevo: "14:00" + appointment_end_time
                try {
                    $start = Carbon::createFromFormat('H:i', $raw);
                    $endRaw = trim((string) ($appointment->appointment_end_time ?? ''));
                    $endRaw = substr($endRaw, 0, 5); // "15:10:00" -> "15:10"

                    $end = $endRaw
                        ? Carbon::createFromFormat('H:i', $endRaw)
                        : $start->copy()->addMinutes((int) $slotDuration); // fallback por si hay registros viejos sin end_time

                    return [
                        'start' => $start->format('H:i'),
                        'end'   => $end->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    return null; // no romper endpoint por datos raros
                }
            })->filter()->values()->toArray(),
            $holdSlots
        );

        foreach ($availableRanges as $range) {
            $start = Carbon::parse($date->toDateString() . ' ' . $range->start()->format('H:i'));
            $end = Carbon::parse($date->toDateString() . ' ' . $range->end()->format('H:i'));

            // Si todo el rango termina antes del mínimo permitido, no sirve
            if ($end->lte($minAllowed)) {
                continue;
            }

            // Skip if the entire range is in the past (only for today)
            if ($isToday && $end->lte($now)) {
                continue;
            }

            $currentSlotStart = clone $start;

            $cycleMinutes = (int) $slotDuration + (int) $breakDuration;
            $anchor = $start->copy(); // el inicio real del rango disponible, como 15:40

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

            if ($currentSlotStart->lt($minAllowed)) {
                $currentSlotStart = $snapToCycle($minAllowed);
            }

            if ($isToday && $currentSlotStart->lt($now)) {
                $currentSlotStart = $snapToCycle($now);
            }

            while ($currentSlotStart->copy()->addMinutes($slotDuration)->lte($end)) {
                $slotEnd = $currentSlotStart->copy()->addMinutes($slotDuration);

                // Check if this slot conflicts with any existing booking
                $isAvailable = true;
                foreach ($bookedSlots as $bookedSlot) {
                    $bookedStart = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['start']);
                    $bookedEnd = Carbon::parse($date->toDateString() . ' ' . $bookedSlot['end']);

                    if ($currentSlotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                        $isAvailable = false;
                        break;
                    }
                }

                // Only add slots that are available and in the future (for today)
                if ($isAvailable && $currentSlotStart->gte($minAllowed)) {
                    $slots[] = [
                        'start' => $currentSlotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'display' => $currentSlotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    ];
                }

                // Add break duration if specified
                $currentSlotStart->addMinutes($slotDuration + $breakDuration);

                // Check if next slot would exceed end time
                if ($currentSlotStart->copy()->addMinutes($slotDuration)->gt($end)) {
                    break;
                }
            }
        }

        return $slots;
    }

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

        $now = now();
        $minAllowed = $now->copy()->addHours(24);
        AppointmentHold::where('expires_at', '<', now())->delete();

        // ✅ tu regla del sábado
        $maxAllowedDate = $now->copy()->next(Carbon::SATURDAY)->endOfDay();

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        // Recortar por rango permitido
        if ($end->lt($minAllowed) || $start->gt($maxAllowedDate)) {
            return response()->json([
                'available_dates' => [],
                'min_allowed' => $minAllowed->toDateTimeString(),
                'max_allowed' => $maxAllowedDate->toDateTimeString(),
            ]);
        }

        $start = $start->lt($minAllowed) ? $minAllowed->copy()->startOfDay() : $start;
        $end   = $end->gt($maxAllowedDate) ? $maxAllowedDate->copy()->endOfDay() : $end;

        try {
            // ---- OpeningHours config (igual que en getEmployeeAvailability) ----
            $daysInEnglish = $this->mapSpanishDaysToEnglish($employee->days ?? []);

            // Function to ensure proper time formatting (misma lógica)
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

            // ---- citas existentes del rango (para saber si un día se llenó) ----
            $existingAppointments = Appointment::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->where('employee_id', $employee->id)
                ->whereNotIn('status', ['Cancelled'])
                ->get(['appointment_date', 'appointment_time', 'appointment_end_time']);

            $activeHolds = AppointmentHold::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->where('employee_id', $employee->id)
                ->where('expires_at', '>', now())
                ->get(['appointment_date', 'appointment_time']);

            $bookedByDate = [];
            $slotDuration = (int) $employee->slot_duration;

            foreach ($existingAppointments as $appt) {
                $raw = trim((string) $appt->appointment_time);

                // Caso viejo: rango AM/PM
                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) !== 2) continue;

                    $bookedByDate[$appt->appointment_date][] = [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                    continue;
                }

                // Caso nuevo: HH:MM + end_time
                try {
                    $start = Carbon::createFromFormat('H:i', $raw);
                    $endRaw = trim((string) ($appt->appointment_end_time ?? ''));
                    $endRaw = $endRaw ? substr($endRaw, 0, 5) : '';

                    $end = $endRaw
                        ? Carbon::createFromFormat('H:i', $endRaw)
                        : $start->copy()->addMinutes($slotDuration); // fallback

                    $bookedByDate[$appt->appointment_date][] = [
                        'start' => $start->format('H:i'),
                        'end'   => $end->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    continue;
                }
            }

            foreach ($activeHolds as $hold) {
                $raw = trim((string) $hold->appointment_time);

                // Rango viejo
                if (str_contains($raw, ' - ')) {
                    $times = explode(' - ', $raw);
                    if (count($times) !== 2) continue;

                    $bookedByDate[$hold->appointment_date][] = [
                        'start' => Carbon::createFromFormat('g:i A', trim($times[0]))->format('H:i'),
                        'end'   => Carbon::createFromFormat('g:i A', trim($times[1]))->format('H:i'),
                    ];
                    continue;
                }

                // Formato nuevo "HH:MM" (solo inicio)
                try {
                    $holdStart = Carbon::createFromFormat('H:i', $raw);
                    $holdEnd = $holdStart->copy()->addMinutes($slotDuration);

                    $bookedByDate[$hold->appointment_date][] = [
                        'start' => $holdStart->format('H:i'),
                        'end'   => $holdEnd->format('H:i'),
                    ];
                } catch (\Throwable $e) {
                    continue;
                }
            }

            // ---- recorrer días dentro del rango permitido ----
            $availableDates = [];
            $cursor = $start->copy()->startOfDay();

            while ($cursor->lte($end)) {
                $dateStr = $cursor->toDateString();

                $ranges = $openingHours->forDate($cursor);
                if ($ranges->isEmpty()) {
                    $cursor->addDay();
                    continue;
                }

                $bookedSlots = $bookedByDate[$dateStr] ?? [];

                if ($this->hasAnyAvailableSlot(
                    $ranges,
                    $employee->slot_duration,
                    $employee->break_duration ?? 0,
                    $cursor,
                    $bookedSlots,
                    $minAllowed
                )) {
                    $availableDates[] = $dateStr;
                }

                $cursor->addDay();
            }

            return response()->json([
                'available_dates' => $availableDates,
                'min_allowed' => $minAllowed->toDateTimeString(),
                'max_allowed' => $maxAllowedDate->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    protected function hasAnyAvailableSlot($availableRanges, $slotDuration, $breakDuration, Carbon $date, array $bookedSlots, Carbon $minAllowed): bool
    {
        foreach ($availableRanges as $range) {
            $start = Carbon::parse($date->toDateString() . ' ' . $range->start()->format('H:i'));
            $end   = Carbon::parse($date->toDateString() . ' ' . $range->end()->format('H:i'));

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

            if ($end->lte($minAllowed)) continue;

            $current = $start->copy();
            if ($current->lt($minAllowed)) {
                $current = $snapToCycle($minAllowed);
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

                if (!$conflict && $current->gte($minAllowed)) {
                    return true; // ✅ con uno basta
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