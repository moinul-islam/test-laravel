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
        
        @if($shouldShowCard)
        <div class="col-4" style="{{ !$isOpen ? 'opacity: 0.6;' : '' }}">
           <div class="card shadow-sm border-0 position-relative">
              {{-- Service Hours Badge --}}
              @if(!$isOpen)
              <span class="badge bg-danger position-absolute top-0 end-0 m-2" style="z-index: 10;">
                  Closed now
              </span>
              @endif

              @if(isset($item->title))
                 {{-- This is a Post --}}
                 @if($item->image)
                    <img src="{{ asset('uploads/'.$item->image) }}" class="card-img-top" alt="Post Image">
                 @else
                    <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image">
                 @endif
              @else
                 {{-- This is a User (Profile) --}}
                 @if($item->image)
                    <img src="{{ asset('profile-image/'.$item->image) }}" class="card-img-top" alt="Profile Image">
                 @else
                    <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image">
                 @endif
              @endif
             
              <div class="card-body p-2">
                 @if($isUserProfile)
                    {{-- Profile card layout --}}
                    <a href="{{ route('profile.show', $item->username) }}">
                       <h5 class="card-title mb-0">
                          {{ $item->name ? Str::limit($item->name, 20) : 'No Name' }}
                       </h5>
                    </a>
                    <small class="price-tag text-success">{{ $item->area ? Str::limit($item->area, 20) : 'No Address' }}</small>
                    
                    <a href="tel:{{ $item->phone_number }}">
                     <span class="badge {{ $isOwnProfile ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnProfile ? 'disabled' : '' }}">
                       <i class="bi bi-telephone"></i>
                    </span>
                    </a>
                 @else
                    {{-- Regular product/service card layout --}}
                    <h5 class="card-title mb-0">{{ $item->title ? Str::limit($item->title, 20) : 'No Title' }}</h5>
                    <small class="price-tag text-success">{{ $item->price ? Str::limit($item->price, 20) : 'No price' }}</small>
                    
                    <span class="badge {{ $isOwnPost ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnPost ? 'disabled' : '' }}"
                           @if(!$isOwnPost)
                              @if(auth()->check() && auth()->user()->phone_verified == '0')
                                 onclick="addToCart('{{ $item->id }}', '{{ $item->title }}', '{{ $item->price ?? 0 }}', '{{ $item->image ? asset('uploads/'.$item->image) : asset('profile-image/no-image.jpeg') }}', '{{ $categoryType }}')"
                                 style="cursor: pointer;"
                                 data-category-type="{{ $categoryType }}"
                              @else
                                 onclick="alert('দয়া করে আগে ফোন নম্বর ভেরিফাই করুন!')"
                                 style="cursor: not-allowed; opacity: 0.6;"
                              @endif
                           @endif>
                        @if($categoryType == 'service')
                           <i class="bi bi-calendar-check"></i>
                        @else
                           <i class="bi bi-cart-plus"></i>
                        @endif
                     </span>

                 @endif                            
              </div>
           </div>
        </div>
        @endif
    @empty
    <div class="col-12">
       <div class="text-center py-5">
          <p class="text-muted">Nothing is found!</p>
       </div>
    </div>
    @endforelse
    
    {{-- যদি posts আছে কিন্তু কোনটাই দেখানো হচ্ছে না (সব hide হয়ে গেছে) --}}
    @if($posts->count() > 0 && $visibleItemsCount == 0)
    <div class="col-12">
       <div class="text-center py-5">
          <p class="text-muted">Nothing is found!</p>
       </div>
    </div>
    @endif
</div>