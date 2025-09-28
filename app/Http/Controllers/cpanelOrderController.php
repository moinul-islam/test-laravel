<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class OrderController extends Controller
{




// OrderController.php এ এই methods update করুন:

public function deliveryPage()
{
    $userId = auth()->id();
    
    // Query build করি - এখন delivered এবং cancelled orders ও include করব
    $orders = Order::where(function($query) use ($userId) {
            // Confirmed orders যেগুলো কেউ accept করেনি (সবাই দেখবে)
            $query->where('status', 'confirmed')
                  ->whereNull('delivery_person_id');
        })
        ->orWhere(function($query) use ($userId) {
            // অথবা যে orders এই delivery person accept করেছে (processing/shipped/delivered/cancelled)
            $query->where('delivery_person_id', $userId)
                  ->whereIn('status', ['processing', 'shipped', 'delivered', 'cancelled']);
        })
        ->with(['user', 'vendor'])
        ->latest()
        ->paginate(10);

    return view('frontend.delivery', compact('orders'));
}

public function acceptForDelivery(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Check if order is confirmed and not taken by someone else
    if ($order->status != 'confirmed') {
        return response()->json([
            'success' => false,
            'message' => 'This order is not available for acceptance'
        ], 400);
    }
    
    // Check if already taken
    if ($order->delivery_person_id !== null) {
        return response()->json([
            'success' => false,
            'message' => 'This order has already been accepted by another delivery person'
        ], 400);
    }

    try {
        // Update order status to processing and assign delivery person
        $order->update([
            'status' => 'processing',
            'delivery_person_id' => auth()->id()
        ]);

        // Send notification to customer
        $customer = \App\Models\User::find($order->user_id);
        $deliveryPerson = auth()->user();
        
        if ($customer) {
            $this->sendBrowserNotification(
                $order->user_id,
                'Order Being Processed!',
                "Your order #$order->id has been picked up for delivery by {$deliveryPerson->name}! 📦",
                $order->id
            );
        }

        // Send notification to vendor
        $vendor = \App\Models\User::find($order->vendor_id);
        if ($vendor) {
            $this->sendBrowserNotification(
                $order->vendor_id,
                'Order Picked for Delivery',
                "Order #$order->id has been accepted by delivery personnel ({$deliveryPerson->name}).",
                $order->id
            );
        }

        \Log::info('Order accepted for delivery', [
            'order_id' => $order->id,
            'delivery_person' => auth()->id(),
            'previous_status' => 'confirmed',
            'new_status' => 'processing'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully!'
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to accept order for delivery', [
            'order_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to accept order. Please try again.'
        ], 500);
    }
}

// Mark as shipped (vendor করবে product ready হলে)
public function markAsShipped(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Only vendor can mark as shipped
    if ($order->vendor_id != auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    if ($order->status !== 'processing') {
        return response()->json([
            'success' => false,
            'message' => 'Only processing orders can be marked as shipped'
        ], 400);
    }

    $order->update(['status' => 'shipped']);

    // Notify delivery person
    if ($order->delivery_person_id) {
        $this->sendBrowserNotification(
            $order->delivery_person_id,
            'Order Ready for Delivery',
            "Order #$order->id is ready for delivery! Please deliver to customer.",
            $order->id
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Order marked as shipped!'
    ]);
}

public function completeDelivery(Request $request, $id)
{
    try {
        $order = Order::findOrFail($id);
        
        // Debug করার জন্য log করুন
        \Log::info('Complete Delivery Attempt', [
            'order_id' => $order->id,
            'delivery_person_id' => $order->delivery_person_id,
            'auth_id' => auth()->id(),
            'auth_user' => auth()->user() ? auth()->user()->id : null,
        ]);
        
        // Check if user is logged in
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Type cast করে compare করুন
        $deliveryPersonId = $order->delivery_person_id ? (int)$order->delivery_person_id : null;
        $currentUserId = (int)auth()->id();
        
        if ($deliveryPersonId !== $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this order',
                'debug' => [
                    'order_delivery_person' => $deliveryPersonId,
                    'current_user' => $currentUserId
                ]
            ], 403);
        }
        
        if ($order->status !== 'shipped') {
            return response()->json([
                'success' => false,
                'message' => 'Only shipped orders can be marked as delivered'
            ], 400);
        }
        
        $order->update([
            'status' => 'delivered',
            
        ]);
        
        // Notifications...
        
        return response()->json([
            'success' => true,
            'message' => 'Order marked as delivered successfully!'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Complete Delivery Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}






public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        'cancel_reason' => 'nullable|string|max:255'
    ]);

    $order = Order::findOrFail($id);
    
    // Only vendor can update order status
    if ($order->vendor_id != auth()->id()) {
        abort(403);
    }

    $oldStatus = $order->status;
    
    // If vendor is cancelling the order, add the reason to post_ids
    if ($request->status === 'cancelled' && $request->has('cancel_reason')) {
        $postIds = $order->post_ids;
        
        if (is_array($postIds)) {
            $updatedPostIds = array_map(function($item) use ($request) {
                $item['cancel_reason'] = $request->cancel_reason;
                return $item;
            }, $postIds);
        } else {
            $updatedPostIds = [
                [
                    'post_id' => null,
                    'quantity' => 0,
                    'cancel_reason' => $request->cancel_reason
                ]
            ];
        }
        
        $order->update([
            'status' => $request->status,
            'post_ids' => $updatedPostIds
        ]);
    } else {
        // Regular status update
        $order->update(['status' => $request->status]);
    }

    // Send status update notification to customer
    $customer = \App\Models\User::find($order->user_id);
    $vendor = auth()->user();
    
    if ($customer) {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed by the vendor! 🎉',
            'processing' => 'Your order is being processed. 📦',
            'shipped' => 'Your order has been shipped! 🚚',
            'delivered' => 'Your order has been delivered! ✅',
            'cancelled' => 'Your order has been cancelled. ❌'
        ];

        if (isset($statusMessages[$request->status])) {
            $this->sendBrowserNotification(
                $order->user_id,
                'Order Status Updated',
                $statusMessages[$request->status] . " Order ID: {$order->id}",
                $order->id
            );
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Order status updated successfully!'
    ]);
}

public function cancelOrder(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:cancelled',
        'cancel_reason' => 'required|string|max:255'
    ]);

    $order = Order::findOrFail($id);
    
    // Only customer can cancel their own order and only if it's pending
    if ($order->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to cancel this order'
        ], 403);
    }

    if ($order->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Order cannot be cancelled as it is already ' . $order->status
        ], 400);
    }

    try {
        \Log::info('Cancelling order with reason', [
            'order_id' => $id,
            'cancel_reason' => $request->cancel_reason,
            'user_id' => auth()->id()
        ]);

        // Add cancel reason to each item in post_ids
        $postIds = $order->post_ids;
        
        if (is_array($postIds)) {
            $updatedPostIds = array_map(function($item) use ($request) {
                $item['cancel_reason'] = $request->cancel_reason;
                return $item;
            }, $postIds);
        } else {
            // If post_ids is somehow not an array, create a basic structure
            $updatedPostIds = [
                [
                    'post_id' => null,
                    'quantity' => 0,
                    'cancel_reason' => $request->cancel_reason
                ]
            ];
        }

        // Update order status and post_ids with cancel reason
        $order->update([
            'status' => 'cancelled',
            'post_ids' => $updatedPostIds
        ]);

        // Send notification to vendor about cancellation
        $vendor = \App\Models\User::find($order->vendor_id);
        $customer = auth()->user();
        
        if ($vendor) {
            $this->sendBrowserNotification(
                $order->vendor_id,
                'Order Cancelled',
                "Order #{$order->id} cancelled by {$customer->name}. Reason: {$request->cancel_reason}",
                $order->id
            );
            \Log::info('Cancellation notification sent to vendor', [
                'vendor_id' => $order->vendor_id, 
                'order_id' => $order->id
            ]);
        }

        \Log::info('Order cancelled successfully', [
            'order_id' => $id,
            'updated_post_ids' => $updatedPostIds
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully!',
            'cancel_reason' => $request->cancel_reason
        ]);

    } catch (\Exception $e) {
        \Log::error('Order cancellation failed:', [
            'order_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel order.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function store(Request $request)
    {
        // Debug: Log incoming request
        \Log::info('Order Request Data:', $request->all());
        
        $request->validate([
            'phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'total_amount' => 'required|numeric|min:0',
            'cart_items' => 'required|array|min:1'
        ]);

        try {
            \Log::info('Validation passed, processing order...');
            
            // Group cart items by vendor
            $vendorGroups = collect($request->cart_items)->groupBy(function ($item) {
                $post = Post::find($item['id']);
                \Log::info('Post found for ID ' . $item['id'] . ': ' . ($post ? 'Yes' : 'No'));
                return $post ? $post->user_id : null;
            });

            \Log::info('Vendor Groups:', $vendorGroups->toArray());

            $createdOrders = [];

            foreach ($vendorGroups as $vendorId => $items) {
                if (!$vendorId) {
                    \Log::warning('Skipping vendor group with null vendor_id');
                    continue;
                }

                \Log::info('Processing vendor ID: ' . $vendorId);

                // Calculate total for this vendor
                $vendorTotal = $items->sum(function ($item) {
                    return $item['price'] * $item['quantity'];
                });

                // Prepare post_ids with quantities and service_time
                $postIds = $items->map(function ($item) {
                    $data = [
                        'post_id' => (int) $item['id'],
                        'quantity' => (int) $item['quantity']
                    ];
                   
                    // Add service_time if exists
                    if (isset($item['service_time'])) {
                        $data['service_time'] = $item['service_time'];
                    }
                   
                    return $data;
                })->toArray();

                \Log::info('Order data to be created:', [
                    'user_id' => auth()->id(),
                    'vendor_id' => $vendorId,
                    'phone' => $request->phone,
                    'shipping_address' => $request->shipping_address,
                    'total_amount' => $vendorTotal,
                    'status' => 'pending',
                    'post_ids' => $postIds
                ]);

                // Create order for this vendor
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'vendor_id' => $vendorId,
                    'phone' => $request->phone,
                    'shipping_address' => $request->shipping_address,
                    'total_amount' => $vendorTotal,
                    'status' => 'pending',
                    'post_ids' => $postIds
                ]);

                \Log::info('Order created with ID: ' . $order->id);

                // NEW: Send browser notification to vendor
                $vendor = \App\Models\User::find($vendorId);
                $customer = auth()->user();
                
                if ($vendor) {
                    $this->sendBrowserNotification(
                        $vendorId,
                        'New Order Received!',
                        "Order from {$customer->name}. Amount: {$vendorTotal}. Total items: " . count($postIds),
                        $order->id
                    );
                    \Log::info('Browser notification sent to vendor', ['vendor_id' => $vendorId, 'order_id' => $order->id]);
                }

               
            }

            \Log::info('All orders created successfully:', $createdOrders);

            return response()->json([
                'success' => true,
                'message' => 'Orders placed successfully!',
                'orders' => $createdOrders,
                'total_orders' => count($createdOrders)
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Buy page - যারা order দিয়েছে তারা দেখবে
    public function buyPage()
    {
        $user = Auth::user();
        
        // User যে orders দিয়েছে সেগুলো দেখাবে
        $orders = Order::where('user_id', $user->id)
            ->with(['vendor'])
            ->latest()
            ->paginate(10);

        return view('frontend.buy', compact('orders'));
    }

    // Sell page - যাদের কাছে order এসেছে তারা দেখবে
    public function sellPage()
    {
        $user = Auth::user();
        session(['vendor_orders_seen_' . $user->id => now()]);
        // User এর কাছে যে orders এসেছে সেগুলো দেখাবে
        $orders = Order::where('vendor_id', $user->id)
            ->with(['user'])
            ->latest()
            ->paginate(10);

        return view('frontend.sell', compact('orders'));
        
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with(['vendor'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'vendor'])->findOrFail($id);
        
        // Check if user is authorized to view this order
        if ($order->user_id !== auth()->id() && $order->vendor_id !== auth()->id()) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

   




    private function sendBrowserNotification($userId, $title, $body, $orderId = null)
{
    try {
        Log::info('Starting notification process', ['user_id' => $userId]);
        
        $user = \App\Models\User::find($userId);
        
        if (!$user || !$user->fcm_token) {
            Log::info('No FCM token found for user', ['user_id' => $userId, 'user_exists' => !!$user]);
            return false;
        }

        Log::info('User and token found', ['user_id' => $userId, 'token_length' => strlen($user->fcm_token)]);

        // Initialize Firebase Admin SDK
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/' . env('FIREBASE_CREDENTIALS')));
        
        Log::info('Firebase factory created');
        
        $messaging = $factory->createMessaging();
        
        Log::info('Firebase messaging created');

        // Create message
        $message = CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification(Notification::create($title, $body));

        Log::info('Message created, attempting to send');

        // Send the message
        $result = $messaging->send($message);
        
        Log::info('Firebase messaging response', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'firebase_response' => $result,
            'token_used' => $user->fcm_token
        ]);
        
        return true;

    } catch (\Exception $e) {
        Log::error('Firebase notification error', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}


    
}