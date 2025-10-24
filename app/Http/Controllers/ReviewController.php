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
            'product_id' => 'nullable|exists:posts,id', // Changed from products to posts
            'rating' => 'required|integer|min:0|max:5',
            'comment' => 'required|string|min:1|max:1000',
        ]);


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




        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this ' . ($request->product_id ? 'product.' : 'profile.'));
        }

        // Create new review
        Review::create([
            'user_id' => Auth::id(),
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
        // Delete the review
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
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