@extends("frontend.master")
@section('main-content')
<div class="container mt-4">
<!-- Dashboard Content -->
<div class="row mt-3">
   @auth
   {{-- Include Profile Card Partial --}}
   @include('frontend.profile-card')
   
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
      <!-- Horizontal Scrollable Navigation -->
      <div class="scroll-container mb-4">
         <div class="scroll-content">
            {{-- Product & Services Link --}}
            @php
            // cat_type post table e ase, but category related info category table theke check korte hobe
            $productServiceCategoryIds = \App\Models\Category::whereIn('cat_type', ['product', 'service'])->pluck('id');
            $hasProductServices = \App\Models\Post::where('user_id', $user->id)
                ->whereIn('category_id', $productServiceCategoryIds)
                ->exists();
            @endphp

            @auth
                @if(Auth::id() === $user->id || $hasProductServices)
                    <a href="/{{ $user->username }}/products-services" class="nav-item-custom">
                        <span><i class="bi bi-cart"></i></span>
                        <span>Product & Services</span>
                    </a>
                @endif
            @endauth
            @guest
                @if($hasProductServices)
                    <a href="/{{ $user->username }}/products-services" class="nav-item-custom">
                        <span><i class="bi bi-cart"></i></span>
                        <span>Product & Services</span>
                    </a>
                @endif
            @endguest

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
            }
            // যদি কোনো category navigation না থাকে, তাহলে parent categories দেখান
            if($navCategories->isEmpty()) {
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
            <span><i class="bi bi-file-post"></i></span>
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
          <select class="form-select" id="category_name" name="category_name" required>
             <option value="">Select a category...</option>
             @foreach($categories as $category)
                @if($category->cat_type === 'post')
                   <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                @endif
             @endforeach
          </select>
          <input type="hidden" id="category_id" name="category_id" value="">
       </div>

       {{-- Title Field --}}
       <div class="mb-3">
          <label for="title" class="form-label">Post Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="title" name="title" placeholder="Enter post title..." required>
       </div>

       {{-- Media Upload --}}
       <div class="mb-4">
          <label for="media" class="form-label">Choose Media (Images/Videos)</label>
          <input type="file" name="media[]" class="form-control" id="mediaInput" multiple accept="image/*,video/*">
          <small class="text-muted">Videos must be 60 seconds or less and under 500MB.</small>
          
          {{-- Validation Status --}}
          <div id="mediaValidationStatus" style="display: none;" class="mt-2">
             <div class="alert alert-info mb-0" role="alert">
                <small id="mediaStatusText">Checking files...</small>
             </div>
          </div>
          
          {{-- Media Preview --}}
          <div id="mediaPreviewContainer" class="mt-3 row g-2"></div>
       </div>

       {{-- Description Field --}}
       <div class="mb-3">
          <label for="description" class="form-label">Post Description</label>
          <textarea class="form-control" id="description" name="description" rows="4" placeholder="Type your text here..."></textarea>
       </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const MAX_VIDEO_DURATION = 60; // seconds
    const MAX_VIDEO_SIZE_MB = 500;
    const MAX_VIDEO_SIZE_BYTES = MAX_VIDEO_SIZE_MB * 1024 * 1024;
    
    const mediaInput = document.getElementById('mediaInput');
    const mediaValidationStatus = document.getElementById('mediaValidationStatus');
    const mediaStatusText = document.getElementById('mediaStatusText');
    const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
    const submitBtn = document.getElementById('submitBtn');
    const createPostForm = document.getElementById('createPostForm');
    const createPostModal = document.getElementById('createPostModal');
    
    let validFiles = [];
    let bsModal = null;
    
    // Initialize Bootstrap modal instance
    if (createPostModal) {
        bsModal = new bootstrap.Modal(createPostModal);
    }
    
    // Media input change handler
    mediaInput.addEventListener('change', async function(e) {
        const files = Array.from(this.files);
        if (files.length === 0) return;
        
        validFiles = [];
        mediaPreviewContainer.innerHTML = '';
        mediaValidationStatus.style.display = 'block';
        mediaStatusText.textContent = 'Validating files...';
        
        // Validate each file
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileType = file.type.split('/')[0];
            
            if (fileType === 'image') {
                // Images are always valid
                validFiles.push(file);
                addMediaPreview(file, 'image', validFiles.length - 1);
            } else if (fileType === 'video') {
                // Check video size
                if (file.size > MAX_VIDEO_SIZE_BYTES) {
                    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                    alert(`ভিডিও "${file.name}" খুব বড় (${fileSizeMB}MB)। অনুগ্রহ করে ${MAX_VIDEO_SIZE_MB}MB বা তার কম আকারের ভিডিও আপলোড করুন।\n\nVideo "${file.name}" is too large (${fileSizeMB}MB). Please upload videos ${MAX_VIDEO_SIZE_MB}MB or less.`);
                    continue;
                }
                
                // Check video duration
                try {
                    const duration = await getVideoDuration(file);
                    
                    if (duration > MAX_VIDEO_DURATION) {
                        alert(`ভিডিও "${file.name}" খুব বড় (${duration.toFixed(1)} সেকেন্ড)। দয়া করে ${MAX_VIDEO_DURATION} সেকেন্ড বা তার কম সময়ের ভিডিও আপলোড করুন।\n\nVideo "${file.name}" is too long (${duration.toFixed(1)} seconds). Please upload videos ${MAX_VIDEO_DURATION} seconds or less.`);
                        continue;
                    }
                    
                    // Video is valid
                    validFiles.push(file);
                    addMediaPreview(file, 'video', validFiles.length - 1);
                } catch (error) {
                    console.error('Video validation failed:', error);
                    alert('Could not validate video: ' + file.name);
                }
            }
        }
        
        if (validFiles.length > 0) {
            mediaStatusText.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${validFiles.length} file(s) ready to upload`;
            submitBtn.disabled = false;
        } else {
            mediaStatusText.innerHTML = `<i class="fas fa-exclamation-circle text-danger"></i> No valid files selected`;
            submitBtn.disabled = true;
        }
        
        setTimeout(() => {
            mediaValidationStatus.style.display = 'none';
        }, 2000);
    });
    
    // Get video duration
    function getVideoDuration(file) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                resolve(video.duration);
            };
            video.onerror = () => reject(new Error('Could not load video'));
            video.src = URL.createObjectURL(file);
        });
    }
    
    // Add media preview
    function addMediaPreview(file, type, index) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3';
        col.setAttribute('data-index', index);
        
        const url = URL.createObjectURL(file);
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        
        if (type === 'image') {
            col.innerHTML = `
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">
                    <span class="badge bg-primary position-absolute top-0 start-0 m-1">Image</span>
                    <span class="badge bg-secondary position-absolute bottom-0 start-0 m-1">${sizeMB}MB</span>
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeMedia(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        } else {
            col.innerHTML = `
                <div class="position-relative">
                    <video src="${url}" class="w-100 rounded" style="height: 150px; object-fit: cover;"></video>
                    <span class="badge bg-success position-absolute top-0 start-0 m-1">Video</span>
                    <span class="badge bg-secondary position-absolute bottom-0 start-0 m-1">${sizeMB}MB</span>
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeMedia(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
        
        mediaPreviewContainer.appendChild(col);
    }
    
    // Remove media
    window.removeMedia = function(index) {
        validFiles.splice(index, 1);
        
        // Re-render all previews with updated indices
        mediaPreviewContainer.innerHTML = '';
        validFiles.forEach((file, idx) => {
            const fileType = file.type.split('/')[0];
            addMediaPreview(file, fileType, idx);
        });
        
        if (validFiles.length === 0) {
            submitBtn.disabled = true;
            mediaInput.value = '';
        }
    };
    
    // Form submission with AJAX
    createPostForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
        
        // Create FormData
        const formData = new FormData(this);
        
        // Remove old media input and add valid files
        formData.delete('media[]');
        validFiles.forEach(file => {
            formData.append('media[]', file);
        });
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (response.ok && result.success) {
                // Close modal
                if (bsModal) {
                    bsModal.hide();
                }
                
                // Reset form
                createPostForm.reset();
                mediaPreviewContainer.innerHTML = '';
                validFiles = [];
                
                // Show success notification
                showNotification('Post is uploading in background! It will appear on your profile when ready.', 'success');
                
                // Optional: Reload page after 2 seconds to show the post
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            } else {
                throw new Error(result.message || 'Upload failed');
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            console.error('Error details:', error.message);
            showNotification('Upload failed. Check console for details.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Create Post';
        }
    });
    
    // Show notification function
    function showNotification(message, type = 'info') {
        // Create toast element
        const toastHTML = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Add to body
        const toastContainer = document.createElement('div');
        toastContainer.innerHTML = toastHTML;
        document.body.appendChild(toastContainer);
        
        // Initialize and show toast
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        toast.show();
        
        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastContainer.remove();
        });
    }
    
    // Reset form when modal closes
    if (createPostModal) {
        createPostModal.addEventListener('hidden.bs.modal', function() {
            createPostForm.reset();
            mediaPreviewContainer.innerHTML = '';
            validFiles = [];
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Create Post';
            mediaValidationStatus.style.display = 'none';
        });
    }
});
</script>

<style>
.badge {
    font-size: 10px;
}
</style>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="createPostForm" class="btn btn-primary" id="submitBtn" disabled>Create Post</button>
         </div>
      </div>
   </div>
</div>
@endif
@endauth

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category select change event - set hidden category_id field
    const categorySelect = document.getElementById('category_name');
    const categoryIdInput = document.getElementById('category_id');
    
    if (categorySelect && categoryIdInput) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const categoryName = selectedOption.value;
            
            if (categoryName) {
                // Find category ID from categories data
                const categories = @json($categories ?? []);
                const selectedCategory = categories.find(cat => cat.category_name === categoryName);
                
                if (selectedCategory) {
                    categoryIdInput.value = selectedCategory.id;
                }
            } else {
                categoryIdInput.value = '';
            }
            
            toggleSubmit();
        });
    }
    
    function toggleSubmit() {
        const titleInput = document.getElementById('title');
        const categorySelect = document.getElementById('category_name');
        const imageInput = document.getElementById('formFile');
        const descInput = document.getElementById('description');
        const submitBtn = document.getElementById('submitBtn');
        
        if (titleInput && categorySelect && submitBtn) {
            // Check if all required fields are filled
            const hasRequiredFields = titleInput.value.trim() !== '' && 
                                      categorySelect.value !== '';
            
            // Check if at least image or description is provided
            const hasContent = (imageInput && imageInput.files.length > 0) || 
                              (descInput && descInput.value.trim() !== '');
            
            submitBtn.disabled = !(hasRequiredFields && hasContent);
        }
    }
    
    // Event listeners for form validation
    const titleInput = document.getElementById('title');
    const imageInput = document.getElementById('formFile');
    const descInput = document.getElementById('description');
    
    if (titleInput) titleInput.addEventListener('input', toggleSubmit);
    if (imageInput) imageInput.addEventListener('change', toggleSubmit);
    if (descInput) descInput.addEventListener('input', toggleSubmit);
    
    // Reset form when modal opens
    const createModal = document.getElementById('createPostModal');
    if (createModal) {
        createModal.addEventListener('show.bs.modal', function() {
            // Reset form
            document.getElementById('createPostForm').reset();
            if (categoryIdInput) categoryIdInput.value = '';
            toggleSubmit();
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



   document.addEventListener('DOMContentLoaded', function () {
    const scrollContainer = document.querySelector('.scroll-container');
    const activeItem = document.querySelector('.nav-item-custom.active');

    if (scrollContainer && activeItem) {
        const containerRect = scrollContainer.getBoundingClientRect();
        const itemRect = activeItem.getBoundingClientRect();

        // Calculate center position
        const scrollLeft = 
            (itemRect.left + itemRect.width / 2) - 
            (containerRect.left + containerRect.width / 2);

        scrollContainer.scrollTo({
            left: scrollContainer.scrollLeft + scrollLeft,
            behavior: 'smooth'
        });
    }
    });

</script>
@include('frontend.body.review-cdn')
@endsection