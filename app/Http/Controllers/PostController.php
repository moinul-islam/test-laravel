<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Category;

class PostController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
   public function index(Request $request)
{
    $posts = Post::with(['user', 'category', 'comments.user']) // comments সহ লোড হবে
        ->latest()
        ->paginate(3);
    if ($request->ajax()) {
        return response()->json([
            'posts' => view('frontend.posts-partial', compact('posts'))->render(),
            'hasMore' => $posts->hasMorePages()
        ]);
    }
    return view("frontend.index", compact('posts'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::id(); // লগইন করা ইউজারের ID
        
        // Validation
        $request->validate([
            'category_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048|required_without:description',
            'description' => 'nullable|string|max:1000|required_without:image',
        ]);

        $categoryId = null;
        $newCategory = null;

        // Check if category_id is provided (existing category selected)
        if ($request->filled('category_id') && $request->category_id != '') {
            // Validate that the category exists
            $categoryExists = Category::where('id', $request->category_id)->exists();
            if ($categoryExists) {
                $categoryId = $request->category_id;
            } else {
                // If category_id doesn't exist, treat as new category
                $newCategory = $request->category_name;
            }
        } else {
            // User typed a new category name
            $newCategory = $request->category_name;
        }

        // Handle image upload
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads'), $imageName);
        }
       
        // DB-এ সেভ করা
        Post::create([
            'title' => $request->title,
            'price' => $request->price,
            'highest_price' => $request->discount ?? null,
            'image' => $imageName,
            'description' => $request->description,
            'user_id' => $user_id,
            'category_id' => $categoryId, // Will be null if new category
            'new_category' => $newCategory, // Will be null if existing category
        ]);
       
        return back()->with('success', 'Post created successfully!');
    }

    public function showByCategory(Request $request, $slug)
    {
        // Find category by slug (any level)
        $category = Category::where('slug', $slug)->first();
        
        if (!$category) {
            abort(404, 'Category not found');
        }
        
        // Get all descendant category IDs (recursive)
        $categoryIds = $this->getAllDescendantCategoryIds($category->id);
        $categoryIds[] = $category->id; // Include the category itself
        
        // Check if there are users with these category IDs (profile data)
        $hasUsers = User::whereIn('category_id', $categoryIds)->exists();
        
        if ($hasUsers) {
            // Load users if they exist for this category
            $posts = User::whereIn('category_id', $categoryIds)
                        ->with('category');
        } else {
            // Load regular posts for product/service categories
            $posts = Post::with(['user', 'category'])
                         ->whereIn('category_id', $categoryIds)
                         ->latest();
        }
        
        // Handle sorting
        if ($request->get('sort')) {
            switch ($request->get('sort')) {
                case 'price-low':
                    $posts = $posts->orderBy('price', 'asc');
                    break;
                case 'price-high':
                    $posts = $posts->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $posts = $posts->orderBy('created_at', 'desc');
                    break;
                default:
                    $posts = $posts->latest();
            }
        } else {
            $posts = $posts->latest();
        }
        
        $posts = $posts->paginate(12);
        
        // Get breadcrumb data
        $breadcrumbs = $this->getCategoryBreadcrumbs($category);
        
        // Get parent category (if exists)
        $parentCategory = null;
        if ($category->parent_cat_id) {
            $parentCategory = Category::find($category->parent_cat_id);
        }
        
        // Get sibling categories
        $siblingCategories = [];
        if ($category->parent_cat_id) {
            $siblingCategories = Category::where('parent_cat_id', $category->parent_cat_id)
                                        ->whereIn('cat_type', ['product', 'service', 'profile'])
                                        ->get();
        }
        
        // Get child categories
        $childCategories = Category::where('parent_cat_id', $category->id)
                                  ->whereIn('cat_type', ['product', 'service', 'profile'])
                                  ->get();
        
        // AJAX request handling
        if ($request->ajax()) {
            return response()->json([
                'posts' => view('frontend.products-partial', compact('posts'))->render(),
                'hasMore' => $posts->hasMorePages()
            ]);
        }
        
        return view('frontend.products', [
            'posts' => $posts,
            'category' => $category,
            'parentCategory' => $parentCategory,
            'siblingCategories' => $siblingCategories,
            'childCategories' => $childCategories,
            'breadcrumbs' => $breadcrumbs
        ]);
    }
    
    /**
     * Get all descendant category IDs recursively
     */
    private function getAllDescendantCategoryIds($categoryId)
    {
        $ids = [];
        
        // Get direct children
        $children = Category::where('parent_cat_id', $categoryId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids[] = $childId;
            // Recursively get descendants of each child
            $ids = array_merge($ids, $this->getAllDescendantCategoryIds($childId));
        }
        
        return $ids;
    }
    
    /**
     * Get category breadcrumbs for navigation
     */
    private function getCategoryBreadcrumbs($category)
    {
        $breadcrumbs = [];
        $current = $category;
        
        // Build breadcrumb trail from current to root
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent_cat_id ? Category::find($current->parent_cat_id) : null;
        }
        
        return $breadcrumbs;
    }
   
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
   
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
   
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
   
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
       
        // Check if the authenticated user owns this post
        if ($post->user_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this post.'
            ], 403);
        }
       
        // Delete the image file if it exists
        if ($post->image) {
            $imagePath = public_path('uploads/' . $post->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
       
        // Delete the post from database
        $post->delete();
       
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully!'
        ]);
    }
}