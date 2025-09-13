<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\RendezVous;

class PatientConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rendezVous;

    public function __construct(RendezVous $rendezVous)
    {
        $this->rendezVous = $rendezVous;
    }

    public function build()
    {
        return $this->subject('Confirmation de votre rendez-vous médical')
                    ->view('emails.confirmation-rendezvous');
    }
}