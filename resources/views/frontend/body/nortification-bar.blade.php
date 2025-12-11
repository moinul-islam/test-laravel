@php
    $userId = Auth::id();
    $hasPlacedOrders = \App\Models\Order::where('user_id', $userId)->exists();
    $hasReceivedOrders = \App\Models\Order::where('vendor_id', $userId)->exists();
    
    $lastSeenKey = 'vendor_orders_seen_' . $userId;
    $lastSeen = session($lastSeenKey);
    
    $query = \App\Models\Order::where('vendor_id', $userId)
        ->where('status', 'pending');
        
    if ($lastSeen) {
        $query->where('created_at', '>', $lastSeen);
    }
    
    $newOrdersCount = $query->count();
@endphp

@php
    $userId = Auth::id();
    $hasPlacedOrders = \App\Models\Order::where('user_id', $userId)->exists();
    $hasReceivedOrders = \App\Models\Order::where('vendor_id', $userId)->exists();
    
    $lastSeenKey = 'vendor_orders_seen_' . $userId;
    $lastSeen = session($lastSeenKey);
    
    $query = \App\Models\Order::where('vendor_id', $userId)
        ->where('status', 'pending');
        
    if ($lastSeen) {
        $query->where('created_at', '>', $lastSeen);
    }
    
    $newOrdersCount = $query->count();
    
    // Notification count যোগ করুন
    $unseenNotificationsCount = \App\Models\Notification::where('receiver_id', $userId)
        ->where('seen', false)
        ->count();
    
    // মোট unseen count
    $totalUnseenCount = $newOrdersCount + $unseenNotificationsCount;
@endphp    

@php
    $showBuy = $hasPlacedOrders ?? false;
    $showSell = $hasReceivedOrders ?? false;
@endphp

@if($showBuy || $showSell)
<div class="mb-4 d-flex justify-content-center">
    <div class="btn-group" role="group" aria-label="Notification Tabs">
        <a href="{{ url('/notifications') }}"
            class="btn {{ request()->is('notifications') ? 'btn-primary' : 'btn-outline-primary' }}">
            Post
        </a>
        @if($showBuy)
            <a href="{{ route('buy') }}"
                class="btn {{ request()->is('buy') ? 'btn-info' : 'btn-outline-info' }}">
                Buy
            </a>
        @endif
        @if($showSell)
            <a href="{{ route('sell') }}"
                class="btn d-flex align-items-center {{ request()->is('sell') ? 'btn-success' : 'btn-outline-success' }}">
                Sell
                @if(($newOrdersCount ?? 0) > 0)
                    <span class="dropdown-badge ms-2">{{ $newOrdersCount }}</span>
                @endif
            </a>
        @endif
    </div>
</div>
@endif