<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class NouveauRendezVousMedecin extends Notification implements ShouldQueue
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
            ->subject('📅 Nouveau rendez-vous programmé')
            ->greeting('Bonjour Dr. ' . $notifiable->name . ' !')
            ->line('Un nouveau rendez-vous a été programmé.')
            ->line('**Patient :** ' . $this->rendezVous->patient->user->name)
            ->line('**Date :** ' . $this->rendezVous->date->format('d/m/Y'))
            ->line('**Heure :** ' . substr($this->rendezVous->heure_debut, 0, 5))
            ->line('**Téléphone :** ' . $this->rendezVous->patient->user->telephone)
            ->action('Voir mes rendez-vous', url('/medecin/rendez-vous'))
            ->line('Merci de votre disponibilité !');
    }

    public function toArray($notifiable)
    {
        return [
            'rendez_vous_id' => $this->rendezVous->id,
            'type' => 'nouveau_rdv_medecin',
            'message' => 'Nouveau rendez-vous avec ' . $this->rendezVous->patient->user->name,
            'date' => $this->rendezVous->date->format('d/m/Y'),
            'heure' => $this->rendezVous->heure_debut
        ];
    }
}