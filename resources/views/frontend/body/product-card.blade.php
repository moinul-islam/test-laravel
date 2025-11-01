<div class="card-body p-2">
    @if($isUserProfile)
        {{-- Profile card layout --}}
        <a href="{{ route('profile.show', $item->username) }}" class="text-decoration-none">
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
        
        {{-- Price display with discount logic --}}
        @if($item->price && $item->discount_price)
            <small class="price-tag text-danger text-decoration-line-through">{{ number_format($item->price, 2) }}</small>
            <small class="price-tag text-success">{{ number_format($item->price - $item->discount_price, 2) }}</small>
        @elseif($item->price)
            <small class="price-tag text-success">{{ number_format($item->price, 2) }}</small>
        @else
            <small class="price-tag text-success">No price</small>
        @endif
        
        <span class="badge {{ $isOwnPost ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnPost ? 'disabled' : '' }}"
            @if(!$isOwnPost)
                @if(auth()->check() && auth()->user()->phone_verified == '0')
                    onclick="addToCart(
                        '{{ $item->id }}',
                        '{{ $item->title }}',
                        '{{ $item->price ?? 0 }}',
                        '{{ $item->image ? asset('uploads/'.$item->image) : asset('profile-image/no-image.jpeg') }}',
                        '{{ $categoryType }}',
                        '{{ $item->discount_price ?? 0 }}'
                    )"
                    style="cursor: pointer;"
                    data-category-type="{{ $categoryType }}"
                @else
                    onclick="alert('দয়া করে আগে ফোন নম্বর ভেরিফাই করুন!')"
                    style="cursor: not-allowed; opacity: 0.6;"
                @endif
            @endif>
            @if($categoryType == 'service')                            
                                    @if($isOwnPost)
                                        <i class="bi bi-pencil" onclick="editPost({{ $item->id }})" style="cursor: pointer;"></i>
                                    @else
                                        <i class="bi bi-calendar-check"></i>
                                    @endif
            @elseif($categoryType == 'post')                                
                @if($isOwnPost)
                    <i class="bi bi-pencil" onclick="editPost({{ $item->id }})" style="cursor: pointer;"></i>
                @else
                    <i class="bi bi-eye"></i>
                @endif
            @else                                
                @if($isOwnPost)
                    <i class="bi bi-pencil" onclick="editPost({{ $item->id }})" style="cursor: pointer;"></i>
                @else
                    <i class="bi bi-cart-plus"></i>
                @endif
            @endif
        </span>
    @endif                            
</div>