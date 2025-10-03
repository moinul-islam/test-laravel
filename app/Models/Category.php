<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
        'image',
        'parent_cat_id',
        'slug',
        'cat_type'
    ];
    // Parent category relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_cat_id');
    }
    // Child categories relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_cat_id');
    }

    // Category.php এ relationship add করুন  
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}