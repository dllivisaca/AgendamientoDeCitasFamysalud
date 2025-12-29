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
            'amount' => ['required','numeric','min:0.01'], // USD
        ]);

        $clientTxId = (string) Str::uuid();

        // Payphone usa centavos: 100 = $1.00
        $amountCents = (int) round($request->amount * 100);

        DB::table('payment_attempts')->insert([
            'appointment_hold_id' => $request->appointment_hold_id,
            'user_id' => auth()->id(),
            'provider' => 'payphone',
            'status' => 'initiated',
            'client_transaction_id' => $clientTxId,
            'amount' => $request->amount,
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'clientTransactionId' => $clientTxId,
            'amountCents' => $amountCents,
            'storeId' => config('services.payphone.store_id'),
            // El token lo sigues manejando en .env, pero Payphone lo necesita en frontend
            'token' => config('services.payphone.token'),
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
            // TODO: crear appointment desde hold + marcar como pagada
            return redirect('/')->with('success', 'Pago aprobado. Cita confirmada.');
        }

        return redirect('/')->with('error', 'Pago no aprobado o cancelado.');
    }
}