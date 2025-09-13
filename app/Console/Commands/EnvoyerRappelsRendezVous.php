<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RendezVous;
use App\Notifications\RappelRendezVous;
use Carbon\Carbon;

class EnvoyerRappelsRendezVous extends Command
{
    protected $signature = 'rappels:envoyer';
    protected $description = 'Envoyer les rappels de rendez-vous 24h à l\'avance';

    public function handle()
    {
        $dateDemain = Carbon::tomorrow()->format('Y-m-d');
        
        // CORRECTION ICI : Supprime .user si patient est déjà un User
        $rendezVous = RendezVous::with(['patient', 'medecin.user', 'medecin.specialite'])
            ->where('date', $dateDemain)
            ->where('statut', 'confirme')
            ->get();

        $this->info('Envoi des rappels pour ' . $rendezVous->count() . ' rendez-vous...');

        foreach ($rendezVous as $rdv) {
            // CORRECTION ICI : Utilise directement $rdv->patient
            if ($rdv->patient) {
                try {
                    $rdv->patient->notify(new RappelRendezVous($rdv));
                    $this->info('Rappel envoyé pour RDV #' . $rdv->id . ' à ' . $rdv->patient->email);
                } catch (\Exception $e) {
                    $this->error('Erreur RDV #' . $rdv->id . ': ' . $e->getMessage());
                }
            } else {
                $this->warn('Aucun patient pour RDV #' . $rdv->id);
            }
        }

        $this->info('Envoi des rappels terminé !');
    }
}