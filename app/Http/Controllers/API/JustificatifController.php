<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\RendezVous;


class JustificatifController extends Controller
{


public function stats()
{
    try {
        // Total des justificatifs générés : tu peux utiliser une colonne booléenne ou compter les rendez-vous avec note/fichier
        $total = DB::table('rendez_vous')->whereNotNull('notes')->count();

        // Patients uniques concernés
        $patientsUniques = DB::table('rendez_vous')
            ->whereNotNull('notes')
            ->distinct()
            ->count('patient_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'patients_uniques' => $patientsUniques,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function index(Request $request)
{
    $perPage = $request->get('limit', 10);
    $page = $request->get('page', 1);

    // Récupération des rendez-vous avec relations users
    $query = RendezVous::with(['patient', 'medecin'])->orderBy('created_at', 'desc');

    $total = $query->count();

    $rendezVous = $query->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

    // Transformer les données pour le frontend
    $justificatifs = $rendezVous->map(function($rdv){
        return [
            'id' => $rdv->id,
            'patient' => [
                'nom' => $rdv->patient->nom ?? '',
                'prenom' => $rdv->patient->prenom ?? '',
            ],
            'medecin' => [
                'nom' => $rdv->medecin->nom ?? '',
                'prenom' => $rdv->medecin->prenom ?? '',
            ],
            'date_consultation' => $rdv->date,
            'created_at' => $rdv->created_at,
            'notes' => $rdv->notes,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $justificatifs,
        'meta' => [
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
        ]
    ]);
}


}
