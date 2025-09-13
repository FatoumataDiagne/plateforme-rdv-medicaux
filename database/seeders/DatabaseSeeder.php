<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SpecialiteSeeder::class,
        ]);
        
        // Créer 20 médecins
        \App\Models\Medecin::factory()->count(20)->create();
        
        // Créer 50 rendez-vous
        \App\Models\RendezVous::factory()->count(50)->create();
    }
}