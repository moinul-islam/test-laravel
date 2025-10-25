@php
    $visibleItemsCount = 0;
@endphp

<div class="row g-3 g-md-4 mb-4" id="posts-container">
    @forelse($posts as $item)
        @php
            // Check if this is a User object (profile) or Post object
            $isUserProfile = !isset($item->title);
            
            if ($isUserProfile) {
                // This is a User object
                $isOwnProfile = auth()->check() && auth()->id() == $item->id;
                $categoryType = 'profile';
                
                // Check service hours for profile
                $serviceHours = json_decode($item->service_hr, true) ?? [];
                $todayName = strtolower(now()->setTimezone('Asia/Dhaka')->format('l'));
                $todayData = $serviceHours[$todayName] ?? null;
                
                $isOpen = false;
                if(is_array($todayData) && isset($todayData['open'], $todayData['close'])) {
                    $openTime = \Carbon\Carbon::createFromFormat('H:i', $todayData['open'], 'Asia/Dhaka');
                    $closeTime = \Carbon\Carbon::createFromFormat('H:i', $todayData['close'], 'Asia/Dhaka');
                    $now = now()->setTimezone('Asia/Dhaka');
                    if($now->between($openTime, $closeTime)) {
                        $isOpen = true;
                    }
                }
                
                $shouldShowCard = true;
            } else {
                // This is a Post object
                $isOwnPost = auth()->check() && auth()->id() == $item->user_id;
                $categoryType = $item->category->cat_type ?? 'product';
                
                // Check service hours for post's user
                $postUser = $item->user;
                $serviceHours = json_decode($postUser->service_hr, true) ?? [];
                $todayName = strtolower(now()->setTimezone('Asia/Dhaka')->format('l'));
                $todayData = $serviceHours[$todayName] ?? null;
                
                $isOpen = false;
                if(is_array($todayData) && isset($todayData['open'], $todayData['close'])) {
                    $openTime = \Carbon\Carbon::createFromFormat('H:i', $todayData['open'], 'Asia/Dhaka');
                    $closeTime = \Carbon\Carbon::createFromFormat('H:i', $todayData['close'], 'Asia/Dhaka');
                    $now = now()->setTimezone('Asia/Dhaka');
                    if($now->between($openTime, $closeTime)) {
                        $isOpen = true;
                    }
                }
                
                $shouldShowCard = true;
            }
            
            if ($shouldShowCard) {
                $visibleItemsCount++;
            }
        @endphp
        @php
                            $hasAlreadyReviewed = \App\Models\Review::where('product_id', $item->id)
                                ->where('user_id', Auth::id())
                                ->exists();
                        @endphp
        
        @if($shouldShowCard)
      
           @include('frontend.body.product-card')
           @include('frontend.body.review-modal')
        @endif
    @empty
    <div class="col-12">
       <div class="text-center py-5">
          <p class="text-muted">Nothing is found!</p>
       </div>
    </div>
    @endforelse
    
    @if($posts->count() > 0 && $visibleItemsCount == 0)
    <div class="col-12">
       <div class="text-center py-5">
          <p class="text-muted">Nothing is found!</p>
       </div>
    </div>
    @endif
</div>

@include('frontend.body.review-cdn')