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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PatientNotificationAppointmentConfirmed;

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

        // ✅ Normalizar status para que la BD (ENUM) no reviente
        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            if ($status === 'canceled') {
                $request->merge(['status' => 'cancelled']);
            }
        }

        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|in:pending_verification,pending_payment,on_hold,confirmed,paid,completed,cancelled,canceled,no_show,rescheduled',

            // ✅ Reagendamiento (viene desde el wizard)
            'reschedule_date'      => 'nullable|date',
            'reschedule_time'      => 'nullable|string|max:5',
            'reschedule_end_time'  => 'nullable|string|max:5',
            'reschedule_reason'        => 'nullable|in:patient_requested,doctor_requested,admin_requested,other',
            'reschedule_reason_other'  => 'nullable|string|max:180',

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

            // ✅ Motivo del cambio (nuevo)
            'change_reason' => 'nullable|in:typo,patient_update,admin_adjustment,other',
            'change_reason_other' => 'nullable|string|max:180',

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
            'transfer_date' => 'nullable|date|required_if:payment_method,transfer',
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

        // ✅ BACKEND FIX: si es EFECTIVO y NO viene payment_paid_at,
        // usar cash_paid_at como fuente (por si el front no lo está enviando)
        $pmIncoming = strtolower(trim((string) $request->input('payment_method', $appointment->payment_method)));

        if ($pmIncoming === 'cash') {
            // Si tu form manda cash_paid_at (hidden), úsalo como payment_paid_at
            if (!$request->filled('payment_paid_at') && $request->filled('cash_paid_at')) {
                $request->merge([
                    'payment_paid_at' => $request->input('cash_paid_at'),
                ]);
            }
        }

        logger()->info('UPDATE STATUS - incoming payment fields', [
            'appointment_id' => $request->appointment_id,
            'payment_method_in' => $request->input('payment_method'),
            'payment_paid_at_in' => $request->input('payment_paid_at'),
            'amount_paid_in' => $request->input('amount_paid'),
            'payment_notes_in' => $request->input('payment_notes'),
            'cash_paid_at_in' => $request->input('cash_paid_at'),
            'pm_effective_in' => $request->input('payment_method', $appointment->payment_method),
        ]);

        $newDate = $request->input('reschedule_date');
        $newTime = $request->input('reschedule_time');
        $newEnd  = $request->input('reschedule_end_time');

        $isRescheduleNow =
            ($request->input('status') === 'rescheduled')
            && $request->filled('reschedule_date')
            && $request->filled('reschedule_time')
            && $request->filled('reschedule_end_time')
            // ✅ SOLO si realmente cambió vs lo que ya tiene la cita
            && (
                (string) $newDate !== (string) $appointment->appointment_date
                || (string) $newTime !== (string) $appointment->appointment_time
                || (string) $newEnd  !== (string) $appointment->appointment_end_time
            );

        // ✅ Si no hay reagendamiento real, ignoramos cualquier campo reschedule/audit que venga del front
        if (!$isRescheduleNow) {
            $request->request->remove('audit_reschedule_reason');
            $request->request->remove('audit_reschedule_reason_other');
            $request->request->remove('reschedule_date');
            $request->request->remove('reschedule_time');
            $request->request->remove('reschedule_end_time');
            $request->request->remove('reschedule_reason');
            $request->request->remove('reschedule_reason_other');
        }

        // ✅ Solo cuando es un reagendamiento REAL en esta acción
        if ($isRescheduleNow) {

            $request->merge([
                'appointment_date' => $request->input('reschedule_date'),
                'appointment_time' => $request->input('reschedule_time'),
                'appointment_end_time' => $request->input('reschedule_end_time'),
            ]);

            // ✅ (opcional) auditar el motivo SOLO si hubo reagendamiento real
            if ($request->has('reschedule_reason')) {
                $request->merge(['audit_reschedule_reason' => $request->input('reschedule_reason')]);
            }
            if ($request->has('reschedule_reason_other')) {
                $request->merge(['audit_reschedule_reason_other' => $request->input('reschedule_reason_other')]);
            }
        }

        // ✅ Campos que SÍ vamos a considerar para auditoría
        $allTrackable = [
            // Estados
            'status', 'payment_method', 'payment_status',

            // Cita (reagendamiento)
            'appointment_date', 'appointment_time', 'appointment_end_time',

            // Motivo de reagendamiento (solo para audit)
            'audit_reschedule_reason', 'audit_reschedule_reason_other',

            // Montos y pago
            'amount', 'amount_paid', 'payment_paid_at', 'client_transaction_id',
            'payment_notes',

            // Paciente
            'patient_full_name', 'patient_doc_type', 'patient_doc_number', 'patient_dob',
            'patient_email', 'patient_phone', 'patient_address', 'patient_timezone',
            'patient_notes',

            // Facturación
            'billing_name', 'billing_doc_type', 'billing_doc_number',
            'billing_email', 'billing_phone', 'billing_address',

            // Transferencia (solo data “editable”)
            'transfer_bank_origin', 'transfer_payer_name', 'transfer_date', 'transfer_reference',
            'transfer_receipt_path',
        ];

        // ✅ Campos que NO queremos auditar porque son "auto" (side-effects)
        $neverAudit = [
            'payment_channel',
            'payment_paid_at_date_source',
            'transfer_validated_at',
            'transfer_validated_by',
            'transfer_validation_status',
            'transfer_validation_notes',
            'terms_accepted',
            'terms_accepted_at',
            'amount_standard',
            'discount_amount',
        ];

        // ✅ Solo auditar lo que realmente llegó en el request
        $tracked = array_values(array_diff(
            array_intersect($allTrackable, array_keys($request->all())),
            $neverAudit
        ));

        // ✅ Si NO es reagendamiento real, no auditar nada de cita/reschedule
        if (!$isRescheduleNow) {
            $tracked = array_values(array_diff($tracked, [
                'appointment_date',
                'appointment_time',
                'appointment_end_time',
                'audit_reschedule_reason',
                'audit_reschedule_reason_other',
            ]));
        }

        // ✅ Obtener último reschedule previo (para auditoría OLD values)
        $prevReschedule = DB::table('appointment_reschedules')
            ->where('appointment_id', $appointment->id)
            ->latest('id')
            ->first();

        $prevRescheduleReason = $prevReschedule->reason ?? null;
        $prevRescheduleNote   = $prevReschedule->note ?? null;

        $before = !empty($tracked) ? $appointment->only($tracked) : [];

        // ✅ Inyectar OLD values reales del reschedule (si aplica)
        if (in_array('audit_reschedule_reason', $tracked, true)) {
            $before['audit_reschedule_reason'] = $prevRescheduleReason;
        }

        if (in_array('audit_reschedule_reason_other', $tracked, true)) {
            $before['audit_reschedule_reason_other'] = $prevRescheduleNote;
        }

        // ✅ Solo cambiar status si realmente viene (y no por efecto del método de pago)
        if ($request->filled('status')) {
            $appointment->status = $request->status;
        }

        // ✅ Si es reagendamiento REAL: actualizar fecha/hora de la cita + guardar historial
        if ($isRescheduleNow) {

            $newDate = $request->input('reschedule_date');
            $newTime = $request->input('reschedule_time');
            $newEnd  = $request->input('reschedule_end_time');

            // No hacemos cambios silenciosos si faltan datos
            if (!$newDate || !$newTime || !$newEnd) {
                return redirect()->back()->with('error', 'Para reagendar, debe seleccionar fecha y horario.');
            }

            // Valores anteriores (para historial)
            $oldDate = $appointment->appointment_date;
            $oldTime = $appointment->appointment_time;
            $oldEnd  = $appointment->appointment_end_time;

            DB::transaction(function () use ($request, $appointment, $oldDate, $oldTime, $oldEnd, $newDate, $newTime, $newEnd) {

                // 1) ✅ Actualizar appointment (cita real)
                $appointment->appointment_date = $newDate;
                $appointment->appointment_time = $newTime;
                $appointment->appointment_end_time = $newEnd;
                $appointment->save();

                $reason = strtolower(trim((string) $request->input('reschedule_reason')));
                if ($reason === 'admin_requested') {
                    $reason = 'admin';
                }
                $allowedReasons = ['patient_requested', 'doctor_requested', 'admin', 'other'];
                if (!in_array($reason, $allowedReasons, true)) {
                    $reason = 'other';
                }

                // 2) ✅ Guardar historial en appointment_reschedules
                DB::table('appointment_reschedules')->insert([
                    'appointment_id' => $appointment->id,

                    // ANTES (inicio + fin)
                    'from_datetime'     => Carbon::parse($oldDate . ' ' . $oldTime),
                    'from_end_datetime' => Carbon::parse($oldDate . ' ' . $oldEnd),

                    // DESPUÉS (inicio + fin)
                    'to_datetime'       => Carbon::parse($newDate . ' ' . $newTime),
                    'to_end_datetime'   => Carbon::parse($newDate . ' ' . $newEnd),

                    'reason' => $reason,
                    'note'   => $request->input('reschedule_reason_other'),

                    'rescheduled_by_user_id' => Auth::id(),
                    'created_at' => now(),
                ]);

                // ✅ Consumir/eliminar el HOLD del nuevo turno (ya fue usado para reagendar)
                AppointmentHold::where('employee_id', $appointment->employee_id)
                    ->where('appointment_date', $newDate)
                    ->where('appointment_time', $newTime)
                    ->where('appointment_end_time', $newEnd)
                    ->where('expires_at', '>', now()) // opcional pero recomendado
                    ->delete();

                // ✅ Limpieza extra: borrar hold del turno anterior si existiera (evita basura)
                AppointmentHold::where('employee_id', $appointment->employee_id)
                    ->where('appointment_date', $oldDate)
                    ->where('appointment_time', $oldTime)
                    ->where('appointment_end_time', $oldEnd)
                    ->where('expires_at', '>', now())
                    ->delete();
            });
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

        // ✅ Guardar monto pagado SOLO si viene con valor (evita setear null en columnas NOT NULL)
        // Nota: "0" y "0.00" cuentan como valor válido
        if ($request->filled('amount_paid')) {
            $raw = $request->input('amount_paid');

            if ($raw !== null) {
                $raw = is_string($raw) ? trim($raw) : $raw;

                // Si viene vacío, NO lo tocamos (ej: reagendar no debería modificar pagos)
                if ($raw !== '') {
                    $appointment->amount_paid = $raw;
                }
            }
        }

        // ✅ Guardar payment_paid_at (normalizado a DATETIME MySQL)
        $valPaidAt = $request->input('payment_paid_at', null);

        if ($valPaidAt !== null) {
            $valPaidAt = trim((string) $valPaidAt);

            if ($valPaidAt !== '') {
                try {
                    // Soporta "YYYY-MM-DDTHH:MM" (datetime-local) y "YYYY-MM-DD HH:MM:SS"
                    $appointment->payment_paid_at = Carbon::parse($valPaidAt)->format('Y-m-d H:i:s');
                    $appointment->payment_paid_at_date_source = 'manual';
                } catch (\Throwable $e) {
                    // Si llega un formato inválido, NO guardes basura
                    $appointment->payment_paid_at = null;
                    $appointment->payment_paid_at_date_source = null;

                    logger()->warning('payment_paid_at invalid format', [
                        'appointment_id' => $appointment->id ?? null,
                        'incoming' => $valPaidAt,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $appointment->payment_paid_at = null;
                $appointment->payment_paid_at_date_source = null;
            }
        }

        // ✅ Guardar observaciones de pago (payment_notes) si vienen en el request
        if ($request->filled('payment_notes')) {
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

        // ✅ Calcular diferencias SOLO por lo que vino del request (evita falsos cambios por formato)
        $changedFields = [];
        $oldValues = [];
        $newValues = [];

        // Normalizador de valores para evitar "cambios fantasmas" (null vs "", fechas, números)
        $norm = function ($key, $val) {
            if ($val === '') return null;

            // Si viene Carbon/DateTime del modelo
            if ($val instanceof \DateTimeInterface) {
                // transfer_date y patient_dob son fechas (solo día)
                if (in_array($key, ['transfer_date', 'patient_dob'], true)) {
                    return Carbon::instance($val)->toDateString(); // Y-m-d
                }
                // payment_paid_at es datetime (manténlo como string estándar)
                if ($key === 'payment_paid_at') {
                    return Carbon::instance($val)->format('Y-m-d H:i:s');
                }
                return Carbon::instance($val)->toDateTimeString();
            }

            // Fechas que vienen como string desde el request
            if (is_string($val) && in_array($key, ['transfer_date', 'patient_dob'], true)) {
                $val = trim($val);
                if ($val === '') return null;
                try { return Carbon::parse($val)->toDateString(); } catch (\Throwable $e) { return $val; }
            }

            if (is_string($val) && $key === 'payment_paid_at') {
                $val = trim($val);
                if ($val === '') return null;
                try { return Carbon::parse($val)->format('Y-m-d H:i:s'); } catch (\Throwable $e) { return $val; }
            }

            // Números: compara como número (evita "10" vs "10.00")
            if (in_array($key, ['amount', 'amount_paid'], true)) {
                if ($val === null) return null;
                return (string) ((float) $val);
            }

            return is_string($val) ? trim($val) : $val;
        };

        foreach ($tracked as $key) {

            // Solo si vino en el request (tu $tracked ya filtra, pero por seguridad)
            if (!$request->has($key)) continue;

            $old = $before[$key] ?? null;
            $incoming = $request->input($key);

            $oldNorm = $norm($key, $old);
            $inNorm  = $norm($key, $incoming);

            if ($oldNorm !== $inNorm) {
                $changedFields[] = $key;
                $oldValues[$key] = $oldNorm;
                $newValues[$key] = $inNorm;
            }
        }

        // ✅ Solo crear audit si realmente hubo cambios
        if (!empty($tracked) && !empty($changedFields)) {

            $actorId = Auth::id();

            // actor_role: intento 1 (Spatie), fallback a null
            $actorRole = null;
            if (auth()->check()) {
                try {
                    if (method_exists(auth()->user(), 'getRoleNames')) {
                        $roles = auth()->user()->getRoleNames();
                        $actorRole = $roles && count($roles) ? $roles[0] : null;
                    }
                } catch (\Throwable $e) {
                    $actorRole = null;
                }
            }

            DB::table('appointment_audits')->insert([
                'appointment_id' => $appointment->id,
                'actor_user_id'  => $actorId,
                'actor_role'     => $actorRole,

                // Acción corta y consistente
                'action'         => 'update',

                // Longtext: guardamos JSON
                'changed_fields' => json_encode($changedFields, JSON_UNESCAPED_UNICODE),
                'old_values'     => json_encode($oldValues, JSON_UNESCAPED_UNICODE),
                'new_values'     => json_encode($newValues, JSON_UNESCAPED_UNICODE),

                // Motivo (tu select) + texto si fue "other"
                'reason'         => $request->input('change_reason'),
                'reason_other'   => $request->input('change_reason_other'),

                // Tu tabla tiene created_at con default current_timestamp()
                // Si quieres ponerlo explícito, descomenta:
                // 'created_at'     => now(),
            ]);
        }

        event(new StatusUpdated($appointment));

        // ✅ Responder JSON también para fetch/AJAX aunque no venga Accept: application/json
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cambios guardados correctamente.'
            ]);
        }

        return redirect()->back()->with('success', 'Cambios guardados correctamente.');
    }

    public function confirm(Appointment $appointment, Request $request)
    {
        try {

            // ✅ Si ya está confirmada, no volver a confirmar
            if ($appointment->status === 'confirmed') {
                return response()->json([
                    'success' => true,
                    'message' => 'La cita ya estaba confirmada.',
                ]);
            }

            // 1) Guardar status anterior para auditoría
            $oldStatus = $appointment->status;

            // 2) Actualizar estado
            $appointment->status = 'confirmed';
            $appointment->save();

            // 3) ✅ Registrar en appointment_audits
            $actorId = Auth::id();

            // actor_role: intento 1 (Spatie), fallback a null
            $actorRole = null;
            if (auth()->check()) {
                try {
                    if (method_exists(auth()->user(), 'getRoleNames')) {
                        $roles = auth()->user()->getRoleNames();
                        $actorRole = $roles && count($roles) ? $roles[0] : null;
                    }
                } catch (\Throwable $e) {
                    $actorRole = null;
                }
            }

            DB::table('appointment_audits')->insert([
                'appointment_id' => $appointment->id,
                'actor_user_id'  => $actorId,
                'actor_role'     => $actorRole,

                // ✅ Mejor: acción específica
                'action'         => 'confirm',

                'changed_fields' => json_encode(['status'], JSON_UNESCAPED_UNICODE),
                'old_values'     => json_encode(['status' => $oldStatus], JSON_UNESCAPED_UNICODE),
                'new_values'     => json_encode(['status' => 'confirmed'], JSON_UNESCAPED_UNICODE),

                'reason'         => null,
                'reason_other'   => null,
            ]);

            // 4) 📧 Notificar al paciente si tiene email (sin tumbar la confirmación si falla)
            $email = trim((string) ($appointment->patient_email ?? ''));
            if ($email !== '') {
                try {
                    Notification::route('mail', $email)
                        ->notify(new PatientNotificationAppointmentConfirmed($appointment));
                } catch (\Throwable $e) {
                    logger()->error('CONFIRM: email notification failed', [
                        'appointment_id' => $appointment->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 5) Mantener coherencia con el sistema
            event(new StatusUpdated($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Cita confirmada correctamente.',
            ]);

        } catch (\Throwable $e) {

            logger()->error('CONFIRM: failed', [
                'appointment_id' => $appointment->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo confirmar la cita.',
            ], 500);
        }
    }

     public function cancel(Appointment $appointment, Request $request)
    {
        try {

            // ✅ Si ya está cancelada, no volver a cancelar
            if (($appointment->status ?? null) === 'cancelled') {
                return response()->json([
                    'success' => true,
                    'message' => 'La cita ya estaba cancelada.',
                ]);
            }

            // 1) Guardar status anterior para auditoría
            $oldStatus = $appointment->status;

            // 2) Actualizar estado
            $appointment->status = 'cancelled';
            $appointment->save();

            // 3) ✅ Registrar en appointment_audits (similar a confirm)
            $actorId = Auth::id();

            $actorRole = null;
            if (auth()->check()) {
                try {
                    if (method_exists(auth()->user(), 'getRoleNames')) {
                        $roles = auth()->user()->getRoleNames();
                        $actorRole = $roles && count($roles) ? $roles[0] : null;
                    }
                } catch (\Throwable $e) {
                    $actorRole = null;
                }
            }

            DB::table('appointment_audits')->insert([
                'appointment_id' => $appointment->id,
                'actor_user_id'  => $actorId,
                'actor_role'     => $actorRole,

                'action'         => 'cancel',

                'changed_fields' => json_encode(['status'], JSON_UNESCAPED_UNICODE),
                'old_values'     => json_encode(['status' => $oldStatus], JSON_UNESCAPED_UNICODE),
                'new_values'     => json_encode(['status' => 'cancelled'], JSON_UNESCAPED_UNICODE),

                'reason'         => null,
                'reason_other'   => null,
            ]);

            // ✅ Si existiera un hold “colgado” de ese mismo turno, lo limpiamos
            AppointmentHold::where('employee_id', $appointment->employee_id)
                ->where('appointment_date', $appointment->appointment_date)
                ->where('appointment_time', $appointment->appointment_time)
                ->where('appointment_end_time', $appointment->appointment_end_time)
                ->delete();

            // 4) Mantener coherencia con el sistema
            event(new StatusUpdated($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Cita cancelada exitosamente.',
            ]);

        } catch (\Throwable $e) {

            logger()->error('CANCEL: failed', [
                'appointment_id' => $appointment->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo cancelar la cita.',
            ], 500);
        }
    }

    public function audits(Appointment $appointment)
    {
        // Traer audits + nombre del actor
        $rows = DB::table('appointment_audits as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.actor_user_id')
            ->where('a.appointment_id', $appointment->id)
            ->orderByDesc('a.id')
            ->select([
                'a.id',
                'a.created_at',
                'a.actor_role',
                'a.action',
                'a.reason',
                'a.reason_other',
                'a.changed_fields',
                'a.old_values',
                'a.new_values',
                DB::raw("COALESCE(u.name, 'Sistema') as actor_name"),
            ])
            ->get();

        $audits = $rows->map(function ($r) {

            // changed_fields: JSON array ["status","appointment_date",...]
            $changedFields = [];
            if (!empty($r->changed_fields)) {
                $tmp = json_decode($r->changed_fields, true);
                if (is_array($tmp)) $changedFields = $tmp;
            }

            // old_values: JSON object {"status":"pending",...}
            $old = [];
            if (!empty($r->old_values)) {
                $tmp = json_decode($r->old_values, true);
                if (is_array($tmp)) $old = $tmp;
            }

            // new_values: JSON object {"status":"confirmed",...}
            $new = [];
            if (!empty($r->new_values)) {
                $tmp = json_decode($r->new_values, true);
                if (is_array($tmp)) $new = $tmp;
            }

            // Armar formato simple: { field: {before, after} }
            $changes = [];
            foreach ($changedFields as $field) {
                $changes[$field] = [
                    'before' => $old[$field] ?? null,
                    'after'  => $new[$field] ?? null,
                ];
            }

            return [
                'id' => $r->id,
                'created_at' => $r->created_at,
                'actor_name' => $r->actor_name,
                'actor_role' => $r->actor_role,
                'action' => $r->action,
                'reason' => $r->reason,
                'reason_other' => $r->reason_other,
                'changed_fields' => $changes,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'audits' => $audits,
        ]);
    }

}
