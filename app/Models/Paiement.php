<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $table = 'paiements'; // obligatoire si jamais Laravel cherche "paiement"

    protected $fillable = [
        'rendez_vous_id',
        'montant',
        'transaction_id',
        'details',
        'methode',
        'statut',
    ];

    public function rendezvous()
    {
        return $this->belongsTo(RendezVous::class, 'rendezvous_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
