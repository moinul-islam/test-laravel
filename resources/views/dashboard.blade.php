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
                    <a href="/{{ $user->username }}/products-services" class="nav-item-custom animated-rgb-border-color">
                        <span><i class="bi bi-cart animated-rgb-text-color"></i></span>
                    </a>
                @endif
            @endauth
            @guest
                @if($hasProductServices)
                    <a href="/{{ $user->username }}/products-services" class="nav-item-custom animated-rgb-border-color">
                        <span><i class="bi bi-cart animated-rgb-text-color"></i></span>
                    </a>
                @endif
            @endguest

            <style>
            @keyframes rgb-border-bg-color {
                0% { 
                    border-color: #0d6efd; 
                    background-color: rgba(13,110,253,0.08); 
                    color: #0d6efd;
                }
                20% { 
                    border-color: #198754; 
                    background-color: rgba(25,135,84,0.08);
                    color: #198754;
                }
                40% { 
                    border-color: #ffc107; 
                    background-color: rgba(255,193,7,0.08);
                    color: #ffc107;
                }
                60% { 
                    border-color: #dc3545; 
                    background-color: rgba(220,53,69,0.08);
                    color: #dc3545;
                }
                80% { 
                    border-color: #6610f2; 
                    background-color: rgba(102,16,242,0.08);
                    color: #6610f2;
                }
                100% { 
                    border-color: #0d6efd; 
                    background-color: rgba(13,110,253,0.08); 
                    color: #0d6efd;
                }
            }
            .animated-rgb-border-color {
                animation: rgb-border-bg-color 2s linear infinite;
                background-color: rgba(13,110,253,0.08);
                transition: background 0.3s, border-color 0.3s, color 0.3s;
            }
            
            </style>

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
            <span>All</span>
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

       {{-- Title Field - Removed --}}

       {{-- Image & Video Upload (Max 5 Images + 1 Video) --}}
       <div class="mb-4">
          <label for="media" class="form-label">Choose Images & Videos</label>
          <input type="file" name="media[]" class="form-control" id="mediaInput" multiple accept="image/*,video/*,.heic,.heif">
          <small class="text-muted">Upload at least one image/video OR fill the description field. Max 5 images + 1 video (max 20MB). Formats: JPG, PNG, HEIC, HEIF, MP4, MOV</small>
          
          {{-- Hidden field for processed media data --}}
          <input type="hidden" name="media_data" id="mediaData">
          
          {{-- Processing Status --}}
          <div id="mediaProcessingStatus" style="display: none;" class="mt-2">
             <div class="progress mb-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="mediaProgress"></div>
             </div>
             <small id="mediaStatusText">Processing media...</small>
          </div>
          
          {{-- Media Preview --}}
          <div id="mediaPreviewContainer" class="mt-3 row g-2"></div>
       </div>

       <div class="alert alert-warning mb-3" id="nonAreaPostWarning" style="display:none;">
           <i class="bi bi-exclamation-triangle"></i>
           এলাকার পোস্ট বাদে অন্য কোনো পোস্ট করলে পোস্ট রিমুভ হয়ে যাবে।
           <br>
           <small>Any post outside your area will be removed automatically.</small>
       </div>
       <script>
       document.addEventListener('DOMContentLoaded', function() {
           const categorySelect = document.getElementById('category_name');
           const warningDiv = document.getElementById('nonAreaPostWarning');
           const areaCategoryNames = @json($categories->where('cat_type', 'post')->where('is_area', 1)->pluck('category_name')->toArray());
           
           categorySelect && categorySelect.addEventListener('change', function() {
               const selected = this.value;
               // Show warning if not an area category
               if (selected && !areaCategoryNames.includes(selected)) {
                   warningDiv.style.display = '';
               } else {
                   warningDiv.style.display = 'none';
               }
           });
       });
       </script>

       {{-- Description Field --}}
       <div class="mb-3">
          <label for="description" class="form-label">Post Description</label>
          <textarea class="form-control" id="description" name="description" rows="4" placeholder="Type your text here..."></textarea>
       </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const MAX_WIDTH = 1800;
    const MAX_HEIGHT = 1800;
    const IMAGE_QUALITY = 0.7;
    const MAX_IMAGES = 5;
    const MAX_VIDEOS = 1;
    const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50MB
    
    const mediaInput = document.getElementById('mediaInput');
    const mediaDataInput = document.getElementById('mediaData');
    const mediaProcessingStatus = document.getElementById('mediaProcessingStatus');
    const mediaProgress = document.getElementById('mediaProgress');
    const mediaStatusText = document.getElementById('mediaStatusText');
    const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
    
    let processedMediaArray = [];
    let currentFileIndex = 0;
    let totalFiles = 0;
    
    mediaInput.addEventListener('change', async function(e) {
        const files = Array.from(this.files);
        if (files.length === 0) return;
        
        // Separate images and videos
        const imageFiles = files.filter(f => f.type.startsWith('image/') || f.name.match(/\.(heic|heif)$/i));
        const videoFiles = files.filter(f => f.type.startsWith('video/'));
        
        // Count current media
        const currentImages = processedMediaArray.filter(m => m.type === 'image').length;
        const currentVideos = processedMediaArray.filter(m => m.type === 'video').length;
        
        // Check limits
        if (currentImages + imageFiles.length > MAX_IMAGES) {
            alert(`আপনি সর্বোচ্চ ${MAX_IMAGES}টি ছবি আপলোড করতে পারবেন। বর্তমানে ${currentImages}টি ছবি আছে।\n\nYou can upload a maximum of ${MAX_IMAGES} images. Currently you have ${currentImages} image(s).`);
            this.value = '';
            return;
        }
        
        if (currentVideos + videoFiles.length > MAX_VIDEOS) {
            alert(`আপনি সর্বোচ্চ ${MAX_VIDEOS}টি ভিডিও আপলোড করতে পারবেন।\n\nYou can upload a maximum of ${MAX_VIDEOS} video.`);
            this.value = '';
            return;
        }
        
        // Check video size
        for (let video of videoFiles) {
            if (video.size > MAX_VIDEO_SIZE) {
                alert(`ভিডিও সাইজ সর্বোচ্চ 20MB হতে পারবে। "${video.name}" অনেক বড়।\n\nVideo size must be max 20MB. "${video.name}" is too large.`);
                this.value = '';
                return;
            }
        }
        
        mediaProcessingStatus.style.display = 'block';
        totalFiles = files.length;
        currentFileIndex = 0;
        
        // Process files one by one
        for (let i = 0; i < files.length; i++) {
            currentFileIndex = i;
            mediaProgress.style.width = ((i / files.length) * 100) + '%';
            const file = files[i];
            
            const isVideo = file.type.startsWith('video/');
            mediaStatusText.textContent = `Processing ${isVideo ? 'video' : 'image'} ${i + 1} of ${files.length}...`;
            
            try {
                if (isVideo) {
                    const processedBase64 = await processVideo(file);
                    processedMediaArray.push({
                        type: 'video',
                        data: processedBase64
                    });
                    addMediaPreview(processedBase64, 'video', processedMediaArray.length - 1);
                } else {
                    const processedBase64 = await processImage(file);
                    processedMediaArray.push({
                        type: 'image',
                        data: processedBase64
                    });
                    addMediaPreview(processedBase64, 'image', processedMediaArray.length - 1);
                }
            } catch (error) {
                console.error('Media processing failed:', error);
                alert('Media processing failed: ' + file.name + '\nError: ' + error.message);
            }
        }
        
        mediaProgress.style.width = '100%';
        const imgCount = processedMediaArray.filter(m => m.type === 'image').length;
        const vidCount = processedMediaArray.filter(m => m.type === 'video').length;
        mediaStatusText.innerHTML = `<i class="fas fa-check-circle text-success"></i> All media processed! (${imgCount}/${MAX_IMAGES} images, ${vidCount}/${MAX_VIDEOS} video)`;
        mediaDataInput.value = JSON.stringify(processedMediaArray);
        
        this.value = '';
        
        setTimeout(() => {
            mediaProcessingStatus.style.display = 'none';
        }, 2000);
    });
    
    // Process video - just read as base64
    async function processVideo(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                resolve(e.target.result);
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Read file as base64
    function readFileAsBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Process image
    async function processImage(file) {
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        if (fileExt === 'heic' || fileExt === 'heif') {
            const jpegBlob = await convertHeicToJpeg(file);
            return await compressImage(jpegBlob);
        } else {
            return await compressImage(file);
        }
    }
    
    function convertHeicToJpeg(file) {
        return new Promise((resolve, reject) => {
            const fileReader = new FileReader();
            fileReader.onload = function(event) {
                heic2any({
                    blob: new Blob([event.target.result]),
                    toType: 'image/jpeg',
                    quality: 0.8
                }).then(resolve).catch(reject);
            };
            fileReader.onerror = reject;
            fileReader.readAsArrayBuffer(file);
        });
    }
    
    function compressImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                        if (width > height) {
                            height = Math.round(height * (MAX_WIDTH / width));
                            width = MAX_WIDTH;
                        } else {
                            width = Math.round(width * (MAX_HEIGHT / height));
                            height = MAX_HEIGHT;
                        }
                    }
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob(blob => {
                        const blobReader = new FileReader();
                        blobReader.onload = () => resolve(blobReader.result);
                        blobReader.onerror = reject;
                        blobReader.readAsDataURL(blob);
                    }, 'image/jpeg', IMAGE_QUALITY);
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Add media preview
    function addMediaPreview(base64, type, index) {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3';
        
        const mediaElement = type === 'video' 
            ? `<video src="${base64}" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;" controls></video>`
            : `<img src="${base64}" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">`;
        
        const badgeText = type === 'video' ? 'Video' : `Image ${processedMediaArray.filter((m, i) => i <= index && m.type === 'image').length}`;
        
        col.innerHTML = `
            <div class="position-relative">
                ${mediaElement}
                <span class="badge bg-${type === 'video' ? 'success' : 'primary'} position-absolute top-0 start-0 m-1">${badgeText}</span>
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeMedia(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        mediaPreviewContainer.appendChild(col);
    }
    
    // Remove media
    window.removeMedia = function(index) {
        processedMediaArray.splice(index, 1);
        mediaDataInput.value = JSON.stringify(processedMediaArray);
        
        // Re-render all previews with updated indices
        mediaPreviewContainer.innerHTML = '';
        processedMediaArray.forEach((media, idx) => {
            addMediaPreview(media.data, media.type, idx);
        });
        
        // Show updated count
        if (processedMediaArray.length > 0) {
            const imgCount = processedMediaArray.filter(m => m.type === 'image').length;
            const vidCount = processedMediaArray.filter(m => m.type === 'video').length;
            mediaStatusText.innerHTML = `${imgCount}/${MAX_IMAGES} images, ${vidCount}/${MAX_VIDEOS} video uploaded`;
            mediaProcessingStatus.style.display = 'block';
        }
    };
    
    // Form submission handler with validation
    const form = document.getElementById('createPostForm');
    const submitBtn = document.getElementById('submitBtn');
    const categorySelect = document.getElementById('category_name');
    const descriptionField = document.getElementById('description');
    
    function validateForm() {
        const categoryFilled = categorySelect && categorySelect.value !== '';
        const descriptionFilled = descriptionField && descriptionField.value.trim() !== '';
        const mediaFilled = processedMediaArray.length > 0;
        
        const isValid = categoryFilled && (descriptionFilled || mediaFilled);
        
        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }
        
        return isValid;
    }
    
    if (categorySelect) {
        categorySelect.addEventListener('change', validateForm);
    }
    
    if (descriptionField) {
        descriptionField.addEventListener('input', validateForm);
    }
    
    const originalAddMediaPreview = addMediaPreview;
    window.addMediaPreview = function(...args) {
        originalAddMediaPreview.apply(this, args);
        validateForm();
    };
    
    const originalRemoveMedia = window.removeMedia;
    window.removeMedia = function(index) {
        originalRemoveMedia(index);
        validateForm();
    };
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Check if media is still processing
            if (mediaProcessingStatus.style.display !== 'none' && mediaStatusText.textContent.includes('Processing')) {
                alert('Please wait for media processing to complete!');
                return false;
            }
            
            // Check validation
            if (!validateForm()) {
                alert('অনুগ্রহ করে Category নির্বাচন করুন এবং Description অথবা Image/Video যোগ করুন\n\nPlease select a Category and add either Description or Images/Videos');
                return false;
            }
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            
            // Show uploading message
            showToast('আপনার পোস্ট আপলোড হচ্ছে... / Your post is uploading...', 'info');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
            if (modal) {
                modal.hide();
            }
            
            // Prepare form data
            const formData = new FormData(form);
            
            try {
                // Send AJAX request
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Success
                    showToast('পোস্ট সফলভাবে তৈরি হয়েছে! / Post created successfully!', 'success');
                    
                    // Reset form
                    form.reset();
                    processedMediaArray = [];
                    mediaPreviewContainer.innerHTML = '';
                    mediaDataInput.value = '';
                    
                    // Reload page after 1.5 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Error
                    showToast(result.message || 'পোস্ট তৈরিতে সমস্যা হয়েছে / Error creating post', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span id="submitBtnText">Create Post</span>';
                }
            } catch (error) {
                console.error('Upload error:', error);
                showToast('নেটওয়ার্ক সমস্যা / Network error', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span id="submitBtnText">Create Post</span>';
            }
        });
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove any existing toast
        const existingToast = document.getElementById('uploadToast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        const toastHtml = `
            <div id="uploadToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast show ${bgColor} text-white" role="alert">
                    <div class="toast-body d-flex align-items-center">
                        <i class="fas ${icon} me-2"></i>
                        <span>${message}</span>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const toast = document.getElementById('uploadToast');
            if (toast) {
                toast.remove();
            }
        }, 5000);
    }
    
    // Initial validation check
    validateForm();
});
</script>

<style>
.progress {
    height: 25px;
}
.progress-bar {
    font-size: 14px;
    line-height: 25px;
}
.badge {
    font-size: 10px;
}
.toast {
    min-width: 300px;
}
</style>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="createPostForm" class="btn btn-primary" id="submitBtn" disabled>
                <span id="submitBtnText">Create Post</span>
            </button>
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
        const categorySelect = document.getElementById('category_name');
        const imageInput = document.getElementById('mediaInput'); // Changed from 'formFile' to 'mediaInput'
        const descInput = document.getElementById('description');
        const submitBtn = document.getElementById('submitBtn');
        const mediaDataInput = document.getElementById('mediaData'); // Check processed images
        
        if (categorySelect && submitBtn) {
            // Check if category is filled (REQUIRED)
            const categoryFilled = categorySelect.value !== '';
            
            // Check if at least image or description is provided
            let hasImages = false;
            
            // Check both raw file input and processed media data
            if (imageInput && imageInput.files.length > 0) {
                hasImages = true;
            }
            
            // Also check if processed images exist
            if (mediaDataInput && mediaDataInput.value) {
                try {
                    const processedMedia = JSON.parse(mediaDataInput.value);
                    if (Array.isArray(processedMedia) && processedMedia.length > 0) {
                        hasImages = true;
                    }
                } catch (e) {
                    // Ignore JSON parse errors
                }
            }
            
            const hasDescription = descInput && descInput.value.trim() !== '';
            
            // Enable button if: Category is filled AND (has images OR has description)
            submitBtn.disabled = !(categoryFilled && (hasImages || hasDescription));
        }
    }

    // Event listeners for form validation
    const imageInput = document.getElementById('mediaInput'); // Changed from 'formFile'
    const descInput = document.getElementById('description');
    
    if (categorySelect) categorySelect.addEventListener('change', toggleSubmit);
    if (imageInput) imageInput.addEventListener('change', toggleSubmit);
    if (descInput) descInput.addEventListener('input', toggleSubmit);
    
    // Also listen to mediaData changes (when images are processed)
    const mediaDataInput = document.getElementById('mediaData');
    if (mediaDataInput) {
        // Create a MutationObserver to watch for changes to the hidden input
        const observer = new MutationObserver(toggleSubmit);
        observer.observe(mediaDataInput, { attributes: true, attributeFilter: ['value'] });
        
        // Also manually trigger when value changes
        const originalValueSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
        Object.defineProperty(mediaDataInput, 'value', {
            set: function(val) {
                originalValueSetter.call(this, val);
                toggleSubmit();
            },
            get: function() {
                return this.getAttribute('value');
            }
        });
    }

    // Reset form when modal opens
    const createModal = document.getElementById('createPostModal');
    if (createModal) {
        createModal.addEventListener('show.bs.modal', function() {
            // Reset form
            const form = document.getElementById('createPostForm');
            if (form) form.reset();
            
            if (categoryIdInput) categoryIdInput.value = '';
            if (mediaDataInput) mediaDataInput.value = '';
            
            // Clear image preview
            const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
            if (mediaPreviewContainer) mediaPreviewContainer.innerHTML = '';
            
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