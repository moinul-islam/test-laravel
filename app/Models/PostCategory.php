<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    
    // Post relationship
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }
    
    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}