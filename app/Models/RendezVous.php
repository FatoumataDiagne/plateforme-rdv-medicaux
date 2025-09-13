<?php
// App/Models/RendezVous.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id', 'medecin_id', 'date', 'heure_debut', 'heure_fin',
        'statut', 'statut_paiement', 'mode_paiement', 'montant', 'notes'
    ];

    /**
     * Un rendez-vous appartient à un patient (user)
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Un rendez-vous appartient à un médecin
     */
    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class, 'medecin_id');
    }
    public function paiement()
{
    return $this->hasOne(Paiement::class);
}
 public function specialite()
    {
        return $this->belongsTo(Specialite::class, 'specialite_id');
    }

}