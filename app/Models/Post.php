<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;
   
    protected $guarded = [];
    
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
    
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'id')->latest();
    }
    
    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
    
    public function reviewCount()
    {
        return $this->reviews()->count();
    }
    
    // Discount expire check করবে এবং database এ save করবে
    public function getDiscountPriceAttribute($value)
    {
        // যদি discount সময় শেষ হয়ে যায়
        if ($this->discount_until && now()->greaterThan($this->discount_until) && $value !== null) {
            // Database এ update করবে
            $this->updateQuietly([
                'discount_price' => null,
                'discount_until' => null
            ]);
            return null;
        }
        return $value;
    }

    // সঠিক price পেতে এই method ব্যবহার করুন
    public function getCurrentPrice()
    {
        // যদি discount active থাকে
        if ($this->discount_price && $this->discount_until && now()->lessThan($this->discount_until)) {
            return $this->discount_price;
        }
        return $this->price; // নাহলে normal price
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


  

// All comments including replies
public function allComments()
{
    return $this->hasMany(Comment::class); // সব comments + replies
}

// Comments count method
public function commentsCount()
{
    return $this->comments()->count(); // শুধু main comments count
}

// Total comments including replies
public function totalCommentsCount()
{
    return $this->allComments()->count(); // সব comments + replies count
}




}