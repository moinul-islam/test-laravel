<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

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
        
        // ✅ Send Notification
        try {
            $commentAuthor = Auth::user();
            
            if ($request->comment_id) {
                // এটি একটি Reply - parent comment এর author কে notification পাঠান
                $parentComment = Comment::find($request->comment_id);
                
                // নিজের comment এ reply করলে notification পাঠাবেন না
                if ($parentComment && $parentComment->user_id != Auth::id()) {
                    // Truncate comment for notification
                    $commentPreview = strlen($request->content) > 50 
                        ? substr($request->content, 0, 50) . '...'
                        : $request->content;
                    
                    $this->sendBrowserNotification(
                        $parentComment->user_id,
                        '💬 Reply from ' . $commentAuthor->name,
                        "{$commentAuthor->name} replied to your comment: \"{$commentPreview}\"",
                        $post->id,
                        url('/post/' . $post->slug),
                        'comment_reply',
                        $post->slug
                    );
                    
                    Log::info('Reply notification sent', [
                        'parent_comment_author' => $parentComment->user_id,
                        'replier' => Auth::id(),
                        'post_id' => $post->id
                    ]);
                }
            } else {
                // এটি একটি নতুন Comment - post owner কে notification পাঠান
                
                // নিজের post এ comment করলে notification পাঠাবেন না
                if ($post && $post->user_id != Auth::id()) {
                    // Truncate comment for notification
                    $commentPreview = strlen($request->content) > 50 
                        ? substr($request->content, 0, 50) . '...'
                        : $request->content;
                    
                    $this->sendBrowserNotification(
                        $post->user_id,
                        '💬 New Comment from ' . $commentAuthor->name,
                        "{$commentAuthor->name} commented on your post: \"{$commentPreview}\"",
                        $post->id,
                        url('/post/' . $post->slug),
                        'comment',
                        $post->slug
                    );
                    
                    Log::info('Comment notification sent', [
                        'post_owner' => $post->user_id,
                        'commenter' => Auth::id(),
                        'post_id' => $post->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send comment notification', [
                'error' => $e->getMessage(),
                'post_id' => $post->id
            ]);
        }
        
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
    
    /**
     * Send Firebase notification
     */
    private function sendBrowserNotification($userId, $title, $body, $sourceId = null, $customLink = null, $notificationType = 'comment', $slug = null)
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