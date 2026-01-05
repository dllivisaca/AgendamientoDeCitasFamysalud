<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;

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
                'init_response' => json_encode($request->input('payload')), // ✅ guardamos payload
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

                $payload = json_decode($attempt->init_response ?? '{}', true);

                $bookingId = $payload['booking_id'] ?? ('BK-' . strtoupper(Str::random(12)));

                DB::table('appointments')->insert([
                    'user_id'      => $attempt->user_id,
                    'employee_id'  => $hold->employee_id,
                    'service_id'   => $hold->service_id,
                    'booking_id'   => $bookingId,

                    'patient_full_name' => $payload['patient_full_name'],
                    'patient_email'     => $payload['patient_email'],
                    'patient_phone'     => $payload['patient_phone'],

                    'patient_dob'        => $payload['patient_dob'] ?? null,
                    'patient_doc_type'   => $payload['patient_doc_type'] ?? null,
                    'patient_doc_number' => $payload['patient_doc_number'] ?? null,
                    'patient_address'    => $payload['patient_address'] ?? null,
                    'patient_notes'      => $payload['patient_notes'] ?? null,

                    'billing_name'       => $payload['billing_name'] ?? null,
                    'billing_doc_type'   => $payload['billing_doc_type'] ?? null,
                    'billing_doc_number' => $payload['billing_doc_number'] ?? null,
                    'billing_email'      => $payload['billing_email'] ?? null,
                    'billing_phone'      => $payload['billing_phone'] ?? null,
                    'billing_address'    => $payload['billing_address'] ?? null,

                    'amount'          => $attempt->amount,
                    'payment_method'  => 'card',
                    'amount_standard' => $attempt->amount,
                    'discount_amount' => 0,

                    'appointment_date'     => $hold->appointment_date,
                    'appointment_time'     => $hold->appointment_time,
                    'appointment_end_time' => $hold->appointment_end_time ?? null,
                    'appointment_mode'     => $payload['appointment_mode'] ?? 'presencial',

                    'patient_timezone'       => $payload['patient_timezone'] ?? null,
                    'patient_timezone_label' => $payload['patient_timezone_label'] ?? null,

                    'data_consent'      => $payload['data_consent'] ?? 0,
                    'terms_accepted'    => 1,
                    'terms_accepted_at' => now(),

                    'status'         => 'paid',
                    'payment_status' => 'paid',

                    'client_transaction_id' => $clientTransactionId,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('appointment_holds')->where('id', $hold->id)->delete();

                return response()->json([
                    'ok' => true,
                    'redirect_url' => url('/?paid=1&booking_id=' . urlencode($bookingId)),
                    'booking_id' => $bookingId,
                ], 200);
            });
        }
    }