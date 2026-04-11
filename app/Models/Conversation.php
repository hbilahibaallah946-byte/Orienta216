<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversations';
    
    protected $fillable = [
        'etudiant_id',
        'conseiller_id',
        'pris_par',        // ← Utilisez pris_par au lieu de conseiller_nom
        'statut',
        'pris_le',
        'dernier_message_at'
    ];
    
    protected $casts = [
        'pris_le' => 'datetime',
        'dernier_message_at' => 'datetime'
    ];
    
    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }
    
    public function conseiller()
    {
        return $this->belongsTo(User::class, 'conseiller_id');
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}