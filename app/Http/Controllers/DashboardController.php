<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Setting;
use Carbon\Carbon;
use App\Events\StatusUpdated;

class DashboardController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrFail();
        $user = auth()->user();

        // Start with base query
        $query = Appointment::query()->with(['employee.user', 'service.category', 'user']);

        // Only admins can see all data - no conditions added
        if (!$user->hasRole('admin')) {
            $query->where(function($q) use ($user) {
                if ($user->employee) {
                    $q->where('employee_id', $user->employee->id);
                }
                $q->orWhere('user_id', $user->id);
            });
        }

        // Format the appointments with proper date handling
        $appointments = $query->whereNotIn('status', ['canceled', 'cancelled'])
            ->get()->map(function ($appointment) {
            // ✅ Parse appointment date (igual)
            $appointmentDate = Carbon::parse($appointment->appointment_date);

            // ✅ Tu BD: appointment_time = inicio, appointment_end_time = fin
            $startTime = trim((string) $appointment->appointment_time);
            $endTime   = trim((string) ($appointment->appointment_end_time ?? ''));

            // Si falta inicio, no se puede renderizar
            if ($startTime === '') {
                \Log::warning("Appointment {$appointment->id} missing appointment_time");
                return null;
            }

            // Construye datetimes (Carbon::parse tolera HH:mm)
            $startDateTime = Carbon::parse($appointmentDate->toDateString() . ' ' . $startTime);

            // Si no hay fin, default +30 min
            $endDateTime = $endTime !== ''
                ? Carbon::parse($appointmentDate->toDateString() . ' ' . $endTime)
                : $startDateTime->copy()->addMinutes(30);

            // Overnight safety
            if ($endDateTime->lt($startDateTime)) {
                $endDateTime->addDay();
            }

            // ✅ AQUÍ va la variable, antes del return array
            $color = $this->getStatusColor($appointment->status);

            // ✅ DOB label desde BD (SIN usar Date() en JS para evitar -1 día por timezone)
            $dobLabel = null;
            if (!empty($appointment->patient_dob)) {
                $dob = Carbon::parse($appointment->patient_dob);

                // Ej: "11 abr 2000" (sin punto)
                $dobLabel = $dob->locale('es')->translatedFormat('d M Y');
                $dobLabel = str_replace('.', '', mb_strtolower($dobLabel, 'UTF-8'));
            }

            // ✅ Edad calculada desde DOB (backend)
            $patientAge = null;
            if (!empty($appointment->patient_dob)) {
                $years = Carbon::parse($appointment->patient_dob)->age;
                $patientAge = $years . ' años';
            }

            return [
                'id' => $appointment->id,
                'booking_id' => $appointment->booking_id ?? null,
                'title' => sprintf(
                    '%s - %s',
                    $appointment->patient_full_name,
                    $appointment->service->title ?? 'Service'
                ),
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'patient_dob_label' => $dobLabel,
                'patient_age' => $patientAge,
                'description' => $appointment->patient_notes,
                'email' => $appointment->patient_email,
                'phone' => $appointment->patient_phone,
                'amount' => $appointment->amount,
                'status' => $appointment->status,
                'staff' => $appointment->employee->user->name ?? 'Unassigned',

                // =======================
                // ✅ CAMPOS PARA MODAL (MODO LECTURA)
                // =======================
                'booking_code'      => $appointment->booking_code ?? null,

                // Paciente (extra)
                'patient_full_name' => $appointment->patient_full_name ?? null,
                'patient_doc_type'  => $appointment->patient_doc_type ?? null,
                'patient_doc_number'=> $appointment->patient_doc_number ?? null,
                'patient_dob'       => $appointment->patient_dob ?? null,
                'patient_address'   => $appointment->patient_address ?? null,
                'patient_timezone'  => $appointment->patient_timezone ?? null,

                // Facturación
                'billing_name'      => $appointment->billing_name ?? null,
                'billing_doc_type'  => $appointment->billing_doc_type ?? null,
                'billing_doc_number'=> $appointment->billing_doc_number ?? null,
                'billing_email'     => $appointment->billing_email ?? null,
                'billing_phone'     => $appointment->billing_phone ?? null,
                'billing_address'   => $appointment->billing_address ?? null,

                // Cita
                'appointment_mode'  => $appointment->appointment_mode ?? null, // virtual/presencial si existe
                'created_at'        => optional($appointment->created_at)->toIso8601String(),

                // =======================
                // ✅ PAGO (lo que te falta)
                // =======================
                'payment_method'        => $appointment->payment_method ?? null,     // card|transfer|cash
                'payment_status'        => $appointment->payment_status ?? null,     // pending|unpaid|partial|paid|refunded
                'amount_paid'           => $appointment->amount_paid ?? null,
                'payment_paid_at'       => $appointment->payment_paid_at ?? null,
                'payment_notes'         => $appointment->payment_notes ?? null,
                'client_transaction_id' => $appointment->client_transaction_id ?? null,

                // Transferencia (si tienes estas columnas)
                'transfer_bank_origin'  => $appointment->transfer_bank_origin ?? null,
                'transfer_payer_name'   => $appointment->transfer_payer_name ?? null,
                'transfer_date'         => $appointment->transfer_date ?? null,
                'transfer_reference'    => $appointment->transfer_reference ?? null,
                'transfer_receipt_path' => $appointment->transfer_receipt_path ?? null,

                // Validación transferencia (si existen)
                'transfer_validation_status' => $appointment->transfer_validation_status ?? null,
                'transfer_validated_at'      => $appointment->transfer_validated_at ?? null,
                'transfer_validated_by'      => $appointment->transfer_validated_by ?? null,
                'transfer_validation_notes'  => $appointment->transfer_validation_notes ?? null,

                'color'           => $color, // ✅ FullCalendar lo entiende en casi todas las configs
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',

                // ✅ Clase para forzar estilos por CSS si algo lo pisa
                'classNames'      => ['appt-status-' . ($appointment->status ?? 'unknown')],
                'service_title' => $appointment->service->title ?? 'Service',
                'name' => $appointment->patient_full_name,
                'notes' => $appointment->patient_notes,
                'area_name' => $appointment->service->category->title
                    ?? 'N/A',
            ];
        })->filter();
        return view('backend.dashboard.index', compact('appointments'));
    }

    // Helper function to get color based on status
    private function getStatusColor($status)
    {
        $s = strtolower(trim((string) $status));

        // Normaliza por si llega con espacios o guiones
        $s = str_replace([' ', '-'], '_', $s);
        $s = preg_replace('/_+/', '_', $s);

        $colors = [
            'pending_verification' => '#7f8c8d',
            'pending_payment'      => '#f39c12',
            'paid'                 => '#2ecc71',
            'confirmed'            => '#3498db',
            'completed'            => '#008000',
            'canceled'             => '#ff0000',
            'rescheduled'          => '#f1c40f',
            'no_show'              => '#e67e22',
            'on_hold'              => '#95a5a6',
        ];

        return $colors[$s] ?? '#7f8c8d';
    }


    // In AppointmentController.php
    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|in:pending_verification,pending_payment,paid,confirmed,completed,canceled,rescheduled,no_show,on_hold'
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $status = strtolower(trim((string) $request->status));
        $status = str_replace([' ', '-'], '_', $status);
        $status = preg_replace('/_+/', '_', $status);

        $appointment->status = $status;
        $appointment->save();

        event(new StatusUpdated($appointment));

        return back()->with('success', 'Status updated successfully');
    }
}
