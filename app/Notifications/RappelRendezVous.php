<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class RappelRendezVous extends Notification implements ShouldQueue
{
    use Queueable;

    public $rendezVous;

    public function __construct(RendezVous $rendezVous)
    {
        $this->rendezVous = $rendezVous;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📋 Rappel : Votre rendez-vous médical demain')
            ->view('emails.rappel-rendezvous', ['rendezVous' => $this->rendezVous])
            ->action('Voir mes rendez-vous', url('/rendez-vous'));
    }

    public function toArray($notifiable)
    {
        return [
            'rendez_vous_id' => $this->rendezVous->id,
            'type' => 'rappel',
            'message' => 'Rappel : Votre rendez-vous est demain à ' . $this->rendezVous->heure_debut,
            'medecin' => $this->rendezVous->medecin->user->name,
            'date' => $this->rendezVous->date->format('d/m/Y')
        ];
    }
}