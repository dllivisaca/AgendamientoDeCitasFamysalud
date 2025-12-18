<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class UserNotificationBookingUpdated extends Notification implements ShouldQueue
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
            ->greeting('Hello ' . ($this->appointment['patient_full_name'] ?? ''))
            ->subject('Booking Status Updated')
            ->line('Your booking status has been updated to: ' . ($this->appointment['status'] ?? ''))
            ->line('**Appointment Details:**')
            ->line('Name: ' . ($this->appointment['patient_full_name'] ?? ''))
            ->line('Phone: ' . ($this->appointment['patient_phone'] ?? ''))
            ->line('Service: ' . ($this->appointment->service['title'] ?? ''))
            ->line('Staff: ' . ($this->appointment->employee->user['name'] ?? ''))
            ->line('Amount: ' . ($this->appointment['amount'] ?? ''))
            ->line('Appointment Date: ' . Carbon::parse($this->appointment['appointment_date'])->format('d M Y'))
            ->line('Slot Time: ' . ($this->appointment['appointment_time'] ?? ''))
            ->line('Mode: ' . ($this->appointment['appointment_mode'] ?? ''))
            ->line('Your Timezone: ' . ($this->appointment['patient_timezone_label'] ?? $this->appointment['patient_timezone'] ?? ''))
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
