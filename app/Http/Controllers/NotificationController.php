<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user (Web)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get grouped notifications
        $groupedNotifications = $this->getGroupedNotifications($user->id);
        
        // Get unseen count
        $unseenCount = Notification::where('receiver_id', $user->id)
            ->where('seen', false)
            ->count();
        
        return view('notifications.index', compact('groupedNotifications', 'unseenCount'));
    }
    
    /**
     * Get grouped notifications
     */
    private function getGroupedNotifications($userId)
    {
        $notifications = Notification::with(['sender', 'post', 'comment'])
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
        
        $updated = Notification::whereIn('id', $request->notification_ids)
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
     * ✅ Mark notification as seen from APP (API - আপনার route)
     */
    public function updateSeen(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'source_id' => 'required',
            'type' => 'required|string',
        ]);
    
        $userId = $request->user_id;
        $sourceId = $request->source_id;
        $type = $request->type;
    
        $query = Notification::where('receiver_id', $userId);
    
        if ($type === 'post_like' || $type === 'comment') {
            $query->where('post_id', $sourceId);
        } elseif ($type === 'comment_reply') {
            $query->where('comment_id', $sourceId);
        } elseif ($type === 'follow') {
            $query->where('sender_id', $sourceId);
        }
    
        $notification = $query->first();
    
        if ($notification) {
            $notification->seen = 1;
            $notification->seen_at = now();
            $notification->save();
    
            return response()->json([
                'message' => 'Notification marked as seen',
                'notification' => $notification
            ]);
        }
    
        return response()->json([
            'message' => 'No matching notification found',
        ], 404);
    }
    
    
    /**
     * Mark all notifications as seen
     */
    public function markAllAsSeen()
    {
        $updated = Notification::where('receiver_id', Auth::id())
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
        $count = Notification::where('receiver_id', Auth::id())
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
        
        $deleted = Notification::whereIn('id', $request->notification_ids)
            ->where('receiver_id', Auth::id())
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notifications deleted',
            'count' => $deleted
        ]);
    }
}