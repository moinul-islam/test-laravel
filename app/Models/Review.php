<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{


    protected $fillable = ['user_id', 'product_id', 'rating', 'comment'];
  
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Product/Post being reviewed
    public function product()
    {
        return $this->belongsTo(Post::class, 'product_id', 'id');
    }

    // Scope for filtering
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Get recent reviews
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }


}