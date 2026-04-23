<?php
// app/Models/Recommandation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommandation extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_id',
        'filiere_id',
        'score',
        'score_academique',
        'score_compatibilite',
        'score_competitivite',
        'score_reference_marche',
        'accessible_selon_bac',
        'rang',
    ];

    protected $casts = [
        'score' => 'integer',
        'rang'  => 'integer',
        'score_academique' => 'integer',
        'score_compatibilite' => 'integer',
        'score_competitivite' => 'integer',
        'score_reference_marche' => 'decimal:3',
        'accessible_selon_bac' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
}