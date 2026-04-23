<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['user_id', 'body', 'parent_id'];

    protected $appends = ['likes_count', 'dislikes_count', 'user_reaction'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user', 'likes');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function getLikesCountAttribute(): int
    {
        return $this->likes()->where('value', 1)->count();
    }

    public function getDislikesCountAttribute(): int
    {
        return $this->likes()->where('value', -1)->count();
    }

    public function getUserReactionAttribute(): ?int
    {
        $userId = auth()->id();
        if (!$userId) return null;
        $like = $this->likes()->where('user_id', $userId)->first();
        return $like ? $like->value : null;
    }
}