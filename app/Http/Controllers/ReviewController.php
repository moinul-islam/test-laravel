<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'business_user_id' => 'nullable|exists:users,id',
            'product_id' => 'nullable|exists:posts,id', // Changed from products to posts
            'rating' => 'required|integer|min:0|max:5',
            'comment' => 'required|string|min:1|max:1000',
        ]);

        // Ensure either business_user_id or product_id is provided
        if (!$request->business_user_id && !$request->product_id) {
            return back()->with('error', 'Invalid review target.');
        }

        // Auto-assign rating 3 if it's 0
        $rating = $request->rating;
        if ($rating == 0) {
            $rating = 3;
        }

        // Check if user is trying to review their own product
        if ($request->product_id) {
            $post = Post::findOrFail($request->product_id); // Changed from $product to $post
            if (Auth::id() == $post->user_id) {
                return back()->with('error', 'You cannot review your own product.');
            }
        }

        // Check if user is trying to review their own profile
        if ($request->business_user_id && Auth::id() == $request->business_user_id) {
            return back()->with('error', 'You cannot review your own profile.');
        }

        // Check if user already reviewed this target
        $existingReview = Review::where('user_id', Auth::id())
            ->when($request->product_id, fn($query) => $query->where('product_id', $request->product_id))
            ->when($request->business_user_id, fn($query) => $query->where('business_user_id', $request->business_user_id))
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this ' . ($request->product_id ? 'product.' : 'profile.'));
        }

        // Create new review
        Review::create([
            'user_id' => Auth::id(),
            'business_user_id' => $request->business_user_id,
            'product_id' => $request->product_id,
            'rating' => $rating,
            'comment' => $request->comment,
            'status' => 'approved',
        ]);

        return back()->with('success', 'Thank you for your review!');
    }

   
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check if the user is authorized to update this review
        if (Auth::id() != $review->user_id && Auth::id() != ($review->product ? $review->product->user_id : $review->business_user_id)) {
            return back()->with('error', 'You are not authorized to update this review.');
        }

        // Validate the request
        $validatedData = $request->validate([
            'rating' => 'required|integer|min:0|max:5',
            'comment' => 'required|string|min:1|max:1000',
        ]);

        // Auto-assign rating 3 if it's 0
        $rating = $request->rating;
        if ($rating == 0) {
            $rating = 3;
        }

        // Update the review
        $review->update([
            'rating' => $rating,
            'comment' => $request->comment,
            'status' => 'approved',
        ]);

        return back()->with('success', 'Review updated successfully.');
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Check if the user is authorized to delete this review
        $isAdmin = Auth::check() && Auth::user()->role == 'admin'; // Adjusted for role check
        
        if (Auth::id() != $review->user_id && 
            Auth::id() != ($review->product ? $review->product->user_id : $review->business_user_id) && 
            !$isAdmin) {
            return back()->with('error', 'You are not authorized to delete this review.');
        }

        // Delete the review
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    /**
     * Show all reviews for a specific user (business profile)
     */
    public function userWiseReview(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if ($user == null || $user->profile_type == 'personal') {
            return redirect('/' . $username);
        }

        // Get all reviews for this user with pagination
        $reviews = Review::where('business_user_id', $user->id)
                        ->where('status', 'approved')
                        ->with('user') // Eager load user who wrote the review
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        // Get average rating
        $averageRating = Review::where('business_user_id', $user->id)
                            ->where('status', 'approved')
                            ->avg('rating') ?? 0;
        
        // Get rating breakdown
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingBreakdown[$i] = Review::where('business_user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->where('rating', $i)
                                        ->count();
        }
        
        return view('frontend.review.user_wise_review', 
            compact('username', 'user', 'reviews', 'averageRating', 'ratingBreakdown'));
    }

    /**
     * Show all reviews for a specific product/post
     */
    public function productReviews($slug)
    {
        $product = Post::where('slug', $slug)->firstOrFail();

        // Get all reviews for this product with pagination
        $reviews = Review::where('product_id', $product->id)
                        ->where('status', 'approved')
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        // Get average rating
        $averageRating = $product->averageRating();
        
        // Get rating breakdown
        $ratingBreakdown = $product->ratingBreakdown();
        
        return view('frontend.review.product_reviews', 
            compact('product', 'reviews', 'averageRating', 'ratingBreakdown'));
    }
}