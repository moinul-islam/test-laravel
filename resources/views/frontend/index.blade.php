@extends("frontend.master")
@section('main-content')

{{-- Posts Container --}}
<div class="container mt-4">




    {{-- Include Sidebar --}}
    @include('frontend.body.sidebar')

    {{--
    @include('frontend.phonebook')
    --}}

    @php
           $countries = App\Models\Country::orderByRaw("CASE WHEN username = 'international' THEN 0 ELSE 1 END")
       ->orderBy('name') // অথবা যেকোনো column দিয়ে sort করতে চান
       ->get();
           
           // $visitorLocationPath থেকে current location খুঁজে বের করুন
           $selectedCountry = null;
           $selectedCity = null;
           $cities = collect();
           
           if(isset($visitorLocationPath) && $visitorLocationPath) {
               // প্রথমে check করুন এটা country কিনা
               $selectedCountry = App\Models\Country::where('username', $visitorLocationPath)->first();
               
               if($selectedCountry) {
                   // এটা country, তাহলে এর cities load করুন
                   $cities = App\Models\City::where('country_id', $selectedCountry->id)
                                           ->orderBy('name', 'asc')
                                           ->get();
               } else {
                   // না হলে check করুন এটা city কিনা
                   $selectedCity = App\Models\City::where('username', $visitorLocationPath)->first();
                   
                   if($selectedCity) {
                       // City পেলে এর country খুঁজুন
                       $selectedCountry = App\Models\Country::find($selectedCity->country_id);
                       
                       // এই country এর সব cities load করুন
                       if($selectedCountry) {
                           $cities = App\Models\City::where('country_id', $selectedCountry->id)
                                                   ->orderBy('name', 'asc')
                                                   ->get();
                       }
                   }
               }
           }
       @endphp

    <!-- Horizontal Scrollable Navigation -->
    <div class="scroll-container mb-4">
        <div class="scroll-content">

            <button href="" class="nav-item-custom animated-rgb-border-color" id="openSidebarBtn">
                <span><i class="bi bi-shop animated-rgb-text-color"></i></span>
            </button>
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
            .animated-rgb-text-color {
                animation: rgb-border-bg-color 2s linear infinite;
            }
            </style>

            <!--
            
            <button class="nav-item-custom" data-bs-toggle="modal" data-bs-target="#fullModal">
                <span><i class="bi bi-journal-bookmark"></i></span>
            </button> 

            <button class="nav-item-custom" data-bs-toggle="modal" data-bs-target="#locationModal">
                <span><i class="bi bi-geo-alt"></i></span>
                <span>
                    @if($selectedCity)
                        {{ $selectedCity->name }}, {{ $selectedCountry->name ?? '' }}
                    @elseif($selectedCountry)
                        {{ $selectedCountry->name }}
                    @else
                        Select Location
                    @endif
                </span>
            </button>

            -->

            
           
           


            
            @php
    // Determine which categories to show in navigation
    $navCategories = collect();
    
    if(isset($category)) {
        // শুধু post type ধরবে
        if($category->parent_cat_id) {
            // যদি child হয় তাহলে parent এর child গুলো নেবে (যেগুলোতে post আছে)
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
                                ->get();
        } 
        // যদি parent হয় তাহলে এর child নেবে
        else if($category->cat_type == 'post') {
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
                                ->get();
        }
    }
@endphp

@php
    // Check if category is set from path parameter or query parameter
    $selectedCategorySlug = isset($category) ? $category->slug : request()->get('category');
@endphp


{{-- All Posts Link --}}
<a href="{{ url('/' . $visitorLocationPath) }}" 
   class="nav-item-custom {{ !$selectedCategorySlug ? 'active' : '' }}">
   <span><i class="bi bi-file-post"></i></span>
   <span>All Posts</span>
</a>
<!-- <a href="{{ route('popular.users') }}" class="nav-item-custom">
    <i class="bi bi-stars"></i>Popular
</a> -->

@if($navCategories->count() > 0)
    {{-- Show determined post categories --}}
    @foreach($navCategories as $navCat)
        <a href="{{ url('/' . $visitorLocationPath . '/' . $navCat->slug) }}" 
        class="nav-item-custom {{ $selectedCategorySlug == $navCat->slug ? 'active' : '' }}">
            <span>{{ $navCat->category_name }}</span>
        </a>
    @endforeach
@else
    {{-- Show all parent post categories --}}
    @php
        $parentCategories = \App\Models\Category::where('cat_type', 'post')
                            ->whereNull('parent_cat_id')
                            ->whereHas('posts') // শুধু যেগুলোতে post আছে
                            ->get();
    @endphp

    @foreach($parentCategories as $parentCat)
        @php
            $subCategories = \App\Models\Category::where('parent_cat_id', $parentCat->id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
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
                            <a class="dropdown-item" href="{{ url('/' . $visitorLocationPath . '/' . $subCat->slug) }}">
                                {{ $subCat->category_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            {{-- যদি sub না থাকে তাহলে সরাসরি link --}}
            <a href="{{ url('/' . $visitorLocationPath . '/' . $parentCat->slug) }}" 
               class="nav-item-custom {{ $selectedCategorySlug == $parentCat->slug ? 'active' : '' }}">
                <span>
                    <i class="bi {{ $parentCat->image }}"></i>
                </span>
                <span>{{ $parentCat->category_name }}</span>
            </a>
        @endif
    @endforeach
@endif
        </div>
    </div>


    @guest
        <div class="alert alert-info mb-4 login-popup-trigger" role="alert" style="cursor:pointer;" 
             onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('authModal')); modal.show();">
            পোস্ট করতে 
            <span class="alert-link">লগইন করুন</span>।
        </div>

        <style>
            /* Custom backdrop with black shadow effect */
            .custom-modal-backdrop {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5) !important;
                z-index: 1040;
                backdrop-filter: blur(1px);
                transition: opacity 0.3s;
            }
        </style>
        <script>
            (function(){
                // Add custom backdrop (deeper shadow) when modal is shown via THIS popup
                var modal = document.getElementById('authModal');
                var backdropEl = null;
                function showCustomBackdrop() {
                    if (!backdropEl) {
                        backdropEl = document.createElement('div');
                        backdropEl.className = 'custom-modal-backdrop fade show';
                        document.body.appendChild(backdropEl);
                    }
                }
                function removeCustomBackdrop() {
                    if (backdropEl && backdropEl.parentNode) {
                        backdropEl.parentNode.removeChild(backdropEl);
                        backdropEl = null;
                    }
                }

                // Triggered when the alert is clicked
                var triggers = document.getElementsByClassName('login-popup-trigger');
                Array.from(triggers).forEach(function(el) {
                    el.addEventListener('click', function(e){
                        showCustomBackdrop();
                    });
                });

                // Listen for close events on modal (includes close btn)
                if (modal) {
                    modal.addEventListener('hide.bs.modal', function(){
                        // Will fire when closing starts
                        removeCustomBackdrop();
                    });
                    // Also remove on hidden in case (failsafe)
                    modal.addEventListener('hidden.bs.modal', function(){
                        removeCustomBackdrop();
                    });
                }
            })();
        </script>
    @endguest

    {{-- Simple Create Post Form (like create-post-modal.php but inline, not popup) --}}
    @auth
    @php
        // Ensure $categories is always defined to avoid "undefined variable" error
        if (!isset($categories)) {
            $categories = \App\Models\Category::where('cat_type', 'post')->get();
        }
    @endphp

    {{-- Custom Bootstrap Alerts for Error Handling --}}
    <div id="simpleCreatePostAlerts" style="display:none;">
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert" id="simpleCreatePostAlert">
            <span id="simpleCreatePostAlertMsg"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                onclick="document.getElementById('simpleCreatePostAlerts').style.display='none'"></button>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data" id="simpleCreatePostForm">
                @csrf

                {{-- Description field --}}
                <div class="mb-2">
                    <textarea class="form-control" id="simple_description" name="description" rows="2" placeholder="What's on your mind?"></textarea>
                </div>

                <div class="d-flex align-items-center">
                    {{-- Category with icon --}}
                    <div class="me-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-tag" title="Category"></i>
                            </span>
                            <select class="form-select form-select-sm border-start-0" id="simple_category_name" name="category_name" required style="border-left:0; padding-right: 0px;">
                                <option value="">Add Category</option>
                                @foreach($categories as $category)
                                    @if($category->cat_type === 'post')
                                        <option value="{{ $category->category_name }}">{{ $category->category_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Media with icon, trigger input only by clicking image icon --}}
                    <div class="me-2">
                        <div class="input-group input-group-sm align-items-center" style="height: 100%;">
                            <span class="input-group-text bg-white border-rounded" style="padding:.25rem .5rem; cursor:pointer;" id="simpleMediaIconTrigger">
                                <i class="bi bi-image" title="Add images/videos"></i>
                            </span>
                            <!-- The input is visually hidden and only triggered by the icon -->
                            <input type="file" name="media[]" class="form-control form-control-sm border d-none"
                                   id="simpleMediaInput" multiple accept="image/*,video/*,.heic,.heif">
                        </div>
                    </div>

                    <input type="hidden" name="media_data" id="simpleMediaData">
                    <input type="hidden" id="simple_category_id" name="category_id" value="">

                    {{-- Post button right --}}
                    <div class="ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm" id="simpleSubmitBtn" disabled style="min-width:64px; width:100%;">
                           
                            <span id="simpleSubmitBtnText">Post</span>
                            <span id="simpleSubmitBtnSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status" aria-hidden="true"></span>
                           
                        </button>
                    </div>
                </div>

                <div id="simpleMediaProcessingStatus" style="display: none;" class="mt-1">
                    <div class="progress mb-1" style="height:14px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="simpleMediaProgress"></div>
                    </div>
                    <small id="simpleMediaStatusText" style="font-size:11px;">Processing media...</small>
                </div>
                <div id="simpleMediaPreviewContainer" class="mt-2 row g-1"></div>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Image icon click triggers the hidden input for media
                const mediaIconTrigger = document.getElementById('simpleMediaIconTrigger');
                const mediaInput = document.getElementById('simpleMediaInput');
                if(mediaIconTrigger && mediaInput){
                    mediaIconTrigger.addEventListener('click', function(e) {
                        mediaInput.click();
                    });
                }

                // Category help/error span (for visual border only, not alert)
                const categorySelect = document.getElementById('simple_category_name');
                const postBtn = document.getElementById('simpleSubmitBtn');
                const postBtnText = document.getElementById('simpleSubmitBtnText');
                const postBtnSpinner = document.getElementById('simpleSubmitBtnSpinner');
                let categoryHelpMsg = document.createElement('span');
                categoryHelpMsg.className = 'text-danger ms-1';
                categoryHelpMsg.id = 'category-help-msg';
                categoryHelpMsg.style.display = 'none';
                categoryHelpMsg.style.fontSize = '12px';
                categoryHelpMsg.textContent = 'দয়া করে ক্যাটাগরি সিলেক্ট করুন';

                if(categorySelect && categorySelect.parentNode && !document.getElementById('category-help-msg')) {
                    categorySelect.parentNode.appendChild(categoryHelpMsg);
                }

                // Enable/disable post button logic for description OR image/video (at least one required)
                const descInput = document.getElementById('simple_description');

                window.simpleProcessedMediaArray = window.simpleProcessedMediaArray || [];
                function getMediaCount() {
                    return window.simpleProcessedMediaArray.length || 0;
                }

                function validateBtnEnable() {
                    if (
                        (descInput && descInput.value.trim() !== '') ||
                        getMediaCount() > 0
                    ) {
                        postBtn.disabled = false;
                    } else {
                        postBtn.disabled = true;
                    }
                }

                if(descInput) descInput.addEventListener('input', validateBtnEnable);

                window.simpleValidateBtnEnable = validateBtnEnable;
                validateBtnEnable();

                // Alert helper
                function showCustomAlert(msg) {
                    let alertBlock = document.getElementById('simpleCreatePostAlerts');
                    let alertMsg = document.getElementById('simpleCreatePostAlertMsg');
                    if(alertBlock && alertMsg) {
                        alertMsg.textContent = msg;
                        alertBlock.style.display = 'block';
                    }
                }

                // Remove alert on focus
                document.getElementById('simpleCreatePostForm').addEventListener('focusin', function() {
                    let alertBlock = document.getElementById('simpleCreatePostAlerts');
                    if(alertBlock) alertBlock.style.display = 'none';
                });

                // On submit block post if invalid and show alert, otherwise show spinner during upload
                if(postBtn && categorySelect){
                    const form = document.getElementById('simpleCreatePostForm');
                    if(form) {
                        form.addEventListener('submit', function(e) {
                            if(
                                (
                                    (!descInput || descInput.value.trim() === '') &&
                                    getMediaCount() === 0
                                )
                            ) {
                                e.preventDefault();
                                showCustomAlert('Write something or add at least one image/video to post.');
                                if(descInput) descInput.focus();
                                return false;
                            }
                            if(categorySelect.value === '') {
                                e.preventDefault();
                                categorySelect.classList.add('border-danger');
                                categorySelect.focus();
                                categoryHelpMsg.style.display = 'inline';
                                showCustomAlert('Please select a category.');
                                setTimeout(() => {
                                    categorySelect.classList.remove('border-danger');
                                    categoryHelpMsg.style.display = 'none';
                                }, 1800);
                                return false;
                            }
                            // Show spinner in button, disable text
                            if(postBtn && postBtnSpinner && postBtnText) {
                                postBtn.disabled = true;
                                postBtnSpinner.classList.remove('d-none');
                                postBtnText.textContent = 'Posting...';
                            }
                        });
                    }
                }
            });
            </script>
        </div>
    </div>

    {{-- Inline JS for this form (minimal, trimmed/adapted from modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const MAX_WIDTH = 1800, MAX_HEIGHT = 1800, IMAGE_QUALITY = 0.7;
        const MAX_IMAGES = 5, MAX_VIDEOS = 1, MAX_VIDEO_SIZE = 50 * 1024 * 1024;

        const mediaInput = document.getElementById('simpleMediaInput');
        const mediaDataInput = document.getElementById('simpleMediaData');
        const mediaProcessingStatus = document.getElementById('simpleMediaProcessingStatus');
        const mediaProgress = document.getElementById('simpleMediaProgress');
        const mediaStatusText = document.getElementById('simpleMediaStatusText');
        const mediaPreviewContainer = document.getElementById('simpleMediaPreviewContainer');
        window.simpleProcessedMediaArray = [];
        let processedMediaArray = window.simpleProcessedMediaArray;

        // Media upload/preview logic
        if(mediaInput) {
            mediaInput.addEventListener('change', async function() {
                const files = Array.from(this.files);
                if(files.length === 0) return;
                const imageFiles = files.filter(f => f.type.startsWith('image/') || f.name.match(/\.(heic|heif)$/i));
                const videoFiles = files.filter(f => f.type.startsWith('video/'));
                const currentImages = processedMediaArray.filter(m => m.type === 'image').length;
                const currentVideos = processedMediaArray.filter(m => m.type === 'video').length;
                if(currentImages + imageFiles.length > MAX_IMAGES) {
                    showCustomAlert(`Max ${MAX_IMAGES} images allowed.`);
                    this.value = '';
                    return;
                }
                if(currentVideos + videoFiles.length > MAX_VIDEOS) {
                    showCustomAlert(`Max ${MAX_VIDEOS} video allowed.`);
                    this.value = '';
                    return;
                }
                for(let video of videoFiles) {
                    if(video.size > MAX_VIDEO_SIZE) {
                        showCustomAlert(`Video too large: ${video.name}`);
                        this.value = '';
                        return;
                    }
                }

                mediaProcessingStatus.style.display = 'block';
                mediaProgress.style.width = '0%';

                for(let i = 0; i < files.length; i++) {
                    mediaProgress.style.width = ((i / files.length) * 100) + '%';
                    const file = files[i];
                    const isVideo = file.type.startsWith('video/');
                    mediaStatusText.textContent = `Processing ${isVideo ? 'video' : 'image'} ${i + 1} of ${files.length}...`;
                    try {
                        let processedBase64;
                        if (isVideo) {
                            processedBase64 = await new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.onload = e => resolve(e.target.result);
                                reader.onerror = reject;
                                reader.readAsDataURL(file);
                            });
                            processedMediaArray.push({type: 'video', data: processedBase64});
                            addMediaPreview(processedBase64, 'video', processedMediaArray.length - 1);
                        } else {
                            processedBase64 = await processImage(file);
                            processedMediaArray.push({type: 'image', data: processedBase64});
                            addMediaPreview(processedBase64, 'image', processedMediaArray.length - 1);
                        }
                        if(window.simpleValidateBtnEnable) window.simpleValidateBtnEnable();
                    } catch (error) {
                        showCustomAlert('Error: ' + file.name);
                    }
                }
                mediaProgress.style.width = '100%';
                mediaDataInput.value = JSON.stringify(processedMediaArray);
                setTimeout(() => { mediaProcessingStatus.style.display = 'none'; }, 1100);
                this.value = '';
            });
        }
        // Process image helpers (copied from modal, but minimal)
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
                const reader = new FileReader();
                reader.onload = function(event) {
                    heic2any({
                        blob: new Blob([event.target.result]),
                        toType: 'image/jpeg', quality: 0.8
                    }).then(resolve).catch(reject);
                };
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        }
        function compressImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        let width = img.width, height = img.height;
                        if(width>MAX_WIDTH||height>MAX_HEIGHT){
                            if(width>height){
                                height = Math.round(height*(MAX_WIDTH/width));
                                width = MAX_WIDTH;
                            }else{
                                width = Math.round(width*(MAX_HEIGHT/height));
                                height = MAX_HEIGHT;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width; canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.fillStyle = "#FFF";
                        ctx.fillRect(0,0,width,height);
                        ctx.drawImage(img,0,0,width,height);
                        canvas.toBlob(blob=>{
                            const blobReader = new FileReader();
                            blobReader.onload=()=>resolve(blobReader.result);
                            blobReader.onerror=reject;
                            blobReader.readAsDataURL(blob);
                        },'image/jpeg',IMAGE_QUALITY);
                    };
                    img.onerror=reject; img.src = e.target.result;
                };
                reader.onerror=reject; reader.readAsDataURL(file);
            });
        }
        function addMediaPreview(base64, type, index) {
            const col = document.createElement('div');
            col.className = 'col-6 col-lg-3';
            const html = type === 'video'
                ? `<video src="${base64}" style="width:100%;height:100px;object-fit:cover;" controls></video>`
                : `<img src="${base64}" style="width:100%;height:100px;object-fit:cover;" class="rounded" />`;
            col.innerHTML = `
                <div class="position-relative">
                    ${html}
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center" style="font-size:15px;width:22px;height:22px;padding:0;line-height:1.1;" onclick="removeSimpleMedia(${index})">
                        <span style="display:inline-block;width:14px;height:14px;">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
                                <line x1="4" y1="4" x2="12" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                <line x1="12" y1="4" x2="4" y2="12" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </button>
                </div>
            `;
            mediaPreviewContainer.appendChild(col);
        }
        // Remove media
        window.removeSimpleMedia = function(index) {
            processedMediaArray.splice(index, 1);
            mediaDataInput.value = JSON.stringify(processedMediaArray);
            // re-render
            mediaPreviewContainer.innerHTML = '';
            processedMediaArray.forEach((media, idx) => addMediaPreview(media.data, media.type, idx));
            // Update post button state
            if(window.simpleValidateBtnEnable) window.simpleValidateBtnEnable();
        };

        // Handle hidden category id
        const categorySelect = document.getElementById('simple_category_name');
        const categoryIdInput = document.getElementById('simple_category_id');
        if(categorySelect && categoryIdInput) {
            categorySelect.addEventListener('change', function() {
                const name = this.value;
                // Use categories provided by PHP, safe since we ensure it's defined
                const cats = @json($categories);
                const found = cats.find(cat => cat.category_name === name);
                if(found) categoryIdInput.value = found.id;
                else categoryIdInput.value = '';
            });
        }

        // -- Use same alert logic as above for errors from this script --
        function showCustomAlert(msg) {
            let alertBlock = document.getElementById('simpleCreatePostAlerts');
            let alertMsg = document.getElementById('simpleCreatePostAlertMsg');
            if(alertBlock && alertMsg) {
                alertMsg.textContent = msg;
                alertBlock.style.display = 'block';
            }
        }
    });
    </script>
    <style>
    #simpleCreatePostForm textarea { min-height: 60px; }
    #simpleMediaPreviewContainer img, #simpleMediaPreviewContainer video { border-radius:8px; }
    #simpleMediaPreviewContainer .col-6 { margin-bottom: 0.25rem; }
    /* Spinner and button visual for posting state */
    #simpleSubmitBtn[disabled] {
        pointer-events: none;
        opacity: 0.7;
    }
    </style>
    @endauth

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

@include('frontend.location')

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
</script>

@endsection


