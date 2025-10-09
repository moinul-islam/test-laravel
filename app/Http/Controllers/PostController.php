<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Country;
use App\Models\City;
use App\Models\Category;

class PostController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
   public function index(Request $request)
   {
       $path = view()->shared('visitorLocationPath');
       if ($path) {
           return redirect('/' . $path);
       }
       // Fallback: if no location path, show default home page
       return view('frontend.index');
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
            'description' => 'nullable|string|max:1000|required_without:image_data',
        ]);
        
        $photo = null;
       
        // Check if we have base64 image data from frontend processing
        if ($request->filled('image_data')) {
            // Process base64 image
            $imageData = $request->input('image_data');
           
            // Extract the base64 content
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $decodedImage = base64_decode($imageData);
           
            if ($decodedImage !== false) {
                // Generate unique filename
                $name_gen = hexdec(uniqid()) . '.jpg';
                
                // Ensure directory exists
                if (!file_exists(public_path('uploads'))) {
                    mkdir(public_path('uploads'), 0755, true);
                }
               
                // Save the image inside uploads folder
                file_put_contents(public_path('uploads/' . $name_gen), $decodedImage);
                
                // Save only the filename for database
                $photo = $name_gen;
            }
        }
        // Fallback to traditional file upload if no image_data present
        elseif ($request->hasFile('image')) {
            $photoFile = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $photoFile->getClientOriginalExtension();
           
            // Move the file to uploads folder
            $photoFile->move(public_path('uploads'), $name_gen);
            
            // Save only the filename for database
            $photo = $name_gen;
        }
        
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
        
        // DB-এ সেভ করা
        Post::create([
            'title' => $request->title,
            'price' => $request->price,
            'highest_price' => $request->discount ?? null,
            'image' => $photo,
            'description' => $request->description,
            'user_id' => $user_id,
            'category_id' => $categoryId, // Will be null if new category
            'new_category' => $newCategory, // Will be null if existing category
        ]);
       
        return back()->with('success', 'Post created successfully!');
    }

    public function edit($id)
{
    $post = Post::with('category')->findOrFail($id);
    
    // Check if user owns this post
    if(Auth::id() != $post->user_id) {
        abort(403, 'Unauthorized action.');
    }
    
    return response()->json($post);
}

public function update(Request $request, $id)
{
    $post = Post::findOrFail($id);
    
    // Check if user owns this post
    if(Auth::id() != $post->user_id) {
        abort(403, 'Unauthorized action.');
    }
    
    // Validation
    $request->validate([
        'category_name' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|string|max:1000|required_without:image_data',
    ]);
    
    $photo = $post->image; // Keep existing image by default
    
    // Check if we have base64 image data from frontend processing
    if ($request->filled('image_data')) {
        // Delete old image if exists
        if($post->image && file_exists(public_path('uploads/' . $post->image))) {
            unlink(public_path('uploads/' . $post->image));
        }
        
        // Process base64 image
        $imageData = $request->input('image_data');
        
        // Extract the base64 content
        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedImage = base64_decode($imageData);
        
        if ($decodedImage !== false) {
            // Generate unique filename
            $name_gen = hexdec(uniqid()) . '.jpg';
            
            // Ensure directory exists
            if (!file_exists(public_path('uploads'))) {
                mkdir(public_path('uploads'), 0755, true);
            }
            
            // Save the image inside uploads folder
            file_put_contents(public_path('uploads/' . $name_gen), $decodedImage);
            
            // Save only the filename for database
            $photo = $name_gen;
        }
    }
    // Fallback to traditional file upload if no image_data present
    elseif ($request->hasFile('photo')) {
        // Delete old image if exists
        if($post->image && file_exists(public_path('uploads/' . $post->image))) {
            unlink(public_path('uploads/' . $post->image));
        }
        
        $photoFile = $request->file('photo');
        $name_gen = hexdec(uniqid()) . '.' . $photoFile->getClientOriginalExtension();
        
        // Move the file to uploads folder
        $photoFile->move(public_path('uploads'), $name_gen);
        
        // Save only the filename for database
        $photo = $name_gen;
    }
    
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
    
    // Update post in DB
    $post->update([
        'title' => $request->title,
        'price' => $request->price,
        'highest_price' => $request->discount ?? null,
        'image' => $photo,
        'description' => $request->description,
        'category_id' => $categoryId, // Will be null if new category
        'new_category' => $newCategory, // Will be null if existing category
    ]);
    
    return back()->with('success', 'Post updated successfully!');
}
public function showByCategory(Request $request, $username, $slug)
{
    $path = $username;
    $category = Category::where('slug', $slug)->first();
   
    if (!$category) {
        abort(404, 'Category not found');
    }
   
    // Get all descendant category IDs
    $categoryIds = $this->getAllDescendantCategoryIds($category->id);
    $categoryIds[] = $category->id;
   
    // Initialize user IDs based on location
    $userIds = [];
    
    if ($path == 'international') {
        $userIds = User::where(function($query) {
            $query->where('phone_verified', 0)
                  ->orWhere('email_verified', 0);
        })->pluck('id')->toArray();
    } else {
        $country = Country::where('username', $path)->first();
        if ($country) {
            $userIds = User::where('country_id', $country->id)
                ->where(function($query) {
                    $query->where('phone_verified', 0)
                          ->orWhere('email_verified', 0);
                })
                ->pluck('id')
                ->toArray();
        } else {
            $city = City::where('username', $path)->first();
            if ($city) {
                $userIds = User::where('city_id', $city->id)
                    ->where(function($query) {
                        $query->where('phone_verified', 0)
                              ->orWhere('email_verified', 0);
                    })
                    ->pluck('id')
                    ->toArray();
            } else {
                $userIds = User::where(function($query) {
                    $query->where('phone_verified', 0)
                          ->orWhere('email_verified', 0);
                })->pluck('id')->toArray();
            }
        }
    }
   
    // Handle sorting
    $sortType = $request->get('sort', 'newest');
    
    // ✅ Check category type - Profile vs Product/Service
    if ($category->cat_type == 'profile') {
        // Show Users for Profile categories
        $posts = User::whereIn('category_id', $categoryIds)
            ->whereIn('id', $userIds)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(12);
            
    } else {
        // Show Posts for Product/Service categories
        
        // ✅ Best Selling এর জন্য আলাদা query
        if ($sortType == 'best-selling') {
            $posts = Post::with(['user', 'category'])
                ->whereIn('posts.category_id', $categoryIds)
                ->whereIn('posts.user_id', $userIds)
                ->leftJoin('orders', function($join) {
                    $join->whereRaw("JSON_SEARCH(orders.post_ids, 'one', CAST(posts.id AS CHAR), NULL, '$[*].post_id') IS NOT NULL")
                         ->whereIn('orders.status', ['delivered', 'shipped']);
                })
                ->select(
                    'posts.id',
                    'posts.category_id',
                    'posts.new_category',
                    'posts.image',
                    'posts.title',
                    'posts.description',
                    'posts.price',
                    'posts.user_id',
                    'posts.created_at',
                    'posts.updated_at',
                    DB::raw('COUNT(orders.id) as order_count')
                )
                ->groupBy(
                    'posts.id',
                    'posts.category_id',
                    'posts.new_category',
                    'posts.image',
                    'posts.title',
                    'posts.description',
                    'posts.price',
                    'posts.user_id',
                    'posts.created_at',
                    'posts.updated_at'
                )
                ->orderByDesc('order_count')
                ->orderByDesc('posts.created_at')
                ->paginate(12);
        } else {
            // ✅ অন্যান্য sorting এর জন্য normal query
            $posts = Post::with(['user', 'category'])
                         ->whereIn('posts.category_id', $categoryIds)
                         ->whereIn('posts.user_id', $userIds);
            
            // Apply sorting
            switch ($sortType) {
                case 'price-low':
                    $posts = $posts->orderBy('price', 'asc');
                    break;
                    
                case 'price-high':
                    $posts = $posts->orderBy('price', 'desc');
                    break;
                    
                case 'newest':
                default:
                    $posts = $posts->orderBy('created_at', 'desc');
                    break;
            }
            
            $posts = $posts->paginate(12);
        }
    }
   
    // Get breadcrumb data
    $breadcrumbs = $this->getCategoryBreadcrumbs($category);
   
    // Get parent category
    $parentCategory = null;
    if ($category->parent_cat_id) {
        $parentCategory = Category::find($category->parent_cat_id);
    }
   
    // Get sibling categories
    $siblingCategories = [];
    if ($category->parent_cat_id) {
        $locationCategoryIds = [];
        
        if ($category->cat_type == 'profile') {
            // Profile category - check users only
            $locationCategoryIds = User::whereIn('id', $userIds)
                ->distinct()
                ->pluck('category_id')
                ->toArray();
        } else {
            // Product/Service category - check posts only
            $locationCategoryIds = Post::whereIn('user_id', $userIds)
                ->distinct()
                ->pluck('category_id')
                ->toArray();
        }
        
        $siblingCategories = Category::where('parent_cat_id', $category->parent_cat_id)
                                    ->where('cat_type', $category->cat_type) // Same type only
                                    ->whereIn('id', $locationCategoryIds)
                                    ->get();
    }
   
    // Get child categories
    $childCategories = [];
    $locationCategoryIds = [];
    
    if ($category->cat_type == 'profile') {
        // Profile category - check users only
        $locationCategoryIds = User::whereIn('id', $userIds)
            ->distinct()
            ->pluck('category_id')
            ->toArray();
    } else {
        // Product/Service category - check posts only
        $locationCategoryIds = Post::whereIn('user_id', $userIds)
            ->distinct()
            ->pluck('category_id')
            ->toArray();
    }
    
    $childCategories = Category::where('parent_cat_id', $category->id)
                              ->where('cat_type', $category->cat_type) // Same type only
                              ->whereIn('id', $locationCategoryIds)
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
        'breadcrumbs' => $breadcrumbs,
        'visitorLocationPath' => $path
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