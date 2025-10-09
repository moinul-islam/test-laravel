<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminPostController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function ($request, $next) {
                if (!Auth::check() || Auth::user()->role !== 'admin') {
                    return redirect()->route('home')->with('error', 'Unauthorized access. Admin only!');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Show create post form
     */
    public function showCreateForm()
    {
        $users = User::where('role', '!=', 'admin')
                     ->orderBy('name', 'asc')
                     ->get();
        
        $categories = Category::orderBy('category_name', 'asc')->get();
        
        return view('admin.create-post', compact('users', 'categories'));
    }

    /**
     * Store post
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_data' => 'nullable|string',
        ]);

        try {
            // Check if category exists
            $category = null;
            if ($request->filled('category_id')) {
                $category = Category::find($request->category_id);
            }

            // Create new post
            $post = new Post();
            $post->user_id = $request->user_id;
            $post->title = $request->title;
            $post->price = $request->price;
            $post->description = $request->description;

            // Handle category
            if ($category) {
                $post->category_id = $category->id;
                $post->new_category = null;
            } else {
                $post->new_category = $request->category_name;
                $post->category_id = null;
            }

            // Handle image upload
            if ($request->filled('image_data')) {
                $image = $request->image_data;
                
                // Remove data:image/jpeg;base64, prefix
                if (strpos($image, 'data:image') !== false) {
                    $image = substr($image, strpos($image, ',') + 1);
                }
                
                $image = str_replace(' ', '+', $image);
                $imageName = 'post_' . time() . '_' . uniqid() . '.jpg';
                
                // Create uploads directory if not exists
                $uploadPath = public_path('uploads');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0755, true);
                }
                
                // Save image
                File::put($uploadPath . '/' . $imageName, base64_decode($image));
                $post->image = $imageName;
            }

            $post->save();

            // Get user name for success message
            $userName = User::find($request->user_id)->name;

            return redirect()->back()->with('success', "Post created successfully for {$userName}!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create post: ' . $e->getMessage())
                ->withInput();
        }
    }
}