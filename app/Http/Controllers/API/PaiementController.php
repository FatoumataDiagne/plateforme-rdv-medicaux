<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paiement;

class PaiementController extends Controller
{
   

public function index(Request $request)
{
    $paiements = Paiement::paginate(10);

    return response()->json($paiements);
}

}
