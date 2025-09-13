<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrer les données existantes
        $medecins = DB::table('medecins')->whereNull('user_id')->get();
        
        foreach ($medecins as $medecin) {
            $userId = DB::table('users')->insertGetId([
                'prenom' => $medecin->prenom,
                'nom' => $medecin->nom,
                'email' => $medecin->email,
                'telephone' => $medecin->telephone,
                'role' => 'medecin',
                'password' => Hash::make('password_temp'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('medecins')
                ->where('id', $medecin->id)
                ->update(['user_id' => $userId]);
        }

        // Supprimer les colonnes dupliquées
        DB::statement('ALTER TABLE medecins DROP COLUMN IF EXISTS prenom');
        DB::statement('ALTER TABLE medecins DROP COLUMN IF EXISTS nom');
        DB::statement('ALTER TABLE medecins DROP COLUMN IF EXISTS email');
        DB::statement('ALTER TABLE medecins DROP COLUMN IF EXISTS telephone');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer les colonnes
        DB::statement('ALTER TABLE medecins ADD COLUMN prenom VARCHAR(255)');
        DB::statement('ALTER TABLE medecins ADD COLUMN nom VARCHAR(255)');
        DB::statement('ALTER TABLE medecins ADD COLUMN email VARCHAR(255)');
        DB::statement('ALTER TABLE medecins ADD COLUMN telephone VARCHAR(255) NULL');

        // Restaurer les données
        $medecins = DB::table('medecins')
                    ->join('users', 'medecins.user_id', '=', 'users.id')
                    ->select('medecins.id', 'users.prenom', 'users.nom', 'users.email', 'users.telephone')
                    ->get();

        foreach ($medecins as $medecin) {
            DB::table('medecins')
                ->where('id', $medecin->id)
                ->update([
                    'prenom' => $medecin->prenom,
                    'nom' => $medecin->nom,
                    'email' => $medecin->email,
                    'telephone' => $medecin->telephone
                ]);
        }

        // Supprimer les users médecins
        DB::table('users')->where('role', 'medecin')->delete();

        // Remettre user_id à NULL
        DB::table('medecins')->update(['user_id' => null]);
    }
};