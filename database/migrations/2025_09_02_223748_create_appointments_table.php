<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('appointments', function (Blueprint $table) {
        $table->id();
        $table->string('reference_number')->unique();
        $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('medecin_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('specialite_id')->constrained()->onDelete('cascade');
        $table->dateTime('appointment_date');
        $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
        $table->enum('payment_method', ['online', 'cash'])->default('cash');
        $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
        $table->decimal('amount', 8, 2)->default(0);
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

