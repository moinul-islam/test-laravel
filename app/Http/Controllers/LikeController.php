<?php
namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
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
        
        $existingLike = Like::where('user_id', $userId)
                           ->where('post_id', $postId)
                           ->first();
        
        if ($existingLike) {
            // Unlike - notification remove করুন
            $existingLike->delete();
            
            Notification::where('sender_id', $userId)
                       ->where('type', 'post_like')
                       ->where('post_id', $postId)
                       ->delete();
            
            $liked = false;
            
            Log::info('Post unliked and notification removed', [
                'user_id' => $userId,
                'post_id' => $postId
            ]);
        } else {
            // Like
            Like::create([
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            $liked = true;
            
            // Send notification to post owner
            $post = Post::find($postId);
            if ($post && $post->user_id != $userId) {
                try {
                    $liker = Auth::user();
                    
                    // Database notification store
                    $notification = Notification::create([
                        'receiver_id' => $post->user_id,
                        'sender_id' => $userId,
                        'type' => 'post_like',
                        'post_id' => $post->id,
                        'seen' => false
                    ]);
                    
                    // Firebase notification
                    $this->sendFirebaseNotification(
                        $post->user_id,
                        '👍 ' . $liker->name . ' liked your post',
                        "{$liker->name} liked your post: \"{$post->title}\"",
                        $notification->id,
                        url('/post/' . $post->slug),
                        $post->slug,
                        'post_like'
                    );
                    
                    Log::info('Post like notification created', [
                        'notification_id' => $notification->id,
                        'receiver' => $post->user_id,
                        'sender' => $userId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
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
        
        $existingLike = Like::where('user_id', $userId)
                           ->where('comment_id', $commentId)
                           ->first();
        
        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            
            Notification::where('sender_id', $userId)
                       ->where('type', 'comment_like')
                       ->where('comment_id', $commentId)
                       ->delete();
            
            $liked = false;
            
            Log::info('Comment unliked and notification removed', [
                'user_id' => $userId,
                'comment_id' => $commentId
            ]);
        } else {
            // Like
            Like::create([
                'user_id' => $userId,
                'comment_id' => $commentId
            ]);
            $liked = true;
            
            $comment = Comment::find($commentId);
            if ($comment && $comment->user_id != $userId) {
                try {
                    $liker = Auth::user();
                    $post = Post::find($comment->post_id);
                    
                    $commentPreview = strlen($comment->content) > 50 
                        ? substr($comment->content, 0, 50) . '...'
                        : $comment->content;
                    
                    // Database notification
                    $notification = Notification::create([
                        'receiver_id' => $comment->user_id,
                        'sender_id' => $userId,
                        'type' => 'comment_like',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    // Firebase notification
                    $this->sendFirebaseNotification(
                        $comment->user_id,
                        '👍 ' . $liker->name . ' liked your comment',
                        "{$liker->name} liked your comment: \"{$commentPreview}\"",
                        $notification->id,
                        $post->slug,
                        'comment_like',
                        $comment->id
                    );
                    
                    Log::info('Comment like notification created', [
                        'notification_id' => $notification->id,
                        'receiver' => $comment->user_id,
                        'sender' => $userId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create notification', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
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
    private function sendFirebaseNotification($userId, $title, $body, $notificationId, $slug, $notificationType, $commentId = null)
    {
        try {
            $user = \App\Models\User::with('fcmTokens')->find($userId);
            
            if (!$user || $user->fcmTokens->isEmpty()) {
                Log::info('No FCM tokens', ['user_id' => $userId]);
                return false;
            }
            
            $serviceAccountFile = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            
            if (!file_exists($serviceAccountFile)) {
                Log::error('Firebase credentials not found');
                return false;
            }
            
            $factory = (new Factory)->withServiceAccount($serviceAccountFile);
            $messaging = $factory->createMessaging();
            
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
                            'notification_id' => (string)$notificationId,
                            'user_id' => (string)$userId,
                            'slug' => $slug ?? '',
                            'comment_id' => $commentId ? (string)$commentId : '',
                            'type' => 'browser_notification',
                            'notification_type' => $notificationType,
                            'sender_id' => $sender ? (string)$sender->id : '',
                            'sender_name' => $sender ? $sender->name : '',
                            'sender_image' => $sender && $sender->image ? url('profile-image/' . $sender->image) : '',
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);
                    
                    $messaging->send($messageBuilder);
                    
                    Log::info('Firebase sent', [
                        'notification_id' => $notificationId
                    ]);
                } catch (\Exception $ex) {
                    Log::warning('Firebase token failed', [
                        'error' => $ex->getMessage()
                    ]);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}