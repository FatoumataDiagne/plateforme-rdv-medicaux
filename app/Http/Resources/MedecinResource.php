<?php

namespace App\Http\Resources; 

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class MedecinResource extends JsonResource
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
            'prenom' => $this->user ? $this->user->prenom : null,
            'nom' => $this->user ? $this->user->nom : null,
            'email' => $this->user ? $this->user->email : null,
            'telephone' => $this->user ? $this->user->telephone : null,
            'specialite' => $this->specialite ? $this->specialite->nom : null,
            'tarif_consultation' => $this->tarif_consultation,
            'adresse_cabinet' => $this->adresse_cabinet,
            'ville' => $this->ville,
            'code_postal' => $this->code_postal,
            'numero_ordre' => $this->numero_ordre,
        ];
    }
}
