<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Comment;
use App\Models\Notification as NotificationModel; // ✅ Alias করলাম
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification; // ✅ Alias

class NotificationController extends Controller
{
    protected $messaging;
    
    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Get notifications for authenticated user (Web)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get grouped notifications
        $groupedNotifications = $this->getGroupedNotifications($user->id);
        
        // Get unseen count
        $unseenCount = NotificationModel::where('receiver_id', $user->id)
            ->where('seen', false)
            ->count();
        
        return view('notifications.index', compact('groupedNotifications', 'unseenCount'));
    }
    
    /**
     * Get grouped notifications
     */
    private function getGroupedNotifications($userId)
    {
        $notifications = NotificationModel::with(['sender', 'post', 'comment'])
            ->where('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $grouped = [];
        
        foreach ($notifications as $notification) {
            $key = $notification->type . '_' . 
                   ($notification->post_id ?? '') . '_' . 
                   ($notification->comment_id ?? '');
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'type' => $notification->type,
                    'post_id' => $notification->post_id,
                    'comment_id' => $notification->comment_id,
                    'post' => $notification->post,
                    'comment' => $notification->comment,
                    'senders' => collect([]),
                    'count' => 0,
                    'latest_time' => $notification->created_at,
                    'all_seen' => true,
                    'notification_ids' => []
                ];
            }
            
            $grouped[$key]['senders']->push($notification->sender);
            $grouped[$key]['count']++;
            $grouped[$key]['notification_ids'][] = $notification->id;
            
            if (!$notification->seen) {
                $grouped[$key]['all_seen'] = false;
            }
        }
        
        return collect($grouped)->values();
    }
    
    /**
     * Mark notification as seen from WEB
     */
    public function markAsSeen(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);
        
        $updated = NotificationModel::whereIn('id', $request->notification_ids)
            ->where('receiver_id', Auth::id())
            ->update([
                'seen' => true,
                'seen_at' => now()
            ]);
        
        Log::info('Notifications marked as seen (web)', [
            'user_id' => Auth::id(),
            'count' => $updated
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as seen',
            'count' => $updated
        ]);
    }
    
    /**
     * ✅ Mark notification as seen from APP (API - আপনার পুরানো method)
     */
    public function updateSeen(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'source_id' => 'required',
            'type' => 'nullable|string',
            'seen' => 'nullable|boolean',
        ]);

        $userId = $request->user_id;
        $sourceId = $request->source_id;
        $seen = $request->input('seen', true);
        $type = $request->type;

        // Query builder - receiver_id ব্যবহার করছি
        $query = NotificationModel::where('receiver_id', $userId);

        // Type অনুযায়ী source_id match করো
        if ($type === 'post_like') {
            $query->where('post_id', $sourceId)->where('type', 'post_like');
        } elseif ($type === 'comment') {
            $query->where('comment_id', $sourceId)->where('type', 'comment');
        } elseif ($type === 'comment_reply') {
            $query->where('comment_id', $sourceId)->where('type', 'comment_reply');
        } elseif ($type === 'comment_like') {
            $query->where('comment_id', $sourceId)->where('type', 'comment_like');
        } elseif ($type === 'post_reply') {
            $query->where('comment_id', $sourceId)->where('type', 'post_reply');
        } else {
            // Type না থাকলে post_id বা comment_id যেকোনো একটা match করো
            $query->where(function($q) use ($sourceId) {
                $q->where('post_id', $sourceId)
                  ->orWhere('comment_id', $sourceId);
            });
        }

        // সব matching notifications update করো
        $updated = $query->update([
            'seen' => $seen,
            'seen_at' => $seen ? now() : null
        ]);

        if ($updated > 0) {
            Log::info('Notifications marked as seen (app)', [
                'user_id' => $userId,
                'source_id' => $sourceId,
                'type' => $type,
                'count' => $updated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as seen',
                'count' => $updated
            ]);
        }

        Log::warning('No matching notification found', [
            'user_id' => $userId,
            'source_id' => $sourceId,
            'type' => $type
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No matching notification found'
        ], 404);
    }
    
    /**
     * Mark all notifications as seen
     */
    public function markAllAsSeen()
    {
        $updated = NotificationModel::where('receiver_id', Auth::id())
            ->where('seen', false)
            ->update([
                'seen' => true,
                'seen_at' => now()
            ]);
        
        Log::info('All notifications marked as seen', [
            'user_id' => Auth::id(),
            'count' => $updated
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as seen',
            'count' => $updated
        ]);
    }
    
    /**
     * Get unseen notification count
     */
    public function getUnseenCount()
    {
        $count = NotificationModel::where('receiver_id', Auth::id())
            ->where('seen', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    
    /**
     * Delete notification group
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array'
        ]);
        
        $deleted = NotificationModel::whereIn('id', $request->notification_ids)
            ->where('receiver_id', Auth::id())
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notifications deleted',
            'count' => $deleted
        ]);
    }

    // ============================================
    // ✅ আপনার পুরানো Firebase Methods (intact)
    // ============================================

    public function sendNotificationUserWise(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !$user->firebase_token) {
            return response()->json(['success' => false, 'message' => 'User not found or token missing'], 404);
        }

        $serviceAccountFile = base_path("einfo-e95ba-firebase-adminsdk-fbsvc-3566bbd9bf.json");

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountFile);

        $messaging = $factory->createMessaging();
        
        $message = CloudMessage::withTarget('token', $user->firebase_token)
            ->withNotification(FirebaseNotification::create($request->title, $request->body))
            ->withData([
                'user_id' => '12345',
                'user_image' => 'https://einfo.site/upload/img/1750773514.jpg',
                'action' => 'open_screen',
                'web_url' => 'https://einfo.site/post/6811df35ebb90?highlight=reply&reply_id=493',
                'screen_name' => 'profile',
                'timestamp' => date('Y-m-d H:i:s'),
                'custom_key' => 'custom_value'
            ]);

        try {
            $response = $messaging->send($message);
            return response()->json([
                'success' => true,
                'message' => 'Successfully sent notification',
                'response' => $response
            ]);
        } catch (MessagingException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registerToken(Request $request)
    {
        $user = User::where('username', $request->user_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->firebase_token = $request->firebase_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Token saved successfully'
        ]);
    }

    public function sendNotification(Request $request)
    {
        $deviceToken = $request->device_token;

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'message' => 'Device token is required'
            ], 422);
        }

        $title = $request->title ?? 'Test Notification';
        $body = $request->body ?? 'This is a test notification message';

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(FirebaseNotification::create($title, $body));

        try {
            $response = $this->messaging->send($message);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendTopicNotification(Request $request)
    {
        $isAuthenticated = auth()->check();
        
        $notificationData = [
            'title' => $request->title ?? 'New Notification',
            'body' => $request->body ?? 'You have a new notification',
            'slug' => $request->slug ?? '',
            'is_authenticated' => $isAuthenticated,
            'auth_url' => $isAuthenticated ? route('user.dashboard') : route('login'),
        ];
        
        $message = CloudMessage::withTarget('topic', $request->topic ?? 'all_users')
            ->withNotification(FirebaseNotification::create($request->title ?? 'New Notification', $request->body ?? 'You have a new notification'))
            ->withData($notificationData);
        
        try {
            $response = $this->messaging->send($message);
            return response()->json([
                'success' => true,
                'message' => 'Topic notification sent successfully',
                'data' => $notificationData,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send topic notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}