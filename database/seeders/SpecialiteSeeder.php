<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialite;

class SpecialiteSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 10 spécialités
        Specialite::factory()->count(10)->create();
    }
}