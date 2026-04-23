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
        'licence',
        'code',
        'universite',
        'type_bac',
        'formule',
        'capacite',
        'score_dernier_oriente_2025',
        'criteres',
        'description',
    ];

    protected $casts = [
        'criteres' => 'array',
        'capacite' => 'integer',
        'score_dernier_oriente_2025' => 'decimal:3',
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