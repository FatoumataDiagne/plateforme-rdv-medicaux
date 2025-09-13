<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            $query = User::query();

            if ($search) {
                $query->where('prenom', 'like', "%$search%")
                      ->orWhere('nom', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
            }

            $users = $query->paginate($perPage);

           return response()->json([
    'success' => true,
    'data' => $users->items(), // seulement les utilisateurs, pas l'objet paginator entier
    'meta' => [
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
        'per_page' => $users->perPage(),
        'total' => $users->total(),
    ]
]);

        } catch (\Exception $e) {
            Log::error('Error loading users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs'
            ], 500);
        }
    }
}
