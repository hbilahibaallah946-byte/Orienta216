<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/ConseillerSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConseillerSetting extends Model
{
    protected $fillable = [
        'conseiller_id',
        'jours_disponibles',
        'heure_debut',
        'heure_fin',
        'max_etudiants_par_jour',
        'notif_nouveau_message',
        'notif_nouveau_etudiant',
        'notif_email',
    ];

    protected $casts = [
        'jours_disponibles'      => 'array',
        'notif_nouveau_message'  => 'boolean',
        'notif_nouveau_etudiant' => 'boolean',
        'notif_email'            => 'boolean',
    ];

    public function conseiller()
    {
        return $this->belongsTo(User::class, 'conseiller_id');
    }

}