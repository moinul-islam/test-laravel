@extends("frontend.master")
@section('main-content')

<style>
   .card-img-top {
   height: 100px;
   object-fit: cover;
   width: 100%;
   }
   .card-title {
   display: inline-block;
   width: 100%;
   white-space: nowrap;
   overflow: hidden;
   text-overflow: ellipsis;
   }
   .price-tag {
   display: inline-block;
   max-width: 100px;
   white-space: nowrap;
   overflow: hidden;
   text-overflow: ellipsis;
   }
   a {
   text-decoration: none;
   }
   @media (max-width: 767.98px) {
   .card-title {
   font-size: 0.9rem;
   }
   .price-tag {
   font-size: 0.7rem;
   }
   }
   @media (max-width: 575.98px) {
   .card-title {
   font-size: 0.8rem;
   }
   .price-tag {
   font-size: 0.7rem;
   max-width: 70px;
   }
   }
   .cart-badge {
   float: right;
   }
   @media (max-width: 400px) {
   .cart-badge {
   float: none;
   display: block;
   width: 100%;
   text-align: center;
   margin-top: 8px;
   }
   }
   .top-badge {
   position: absolute;
   top: 10px;
   left: 10px;
   padding: 4px;
   font-size: 0.6rem;
   z-index: 10;
   text-transform: uppercase;
   }
   
   /* CSS for disabled badge */
   .cart-badge.disabled {
   opacity: 0.5;
   cursor: not-allowed;
   pointer-events: none;
   }

   .cart-badge:not(.disabled) {
   cursor: pointer;
   transition: opacity 0.3s ease;
   }

   .cart-badge:not(.disabled):hover {
   opacity: 0.8;
   }
</style>

<div class="mt-4">
   
   <div class="container-fluid container">
      

   {{-- Include Sidebar --}}
@include('frontend.body.sidebar')


<!-- Horizontal Scrollable Navigation -->
<div class="scroll-container">
    <div class="scroll-content">
        <a href="#" class="nav-item-custom" id="openSidebarBtn">
            <span><i class="bi bi-list"></i></span>
        </a>
        
        @if(!isset($category) || $category->cat_type != 'profile')
            <a href="#" class="nav-item-custom dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <span><i class="bi bi-funnel"></i> Filter</span>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="sortBy('price-low'); return false;">Price: Low to High</a></li>
                <li><a class="dropdown-item" href="#" onclick="sortBy('price-high'); return false;">Price: High to Low</a></li>
                <li><a class="dropdown-item" href="#" onclick="sortBy('best-selling'); return false;">Best Selling</a></li>
                <li><a class="dropdown-item" href="#" onclick="sortBy('newest'); return false;">Newest First</a></li>
            </ul>
        @endif
        
        @php
            // Determine which categories to show in navigation
            $navCategories = collect();
            
            if(isset($category)) {
                // Determine if current category is profile or product/service
                $isProfile = ($category->cat_type == 'profile');
                
                // If category is a child (has parent), show siblings
                if($category->parent_cat_id) {
                    if($isProfile) {
                        // Show only profile siblings
                        $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                            ->where('cat_type', 'profile')
                            ->get();
                    } else {
                        // Show both product AND service siblings
                        $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                            ->whereIn('cat_type', ['product', 'service','post'])
                            ->get();
                    }
                } 
                // If category is parent (universal), show its children
                else if($category->cat_type == 'universal') {
                    $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                        ->whereIn('cat_type', ['product', 'service', 'profile'])
                        ->get();
                }
            }
        @endphp
        
        {{-- Fetch child categories if current category exists --}}
        @php
            $childCats = collect();
            if(isset($category)) {
                $childCats = \App\Models\Category::where('parent_cat_id', $category->id)->get();
            }
        @endphp
        
        @if($childCats->count() > 0)
            {{-- If current category has children, show them --}}
            
            {{-- All items badge for current category --}}
            <a href="{{ route('products.category', [$visitorLocationPath, $category->slug]) }}" 
               class="nav-item-custom active"
               style="background: #0d6efd; color: white;">
                <span>All {{ $category->category_name }}</span>
            </a>
            
            {{-- Child categories --}}
            @foreach($childCats as $childCat)
                <a href="{{ route('products.category', [$visitorLocationPath,$childCat->slug]) }}" 
                   class="nav-item-custom"
                   style="background: #f8f9fa; color: #212529; border: 1px solid #6c757d;">
                    <span>{{ $childCat->category_name }}</span>
                </a>
            @endforeach
            
        @elseif($navCategories->count() > 0)
            {{-- If no children, show sibling categories with current one active --}}
            
            {{-- Get parent to show "All Parent" badge --}}
            @php
                $parentCat = null;
                if(isset($category) && $category->parent_cat_id) {
                    $parentCat = \App\Models\Category::find($category->parent_cat_id);
                }
            @endphp
            
            @if($parentCat)
                {{-- All items badge for parent category --}}
                <a href="{{ route('products.category', [$visitorLocationPath, $parentCat->slug]) }}" 
                   class="nav-item-custom"
                   style="background: #f8f9fa; color: #212529; border: 1px solid #6c757d;">
                    <span>All {{ $parentCat->category_name }}</span>
                </a>
            @endif
            
            {{-- Show sibling categories --}}
            @foreach($navCategories as $navCat)
                <a href="{{ route('products.category',[$visitorLocationPath, $navCat->slug]) }}" 
                   class="nav-item-custom {{ isset($category) && $category->id == $navCat->id ? 'active' : '' }}"
                   @if(isset($category) && $category->id == $navCat->id)
                       style="background: #0d6efd; color: white;"
                   @else
                       style="background: #f8f9fa; color: #212529; border: 1px solid #6c757d;"
                   @endif>
                    <span>{{ $navCat->category_name }}</span>
                </a>
            @endforeach
            
        @else
            {{-- Show main parent categories as dropdown --}}
            @php
                $parentCategories = \App\Models\Category::where('cat_type', 'universal')
                                                       ->whereNull('parent_cat_id')
                                                       ->get();
            @endphp
            
            @foreach($parentCategories as $parentCat)
                @php
                    $subCategories = \App\Models\Category::where('parent_cat_id', $parentCat->id)
                                                       ->whereIn('cat_type', ['product', 'service', 'profile'])
                                                       ->get();
                @endphp
                
                @if($subCategories->count() > 0)
                    <div class="dropdown nav-item-custom">
                        <a href="#" class="nav-item-custom dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>{{ $parentCat->category_name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            @foreach($subCategories as $subCat)
                                <li>
                                    <a class="dropdown-item" href="{{ route('products.category', [$visitorLocationPath, $subCat->slug]) }}">
                                        {{ $subCat->category_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>

   </div>
</div>

<div class="mt-4">
   <div class="container">
      <div class="">
         <div class="g-3 g-md-4" id="posts-container">
            @include('frontend.products-partial', ['posts' => $posts])
         </div>
         
         <!-- Loading Spinner -->
         <div id="loading" style="display: none; text-align: center; margin: 20px 0;">
            <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading more products...</p>
         </div>
         
         <!-- Load More Button -->
         @if($posts->hasMorePages())
         <div id="load-more-container" class="text-center mt-3">
            <button id="load-more-btn" class="btn btn-primary">Load More Products</button>
         </div>
         @endif
      </div>
   </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function sortBy(type) {
    console.log('Sorting by:', type);
    // Add your sorting logic here
    switch(type) {
        case 'price-low':
            // Sort products by price low to high
            break;
        case 'price-high':
            // Sort products by price high to low
            break;
        case 'best-selling':
            // Sort by best selling items
            break;
        case 'newest':
            // Sort by newest items
            break;
        case 'rating':
            // Sort by highest rated items
            break;
    }
}

// AJAX Load More Functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let isLoading = false;
    
    const postsContainer = document.getElementById('posts-container');
    const loadingSpinner = document.getElementById('loading');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreContainer = document.getElementById('load-more-container');
    
    @if(isset($category))
    const categorySlug = '{{ $category->slug }}';
    @else
    const categorySlug = null;
    @endif

    // Load More Button Click
    if (loadMoreBtn && categorySlug) {
        loadMoreBtn.addEventListener('click', function() {
            loadMorePosts();
        });
    }

    // Auto Load on Scroll (Optional)
    window.addEventListener('scroll', function() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
            if (!isLoading && loadMoreBtn && loadMoreBtn.style.display !== 'none' && categorySlug) {
                loadMorePosts();
            }
        }
    });

    function loadMorePosts() {
        if (isLoading || !categorySlug) return;
        
        isLoading = true;
        currentPage++;
        
        // Show loading spinner
        loadingSpinner.style.display = 'block';
        if (loadMoreBtn) loadMoreBtn.style.display = 'none';
        
        // AJAX request
        const url = `/products/${categorySlug}`;
        
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                page: currentPage
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Hide loading spinner
                loadingSpinner.style.display = 'none';
                
                // Append new posts
                postsContainer.insertAdjacentHTML('beforeend', response.posts);
                
                // Show/Hide load more button
                if (response.hasMore) {
                    if (loadMoreBtn) loadMoreBtn.style.display = 'block';
                } else {
                    if (loadMoreContainer) loadMoreContainer.style.display = 'none';
                }
                
                isLoading = false;
            },
            error: function(xhr, status, error) {
                loadingSpinner.style.display = 'none';
                if (loadMoreBtn) loadMoreBtn.style.display = 'block';
                console.error('Error loading posts:', error);
                isLoading = false;
            }
        });
    }
});
</script>


<script>
// Auto scroll active item to center
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.querySelector('.scroll-container');
    const activeItem = document.querySelector('.nav-item-custom.active');
    
    if (scrollContainer && activeItem) {
        scrollToCenter(activeItem, scrollContainer);
    }
    
    // Handle navigation clicks and scroll to center
    document.querySelectorAll('.nav-item-custom').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            document.querySelectorAll('.nav-item-custom').forEach(nav => {
                nav.classList.remove('active');
            });
            
            // Add active class to clicked item (if not a dropdown)
            if (!this.classList.contains('dropdown-toggle')) {
                this.classList.add('active');
                scrollToCenter(this, scrollContainer);
            }
        });
    });
});

function scrollToCenter(element, container) {
    const elementRect = element.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();
    
    // Calculate the position to scroll to center the element
    const elementCenter = elementRect.left + elementRect.width / 2;
    const containerCenter = containerRect.left + containerRect.width / 2;
    const scrollOffset = elementCenter - containerCenter;
    
    // Smooth scroll to center
    container.scrollBy({
        left: scrollOffset,
        behavior: 'smooth'
    });
}
function sortBy(type) {
    console.log('Sorting by:', type);
    
    // Show loading state
    const postsContainer = document.getElementById('posts-container');
    const loadingSpinner = document.getElementById('loading');
    
    loadingSpinner.style.display = 'block';
    
    // Get current URL
    const currentUrl = window.location.pathname;
    
    // AJAX request
    $.ajax({
        url: currentUrl,
        method: 'GET',
        data: {
            sort: type,
            page: 1
        },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            loadingSpinner.style.display = 'none';
            
            // Replace posts container content
            postsContainer.innerHTML = response.posts;
            
            // Update load more button
            const loadMoreContainer = document.getElementById('load-more-container');
            if (response.hasMore) {
                if (loadMoreContainer) loadMoreContainer.style.display = 'block';
            } else {
                if (loadMoreContainer) loadMoreContainer.style.display = 'none';
            }
        },
        error: function(xhr, status, error) {
            loadingSpinner.style.display = 'none';
            console.error('Error sorting posts:', error);
            alert('Error loading products. Please try again.');
        }
    });
    
    // Prevent default link behavior
    event.preventDefault();
}
</script>

@endsection