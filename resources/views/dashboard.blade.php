@extends("frontend.master")
@section('main-content')
<div class="container mt-4">
<!-- Dashboard Content -->
<div class="row mt-3">
@auth
{{-- Email দিয়ে registered user --}}
@if(Auth::user()->email && (is_null(Auth::user()->email_verified) || (Auth::user()->email_verified > 0 && Auth::user()->email_verified < 9)))
   <div class="mb-4">
      <div class="card border-warning">
         <div class="card-body">
            <h5 class="card-title text-warning">Verify your email</h5>
            <p class="text-muted">Please enter the OTP sent to your email <strong>{{ Auth::user()->email }}</strong>.</p>
            @if(Auth::user()->email_verified && Auth::user()->email_verified > 0 && Auth::user()->email_verified < 9)
            <p class="text-info">OTP attempts: {{ Auth::user()->email_verified }}/9</p>
            @endif
            <form action="{{ route('verify.otp') }}" method="POST">
               @csrf
               <div class="mb-3">
                  <label for="otp" class="form-label">Enter OTP <em>(Check spam folder also)</em></label>
                  <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter OTP" required>
               </div>
               <button type="submit" class="btn btn-success">Verify</button>
               @if(Auth::user()->email_verified < 9)
               <a href="/resend-otp" class="btn btn-link">Send Code Again</a>
               @endif
            </form>
         </div>
      </div>
   </div>

{{-- Phone দিয়ে registered user --}}
@elseif(Auth::user()->phone_number && (is_null(Auth::user()->phone_verified) || (Auth::user()->phone_verified > 0 && Auth::user()->phone_verified < 9)))
   <div class="mb-4">
      <div class="card border-warning">
         <div class="card-body">
            <h5 class="card-title text-warning">Verify your phone number</h5>
            <p class="text-muted">Please enter the OTP sent to your phone number <strong>{{ Auth::user()->phone_number }}</strong>.</p>
            @if(Auth::user()->phone_verified && Auth::user()->phone_verified > 0 && Auth::user()->phone_verified < 9)
            <p class="text-info">OTP attempts: {{ Auth::user()->phone_verified }}/9</p>
            @endif
            <form action="{{ route('verify.otp') }}" method="POST">
               @csrf
               <div class="mb-3">
                  <label for="otp" class="form-label">Enter OTP</label>
                  <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter OTP" required>
               </div>
               <button type="submit" class="btn btn-success">Verify</button>
               @if(Auth::user()->phone_verified < 9)
               <a href="/resend-otp" class="btn btn-link">Send Code Again</a>
               @endif
            </form>
         </div>
      </div>
   </div>

{{-- Email suspended (9 attempts) --}}
@elseif(Auth::user()->email && Auth::user()->email_verified == 9)
   <div class="mb-4">
      <div class="card border-danger">
         <div class="card-body">
            <h5 class="card-title text-danger">Your Account is suspended</h5>
            <p class="text-muted">You have exceeded the maximum OTP attempts for email verification. Please contact support or try with a different email.</p>
         </div>
      </div>
   </div>

{{-- Phone suspended (9 attempts) --}}
@elseif(Auth::user()->phone_number && Auth::user()->phone_verified == 9)
   <div class="mb-4">
      <div class="card border-danger">
         <div class="card-body">
            <h5 class="card-title text-danger">Your Account is suspended</h5>
            <p class="text-muted">You have exceeded the maximum OTP attempts for phone verification. Please contact support or try with a different phone number.</p>
         </div>
      </div>
   </div>

{{-- Verified users (email_verified == 0 OR phone_verified == 0) --}}
@else
   {{-- Include Profile Card Partial --}}
   @include('frontend.profile-card')
@endif

{{-- Error/Success Messages --}}
@if(session('error'))
<div class="alert alert-danger mt-3">
   {{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="alert alert-success mt-3">
   {{ session('success') }}
</div>
@endif

@endauth
   @guest
   {{-- Include Profile Card Partial for Guest Users --}}
   @include('frontend.profile-card')
   @endguest
   <div class="">
      @php
      // Get categories that have posts from this specific user
      $userPostCategories = \App\Models\Post::where('user_id', $user->id)
      ->with('category')
      ->get()
      ->pluck('category')
      ->unique('id')
      ->filter(); // Remove null categories
      @endphp
      <style>
         .scroll-container {
         position: sticky;
         top: 0;
         z-index: 1000;
         background: #fff;
         /* border-bottom: 1px solid #e0e0e0; */
         }
         .scroll-content {
         display: flex;
         overflow-x: auto;
         white-space: nowrap;
         padding: 10px 0;
         gap: 5px;
         scrollbar-width: none;
         -ms-overflow-style: none;
         }
         .scroll-content::-webkit-scrollbar {
         display: none;
         }
         .nav-item-custom {
         display: inline-flex;
         align-items: center;
         padding: 4px 10px 6px 10px;
         margin-right: 10px;
         text-decoration: none;
         color: #333;
         border: 1px solid #ddd;
         border-radius: 8px;
         white-space: nowrap;
         transition: all 0.3s ease;
         min-width: fit-content;
         }
         .nav-item-custom.active {
         background-color: #c6e0fcff;
         color: #007bff;
         border-radius: 8px;
         }
         .grid-section {
         scroll-margin-top: 120px; /* এখানে 80px হচ্ছে header এর height */
         }
      </style>
      <div class="scroll-container pt-5 mb-5">
         <div class="scroll-content">
            @foreach($userPostCategories as $category)
            <a href="#{{ $category->slug }}" class="nav-item-custom category-link" data-category="{{ $category->id }}" onclick="scrollToCategory('{{ $category->id }}')">
            <span>{{ $category->category_name }}</span>
            </a>
            @endforeach
         </div>
      </div>
      <script>
         function scrollToCategory(categoryId) {
             // Remove active class from all category links
             document.querySelectorAll('.category-link').forEach(link => {
                 link.classList.remove('active');
             });
            
             // Add active class to clicked category
             const activeLink = document.querySelector(`[data-category="${categoryId}"]`);
             if (activeLink) {
                 activeLink.classList.add('active');
                 centerActiveLink(activeLink);
             }
            
             // Smooth scroll to category
             document.getElementById('category-' + categoryId).scrollIntoView({
                 behavior: 'smooth'
             });
         }
         
         // Function to center the active link in the horizontal scroll container
         function centerActiveLink(activeLink) {
             const scrollContainer = document.querySelector('.scroll-content');
             const containerWidth = scrollContainer.clientWidth;
             const linkLeft = activeLink.offsetLeft;
             const linkWidth = activeLink.offsetWidth;
             
             // Calculate the scroll position to center the link
             const scrollPosition = linkLeft - (containerWidth / 2) + (linkWidth / 2);
             
             // Smooth scroll to center the active link
             scrollContainer.scrollTo({
                 left: scrollPosition,
                 behavior: 'smooth'
             });
         }
         
         // Intersection Observer to automatically set active category based on scroll
         const observerOptions = {
             root: null,
             rootMargin: '-100px 0px -50% 0px',
             threshold: 0
         };
         
         const observer = new IntersectionObserver((entries) => {
             entries.forEach(entry => {
                 if (entry.isIntersecting) {
                     const categoryId = entry.target.id.replace('category-', '');
                    
                     // Remove active class from all
                     document.querySelectorAll('.category-link').forEach(link => {
                         link.classList.remove('active');
                     });
                    
                     // Add active class to current category
                     const activeLink = document.querySelector(`[data-category="${categoryId}"]`);
                     if (activeLink) {
                         activeLink.classList.add('active');
                         // Center the active link in scroll container
                         centerActiveLink(activeLink);
                     }
                 }
             });
         }, observerOptions);
         
         // Observe all category sections
         document.addEventListener('DOMContentLoaded', function() {
             document.querySelectorAll('[id^="category-"]').forEach(section => {
                 observer.observe(section);
             });
         });
         
         function sortBy(type) {
             console.log('Sorting by:', type);
             // Add your sorting logic here
         }
      </script>
     @php
$new_category_posts = \App\Models\Post::where('user_id', $user->id)
   ->whereNotNull('new_category')
   ->latest()
   ->get();
@endphp

@foreach($userPostCategories as $category)
    @php
    $posts = \App\Models\Post::where('user_id', $user->id)
        ->where('category_id', $category->id)
        ->with(['user', 'category'])
        ->latest()
        ->get();
    @endphp

    @if($posts->count() > 0)
    <section class="grid-section mb-4" id="category-{{ $category->id }}">
        <div class="container">
            <!-- Category Title -->
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="fw-bold text-dark mb-0">{{ $category->category_name }}</h4>
                </div>
            </div>
            <!-- Posts Section -->
            <div class="row g-3 g-md-4 mb-4" id="posts-container-{{ $category->id }}">
                @foreach($posts as $item)
                @php
                $isOwnPost = auth()->check() && auth()->id() == $item->user_id;
                $categoryType = $item->category->cat_type ?? 'product';
                @endphp
                <div class="col-4">
                    <div class="card shadow-sm border-0">
                        @if($item->image)
                        <img src="{{ asset('uploads/'.$item->image) }}" class="card-img-top" alt="Post Image">
                        @else
                        <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image">
                        @endif
                        <div class="card-body p-2">
                            <h5 class="card-title mb-0">{{ $item->title ? Str::limit($item->title, 20) : 'No Title' }}</h5>
                            <small class="price-tag text-success">{{ $item->price ? number_format($item->price, 2) : 'No price' }}</small>
                            <span class="badge {{ $isOwnPost ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnPost ? 'disabled' : '' }}"
                            @if(!$isOwnPost)
                            onclick="addToCart('{{ $item->id }}', '{{ $item->title }}', '{{ $item->price ?? 0 }}', '{{ $item->image ? asset('uploads/'.$item->image) : asset('profile-image/no-image.jpeg') }}', '{{ $categoryType }}')"
                            style="cursor: pointer;"
                            data-category-type="{{ $categoryType }}"
                            @endif>
                            @if($categoryType == 'service')
                            <i class="bi bi-calendar-check"></i>
                            @else
                            <i class="bi bi-cart-plus"></i>
                            @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endforeach

{{-- Others Section --}}
@if($new_category_posts->count() > 0)
<section class="grid-section mb-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="fw-bold text-dark mb-0">Others</h4>
            </div>
        </div>
        <div class="row g-3 g-md-4 mb-4">
            @foreach($new_category_posts as $item)
            @php
            $isOwnPost = auth()->check() && auth()->id() == $item->user_id;
            $categoryType = $item->category->cat_type ?? 'product';
            @endphp
            <div class="col-4">
                <div class="card shadow-sm border-0">
                    @if($item->image)
                    <img src="{{ asset('uploads/'.$item->image) }}" class="card-img-top" alt="Post Image">
                    @else
                    <img src="{{ asset('profile-image/no-image.jpeg') }}" class="card-img-top" alt="No Image">
                    @endif
                    <div class="card-body p-2">
                        <h5 class="card-title mb-0">{{ $item->title ? Str::limit($item->title, 20) : 'No Title' }}</h5>
                        <small class="price-tag text-success">{{ $item->price ? number_format($item->price, 2) : 'No price' }}</small>
                        <span class="badge {{ $isOwnPost ? 'bg-secondary' : 'bg-primary' }} cart-badge {{ $isOwnPost ? 'disabled' : '' }}"
                        @if(!$isOwnPost)
                        onclick="addToCart('{{ $item->id }}', '{{ $item->title }}', '{{ $item->price ?? 0 }}', '{{ $item->image ? asset('uploads/'.$item->image) : asset('profile-image/no-image.jpeg') }}', '{{ $categoryType }}')"
                        style="cursor: pointer;"
                        data-category-type="{{ $categoryType }}"
                        @endif>
                        @if($categoryType == 'service')
                        <i class="bi bi-calendar-check"></i>
                        @else
                        <i class="bi bi-cart-plus"></i>
                        @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

      {{-- If user has no posts --}}
      @if($userPostCategories->count() == 0)
      <div class="container">
         <div class="row">
            <div class="col-12">
               <div class="text-center py-5">
                  <p class="text-muted">{{ $user->name }} has no posts yet!</p>
               </div>
            </div>
         </div>
      </div>
      @endif
   </div>
   {{-- Cart JavaScript --}}
   <script>
      function addToCart(id, title, price, image, categoryType) {
          console.log('Adding to cart:', {id, title, price, image, categoryType});
          alert('Added to cart: ' + title);
      }
   </script>
</div>
{{-- Create Post Modal (Only for Own Profile) --}}
@auth
@if(Auth::id() === $user->id)
<div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="createPostModalLabel">Create New Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
               @csrf
               {{-- Post Category Dropdown --}}
               <div class="mb-3">
                  <label for="category_name" class="form-label">Post Category <span class="text-danger">*</span></label>
                  <div style="position: relative;">
                     <input type="text" 
                        class="form-control" 
                        id="category_name" 
                        name="category_name" 
                        placeholder="Type to search categories..."
                        autocomplete="off"
                        required>
                     <input type="hidden" id="category_id" name="category_id" value="">
                     <div id="suggestions" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;"></div>
                  </div>
                  @error('category_id')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
               {{-- Title Field --}}
               <div class="mb-3">
                  <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter product/service title..." value="{{ old('title') }}" required>
                  @error('title')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
               {{-- Price Field --}}
               <div class="mb-3">
                  <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="price" name="price" placeholder="Enter price..." value="{{ old('price') }}" min="0" step="0.01" required>
                  @error('price')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
               <div class="mb-3">
                  <label for="image" class="form-label">Choose Image</label>
                  <input class="form-control" type="file" id="image" name="image" accept="image/*">
                  @error('image')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
               <div class="mb-3">
                  <label for="description" class="form-label">Product or Service Description</label>
                  <textarea class="form-control" id="description" name="description" rows="4" placeholder="Type your text here...">{{ old('description') }}</textarea>
                  @error('description')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
            </form>
            @if(session('success'))
            <div class="alert alert-success mt-3">
               {{ session('success') }}
            </div>
            @endif
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="createPostForm" class="btn btn-primary" id="submitBtn" disabled>Create Post</button>
         </div>
      </div>
   </div>
</div>
@endif
@endauth
{{-- Modal and Category JavaScript --}}
<script>
   // Categories data from backend
   const categories = @json($categories ?? []);
   const categoryInput = document.getElementById('category_name');
   const categoryIdInput = document.getElementById('category_id');
   const suggestionsDiv = document.getElementById('suggestions');
   let filteredCategories = [];
   
   if (categoryInput) {
       function showSuggestions(searchTerm) {
           if (searchTerm.length === 0) {
               suggestionsDiv.style.display = 'none';
               return;
           }
       
           filteredCategories = categories.filter(category =>
               category.category_name.toLowerCase().includes(searchTerm.toLowerCase())
           );
       
           if (filteredCategories.length === 0) {
               suggestionsDiv.innerHTML = '<div style="padding: 10px 15px; color: #6c757d;">No matching categories found. You can create a new one!</div>';
               suggestionsDiv.style.display = 'block';
               return;
           }
       
           const suggestionsHtml = filteredCategories.map(category => `
               <div style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;"
                    onclick="selectCategory(${category.id}, '${category.category_name}')"
                    onmouseover="this.style.backgroundColor='#f8f9fa'"
                    onmouseout="this.style.backgroundColor='white'">
                   ${category.category_name} <small style="color: #6c757d;">(${category.cat_type})</small>
               </div>
           `).join('');
       
           suggestionsDiv.innerHTML = suggestionsHtml;
           suggestionsDiv.style.display = 'block';
       }
       
       function selectCategory(id, name) {
           categoryInput.value = name;
           categoryIdInput.value = id;
           suggestionsDiv.style.display = 'none';
           toggleSubmit();
       }
       
       categoryInput.addEventListener('input', function() {
           const searchValue = this.value.trim();
           
           if (searchValue.length > 0) {
               showSuggestions(searchValue);
               
               // Check if typed value exactly matches any existing category
               const exactMatch = categories.find(category => 
                   category.category_name.toLowerCase() === searchValue.toLowerCase()
               );
               
               if (exactMatch) {
                   categoryIdInput.value = exactMatch.id; // Set existing category ID
               } else {
                   categoryIdInput.value = ''; // Clear category_id for new category
               }
           } else {
               suggestionsDiv.style.display = 'none';
               categoryIdInput.value = '';
           }
           
           toggleSubmit();
       });
       
       // Hide suggestions when clicking outside
       document.addEventListener('click', function(e) {
           if (!e.target.closest('#category_name') && !e.target.closest('#suggestions')) {
               suggestionsDiv.style.display = 'none';
           }
       });
   }
   
   function toggleSubmit() {
       const titleInput = document.getElementById('title');
       const priceInput = document.getElementById('price');
       const imageInput = document.getElementById('image');
       const descInput = document.getElementById('description');
       const submitBtn = document.getElementById('submitBtn');
       
       if (titleInput && priceInput && categoryInput && submitBtn) {
           // Check if all required fields are filled
           const hasRequiredFields = titleInput.value.trim() !== '' && 
                                    priceInput.value.trim() !== '' && 
                                    categoryInput.value.trim() !== '';
           
           // Check if at least image or description is provided
           const hasContent = (imageInput && imageInput.files.length > 0) || 
                             (descInput && descInput.value.trim() !== '');
           
           submitBtn.disabled = !(hasRequiredFields && hasContent);
       }
   }
   
   // Event listeners for form validation
   document.addEventListener('DOMContentLoaded', function() {
       const titleInput = document.getElementById('title');
       const priceInput = document.getElementById('price');
       const imageInput = document.getElementById('image');
       const descInput = document.getElementById('description');
       
       if (titleInput) titleInput.addEventListener('input', toggleSubmit);
       if (priceInput) priceInput.addEventListener('input', toggleSubmit);
       if (imageInput) imageInput.addEventListener('change', toggleSubmit);
       if (descInput) descInput.addEventListener('input', toggleSubmit);
       
       // Initial check
       toggleSubmit();
   });
</script>
{{-- User-specific Posts Loading JavaScript --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       let currentPage = 1;
       let isLoading = false;
       
       // User ID get করুন (PHP থেকে)
       const userId = @json($user->id ?? null);
       
       const postsContainer = document.getElementById('posts-container');
       const loadingSpinner = document.getElementById('loading');
       const loadMoreBtn = document.getElementById('load-more-btn');
       const loadMoreContainer = document.getElementById('load-more-container');
   
       // Load More Button Click
       if (loadMoreBtn && userId) {
           loadMoreBtn.addEventListener('click', function() {
               loadMorePosts();
           });
       }
   
       // Auto Load on Scroll (Optional)
       window.addEventListener('scroll', function() {
           if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
               if (!isLoading && loadMoreBtn && loadMoreBtn.style.display !== 'none' && userId) {
                   loadMorePosts();
               }
           }
       });
   
       function loadMorePosts() {
           if (isLoading || !userId) return;
           
           isLoading = true;
           currentPage++;
           
           // Show loading spinner
           loadingSpinner.style.display = 'block';
           if (loadMoreBtn) loadMoreBtn.style.display = 'none';
           
           // User-specific route ব্যবহার করুন
           const url = `/posts/load-more/${userId}`;
           
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
                   
                   // Initialize read more functionality for new posts
                   initReadMore();
                   
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
       
       // Initialize Read More functionality
       function initReadMore() {
           document.querySelectorAll('.read-more').forEach(link => {
               // Remove existing event listeners to avoid duplicates
               link.replaceWith(link.cloneNode(true));
           });
           
           document.querySelectorAll('.read-more').forEach(link => {
               link.addEventListener('click', function() {
                   const para = this.previousElementSibling;
                   if (para.style.maxHeight === 'none') {
                       para.style.maxHeight = '75px';
                       this.textContent = 'Read more';
                   } else {
                       para.style.maxHeight = 'none';
                       this.textContent = 'Read less';
                   }
               });
           });
       }
       
       // Initialize read more for existing posts
       initReadMore();
   });
</script>
@endsection
