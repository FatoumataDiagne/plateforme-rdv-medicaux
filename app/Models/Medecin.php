<?php
// App/Models/Medecin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medecin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'specialite_id', 'tarif_consultation', 
        'adresse_cabinet', 'num_ordre'
    ];

    /**
     * Un médecin appartient à un user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un médecin a une spécialité
     */
    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    /**
     * Un médecin a plusieurs rendez-vous
     */
    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class);
    }
    public function plagesHoraires()
{
    return $this->hasMany(PlageHoraire::class);
}
}
