<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'prenom',
        'nom',
        'email',
        'password',
        'role',
        'telephone',
        'date_naissance',
        'adresse',
        'specialite_id',
        'adresse_cabinet'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance' => 'date',
    ];

    /**
     * Relation avec la spécialité (pour les médecins)
     */
      public function medecin()
    {
        return $this->hasOne(Medecin::class);
    }
    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }

    /**
     * Vérifie si l'utilisateur est un admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est un médecin
     */
    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    /**
     * Vérifie si l'utilisateur est un patient
     */
    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }
}