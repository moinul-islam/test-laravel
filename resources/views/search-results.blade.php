@extends("frontend.master")
@section('main-content')
<div class="container my-4">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold">Search Results: <span class="text-muted">"{{ $query }}"</span></h4>
            <p class="text-muted mb-0">
                Found {{ $users->count() }} users and {{ $posts->count() }} posts
            </p>
        </div>
    </div>

    @php
        $visibleItemsCount = 0;
        // Merge users and posts into a single collection
        $allResults = collect();
        
        // Add users to results
        foreach($users as $user) {
            $allResults->push($user);
        }
        
        // Add posts to results
        foreach($posts as $post) {
            $allResults->push($post);
        }
    @endphp

    <!-- Results Grid -->
    <div class="row g-3 g-md-4 mb-4" id="search-results-container">
        @forelse($allResults as $item)
            @php
                // Check if this is a User object or Post object
                $isUserProfile = !isset($item->title);
                
                if ($isUserProfile) {
                    // This is a User object
                    $isOwnProfile = auth()->check() && auth()->id() == $item->id;
                    $categoryType = 'profile';
                    $shouldShowCard = true;
                } else {
                    // This is a Post object
                    $isOwnPost = auth()->check() && auth()->id() == $item->user_id;
                    $categoryType = $item->category->cat_type ?? 'product';
                    $shouldShowCard = true;
                }
                
                // If card should be shown, increment counter
                if ($shouldShowCard) {
                    $visibleItemsCount++;
                }
            @endphp
            
            @if($shouldShowCard)
            <div class="col-4">
                <div class="card shadow-sm border-0">
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
                            
                            <a href="{{ route('profile.show', $item->username) }}">
                                <span class="badge {{ $isOwnProfile ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnProfile ? 'disabled' : '' }}">
                                    <i class="bi bi-person"></i>
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
                    <i class="bi bi-search" style="font-size: 60px; color: #ddd;"></i>
                    <h4 class="mt-4 text-muted">কোনো ফলাফল পাওয়া যায়নি</h4>
                    <p class="text-muted">অন্য কিছু search করে দেখুন</p>
                </div>
            </div>
        @endforelse
        
        {{-- যদি results আছে কিন্তু কোনটাই দেখানো হচ্ছে না --}}
        @if($allResults->count() > 0 && $visibleItemsCount == 0)
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 60px; color: #ddd;"></i>
                <h4 class="mt-4 text-muted">কোনো ফলাফল পাওয়া যায়নি</h4>
                <p class="text-muted">অন্য কিছু search করে দেখুন</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Cart JavaScript (if needed) --}}
<script>
function addToCart(id, title, price, image, categoryType) {
    // Your existing cart logic here
    console.log('Added to cart:', {id, title, price, image, categoryType});
    
    // Example cart logic
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if item already exists
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            title: title,
            price: price,
            image: image,
            categoryType: categoryType,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    alert('Added to cart successfully!');
}
</script>
@endsection