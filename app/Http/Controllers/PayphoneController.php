<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;
    use App\Mail\AppointmentRegisteredMail;
    use App\Mail\AppointmentAdminNewBookingMail;
    use App\Models\User;

    class PayphoneController extends Controller
    {
        public function init(Request $request)
        {
            $request->headers->set('Accept', 'application/json');
            $request->validate([
                'appointment_hold_id' => ['required','integer'],
                'amount' => ['required','numeric','min:0.01'],
                'payload' => ['required','array'],
                'payload.patient_full_name' => ['required','string'],
                'payload.patient_email' => ['required','string'],
                'payload.patient_phone' => ['required','string'],
            ]);

            $payload = $request->input('payload', []);

            Log::info('[PayPhone INIT] payload keys', array_keys($payload ?? []));
            Log::info('[PayPhone INIT] payload sample', [
                'patient_dob' => $payload['patient_dob'] ?? null,
                'patient_doc_type' => $payload['patient_doc_type'] ?? null,
                'patient_doc_number' => $payload['patient_doc_number'] ?? null,
                'patient_address' => $payload['patient_address'] ?? null,
                'patient_notes' => $payload['patient_notes'] ?? null,
                'billing_email' => $payload['billing_email'] ?? null,
                'billing_phone' => $payload['billing_phone'] ?? null,
                'patient_timezone' => $payload['patient_timezone'] ?? null,
                'patient_timezone_label' => $payload['patient_timezone_label'] ?? null,
                'data_consent' => $payload['data_consent'] ?? null,
            ]);

            $clientTxId = (string) Str::uuid();

            // Payphone usa centavos
            $amountCents = (int) round($request->amount * 100);

            DB::table('payment_attempts')->insert([
                'appointment_hold_id' => $request->appointment_hold_id,
                'user_id' => auth()->id(),
                'provider' => 'payphone',
                'status' => 'initiated',
                'client_transaction_id' => $clientTxId,
                'amount' => $request->amount,
                'currency' => 'USD',
                'init_response' => json_encode($payload), // ✅ guardamos payload completo
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'token' => config('services.payphone.token'), // token SDK
                'clientTransactionId' => $clientTxId,
                'amountCents' => $amountCents,
                'storeId' => config('services.payphone.store_id'),
                // ⚠️ NO recomiendo enviar token al frontend
            ]);
        }

        public function response(Request $request)
        {
            // Payphone redirige con ?id=...&clientTransactionId=...
            $id = (int) $request->query('id', 0);
            $clientTransactionId = (string) $request->query('clientTransactionId', '');

            if (!$id || !$clientTransactionId) {
                return redirect('/')->with('error', 'Pago no válido.');
            }

            // ✅ IMPORTANTE:
            // Aquí ya NO confirmamos, solo reenviamos al home con los parámetros.
            // Tu JS en index.blade.php detecta estos params y llama /payments/payphone/confirm
            return redirect('/?id=' . urlencode((string)$id) . '&clientTransactionId=' . urlencode($clientTransactionId));
        }

        public function confirm(Request $request)
        {
            $request->headers->set('Accept', 'application/json');

            $id = (int) $request->query('id', 0);
            $clientTransactionId = (string) $request->query('clientTransactionId', '');

            if (!$id || !$clientTransactionId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Pago no válido.',
                ], 422);
            }

            // ✅ PASO 1: validar que el intento exista ANTES de llamar a PayPhone
            $attempt = DB::table('payment_attempts')
                ->where('client_transaction_id', $clientTransactionId)
                ->first();

            if (!$attempt) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No existe un intento de pago iniciado para este clientTransactionId.',
                ], 404);
            }

            // Confirm (POST)
            $confirmUrl = 'https://pay.payphonetodoesposible.com/api/button/V2/Confirm';

            $confirm = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.payphone.token'),
                'Content-Type'  => 'application/json',
            ])->post($confirmUrl, [
                'id' => $id,
                'clientTxId' => $clientTransactionId,
            ]);

            $body = $confirm->json() ?? [];

            // Guarda respuesta
            DB::table('payment_attempts')
                ->where('client_transaction_id', $clientTransactionId)
                ->update([
                    'provider_transaction_id' => $body['transactionId'] ?? null,
                    'authorization_code'      => $body['authorizationCode'] ?? null,
                    'provider_status'         => $body['status'] ?? ($body['transactionStatus'] ?? null),
                    'confirm_response'        => json_encode($body),
                    'status'                  => ($body['transactionStatus'] ?? '') === 'Approved' ? 'approved' : 'failed',
                    'updated_at'              => now(),
                ]);

            if (($body['transactionStatus'] ?? '') !== 'Approved') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Pago no aprobado o cancelado.',
                    'provider' => $body,
                ], 200);
            }

            // ✅ PASO 2: Validar monto y moneda contra lo esperado (payment_attempts)
            $attemptCheck = DB::table('payment_attempts')
                ->where('client_transaction_id', $clientTransactionId)
                ->first();

            if (!$attemptCheck) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No existe el intento de pago para validar monto/moneda.',
                ], 404);
            }

            // PayPhone devuelve amount y currency en confirm
            $ppAmount   = isset($body['amount']) ? (float) $body['amount'] : null;
            $ppCurrency = isset($body['currency']) ? strtoupper((string) $body['currency']) : null;

            $expectedAmount   = (float) $attemptCheck->amount;
            // Tú SIEMPRE guardas USD en init(), así que lo dejamos fijo:
            $expectedCurrency = 'USD';

            /**
             * Normalizar monto por si PayPhone lo devuelve en centavos:
             * Ej: expected = 16.20, provider = 1620  -> 1620/100 = 16.20
             */
            $ppAmountNormalized = $ppAmount;
            if ($ppAmount !== null) {
                // Si viene "demasiado grande" comparado al esperado, probamos /100
                if ($ppAmount > ($expectedAmount * 10) && abs(($ppAmount / 100) - $expectedAmount) < 0.01) {
                    $ppAmountNormalized = $ppAmount / 100;
                }
            }

            // Comparación de monto con tolerancia mínima por decimales
            $amountOk = ($ppAmountNormalized !== null) && (abs($ppAmountNormalized - $expectedAmount) < 0.01);

            // Moneda: si PayPhone no manda currency, asumimos USD (porque tu intento es USD)
            $currencyOk = ($ppCurrency === null) || ($ppCurrency === $expectedCurrency);

            if (!$amountOk || !$currencyOk) {
                // Marca el intento como fallo por mismatch y guarda confirm_response (ya lo guardas arriba, igual actualizamos status)
                DB::table('payment_attempts')
                    ->where('client_transaction_id', $clientTransactionId)
                    ->update([
                        'status' => 'failed',
                        'provider_status' => 'mismatch_amount_currency',
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'ok' => false,
                    'message' => 'Pago aprobado, pero monto/moneda no coinciden con lo esperado.',
                    'expected' => [
                        'amount' => $expectedAmount,
                        'currency' => $expectedCurrency,
                    ],
                    'provider' => [
                        'amount_raw' => $ppAmount,
                        'amount_normalized' => $ppAmountNormalized,
                        'currency' => $ppCurrency,
                    ],
                ], 200);
            }

            // Si aprobado -> crea la cita (igual que hacías antes) y responde con URL final
            return DB::transaction(function () use ($clientTransactionId) {

                $attempt = DB::table('payment_attempts')
                    ->where('client_transaction_id', $clientTransactionId)
                    ->lockForUpdate()
                    ->first();

                if (!$attempt) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No se encontró el intento de pago.',
                    ], 404);
                }

                // Anti-duplicados
                $exists = DB::table('appointments')
                    ->where('client_transaction_id', $clientTransactionId)
                    ->first();

                if ($exists) {
                    DB::table('appointment_holds')->where('id', $attempt->appointment_hold_id)->delete();

                    // ✅ Email: Registro de cita (PayPhone) - idempotente (no tumba flujo si falla)
                    try {
                        $toEmail = trim((string) ($exists->patient_email ?? ''));
                        if ($toEmail !== '') {

                            // Si tienes columna tipo "registered_mail_sent_at" mejor; si no, enviamos siempre.
                            // Para evitar doble envío sin columna, puedes omitir esta parte.

                            // Obtener titles (service/category) sin Eloquent:
                            $serviceTitle = null;
                            $categoryTitle = null;

                            if (!empty($exists->service_id)) {
                                $row = DB::table('services as s')
                                    ->leftJoin('categories as c', 'c.id', '=', 's.category_id')
                                    ->where('s.id', $exists->service_id)
                                    ->select(['s.title as service_title', 'c.title as category_title'])
                                    ->first();

                                $serviceTitle = $row->service_title ?? null;
                                $categoryTitle = $row->category_title ?? null;
                            }

                            $dateStr = $exists->appointment_date ?? null;

                            $timeStr = $exists->appointment_time ?? null;
                            $timeShort = $timeStr ? substr((string)$timeStr, 0, 5) : null;

                            $endStr = $exists->appointment_end_time ?? null;
                            $endShort = $endStr ? substr((string)$endStr, 0, 5) : null;

                            $startsAt = ($dateStr && $timeShort) ? ($dateStr.' '.$timeShort.':00') : null;
                            $endsAt   = ($dateStr && $endShort)  ? ($dateStr.' '.$endShort.':00')  : null;

                            $mailData = [
                                'date' => $dateStr,
                                'time' => $timeShort,
                                'starts_at' => $startsAt,
                                'end_time' => $endShort,
                                'ends_at' => $endsAt,
                                'mode' => $exists->appointment_mode ?? null,
                                'patient_timezone' => $exists->patient_timezone ?? null,
                                'service' => $serviceTitle,
                                'area' => $categoryTitle,
                            ];

                            Mail::to($toEmail)->send(new AppointmentRegisteredMail($mailData));

                            Log::info('[PayPhone] registered mail sent (existing appointment)', [
                                'booking_id' => $exists->booking_id ?? null,
                                'to' => $toEmail,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('[PayPhone] registered mail FAILED (existing appointment)', [
                            'booking_id' => $exists->booking_id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // ✅ Email admin: Nueva cita registrada (PayPhone/card) - EXISTING appointment
                    try {

                        // Titles sin Eloquent (ya lo haces arriba)
                        $serviceTitle = $serviceTitle ?? null;
                        $categoryTitle = $categoryTitle ?? null;

                        // Profesional (employee -> user)
                        $professionalName = null;
                        if (!empty($exists->employee_id)) {
                            $empRow = DB::table('employees as e')
                                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                                ->where('e.id', $exists->employee_id)
                                ->select([
                                    DB::raw('COALESCE(u.name, e.full_name) as professional_name'),
                                ])
                                ->first();

                            $professionalName = $empRow->professional_name ?? null;
                        }

                        $dateStr = $exists->appointment_date ?? null;

                        $timeStr = $exists->appointment_time ?? null;
                        $timeShort = $timeStr ? substr((string)$timeStr, 0, 5) : null;

                        $endStr = $exists->appointment_end_time ?? null;
                        $endShort = $endStr ? substr((string)$endStr, 0, 5) : null;

                        $startsAt = ($dateStr && $timeShort) ? ($dateStr.' '.$timeShort.':00') : null;
                        $endsAt   = ($dateStr && $endShort)  ? ($dateStr.' '.$endShort.':00')  : null;

                        $mailDataAdmin = [
                            'booking_id' => $exists->booking_id ?? null,

                            'date' => $dateStr,
                            'time' => $timeShort,
                            'starts_at' => $startsAt,
                            'end_time' => $endShort,
                            'ends_at' => $endsAt,

                            'mode' => $exists->appointment_mode ?? 'presencial',
                            'patient_timezone' => $exists->patient_timezone ?? null,

                            'area' => $categoryTitle,
                            'service' => $serviceTitle,
                            'professional' => $professionalName,

                            'patient_full_name' => $exists->patient_full_name ?? null,
                            'patient_email' => $exists->patient_email ?? null,
                            'patient_phone' => $exists->patient_phone ?? null,

                            'payment_method' => $exists->payment_method ?? 'card',
                            'payment_status' => $exists->payment_status ?? 'paid',
                            'amount' => $exists->amount ?? null,
                        ];

                        // ✅ Admin fijo: Users ID = 1
                        $adminEmail = trim((string) (User::find(1)->email ?? ''));

                        Log::info('[PayPhone] ADMIN NEW BOOKING MAIL (existing) - admin email resolved', [
                            'admin_user_id' => 1,
                            'admin_email' => $adminEmail,
                            'booking_id' => $exists->booking_id ?? null,
                        ]);

                        if ($adminEmail !== '') {

                            // ✅ MISMA lógica que AppointmentController.php
                            $maxAttempts = 3;         // 1 intento + 2 reintentos
                            $delaysSec = [5, 15, 30]; // espera progresiva

                            for ($i = 0; $i < $maxAttempts; $i++) {
                                try {

                                    if ($i > 0) {
                                        $sleep = $delaysSec[$i] ?? 30;
                                        sleep($sleep);
                                    } else {
                                        usleep(500000); // 0.5s
                                    }

                                    Mail::to($adminEmail)->send(new AppointmentAdminNewBookingMail($mailDataAdmin));

                                    Log::info('[PayPhone] ADMIN NEW BOOKING MAIL (existing): sent OK', [
                                        'to' => $adminEmail,
                                        'attempt' => $i + 1,
                                        'booking_id' => $exists->booking_id ?? null,
                                    ]);

                                    break;

                                } catch (\Throwable $e) {

                                    $msg = $e->getMessage();

                                    Log::warning('[PayPhone] ADMIN NEW BOOKING MAIL (existing): attempt failed', [
                                        'to' => $adminEmail,
                                        'attempt' => $i + 1,
                                        'error' => $msg,
                                        'booking_id' => $exists->booking_id ?? null,
                                    ]);

                                    if ($i === $maxAttempts - 1) {
                                        throw $e;
                                    }

                                    if (stripos($msg, 'Too many emails per second') === false && stripos($msg, '550 5.7.0') === false) {
                                        throw $e;
                                    }
                                }
                            }

                        } else {
                            Log::warning('[PayPhone] ADMIN NEW BOOKING MAIL (existing): skipped (admin email empty)', [
                                'admin_user_id' => 1,
                                'booking_id' => $exists->booking_id ?? null,
                            ]);
                        }

                    } catch (\Throwable $e) {
                        Log::error('[PayPhone] ADMIN NEW BOOKING MAIL FAILED (existing)', [
                            'booking_id' => $exists->booking_id ?? null,
                            'error' => $e->getMessage(),
                            'trace' => substr($e->getTraceAsString(), 0, 2000),
                        ]);
                    }

                    return response()->json([
                        'ok' => true,
                        'redirect_url' => url('/?paid=1&booking_id=' . urlencode((string)($exists->booking_id ?? ''))),
                        'booking_id' => $exists->booking_id ?? null,
                    ], 200);
                }

                $hold = DB::table('appointment_holds')
                    ->where('id', $attempt->appointment_hold_id)
                    ->lockForUpdate()
                    ->first();

                if (!$hold) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'El turno reservado expiró o no existe.',
                    ], 200);
                }

                // ✅ PASO 3: Validar que el hold esté "activo" (no expirado) y sea consistente

                // 3.B) El hold debe tener los datos del slot completos
                $missing = [];
                if (empty($hold->employee_id)) $missing[] = 'employee_id';
                if (empty($hold->service_id)) $missing[] = 'service_id';
                if (empty($hold->appointment_date)) $missing[] = 'appointment_date';
                if (empty($hold->appointment_time)) $missing[] = 'appointment_time';
                if (empty($hold->appointment_end_time)) $missing[] = 'appointment_end_time';

                if (!empty($missing)) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'El hold existe pero está incompleto (slot inválido).',
                        'missing' => $missing,
                    ], 200);
                }

                // 3.A) Validar expiración real usando expires_at
                if (!empty($hold->expires_at) && now()->greaterThan(\Carbon\Carbon::parse($hold->expires_at))) {
                    // Opcional: borrar el hold expirado para limpiar
                    DB::table('appointment_holds')->where('id', $hold->id)->delete();

                    return response()->json([
                        'ok' => false,
                        'message' => 'El turno reservado expiró.',
                    ], 200);
                }

                $payload = json_decode($attempt->init_response ?? '{}', true);

                Log::info('[PayPhone] payload keys', array_keys($payload ?? []));
                Log::info('[PayPhone] payload sample', [
                    'patient_dob' => $payload['patient_dob'] ?? null,
                    'billing_email' => $payload['billing_email'] ?? null,
                    'billing_phone' => $payload['billing_phone'] ?? null,
                    'data_consent' => $payload['data_consent'] ?? null,
                    'patient_timezone' => $payload['patient_timezone'] ?? null,
                    'patient_timezone_label' => $payload['patient_timezone_label'] ?? null,
                ]);

                $norm = function ($v) {
                    if ($v === null) return null;
                    if (is_string($v)) {
                        $v = trim($v);
                        return $v === '' ? null : $v;
                    }
                    return $v;
                };

                $bookingId = 'FS-' . strtoupper(Str::random(12));

                DB::table('appointments')->insert([
                    'user_id'      => $attempt->user_id,
                    'employee_id'  => $hold->employee_id,
                    'service_id'   => $hold->service_id,
                    'booking_id'   => $bookingId,

                    'patient_full_name' => $payload['patient_full_name'],
                    'patient_email'     => $payload['patient_email'],
                    'patient_phone'     => $payload['patient_phone'],

                    'patient_dob'        => $norm($payload['patient_dob'] ?? null),
                    'patient_doc_type'   => $norm($payload['patient_doc_type'] ?? null),
                    'patient_doc_number' => $norm($payload['patient_doc_number'] ?? null),
                    'patient_address'    => $norm($payload['patient_address'] ?? null),
                    'patient_notes'      => $norm($payload['patient_notes'] ?? null),

                    'billing_name'       => $norm($payload['billing_name'] ?? null),
                    'billing_doc_type'   => $norm($payload['billing_doc_type'] ?? null),
                    'billing_doc_number' => $norm($payload['billing_doc_number'] ?? null),
                    'billing_email'      => $norm($payload['billing_email'] ?? null),
                    'billing_phone'      => $norm($payload['billing_phone'] ?? null),
                    'billing_address'    => $norm($payload['billing_address'] ?? null),

                    'amount'          => $attempt->amount,
                    'payment_method'  => 'card',
                    'amount_standard' => $attempt->amount,
                    'discount_amount' => 0,

                    // ✅ Nuevos campos de pago (NO cambia lógica, solo guarda metadata)
                    'amount_paid' => $attempt->amount,                         // payment_attempts.amount
                    'payment_paid_at' => $attempt->created_at ?? now(),         // payment_attempts.created_at
                    'payment_paid_at_date_source' => 'payphone',                // minúsculas
                    'payment_channel' => 'payphone',                            // minúsculas
                    'appointment_channel' => 'patient_online',                  // minúsculas + underscore

                    'appointment_date'     => $hold->appointment_date,
                    'appointment_time'     => $hold->appointment_time,
                    'appointment_end_time' => $hold->appointment_end_time ?? null,
                    'appointment_mode'     => $payload['appointment_mode'] ?? 'presencial',

                    'patient_timezone'       => $norm($payload['patient_timezone'] ?? null),
                    'patient_timezone_label' => $norm($payload['patient_timezone_label'] ?? null),

                    'data_consent' => (
                        ($payload['data_consent'] ?? null) === true ||
                        ($payload['data_consent'] ?? null) === 1 ||
                        ($payload['data_consent'] ?? null) === "1" ||
                        ($payload['data_consent'] ?? null) === "true" ||
                        ($payload['data_consent'] ?? null) === "on"
                    ) ? 1 : 0,
                    'terms_accepted'    => 1,
                    'terms_accepted_at' => now(),

                    'status'         => 'paid',
                    'payment_status' => 'paid',

                    'client_transaction_id' => $clientTransactionId,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('appointment_holds')->where('id', $hold->id)->delete();

                // ✅ Email: Registro de cita (PayPhone) - no tumbar flujo si falla
                try {
                    $toEmail = trim((string) ($payload['patient_email'] ?? ''));
                    if ($toEmail !== '') {

                        // Obtener titles (service/category) sin Eloquent:
                        $serviceTitle = null;
                        $categoryTitle = null;

                        if (!empty($hold->service_id)) {
                            $row = DB::table('services as s')
                                ->leftJoin('categories as c', 'c.id', '=', 's.category_id')
                                ->where('s.id', $hold->service_id)
                                ->select(['s.title as service_title', 'c.title as category_title'])
                                ->first();

                            $serviceTitle = $row->service_title ?? null;
                            $categoryTitle = $row->category_title ?? null;
                        }

                        $dateStr = $hold->appointment_date ?? null;

                        $timeStr = $hold->appointment_time ?? null;
                        $timeShort = $timeStr ? substr((string)$timeStr, 0, 5) : null;

                        $endStr = $hold->appointment_end_time ?? null;
                        $endShort = $endStr ? substr((string)$endStr, 0, 5) : null;

                        $startsAt = ($dateStr && $timeShort) ? ($dateStr.' '.$timeShort.':00') : null;
                        $endsAt   = ($dateStr && $endShort)  ? ($dateStr.' '.$endShort.':00')  : null;

                        $mailData = [
                            'date' => $dateStr,
                            'time' => $timeShort,
                            'starts_at' => $startsAt,
                            'end_time' => $endShort,
                            'ends_at' => $endsAt,
                            'mode' => $payload['appointment_mode'] ?? 'presencial',
                            'patient_timezone' => $payload['patient_timezone'] ?? null,
                            'service' => $serviceTitle,
                            'area' => $categoryTitle,
                        ];

                        Mail::to($toEmail)->send(new AppointmentRegisteredMail($mailData));

                        Log::info('[PayPhone] registered mail sent', [
                            'booking_id' => $bookingId,
                            'to' => $toEmail,
                        ]);
                    } else {
                        Log::warning('[PayPhone] registered mail skipped (empty email)', [
                            'booking_id' => $bookingId,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('[PayPhone] registered mail FAILED', [
                        'booking_id' => $bookingId,
                        'error' => $e->getMessage(),
                    ]);
                }

                // ✅ Email admin: Nueva cita registrada (PayPhone/card) - no tumbar flujo si falla
                try {

                    // Reusar titles (si ya los calculaste arriba para el correo del paciente)
                    // Si NO existen en tu scope en este punto, vuelve a calcularlos aquí.
                    $serviceTitle = $serviceTitle ?? null;
                    $categoryTitle = $categoryTitle ?? null;

                    if ($serviceTitle === null || $categoryTitle === null) {
                        if (!empty($hold->service_id)) {
                            $row = DB::table('services as s')
                                ->leftJoin('categories as c', 'c.id', '=', 's.category_id')
                                ->where('s.id', $hold->service_id)
                                ->select(['s.title as service_title', 'c.title as category_title'])
                                ->first();

                            $serviceTitle = $row->service_title ?? null;
                            $categoryTitle = $row->category_title ?? null;
                        }
                    }

                    // Profesional (employee -> user)
                    $professionalName = null;
                    if (!empty($hold->employee_id)) {
                        $empRow = DB::table('employees as e')
                            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                            ->where('e.id', $hold->employee_id)
                            ->select([
                                DB::raw('COALESCE(u.name, e.full_name) as professional_name'),
                            ])
                            ->first();

                        $professionalName = $empRow->professional_name ?? null;
                    }

                    $dateStr = $hold->appointment_date ?? null;

                    $timeStr = $hold->appointment_time ?? null;
                    $timeShort = $timeStr ? substr((string)$timeStr, 0, 5) : null;

                    $endStr = $hold->appointment_end_time ?? null;
                    $endShort = $endStr ? substr((string)$endStr, 0, 5) : null;

                    $startsAt = ($dateStr && $timeShort) ? ($dateStr.' '.$timeShort.':00') : null;
                    $endsAt   = ($dateStr && $endShort)  ? ($dateStr.' '.$endShort.':00')  : null;

                    $mailDataAdmin = [
                        'booking_id' => $bookingId,

                        'date' => $dateStr,
                        'time' => $timeShort,
                        'starts_at' => $startsAt,
                        'end_time' => $endShort,
                        'ends_at' => $endsAt,

                        'mode' => $payload['appointment_mode'] ?? 'presencial',
                        'patient_timezone' => $payload['patient_timezone'] ?? null,

                        'area' => $categoryTitle,
                        'service' => $serviceTitle,
                        'professional' => $professionalName,

                        'patient_full_name' => $payload['patient_full_name'] ?? null,
                        'patient_email' => $payload['patient_email'] ?? null,
                        'patient_phone' => $payload['patient_phone'] ?? null,

                        'payment_method' => 'card',
                        'payment_status' => 'paid',
                        'amount' => $attempt->amount ?? null,
                    ];

                    // ✅ Admin fijo: Users ID = 1
                    $adminEmail = trim((string) (User::find(1)->email ?? ''));

                    Log::info('[PayPhone] ADMIN NEW BOOKING MAIL - admin email resolved', [
                        'admin_user_id' => 1,
                        'admin_email' => $adminEmail,
                        'booking_id' => $bookingId,
                    ]);

                    if ($adminEmail !== '') {

                        // ✅ Delay / retry para Mailtrap free (evita rate limit)
                        // Si quieres 1 minuto exacto, cambia $initialDelaySec = 60;
                        usleep(500000); // 0.5s

                        if ($initialDelaySec > 0) {
                            sleep($initialDelaySec);
                        }

                        $maxAttempts = 3;         // 1 intento + 2 reintentos
                        $delaysSec = [5, 15, 30]; // backoff

                        for ($i = 0; $i < $maxAttempts; $i++) {
                            try {

                                if ($i > 0) {
                                    $sleep = $delaysSec[$i] ?? 30;
                                    sleep($sleep);
                                }

                                Mail::to($adminEmail)->send(new AppointmentAdminNewBookingMail($mailDataAdmin));

                                Log::info('[PayPhone] ADMIN NEW BOOKING MAIL: sent OK', [
                                    'to' => $adminEmail,
                                    'attempt' => $i + 1,
                                    'booking_id' => $bookingId,
                                ]);

                                break;

                            } catch (\Throwable $e) {

                                $msg = $e->getMessage();

                                Log::warning('[PayPhone] ADMIN NEW BOOKING MAIL: attempt failed', [
                                    'to' => $adminEmail,
                                    'attempt' => $i + 1,
                                    'error' => $msg,
                                    'booking_id' => $bookingId,
                                ]);

                                // último intento -> lanzar para que lo capture el catch externo
                                if ($i === $maxAttempts - 1) {
                                    throw $e;
                                }

                                // si NO es rate limit, no reintentar
                                if (stripos($msg, 'Too many emails per second') === false && stripos($msg, '550 5.7.0') === false) {
                                    throw $e;
                                }
                            }
                        }

                    } else {
                        Log::warning('[PayPhone] ADMIN NEW BOOKING MAIL: skipped (admin email empty)', [
                            'admin_user_id' => 1,
                            'booking_id' => $bookingId,
                        ]);
                    }

                } catch (\Throwable $e) {
                    Log::error('[PayPhone] ADMIN NEW BOOKING MAIL FAILED', [
                        'booking_id' => $bookingId,
                        'error' => $e->getMessage(),
                        'trace' => substr($e->getTraceAsString(), 0, 2000),
                    ]);
                }

                return response()->json([
                    'ok' => true,
                    'redirect_url' => url('/?paid=1&booking_id=' . urlencode($bookingId)),
                    'booking_id' => $bookingId,
                ], 200);
            });
        }
    }