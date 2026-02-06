<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentAdminNewBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $bookingId = $this->data['booking_id'] ?? null;

        return $this->subject(
                $bookingId
                    ? "Nueva cita registrada - Código {$bookingId}"
                    : "Nueva cita registrada - FamySALUD"
            )
            ->view('emails.admin_new_booking');
    }
}