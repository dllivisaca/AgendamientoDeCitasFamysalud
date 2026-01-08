<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\AppointmentHold;
use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Events\BookingCreated;
use App\Events\StatusUpdated;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    public function index()
    {
        $appointments = Appointment::with(['service.category', 'employee.user', 'transferValidatedBy'])->latest()->get();
        // dd($appointments); // for debugging only
        return view('backend.appointment.index', compact('appointments'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        logger()->info('BOOKINGS PAYLOAD (KEYS)', $request->only([
            'billing_doc_number',
            'amount_standard',
            'discount_amount',
            'transfer_bank_origin',
            'transfer_payer_name',
            'transfer_date',
            'transfer_reference',
            'tr_bank',
            'tr_holder',
            'tr_date',
            'tr_ref',
        ]));
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'employee_id' => 'required|exists:employees,id',
            'service_id' => 'required|exists:services,id',

            // ✅ HOLD ID
            'hold_id' => 'required|integer',

            // PACIENTE
            'patient_full_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_address' => 'nullable|string|max:255',
            'patient_dob' => 'nullable|date',
            'patient_doc_type' => 'nullable|string|max:20',
            'patient_doc_number' => 'nullable|string|max:20',
            'patient_notes' => 'nullable|string',

            // FACTURACIÓN
            'billing_name' => 'nullable|string|max:255',
            'billing_doc_type' => 'nullable|string|max:20',
            'billing_doc_number' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'billing_phone' => 'nullable|string|max:20',

            // CITA (nuevos nombres)
            'amount' => 'required|numeric',

            // ✅ Montos adicionales (opcionales)
            'amount_standard' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',

            // ✅ Datos de transferencia (opcionales)
            'transfer_bank_origin' => 'nullable|string|max:120',
            'transfer_payer_name' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date|after_or_equal:' . now()->subDays(30)->toDateString()
                 . '|before_or_equal:' . now()->addDay()->toDateString(),
            'transfer_reference' => 'nullable|string|max:120',

            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'appointment_end_time' => 'required|string|max:5',
            'appointment_mode' => 'required|in:presencial,virtual',

            // TZ
            'patient_timezone' => 'nullable|string|max:50',
            'patient_timezone_label' => 'nullable|string|max:20',

            // CONSENTIMIENTO
            'data_consent' => 'required|boolean',

            // STATUS
            'status' => 'required|string',

            'payment_method' => 'required|in:transfer,card',
            'tr_file' => 'nullable|required_if:payment_method,transfer|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Mapear consentimiento a columnas reales de appointments
        $validated['terms_accepted'] = !empty($validated['data_consent']) ? 1 : 0;
        $validated['terms_accepted_at'] = $validated['terms_accepted'] ? now() : null;

        unset($validated['tr_file']);

            // Set user_id if not provided but user is authenticated
        // if (auth()->check() && !$request->has('user_id')) {
        //     $validated['user_id'] = auth()->id();
        // }

        $isPrivilegedRole = auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('moderator') ||
            auth()->user()->hasRole('employee')
        );

            // If admin/moderator/employee is booking, user_id should be null
        if ($isPrivilegedRole) {
            $validated['user_id'] = null;
        } elseif (auth()->check() && !$request->has('user_id')) {
            // Otherwise, assign user_id to the authenticated user
            $validated['user_id'] = auth()->id();
        }


        // Generate unique booking ID
        $validated['booking_id'] = 'FS-' . strtoupper(uniqid());

        // ✅ PASO 6: validar HOLD activo
        $sessionId = $request->session()->getId();

        $hold = AppointmentHold::where('id', $validated['hold_id'])
            ->where('session_id', $sessionId)
            ->where('employee_id', $validated['employee_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('appointment_end_time', $validated['appointment_end_time'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$hold) {
            return response()->json([
                'success' => false,
                'message' => 'Tu reserva expiró o ese turno ya no está disponible. Por favor selecciona el turno nuevamente.'
            ], 409);
        }

        // ✅ Seguridad extra: evitar doble cita en el mismo turno (por si hay carrera)
        $slotTaken = Appointment::where('employee_id', $validated['employee_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('appointment_end_time', $validated['appointment_end_time'])
            ->where('status', '!=', 'Cancelled')
            ->exists();

        if ($slotTaken) {
            // si por alguna razón el turno ya se convirtió en cita, liberamos hold
            $hold->delete();

            return response()->json([
                'success' => false,
                'message' => 'Ese turno ya no está disponible. Por favor selecciona otro.'
            ], 409);
        }

        $appointment = null;

        DB::transaction(function () use (&$appointment, &$validated, $hold, $request) {
            unset($validated['hold_id']);
            unset($validated['tr_file']);

            if (($validated['payment_method'] ?? null) === 'transfer' && $request->hasFile('tr_file')) {
                $validated['transfer_receipt_path'] = $request->file('tr_file')->store('transfer_proofs', 'public');
            }

            $appointment = Appointment::create($validated);

            // ✅ consumir el hold
            $hold->delete();
        });

        event(new BookingCreated($appointment));

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'booking_id' => $appointment->booking_id,
            'appointment' => $appointment
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        logger()->info('UPDATE STATUS HIT', [
            'user_id' => auth()->id(),
            'payload' => request()->all(),
            'route_name' => request()->route() ? request()->route()->getName() : null,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|string',

            // ✅ Validación de transferencia (desde tu modal)
            'transfer_validation_status' => 'nullable|in:validated,rejected',
            'transfer_validation_notes'  => 'nullable|string|required_if:transfer_validation_status,rejected',

            // Precios (si los envías desde el front)
            'amount_standard' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',

            // Términos
            'data_consent' => 'nullable|boolean',

            // Transferencia (datos)
            'transfer_bank_origin' => 'nullable|string|max:120',
            'transfer_payer_name' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date|after_or_equal:' . now()->subDays(30)->toDateString()
                 . '|before_or_equal:' . now()->addDay()->toDateString(),
            'transfer_reference' => 'nullable|string|max:120',

            // Archivo comprobante (si el método de pago es transferencia)
            'tr_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->status = $request->status;

        logger()->info('UPDATE STATUS APPOINTMENT', [
            'id' => $appointment->id ?? null,
            'payment_method_db' => $appointment->payment_method ?? null,
            'status_before' => $appointment->status ?? null,
            'payment_status_before' => $appointment->payment_status ?? null,
        ]);

        // ✅ Solo si el método de pago es transferencia, aplicar validación admin
        $pm = strtolower(trim((string) ($appointment->payment_method ?? ''))); // "transfer" | "card"
        $validation = strtolower(trim((string) ($request->transfer_validation_status ?? ''))); // validated | rejected | ""

        // ✅ Guardar el status de validación (si viene vacío, queda NULL)
        if ($pm === 'transfer') {
            $appointment->transfer_validation_status = ($validation !== '') ? $validation : null;
        } else {
            // Si no es transferencia, limpiamos por seguridad
            $appointment->transfer_validation_status = null;
        }

        if ($pm === 'transfer' && in_array($validation, ['validated', 'rejected'], true)) {

            // Guarda auditoría de validación
            $appointment->transfer_validated_at = now();
            $appointment->transfer_validated_by = Auth::id(); // user logueado (admin/mod/employee)
            if ($request->has('transfer_validation_notes')) {
                $appointment->transfer_validation_notes = $request->transfer_validation_notes;
            }

            // Reglas de negocio (según tu texto del modal):
            // - Validada => cita "Paid" y pago "paid"
            // - Rechazada => cita "On Hold" y pago "pending"
            if ($validation === 'validated') {
                $appointment->status = 'Paid';
                $appointment->payment_status = 'paid';
            } else { // rejected
                $appointment->status = 'On Hold';
                $appointment->payment_status = 'pending';
            }
        }

         // ✅ Guardar precios (si vienen)
        if ($request->has('amount_standard')) {
            $appointment->amount_standard = $request->amount_standard;
        }
        if ($request->has('discount_amount')) {
            $appointment->discount_amount = $request->discount_amount;
        }

        // ✅ Guardar términos (si vienen)
        if ($request->has('data_consent')) {
            $appointment->terms_accepted = (bool) $request->data_consent;
            $appointment->terms_accepted_at = $request->data_consent ? now() : null;
        }

        // ✅ Guardar datos de transferencia SOLO si vienen en el request
        if ($request->hasAny(['transfer_bank_origin', 'transfer_payer_name', 'transfer_date', 'transfer_reference'])) {

            // OJO: si algún campo viene vacío a propósito, se guardará vacío.
            // Si NO viene en el request, no lo tocamos.
            if ($request->has('transfer_bank_origin')) {
                $appointment->transfer_bank_origin = $request->transfer_bank_origin;
            }
            if ($request->has('transfer_payer_name')) {
                $appointment->transfer_payer_name = $request->transfer_payer_name;
            }
            if ($request->has('transfer_date')) {
                $appointment->transfer_date = $request->transfer_date;
            }
            if ($request->has('transfer_reference')) {
                $appointment->transfer_reference = $request->transfer_reference;
            }
        }

        // ✅ Guardar archivo comprobante (si viene)
        if ($request->hasFile('tr_file')) {
            $appointment->transfer_receipt_path = $request->file('tr_file')->store('transfer_proofs', 'public');
        }
        $appointment->save();

        event(new StatusUpdated($appointment));

        return redirect()->back()->with('success', 'Cambios guardados correctamente.');
    }

}
