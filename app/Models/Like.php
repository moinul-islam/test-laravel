<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'comment_id'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User',
            'username' => 'deleted',
            'image' => 'default.png'
        ]);
    }

    // Relationship with Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relationship with Comment
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}