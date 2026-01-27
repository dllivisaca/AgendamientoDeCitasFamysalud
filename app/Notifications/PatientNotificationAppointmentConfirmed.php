<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class PatientNotificationAppointmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hola ' . ($this->appointment['patient_full_name'] ?? ''))
            ->subject('✅ Confirmación de tu cita - FamySalud')
            ->line('Tu cita ha sido **confirmada exitosamente**.')
            ->line('**Detalles de la cita:**')
            ->line('Servicio: ' . ($this->appointment->service['title'] ?? ''))
            ->line('Fecha: ' . Carbon::parse($this->appointment['appointment_date'])->format('d M Y'))
            ->line('Hora: ' . ($this->appointment['appointment_time'] ?? '') . ' - ' . ($this->appointment['appointment_end_time'] ?? ''))
            ->line('Modalidad: ' . ucfirst($this->appointment['appointment_mode'] ?? ''))
            ->line('Profesional: ' . ($this->appointment->employee->user->name ?? ''))
            ->line('Zona horaria: ' . ($this->appointment['patient_timezone_label'] ?? $this->appointment['patient_timezone'] ?? ''))
            ->line('Si necesitas reagendar o tienes alguna consulta, por favor contáctanos.')
            ->salutation('Gracias por confiar en FamySalud 💙');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}