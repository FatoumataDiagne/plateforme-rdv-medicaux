<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class RendezVousConfirme extends Notification implements ShouldQueue
{
    use Queueable;

    public $rendezVous;

    public function __construct(RendezVous $rendezVous)
    {
        $this->rendezVous = $rendezVous;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Envoi par email ET sauvegarde en base
    }

    public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('✅ Confirmation de votre rendez-vous médical')
        ->view('emails.confirmation-rendezvous', ['rendezVous' => $this->rendezVous])
        ->action('Voir mes rendez-vous', url('/rendez-vous'));
}

    public function toArray($notifiable)
    {
        return [
            'rendez_vous_id' => $this->rendezVous->id,
            'message' => 'Votre rendez-vous du ' . $this->rendezVous->date->format('d/m/Y') . ' a été confirmé',
            'medecin' => $this->rendezVous->medecin->user->name,
            'specialite' => $this->rendezVous->medecin->specialite->name
        ];
    }
}