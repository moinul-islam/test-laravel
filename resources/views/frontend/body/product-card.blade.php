<div class="col-4" style="{{ !$isOpen ? 'opacity: 0.6;' : '' }}">
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