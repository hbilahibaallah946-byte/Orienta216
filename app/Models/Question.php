<?php
// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'questionnaire_id', 
        'texte', 
        'type', 
        'options',
        'ordre', 
        'mots_cles', 
        'categorie',
        'is_active'
    ];

    protected $casts = [
        'options'   => 'array',
        'mots_cles' => 'array',
    ];

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }
}