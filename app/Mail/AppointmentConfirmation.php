<?php
// app/Mail/AppointmentConfirmation.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment; // Importe ton modèle

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment; // Rend la variable accessible dans la vue

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Confirmation de votre rendez-vous médical',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.appointments.confirmation', // Nous créerons cette vue
        );
    }

    public function attachments()
    {
        return [];
    }
}