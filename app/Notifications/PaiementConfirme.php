<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class PaiementConfirme extends Notification implements ShouldQueue
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
            ->subject('✅ Paiement confirmé - Rendez-vous du ' . $this->rendezVous->date->format('d/m/Y'))
            ->view('emails.paiement-confirme', ['rendezVous' => $this->rendezVous])
            ->action('Voir le justificatif', url('/rendez-vous/' . $this->rendezVous->id . '/pdf'));
    }

    public function toArray($notifiable)
    {
        return [
            'rendez_vous_id' => $this->rendezVous->id,
            'type' => 'paiement_confirme',
            'message' => 'Paiement de ' . number_format($this->rendezVous->montant, 2) . ' € confirmé',
            'montant' => $this->rendezVous->montant,
            'mode_paiement' => $this->rendezVous->mode_paiement
        ];
    }
}