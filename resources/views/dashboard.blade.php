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
               {{-- Post Category Dropdown (ONLY POST TYPE) --}}
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
                  @error('category_id')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>

               {{-- Title Field --}}
               <div class="mb-3">
                  <label for="title" class="form-label" id="title_label">Notice Title <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter notice title..." value="{{ old('title') }}" required>
                  @error('title')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
               </div>

               {{-- Image Upload --}}
               <div class="row mb-4">
                  <div class="col-12">
                    <label for="image" class="form-label">Choose Image</label>
                  </div>
                  <div class="col-12 text-secondary">
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
                  });
               </script>

               {{-- Description Field --}}
               <div class="mb-3">
                  <label for="description" class="form-label" id="description_label">Notice Description</label>
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
