<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Medecin;

class RendezVousFactory extends Factory
{
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('now', '+30 days');
        
        return [
            'patient_id' => User::factory(),
            'medecin_id' => Medecin::inRandomOrder()->first()->id,
            'date' => $date->format('Y-m-d'),
            'heure_debut' => '09:00:00',
            'heure_fin' => '10:00:00',
            'statut' => $this->faker->randomElement(['en_attente', 'confirme', 'annule']),
            'statut_paiement' => $this->faker->randomElement(['en_attente', 'paye', 'rembourse']),
            'mode_paiement' => $this->faker->randomElement(['en_ligne', 'sur_place', null]),
            'montant' => $this->faker->randomElement([25, 30, 40, 50, 60]),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}