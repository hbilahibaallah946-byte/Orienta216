<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moyenne extends Model
{
    protected $table = 'moyennes';
    
    protected $fillable = [
        'user_id',
        'specialite',
        'matieres',
        'session',
        'moyenne',
        'score',
        'score_plus_7'
    ];
    
    protected $casts = [
        'matieres' => 'array',
        'moyenne' => 'decimal:2',
        'score' => 'decimal:2',
        'score_plus_7' => 'decimal:2'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}