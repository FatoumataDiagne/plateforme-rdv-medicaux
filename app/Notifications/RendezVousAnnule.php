<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class RendezVousAnnule extends Notification implements ShouldQueue
{
    use Queueable;

    public $rendezVous;
    public $raison;

    public function __construct(RendezVous $rendezVous, $raison = null)
    {
        $this->rendezVous = $rendezVous;
        $this->raison = $raison;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // Vérifications sécurisées pour éviter les erreurs
        $medecinName = $this->rendezVous->medecin ? $this->rendezVous->medecin->name : 'Médecin';
        $dateRdv = $this->rendezVous->date ? $this->rendezVous->date->format('d/m/Y') : 'Date inconnue';
        $heureRdv = $this->rendezVous->heure_debut ? substr($this->rendezVous->heure_debut, 0, 5) : 'Heure inconnue';

        return (new MailMessage)
            ->subject('❌ Annulation de votre rendez-vous médical')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre rendez-vous médical a été annulé.')
            ->line('**Détails du rendez-vous annulé :**')
            ->line('- Médecin : Dr. ' . $medecinName)
            ->line('- Date : ' . $dateRdv)
            ->line('- Heure : ' . $heureRdv);
            
        if ($this->raison) {
            $mailMessage->line('**Raison :** ' . $this->raison);
        }

        $mailMessage->action('Prendre un nouveau rendez-vous', url('/rendez-vous'))
                   ->line('Nous sommes désolés pour ce contretemps.');

        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            'rendez_vous_id' => $this->rendezVous->id,
            'type' => 'annulation',
            'message' => 'Votre rendez-vous du ' . ($this->rendezVous->date ? $this->rendezVous->date->format('d/m/Y') : '') . ' a été annulé',
            'raison' => $this->raison,
            'medecin' => $this->rendezVous->medecin ? $this->rendezVous->medecin->name : 'Médecin'
        ];
    }
}