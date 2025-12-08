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

       {{-- Single Media Upload (Images + Videos) --}}
       <div class="mb-4">
          <label for="media" class="form-label">Choose Media (Images/Videos)</label>
          <input type="file" name="media[]" class="form-control" id="mediaInput" multiple accept="image/*,video/*,.heic,.heif">
          <small class="text-muted">Select multiple images/videos. Videos longer than 60 seconds will need to be trimmed.</small>
          
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

       {{-- Description Field --}}
       <div class="mb-3">
          <label for="description" class="form-label">Post Description</label>
          <textarea class="form-control" id="description" name="description" rows="4" placeholder="Type your text here..."></textarea>
       </div>
    </form>
</div>

{{-- Video Trim Modal --}}
<div class="modal fade" id="videoTrimModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Trim Video (Max 60 seconds)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeTrimModal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> This video is <strong id="videoDuration"></strong> seconds long. Please select 60 seconds or less.
                </div>
                
                <video id="trimVideoPreview" controls class="w-100 mb-3" style="max-height: 400px; background: #000;"></video>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Start Time (seconds)</label>
                        <input type="range" class="form-range" id="trimStart" min="0" step="0.1" value="0">
                        <input type="number" class="form-control mt-1" id="trimStartValue" value="0" min="0" step="0.1" style="width: 100%;">
                    </div>
                    <div class="col-6">
                        <label class="form-label">End Time (seconds)</label>
                        <input type="range" class="form-range" id="trimEnd" min="0" step="0.1" value="60">
                        <input type="number" class="form-control mt-1" id="trimEndValue" value="60" min="0" step="0.1" style="width: 100%;">
                    </div>
                </div>
                
                <div class="alert alert-info">
                    Selected duration: <strong id="selectedDuration">60</strong> seconds
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="trimVideoBtn">
                        <i class="fas fa-cut"></i> Trim Video
                    </button>
                    <button class="btn btn-secondary" id="cancelTrimBtn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Background Upload Progress Modal --}}
<div class="modal fade" id="uploadProgressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-3">Uploading Post...</h5>
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                         id="uploadProgressBar" style="width: 0%">0%</div>
                </div>
                <p class="text-muted mb-0" id="uploadStatusText">Preparing upload...</p>
            </div>
        </div>
    </div>
</div>

{{-- External Libraries --}}
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const MAX_WIDTH = 1800;
    const MAX_HEIGHT = 1800;
    const IMAGE_QUALITY = 0.7;
    const MAX_VIDEO_DURATION = 60; // seconds
    
    const mediaInput = document.getElementById('mediaInput');
    const mediaDataInput = document.getElementById('mediaData');
    const mediaProcessingStatus = document.getElementById('mediaProcessingStatus');
    const mediaProgress = document.getElementById('mediaProgress');
    const mediaStatusText = document.getElementById('mediaStatusText');
    const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
    
    let processedMediaArray = [];
    let pendingVideoFile = null;
    let currentFileIndex = 0;
    let totalFiles = 0;
    let trimModal = null;
    let uploadProgressModal = null;
    
    mediaInput.addEventListener('change', async function(e) {
        const files = Array.from(this.files);
        if (files.length === 0) return;
        
        processedMediaArray = [];
        mediaPreviewContainer.innerHTML = '';
        mediaProcessingStatus.style.display = 'block';
        totalFiles = files.length;
        currentFileIndex = 0;
        
        // Process files one by one
        for (let i = 0; i < files.length; i++) {
            currentFileIndex = i;
            mediaProgress.style.width = ((i / files.length) * 100) + '%';
            const file = files[i];
            const fileType = file.type.split('/')[0]; // 'image' or 'video'
            
            if (fileType === 'image') {
                mediaStatusText.textContent = `Processing image ${i + 1} of ${files.length}...`;
                try {
                    const processedBase64 = await processImage(file);
                    processedMediaArray.push({
                        type: 'image',
                        data: processedBase64
                    });
                    addMediaPreview(processedBase64, 'image', processedMediaArray.length - 1);
                } catch (error) {
                    console.error('Image processing failed:', error);
                    alert('Image processing failed: ' + file.name);
                }
            } else if (fileType === 'video') {
                mediaStatusText.textContent = `Processing video ${i + 1} of ${files.length}...`;
                try {
                    const duration = await getVideoDuration(file);
                    
                    if (duration > MAX_VIDEO_DURATION) {
                        // Video too long - show trim modal
                        pendingVideoFile = file;
                        await showTrimModalAndWait(file, duration);
                    } else {
                        // Video is OK - compress it using custom method
                        mediaStatusText.textContent = `Processing video ${i + 1} of ${files.length}...`;
                        const processedBase64 = await compressVideoCustom(file);
                        processedMediaArray.push({
                            type: 'video',
                            data: processedBase64
                        });
                        addMediaPreview(processedBase64, 'video', processedMediaArray.length - 1);
                    }
                } catch (error) {
                    console.error('Video processing failed:', error);
                    // Use original video if processing fails
                    const videoBase64 = await readFileAsBase64(file);
                    processedMediaArray.push({
                        type: 'video',
                        data: videoBase64
                    });
                    addMediaPreview(videoBase64, 'video', processedMediaArray.length - 1);
                }
            }
        }
        
        mediaProgress.style.width = '100%';
        mediaStatusText.innerHTML = `<i class="fas fa-check-circle text-success"></i> All ${files.length} media processed!`;
        mediaDataInput.value = JSON.stringify(processedMediaArray);
        
        setTimeout(() => {
            mediaProcessingStatus.style.display = 'none';
        }, 2000);
    });
    
    // Read file as base64 (simple, no compression)
    function readFileAsBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Get video duration
    function getVideoDuration(file) {
        return new Promise((resolve) => {
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                resolve(video.duration);
            };
            video.src = URL.createObjectURL(file);
        });
    }
    
    // Show trim modal and wait for user action - Custom System
    function showTrimModalAndWait(file, duration) {
        return new Promise((resolve) => {
            if (!trimModal) {
                trimModal = new bootstrap.Modal(document.getElementById('videoTrimModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            
            const videoPreview = document.getElementById('trimVideoPreview');
            const trimStart = document.getElementById('trimStart');
            const trimStartValue = document.getElementById('trimStartValue');
            const trimEnd = document.getElementById('trimEnd');
            const trimEndValue = document.getElementById('trimEndValue');
            const videoDuration = document.getElementById('videoDuration');
            const selectedDuration = document.getElementById('selectedDuration');
            const trimBtn = document.getElementById('trimVideoBtn');
            const cancelBtn = document.getElementById('cancelTrimBtn');
            
            // Clean up previous video URL
            if (videoPreview.src) {
                URL.revokeObjectURL(videoPreview.src);
            }
            
            const videoUrl = URL.createObjectURL(file);
            videoPreview.src = videoUrl;
            videoDuration.textContent = duration.toFixed(1);
            
            // Set max values
            const maxEnd = Math.min(MAX_VIDEO_DURATION, duration);
            trimStart.max = duration;
            trimEnd.max = duration;
            trimStart.value = 0;
            trimEnd.value = maxEnd;
            trimStartValue.value = 0;
            trimEndValue.value = maxEnd;
            
            // Reset button state
            trimBtn.disabled = false;
            trimBtn.innerHTML = '<i class="fas fa-cut"></i> Trim Video';
            
            // Update selected duration on change
            function updateDuration() {
                const start = parseFloat(trimStart.value) || 0;
                const end = parseFloat(trimEnd.value) || 0;
                const diff = Math.max(0, end - start);
                selectedDuration.textContent = diff.toFixed(1);
                
                // Sync input values
                trimStartValue.value = start.toFixed(1);
                trimEndValue.value = end.toFixed(1);
                
                // Update video preview position
                if (videoPreview.readyState >= 2) {
                    videoPreview.currentTime = start;
                }
                
                if (diff > MAX_VIDEO_DURATION || diff <= 0 || diff < 1) {
                    selectedDuration.classList.add('text-danger');
                    selectedDuration.classList.remove('text-success');
                    trimBtn.disabled = true;
                } else {
                    selectedDuration.classList.add('text-success');
                    selectedDuration.classList.remove('text-danger');
                    trimBtn.disabled = false;
                }
            }
            
            // Sync range and number inputs
            trimStart.addEventListener('input', function() {
                trimStartValue.value = this.value;
                updateDuration();
            });
            trimStartValue.addEventListener('input', function() {
                const val = Math.max(0, Math.min(parseFloat(this.value) || 0, duration));
                trimStart.value = val;
                updateDuration();
            });
            
            trimEnd.addEventListener('input', function() {
                trimEndValue.value = this.value;
                updateDuration();
            });
            trimEndValue.addEventListener('input', function() {
                const val = Math.max(0, Math.min(parseFloat(this.value) || 0, duration));
                trimEnd.value = val;
                updateDuration();
            });
            
            // Video time update to sync with start time
            videoPreview.addEventListener('loadedmetadata', function() {
                updateDuration();
            });
            
            // Remove old click handlers
            const newTrimBtn = trimBtn.cloneNode(true);
            trimBtn.parentNode.replaceChild(newTrimBtn, trimBtn);
            const newCancelBtn = cancelBtn.cloneNode(true);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            // Trim button click handler - Custom trim using MediaRecorder
            document.getElementById('trimVideoBtn').addEventListener('click', async function() {
                const start = parseFloat(trimStart.value) || 0;
                const end = parseFloat(trimEnd.value) || 0;
                const duration = end - start;
                
                if (duration > MAX_VIDEO_DURATION || duration <= 0 || duration < 1) {
                    alert(`Video duration must be between 1 and ${MAX_VIDEO_DURATION} seconds!`);
                    return;
                }
                
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Trimming...';
                trimModal.hide();
                
                // Show processing status
                mediaProcessingStatus.style.display = 'block';
                mediaStatusText.textContent = 'Trimming video...';
                
                try {
                    const trimmedBase64 = await trimVideoCustom(file, start, end);
                    processedMediaArray.push({
                        type: 'video',
                        data: trimmedBase64
                    });
                    addMediaPreview(trimmedBase64, 'video', processedMediaArray.length - 1);
                    mediaDataInput.value = JSON.stringify(processedMediaArray);
                    mediaStatusText.innerHTML = '<i class="fas fa-check-circle text-success"></i> Video trimmed successfully!';
                } catch (error) {
                    console.error('Video trim failed:', error);
                    alert('Video trim failed. Using original video.');
                    const videoBase64 = await readFileAsBase64(file);
                    processedMediaArray.push({
                        type: 'video',
                        data: videoBase64
                    });
                    addMediaPreview(videoBase64, 'video', processedMediaArray.length - 1);
                    mediaDataInput.value = JSON.stringify(processedMediaArray);
                }
                
                setTimeout(() => {
                    mediaProcessingStatus.style.display = 'none';
                }, 2000);
                
                // Cleanup
                URL.revokeObjectURL(videoUrl);
                resolve();
            });
            
            // Cancel button
            document.getElementById('cancelTrimBtn').addEventListener('click', function() {
                URL.revokeObjectURL(videoUrl);
                // Use original video
                readFileAsBase64(file).then(videoBase64 => {
                    processedMediaArray.push({
                        type: 'video',
                        data: videoBase64
                    });
                    addMediaPreview(videoBase64, 'video', processedMediaArray.length - 1);
                    mediaDataInput.value = JSON.stringify(processedMediaArray);
                    resolve();
                });
            });
            
            updateDuration();
            trimModal.show();
        });
    }
    
    // Custom Video Trim using MediaRecorder API - Optimized
    async function trimVideoCustom(file, startTime, endTime) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.preload = 'auto';
            video.muted = true;
            video.playsInline = true;
            video.crossOrigin = 'anonymous';
            
            const videoUrl = URL.createObjectURL(file);
            video.src = videoUrl;
            
            video.onloadedmetadata = function() {
                video.currentTime = startTime;
            };
            
            video.onseeked = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Calculate aspect ratio
                const aspectRatio = video.videoWidth / video.videoHeight;
                let width = 854;
                let height = 480;
                
                if (aspectRatio > width / height) {
                    height = width / aspectRatio;
                } else {
                    width = height * aspectRatio;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                // Try to use better codec
                let mimeType = 'video/webm;codecs=vp9,opus';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm;codecs=vp8,opus';
                }
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm';
                }
                
                const stream = canvas.captureStream(24); // Reduced to 24fps for faster processing
                const mediaRecorder = new MediaRecorder(stream, {
                    mimeType: mimeType,
                    videoBitsPerSecond: 1500000 // Reduced bitrate for faster processing
                });
                
                const chunks = [];
                let frameCount = 0;
                const totalFrames = Math.ceil((endTime - startTime) * 24);
                
                mediaRecorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) {
                        chunks.push(e.data);
                    }
                };
                
                mediaRecorder.onstop = function() {
                    URL.revokeObjectURL(videoUrl);
                    const blob = new Blob(chunks, { type: mimeType });
                    const reader = new FileReader();
                    reader.onload = function() {
                        resolve(reader.result);
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(blob);
                };
                
                // Optimized frame drawing
                function drawFrame() {
                    if (video.ended || video.currentTime >= endTime) {
                        clearInterval(drawInterval);
                        mediaRecorder.stop();
                        video.pause();
                        return;
                    }
                    
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    frameCount++;
                    
                    // Update progress
                    if (frameCount % 10 === 0) {
                        const progress = (frameCount / totalFrames) * 100;
                        mediaStatusText.textContent = `Trimming video... ${Math.round(progress)}%`;
                    }
                }
                
                // Start recording
                try {
                    mediaRecorder.start(100); // Collect data every 100ms
                    const drawInterval = setInterval(drawFrame, 42); // ~24fps
                    
                    // Play video
                    video.play().then(() => {
                        // Auto-stop when end time reached
                        setTimeout(() => {
                            if (mediaRecorder.state !== 'inactive') {
                                clearInterval(drawInterval);
                                mediaRecorder.stop();
                                video.pause();
                            }
                        }, (endTime - startTime) * 1000);
                    }).catch(reject);
                } catch (error) {
                    URL.revokeObjectURL(videoUrl);
                    reject(error);
                }
            };
            
            video.onerror = function(e) {
                URL.revokeObjectURL(videoUrl);
                reject(new Error('Video loading failed'));
            };
        });
    }
    
    // Custom Video Compression - Optimized
    async function compressVideoCustom(file) {
        return new Promise((resolve, reject) => {
            // If file is already small (< 10MB), use original
            if (file.size < 10 * 1024 * 1024) {
                readFileAsBase64(file).then(resolve).catch(reject);
                return;
            }
            
            const video = document.createElement('video');
            video.preload = 'auto';
            video.muted = true;
            video.playsInline = true;
            video.crossOrigin = 'anonymous';
            
            const videoUrl = URL.createObjectURL(file);
            video.src = videoUrl;
            
            video.onloadedmetadata = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Calculate aspect ratio
                const aspectRatio = video.videoWidth / video.videoHeight;
                let width = 854;
                let height = 480;
                
                if (aspectRatio > width / height) {
                    height = width / aspectRatio;
                } else {
                    width = height * aspectRatio;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                // Try better codec first
                let mimeType = 'video/webm;codecs=vp9,opus';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm;codecs=vp8,opus';
                }
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm';
                }
                
                const stream = canvas.captureStream(24); // 24fps for faster processing
                const mediaRecorder = new MediaRecorder(stream, {
                    mimeType: mimeType,
                    videoBitsPerSecond: 1500000 // 1.5 Mbps for compression
                });
                
                const chunks = [];
                let frameCount = 0;
                const duration = video.duration;
                const totalFrames = Math.ceil(duration * 24);
                
                mediaRecorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) {
                        chunks.push(e.data);
                    }
                };
                
                mediaRecorder.onstop = function() {
                    URL.revokeObjectURL(videoUrl);
                    const blob = new Blob(chunks, { type: mimeType });
                    
                    // Check if compression actually reduced size
                    if (blob.size >= file.size) {
                        // Compression didn't help, use original
                        readFileAsBase64(file).then(resolve).catch(reject);
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(blob);
                };
                
                function drawFrame() {
                    if (video.ended) {
                        clearInterval(drawInterval);
                        mediaRecorder.stop();
                        return;
                    }
                    
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    frameCount++;
                    
                    // Update progress
                    if (frameCount % 30 === 0) {
                        const progress = (frameCount / totalFrames) * 100;
                        mediaStatusText.textContent = `Compressing video... ${Math.round(progress)}%`;
                    }
                }
                
                try {
                    mediaRecorder.start(100);
                    const drawInterval = setInterval(drawFrame, 42); // ~24fps
                    
                    video.onended = function() {
                        clearInterval(drawInterval);
                        if (mediaRecorder.state !== 'inactive') {
                            mediaRecorder.stop();
                        }
                    };
                    
                    video.onerror = function(e) {
                        URL.revokeObjectURL(videoUrl);
                        clearInterval(drawInterval);
                        // Fallback to original
                        readFileAsBase64(file).then(resolve).catch(reject);
                    };
                    
                    video.play().catch(() => {
                        URL.revokeObjectURL(videoUrl);
                        // Fallback to original
                        readFileAsBase64(file).then(resolve).catch(reject);
                    });
                } catch (error) {
                    URL.revokeObjectURL(videoUrl);
                    // Fallback to original
                    readFileAsBase64(file).then(resolve).catch(reject);
                }
            };
            
            video.onerror = function() {
                URL.revokeObjectURL(videoUrl);
                // Fallback to original
                readFileAsBase64(file).then(resolve).catch(reject);
            };
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
        
        if (type === 'image') {
            col.innerHTML = `
                <div class="position-relative">
                    <img src="${base64}" class="img-fluid rounded" style="width: 100%; height: 150px; object-fit: cover;">
                    <span class="badge bg-primary position-absolute top-0 start-0 m-1">Image</span>
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeMedia(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        } else {
            col.innerHTML = `
                <div class="position-relative">
                    <video src="${base64}" class="w-100 rounded" style="height: 150px; object-fit: cover;"></video>
                    <span class="badge bg-success position-absolute top-0 start-0 m-1">Video</span>
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
        processedMediaArray.splice(index, 1);
        mediaDataInput.value = JSON.stringify(processedMediaArray);
        
        // Re-render all previews with updated indices
        mediaPreviewContainer.innerHTML = '';
        processedMediaArray.forEach((media, idx) => {
            addMediaPreview(media.data, media.type, idx);
        });
    };
    
    // Form submission handler - AJAX submission with background upload
    const form = document.getElementById('createPostForm');
    const createPostModal = document.getElementById('createPostModal');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Check if media is still processing
            if (mediaProcessingStatus.style.display !== 'none') {
                alert('Please wait for media processing to complete!');
                return false;
            }
            
            // Disable submit button
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Post...';
            }
            
            // Get form data
            const formData = new FormData(form);
            
            // Check if there are videos in the media data
            let hasVideos = false;
            if (formData.get('media_data')) {
                try {
                    const mediaData = JSON.parse(formData.get('media_data'));
                    hasVideos = mediaData.some(media => media.type === 'video');
                } catch (e) {
                    console.error('Error parsing media data:', e);
                }
            }
            
            // Close modal immediately
            if (createPostModal) {
                const modalInstance = bootstrap.Modal.getInstance(createPostModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            
            // Show upload progress modal
            if (!uploadProgressModal) {
                uploadProgressModal = new bootstrap.Modal(document.getElementById('uploadProgressModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            uploadProgressModal.show();
            
            const uploadProgressBar = document.getElementById('uploadProgressBar');
            const uploadStatusText = document.getElementById('uploadStatusText');
            
            try {
                // Create XMLHttpRequest for progress tracking
                const xhr = new XMLHttpRequest();
                
                // Track upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        uploadProgressBar.style.width = percentComplete + '%';
                        uploadProgressBar.textContent = Math.round(percentComplete) + '%';
                        uploadStatusText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
                    }
                });
                
                // Handle response
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        try {
                            const result = JSON.parse(xhr.responseText);
                            uploadProgressBar.style.width = '100%';
                            uploadProgressBar.textContent = '100%';
                            uploadStatusText.textContent = 'Upload complete!';
                            
                            // Hide progress modal
                            setTimeout(() => {
                                uploadProgressModal.hide();
                                showToast('success', 'Post created successfully!', 2000);
                                
                                // Fetch and show new post immediately without full page reload
                                if (result.post && result.post.slug) {
                                    fetchNewPost(result.post.slug);
                                } else {
                                    // Fallback to reload
                                    setTimeout(() => window.location.reload(), 1500);
                                }
                            }, 500);
                        } catch (e) {
                            uploadProgressModal.hide();
                            showToast('success', 'Post created successfully!', 2000);
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    } else {
                        uploadProgressModal.hide();
                        let errorMsg = 'Failed to create post. Please try again.';
                        try {
                            const result = JSON.parse(xhr.responseText);
                            errorMsg = result.message || errorMsg;
                        } catch (e) {}
                        showToast('error', errorMsg, 5000);
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Create Post';
                        }
                    }
                });
                
                xhr.addEventListener('error', function() {
                    uploadProgressModal.hide();
                    showToast('error', 'Network error. Please try again.', 5000);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Create Post';
                    }
                });
                
                // Start upload
                uploadStatusText.textContent = 'Preparing upload...';
                xhr.open('POST', form.action);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token'));
                xhr.send(formData);
                
            } catch (error) {
                console.error('Form submission error:', error);
                uploadProgressModal.hide();
                showToast('error', 'An error occurred. Please try again.', 5000);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Create Post';
                }
            }
        });
    }
    
    // Toast notification function
    function showToast(type, message, duration = 3000) {
        // Remove existing toast if any
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    // Fetch new post and add to top of list
    async function fetchNewPost(slug) {
        try {
            // Get current user from page
            const currentUser = @json($user->username ?? null);
            if (!currentUser) {
                window.location.reload();
                return;
            }
            
            // Fetch posts partial view with AJAX
            const response = await fetch(`/${currentUser}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                
                if (result.posts) {
                    const postsContainer = document.getElementById('posts-container');
                    if (postsContainer) {
                        // Replace with new posts HTML
                        postsContainer.innerHTML = result.posts;
                        
                        // Scroll to top to show new post
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        
                        // Re-initialize any event listeners if needed
                        initReadMore();
                        return;
                    }
                }
            }
            
            // Fallback: reload page
            window.location.reload();
        } catch (error) {
            console.error('Error fetching new post:', error);
            // Fallback: reload page
            window.location.reload();
        }
    }
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
