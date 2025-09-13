<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SpecialiteController;
use App\Http\Controllers\API\MedecinController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RendezVousController;
use App\Http\Controllers\API\PdfController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AIChatController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\PaiementController;
use App\Http\Controllers\API\JustificatifController; 

// ----------------------
// Routes PUBLIQUES
// ----------------------
Route::get('/test', function () {
    return response()->json(['message' => 'API works!']);
});

Route::get('/medecins', function () {
    $medecins = Medecin::with(['user', 'specialite'])->get();
    return response()->json($medecins);
});

Route::get('/medecins', [MedecinController::class, 'index']);
Route::apiResource('specialites', SpecialiteController::class);
Route::apiResource('medecins', MedecinController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ----------------------
// Routes PROTÉGÉES (authentification)
// ----------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Rendez-vous
    Route::apiResource('rendezvous', RendezVousController::class);
    Route::post('/rendez-vous/{id}/annuler', [RendezVousController::class, 'annuler']);
    Route::post('/rendez-vous/{id}/confirmer-paiement', [RendezVousController::class, 'confirmerPaiement']);
    Route::get('/rendezvous/{id}/generate-pdf', [PdfController::class, 'generateJustificatif']);
    Route::get('/mes-rendezvous/stats', [RendezVousController::class, 'stats']);
    Route::put('/rendezvous/{id}/paiement', [RendezVousController::class, 'updatePaiement']);

    // AI Chat
    Route::post('/ai-chat/ask', [AIChatController::class, 'askQuestion']);

    // Payments
    Route::prefix('payments')->group(function () {
        Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent']);
        Route::post('/confirm', [PaymentController::class, 'confirmPayment']);
    });
    Route::post('/paiements/simuler', [PaymentController::class, 'simulerPaiement']);

    // ----------------------
    // Routes ADMIN
    // ----------------------
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // AdminController
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/rendezvous', [AdminController::class, 'getRendezVous']);
        Route::put('/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::get('/specialites', [AdminController::class, 'getSpecialites']);
        Route::get('/paiements', [AdminController::class, 'getPaiements']);
        Route::get('/statistiques', [AdminController::class, 'getStatistiques']);
       

        Route::post('/specialites', [AdminController::class, 'createSpecialite']);
        Route::put('/specialites/{id}', [AdminController::class, 'updateSpecialite']);
        Route::delete('/specialites/{id}', [AdminController::class, 'deleteSpecialite']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        // Paiements via PaiementController
        Route::get('/paiements-admin', [PaiementController::class, 'index']);
        
        // Justificatifs Admin
        Route::prefix('justificatifs')->group(function () {
            Route::get('/', [JustificatifController::class, 'index']);
            Route::get('/stats', [JustificatifController::class, 'stats']);
            Route::get('/{id}', [JustificatifController::class, 'show']);
            Route::get('/{id}/download', [JustificatifController::class, 'download']);
        });
    });
});

// ----------------------
// Routes MÉDECIN (authentification + middleware medecin)
// ----------------------
Route::middleware(['auth:sanctum', 'medecin'])->prefix('medecin')->group(function () {
    // Plages horaires
    Route::get('/plages-horaires', [MedecinController::class, 'getPlagesHoraires']);
    Route::post('/plages-horaires', [MedecinController::class, 'addPlageHoraire']);
    Route::put('/plages-horaires/{id}', [MedecinController::class, 'updatePlageHoraire']);
    Route::delete('/plages-horaires/{id}', [MedecinController::class, 'deletePlageHoraire']);
    
    // Rendez-vous à confirmer
    Route::get('/rendezvous-a-confirmer', [MedecinController::class, 'getRendezVousAConfirmer']);
    Route::put('/rendezvous/{id}/confirmer', [MedecinController::class, 'confirmerRendezVous']);
    Route::put('/rendezvous/{id}/refuser', [MedecinController::class, 'refuserRendezVous']);
    
    // Agenda
    Route::get('/agenda', [MedecinController::class, 'getAgenda']);
    
    // Justificatif
    Route::get('/rendezvous/{id}/justificatif', [MedecinController::class, 'genererJustificatif']);
});

// ----------------------
// Handler pour OPTIONS (CORS)
// ----------------------
Route::options('/{any}', function () {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:4200')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');