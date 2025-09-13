<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\RendezVous;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'rendezvous_id' => 'required|exists:rendez_vous,id',
            'amount' => 'required|numeric|min:1'
        ]);

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $rendezVous = RendezVous::findOrFail($request->rendezvous_id);
            
            // Vérifier que l'utilisateur peut payer ce RDV
            if ($rendezVous->patient_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Convertir en cents
                'currency' => 'eur',
                'metadata' => [
                    'rendezvous_id' => $rendezVous->id,
                    'patient_id' => auth()->id()
                ]
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'clientSecret' => $paymentIntent->client_secret,
                    'paymentIntentId' => $paymentIntent->id
                ],
                'message' => 'Payment Intent créé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement'
            ], 500);
        }
    }

    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'paymentIntentId' => 'required|string',
            'rendezvous_id' => 'required|exists:rendez_vous,id'
        ]);

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            
            $paymentIntent = PaymentIntent::retrieve($request->paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                $rendezVous = RendezVous::findOrFail($request->rendezvous_id);
                $rendezVous->update([
                    'statut_paiement' => 'paye',
                    'mode_paiement' => 'en_ligne'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement confirmé avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Paiement non réussi'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement'
            ], 500);
        }
    }
}