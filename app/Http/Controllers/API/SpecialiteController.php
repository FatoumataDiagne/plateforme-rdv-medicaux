<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialite;
use Illuminate\Http\JsonResponse;

class SpecialiteController extends Controller
{
    /**
     * Display a listing of all specialities.
     */
    public function index(): JsonResponse
    {
        try {
            $specialites = Specialite::all();
            return response()->json([
                'success' => true,
                'data' => $specialites,
                'message' => 'Liste des spécialités récupérée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des spécialités.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created speciality.
     */
    public function store(Request $request): JsonResponse
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
                'message' => 'Spécialité créée avec succès.'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la spécialité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified speciality.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $specialite,
                'message' => 'Spécialité récupérée avec succès.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Spécialité non trouvée.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la spécialité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified speciality.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);

            $validated = $request->validate([
                'nom' => 'sometimes|required|string|max:255|unique:specialites,nom,' . $id,
                'description' => 'nullable|string'
            ]);

            $specialite->update($validated);

            return response()->json([
                'success' => true,
                'data' => $specialite,
                'message' => 'Spécialité mise à jour avec succès.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Spécialité non trouvée.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la spécialité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified speciality.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $specialite = Specialite::findOrFail($id);
            $specialite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Spécialité supprimée avec succès.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Spécialité non trouvée.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la spécialité.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}