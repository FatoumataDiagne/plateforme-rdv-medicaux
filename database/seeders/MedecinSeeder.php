<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Medecin;
use App\Models\Specialite;

class MedecinSeeder extends Seeder
{
    public function run(): void
    {
        // On s'assure d'avoir des spécialités
        if (Specialite::count() === 0) {
            Specialite::factory(5)->create(); // crée 5 spécialités au hasard
        }

        // Créer 10 médecins avec leur user lié
        User::factory(10)->create()->each(function ($user) {
            Medecin::factory()->create([
                'user_id' => $user->id,
                'specialite_id' => Specialite::inRandomOrder()->first()->id,
            ]);
        });
    }
}
