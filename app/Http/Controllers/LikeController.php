<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;

class LikeController extends Controller
{
    public function togglePostLike(Request $request)
    {
        $request->validate(['post_id' => 'required|exists:posts,id']);

        $userId = Auth::id();
        $postId = $request->post_id;

        $existingLike = Like::where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            Like::create(['user_id' => $userId, 'post_id' => $postId]);
            $liked = true;

            $post = Post::find($postId);
            if ($post && $post->user_id != $userId) {
                $liker = Auth::user();

                $this->sendBrowserNotification(
                    $post->user_id,
                    '👍 ' . $liker->name . ' liked your post',
                    $liker->name . ' liked your post: "' . $post->title . '"',
                    $post->id,
                    $post->slug,
                    'post_like'
                );
            }
        }

        $likesCount = Like::where('post_id', $postId)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    public function toggleCommentLike(Request $request)
    {
        $request->validate(['comment_id' => 'required|exists:comments,id']);

        $userId = Auth::id();
        $commentId = $request->comment_id;

        $existingLike = Like::where('user_id', $userId)->where('comment_id', $commentId)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            Like::create(['user_id' => $userId, 'comment_id' => $commentId]);
            $liked = true;

            $comment = Comment::find($commentId);
            if ($comment && $comment->user_id != $userId) {
                $liker = Auth::user();
                $post = Post::find($comment->post_id);

                $preview = strlen($comment->content) > 50
                    ? substr($comment->content, 0, 50) . '...'
                    : $comment->content;

                $this->sendBrowserNotification(
                    $comment->user_id,
                    '👍 ' . $liker->name . ' liked your comment',
                    $liker->name . ' liked your comment: "' . $preview . '"',
                    $post->id,
                    $post->slug,
                    'comment_like'
                );
            }
        }

        $likesCount = Like::where('comment_id', $commentId)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }

    private function sendBrowserNotification($receiverId, $title, $body, $postId, $slug, $type)
    {
        try {
            $user = \App\Models\User::with('fcmTokens')->find($receiverId);

            if (!$user || $user->fcmTokens->isEmpty()) {
                return false;
            }

            $sender = Auth::user();
            $postUrl = url("/post/{$slug}");

            $serviceAccount = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            $messaging = (new Factory)->withServiceAccount($serviceAccount)->createMessaging();

            $uniqueId = "{$type}-{$postId}-" . now()->timestamp;

            foreach ($user->fcmTokens as $tokenModel) {
                $token = $tokenModel->fcm_token;

                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification([
                        'title' => $title,
                        'body'  => $body,
                        'image' => $sender?->image ? url('profile-image/' . $sender->image) : null,
                    ])
                    ->withWebPushConfig(WebPushConfig::fromArray([
                        'fcm_options' => [
                            'link' => $postUrl   // এটাই সবচেয়ে জরুরি – browser push এ ক্লিক করলে এই লিঙ্কে যাবে
                        ]
                    ]))
                    ->withData([
                        'post_id'         => (string)$postId,
                        'slug'            => $slug,
                        'type'            => 'browser_notification',
                        'notification_type'=> $type,
                        'click_action'    => $postUrl,
                        'url'             => $postUrl,
                        'notification_id' => $uniqueId,
                        'sender_id'       => (string)$sender?->id,
                        'sender_name'     => $sender?->name ?? '',
                        'sender_image'    => $sender?->image ? url('profile-image/' . $sender->image) : '',
                    ]);

                $messaging->send($message);
            }

            Log::info('Like notification sent successfully', [
                'receiver' => $receiverId,
                'post_url' => $postUrl,
                'type'     => $type
            ]);

        } catch (\Exception $e) {
            Log::error('Like notification failed', ['error' => $e->getMessage()]);
        }
    }
}