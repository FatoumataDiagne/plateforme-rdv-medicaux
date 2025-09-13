<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // D'abord ajouter les colonnes en nullable
        $table->string('prenom')->nullable()->after('name');
        $table->string('nom')->nullable()->after('prenom');
        $table->string('telephone')->nullable()->after('email');
        $table->enum('role', ['patient', 'medecin', 'admin'])->default('patient')->after('password');
        $table->date('date_naissance')->nullable()->after('role');
        $table->text('adresse')->nullable()->after('date_naissance');
    });

    // Mettre à jour les données existantes
    DB::table('users')->update([
        'prenom' => 'Utilisateur',
        'nom' => 'Anonyme',
        'role' => 'patient'
    ]);

    // Maintenant rendre les colonnes obligatoires
    Schema::table('users', function (Blueprint $table) {
        $table->string('prenom')->nullable(false)->change();
        $table->string('nom')->nullable(false)->change();
        $table->string('role')->nullable(false)->change();
    });

    // Enfin supprimer l'ancien champ name
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('name');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        // Recréer name d'abord
        $table->string('name')->nullable();
    });

    // Mettre à jour les données
    DB::table('users')->update([
        'name' => DB::raw("CONCAT(prenom, ' ', nom)")
    ]);

    Schema::table('users', function (Blueprint $table) {
        $table->string('name')->nullable(false)->change();
        $table->dropColumn(['prenom', 'nom', 'telephone', 'role', 'date_naissance', 'adresse']);
    });
}
};
