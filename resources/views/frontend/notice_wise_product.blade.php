@extends("frontend.master")
@section('main-content')
<div class="container mt-4">
<!-- Dashboard Content -->
<div class="row mt-3">
{{-- Discount &  Offer --}}
    @if($discount_wise_products->count() > 0)
    <section class="grid-section mb-4">
        <div class="">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="fw-bold text-dark mb-0">Notice & Announcement</h4>
                </div>
            </div>
            <div class="row g-3 g-md-4 mb-4">
                @foreach($discount_wise_products as $item)
                @php
                $isOwnPost = auth()->check() && auth()->id() == $item->user_id;
                $categoryType = $item->category->cat_type ?? 'product';
                $hasAlreadyReviewed = \App\Models\Review::where('product_id', $item->id)
                                    ->where('user_id', Auth::id())
                                    ->exists();
                @endphp
                <div class="col-4">
                    <div class="card shadow-sm border-0">

                        @if($hasAlreadyReviewed)
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

                        @if($item->image)
                        <img src="{{ asset('uploads/'.$item->image) }}" class="card-img-top" alt="Post Image">
                        @else
                        <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image">
                        @endif
                        <div class="card-body p-2">
                                <h5 class="card-title mb-0">{{ $item->title ? Str::limit($item->title, 20) : 'No Title' }}</h5>
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
                            </div>
                    </div>
                </div>
                @include('frontend.body.review-modal')
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
</div>
@endsection
