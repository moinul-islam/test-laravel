<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

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

        // Query builder
        $query = Notification::where('receiver_id', $userId);

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

    /**
     * Show admin send-notification form
     */
    public function showAdminSendForm()
    {
        $this->authorizeAdmin();

        $countries = Country::orderBy('name')->get();

        return view('frontend.send-notification', compact('countries'));
    }

    /**
     * Handle admin notification sending (FCM broadcast)
     */
    public function sendAdminNotification(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'link' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'target_type' => 'required|in:international,country,city',
            'country_id' => 'required_if:target_type,country,city|exists:countries,id',
            'city_id' => 'required_if:target_type,city|exists:cities,id',
        ]);

        // Handle optional image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $uploadPath = public_path('uploads/notifications');

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $request->image->move($uploadPath, $imageName);
            $imageUrl = url('uploads/notifications/' . $imageName);
        }

        // Determine target users
        $userQuery = User::with('fcmTokens')->whereHas('fcmTokens');

        if ($validated['target_type'] === 'country') {
            $userQuery->where('country_id', $validated['country_id']);
        } elseif ($validated['target_type'] === 'city') {
            $userQuery->where('city_id', $validated['city_id']);
        }

        $users = $userQuery->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'No users found for the selected target.')->withInput();
        }

        $sender = Auth::user();
        $totalTokens = 0;
        $sentCount = 0;

        try {
            $serviceAccountFile = storage_path('app/' . env('FIREBASE_CREDENTIALS'));
            $factory = (new Factory)->withServiceAccount($serviceAccountFile);
            $messaging = $factory->createMessaging();

            $webUrl = $validated['link'] ?: url('/');
            $timestamp = now()->timestamp;

            foreach ($users as $user) {
                foreach ($user->fcmTokens as $tokenModel) {
                    $token = $tokenModel->fcm_token;
                    $totalTokens++;

                    try {
                        $uniqueId = 'admin-notification-' . $timestamp . '-' . $user->id;

                        $message = CloudMessage::withTarget('token', $token)
                            ->withNotification([
                                'title' => $validated['title'],
                                'body' => $validated['description'],
                                'image' => $imageUrl ?? ($sender && $sender->image ? url($sender->image) : ''),
                            ])
                            ->withData([
                                'type' => 'browser_notification',
                                'notification_type' => 'admin_broadcast',
                                'user_id' => (string)$user->id,
                                'sender_id' => $sender ? (string)$sender->id : '',
                                'sender_name' => $sender ? $sender->name : '',
                                'sender_image' => $sender && $sender->image ? url($sender->image) : '',
                                'action' => 'open_link',
                                'screen_name' => 'webview',
                                'web_url' => $webUrl,
                                'deep_link' => $webUrl,
                                'click_action' => $webUrl,
                                'title' => $validated['title'],
                                'body' => $validated['description'],
                                'timestamp' => now()->toDateTimeString(),
                                'notification_id' => $uniqueId,
                            ]);

                        $messaging->send($message);
                        $sentCount++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to send admin broadcast notification to token', [
                            'user_id' => $user->id,
                            'token' => $token,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            Log::info('Admin broadcast notification sent', [
                'admin_id' => $sender ? $sender->id : null,
                'target_type' => $validated['target_type'],
                'country_id' => $validated['country_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'total_tokens' => $totalTokens,
                'sent_count' => $sentCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin broadcast notification failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send notifications. Please check Firebase configuration.')->withInput();
        }

        return back()->with('success', "Notification sent to {$sentCount} devices.");
    }

    /**
     * Simple admin role check helper
     */
    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user || !in_array(strtolower($user->role ?? ''), ['admin'])) {
            abort(403, 'Unauthorized');
        }
    }
}