<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function user()
{
    return $this->belongsTo(User::class)->withDefault([
        'name' => 'Deleted User',
        'username' => 'deleted',
        'image' => 'default.png'
    ]);
}

   

     public function parent()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    // Replies (child comments)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

}
