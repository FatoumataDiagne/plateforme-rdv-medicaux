<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RendezVousResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_nom' => $this->patient ? ($this->patient->prenom . ' ' . $this->patient->nom) : 'N/A',
            'patient_email' => $this->patient->email ?? 'N/A',
            'medecin_id' => $this->medecin_id,
            'medecin_nom' => $this->medecin && $this->medecin->user 
                ? ($this->medecin->user->prenom . ' ' . $this->medecin->user->nom) 
                : 'N/A',
            'medecin_specialite' => $this->medecin && $this->medecin->specialite 
                ? $this->medecin->specialite->nom 
                : 'N/A',
            'date' => $this->date,
            'heure_debut' => $this->heure_debut,
            'heure_fin' => $this->heure_fin,
            'statut' => $this->statut,
            'statut_paiement' => $this->statut_paiement,
            'mode_paiement' => $this->mode_paiement,
            'montant' => (float) $this->montant,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}