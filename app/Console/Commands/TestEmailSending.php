<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment; // Assure-toi d'importer ton modèle

class TestEmailSending extends Command
{
    protected $signature = 'email:test';
    protected $description = 'Tester l\'envoi d\'email avec Mailtrap Sending';

    public function handle()
    {
        // 1. Récupère un rendez-vous existant pour le test, ou crée-en un factice
        $appointment = Appointment::first(); // Prend le premier RDV de la base

        if (!$appointment) {
            $this->error("Aucun rendez-vous trouvé en base pour le test.");
            return;
        }

        // 2. Email de destination RÉEL pour le test
        $testEmail = 'ton.email.personnel@gmail.com'; // ← METS TON VRAI EMAIL ICI

        $this->info("Tentative d'envoi vers : " . $testEmail);

        // 3. Envoi de l'email
        try {
            Mail::to($testEmail)->send(new AppointmentConfirmation($appointment));
            $this->info("✅ Email envoyé avec succès ! Vérifie ta boîte mail (et les spams).");
        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
        }
    }
}