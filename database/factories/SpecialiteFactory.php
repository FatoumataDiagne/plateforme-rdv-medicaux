<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialiteFactory extends Factory
{
    public function definition(): array
    {
        $specialites = [
            'Cardiologie', 'Dermatologie', 'Pédiatrie', 'Gynécologie',
            'Neurologie', 'Ophtalmologie', 'Orthopédie', 'Psychiatrie',
            'Radiologie', 'Chirurgie', 'Médecine générale', 'Dentiste'
        ];

        return [
            'nom' => $this->faker->unique()->randomElement($specialites),
            'description' => $this->faker->sentence(10),
        ];
    }
}