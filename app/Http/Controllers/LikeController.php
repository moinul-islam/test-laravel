<?php
namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class LikeController extends Controller
{
    /**
     * Post Like/Unlike
     */
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
            
            // ✅ Send notification to post owner
            $post = Post::find($postId);
            if ($post && $post->user_id != $userId) {
                try {
                    $liker = Auth::user();
                    
                    $this->sendBrowserNotification(
                        $post->user_id,
                        '👍 ' . $liker->name . ' liked your post',
                        "{$liker->name} liked your post: \"{$post->title}\"",
                        $post->id,
                        url('/post/' . $post->slug),
                        'post_like',
                        $post->slug
                    );
                    
                    Log::info('Post like notification sent', [
                        'post_owner' => $post->user_id,
                        'liker' => $userId,
                        'post_id' => $post->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send like notification', [
                        'error' => $e->getMessage(),
                        'post_id' => $post->id
                    ]);
                }
            }
        }
        
        // Get updated count
        $likesCount = Like::where('post_id', $postId)->count();
        
        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }
    
    /**
     * Comment Like/Unlike
     */
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
            
            // ✅ Send notification to comment owner
            $comment = Comment::find($commentId);
            if ($comment && $comment->user_id != $userId) {
                try {
                    $liker = Auth::user();
                    $post = Post::find($comment->post_id);
                    
                    // Truncate comment content if too long
                    $commentPreview = strlen($comment->content) > 50 
                        ? substr($comment->content, 0, 50) . '...'
                        : $comment->content;
                    
                    $this->sendBrowserNotification(
                        $comment->user_id,
                        '👍 ' . $liker->name . ' liked your comment',
                        "{$liker->name} liked your comment: \"{$commentPreview}\"",
                        $post->id,
                        url('/post/' . $post->slug),
                        'comment_like',
                        $post->slug
                    );
                    
                    Log::info('Comment like notification sent', [
                        'comment_owner' => $comment->user_id,
                        'liker' => $userId,
                        'comment_id' => $comment->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send comment like notification', [
                        'error' => $e->getMessage(),
                        'comment_id' => $comment->id
                    ]);
                }
            }
        }
        
        // Get updated count
        $likesCount = Like::where('comment_id', $commentId)->count();
        
        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount
        ]);
    }
    
    /**
     * Send Firebase notification
     */
    private function sendBrowserNotification($userId, $title, $body, $sourceId = null, $customLink = null, $notificationType = 'post_like', $slug = null)
    {
        try {
            Log::info('Starting notification process', ['user_id' => $userId]);
            
            $user = \App\Models\User::with('fcmTokens')->find($userId);
            
            if (!$user || $user->fcmTokens->isEmpty()) {
                Log::info('No FCM tokens found for user', [
                    'user_id' => $userId,
                    'user_exists' => !!$user
                ]);
                return false;
            }
            
            $serviceAccountFile = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            $factory = (new Factory)->withServiceAccount($serviceAccountFile);
            $messaging = $factory->createMessaging();
            
            $timestamp = now()->timestamp;
            
            // Notification ID based on type
            $uniqueId = "{$notificationType}-{$sourceId}-{$timestamp}";
            $webUrl = $slug ? url("/post/{$slug}") : url('/');
            $deepLink = $slug ? url("/post/{$slug}") : url('/');
            
            $action = 'open_post';
            $screenName = 'post_detail';
            
            $sender = Auth::user();
            
            foreach ($user->fcmTokens as $tokenModel) {
                $token = $tokenModel->fcm_token;
                try {
                    $messageBuilder = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
                        ->withNotification([
                            'title' => $title,
                            'body' => $body,
                            'image' => $sender && $sender->image ? url('profile-image/' . $sender->image) : '',
                        ])
                        ->withData([
                            'user_id' => (string)$userId,
                            'source_id' => $sourceId ? (string)$sourceId : '',
                            'slug' => $slug ?? '',
                            'type' => 'browser_notification',
                            'seen' => 'false',
                            'notification_type' => $notificationType,
                            'sender_id' => $sender ? (string)$sender->id : '',
                            'sender_name' => $sender ? $sender->name : '',
                            'sender_image' => $sender && $sender->image ? url('profile-image/' . $sender->image) : '',
                            'action' => $action,
                            'web_url' => $webUrl,
                            'deep_link' => $deepLink,
                            'click_action' => $webUrl,
                            'screen_name' => $screenName,
                            'timestamp' => date('Y-m-d H:i:s'),
                            'notification_id' => $uniqueId
                        ]);
                    
                    $result = $messaging->send($messageBuilder);
                    
                    Log::info('Firebase messaging response', [
                        'user_id' => $userId,
                        'notification_type' => $notificationType,
                        'firebase_response' => $result
                    ]);
                } catch (\Exception $ex) {
                    Log::warning('Failed to send notification to a token', [
                        'user_id' => $userId,
                        'error' => $ex->getMessage()
                    ]);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase notification error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}