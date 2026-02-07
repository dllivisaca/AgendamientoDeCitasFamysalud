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
        $query = Appointment::query()->with(['employee.user', 'service', 'user']);

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
        $appointments = $query->get()->map(function ($appointment) {
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

            return [
                'id' => $appointment->id,
                'title' => sprintf(
                    '%s - %s',
                    $appointment->patient_full_name,
                    $appointment->service->title ?? 'Service'
                ),
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'description' => $appointment->patient_notes,
                'email' => $appointment->patient_email,
                'phone' => $appointment->patient_phone,
                'amount' => $appointment->amount,
                'status' => $appointment->status,
                'staff' => $appointment->employee->user->name ?? 'Unassigned',
                'color' => $this->getStatusColor($appointment->status),
                'service_title' => $appointment->service->title ?? 'Service',
                'name' => $appointment->patient_full_name,
                'notes' => $appointment->patient_notes,
            ];
        })->filter();
        return view('backend.dashboard.index', compact('appointments'));
    }

    // Helper function to get color based on status
    private function getStatusColor($status)
    {
        $colors = [
            'Pending payment' => '#f39c12',
            'Processing' => '#3498db',
            'Paid' => '#2ecc71',
            'Cancelled' => '#ff0000',
            'Completed' => '#008000',
            'On Hold' => '#95a5a6',
            'Rescheduled' => '#f1c40f',
            'No Show' => '#e67e22',
        ];

        return $colors[$status] ?? '#7f8c8d';
    }


    // In AppointmentController.php
    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|in:Pending payment,Processing,Paid,Cancelled,Completed,On Hold,No Show'
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->status = $request->status;
        $appointment->save();

        event(new StatusUpdated($appointment));

        return back()->with('success', 'Status updated successfully');
    }
}
