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
            @if($isUserProfile)
                <div class="col-12" style="">
                    <div class="card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between card-body">
                            <div class="d-flex align-items-center">
                                <img src="{{ $item->image ? asset('profile-image/'.$item->image) : asset('profile-image/no-image.jpeg') }}"
                                    class="rounded-circle me-2"
                                    alt="Profile Photo"
                                    style="width:40px; height:40px; object-fit:cover;">
                                <div>
                                    <h6 class="mb-0">
                                        <a href="{{ route('profile.show', $item->username) }}" class="text-decoration-none">
                                            {{ $item->name ? Str::limit($item->name, 20) : 'No Name' }}
                                        </a>
                                    </h6>
                                    <small class="text-muted"><i class="bi bi-pin-map"></i> {{ $item->area ? Str::limit($item->area, 20) : 'No Address' }}</small>
                                    <small class="text-muted"><i class="bi bi-clock"></i> 
                                        @if(!$isOpen)
                                            <span class="text-danger">Currently Closed</span>
                                        @else
                                            <span class="text-success">Open Now</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a href="tel:{{ $item->phone_number }}">
                                    <span class="badge {{ $isOwnProfile ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnProfile ? 'disabled' : '' }}">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <!-- @if(!$isOpen)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2" style="z-index: 10; font-size:10px;">
                                Closed now
                            </span>
                        @endif -->
                    </div>
                </div>
            @else
                <div class="col-6 col-md-4" style="{{ !$isOpen ? 'opacity: 0.6;' : '' }}">
                    <div class="card shadow-sm border-0 position-relative">
                        {{-- Service Hours Badge --}}
                        @if(!$isOpen)
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2" style="z-index: 10; font-size:10px;">
                            Closed now
                        </span>
                        @elseif($hasAlreadyReviewed)
                        {{-- Rating Badge --}}
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#reviewModal{{ $item->id }}" 
                            style="cursor: pointer; z-index: 10; font-size:10px;">
                            <div class="user-rating">
                                <div class="stars">
                                    <span class="rating-text">
                                        <i class="bi bi-star-fill"></i> 
                                        {{ number_format($item->averageRating(), 1) }}
                                        ({{ $item->reviewCount() }})
                                    </span>
                                </div>
                            </div>
                        </span>
                        @endif

                        {{-- Card Image --}}
                        @if(isset($item->title))
                            {{-- This is a Post --}}
                            @if($item->image)
                                <img src="{{ asset('uploads/'.$item->image) }}" class="card-img-top" alt="Post Image" style="height: 100px; object-fit: cover;">
                            @else
                                <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image" style="height: 100px; object-fit: cover;">
                            @endif
                        @else
                            {{-- This is a User (Profile) --}}
                            @if($item->image)
                                <img src="{{ asset('profile-image/'.$item->image) }}" class="card-img-top" alt="Profile Image" style="height: 100px; object-fit: cover;">
                            @else
                                <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image" style="height: 100px; object-fit: cover;">
                            @endif
                        @endif
                        
                        {{-- Card Body --}}
                        @include('frontend.body.product-card')
                    </div>
                </div>
                @include('frontend.body.review-modal')
            @endif
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

{{-- Cart JavaScript --}}
   <script>
      function addToCart(id, title, price, image, categoryType, discountPrice = 0) {
          console.log('Adding to cart:', {id, title, price, image, categoryType, discountPrice});
          if (window.cartManager) {
              window.cartManager.addToCart(id, title, price, image, categoryType, discountPrice);
          } else {
              alert('Cart system not available');
          }
      }
   </script>

@include('frontend.body.review-cdn')