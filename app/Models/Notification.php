<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'receiver_id',
        'sender_id',
        'type',
        'post_id',
        'comment_id',
        'seen',
        'seen_at'
    ];

    protected $casts = [
        'seen' => 'boolean',
        'seen_at' => 'datetime',
    ];

    // Relationships
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    // Scopes
    public function scopeUnseen($query)
    {
        return $query->where('seen', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('receiver_id', $userId);
    }
}