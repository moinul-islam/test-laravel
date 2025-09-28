<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
   
    protected $casts = [
        'post_ids' => 'array'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
   
    public function deliveryman()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }
   
    // ✅ Renamed method - no longer conflicts with relationship expectations
    public function getOrderPosts()
    {
        if (!$this->post_ids) return collect([]);
       
        $postIds = collect($this->post_ids)->pluck('post_id')->toArray();
        return Post::whereIn('id', $postIds)->get();
    }
   
    public function getOrderedPostsWithDetails()
    {
        if (!$this->post_ids) return collect([]);
       
        $postIds = collect($this->post_ids)->pluck('post_id')->toArray();
        $posts = Post::with('category')->whereIn('id', $postIds)->get();
       
        return $posts->map(function ($post) {
            $orderItem = collect($this->post_ids)->firstWhere('post_id', $post->id);
            $post->ordered_quantity = $orderItem['quantity'] ?? 0;
            $post->service_time = $orderItem['service_time'] ?? null;
            return $post;
        });
    }
}