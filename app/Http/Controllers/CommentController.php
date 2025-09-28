<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostCategory;

class CommentController extends Controller
{
    public function commentStore(Request $request){
        $comment = New comment();
        $comment->content = $request->content;
        $comment->user_id = $request->user_id;
        $comment->post_id = $request->post_id;
        $comment->comment_id = $request->comment_id;

        $comment->save();

       return back()->with('success', 'Post comment successfully!');


    }
}