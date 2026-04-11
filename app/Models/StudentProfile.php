<?php
// app/Models/StudentProfile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialite',
        'matieres',
        'session',
    ];

    protected $casts = [
        'matieres' => 'array', // convert JSON <-> array automatiquement
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
