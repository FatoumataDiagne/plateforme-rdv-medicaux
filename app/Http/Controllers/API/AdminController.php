<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\RendezVous;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    // PUT /api/admin/users/{id}
public function updateUser(Request $request, $id): JsonResponse
{
    try {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:patient,medecin,admin'
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Utilisateur mis à jour avec succès'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
            'error' => $e->getMessage()
        ], 500);
    }
}

// DELETE /api/admin/users/{id}
public function deleteUser($id): JsonResponse
{
    try {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression'
        ], 500);
    }
}

     public function getStats(): JsonResponse
    {
        try {
            $stats = [
                // STATS GÉNÉRALES (existantes)
                'general' => [
                    'total_users' => User::count(),
                    'total_patients' => User::where('role', 'patient')->count(),
                    'total_medecins' => User::where('role', 'medecin')->count(),
                    'total_admins' => User::where('role', 'admin')->count(),
                    'total_specialites' => Specialite::count(),
                    'total_rendezvous' => RendezVous::count(),
                    'revenus_totaux' => RendezVous::where('statut_paiement', 'paye')->sum('montant'),
                ],

                // STATS PAR STATUT (existantes)
                'par_statut' => [
                    'rendezvous' => [
                        'confirme' => RendezVous::where('statut', 'confirme')->count(),
                        'en_attente' => RendezVous::where('statut', 'en_attente')->count(),
                        'annule' => RendezVous::where('statut', 'annule')->count(),
                    ],
                    'paiements' => [
                        'paye' => RendezVous::where('statut_paiement', 'paye')->count(),
                        'en_attente' => RendezVous::where('statut_paiement', 'en_attente')->count(),
                        'rembourse' => RendezVous::where('statut_paiement', 'rembourse')->count(),
                    ],
                ],

                // NOUVELLES STATS AVANCÉES
                'advanced' => [
                    // Stats financières
                    'finances' => $this->getFinancialStats(),
                    
                    // Stats par spécialité
                    'specialites' => $this->getSpecialiteStats(),
                    
                    // Évolution temporelle
                    'evolution' => $this->getEvolutionStats(),
                    
                    // Taux de conversion
                    'conversion' => $this->getConversionRates(),
                    
                    // Stats mensuelles
                    'mensuelles' => $this->getMonthlyStats(),
                ],

                // Timeline (30 derniers jours)
                'timeline' => $this->getTimelineStats(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getPaiements(Request $request): JsonResponse
    {
        try {
            \Log::info('API appelée: getPaiements', $request->all());
            
            // Validation des paramètres
            $validated = $request->validate([
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            // Vérifier si la table paiements existe
            if (!\Schema::hasTable('paiements')) {
                \Log::warning('Table paiements non trouvée');
                
                // Retourner des données factices pour le développement
                return response()->json([
                    'success' => true,
                    'data' => $this->getSamplePayments(),
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 5,
                        'last_page' => 1
                    ]
                ]);
            }

            // Requête de base pour les paiements
            $query = Paiement::query();

            // Pagination
            $paiements = $query->orderBy('created_at', 'desc')
                              ->paginate($perPage, ['*'], 'page', $page);

            \Log::info('Paiements récupérés: ' . $paiements->count());

            return response()->json([
                'success' => true,
                'data' => $paiements->items(),
                'pagination' => [
                    'current_page' => $paiements->currentPage(),
                    'per_page' => $paiements->perPage(),
                    'total' => $paiements->total(),
                    'last_page' => $paiements->lastPage(),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in getPaiements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Paramètres invalides',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Erreur getPaiements: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

       private function getSamplePayments(): array
    {
        return [
            [
                'id' => 1,
                'montant' => 50.00,
                'statut' => 'payé',
                'methode_paiement' => 'carte',
                'patient_nom' => 'Jean Dupont',
                'medecin_nom' => 'Dr. Martin',
                'date_creation' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'transaction_id' => 'trx_123456'
            ],
            [
                'id' => 2,
                'montant' => 60.00,
                'statut' => 'en_attente',
                'methode_paiement' => 'espèces',
                'patient_nom' => 'Marie Curie',
                'medecin_nom' => 'Dr. Bernard',
                'date_creation' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'transaction_id' => 'trx_123457'
            ],
            [
                'id' => 3,
                'montant' => 70.00,
                'statut' => 'payé',
                'methode_paiement' => 'virement',
                'patient_nom' => 'Pierre Durand',
                'medecin_nom' => 'Dr. Lambert',
                'date_creation' => now()->subDays(3)->format('Y-m-d H:i:s'),
                'transaction_id' => 'trx_123458'
            ]
        ];
    }
    private function getFinancialStats(): array
    {
        $paiementsEnLigne = RendezVous::where('mode_paiement', 'en_ligne')
            ->where('statut_paiement', 'paye')
            ->count();

        $paiementsSurPlace = RendezVous::where('mode_paiement', 'sur_place')
            ->where('statut_paiement', 'paye')
            ->count();

        $revenusEnLigne = RendezVous::where('mode_paiement', 'en_ligne')
            ->where('statut_paiement', 'paye')
            ->sum('montant');

        $revenusSurPlace = RendezVous::where('mode_paiement', 'sur_place')
            ->where('statut_paiement', 'paye')
            ->sum('montant');

        return [
            'paiements_en_ligne' => $paiementsEnLigne,
            'paiements_sur_place' => $paiementsSurPlace,
            'revenus_en_ligne' => $revenusEnLigne,
            'revenus_sur_place' => $revenusSurPlace,
            'taux_paiement_en_ligne' => ($paiementsEnLigne + $paiementsSurPlace) > 0 
                ? round(($paiementsEnLigne / ($paiementsEnLigne + $paiementsSurPlace)) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get all users with pagination
     */
  /**
 * GET /api/admin/users
 * Récupère la liste des utilisateurs avec pagination
 */
public function getUsers(Request $request): JsonResponse
{
    try {
        \Log::info('API getUsers appelée', $request->all());
        
        // Validation des paramètres
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'role' => 'sometimes|string|in:patient,medecin,admin'
        ]);

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $search = $request->get('search');
        $role = $request->get('role');

        // Construction de la requête
        $query = User::query();

        // Filtre par recherche
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($role) {
            $query->where('role', $role);
        }

        // Pagination avec tri
        $users = $query->orderBy('created_at', 'desc')
                      ->paginate($perPage, ['*'], 'page', $page);

        \Log::info('Users récupérés: ' . $users->count());

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error in getUsers: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Paramètres invalides',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        \Log::error('Erreur getUsers: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des utilisateurs',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
/**
 * NOUVEAU: Statistiques par spécialité (version simplifiée)
 */
private function getSpecialiteStats(): array
{
    return Specialite::withCount('medecins')
        ->get()
        ->map(function ($specialite) {
            // Compte les rendez-vous pour cette spécialité
            $rendezVousCount = RendezVous::whereHas('medecin', function ($query) use ($specialite) {
                $query->where('specialite_id', $specialite->id);
            })->count();

            // Calcule les revenus pour cette spécialité
            $revenus = RendezVous::whereHas('medecin', function ($query) use ($specialite) {
                $query->where('specialite_id', $specialite->id);
            })->where('statut_paiement', 'paye')->sum('montant');

            return [
                'id' => $specialite->id,
                'name' => $specialite->name,
                'medecins_count' => $specialite->medecins_count,
                'rendez_vous_count' => $rendezVousCount,
                'revenus' => $revenus,
                'revenus_moyens' => $rendezVousCount > 0 
                    ? round($revenus / $rendezVousCount, 2) 
                    : 0
            ];
        })->toArray();
}
      private function getEvolutionStats(): array
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        return RendezVous::where('created_at', '>=', $sixMonthsAgo)
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as total_rdv'),
                DB::raw('SUM(CASE WHEN statut_paiement = \'paye\' THEN montant ELSE 0 END) as revenus')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * NOUVEAU: Taux de conversion
     */
    private function getConversionRates(): array
    {
        $totalRdv = RendezVous::count();
        $rdvConfirmes = RendezVous::where('statut', 'confirme')->count();
        $rdvPayes = RendezVous::where('statut_paiement', 'paye')->count();

        return [
            'taux_confirmation' => $totalRdv > 0 ? round(($rdvConfirmes / $totalRdv) * 100, 2) : 0,
            'taux_paiement' => $rdvConfirmes > 0 ? round(($rdvPayes / $rdvConfirmes) * 100, 2) : 0,
        ];
    }
private function getMonthlyStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $rdvMois = RendezVous::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $rdvConfirmesMois = RendezVous::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('statut', 'confirme')->count();
        $revenusMois = RendezVous::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('statut_paiement', 'paye')->sum('montant');

        return [
            'rdv_mois' => $rdvMois,
            'rdv_confirmes_mois' => $rdvConfirmesMois,
            'revenus_mois' => $revenusMois,
            'jours_restants' => Carbon::now()->diffInDays($endOfMonth) + 1
        ];
    }

    /**
 * Timeline des 30 derniers jours (SYNTAXE CORRIGÉE)
 */
private function getTimelineStats(): array
{
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();

    $stats = RendezVous::whereBetween('date', [$startDate, $endDate])
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('COUNT(*) as total_rdv'),
            DB::raw("SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as confirmes"),
            DB::raw("SUM(CASE WHEN statut_paiement = 'paye' THEN montant ELSE 0 END) as revenus")
        )
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    return [
        'period' => [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d')
        ],
        'daily_stats' => $stats
    ];
}
    /**
     * Get all appointments with filters
     */
    public function getRendezVous(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $query = RendezVous::with(['patient', 'medecin.user', 'medecin.specialite'])
                ->orderBy('date', 'desc')
                ->orderBy('heure_debut', 'desc');

            // Filtres
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->has('statut_paiement')) {
                $query->where('statut_paiement', $request->statut_paiement);
            }

            $rendezvous = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $rendezvous,
                'message' => 'Rendez-vous récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rendez-vous',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method: Get appointments by month
     */
    private function getRendezVousParMois(): array
    {
        return RendezVous::select(
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mois"),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('mois')
        ->orderBy('mois', 'desc')
        ->limit(6)
        ->get()
        ->toArray();
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'role' => 'required|in:patient,medecin,admin'
            ]);

            $user = User::findOrFail($id);
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Rôle utilisateur mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
      public function getSpecialites(): JsonResponse
    {
        try {
            $specialites = Specialite::withCount('medecins')
                ->orderBy('nom')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $specialites
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des spécialités'
            ], 500);
        }
    }
    
    // POST /api/admin/specialites
    public function createSpecialite(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:specialites,nom',
                'description' => 'nullable|string'
            ]);
            
            $specialite = Specialite::create($validated);
            
            return response()->json([
                'success' => true,
                'data' => $specialite,
                'message' => 'Spécialité créée avec succès'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création'
            ], 500);
        }
    }
    
    // PUT /api/admin/specialites/{id}
    public function updateSpecialite(Request $request, $id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:specialites,nom,' . $id,
                'description' => 'nullable|string'
            ]);
            
            $specialite->update($validated);
            
            return response()->json([
                'success' => true,
                'data' => $specialite,
                'message' => 'Spécialité mise à jour avec succès'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Spécialité non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }
    
    // DELETE /api/admin/specialites/{id}
    public function deleteSpecialite($id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);
            
            // Vérifier si la spécialité est utilisée par des médecins
            if ($specialite->medecins()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer : des médecins utilisent cette spécialité'
                ], 422);
            }
            
            $specialite->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Spécialité supprimée avec succès'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Spécialité non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }
    
}