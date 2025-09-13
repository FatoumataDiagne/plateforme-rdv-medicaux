<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\Medecin;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RendezVousResource;
use PDF;
use App\Notifications\RendezVousConfirme; // ← AJOUT IMPORTANT
use App\Notifications\RendezVousAnnule;

class RendezVousController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $rendezVous = RendezVous::with(['patient', 'medecin.user', 'medecin.specialite'])->get();
            
            return response()->json([
                'success' => true,
                'data' => RendezVousResource::collection($rendezVous),
                'message' => 'Liste des rendez-vous récupérée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rendez-vous.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'medecin_id' => 'required|exists:medecins,id',
            'date' => 'required|date',
            'heure_debut' => 'required|date_format:H:i:s',
            'heure_fin' => 'required|date_format:H:i:s|after:heure_debut',
            'statut' => 'sometimes|in:en_attente,confirme,annule',
            'statut_paiement' => 'sometimes|in:en_attente,paye,rembourse',
            'mode_paiement' => 'nullable|in:en_ligne,sur_place',
            'montant' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $rendezVous = RendezVous::create($validated);
        
        // ⛔️ SUPPRIMEZ cette ligne problématique
        // $rendezVous->load(['patient.user', 'medecin.user', 'medecin.specialite']);
        
        // ✅ Chargez seulement les relations nécessaires
        $rendezVous->load(['patient', 'medecin.user', 'medecin.specialite']);
        
        // ENVOI DE LA NOTIFICATION (corrigé)
        $notificationSent = false;
        if ($rendezVous->patient) { // ✅ Plus besoin de ->user ici
            try {
                $rendezVous->patient->notify(new RendezVousConfirme($rendezVous));
                $notificationSent = true;
                \Log::info('Notification envoyée pour le RDV ID: ' . $rendezVous->id);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi notification RDV ' . $rendezVous->id . ': ' . $e->getMessage());
            }
        }

        // ⛔️ Évitez RendezVousResource temporairement
        // return response()->json([...], 201);
        
        // ✅ Réponse simple sans Resource
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $rendezVous->id,
                'patient_id' => $rendezVous->patient_id,
                'medecin_id' => $rendezVous->medecin_id,
                'date' => $rendezVous->date,
                'heure_debut' => $rendezVous->heure_debut,
                'heure_fin' => $rendezVous->heure_fin,
                'statut' => $rendezVous->statut,
                'statut_paiement' => $rendezVous->statut_paiement,
                'mode_paiement' => $rendezVous->mode_paiement,
                'montant' => $rendezVous->montant,
                'notes' => $rendezVous->notes
            ],
            'notification_sent' => $notificationSent,
            'message' => 'Rendez-vous créé avec succès.'
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Erreur création RDV: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création du rendez-vous.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function show(string $id): JsonResponse
    {
        try {
            $rendezVous = RendezVous::with(['patient', 'medecin.user', 'medecin.specialite'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new RendezVousResource($rendezVous),
                'message' => 'Rendez-vous récupéré avec succès.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rendez-vous.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function updatePaiement(Request $request, $id)
{
    try {
        $rendezVous = RendezVous::findOrFail($id);
        
        $validated = $request->validate([
            'statut_paiement' => 'required|in:en_attente,paye,echoue,rembourse'
        ]);

        $rendezVous->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Statut de paiement mis à jour',
            'data' => $rendezVous
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy(string $id): JsonResponse
    {
        try {
            $rendezVous = RendezVous::findOrFail($id);
            $rendezVous->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rendez-vous supprimé avec succès.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rendez-vous.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generatePdf($id)
    {
        try {
            $rendezvous = RendezVous::with(['patient', 'medecin.user', 'medecin.specialite'])->findOrFail($id);
            
            $pdf = PDF::loadView('pdf.rendezvous', compact('rendezvous'));

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="justificatif-'.$id.'.pdf"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirm($id)
    {
        try {
            $rendezVous = RendezVous::with(['patient', 'medecin.user'])->findOrFail($id);
            $rendezVous->update(['statut' => 'confirmé']);

            // Vérification avant notification
            if ($rendezVous->patient && $rendezVous->patient->user) {
                $rendezVous->patient->user->notify(new RendezVousConfirme($rendezVous));
            }

            return response()->json([
                'success' => true,
                'data' => new RendezVousResource($rendezVous),
                'message' => 'Rendez-vous confirmé avec succès.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du rendez-vous.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Statistiques utilisateur
public function stats(Request $request)
{
    try {
        $user = $request->user();
        
        $stats = [
            'total' => RendezVous::where('patient_id', $user->id)->count(),
            'confirmes' => RendezVous::where('patient_id', $user->id)
                            ->where('statut', 'confirmé')->count(),
            'en_attente' => RendezVous::where('patient_id', $user->id)
                            ->where('statut', 'en_attente')->count(),
            'annules' => RendezVous::where('patient_id', $user->id)
                            ->where('statut', 'annulé')->count(),
            'prochains' => RendezVous::where('patient_id', $user->id)
                            ->where('statut', 'confirmé')
                            ->where('date', '>=', now()->format('Y-m-d'))
                            ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du calcul des statistiques'
        ], 500);
    }
}
public function annuler($id, Request $request)
{
    try {
        // Charge medecin.user pour avoir le nom du médecin
        $rendezVous = RendezVous::with(['patient', 'medecin.user'])->findOrFail($id);
        $raison = $request->input('raison', 'Annulation par le patient');
        
        $rendezVous->update(['statut' => 'annule']);

        // Notification au patient (qui est un User)
        if ($rendezVous->patient) {
            $rendezVous->patient->notify(new RendezVousAnnule($rendezVous, $raison));
        }

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous annulé et notification envoyée.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'annulation',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function confirmerPaiement($id)
{
    try {
        $rendezVous = RendezVous::with(['patient.user'])->findOrFail($id);
        $rendezVous->update(['statut_paiement' => 'paye']);

        // Notification de paiement confirmé
        if ($rendezVous->patient && $rendezVous->patient->user) {
            $rendezVous->patient->user->notify(new PaiementConfirme($rendezVous));
        }

        return response()->json([
            'success' => true,
            'message' => 'Paiement confirmé et notification envoyée.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la confirmation du paiement',
            'error' => $e->getMessage()
        ], 500);
    }
}
}