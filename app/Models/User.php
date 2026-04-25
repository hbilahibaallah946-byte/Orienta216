<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Reponse;
use App\Models\Moyenne;
use App\Notifications\ResetPasswordCustom;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'validated_by',
        'validated_at',
        'preferred_language',
        'language',
        'filiere_id', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'validated_at' => 'datetime',
    'is_active' => 'boolean',
];

    public function profil()
{
    return $this->hasOne(EtudiantProfile::class, 'etudiant_id');
}

public function reponses()
{
    return $this->hasMany(\App\Models\Reponse::class, 'etudiant_id');
}

public function favorites()
{
    return $this->belongsToMany(Filiere::class, 'favorites', 'user_id', 'filiere_id');
}


public function moyennes()
{
    return $this->hasMany(\App\Models\Moyenne::class);
}


    // Ajouter la relation avec Filiere
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    // Relation pour le validateur
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function approve($adminId)
    {
        $this->update([
            'status' => 'approved',
            'validated_by' => $adminId,
            'validated_at' => now(),
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }
    // app/Models/User.php

// Ajoutez ces méthodes dans votre modèle User

public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function receivedMessages()
{
    return $this->hasMany(Message::class, 'receiver_id');
}

public function unreadMessages()
{
    return $this->hasMany(Message::class, 'receiver_id')->where('is_read', false);
}

public function getUnreadCountAttribute()
{
    return $this->unreadMessages()->count();
}

// Pour obtenir les conversations (dernier message avec chaque utilisateur)
public function conversations()
{
    $messages = Message::where('sender_id', $this->id)
        ->orWhere('receiver_id', $this->id)
        ->orderBy('created_at', 'desc')
        ->get();

    $conversations = [];
    foreach ($messages as $message) {
        $otherUser = $message->sender_id == $this->id 
            ? $message->receiver 
            : $message->sender;
        
        if (!isset($conversations[$otherUser->id])) {
            $conversations[$otherUser->id] = [
                'user' => $otherUser,
                'last_message' => $message,
                'unread_count' => Message::where('receiver_id', $this->id)
                    ->where('sender_id', $otherUser->id)
                    ->where('is_read', false)
                    ->count()
            ];
        }
    }
    
    return collect($conversations);
}
public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordCustom($token));
}

}