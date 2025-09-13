<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Specialite;

class MedecinFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // sera écrasé si on passe un user_id dans le seeder
            'specialite_id' => Specialite::inRandomOrder()->first()->id ?? 1,
            'numero_ordre' => 'OM' . $this->faker->unique()->numberBetween(10000, 99999),
            'adresse_cabinet' => $this->faker->streetAddress(),
            'ville' => $this->faker->city(),
            'code_postal' => $this->faker->postcode(),
            'tarif_consultation' => $this->faker->randomElement([25, 30, 40, 50, 60]),
        ];
    }
}
