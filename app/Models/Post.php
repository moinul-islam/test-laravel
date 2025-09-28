<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    
    protected $fillable = [
    'title',
    'price', 
    'highest_price',
    'image',
    'description',
    'user_id',
    'category_id',
    'new_category', // এই লাইনটি add করুন
];

// Post.php এ relationship add করুন
public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
   
    public function comments()
    {
        return $this->hasMany(Comment::class)
                    ->whereNull('comment_id') // শুধু main comment
                    ->latest();               // সর্বশেষ আগে আসবে
    }


}