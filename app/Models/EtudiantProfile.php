<?php
// app/Models/EtudiantProfile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtudiantProfile extends Model
{
    protected $table = 'etudiants_profiles';
    
    protected $fillable = [
        'etudiant_id',
        'interets',
        'competences',
        'preferences',
    ];
    
    protected $casts = [
        'interets' => 'array',
        'competences' => 'array',
        'preferences' => 'array',
    ];
    
    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }
    
    public function allTags(): array
    {
        return array_values(array_unique(array_merge(
            $this->interets ?? [],
            $this->competences ?? [],
            $this->preferences ?? []
        )));
    }
}