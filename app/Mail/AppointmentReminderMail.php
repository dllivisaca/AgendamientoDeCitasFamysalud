<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AppointmentReminderMail extends Mailable
{
    public array $data;
    public string $kind;

    public function __construct(array $data, string $kind)
    {
        $this->data = $data;
        $this->kind = $kind;
    }

    public function build()
    {
        $subject = $this->kind === 'MANUAL_3H'
            ? 'Recordatorio: tu cita es hoy'
            : 'Recordatorio: tu cita es mañana';

        return $this->subject($subject)
            ->view('emails.appointment_reminder');
    }
}