<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\RendezVous;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller; 

class PdfController extends Controller
{
   public function generateJustificatif($rendezVousId)
{
    try {
        \Log::info('PDF Generation started for RDV: ' . $rendezVousId);
        
        // CORRECTION DES RELATIONS :
        $rendezVous = RendezVous::with([
            'patient', // Relation vers le modèle Patient
            'medecin', // Relation vers le modèle Medecin
            'medecin.specialite' // Relation vers la spécialité du médecin
        ])->find($rendezVousId);

        if (!$rendezVous) {
            \Log::warning('Rendez-vous not found: ' . $rendezVousId);
            return response()->json(['error' => 'Rendez-vous non trouvé'], 404);
        }

        \Log::info('Rendez-vous found: ' . $rendezVous->id);

        // Vérifier les autorisations
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $data = [
            'rendezVous' => $rendezVous,
            'dateGeneration' => now()->format('d/m/Y à H:i'),
            'reference' => 'REF-' . $rendezVous->id . '-' . strtoupper(uniqid())
        ];

        \Log::info('Loading PDF view');
        $pdf = Pdf::loadView('pdf.justificatif', $data);
        $pdf->setPaper('A4', 'portrait');
        
        \Log::info('PDF generated successfully');
        return $pdf->download('justificatif' . $rendezVous->id . '.pdf');
        
    } catch (\Exception $e) {
        \Log::error('PDF Generation Error: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile());
        \Log::error('Line: ' . $e->getLine());
        
        return response()->json([
            'error' => 'Erreur lors de la génération du PDF',
            'message' => $e->getMessage()
        ], 500);
    }
}
}