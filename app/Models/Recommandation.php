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
        'rang',
    ];

    protected $casts = [
        'score' => 'integer',
        'rang'  => 'integer',
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