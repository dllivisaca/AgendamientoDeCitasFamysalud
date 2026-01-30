<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\Models\Appointment;
use App\Models\AppointmentHold;

// OJO: usa los models reales que tengas en tu app.
// Estos nombres son los típicos por tu relación: service.category y employee.user
use App\Models\Category;
use App\Models\Service;
use App\Models\Employee;

class AdminAppointmentCreateController extends Controller
{
    // 1) Áreas (Categories)
    public function categories()
    {
        $rows = Category::query()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'catname' => $c->title, // 👈 mantenemos catname para el JS
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    // 2) Servicios por área
    public function services(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer',
        ]);

        $rows = Service::query()
            ->where('category_id', $request->category_id)
            ->select(['id', 'title', 'category_id'])
            ->orderBy('title')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'servname' => $s->title,// 👈 mantenemos sername para el JS
                    'category_id' => $s->category_id,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    // 3) Profesionales por servicio
    // Asumo que Service tiene relación con Employee vía tabla pivote o columna.
    // Para no “adivinar”, lo hago por query configurable:
    // - Primero intento: services.employee_id (si existe)
    // - Si no, intento pivot: employee_services (employee_id, service_id)
    public function employees(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer',
        ]);

        $serviceId = (int) $request->service_id;

        // Caso A: services.employee_id existe
        $hasEmployeeIdColumn = DB::getSchemaBuilder()->hasColumn('services', 'employee_id');

        if ($hasEmployeeIdColumn) {
            $rows = Employee::query()
                ->whereIn('id', function ($q) use ($serviceId) {
                    $q->select('employee_id')->from('services')->where('id', $serviceId);
                })
                ->with('user:id,name')
                ->get()
                ->map(function ($e) {
                    return ['id' => $e->id, 'name' => $e->user->name ?? ('Profesional #' . $e->id)];
                })
                ->values();
            return response()->json(['success' => true, 'data' => $rows]);
        }

        // Caso B: tabla employee_service
        $hasPivot = DB::getSchemaBuilder()->hasTable('employee_service');

        if ($hasPivot) {
            $rows = Employee::query()
                ->whereIn('employees.id', function ($q) use ($serviceId) {
                    $q->select('employee_id')
                    ->from('employee_service')
                    ->where('service_id', $serviceId);
                })
                ->leftJoin('users', 'users.id', '=', 'employees.user_id')
                ->select([
                    'employees.id',
                    DB::raw("COALESCE(users.name, CONCAT('Profesional #', employees.id)) as name")
                ])
                ->orderBy('name')
                ->get()
                ->map(function ($e) {
                    return ['id' => $e->id, 'name' => $e->name];
                })
                ->values();

            return response()->json(['success' => true, 'data' => $rows]);
        }

        // Si no hay forma de deducir relación sin romper tu app:
        return response()->json([
            'success' => false,
            'message' => 'No se encontró relación servicio → profesional (services.employee_id o pivote employee_service).'
        ], 422);
    }

    // 4) Slots disponibles: devuelve una lista [{start:"09:45", end:"09:55"}...]
    // Para NO inventar tu lógica interna, lo hago así:
    // - Usa la disponibilidad del sistema a través de AppointmentHold + appointments (bloqueos y ocupados)
    // - Genera slots básicos en ventana 08:00-18:00 con duración 10 min (ajústalo si quieres)
    public function slots(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $employeeId = (int) $request->employee_id;
        $date = Carbon::parse($request->date)->toDateString();

        // Ventana base (ajustable)
        $dayStart = Carbon::parse($date . ' 08:00:00');
        $dayEnd   = Carbon::parse($date . ' 18:00:00');
        $slotMinutes = 10;

        // Ocupados por citas (no canceladas)
        $taken = Appointment::query()
            ->where('employee_id', $employeeId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get(['appointment_time', 'appointment_end_time'])
            ->map(function ($r) {
                return [
                    'start' => (string) $r->appointment_time,
                    'end'   => (string) $r->appointment_end_time,
                ];
            })
            ->values()
            ->all();

        // Bloqueados por holds vigentes
        $holds = AppointmentHold::query()
            ->where('employee_id', $employeeId)
            ->where('appointment_date', $date)
            ->where('expires_at', '>', now())
            ->get(['appointment_time', 'appointment_end_time'])
            ->map(function ($r) {
                return [
                    'start' => (string) $r->appointment_time,
                    'end'   => (string) $r->appointment_end_time,
                ];
            })
            ->values()
            ->all();

        $busy = array_merge($taken, $holds);

        $slots = [];
        $cursor = $dayStart->copy();

        while ($cursor->lt($dayEnd)) {
            $start = $cursor->format('H:i');
            $end = $cursor->copy()->addMinutes($slotMinutes)->format('H:i');

            $conflict = false;
            foreach ($busy as $b) {
                // Solapa si start < b.end && end > b.start
                if ($start < $b['end'] && $end > $b['start']) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict) {
                $slots[] = ['start' => $start, 'end' => $end];
            }

            $cursor->addMinutes($slotMinutes);
        }

        return response()->json(['success' => true, 'data' => $slots]);
    }

    // 5) Guardar cita (admin)
    public function store(Request $request)
    {
        // ✅ Admin crea: no hay checkbox de consentimiento en este flujo
        // Forzamos data_consent para que no falle la validación del sistema
        $request->merge([
            'data_consent' => 1,
        ]);
        $validated = $request->validate([
            // Slot
            'employee_id' => 'required|exists:employees,id',
            'service_id'  => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'appointment_end_time' => 'required|string|max:5',
            'hold_id' => 'required|integer',

            'appointment_mode' => 'required|in:presencial,virtual',

            // Paciente
            'patient_full_name' => 'required|string|max:255',
            'data_consent' => 'nullable|boolean',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_address' => 'nullable|string|max:255',
            'patient_dob' => 'nullable|date',
            'patient_doc_type' => 'nullable|string|max:20',
            'patient_doc_number' => 'nullable|string|max:20',
            'patient_notes' => 'nullable|string',

            // Facturación (permito null, pero tu front los llenará según mayor/menor)
            'billing_name' => 'nullable|string|max:255',
            'billing_doc_type' => 'nullable|string|max:20',
            'billing_doc_number' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'billing_phone' => 'nullable|string|max:20',

            // Pago
            'payment_method' => 'required|in:cash,transfer,card',
            'amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_status' => 'required|in:pending,unpaid,partial,paid,refunded',

            // ⬇️ Solo se llena cuando NO es transferencia
            'payment_paid_at' => 'nullable|required_if:payment_method,cash,card|date',

            'payment_notes' => 'nullable|string',

            'client_transaction_id' => 'nullable|string|max:120',

            // Transferencia (requeridos solo si payment_method=transfer)
            'transfer_bank_origin' => 'nullable|required_if:payment_method,transfer|string|max:120',
            'transfer_payer_name' => 'nullable|required_if:payment_method,transfer|string|max:255',
            'transfer_date' => 'nullable|required_if:payment_method,transfer|date',
            'transfer_reference' => 'nullable|string|max:120',
            'tr_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // Canal por el que se solicitó la cita (seleccionado en el modal)
            // Si no selecciona nada, queda null
            'appointment_request_source' => 'nullable|string|max:30',

            // Estado de cita
            'status' => 'required|in:pending_verification,pending_payment,confirmed,paid,completed,no_show,cancelled',
        ]);

        // ✅ Validar HOLD activo (para evitar choque de turnos)
        $hold = AppointmentHold::query()
            ->where('id', $validated['hold_id'])
            ->where('employee_id', $validated['employee_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('appointment_end_time', $validated['appointment_end_time'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$hold) {
            return response()->json([
                'success' => false,
                'message' => 'La reserva del turno expiró o ya no está disponible. Selecciona el turno nuevamente.'
            ], 409);
        }

        // ✅ Evitar doble cita (extra)
        $slotTaken = Appointment::query()
            ->where('employee_id', $validated['employee_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('appointment_end_time', $validated['appointment_end_time'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($slotTaken) {
            $hold->delete();
            return response()->json([
                'success' => false,
                'message' => 'Ese turno ya no está disponible. Selecciona otro.'
            ], 409);
        }

        // Admin crea: forzar TyC/consentimiento como aceptado para cumplir NOT NULL
        $validated['terms_accepted'] = 1;
        $validated['terms_accepted_at'] = now();

        // booking_id
        $validated['booking_id'] = 'FS-' . strtoupper(uniqid());

        // Admin crea: user_id null (como tu lógica)
        $validated['user_id'] = null;

        // Admin crea: canal interno fijo
        $validated['appointment_channel'] = 'admin_manual';

        // =========================
        // BILLING: si no llegó (por inputs disabled), usar datos del paciente
        // =========================
        $validated['billing_name'] = $validated['billing_name'] ?: ($validated['patient_full_name'] ?? null);
        $validated['billing_doc_type'] = $validated['billing_doc_type'] ?: ($validated['patient_doc_type'] ?? null);
        $validated['billing_doc_number'] = $validated['billing_doc_number'] ?: ($validated['patient_doc_number'] ?? null);
        $validated['billing_email'] = $validated['billing_email'] ?: ($validated['patient_email'] ?? null);
        $validated['billing_phone'] = $validated['billing_phone'] ?: ($validated['patient_phone'] ?? null);
        $validated['billing_address'] = $validated['billing_address'] ?: ($validated['patient_address'] ?? null);

        // =========================
        // AMOUNTS: amount_standard + discount_amount desde services.price
        // Regla:
        // - amount_standard = price + 8%
        // - discount_amount = (cash|transfer) => 8% del price ; (card) => 0
        // =========================
        $service = Service::query()->select(['id','price'])->find($validated['service_id']);
        $price = $service ? (float)$service->price : 0.0;

        $amountStandard = round($price * 1.08, 2);
        $validated['amount_standard'] = $amountStandard;

        if (in_array($validated['payment_method'], ['cash', 'transfer'], true)) {
            $validated['discount_amount'] = round($price * 0.08, 2);
        } else {
            $validated['discount_amount'] = 0.00;
        }

        // Canal solicitado (si lo eligieron en el modal), si no -> null
        // (viene validado como appointment_request_source)
        $validated['appointment_request_source'] = $validated['appointment_request_source'] ?? null;

        // payment_paid_at se llena SOLO para cash/card (transfer se llena luego al validar en edición)
        $pm = $validated['payment_method'];

        if ($pm === 'transfer') {
            $validated['payment_paid_at'] = null;
            $validated['payment_paid_at_date_source'] = null;
        } else {
            $validated['payment_paid_at'] = Carbon::parse($validated['payment_paid_at'])->format('Y-m-d H:i:s');
            $validated['payment_paid_at_date_source'] = 'manual';
        }

        // Definir payment_channel según método
        $pm = $validated['payment_method'];
        if ($pm === 'transfer') $validated['payment_channel'] = 'bank_transfer';
        if ($pm === 'cash')     $validated['payment_channel'] = 'cash_in_person';
        if ($pm === 'card')     $validated['payment_channel'] = !empty($validated['client_transaction_id']) ? 'payphone' : 'manual_card';

        // =====================================================
        // REGLAS: SOLO restringir combos inválidos
        // - pending_verification solo para transfer
        // - payment_status = pending solo para transfer
        // NO recalcular por montos: se respeta lo que el admin selecciona
        // =====================================================

        // Si NO es transferencia y mandan pending_verification, lo ajustamos
        if ($pm !== 'transfer' && ($validated['status'] ?? null) === 'pending_verification') {
            $validated['status'] = 'pending_payment';
        }

        // Si NO es transferencia y mandan payment_status=pending, lo ajustamos (sin cálculos)
        if ($pm !== 'transfer' && ($validated['payment_status'] ?? null) === 'pending') {
            $validated['payment_status'] = 'unpaid';
        }

        DB::transaction(function () use (&$validated, $request, $hold) {

            // Subir archivo si existe
            if (($validated['payment_method'] ?? null) === 'transfer' && $request->hasFile('tr_file')) {
                $validated['transfer_receipt_path'] = $request->file('tr_file')->store('transfer_proofs', 'public');
            }

            // OJO: tu tabla tiene columnas transfer_* y transfer_receipt_path (según screenshot)
            $appt = Appointment::create($validated);

            // consumir hold
            $hold->delete();

            // No retorno aquí (solo transacción)
        });

        return response()->json([
            'success' => true,
            'message' => 'Cita creada correctamente.'
        ]);
    }
}