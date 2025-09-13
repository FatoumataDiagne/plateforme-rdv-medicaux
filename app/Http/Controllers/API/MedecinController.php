<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Medecin;
use App\Models\User;
use App\Models\Specialite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\MedecinResource; 
use Illuminate\Support\Facades\Auth; 
use App\Models\RendezVous;
use App\Models\PlageHoraire;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Mail; // ← AJOUTEZ CET IMPORT
use App\Mail\PatientConfirmationMail;
class MedecinController extends Controller
{

public function index(): JsonResponse
{
    try {
        // CHARGEZ explicitement les relations
        $medecins = Medecin::with(['user', 'specialite'])->get();
        
        return response()->json([
            'success' => true,
            'data' => MedecinResource::collection($medecins),
            'message' => 'Liste des médecins récupérée avec succès.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des médecins.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Store a newly created doctor and associated user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                // Données utilisateur
                'prenom' => 'required|string|max:255',
                'nom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'telephone' => 'nullable|string|max:20',
                
                // Données médecin
                'specialite_id' => 'required|exists:specialites,id',
                'numero_ordre' => 'required|string|max:255|unique:medecins,numero_ordre',
                'adresse_cabinet' => 'required|string',
                'ville' => 'required|string|max:255',
                'code_postal' => 'required|string|max:10',
                'tarif_consultation' => 'required|numeric|min:0'
            ]);

            // Créer l'utilisateur
            $user = User::create([
                'prenom' => $validated['prenom'],
                'nom' => $validated['nom'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telephone' => $validated['telephone'] ?? null,
                'role' => 'medecin' // Ajouter un rôle si nécessaire
            ]);

            // Créer le médecin
            $medecin = Medecin::create([
                'user_id' => $user->id,
                'specialite_id' => $validated['specialite_id'],
                'numero_ordre' => $validated['numero_ordre'],
                'adresse_cabinet' => $validated['adresse_cabinet'],
                'ville' => $validated['ville'],
                'code_postal' => $validated['code_postal'],
                'tarif_consultation' => $validated['tarif_consultation']
            ]);

            // Charger les relations pour la réponse
            $medecin->load(['user', 'specialite']);

           return response()->json([
            'success' => true,
            'data' => new MedecinResource($medecin->load(['user', 'specialite'])),
            'message' => 'Médecin créé/mis à jour avec succès.'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du médecin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified doctor with relationships.
     */
   public function show(string $id): JsonResponse
{
    try {
        $medecin = Medecin::with(['user', 'specialite'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new MedecinResource($medecin),
            'message' => 'Médecin récupéré avec succès.'
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Médecin non trouvé.'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération du médecin.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Update the specified doctor and associated user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $medecin = Medecin::with(['user', 'specialite'])->findOrFail($id);

            $validated = $request->validate([
                // Données utilisateur
                'prenom' => 'sometimes|string|max:255',
                'nom' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $medecin->user_id,
                'telephone' => 'nullable|string|max:20',
                
                // Données médecin
                'specialite_id' => 'sometimes|exists:specialites,id',
                'numero_ordre' => 'sometimes|string|max:255|unique:medecins,numero_ordre,' . $id,
                'adresse_cabinet' => 'sometimes|string',
                'ville' => 'sometimes|string|max:255',
                'code_postal' => 'sometimes|string|max:10',
                'tarif_consultation' => 'sometimes|numeric|min:0'
            ]);

            // Mettre à jour l'utilisateur
            if (isset($validated['prenom']) || isset($validated['nom']) || 
                isset($validated['email']) || isset($validated['telephone'])) {
                
                $userData = [];
                if (isset($validated['prenom'])) $userData['prenom'] = $validated['prenom'];
                if (isset($validated['nom'])) $userData['nom'] = $validated['nom'];
                if (isset($validated['email'])) $userData['email'] = $validated['email'];
                if (isset($validated['telephone'])) $userData['telephone'] = $validated['telephone'];
                
                $medecin->user->update($userData);
            }

            // Mettre à jour le médecin
            $medecinData = [];
            if (isset($validated['specialite_id'])) $medecinData['specialite_id'] = $validated['specialite_id'];
            if (isset($validated['numero_ordre'])) $medecinData['numero_ordre'] = $validated['numero_ordre'];
            if (isset($validated['adresse_cabinet'])) $medecinData['adresse_cabinet'] = $validated['adresse_cabinet'];
            if (isset($validated['ville'])) $medecinData['ville'] = $validated['ville'];
            if (isset($validated['code_postal'])) $medecinData['code_postal'] = $validated['code_postal'];
            if (isset($validated['tarif_consultation'])) $medecinData['tarif_consultation'] = $validated['tarif_consultation'];
            
            $medecin->update($medecinData);

            // Recharger les relations
            $medecin->load(['user', 'specialite']);

            return response()->json([
              'success' => true,
              'data' => new MedecinResource($medecin->load(['user', 'specialite'])),
              'message' => 'Médecin créé/mis à jour avec succès.'], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du médecin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified doctor and associated user.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medecin = Medecin::with('user')->findOrFail($id);
            
            // Supprimer l'utilisateur associé
            $medecin->user->delete();
            
            // Le médecin sera supprimé automatiquement par la contrainte CASCADE

            return response()->json([
                'success' => true,
                'message' => 'Médecin et son compte utilisateur supprimés avec succès.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du médecin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Récupérer les plages horaires du médecin
public function getPlagesHoraires()
{
    $user = Auth::user();
    
    if (!$user || !$user->medecin) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    
    $medecin = $user->medecin;
    
    $plages = PlageHoraire::where('medecin_id', $medecin->id)
        ->orderByRaw("
            CASE jour_semaine
                WHEN 'Lundi' THEN 1
                WHEN 'Mardi' THEN 2
                WHEN 'Mercredi' THEN 3
                WHEN 'Jeudi' THEN 4
                WHEN 'Vendredi' THEN 5
                WHEN 'Samedi' THEN 6
                WHEN 'Dimanche' THEN 7
            END
        ")
        ->orderBy('heure_debut')
        ->get();
        
    return response()->json($plages);
}

// Ajouter une plage horaire
public function addPlageHoraire(Request $request)
{
    $request->validate([
        'jour_semaine' => 'required|string|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
        'heure_debut' => 'required|date_format:H:i',
        'heure_fin' => 'required|date_format:H:i|after:heure_debut',
    ]);

    $user = Auth::user();
    
    if (!$user || !$user->medecin) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    
    $medecin = $user->medecin;

    $plage = PlageHoraire::create([
        'medecin_id' => $medecin->id,
        'jour_semaine' => $request->jour_semaine,
        'heure_debut' => $request->heure_debut,
        'heure_fin' => $request->heure_fin,
    ]);

    return response()->json($plage, 201);
}

// Modifier une plage horaire
public function updatePlageHoraire(Request $request, $id)
{
    $request->validate([
        'jour_semaine' => 'required|string|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche',
        'heure_debut' => 'required|date_format:H:i',
        'heure_fin' => 'required|date_format:H:i|after:heure_debut',
    ]);

    $user = Auth::user();
    
    if (!$user || !$user->medecin) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    
    $plage = PlageHoraire::where('id', $id)
        ->where('medecin_id', $user->medecin->id)
        ->firstOrFail();

    $plage->update([
        'jour_semaine' => $request->jour_semaine,
        'heure_debut' => $request->heure_debut,
        'heure_fin' => $request->heure_fin,
    ]);

    return response()->json($plage);
}

// Supprimer une plage horaire
public function deletePlageHoraire($id)
{
    $user = Auth::user();
    
    if (!$user || !$user->medecin) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    
    $plage = PlageHoraire::where('id', $id)
        ->where('medecin_id', $user->medecin->id)
        ->firstOrFail();

    $plage->delete();

    return response()->json(['message' => 'Plage horaire supprimée avec succès']);
}
    
    // Récupérer les rendez-vous à confirmer
   public function getRendezVousAConfirmer()
{
    try {
        $user = Auth::user();
        
        if (!$user || !$user->medecin) {
            return response()->json(['message' => 'Médecin non trouvé'], 404);
        }
        
        $medecin = $user->medecin;
        
        $rendezVous = RendezVous::with(['patient', 'specialite'])
            ->where('medecin_id', $medecin->id)
            ->where('statut', 'en_attente')
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get()
            ->map(function ($rdv) {
                // Vérification que le patient existe
                $patientData = $rdv->patient ? [
                    'id' => $rdv->patient->id,
                    'prenom' => $rdv->patient->prenom,
                    'nom' => $rdv->patient->nom,
                    'telephone' => $rdv->patient->telephone,
                ] : [
                    'id' => null,
                    'prenom' => 'Patient non',
                    'nom' => 'trouvé',
                    'telephone' => null,
                ];
                
                // Vérification que la spécialité existe
                $specialiteData = $rdv->specialite ? [
                    'id' => $rdv->specialite->id,
                    'nom' => $rdv->specialite->nom,
                ] : [
                    'id' => null,
                    'nom' => 'Non spécifié',
                ];
                
                return [
                    'id' => $rdv->id,
                    'patient' => $patientData,
                    'specialite' => $specialiteData,
                    'date' => $rdv->date,
                    'heure_debut' => $rdv->heure_debut,
                    'heure_fin' => $rdv->heure_fin,
                    'statut' => $rdv->statut,
                    'created_at' => $rdv->created_at,
                    'updated_at' => $rdv->updated_at,
                ];
            });
            
        return response()->json($rendezVous);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des rendez-vous',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
    // Refuser un rendez-vous
    public function refuserRendezVous(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string',
        ]);
        
        $medecin = Auth::user()->medecin;
        $rendezVous = RendezVous::where('id', $id)
            ->where('medecin_id', $medecin->id)
            ->firstOrFail();
            
        $rendezVous->update([
            'statut' => 'annulé',
            'motif_annulation' => $request->raison,
        ]);
        
        // Envoyer une notification au patient avec le motif
        
        return response()->json(['message' => 'Rendez-vous refusé avec succès']);
    }
    
public function getAgenda(Request $request)
{
    $request->validate([
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
    ]);

    $user = Auth::user();
    
    if (!$user || !$user->medecin) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    
    $medecin = $user->medecin;

    $rendezVous = RendezVous::with(['patient', 'specialite'])
        ->where('medecin_id', $medecin->id)
        ->whereBetween('date', [$request->date_debut, $request->date_fin])
        ->orderBy('date')
        ->orderBy('heure_debut')
        ->get();

    return response()->json($rendezVous);
}
    
    // Générer un justificatif PDF
    /**
 * Générer un justificatif PDF
 */
public function genererJustificatif($id)
{
    try {
        $user = Auth::user();
        
        if (!$user || !$user->medecin) {
            return response()->json(['message' => 'Médecin non trouvé'], 404);
        }
        
        $medecin = $user->medecin;
        
        $rendezVous = RendezVous::with(['patient', 'specialite', 'medecin.user'])
            ->where('id', $id)
            ->where('medecin_id', $medecin->id)
            ->firstOrFail();
        
        // Créer les variables attendues par la vue PDF
        $reference = 'RDV-' . str_pad($rendezVous->id, 6, '0', STR_PAD_LEFT);
        $dateGeneration = now()->format('d/m/Y à H:i');
            
        $pdf = Pdf::loadView('pdf.justificatif', [
            'rendezVous' => $rendezVous,
            'reference' => $reference,
            'dateGeneration' => $dateGeneration
        ]);
        
        return $pdf->download('justificatif-rdv-' . $rendezVous->id . '.pdf');
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la génération du justificatif',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function confirmerRendezVous(Request $request, $id): JsonResponse
{
    try {
        $request->validate([
            'date' => 'sometimes|date',
            'heure_debut' => 'sometimes|date_format:H:i',
            'heure_fin' => 'sometimes|date_format:H:i|after:heure_debut',
        ]);
        
        $user = Auth::user();
        
        if (!$user || !$user->medecin) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé.'
            ], 404);
        }
        
        $medecin = $user->medecin;
        
        $rendezVous = RendezVous::where('id', $id)
            ->where('medecin_id', $medecin->id)
            ->firstOrFail();
            
        $updateData = ['statut' => 'confirmé'];
        
        // Optionnel: permettre de modifier la date/heure lors de la confirmation
        if ($request->has('date')) {
            $updateData['date'] = $request->date;
        }
        
        if ($request->has('heure_debut')) {
            $updateData['heure_debut'] = $request->heure_debut;
        }
        
        if ($request->has('heure_fin')) {
            $updateData['heure_fin'] = $request->heure_fin;
        }
        
        $rendezVous->update($updateData);
        
        // Recharger les relations avec les données nécessaires pour l'email
        $rendezVous->load(['patient', 'specialite', 'medecin.user']);
        
        // 🔥 ENVOI AUTOMATIQUE DE L'EMAIL DE CONFIRMATION AU PATIENT
        try {
            Mail::to($rendezVous->patient->email)
                ->send(new PatientConfirmationMail($rendezVous)); // ← RETIRÉ LE \App\Mail\
                
            \Log::info('Email de confirmation envoyé à: ' . $rendezVous->patient->email, [
                'rendezvous_id' => $rendezVous->id,
                'patient_id' => $rendezVous->patient->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de l\'email de confirmation: ' . $e->getMessage(), [
                'rendezvous_id' => $rendezVous->id,
                'patient_email' => $rendezVous->patient->email
            ]);
            // On continue même si l'email échoue pour ne pas bloquer la confirmation
        }
        
        return response()->json([
            'success' => true,
            'data' => $rendezVous,
            'message' => 'Rendez-vous confirmé avec succès.' . (isset($e) ? ' (Note: Email non envoyé)' : '')
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Rendez-vous non trouvé.'
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la confirmation du rendez-vous.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}