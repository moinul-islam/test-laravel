<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display categories with add/edit form
     */
    public function index()
    {
        $categories = Category::with('parent', 'children')->orderBy('category_name')->get();
        $parentCategories = Category::whereNull('parent_cat_id')->orderBy('category_name')->get();
        
        return view('admin.category', compact('categories', 'parentCategories'));
    }

    /**
     * Store a new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,category_name',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_cat_id' => 'nullable|exists:categories,id',
            'cat_type' => 'required|in:universal,profile,product,service',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle image upload - store in public/icon folder and save only filename in DB
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($data['category_name']) . '.' . $image->getClientOriginalExtension();
            
            // Create icon directory if it doesn't exist
            $iconPath = public_path('icon');
            if (!file_exists($iconPath)) {
                mkdir($iconPath, 0755, true);
            }
            
            // Move image to public/icon folder
            $image->move($iconPath, $imageName);
            
            // Save only the filename in database
            $data['image'] = $imageName;
        }

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    /**
     * Update a category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,category_name,' . $category->id,
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_cat_id' => 'nullable|exists:categories,id',
            'cat_type' => 'required|in:universal,profile,product,service',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle image upload - store in public/icon folder and save only filename in DB
        if ($request->hasFile('image')) {
            // Delete old image from public/icon folder
            if ($category->image && file_exists(public_path('icon/' . $category->image))) {
                unlink(public_path('icon/' . $category->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($data['category_name']) . '.' . $image->getClientOriginalExtension();
            
            // Create icon directory if it doesn't exist
            $iconPath = public_path('icon');
            if (!file_exists($iconPath)) {
                mkdir($iconPath, 0755, true);
            }
            
            // Move image to public/icon folder
            $image->move($iconPath, $imageName);
            
            // Save only the filename in database
            $data['image'] = $imageName;
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a category
     */
    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category that has subcategories!');
        }

        // Delete image from public/icon folder if exists
        if ($category->image && file_exists(public_path('icon/' . $category->image))) {
            unlink(public_path('icon/' . $category->image));
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }

    /**
     * Get subcategories by parent (AJAX)
     */
    public function getSubcategories(Request $request)
    {
        $parentId = $request->get('parent_id');
        $subcategories = Category::where('parent_cat_id', $parentId)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
            
        return response()->json($subcategories);
    }
}