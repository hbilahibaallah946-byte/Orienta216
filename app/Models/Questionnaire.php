<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    protected $fillable = [
        'conseiller_id', 'etudiant_id', 'titre', 'statut',
        'envoye_le', 'repondu_le'
    ];

    protected $casts = [
        'envoye_le'  => 'datetime',
        'repondu_le' => 'datetime',
    ];

    public function conseiller()
    {
        return $this->belongsTo(User::class, 'conseiller_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class);
    }

    public function estRepondu(): bool
    {
        return $this->statut === 'repondu';
    }
}