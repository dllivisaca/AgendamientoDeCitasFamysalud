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
            'transfer_date' => 'nullable|date|after_or_equal:' . now()->subDays(15)->toDateString()
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

            // ✅ Puede venir solo si la tarjeta fue por PayPhone; en card manual (POS físico) puede venir NULL
            'client_transaction_id' => 'nullable|string|max:120',
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

        // ✅ Canal de cita: paciente agenda online (solo si NO es admin/mod/employee)
        if (!$isPrivilegedRole) {
            $validated['appointment_channel'] = 'patient_online';
        }

        // ✅ Canal de pago: transferencia bancaria (solo cuando el paciente agenda online)
        if (!$isPrivilegedRole && (($validated['payment_method'] ?? null) === 'transfer')) {
            $validated['payment_channel'] = 'bank_transfer';
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

        // ✅ Si es tarjeta (Payphone), rellenar nuevos campos desde payment_attempts
    if (($validated['payment_method'] ?? null) === 'card') {

        $clientTxId = (string) ($request->input('client_transaction_id') ?? '');
        $clientTxId = trim($clientTxId);

        if ($clientTxId !== '') {
            // Guardar también el client_transaction_id si tu tabla appointments lo tiene
            $validated['client_transaction_id'] = $clientTxId;

            $attempt = DB::table('payment_attempts')
                ->where('client_transaction_id', $clientTxId)
                ->latest('id')
                ->first();

            // Solo si existe el attempt (no cambiamos tu lógica actual, solo rellenamos)
            if ($attempt) {
                // amount_paid = payment_attempts.amount
                if (isset($attempt->amount)) {
                    $validated['amount_paid'] = $attempt->amount;
                }

                // payment_paid_at = payment_attempts.created_at
                if (isset($attempt->created_at)) {
                    $validated['payment_paid_at'] = $attempt->created_at;
                }

                // sources / channel (siempre en minúsculas)
                $validated['payment_paid_at_date_source'] = 'payphone';
                $validated['payment_channel'] = 'payphone';
            } else {
                    // Si no existe payment_attempts, NO asumimos payphone.
                    // (Esto cubre tarjeta manual POS u otros escenarios)
                }
        }
    }

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
            'status' => 'required|in:pending_verification,pending_payment,on_hold,confirmed,paid,completed,cancelled,no_show,rescheduled',

            // ✅ Guardar método/monto/estado pago desde el modal
            'payment_method' => 'nullable|in:transfer,card,cash',
            'amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,unpaid,partial,paid,refunded',

            // ✅ Campos adicionales cuando el pago es tarjeta / edición manual
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_paid_at' => 'nullable|date',
            'client_transaction_id' => 'nullable|string|max:120',

            'payment_paid_at_date_source' => 'nullable|string|max:30',
            'payment_channel' => 'nullable|string|max:40',
            'payment_notes' => 'nullable|string',

            // ✅ Validación de transferencia (desde tu modal)
            'transfer_validation_status' => 'nullable|in:validated,rejected',
            'transfer_validation_touched' => 'nullable|in:0,1',
            'transfer_validation_status_original' => 'nullable|in:validated,rejected',
            'transfer_validation_notes'  => 'nullable|string|required_if:transfer_validation_status,rejected',

            // Precios (si los envías desde el front)
            'amount_standard' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',

            // Términos
            'data_consent' => 'nullable|boolean',

            // Transferencia (datos)
            'transfer_bank_origin' => 'nullable|string|max:120',
            'transfer_payer_name' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date|after_or_equal:' . now()->subDays(15)->toDateString()
                 . '|before_or_equal:' . now()->addDay()->toDateString(),
            'transfer_reference' => 'nullable|string|max:120',

            // Archivo comprobante (si el método de pago es transferencia)
            'tr_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // ✅ Datos del paciente (editar desde modal)
            'patient_full_name'  => 'nullable|string|max:255',
            'patient_doc_type'   => 'nullable|string|max:20',
            'patient_doc_number' => 'nullable|string|max:20',
            'patient_dob'        => 'nullable|date',
            'patient_email'      => 'nullable|email|max:255',
            'patient_phone'      => 'nullable|string|max:20',
            'patient_address'    => 'nullable|string|max:255',
            'patient_timezone'   => 'nullable|string|max:50',
            'patient_notes' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        // ✅ Solo cambiar status si realmente viene (y no por efecto del método de pago)
        if ($request->filled('status')) {
            $appointment->status = $request->status;
        }

        // ✅ Guardar datos del paciente SOLO si vienen en el request
        if ($request->hasAny([
            'patient_full_name',
            'patient_doc_type',
            'patient_doc_number',
            'patient_dob',
            'patient_email',
            'patient_phone',
            'patient_address',
            'patient_timezone',
            'patient_notes',
        ])) {
            if ($request->has('patient_full_name')) {
                $appointment->patient_full_name = $request->input('patient_full_name');
            }
            if ($request->has('patient_doc_type')) {
                $appointment->patient_doc_type = $request->input('patient_doc_type');
            }
            if ($request->has('patient_doc_number')) {
                $appointment->patient_doc_number = $request->input('patient_doc_number');
            }
            if ($request->has('patient_dob')) {
                $appointment->patient_dob = $request->input('patient_dob');
            }
            if ($request->has('patient_email')) {
                $appointment->patient_email = $request->input('patient_email');
            }
            if ($request->has('patient_phone')) {
                $appointment->patient_phone = $request->input('patient_phone');
            }
            if ($request->has('patient_address')) {
                $appointment->patient_address = $request->input('patient_address');
            }
            if ($request->has('patient_timezone')) {
                $appointment->patient_timezone = $request->input('patient_timezone');
            }
            if ($request->has('patient_notes')) {
                $appointment->patient_notes = $request->input('patient_notes');
            }
        }

        // ✅ Guardar datos de facturación SOLO si vienen en el request
        if ($request->hasAny([
            'billing_name',
            'billing_doc_type',
            'billing_doc_number',
            'billing_email',
            'billing_phone',
            'billing_address',
        ])) {
            if ($request->has('billing_name')) {
                $appointment->billing_name = $request->input('billing_name');
            }
            if ($request->has('billing_doc_type')) {
                $appointment->billing_doc_type = $request->input('billing_doc_type');
            }
            if ($request->has('billing_doc_number')) {
                $appointment->billing_doc_number = $request->input('billing_doc_number');
            }
            if ($request->has('billing_email')) {
                $appointment->billing_email = $request->input('billing_email');
            }
            if ($request->has('billing_phone')) {
                $appointment->billing_phone = $request->input('billing_phone');
            }
            if ($request->has('billing_address')) {
                $appointment->billing_address = $request->input('billing_address');
            }
        }

        // ✅ Guardar método de pago (si lo cambiaron en el modal)
        if ($request->filled('payment_method')) {

            $newPm = strtolower(trim((string) $request->payment_method));
            $oldPm = strtolower(trim((string) ($appointment->payment_method ?? '')));

            $appointment->payment_method = $newPm;

            /**
             * ✅ Si cambiamos DE transferencia a otro método (card/cash/etc),
             * limpiamos TODOS los campos de transferencia para evitar data basura.
             */
            if ($oldPm === 'transfer' && $newPm !== 'transfer') {

                // Transfer data
                $appointment->transfer_bank_origin = null;
                $appointment->transfer_payer_name = null;
                $appointment->transfer_date = null;
                $appointment->transfer_reference = null;

                // Receipt path (y opcionalmente borrar archivo físico)
                if (!empty($appointment->transfer_receipt_path) && Storage::disk('public')->exists($appointment->transfer_receipt_path)) {
                    Storage::disk('public')->delete($appointment->transfer_receipt_path);
                }
                $appointment->transfer_receipt_path = null;

                // Transfer validation/audit fields
                $appointment->transfer_validated_at = null;
                $appointment->transfer_validated_by = null;
                $appointment->transfer_validation_status = null; // si tu columna se llama así
                $appointment->transfer_validation_notes = null;
            }

            /**
             * ✅ Si cambiamos A transferencia, limpiamos datos típicos de tarjeta
             * (mantengo tu lógica original).
             */
            if ($newPm === 'transfer') {
                $appointment->client_transaction_id = null;
                $appointment->amount_paid = null;
                $appointment->payment_paid_at = null;
                $appointment->payment_paid_at_date_source = null;
            }
        }

        // ✅ Guardar monto (si lo cambiaron)
        if ($request->filled('amount')) {
            $appointment->amount = $request->amount;
        }

        // ✅ Guardar estado de pago (si lo cambiaron)
        if ($request->filled('payment_status')) {
            $appointment->payment_status = $request->payment_status;
        }

        // ✅ Guardar monto pagado / fecha pago / transaction id (aunque vengan en 0 o vacíos)
        if ($request->has('amount_paid')) {
            $appointment->amount_paid = $request->input('amount_paid');
        }

        // ✅ Guardar payment_paid_at EXACTAMENTE como lo manda el admin
        $valPaidAt = $request->input('payment_paid_at', null);

        if ($valPaidAt !== null) {
            $valPaidAt = trim((string)$valPaidAt);

            if ($valPaidAt !== '') {
                $appointment->payment_paid_at = $valPaidAt;
                $appointment->payment_paid_at_date_source = 'manual';
            } else {
                $appointment->payment_paid_at = null;
                $appointment->payment_paid_at_date_source = null;
            }
        }

        // ✅ Guardar observaciones de pago (payment_notes) si vienen en el request
        if ($request->has('payment_notes')) {
            $valNotes = $request->input('payment_notes');
            $valNotes = is_string($valNotes) ? trim($valNotes) : $valNotes;

            $appointment->payment_notes = ($valNotes !== '' && $valNotes !== null) ? $valNotes : null;
        }

        if ($request->has('client_transaction_id')) {
            $val = $request->input('client_transaction_id');
            $appointment->client_transaction_id = ($val !== '' && $val !== null) ? $val : null;
        }

        // ✅ Resolver payment_channel según tu lógica (post-edición)
        $pmNow = strtolower(trim((string) ($appointment->payment_method ?? ''))); // transfer | card | cash
        $txNow = trim((string) ($appointment->client_transaction_id ?? ''));
        $paidAtNow = $appointment->payment_paid_at; // puede ser null

        if ($pmNow === 'transfer') {
            $appointment->payment_channel = 'bank_transfer';
        } elseif ($pmNow === 'cash') {
            $appointment->payment_channel = 'cash_in_person';
        } elseif ($pmNow === 'card') {
            if ($txNow !== '') {
                $appointment->payment_channel = 'payphone';
            } elseif (!empty($paidAtNow)) {
                $appointment->payment_channel = 'manual_card';
            } else {
                // Si es card pero no hay tx ni fecha, no afirmamos canal
                $appointment->payment_channel = null;
            }
        }

        logger()->info('UPDATE STATUS APPOINTMENT', [
            'id' => $appointment->id ?? null,
            'payment_method_db' => $appointment->payment_method ?? null,
            'status_before' => $appointment->status ?? null,
            'payment_status_before' => $appointment->payment_status ?? null,
            'patient_full_name_in' => $request->input('patient_full_name'),
            'patient_email_in' => $request->input('patient_email'),
            'patient_doc_number_in' => $request->input('patient_doc_number'),
            'patient_notes_in' => $request->input('patient_notes'),
            'billing_name_in' => $request->input('billing_name'),
            'billing_doc_number_in' => $request->input('billing_doc_number'),
            'amount_paid_in' => $request->input('amount_paid'),
            'payment_paid_at_in' => $request->input('payment_paid_at'),
            'client_transaction_id_in' => $request->input('client_transaction_id'),
            'payment_notes_in' => $request->input('payment_notes'),
        ]);

        // ✅ Solo si el método de pago es transferencia, aplicar validación admin
        $pm = strtolower(trim((string) ($appointment->payment_method ?? ''))); // "transfer" | "card"
        $validation = strtolower(trim((string) ($request->transfer_validation_status ?? ''))); // validated | rejected | ""
        $touched = (string) $request->input('transfer_validation_touched', '0') === '1';

        // ✅ Validación de transferencia = SOLO auditoría (no modifica status ni payment_status)
        if ($pm === 'transfer' && $touched && $validation === '') {
            // "Sin revisar" => limpiar auditoría
            $appointment->transfer_validation_status = null;
            $appointment->transfer_validation_notes = null;
            $appointment->transfer_validated_at = null;
            $appointment->transfer_validated_by = null;

            // ✅ Si payment_paid_at venía de la validación de transferencia, lo limpiamos también
            if (($appointment->payment_paid_at_date_source ?? null) === 'transfer_validated_at') {
                $appointment->payment_paid_at = null;
                $appointment->payment_paid_at_date_source = null;
            }
        }

        // ✅ Guardar el status de validación
        // OJO: si es transfer y validation está vacío, ya lo manejamos arriba como "Sin revisar".
        if ($pm === 'transfer' && $touched && $validation !== '') {
            $appointment->transfer_validation_status = $validation;
        } elseif ($pm !== 'transfer') {
            // Si no es transferencia, limpiamos por seguridad
            $appointment->transfer_validation_status = null;
            $appointment->transfer_validation_notes = null;
            $appointment->transfer_validated_at = null;
            $appointment->transfer_validated_by = null;

            // ✅ Si payment_paid_at venía de la validación de transferencia, lo limpiamos también
            if (($appointment->payment_paid_at_date_source ?? null) === 'transfer_validated_at') {
                $appointment->payment_paid_at = null;
                $appointment->payment_paid_at_date_source = null;
            }
        }

        if ($pm === 'transfer' && $touched && in_array($validation, ['validated', 'rejected'], true)) {

            // ✅ SOLO auditoría
            $appointment->transfer_validated_at = now();
            $appointment->transfer_validated_by = Auth::id();

            // ✅ Si se VALIDÓ la transferencia, usar esta fecha como payment_paid_at (sin tocar status/payment_status)
            if ($validation === 'validated') {
                $appointment->payment_paid_at = $appointment->transfer_validated_at;
                $appointment->payment_paid_at_date_source = 'transfer_validated_at';
            }

            // Guardar notas (si vienen)
            if ($request->has('transfer_validation_notes')) {
                $appointment->transfer_validation_notes = $request->transfer_validation_notes;
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

            // ✅ borrar el anterior si existe
            if (!empty($appointment->transfer_receipt_path) && Storage::disk('public')->exists($appointment->transfer_receipt_path)) {
                Storage::disk('public')->delete($appointment->transfer_receipt_path);
            }

            // ✅ guardar el nuevo
            $appointment->transfer_receipt_path = $request->file('tr_file')->store('transfer_proofs', 'public');
        }
        $appointment->save();

        event(new StatusUpdated($appointment));

        return redirect()->back()->with('success', 'Cambios guardados correctamente.');
    }

}
