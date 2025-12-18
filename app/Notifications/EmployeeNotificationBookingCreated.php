<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class EmployeeNotificationBookingCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
            ->greeting('Hello ' . ($this->appointment->employee->user['name'] ?? ''))
            ->subject('New Booking Created: ' . ($this->appointment['patient_full_name'] ?? ''))
            ->line('**Appointment Details:**')
            ->line('Patient Name: ' . ($this->appointment['patient_full_name'] ?? ''))
            ->line('Patient Email: ' . ($this->appointment['patient_email'] ?? ''))
            ->line('Patient Phone: ' . ($this->appointment['patient_phone'] ?? ''))
            ->line('Service: ' . ($this->appointment->service['title'] ?? ''))
            ->line('Amount: ' . ($this->appointment['amount'] ?? ''))
            ->line('Appointment Date: ' . Carbon::parse($this->appointment['appointment_date'])->format('d M Y'))
            ->line('Slot Time: ' . ($this->appointment['appointment_time'] ?? ''))
            ->line('Mode: ' . ($this->appointment['appointment_mode'] ?? ''))
            ->line('Patient Timezone: ' . ($this->appointment['patient_timezone_label'] ?? $this->appointment['patient_timezone'] ?? ''))
            ->line('Thank you for using our application !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
