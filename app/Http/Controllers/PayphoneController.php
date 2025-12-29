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

        // Confirm (POST) - IMPORTANTÍSIMO (si no confirmas, pueden revertir)
        $confirmUrl = 'https://pay.payphonetodoesposible.com/api/button/V2/Confirm';

        $confirm = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.payphone.token'),
            'Content-Type'  => 'application/json',
        ])->post($confirmUrl, [
            'id' => $id,
            'clientTxId' => $clientTransactionId,
        ]);

        $body = $confirm->json();

        // Guarda respuesta
        DB::table('payment_attempts')
            ->where('client_transaction_id', $clientTransactionId)
            ->update([
                'provider_transaction_id' => $body['transactionId'] ?? null,
                'authorization_code' => $body['authorizationCode'] ?? null,
                'provider_status' => $body['status'] ?? ($body['transactionStatus'] ?? null),
                'confirm_response' => json_encode($body),
                'status' => ($body['transactionStatus'] ?? '') === 'Approved' ? 'approved' : 'failed',
                'updated_at' => now(),
            ]);

        // Si aprobado -> aquí recién creas la cita (o la confirmas) usando appointment_hold_id
        // TIP: recupera payment_attempts.appointment_hold_id y arma la cita desde el hold.
        if (($body['transactionStatus'] ?? '') === 'Approved') {

            return DB::transaction(function () use ($clientTransactionId, $id) {

                $attempt = DB::table('payment_attempts')
                    ->where('client_transaction_id', $clientTransactionId)
                    ->lockForUpdate()
                    ->first();

                if (!$attempt) {
                    return redirect('/')->with('error', 'No se encontró el intento de pago.');
                }

                // ✅ Anti-duplicados: si ya creaste cita para este pago, no repitas
                $exists = DB::table('appointments')
                    ->where('client_transaction_id', $clientTransactionId)
                    ->first();

                if ($exists) {
                    // Si el hold aún existe, lo puedes borrar por limpieza
                    DB::table('appointment_holds')->where('id', $attempt->appointment_hold_id)->delete();
                    return redirect('/')->with('success', 'Pago aprobado. Tu cita ya estaba confirmada ✅');
                }

                $hold = DB::table('appointment_holds')
                    ->where('id', $attempt->appointment_hold_id)
                    ->lockForUpdate()
                    ->first();

                if (!$hold) {
                    return redirect('/')->with('error', 'El turno reservado expiró o no existe.');
                }

                $payload = json_decode($attempt->init_response ?? '{}', true);

                // booking_id obligatorio en tu tabla
                $bookingId = $payload['booking_id'] ?? ('BK-' . strtoupper(Str::random(12)));

                // ✅ Inserta en appointments con TUS columnas
                DB::table('appointments')->insert([
                    // IDs base
                    'user_id'      => $attempt->user_id,
                    'employee_id'  => $hold->employee_id,
                    'service_id'   => $hold->service_id,
                    'booking_id'   => $bookingId,

                    // Paciente (obligatorios en tu tabla: full_name, email, phone)
                    'patient_full_name' => $payload['patient_full_name'],
                    'patient_email'     => $payload['patient_email'],
                    'patient_phone'     => $payload['patient_phone'],

                    // Opcionales
                    'patient_dob'        => $payload['patient_dob'] ?? null,
                    'patient_doc_type'   => $payload['patient_doc_type'] ?? null,
                    'patient_doc_number' => $payload['patient_doc_number'] ?? null,
                    'patient_address'    => $payload['patient_address'] ?? null,
                    'patient_notes'      => $payload['patient_notes'] ?? null,

                    // Facturación
                    'billing_name'       => $payload['billing_name'] ?? null,
                    'billing_doc_type'   => $payload['billing_doc_type'] ?? null,
                    'billing_doc_number' => $payload['billing_doc_number'] ?? null,
                    'billing_email'      => $payload['billing_email'] ?? null,
                    'billing_phone'      => $payload['billing_phone'] ?? null,
                    'billing_address'    => $payload['billing_address'] ?? null,

                    // Montos
                    'amount'          => $attempt->amount,        // total pagado
                    'payment_method'  => 'card',
                    'amount_standard' => $attempt->amount,        // si tarjeta = estándar
                    'discount_amount' => 0,

                    // Fecha/hora vienen del hold
                    'appointment_date'     => $hold->appointment_date,
                    'appointment_time'     => $hold->appointment_time,
                    'appointment_end_time' => $hold->appointment_end_time ?? null,
                    'appointment_mode'     => $payload['appointment_mode'] ?? 'presencial',

                    // Zona horaria
                    'patient_timezone'       => $payload['patient_timezone'] ?? null,
                    'patient_timezone_label' => $payload['patient_timezone_label'] ?? null,

                    // Consentimientos
                    'data_consent'      => $payload['data_consent'] ?? 0,
                    'terms_accepted'    => 1,
                    'terms_accepted_at' => now(),

                    // Estados
                    'status'         => 'confirmed',
                    'payment_status' => 'paid',

                    // Payphone (para trazabilidad)
                    'client_transaction_id' => $clientTransactionId,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ✅ Consumir el hold: eliminarlo (tu paso 6)
                DB::table('appointment_holds')->where('id', $hold->id)->delete();

                return redirect('/')->with('success', "Pago aprobado. Cita confirmada ✅ Código: {$bookingId}");
            });
        }

        return redirect('/')->with('error', 'Pago no aprobado o cancelado.');
    }
}