<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    // Post Like/Unlike
    public function togglePostLike(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id'
        ]);

        $userId = Auth::id();
        $postId = $request->post_id;

        // Check if already liked
        $existingLike = Like::where('user_id', $userId)
                           ->where('post_id', $postId)
                           ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $liked = false;
        } else {
            // Like
            Like::create([
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            $liked = true;
        }

        // Get updated count
        $likesCount = Like::where('post_id', $postId)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    // Comment Like/Unlike
    public function toggleCommentLike(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id'
        ]);

        $userId = Auth::id();
        $commentId = $request->comment_id;

        // Check if already liked
        $existingLike = Like::where('user_id', $userId)
                           ->where('comment_id', $commentId)
                           ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $liked = false;
        } else {
            // Like
            Like::create([
                'user_id' => $userId,
                'comment_id' => $commentId
            ]);
            $liked = true;
        }

        // Get updated count
        $likesCount = Like::where('comment_id', $commentId)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }
}