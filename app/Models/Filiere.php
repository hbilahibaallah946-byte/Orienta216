<?php
// app/Models/Filiere.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'specialite',
        'code',
        'universite',
        'type_bac',
        'formule',
        'annee',
        'criteres',    // ← AJOUTÉ
        'description', // ← AJOUTÉ
    ];

    protected $casts = [
        'criteres' => 'array', // ← CRUCIAL : JSON string → PHP array
    ];

    public function recommandations()
    {
        return $this->hasMany(Recommandation::class);
    }
     public function etudiants()
    {
        return $this->hasMany(User::class, 'filiere_id');
    }
    
    // Vous pouvez aussi ajouter d'autres relations utiles
    public function moyennes()
    {
        return $this->hasManyThrough(Moyenne::class, User::class, 'filiere_id', 'user_id');
    }
}