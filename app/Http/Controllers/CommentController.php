<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class CommentController extends Controller
{
    public function commentStore(Request $request)
    {
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
        $comment->comment_id = $request->comment_id;
        $comment->save();
        
        $comment->load('user');
        
        $post = Post::with('user')->find($request->post_id);
        $totalCommentsCount = $post ? $post->allComments()->count() : 0;
        
        // Send Notification
        try {
            $commentAuthor = Auth::user();
            $commentPreview = strlen($request->content) > 50 
                ? substr($request->content, 0, 50) . '...'
                : $request->content;
            
            if ($request->comment_id) {
                // Reply to comment
                $parentComment = Comment::with('user')->find($request->comment_id);
                
                if ($parentComment && $parentComment->user_id != Auth::id()) {
                    // Notification to parent comment owner
                    $notification = Notification::create([
                        'receiver_id' => $parentComment->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'comment_reply',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $this->sendFirebaseNotification(
                        $parentComment->user_id,
                        '💬 Reply from ' . $commentAuthor->name,
                        "{$commentAuthor->name} replied: \"{$commentPreview}\"",
                        $notification->id,
                        $post->slug,
                        'comment_reply',
                        $parentComment->id
                    );
                    
                    Log::info('Reply notification created', [
                        'notification_id' => $notification->id
                    ]);
                }
                
                // Notify post owner (if different)
                if ($post->user_id != Auth::id() && $post->user_id != $parentComment->user_id) {
                    $notification = Notification::create([
                        'receiver_id' => $post->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'post_reply',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $this->sendFirebaseNotification(
                        $post->user_id,
                        '💬 New Reply from ' . $commentAuthor->name,
                        "{$commentAuthor->name} replied on your post",
                        $notification->id,
                        $post->slug,
                        'post_reply',
                        $comment->id
                    );
                }
                
            } else {
                // New main comment
                if ($post && $post->user_id != Auth::id()) {
                    $notification = Notification::create([
                        'receiver_id' => $post->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'comment',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $this->sendFirebaseNotification(
                        $post->user_id,
                        '💬 New Comment from ' . $commentAuthor->name,
                        "{$commentAuthor->name} commented: \"{$commentPreview}\"",
                        $notification->id,
                        $post->slug,
                        'comment',
                        $comment->id
                    );
                    
                    Log::info('Comment notification created', [
                        'notification_id' => $notification->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        // Return response
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
        
        return back()->with('success', 'Comment posted successfully!');
    }
    
    /**
     * Send Firebase notification
     */
    private function sendFirebaseNotification($userId, $title, $body, $notificationId, $slug, $notificationType, $commentId = null)
    {
        try {
            $user = \App\Models\User::with('fcmTokens')->find($userId);
            
            if (!$user || $user->fcmTokens->isEmpty()) {
                return false;
            }
            
            $serviceAccountFile = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            
            if (!file_exists($serviceAccountFile)) {
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
                            'comment_id' => $commentId ? (string)$commentId : '',
                            'slug' => $slug ?? '',
                            'type' => 'browser_notification',
                            'notification_type' => $notificationType,
                            'sender_id' => $sender ? (string)$sender->id : '',
                            'sender_name' => $sender ? $sender->name : '',
                            'sender_image' => $sender && $sender->image ? url('profile-image/' . $sender->image) : '',
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);
                    
                    $messaging->send($messageBuilder);
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