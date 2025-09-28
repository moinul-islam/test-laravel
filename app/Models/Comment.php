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
        return $this->belongsTo(User::class);
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

}
