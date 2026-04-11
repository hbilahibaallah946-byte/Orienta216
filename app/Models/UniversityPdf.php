<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UniversityPdf extends Model
{
    protected $table = 'university_pdf';

    protected $fillable = ['filename', 'path', 'size'];

    // Récupère le PDF le plus récent
    public static function getCurrent()
    {
        return self::latest()->first();
    }

    // URL publique du fichier
    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }
        return asset('storage/' . $this->path);
    }

    // Supprime le fichier physique lors de la suppression du modèle
    protected static function booted()
    {
        static::deleting(function ($pdf) {
            if ($pdf->path) {
                Storage::disk('public')->delete($pdf->path);
            }
        });
    }
}