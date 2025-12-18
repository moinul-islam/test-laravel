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
         @php
            $maxLength = 200;
            $desc = $post->description;
            $descLength = mb_strlen($desc);
            $hasDesc = $descLength > 0;
            // Calculate which style to use:
            $bodyStyle = 'padding-top:0;';
            if(!$hasDesc) $bodyStyle .= 'padding-bottom:0;';
         @endphp
         <div class="card-body" style="{{ $bodyStyle }}">
            <!-- <h2>{{ $post->title }}</h2> -->
            @if($descLength > $maxLength)
            <p class="m-0 post-desc-short" style="display: block;">
               {{ mb_substr($desc, 0, $maxLength) }}...
               <a href="javascript:void(0);" class="see-more-link text-primary" onclick="toggleDescription(this)">See more</a>
            </p>
            <p class="m-0 post-desc-full" style="display: none;">
               {{ $desc }}
               <a href="javascript:void(0);" class="see-less-link text-primary" onclick="toggleDescription(this)">See less</a>
            </p>
            @elseif($descLength > 0)
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
                        style="width: 100%; {{ count($allMedia) === 1 ? 'max-height:400px;' : 'height:400px;' }} object-fit: cover; cursor: pointer;"
                        onclick="openImageModal('{{ asset('uploads/' . $item['file']) }}')"
                        id="img-zoomer"
                        >
                        @elseif($item['type'] === 'video')
<div style="background:#000;max-height:400px;overflow:hidden;display:flex;align-items:center;">
    <video 
        src="{{ asset('uploads/' . $item['file']) }}"
        class="w-100 post-carousel-video"
        controls
        controlsList="nodownload"
        style="max-height:400px;object-fit:contain;width:100%;background:#000;border-radius:0;margin-bottom:0;"
        data-carousel-id="mixedMediaCarousel-{{ $post->id }}"
        playsinline
        webkit-playsinline>
        Your browser does not support the video tag.
    </video>
</div>
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