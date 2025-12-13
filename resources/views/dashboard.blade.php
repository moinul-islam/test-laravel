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
          <small class="text-muted">Videos must be 60 seconds or less. Any size video supported.</small>
          
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

{{-- External Libraries --}}
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js"></script>

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
    let ffmpegLoaded = false;
    let ffmpeg = null;
    let currentFileIndex = 0;
    let totalFiles = 0;
    
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
                mediaStatusText.textContent = `Checking video ${i + 1} of ${files.length}...`;
                try {
                    const duration = await getVideoDuration(file);
                    
                    if (duration > MAX_VIDEO_DURATION) {
                        // Video too long - reject it
                        alert(`ভিডিও "${file.name}" খুব বড় (${duration.toFixed(1)} সেকেন্ড)। দয়া করে ${MAX_VIDEO_DURATION} সেকেন্ড বা তার কম সময়ের ভিডিও আপলোড করুন।\n\nVideo "${file.name}" is too long (${duration.toFixed(1)} seconds). Please upload videos that are ${MAX_VIDEO_DURATION} seconds or less.`);
                        continue; // Skip this video
                    }
                    
                    // Show original size
                    const originalSizeMB = (file.size / 1024 / 1024).toFixed(2);
                    mediaStatusText.innerHTML = `Compressing video ${i + 1} of ${files.length}...<br>Original: ${originalSizeMB}MB - Please wait...`;
                    
                    // Video is OK - compress it aggressively
                    const compressedBase64 = await compressVideo(file, (progress) => {
                        // Update progress during compression
                        mediaStatusText.innerHTML = `Compressing video ${i + 1} of ${files.length}...<br>Original: ${originalSizeMB}MB - ${progress}% complete`;
                    });
                    
                    processedMediaArray.push({
                        type: 'video',
                        data: compressedBase64
                    });
                    addMediaPreview(compressedBase64, 'video', processedMediaArray.length - 1);
                } catch (error) {
                    console.error('Video processing failed:', error);
                    alert('Video processing failed: ' + file.name + '\nError: ' + error.message);
                }
            }
        }
        
        mediaProgress.style.width = '100%';
        mediaStatusText.innerHTML = `<i class="fas fa-check-circle text-success"></i> All media processed successfully!`;
        mediaDataInput.value = JSON.stringify(processedMediaArray);
        
        setTimeout(() => {
            mediaProcessingStatus.style.display = 'none';
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
    
    // Read file as base64
    function readFileAsBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Compress video AGGRESSIVELY (like Facebook/Instagram/TikTok)
    async function compressVideo(file, progressCallback) {
        // Load FFmpeg if not loaded
        if (!ffmpegLoaded) {
            mediaStatusText.textContent = 'Loading video compressor (one-time setup)...';
            try {
                const { createFFmpeg } = FFmpeg;
                ffmpeg = createFFmpeg({ 
                    log: true,
                    progress: ({ ratio }) => {
                        if (progressCallback) {
                            progressCallback(Math.round(ratio * 100));
                        }
                    }
                });
                
                console.log('Loading FFmpeg...');
                await ffmpeg.load();
                console.log('FFmpeg loaded successfully!');
                ffmpegLoaded = true;
            } catch (error) {
                console.error('FFmpeg load failed:', error);
                
                // Fallback: Use canvas-based compression for very basic compression
                console.log('Using fallback compression method...');
                return await fallbackVideoCompress(file);
            }
        }
        
        try {
            const { fetchFile } = FFmpeg;
            const inputName = 'input.mp4';
            const outputName = 'output.mp4';
            
            console.log('Writing file to FFmpeg...');
            ffmpeg.FS('writeFile', inputName, await fetchFile(file));
            
            console.log('Starting aggressive compression...');
            
            // AGGRESSIVE compression settings (social media style):
            // - 720p max (good balance)
            // - CRF 28 (higher = smaller file, still good quality)
            // - Faster preset (smaller files)
            // - Lower bitrate cap
            // - Optimized audio: 96kbps AAC (good enough, much smaller)
            // - 30fps max (smoother uploads)
            
            await ffmpeg.run(
                '-i', inputName,
                
                // Video settings
                '-vf', 'scale=1280:720:force_original_aspect_ratio=decrease,fps=30',
                '-c:v', 'libx264',
                '-crf', '28',
                '-preset', 'faster',
                '-maxrate', '2M',
                '-bufsize', '2M',
                '-profile:v', 'main',
                '-level', '3.1',
                '-pix_fmt', 'yuv420p',
                
                // Audio settings (good quality but small)
                '-c:a', 'aac',
                '-b:a', '96k',
                '-ar', '44100',
                '-ac', '2',
                
                // Optimization
                '-movflags', '+faststart',
                '-threads', '0',
                
                outputName
            );
            
            console.log('Reading compressed file...');
            const data = ffmpeg.FS('readFile', outputName);
            
            // Cleanup
            ffmpeg.FS('unlink', inputName);
            ffmpeg.FS('unlink', outputName);
            
            const blob = new Blob([data.buffer], { type: 'video/mp4' });
            const compressed = await readFileAsBase64(blob);
            
            // Log compression results
            const originalSize = file.size;
            const compressedSize = blob.size;
            const reduction = ((1 - compressedSize/originalSize) * 100).toFixed(1);
            
            console.log(`✓ Video compressed successfully!`);
            console.log(`  Original: ${(originalSize/1024/1024).toFixed(2)}MB`);
            console.log(`  Compressed: ${(compressedSize/1024/1024).toFixed(2)}MB`);
            console.log(`  Reduction: ${reduction}%`);
            
            // Show final size
            if (mediaStatusText) {
                mediaStatusText.innerHTML = `Compressed: ${(compressedSize/1024/1024).toFixed(2)}MB (${reduction}% smaller)`;
            }
            
            return compressed;
        } catch (error) {
            console.error('Video compression failed:', error);
            throw new Error('Compression failed: ' + error.message);
        }
    }
    
    // Fallback compression using MediaRecorder API
    async function fallbackVideoCompress(file) {
        try {
            console.log('Using browser-based video compression...');
            
            const videoElement = document.createElement('video');
            videoElement.src = URL.createObjectURL(file);
            videoElement.muted = true;
            
            await new Promise((resolve) => {
                videoElement.onloadedmetadata = resolve;
            });
            
            const canvas = document.createElement('canvas');
            const targetWidth = Math.min(videoElement.videoWidth, 1280);
            const targetHeight = Math.min(videoElement.videoHeight, 720);
            
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            
            const ctx = canvas.getContext('2d');
            const stream = canvas.captureStream(30); // 30 fps
            
            // Add audio from original video
            const audioContext = new AudioContext();
            const sourceNode = audioContext.createMediaElementSource(videoElement);
            const dest = audioContext.createMediaStreamDestination();
            sourceNode.connect(dest);
            sourceNode.connect(audioContext.destination);
            
            // Combine video and audio streams
            const audioTrack = dest.stream.getAudioTracks()[0];
            if (audioTrack) {
                stream.addTrack(audioTrack);
            }
            
            const mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'video/webm;codecs=vp8,opus',
                videoBitsPerSecond: 2000000, // 2 Mbps
                audioBitsPerSecond: 96000 // 96 kbps
            });
            
            const chunks = [];
            mediaRecorder.ondataavailable = (e) => chunks.push(e.data);
            
            const recordingPromise = new Promise((resolve) => {
                mediaRecorder.onstop = () => resolve(chunks);
            });
            
            videoElement.play();
            mediaRecorder.start();
            
            // Draw video frames to canvas
            const drawFrame = () => {
                if (!videoElement.paused && !videoElement.ended) {
                    ctx.drawImage(videoElement, 0, 0, targetWidth, targetHeight);
                    requestAnimationFrame(drawFrame);
                }
            };
            drawFrame();
            
            // Wait for video to end
            await new Promise((resolve) => {
                videoElement.onended = resolve;
            });
            
            mediaRecorder.stop();
            audioContext.close();
            URL.revokeObjectURL(videoElement.src);
            
            const recordedChunks = await recordingPromise;
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            
            const originalSize = file.size;
            const compressedSize = blob.size;
            const reduction = ((1 - compressedSize/originalSize) * 100).toFixed(1);
            
            console.log(`✓ Video compressed (fallback method)!`);
            console.log(`  Original: ${(originalSize/1024/1024).toFixed(2)}MB`);
            console.log(`  Compressed: ${(compressedSize/1024/1024).toFixed(2)}MB`);
            console.log(`  Reduction: ${reduction}%`);
            
            if (mediaStatusText) {
                mediaStatusText.innerHTML = `Compressed: ${(compressedSize/1024/1024).toFixed(2)}MB (${reduction}% smaller)`;
            }
            
            return await readFileAsBase64(blob);
        } catch (error) {
            console.error('Fallback compression also failed:', error);
            // Last resort: return original
            alert('Video compression not available. Uploading original video. This may take longer.');
            return await readFileAsBase64(file);
        }
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
    
    // Form submission handler
    const form = document.getElementById('createPostForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if media is still processing
            if (mediaProcessingStatus.style.display !== 'none') {
                e.preventDefault();
                alert('Please wait for media processing to complete!');
                return false;
            }
        });
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