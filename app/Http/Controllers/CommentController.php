<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class CommentController extends Controller
{

    public function delete(Request $request)
    {
        try {
            $comment = Comment::findOrFail($request->comment_id);
            
            if (Auth::id() != $comment->user_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $postId = $comment->post_id;
            $comment->delete();
            
            $post = Post::find($postId);
            $totalComments = $post ? $post->allComments()->count() : 0;
            
            return response()->json([
                'success' => true,
                'total_comments_count' => $totalComments
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    public function commentStore(Request $request)
    {
        // Validate the request
        $request->validate([
            'content' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
            'post_id' => 'required|exists:posts,id',
            'comment_id' => 'nullable|exists:comments,id'
        ]);
        
        // ✅ Debug log
        Log::info('Comment store request', [
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'comment_id' => $request->comment_id,
            'content' => substr($request->content, 0, 50)
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
        
        // Get post info
        $post = Post::with('user')->find($request->post_id);
        $totalCommentsCount = $post ? $post->allComments()->count() : 0;
        
        // ✅ Send Notification - Fixed Logic
        try {
            $commentAuthor = Auth::user();
            
            if ($request->comment_id) {
                // এটি একটি Reply
                $parentComment = Comment::with('user')->find($request->comment_id);
                
                Log::info('Reply detected', [
                    'new_comment_id' => $comment->id,
                    'parent_comment_id' => $request->comment_id,
                    'parent_comment_found' => !!$parentComment,
                    'parent_user_id' => $parentComment ? $parentComment->user_id : null,
                    'current_user_id' => Auth::id()
                ]);
                
                if ($parentComment && $parentComment->user_id != Auth::id()) {
                    // ✅ URL with comment anchor
                    $commentUrl = url('/post/' . $post->slug . '#comment-' . $parentComment->id);
                    
                    $commentPreview = strlen($request->content) > 50 
                        ? substr($request->content, 0, 50) . '...'
                        : $request->content;
                    
                    // ✅ Database notification
                    $notification = NotificationModel::create([
                        'receiver_id' => $parentComment->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'comment_reply',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $notificationSent = $this->sendBrowserNotification(
                        $parentComment->user_id,
                        '💬 Reply from ' . $commentAuthor->name,
                        "{$commentAuthor->name} replied: \"{$commentPreview}\"",
                        $post->id,
                        $commentUrl, // ✅ Direct comment link
                        'comment_reply',
                        $post->slug,
                        $parentComment->id // ✅ Parent comment ID
                    );
                    
                    Log::info('Reply notification sent', [
                        'success' => $notificationSent,
                        'to_user' => $parentComment->user_id,
                        'comment_url' => $commentUrl
                    ]);
                } else {
                    Log::info('Reply notification skipped - self reply or parent not found');
                }
                
                // ✅ BONUS: Post owner কেও জানান (যদি post owner ভিন্ন হয়)
                if ($post->user_id != Auth::id() && $post->user_id != $parentComment->user_id) {
                    $commentUrl = url('/post/' . $post->slug . '#comment-' . $comment->id);
                    
                    $notification = NotificationModel::create([
                        'receiver_id' => $post->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'post_reply',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $this->sendBrowserNotification(
                        $post->user_id,
                        '💬 New Reply from ' . $commentAuthor->name,
                        "{$commentAuthor->name} replied on your post",
                        $post->id,
                        $commentUrl,
                        'post_reply',
                        $post->slug,
                        $comment->id
                    );
                    
                    Log::info('Post owner notified about reply');
                }
                
            } else {
                // নতুন Comment (main comment)
                if ($post && $post->user_id != Auth::id()) {
                    // ✅ URL with comment anchor
                    $commentUrl = url('/post/' . $post->slug . '#comment-' . $comment->id);
                    
                    $commentPreview = strlen($request->content) > 50 
                        ? substr($request->content, 0, 50) . '...'
                        : $request->content;
                    
                    // ✅ Database notification
                    $notification = NotificationModel::create([
                        'receiver_id' => $post->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'comment',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'seen' => false
                    ]);
                    
                    $notificationSent = $this->sendBrowserNotification(
                        $post->user_id,
                        '💬 New Comment from ' . $commentAuthor->name,
                        "{$commentAuthor->name} commented: \"{$commentPreview}\"",
                        $post->id,
                        $commentUrl, // ✅ Direct comment link
                        'comment',
                        $post->slug,
                        $comment->id
                    );
                    
                    Log::info('Comment notification sent', [
                        'success' => $notificationSent,
                        'to_user' => $post->user_id,
                        'comment_url' => $commentUrl
                    ]);
                } else {
                    Log::info('Comment notification skipped - own post');
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Return JSON response
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
    private function sendBrowserNotification($userId, $title, $body, $sourceId = null, $customLink = null, $notificationType = 'comment', $slug = null, $commentId = null)
    {
        try {
            $user = \App\Models\User::with('fcmTokens')->find($userId);
            
            if (!$user) {
                Log::warning('User not found', ['user_id' => $userId]);
                return false;
            }
            
            if ($user->fcmTokens->isEmpty()) {
                Log::info('No FCM tokens', [
                    'user_id' => $userId,
                    'user_name' => $user->name
                ]);
                return false;
            }
            
            $serviceAccountFile = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            
            if (!file_exists($serviceAccountFile)) {
                Log::error('Firebase credentials not found', ['path' => $serviceAccountFile]);
                return false;
            }
            
            $factory = (new Factory)->withServiceAccount($serviceAccountFile);
            $messaging = $factory->createMessaging();
            
            $timestamp = now()->timestamp;
            $uniqueId = "{$notificationType}-{$sourceId}-{$timestamp}";
            
            // ✅ Use custom link if provided (with comment anchor)
            $webUrl = $customLink ?? ($slug ? url("/post/{$slug}") : url('/'));
            $deepLink = $webUrl;
            
            $action = 'open_post';
            $screenName = 'post_detail';
            
            $sender = Auth::user();
            
            $successCount = 0;
            
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
                            'comment_id' => $commentId ? (string)$commentId : '', // ✅ Comment ID added
                            'slug' => $slug ?? '',
                            'type' => 'browser_notification',
                            'seen' => 'false',
                            'notification_type' => $notificationType,
                            'sender_id' => $sender ? (string)$sender->id : '',
                            'sender_name' => $sender ? $sender->name : '',
                            'sender_image' => $sender && $sender->image ? url('profile-image/' . $sender->image) : '',
                            'action' => $action,
                            'web_url' => $webUrl, // ✅ URL with anchor
                            'deep_link' => $deepLink,
                            'click_action' => $webUrl,
                            'screen_name' => $screenName,
                            'timestamp' => date('Y-m-d H:i:s'),
                            'notification_id' => $uniqueId
                        ]);
                    
                    $result = $messaging->send($messageBuilder);
                    $successCount++;
                    
                    Log::info('Notification sent', [
                        'user_id' => $userId,
                        'type' => $notificationType,
                        'url' => $webUrl
                    ]);
                } catch (\Exception $ex) {
                    Log::warning('Token send failed', [
                        'user_id' => $userId,
                        'error' => $ex->getMessage()
                    ]);
                }
            }
            
            return $successCount > 0;
            
        } catch (\Exception $e) {
            Log::error('Notification error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}