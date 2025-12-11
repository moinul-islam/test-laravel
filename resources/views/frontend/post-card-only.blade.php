<div class="mb-4 post-item" data-post-id="{{ $post->id }}">
   <div class="card shadow-sm">
      <div>
         <div class="d-flex align-items-center justify-content-between card-body">
            <div class="d-flex align-items-center">
               <img src="{{ $post->user->image ? asset('profile-image/'.$post->user->image) : 'https://cdn-icons-png.flaticon.com/512/219/219983.png' }}"
                  class="rounded-circle me-2"
                  alt="Profile Photo"
                  style="width:40px; height:40px; object-fit:cover;">
               <div>
                  <h6 class="mb-0">
                     <a href="{{ route('profile.show', $post->user->username) }}" class="text-decoration-none">
                     {{ $post->user->name }}
                     </a>
                  </h6>
                  @if($post->category)
                  <small class="text-muted"><i class="{{ $post->category->image }}"></i> {{ $post->category->category_name }}</small>
                  @endif
                  <small class="text-muted"><i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}</small>
               </div>
            </div>
            <div class="dropdown">
               <button class="btn text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
               <i class="bi bi-three-dots-vertical"></i>
               </button>
               <ul class="dropdown-menu dropdown-menu-end">
                  @if(auth()->id() == $post->user_id)
                  <!-- <li><a class="dropdown-item" href="#"><i class="bi bi-pencil-square me-2"></i>Edit</a></li> -->
                  <li>
                     <a class="dropdown-item text-danger delete-post-btn" 
                        href="#" 
                        data-post-id="{{ $post->id }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#deletePostModal">
                     <i class="bi bi-trash me-2"></i>Delete
                     </a>
                  </li>
                  @else
                  <li><a class="dropdown-item" href="#"><i class="bi bi-flag me-2"></i>Report</a></li>
                  <!-- <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-person-x me-2"></i>Block</a></li> -->
                  @endif
               </ul>
            </div>
         </div>
         <div class="card-body">
            <h2>{{ $post->title }}</h2>
            @php
            $maxLength = 200;
            $desc = $post->description;
            $descLength = mb_strlen($desc);
            @endphp
            @if($descLength > $maxLength)
            <p class="m-0 post-desc-short" style="display: block;">
               {{ mb_substr($desc, 0, $maxLength) }}...
               <a href="javascript:void(0);" class="see-more-link text-primary" onclick="toggleDescription(this)">See more</a>
            </p>
            <p class="m-0 post-desc-full" style="display: none;">
               {{ $desc }}
               <a href="javascript:void(0);" class="see-less-link text-primary" onclick="toggleDescription(this)">See less</a>
            </p>
            @else
            <p class="m-0">{{ $desc }}</p>
            @endif
            <script>
               function toggleDescription(link) {
                   const shortDesc = link.closest('.card-body').querySelector('.post-desc-short');
                   const fullDesc = link.closest('.card-body').querySelector('.post-desc-full');
                   if (shortDesc && fullDesc) {
                       if (shortDesc.style.display === 'none') {
                           shortDesc.style.display = 'block';
                           fullDesc.style.display = 'none';
                       } else {
                           shortDesc.style.display = 'none';
                           fullDesc.style.display = 'block';
                       }
                   }
               }
            </script>
         </div>
         {{-- Post Media Display (Supports both old single image and new mixed media (images/videos)) --}}
         @php
         // Handle both old (string) and new (JSON) format
         $media = null;
         $isSingleImage = false;
         if ($post->image) {
         if (is_string($post->image)) {
         // Check if it's JSON
         if (str_starts_with($post->image, '{') || str_starts_with($post->image, '[')) {
         $media = json_decode($post->image, true);
         } else {
         // Old format: single image string
         $isSingleImage = true;
         }
         } elseif (is_array($post->image)) {
         // Already decoded by cast
         $media = $post->image;
         }
         }
         // Prepare a unified $allMedia array preserving type and file
         $allMedia = [];
         if ($media) {
         if (isset($media['images']) && is_array($media['images'])) {
         foreach ($media['images'] as $img) {
         $allMedia[] = ['type' => 'image', 'file' => $img];
         }
         }
         if (isset($media['videos']) && is_array($media['videos'])) {
         foreach ($media['videos'] as $vid) {
         $allMedia[] = ['type' => 'video', 'file' => $vid];
         }
         }
         }
         @endphp
         @if($isSingleImage)
         {{-- Old Single Image Format --}}
         <img id="img-zoomer" src="{{ asset('uploads/'.$post->image) }}" alt="Post Image" class="img-fluid" style="object-fit:cover; max-height:400px; width:100%;">
         @elseif($media && count($allMedia) > 0)
         {{-- Unified Media Carousel for images and videos --}}
         <div class="media-container">
            <div id="mixedMediaCarousel-{{ $post->id }}" class="carousel slide" data-bs-ride="false">
               <div class="carousel-inner">
                  @foreach($allMedia as $index => $item)
                  <div class="carousel-item @if($index === 0) active @endif">
                     @if($item['type'] === 'image')
                     <img 
                        src="{{ asset('uploads/' . $item['file']) }}" 
                        alt="Post Image {{ $index + 1 }}" 
                        class="img-fluid d-block w-100"
                        style="width: 100%; height: 400px; object-fit: cover; cursor: pointer;"
                        onclick="openImageModal('{{ asset('uploads/' . $item['file']) }}')"
                        id="img-zoomer"
                        >
                     @elseif($item['type'] === 'video')
                     <div style="background:#000;max-height:400px;overflow:hidden;display:flex;align-items:center;">
                        @php
                        // Store mute preference in localStorage, fallback to true (muted) if not set
                        $mutePref = 'true';
                        @endphp
                        <video 
                        src="{{ asset('uploads/' . $item['file']) }}"
                        class="w-100 post-carousel-video"
                        controls
                        controlsList="nodownload"
                        style="max-height:400px;object-fit:contain;width:100%;background:#000;border-radius:0;margin-bottom:0;"
                        data-carousel-id="mixedMediaCarousel-{{ $post->id }}"
                        {{-- muted attribute will be set by JS based on user preference --}}
                        >
                        Your browser does not support the video tag.
                        </video>
                        <script>
                           // Unified mute/unmute handling for all videos
                           document.addEventListener('DOMContentLoaded', function () {
                               let globalMutePref = localStorage.getItem('globalVideoMuted');
                               if (globalMutePref === null) globalMutePref = "true"; // default: muted
                           
                               function setAllVideosMuted(muted) {
                                   document.querySelectorAll('video.post-carousel-video').forEach(video => {
                                       video.muted = muted;
                                       // For some browsers, changing mute requires reloading playback state
                                       if (!video.paused && !muted && video.readyState >= 2) {
                                           video.play().catch(()=>{});
                                       }
                                   });
                               }
                           
                               // Set the mute state initially
                               setAllVideosMuted(globalMutePref === "true");
                           
                               // Listen for mute/unmute actions on any .post-carousel-video
                               let listening = false;
                               if (!window._global_video_mute_listener) {
                                   window._global_video_mute_listener = true;
                                   document.addEventListener('volumechange', function(event) {
                                       let target = event.target;
                                       if (target && target.classList && target.classList.contains('post-carousel-video')) {
                                           localStorage.setItem('globalVideoMuted', target.muted ? "true" : "false");
                                           setAllVideosMuted(target.muted);
                                       }
                                   }, true);
                                   // For browsers that do not propagate 'volumechange' outside video, use event delegation
                                   document.body.addEventListener('click', function(e) {
                                       if (e.target && e.target.tagName === 'VIDEO' && e.target.classList.contains('post-carousel-video')) {
                                           setTimeout(() => {
                                               localStorage.setItem('globalVideoMuted', e.target.muted ? "true" : "false");
                                               setAllVideosMuted(e.target.muted);
                                           }, 50);
                                       }
                                   }, true);
                               }
                           });
                        </script>
                     </div>
                     <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const video = document.querySelector('#mixedMediaCarousel-{{ $post->id }} .carousel-item.active video.post-carousel-video');
                            if (video) {
                                // Autoplay when the slide is shown
                                video.play().catch(()=>{});
                            }
                        
                            let observer;
                            // Auto-pause when scrolled out of view
                            setTimeout(function () {
                                const vid = document.querySelector('#mixedMediaCarousel-{{ $post->id }} .carousel-item.active video.post-carousel-video');
                                if (!vid) return;
                                observer = new IntersectionObserver((entries) => {
                                    entries.forEach(entry => {
                                        if (entry.isIntersecting) {
                                            vid.play().catch(()=>{});
                                        } else {
                                            vid.pause();
                                        }
                                    });
                                }, { threshold: 0.5 }); // 50% visible threshold
                                observer.observe(vid);
                            }, 400);
                        
                            // When slide changes, play new, pause previous
                            const carousel = document.getElementById('mixedMediaCarousel-{{ $post->id }}');
                            if (carousel) {
                                carousel.addEventListener('slid.bs.carousel', function(event) {
                                    const videos = carousel.querySelectorAll('video.post-carousel-video');
                                    videos.forEach((v, idx) => v.pause());
                                    const newActive = carousel.querySelector('.carousel-item.active video.post-carousel-video');
                                    if (newActive) {
                                        newActive.play().catch(()=>{});
                                        if (observer) observer.disconnect();
                                        observer = new IntersectionObserver((entries) => {
                                            entries.forEach(entry => {
                                                if (entry.isIntersecting) {
                                                    newActive.play().catch(()=>{});
                                                } else {
                                                    newActive.pause();
                                                }
                                            });
                                        }, { threshold: 0.5 });
                                        observer.observe(newActive);
                                    }
                                });
                            }
                        });
                     </script>
                     @endif
                  </div>
                  @endforeach
               </div>
               @if(count($allMedia) > 1)
               <button class="carousel-control-prev p-0 border-0 rounded-circle shadow-sm bg-white d-flex align-items-center justify-content-center" type="button" data-bs-target="#mixedMediaCarousel-{{ $post->id }}" data-bs-slide="prev" style="width:34px;height:34px;top:50%;left:8px;transform:translateY(-50%);position:absolute;z-index:2;">
               <span style="font-size: 1.1rem; line-height: 1;" class="d-flex align-items-center justify-content-center text-secondary">
               <i class="bi bi-chevron-left"></i>
               </span>
               <span class="visually-hidden">Previous</span>
               </button>
               <button class="carousel-control-next p-0 border-0 rounded-circle shadow-sm bg-white d-flex align-items-center justify-content-center" type="button" data-bs-target="#mixedMediaCarousel-{{ $post->id }}" data-bs-slide="next" style="width:34px;height:34px;top:50%;right:8px;transform:translateY(-50%);position:absolute;z-index:2;">
               <span style="font-size: 1.1rem; line-height: 1;" class="d-flex align-items-center justify-content-center text-secondary">
               <i class="bi bi-chevron-right"></i>
               </span>
               <span class="visually-hidden">Next</span>
               </button>
               <div class="carousel-indicators" style="bottom: 10px;">
                  @foreach($allMedia as $index => $item)
                  <button type="button"
                  data-bs-target="#mixedMediaCarousel-{{ $post->id }}"
                  data-bs-slide-to="{{ $index }}"
                  @if($index === 0) class="active" aria-current="true" @endif
                  aria-label="Slide {{ $index + 1 }}"
                  style="width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 1.5px solid #999; margin-right: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                  </button>
                  @endforeach
               </div>
               @endif
            </div>
            <script>
               document.addEventListener('DOMContentLoaded', function () {
                   var carousel = document.getElementById('mixedMediaCarousel-{{ $post->id }}');
                   if (!carousel) return;
                   var videos = carousel.querySelectorAll('video.post-carousel-video');
                   var items = carousel.querySelectorAll('.carousel-item');
               
                   // Helper to pause all videos except current
                   function pauseOtherVideos(activeIndex) {
                       videos.forEach(function(video, idx) {
                           if(idx !== activeIndex && !video.paused) { 
                               video.pause(); 
                               video.currentTime = 0;
                           }
                       });
                   }
               
                   // Initial autoplay if first media is video
                   if(items.length && items[0].querySelector('video.post-carousel-video')) {
                       var firstVideo = items[0].querySelector('video.post-carousel-video');
                       firstVideo.play().catch(()=>{});
                   }
               
                   // Listen for slide event to control autoplay & autostop
                   carousel.addEventListener('slide.bs.carousel', function (event) {
                       // Pause all videos before slide
                       pauseOtherVideos(-1);
                   });
               
                   carousel.addEventListener('slid.bs.carousel', function (event) {
                       var activeIndex = event.to ?? Array.prototype.findIndex.call(items, function(item){ return item.classList.contains('active'); });
                       // Sometimes event.to is not set, fallback to DOM
                       if(typeof activeIndex !== 'number' || activeIndex < 0) {
                           items.forEach(function(item, idx){
                               if(item.classList.contains('active')) activeIndex = idx;
                           });
                       }
                       
                       items.forEach(function(item, idx){
                           var v = item.querySelector('video.post-carousel-video');
                           if(idx===activeIndex && v) {
                               v.play().catch(()=>{});
                           } else if(v) {
                               v.pause();
                               v.currentTime = 0;
                           }
                       });
                   });
               });
            </script>
         </div>
         @endif
         {{-- Card Footer: Social Actions --}}
         <div class="bg-white rounded-bottom border-0 pt-0">
            {{-- Action Buttons --}}
            <div class="d-flex justify-content-around text-muted border-top pt-2 pb-2">
               @auth
               <button 
                  class="btn text-muted d-flex align-items-center like-btn {{ $post->isLikedBy(Auth::id()) ? 'liked text-primary' : '' }}" 
                  data-post-id="{{ $post->id }}"
                  >
               <i class="bi {{ $post->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i> 
               <span class="action-count">{{ $post->likesCount() }}</span>
               </button>
               @else
               <button 
                  class="btn text-muted d-flex align-items-center" 
                  onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('authModal')); modal.show();"
                  type="button"
                  >
               <i class="bi bi-hand-thumbs-up me-1"></i>
               <span class="action-count">{{ $post->likesCount() }}</span>
               </button>
               @endauth
               <button 
                  class="btn text-muted d-flex align-items-center comment-toggle-btn" 
                  data-post-id="{{ $post->id }}"
                  @guest
                  onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('authModal')); modal.show();"
                  @endguest
                  >
               <i class="bi bi-chat-left-text me-1"></i> 
               <span class="action-count">{{ $post->allComments()->count() }}</span>
               </button>
               <button class="btn text-muted d-flex align-items-center share-btn" 
                  data-post-id="{{ $post->id }}"
                  data-post-url="{{ url('/post/' . $post->slug) }}"
                  data-post-title="{{ $post->title }}">
               <i class="bi bi-share me-1"></i>
               </button>
            </div>
         </div>
         {{-- Comments Section --}}
         <div class="comments-section" id="comments-section-{{ $post->id }}" style="display: none;">
            {{-- Comment Input --}}
            <div class="p-3 border-top bg-light">
               <div class="d-flex">
                  <img src="{{ asset('profile-image/' . (Auth::user()->image ?? 'default.png')) }}" 
                     class="rounded-circle me-2" 
                     style="width:32px; height:32px; object-fit:cover;" 
                     alt="Your Profile">
                  <div class="flex-grow-1">
                     <form action="{{ route('comment.store') }}" method="POST" class="comment-form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        <input type="hidden" name="post_id" value="{{ $post->id }}">
                        <div class="comment-input-container bg-white rounded-pill px-3 py-2 border">
                           <input type="text" 
                              name="content"
                              class="form-control border-0 bg-transparent p-0 comment-input" 
                              placeholder="Write a comment...">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-2 comment-tools" style="display: none;">
                           <div class="comment-actions">
                              <button type="button" class="btn btn-sm text-muted p-0 me-2">
                              <i class="bi bi-emoji-smile"></i>
                              </button>
                              <button type="button" class="btn btn-sm text-muted p-0 me-2">
                              <i class="bi bi-camera"></i>
                              </button>
                              <button type="button" class="btn btn-sm text-muted p-0">
                              <i class="bi bi-image"></i>
                              </button>
                           </div>
                           @auth
                           <button type="submit" class="btn btn-primary btn-sm">
                           Post
                           </button>
                           @else
                           <button type="button" class="btn btn-primary btn-sm" onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('authModal')); modal.show();">
                           Login to Post
                           </button>
                           @endauth
                        </div>
                     </form>
                  </div>
               </div>
            </div>
            {{-- Comments List --}}
            <div class="comments-list" id="comments-list-{{ $post->id }}">
               @foreach($post->comments as $comment)
               <div class="comment-item p-3 border-bottom" data-comment-id="{{ $comment->id }}" id="comment-{{ $comment->id }}">
                  <div class="d-flex">
                     <img src="{{ asset('profile-image/' . ($comment->user->image ?? 'default.png')) }}" 
                        class="rounded-circle me-2" 
                        style="width:32px; height:32px; object-fit:cover;" 
                        alt="{{ $comment->user->name }}">
                     <div class="flex-grow-1">
                        <div class="comment-content">
                           <h6 class="mb-1" style="font-size: 13px; font-weight: 600;">
                              <a href="/{{ $comment->user->username }}">{{ $comment->user->name }}</a>
                           </h6>
                           <p class="mb-0" style="font-size: 14px;">{{ $comment->content }}</p>
                        </div>
                        <div class="comment-actions-bar d-flex align-items-center mt-1">
                           <small class="text-muted me-3">{{ $comment->created_at->diffForHumans() }}</small>
                           @auth
                           <button class="btn btn-sm text-muted p-0 me-2 comment-like-btn {{ $comment->isLikedBy(Auth::id()) ? 'active text-primary' : '' }}" 
                              data-comment-id="{{ $comment->id }}">
                           <i class="bi {{ $comment->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i>
                           <span class="action-count">{{ $comment->likesCount() }}</span>
                           </button>
                           @auth
                           @if(Auth::id() == $comment->user_id)
                           {{-- Delete Button --}}
                           <button class="btn btn-sm text-danger p-0 me-2"
                              data-bs-toggle="modal" data-bs-target="#commentModal{{ $comment->id }}">
                           <i class="bi bi-trash me-1"></i> Delete
                           </button>
                           @else
                           {{-- Reply Button --}}
                           <button class="btn btn-sm text-muted p-0 me-2 reply-btn"
                              data-comment-id="{{ $comment->id }}">
                           <i class="bi bi-reply me-1"></i> Reply
                           </button>
                           @endif
                           @endauth
                           @else
                           <span class="text-muted me-2" style="font-size: 12px;">
                           <i class="bi bi-hand-thumbs-up me-1"></i>
                           <span class="action-count">{{ $comment->likes_count ?? 0 }}</span>
                           </span>
                           <a href="javascript:void(0);" 
                              class="btn btn-sm text-muted p-0 me-2" 
                              style="font-size: 12px;"
                              onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('authModal')); modal.show();">
                           <i class="bi bi-reply me-1"></i>
                           Login to Reply
                           </a>
                           @endauth
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="commentModal{{ $comment->id }}" tabindex="-1" aria-hidden="true">
                           <div class="modal-dialog">
                              <div class="modal-content">
                                 <div class="modal-header">
                                    <h5 class="modal-title text-danger">Delete Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                 </div>
                                 <div class="modal-body">
                                    Are you sure, you want to delete this comment?
                                 </div>
                                 <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-danger delete-comment-btn" data-comment-id="{{ $comment->id }}">Delete</button>
                                 </div>
                              </div>
                           </div>
                        </div>
                        {{-- Reply Input --}}
                        @auth
                        <div class="reply-input mt-2" style="display: none;" data-comment-id="{{ $comment->id }}">
                           <div class="d-flex">
                              <img src="{{ asset('profile-image/' . (Auth::user()->image ?? 'default.png')) }}" 
                                 class="rounded-circle me-2" 
                                 style="width:28px; height:28px; object-fit:cover;" 
                                 alt="Your Profile">
                              <form action="{{ route('comment.store') }}" method="post" class="flex-grow-1 d-flex reply-form">
                                 @csrf       
                                 <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                 <input type="hidden" name="post_id" value="{{ $post->id }}">
                                 <input type="hidden" name="comment_id" value="{{ $comment->id }}">
                                 <input type="text" 
                                    name="content"
                                    class="form-control form-control-sm reply-text" 
                                    placeholder="Reply to {{ $comment->user->name }}..."  
                                    data-comment-id="{{ $comment->id }}">
                                 <button type="submit" class="btn btn-primary btn-sm ms-2">
                                 Reply
                                 </button>
                              </form>
                           </div>
                        </div>
                        @endauth
                        {{-- Replies --}}
                        @if($comment->replies && $comment->replies->count() > 0)
                        <div class="replies mt-2" data-comment-id="{{ $comment->id }}">
                           @foreach($comment->replies as $reply)
                           <div class="reply-item d-flex mb-2" data-reply-id="{{ $reply->id }}" id="comment-{{ $reply->id }}">
                              <img src="{{ asset('profile-image/' . ($reply->user->image ?? 'default.png')) }}" 
                                 class="rounded-circle me-2" 
                                 style="width:28px; height:28px; object-fit:cover;" 
                                 alt="{{ $reply->user->name }}">
                              <div class="flex-grow-1">
                                 <div class="comment-content" style="font-size: 13px;">
                                    <h6 class="mb-1" style="font-size: 12px; font-weight: 600;">
                                       <a href="/{{ $reply->user->username }}">{{ $reply->user->name }}</a>
                                    </h6>
                                    <p class="mb-0">{{ $reply->content }}</p>
                                 </div>
                                 <div class="comment-actions-bar d-flex align-items-center mt-1">
                                    <small class="text-muted me-2" style="font-size: 11px;">
                                    {{ $reply->created_at->diffForHumans() }}
                                    </small>
                                    @auth
                                    <button class="btn btn-sm text-muted p-0 me-2 reply-like-btn {{ $reply->isLikedBy(Auth::id()) ? 'active text-primary' : '' }}" 
                                       data-reply-id="{{ $reply->id }}" 
                                       style="font-size: 11px;">
                                    <i class="bi {{ $reply->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i>
                                    <span class="action-count">{{ $reply->likesCount() }}</span>
                                    </button>
                                    @auth
                                    @if(Auth::id() == $reply->user_id)
                                    {{-- Delete Reply Button --}}
                                    <button class="btn btn-sm text-danger p-0 me-2" 
                                       style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#replyModal{{ $reply->id }}">
                                    <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                    @else
                                    {{-- Nested Reply Button --}}
                                    <button class="btn btn-sm text-muted p-0 nested-reply-btn"
                                       data-reply-to="{{ $reply->user->name }}" 
                                       data-comment-id="{{ $comment->id }}" style="font-size: 11px;">
                                    <i class="bi bi-reply me-1"></i>
                                    Reply
                                    </button>
                                    @endif
                                    @endauth
                                    @else
                                    <span class="text-muted me-2" style="font-size: 11px;">
                                    <i class="bi bi-hand-thumbs-up me-1"></i>
                                    <span class="action-count">{{ $reply->likes_count ?? 0 }}</span>
                                    </span>
                                    <button 
                                       type="button" 
                                       class="btn btn-sm text-muted p-0" 
                                       style="font-size: 11px;" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#authModal"
                                       >
                                    <i class="bi bi-reply me-1"></i>
                                    Login to Reply
                                    </button>
                                    @endauth
                                 </div>
                                 <div class="modal fade" id="replyModal{{ $reply->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                       <div class="modal-content">
                                          <div class="modal-header">
                                             <h5 class="modal-title text-danger">Delete Reply</h5>
                                             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                          </div>
                                          <div class="modal-body">
                                             Are you sure, you want to delete this reply?
                                          </div>
                                          <div class="modal-footer">
                                             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                             <button type="button" class="btn btn-danger delete-reply-btn" data-reply-id="{{ $reply->id }}">Delete</button>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           @endforeach
                        </div>
                        @endif
                     </div>
                  </div>
               </div>
               @endforeach
            </div>
            {{-- Load More Comments --}}
            <div class="text-center p-3 border-top load-more-section" id="load-more-comments-{{ $post->id }}" style="display: none;">
               <button class="btn text-muted load-more-comments" data-post-id="{{ $post->id }}">
               <i class="bi bi-arrow-down-circle me-1"></i>
               Load more comments (<span class="remaining-comments">5</span>)
               </button>
            </div>
         </div>
      </div>
   </div>
</div>








<style>
   .comment-item {
   transition: background-color 0.2s ease;
   }
   .comment-item:hover {
   background-color: #f8f9fa;
   }
   .comment-like-btn.active,
   .reply-like-btn.active {
   color: #0d6efd !important;
   }
   .reply-item {
   margin-left: 20px;
   border-left: 2px solid #e9ecef;
   padding-left: 15px;
   }
</style>

<script>
   document.addEventListener('DOMContentLoaded', function() {
       
       // ===== POST LIKE BUTTON =====
       document.addEventListener('click', function(e) {
           if (e.target.closest('.like-btn')) {
               e.preventDefault();
               const btn = e.target.closest('.like-btn');
               const postId = btn.dataset.postId;
               const actionCount = btn.querySelector('.action-count');
               const icon = btn.querySelector('i');
               
               // AJAX Request
               fetch('{{ route("post.like") }}', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': '{{ csrf_token() }}'
                   },
                   body: JSON.stringify({
                       post_id: postId
                   })
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       // Update UI
                       actionCount.textContent = data.likes_count;
                       
                       if (data.liked) {
                           btn.classList.add('liked', 'text-primary');
                           icon.className = 'bi bi-hand-thumbs-up-fill me-1';
                       } else {
                           btn.classList.remove('liked', 'text-primary');
                           icon.className = 'bi bi-hand-thumbs-up me-1';
                       }
                   }
               })
               .catch(error => console.error('Error:', error));
           }
       });
       
       // ===== COMMENT/REPLY LIKE BUTTON =====
       document.addEventListener('click', function(e) {
           if (e.target.closest('.comment-like-btn') || e.target.closest('.reply-like-btn')) {
               e.preventDefault();
               const btn = e.target.closest('.comment-like-btn') || e.target.closest('.reply-like-btn');
               const commentId = btn.dataset.commentId || btn.dataset.replyId;
               const actionCount = btn.querySelector('.action-count');
               const icon = btn.querySelector('i');
               
               // AJAX Request
               fetch('{{ route("comment.like") }}', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': '{{ csrf_token() }}'
                   },
                   body: JSON.stringify({
                       comment_id: commentId
                   })
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       // Update UI
                       actionCount.textContent = data.likes_count;
                       
                       if (data.liked) {
                           btn.classList.add('active', 'text-primary');
                           icon.className = 'bi bi-hand-thumbs-up-fill me-1';
                       } else {
                           btn.classList.remove('active', 'text-primary');
                           icon.className = 'bi bi-hand-thumbs-up me-1';
                       }
                   }
               })
               .catch(error => console.error('Error:', error));
           }
       });
   });
</script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       
       // ===== AJAX COMMENT SUBMIT (MAIN COMMENTS) =====
       document.addEventListener('submit', function(e) {
           if (e.target.classList.contains('comment-form')) {
               e.preventDefault();
               
               const form = e.target;
               const postId = form.querySelector('input[name="post_id"]').value;
               const content = form.querySelector('input[name="content"]').value;
               const userId = form.querySelector('input[name="user_id"]').value;
               
               if (!content.trim()) {
                   alert('Please write something...');
                   return;
               }
               
               const submitBtn = form.querySelector('button[type="submit"]');
               if (submitBtn) {
                   submitBtn.disabled = true;
                   submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Posting...';
               }
               
               // Get CSRF token
               const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                document.querySelector('input[name="_token"]')?.value;
               
               fetch('/comment/store', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'Accept': 'application/json',
                       'X-CSRF-TOKEN': csrfToken,
                       'X-Requested-With': 'XMLHttpRequest'
                   },
                   body: JSON.stringify({
                       post_id: postId,
                       user_id: userId,
                       content: content,
                       comment_id: null
                   })
               })
               .then(response => {
                   console.log('Response status:', response.status);
                   if (!response.ok) {
                       throw new Error('Network response was not ok');
                   }
                   return response.json();
               })
               .then(data => {
                   console.log('Success data:', data);
                   
                   if (data.success) {
                       const commentsList = document.getElementById(`comments-list-${postId}`);
                       
                       const newCommentHTML = `
                           <div class="comment-item p-3 border-bottom" data-comment-id="${data.comment.id}">
                               <div class="d-flex">
                                   <img src="${data.comment.user_image}" 
                                       class="rounded-circle me-2" 
                                       style="width:32px; height:32px; object-fit:cover;" 
                                       alt="${data.comment.user_name}">
                                   <div class="flex-grow-1">
                                       <div class="comment-content">
                                           <h6 class="mb-1" style="font-size: 13px; font-weight: 600;">
                                               ${data.comment.user_name}
                                           </h6>
                                           <p class="mb-0" style="font-size: 14px;">${escapeHtml(data.comment.content)}</p>
                                       </div>
                                       <div class="comment-actions-bar d-flex align-items-center mt-1">
                                           <small class="text-muted me-3">Just now</small>
                                           <button class="btn btn-sm text-muted p-0 me-2 comment-like-btn" 
                                                   data-comment-id="${data.comment.id}">
                                               <i class="bi bi-hand-thumbs-up me-1"></i>
                                               <span class="action-count">0</span>
                                           </button>
                                           <button class="btn btn-sm text-muted p-0 me-2 reply-btn" data-comment-id="${data.comment.id}">
                                               <i class="bi bi-reply me-1"></i>
                                               Reply
                                           </button>
                                       </div>
                                       <div class="reply-input mt-2" style="display: none;" data-comment-id="${data.comment.id}">
                                           <div class="d-flex">
                                               <img src="${data.current_user_image}" 
                                                   class="rounded-circle me-2" 
                                                   style="width:28px; height:28px; object-fit:cover;" 
                                                   alt="Your Profile">
                                               <form class="flex-grow-1 d-flex reply-form">
                                                   <input type="hidden" name="user_id" value="${userId}">
                                                   <input type="hidden" name="post_id" value="${postId}">
                                                   <input type="hidden" name="comment_id" value="${data.comment.id}">
                                                   <input type="text" 
                                                       name="content"
                                                       class="form-control form-control-sm reply-text" 
                                                       placeholder="Reply to ${data.comment.user_name}..."  
                                                       data-comment-id="${data.comment.id}">
                                                   <button type="submit" class="btn btn-primary btn-sm ms-2">
                                                       Reply
                                                   </button>
                                               </form>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       `;
                       
                       commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);
                       
                       // Update comment count with actual count from server
                       const commentBtn = document.querySelector(`.comment-toggle-btn[data-post-id="${postId}"]`);
                       if (commentBtn && data.total_comments_count) {
                           const countSpan = commentBtn.querySelector('.action-count');
                           countSpan.textContent = data.total_comments_count;
                       }
                       
                       form.reset();
                       const tools = form.querySelector('.comment-tools');
                       if (tools) tools.style.display = 'none';
                       
                       const newComment = commentsList.firstElementChild;
                       newComment.style.backgroundColor = '#d4edda';
                       setTimeout(() => {
                           newComment.style.transition = 'background-color 0.5s';
                           newComment.style.backgroundColor = '';
                       }, 1000);
                       
                       newComment.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                   } else {
                       alert('Failed to post comment. Please try again.');
                   }
               })
               .catch(error => {
                   console.error('Error details:', error);
                   alert('Error: ' + error.message);
               })
               .finally(() => {
                   if (submitBtn) {
                       submitBtn.disabled = false;
                       submitBtn.innerHTML = 'Post';
                   }
               });
           }
       });
       
       // ===== AJAX REPLY SUBMIT =====
       document.addEventListener('submit', function(e) {
           if (e.target.classList.contains('reply-form')) {
               e.preventDefault();
               
               const form = e.target;
               const postId = form.querySelector('input[name="post_id"]').value;
               const commentId = form.querySelector('input[name="comment_id"]').value;
               const content = form.querySelector('input[name="content"]').value;
               const userId = form.querySelector('input[name="user_id"]').value;
               
               if (!content.trim()) {
                   alert('Please write something...');
                   return;
               }
               
               const submitBtn = form.querySelector('button[type="submit"]');
               if (submitBtn) {
                   submitBtn.disabled = true;
                   submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
               }
               
               const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                                document.querySelector('input[name="_token"]')?.value;
               
               fetch('/comment/store', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'Accept': 'application/json',
                       'X-CSRF-TOKEN': csrfToken,
                       'X-Requested-With': 'XMLHttpRequest'
                   },
                   body: JSON.stringify({
                       post_id: postId,
                       user_id: userId,
                       content: content,
                       comment_id: commentId
                   })
               })
               .then(response => {
                   console.log('Reply response status:', response.status);
                   if (!response.ok) {
                       throw new Error('Network response was not ok');
                   }
                   return response.json();
               })
               .then(data => {
                   console.log('Reply success data:', data);
                   
                   if (data.success) {
                       const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
                       let repliesContainer = commentItem.querySelector(`.replies[data-comment-id="${commentId}"]`);
                       
                       if (!repliesContainer) {
                           const replyInput = commentItem.querySelector(`.reply-input[data-comment-id="${commentId}"]`);
                           repliesContainer = document.createElement('div');
                           repliesContainer.className = 'replies mt-2';
                           repliesContainer.setAttribute('data-comment-id', commentId);
                           replyInput.parentNode.insertBefore(repliesContainer, replyInput);
                       }
                       
                       const newReplyHTML = `
                           <div class="reply-item d-flex mb-2" data-reply-id="${data.comment.id}">
                               <img src="${data.comment.user_image}" 
                                   class="rounded-circle me-2" 
                                   style="width:28px; height:28px; object-fit:cover;" 
                                   alt="${data.comment.user_name}">
                               <div class="flex-grow-1">
                                   <div class="comment-content" style="font-size: 13px;">
                                       <h6 class="mb-1" style="font-size: 12px; font-weight: 600;">
                                           ${data.comment.user_name}
                                       </h6>
                                       <p class="mb-0">${escapeHtml(data.comment.content)}</p>
                                   </div>
                                   <div class="comment-actions-bar d-flex align-items-center mt-1">
                                       <small class="text-muted me-2" style="font-size: 11px;">
                                           Just now
                                       </small>
                                       <button class="btn btn-sm text-muted p-0 me-2 reply-like-btn" 
                                               data-reply-id="${data.comment.id}" 
                                               style="font-size: 11px;">
                                           <i class="bi bi-hand-thumbs-up me-1"></i>
                                           <span class="action-count">0</span>
                                       </button>
                                       <button class="btn btn-sm text-muted p-0 nested-reply-btn" 
                                           data-reply-to="${data.comment.user_name}" 
                                           data-comment-id="${commentId}" 
                                           style="font-size: 11px;">
                                           <i class="bi bi-reply me-1"></i>
                                           Reply
                                       </button>
                                   </div>
                               </div>
                           </div>
                       `;
                       
                       repliesContainer.insertAdjacentHTML('beforeend', newReplyHTML);
                       
                       // Update comment count with actual count from server
                       const commentBtn = document.querySelector(`.comment-toggle-btn[data-post-id="${postId}"]`);
                       if (commentBtn && data.total_comments_count) {
                           const countSpan = commentBtn.querySelector('.action-count');
                           countSpan.textContent = data.total_comments_count;
                       }
                       
                       const newReply = repliesContainer.lastElementChild;
                       newReply.style.backgroundColor = '#d4edda';
                       setTimeout(() => {
                           newReply.style.transition = 'background-color 0.5s';
                           newReply.style.backgroundColor = '';
                       }, 1000);
                       
                       form.reset();
                       form.closest('.reply-input').style.display = 'none';
                       
                       newReply.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                   } else {
                       alert('Failed to post reply. Please try again.');
                   }
               })
               .catch(error => {
                   console.error('Reply error details:', error);
                   alert('Error: ' + error.message);
               })
               .finally(() => {
                   if (submitBtn) {
                       submitBtn.disabled = false;
                       submitBtn.innerHTML = 'Reply';
                   }
               });
           }
       });
       
       function escapeHtml(text) {
           const map = {
               '&': '&amp;',
               '<': '&lt;',
               '>': '&gt;',
               '"': '&quot;',
               "'": '&#039;'
           };
           return text.replace(/[&<>"']/g, m => map[m]);
       }
       
   });
   
   
   
   // Delete Comment
   $(document).on("click", ".delete-comment-btn", function () {
    let id = $(this).data("comment-id");
    let item = $(this).closest(".comment-item");
   
    $.post("{{ route('comment.delete') }}", {
        comment_id: id,
        _token: "{{ csrf_token() }}"
    }, function (res) {
        if (res.success) {
            item.remove();
        }
    });
   });
   
   // Delete Reply
   $(document).on("click", ".delete-reply-btn", function () {
    let id = $(this).data("reply-id");
    let item = $(this).closest(".reply-item");
   
    $.post("{{ route('comment.delete') }}", {
        comment_id: id,
        _token: "{{ csrf_token() }}"
    }, function (res) {
        if (res.success) {
            item.remove();
        }
    });
   });
   
   
   $(document).on('click', '.delete-comment-btn', function() {
    let commentId = $(this).data('comment-id');
    let modalElement = document.getElementById('commentModal' + commentId);
    let modal = bootstrap.Modal.getInstance(modalElement);
    
    $.ajax({
        
        success: function(response) {
            // Modal properly বন্ধ করুন
            modal.hide();
            
            // Comment remove করুন
            $('.comment-item[data-comment-id="' + commentId + '"]').fadeOut(function() {
                $(this).remove();
            });
        },
        error: function(xhr) {
            modal.hide();
        }
    });
   });
   
   
   $(document).on('click', '.delete-reply-btn', function() {
    let replyId = $(this).data('reply-id');
    let modalElement = document.getElementById('replyModal' + replyId);
    let modal = bootstrap.Modal.getInstance(modalElement);
    
    $.ajax({
        success: function(response) {
            // Modal properly বন্ধ করুন
            modal.hide();
            
            // reply remove করুন
            $('.reply-item[data-reply-id="' + replyId + '"]').fadeOut(function() {
                $(this).remove();
            });
        },
        error: function(xhr) {
            modal.hide();
        }
    });
   });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#comment-')) {
        const commentId = hash.replace('#comment-', '');
        const commentElement = document.getElementById('comment-' + commentId);
        
        if (commentElement) {
            const postId = commentElement.closest('.card').querySelector('.comment-toggle-btn')?.dataset.postId;
            if (postId) {
                const commentsSection = document.getElementById(`comments-section-${postId}`);
                if (commentsSection) {
                    commentsSection.style.display = 'block';
                }
            }
            
            setTimeout(() => {
                commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                commentElement.style.backgroundColor = '#fff3cd';
                setTimeout(() => {
                    commentElement.style.transition = 'background-color 1s';
                    commentElement.style.backgroundColor = '';
                }, 2000);
            }, 300);
        }
    }
});
</script>
@include('frontend.body.share-button')
