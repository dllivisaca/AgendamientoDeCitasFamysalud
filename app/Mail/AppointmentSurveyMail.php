<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentSurveyMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $patientName;
    public string $surveyUrl;

    public function __construct(string $patientName, string $surveyUrl)
    {
        $this->patientName = $patientName;
        $this->surveyUrl   = $surveyUrl;
    }

    public function build()
    {
        return $this->subject('Encuesta de satisfacción - FamySALUD')
            ->view('emails.appointment_survey');
    }
}