<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // database/migrations/xxxx_create_paiements_table.php
public function up()
{
    Schema::create('paiements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rendez_vous_id')->constrained('rendez_vous')->onDelete('cascade');
        $table->decimal('montant', 8, 2);
        $table->string('methode')->default('carte');
        $table->string('statut')->default('en_attente');
        $table->string('transaction_id')->nullable();
        $table->text('details')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
