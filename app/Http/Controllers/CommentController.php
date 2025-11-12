<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostCategory;

class CommentController extends Controller
{
    public function commentStore(Request $request)
    {
        // Validate the request
        $request->validate([
            'content' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'comment_id' => 'nullable|exists:comments,id'
        ]);

        // Create comment
        $comment = new Comment();
        $comment->content = $request->content;
        $comment->user_id = $request->user_id;
        $comment->post_id = $request->post_id;
        $comment->comment_id = $request->comment_id; // null for main comments, id for replies
        $comment->save();

        // Load the user relationship
        $comment->load('user');

        // Get total comments count for this post (including replies)
        $post = Post::find($request->post_id);
        $totalCommentsCount = $post ? $post->allComments()->count() : 0;

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully!',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => $comment->user->name,
                    'user_image' => $comment->user->image 
                        ? asset('profile-image/' . $comment->user->image) 
                        : 'https://cdn-icons-png.flaticon.com/512/219/219983.png',
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
                'current_user_image' => Auth::user()->image 
                    ? asset('profile-image/' . Auth::user()->image) 
                    : 'https://cdn-icons-png.flaticon.com/512/219/219983.png',
                'total_comments_count' => $totalCommentsCount
            ], 200);
        }

        // For non-AJAX requests, redirect back
        return back()->with('success', 'Comment posted successfully!');
    }
}