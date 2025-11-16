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
   
{{-- Posts Container --}}
<div class="container mt-4">

@php
$posts = \App\Models\Post::where('user_id', $user->id)
    ->with(['user', 'category'])
    ->latest()
    ->paginate(10);
@endphp

    <!-- Horizontal Scrollable Navigation -->
    <div class="scroll-container mb-4">
        <div class="scroll-content">
            
        {{-- Product & Services Link --}}
        <a href="/{{ $user->username }}/products-services" class="nav-item-custom">
            <span><i class="bi bi-cart"></i></span>
            <span>Product & Services</span>
        </a>

@php
    // User এর post করা সব unique categories খুঁজুন (শুধু post type)
    $userPostCategoryIds = \App\Models\Post::where('user_id', $user->id)
                            ->distinct()
                            ->pluck('category_id');
    
    $userCategories = \App\Models\Category::whereIn('id', $userPostCategoryIds)
                                         ->where('cat_type', 'post')
                                         ->get();
    
    // Navigation এ কোন categories দেখাবে তা নির্ধারণ করুন
    $navCategories = collect();
    
    if(isset($category)) {
        // যদি URL এ category থাকে
        if($category->parent_cat_id) {
            // Child category selected → parent এর সব children যাদের post আছে
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                ->where('cat_type', 'post')
                                ->whereIn('id', $userPostCategoryIds)
                                ->get();
        } else if($category->cat_type == 'post') {
            // Parent category selected → এর সব children যাদের post আছে
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                                ->where('cat_type', 'post')
                                ->whereIn('id', $userPostCategoryIds)
                                ->get();
        }
    } else {
        // কোনো category select না থাকলে → user এর সব parent categories
        $parentCategoryIds = $userCategories->pluck('parent_cat_id')->filter()->unique();
        $navCategories = \App\Models\Category::whereIn('id', $parentCategoryIds)
                            ->where('cat_type', 'post')
                            ->get();
        
        // যদি parent না থাকে তাহলে direct categories দেখান
        if($navCategories->isEmpty()) {
            $navCategories = $userCategories->whereNull('parent_cat_id');
        }
    }
@endphp

@php
    // Check if category is set from path parameter
    $selectedCategorySlug = isset($category) ? $category->slug : null;
@endphp

{{-- All Posts Link --}}
<a href="{{ url('/' . $user->username) }}" 
   class="nav-item-custom {{ !$selectedCategorySlug ? 'active' : '' }}">
    <span>All Posts</span>
</a>

@if($navCategories->count() > 0)
    {{-- Show user's post categories --}}
    @foreach($navCategories as $navCat)
        @php
            // Check if this category has children with posts
            $hasChildrenWithPosts = \App\Models\Category::where('parent_cat_id', $navCat->id)
                                        ->where('cat_type', 'post')
                                        ->whereIn('id', $userPostCategoryIds)
                                        ->exists();
        @endphp
        
        @if($hasChildrenWithPosts)
            {{-- Show dropdown for parent categories --}}
            <div class="dropdown nav-item-custom">
                <a href="#" class="nav-item-custom dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>{{ $navCat->category_name }}</span>
                </a>
                <ul class="dropdown-menu">
                    @php
                        $childCategories = \App\Models\Category::where('parent_cat_id', $navCat->id)
                                            ->where('cat_type', 'post')
                                            ->whereIn('id', $userPostCategoryIds)
                                            ->get();
                    @endphp
                    @foreach($childCategories as $childCat)
                        <li>
                            <a class="dropdown-item" href="{{ url('/' . $user->username . '/' . $childCat->slug) }}">
                                {{ $childCat->category_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            {{-- Direct link for categories without children --}}
            <a href="{{ url('/' . $user->username . '/' . $navCat->slug) }}" 
               class="nav-item-custom {{ $selectedCategorySlug == $navCat->slug ? 'active' : '' }}">
                <span>
                    <i class="bi {{ $navCat->image }}"></i>
                </span>
                <span>{{ $navCat->category_name }}</span>
            </a>
        @endif
    @endforeach
@endif

        </div>
    </div>

    <div class="row">
        <div class="col-12" id="posts-container">
            @include('frontend.posts-partial', ['posts' => $posts])
        </div>
    </div>
    
    {{-- Loading Spinner --}}
    @if(isset($posts) && $posts->hasMorePages())
    <div class="text-center my-4" id="loading-spinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading more posts...</p>
    </div>
    
    <input type="hidden" id="has-more-pages" value="{{ $posts->hasMorePages() ? '1' : '0' }}">
    <input type="hidden" id="current-page" value="1">
    @endif
</div>

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
                  <label for="title" class="form-label" id="title_label">Title <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter product/service title..." value="{{ old('title') }}" required>
                  @error('title')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>
               {{-- Price Field --}}
               <div class="mb-3" id="price_field_container">
                  <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="price" name="price" placeholder="Enter price..." value="{{ old('price') }}" min="0" step="0.01" required>
                  @error('price')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>

               <div class="row mb-4">
                  <div class="col-sm-3">
                     <h6 class="mb-0">Image</h6>
                  </div>
                  <div class="col-sm-9 text-secondary">
                     <input type="file" name="photo" class="form-control" id="formFile">
                     <input type="hidden" name="image_data" id="imageData">
                     
                     @error('photo')
                        <span class="text-danger">{{ $message }}</span>
                     @enderror
                     <div id="imageProcessingStatus" style="display: none;" class="mt-2">
                        <div class="progress">
                           <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="imageProgress"></div>
                        </div>
                        <small id="imageStatusText">Image processing is going on....</small>
                     </div>
                  </div>
               </div>

            <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
            <!-- Add jQuery Ajax code -->
            <script>
// Reusable Image Processing Function
function setupImageProcessing(inputId, dataInputId, statusId, progressId, statusTextId, previewId = null) {
    const MAX_WIDTH = 1800;
    const MAX_HEIGHT = 1800;
    const QUALITY = 0.7;
    
    const imageInput = document.getElementById(inputId);
    const imageDataInput = document.getElementById(dataInputId);
    const imageProcessingStatus = document.getElementById(statusId);
    const imageProgress = document.getElementById(progressId);
    const imageStatusText = document.getElementById(statusTextId);
    const imagePreview = previewId ? document.getElementById(previewId) : null;
    
    if (!imageInput) return;
    
    imageInput.addEventListener('change', function(e) {
        const file = this.files[0];
        if (!file) return;
        
        // Clear previous preview
        if (imagePreview) imagePreview.src = '';
        
        // File type validation
        const fileExt = file.name.split('.').pop().toLowerCase();
        const allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
        
        if (!allowedExts.includes(fileExt)) {
            alert('অনুগ্রহ করে শুধুমাত্র JPG, PNG, GIF, WEBP, HEIC বা HEIF ফাইল আপলোড করুন!');
            this.value = '';
            return;
        }
        
        // Show processing status
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        imageProcessingStatus.style.display = 'block';
        imageProgress.style.width = '10%';
        
        if (fileExt === 'heic' || fileExt === 'heif') {
            imageStatusText.textContent = `HEIC/HEIF ইমেজ (${fileSizeMB} MB) কনভার্ট করা হচ্ছে...`;
        } else {
            imageStatusText.textContent = `ইমেজ (${fileSizeMB} MB) অপ্টিমাইজ করা হচ্ছে...`;
        }
        
        // Process the image
        processImage(file, imageDataInput, imageProgress, imageStatusText, imageProcessingStatus, imagePreview);
    });
    
    function processImage(file, dataInput, progress, statusText, processingStatus, preview) {
        const originalSize = file.size;
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        if ((fileExt === 'heic' || fileExt === 'heif') && typeof heic2any !== 'undefined') {
            convertHeicToJpeg(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
        } else {
            loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
        }
    }
    
    function convertHeicToJpeg(file, originalSize, dataInput, progress, statusText, processingStatus, preview) {
        progress.style.width = '20%';
        
        const fileReader = new FileReader();
        fileReader.onload = function(event) {
            const arrayBuffer = event.target.result;
            
            heic2any({
                blob: new Blob([arrayBuffer]),
                toType: 'image/jpeg',
                quality: 0.8
            }).then(function(jpegBlob) {
                progress.style.width = '40%';
                statusText.textContent = 'HEIC কনভার্ট সফল! এখন অপ্টিমাইজ করা হচ্ছে...';
                loadImageWithOrientation(jpegBlob, originalSize, dataInput, progress, statusText, processingStatus, preview);
            }).catch(function(err) {
                console.error('HEIC conversion error:', err);
                statusText.textContent = 'HEIC কনভার্ট করতে সমস্যা! সাধারণ পদ্ধতি চেষ্টা করা হচ্ছে...';
                loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview);
            });
        };
        
        fileReader.readAsArrayBuffer(file);
    }
    
    function loadImageWithOrientation(file, originalSize, dataInput, progress, statusText, processingStatus, preview) {
        progress.style.width = '50%';
        
        const urlReader = new FileReader();
        urlReader.onload = function(event) {
            const img = new Image();
            
            img.onload = function() {
                progress.style.width = '60%';
                
                let width = img.width;
                let height = img.height;
                let targetWidth = width;
                let targetHeight = height;
                
                if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                    if (width > height) {
                        targetHeight = Math.round(height * (MAX_WIDTH / width));
                        targetWidth = MAX_WIDTH;
                    } else {
                        targetWidth = Math.round(width * (MAX_HEIGHT / height));
                        targetHeight = MAX_HEIGHT;
                    }
                }
                
                const canvas = document.createElement('canvas');
                canvas.width = targetWidth;
                canvas.height = targetHeight;
                const ctx = canvas.getContext('2d');
                
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, targetWidth, targetHeight);
                ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                
                let targetQuality = QUALITY;
                let fileSizeMB = file.size / (1024 * 1024);
                
                if (fileSizeMB > 10) targetQuality = 0.5;
                else if (fileSizeMB > 5) targetQuality = 0.6;
                
                progress.style.width = '90%';
                
                canvas.toBlob(function(blob) {
                    finalizeImageProcessing(blob, originalSize, dataInput, statusText, processingStatus, preview);
                }, 'image/jpeg', targetQuality);
            };
            
            img.src = event.target.result;
        };
        
        urlReader.readAsDataURL(file);
    }
    
    function finalizeImageProcessing(blob, originalSize, dataInput, statusText, processingStatus, preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.style.border = '3px solid #28a745';
            }
            
            const compressedSize = blob.size;
            const compressionRatio = Math.round((1 - (compressedSize / originalSize)) * 100);
            statusText.innerHTML = `<i class="fas fa-check-circle"></i> Optimization complete! <span class="text-success">(${formatFileSize(originalSize)} → ${formatFileSize(compressedSize)}, ${compressionRatio}% Reduced Success!)</span>`;
            statusText.style.color = '#28a745';
            processingStatus.style.display = 'none';
        };
        reader.readAsDataURL(blob);
        
        const dataReader = new FileReader();
        dataReader.onload = function(e) {
            dataInput.value = e.target.result;
        };
        dataReader.readAsDataURL(blob);
    }
    
    function formatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + " B";
        } else if (bytes < 1048576) {
            return (bytes / 1024).toFixed(1) + " KB";
        } else {
            return (bytes / 1048576).toFixed(2) + " MB";
        }
    }
}

// Initialize for Create Post Modal
document.addEventListener('DOMContentLoaded', function() {
    setupImageProcessing('formFile', 'imageData', 'imageProcessingStatus', 'imageProgress', 'imageStatusText', 'mainThmb');
    
    // Initialize for Edit Post Modal
    setupImageProcessing('editFormFile', 'editImageData', 'editImageProcessingStatus', 'editImageProgress', 'editImageStatusText', 'edit_current_image');
});
</script>

<div class="mb-3" id="edit_discount_field_container">
                <label class="form-label">Discount Offer <span class="text-danger">*</span></label>
                
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <!-- Discount Amount-->
                    <input type="number" 
                        class="form-control" 
                        id="discount_price" 
                        name="discount_price" 
                        min="0" 
                        step="0.01" 
                        placeholder="Discount Amount">

                    <!-- Discount Duration -->
                    <input type="number" 
                        class="form-control d-none" 
                        id="discount_days" 
                        name="discount_days" 
                        min="1" 
                        placeholder="Duration (days)">

                    <!-- Discount End Datetime -->
                    <input type="datetime-local" 
                        class="form-control d-none" 
                        id="discount_until" 
                        name="discount_until" 
                        placeholder="Valid Until">
                </div>
                </div>

               <div class="mb-3">
                  <label for="description" class="form-label" id="description_label">Product or Service Description</label>
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

{{-- Edit Post Modal এবং বাকি JavaScript code আগের মতোই থাকবে --}}

{{-- Modal and Category JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto scroll active item to center
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
                if (scrollContainer) {
                    scrollToCenter(this, scrollContainer);
                }
            }
        });
    });
});

function scrollToCenter(element, container) {
    if (!element || !container) return;
    
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
           
           // Check cat_type and update form accordingly
           const selectedCategory = categories.find(cat => cat.id == id);
           if (selectedCategory) {
               updateFormBasedOnCategoryType(selectedCategory.cat_type, 'create');
           }
           
           toggleSubmit();
       }
       
       // Function to update form based on category type
       window.updateFormBasedOnCategoryType = function(catType, formType) {
           const prefix = formType === 'create' ? '' : 'edit_';
           const titleLabel = document.getElementById(prefix + 'title_label');
           const descriptionLabel = document.getElementById(prefix + 'description_label');
           const priceContainer = document.getElementById(prefix + 'price_field_container');
           const priceInput = document.getElementById(prefix + 'price');
           const discountContainer = document.getElementById(prefix + 'discount_field_container');
           
           if (catType === 'post') {
               // Hide price field
               if (priceContainer) priceContainer.style.display = 'none';
               if (priceInput) {
                   priceInput.removeAttribute('required');
                   priceInput.value = '';
               }
               
               // Hide discount field (for edit modal)
               if (discountContainer) discountContainer.style.display = 'none';
               
               // Update labels
               if (titleLabel) {
                   titleLabel.innerHTML = 'Notice Title <span class="text-danger">*</span>';
               }
               if (descriptionLabel) {
                   descriptionLabel.textContent = 'Notice Description';
               }
           } else {
               // Show price field
               if (priceContainer) priceContainer.style.display = 'block';
               if (priceInput) {
                   priceInput.setAttribute('required', 'required');
               }
               
               // Show discount field (for edit modal)
               if (discountContainer) discountContainer.style.display = 'block';
               
               // Reset labels to default
               if (titleLabel) {
                   titleLabel.innerHTML = 'Title <span class="text-danger">*</span>';
               }
               if (descriptionLabel) {
                   descriptionLabel.textContent = 'Product or Service Description';
               }
           }
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
                   // Update form based on category type
                   updateFormBasedOnCategoryType(exactMatch.cat_type, 'create');
               } else {
                   categoryIdInput.value = ''; // Clear category_id for new category
                   // Reset to default when no category selected
                   updateFormBasedOnCategoryType('product', 'create');
               }
           } else {
               suggestionsDiv.style.display = 'none';
               categoryIdInput.value = '';
               // Reset to default when cleared
               updateFormBasedOnCategoryType('product', 'create');
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
       
       if (titleInput && categoryInput && submitBtn) {
           // Get selected category type
           const selectedCategoryId = document.getElementById('category_id').value;
           const selectedCategory = categories.find(cat => cat.id == selectedCategoryId);
           const isPostType = selectedCategory && selectedCategory.cat_type === 'post';
           
           // Check if all required fields are filled
           let hasRequiredFields = titleInput.value.trim() !== '' && 
                                   categoryInput.value.trim() !== '';
           
           // Price is only required if not post type
           if (!isPostType) {
               hasRequiredFields = hasRequiredFields && (priceInput && priceInput.value.trim() !== '');
           }
           
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
       
       // Reset form when create modal opens
       const createModal = document.getElementById('createPostModal');
       if (createModal) {
           createModal.addEventListener('show.bs.modal', function() {
               // Reset to default state
               if (window.updateFormBasedOnCategoryType) {
                   updateFormBasedOnCategoryType('product', 'create');
               }
           });
       }
       
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
@include('frontend.body.review-cdn')

@endsection