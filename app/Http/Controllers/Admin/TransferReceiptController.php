<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Storage;

class TransferReceiptController extends Controller
{
    public function show(Appointment $appointment)
    {
        if (!$appointment->transfer_receipt_path) {
            abort(404);
        }

        // Ej: "transfer_proofs / ABC.png" -> corregimos espacios
        $relativePath = trim($appointment->transfer_receipt_path);
        $relativePath = str_replace(' ', '', $relativePath);   // <- clave por tu caso
        $relativePath = ltrim($relativePath, '/');             // transfer_proofs/ABC.png

        /**
         * Intento 1: storage/app/public/transfer_proofs/...
         */
        if (Storage::disk('public')->exists($relativePath)) {
            $fullPath = Storage::disk('public')->path($relativePath);
            return response()->file($fullPath);
        }

        /**
         * Intento 2: storage/app/transfer_proofs/...
         */
        if (Storage::disk('local')->exists($relativePath)) {
            $fullPath = Storage::disk('local')->path($relativePath);
            return response()->file($fullPath);
        }

        /**
         * Intento 3: public/transfer_proofs/...
         */
        $publicPath = public_path($relativePath);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        abort(404);
    }

    public function view(Appointment $appointment)
    {
        // Esta URL es la que devuelve el archivo directo (PDF o imagen)
        $fileUrl = route('admin.appointments.transfer_receipt', ['appointment' => $appointment->id]);

        // Título que queremos en la pestaña
        $title = 'Comprobante de transferencia';

        return view('admin.appointments.transfer_receipt_view', compact('fileUrl', 'title'));
    }
}