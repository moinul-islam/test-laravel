<!-- Posts Cards -->
<div class="col-12">
   <div class="row">
      <div id="posts-container">
         @forelse($posts as $post)
         <div class="mb-4 post-item" data-post-id="{{ $post->id }}">
            {{-- Original Card with Static Comment System --}}
            <div class="card">
               {{-- Card Body: User + Description --}}
               <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                     <div class="d-flex align-items-center">
                        <img src="{{ $post->user->image ? asset('profile-image/'.$post->user->image) : 'https://cdn-icons-png.flaticon.com/512/219/219983.png' }}"
                           class="rounded-circle me-2"
                           alt="Profile Photo"
                           style="width:40px; height:40px; object-fit:cover;">
                        <div>
                           <h6 class="mb-0">
                              <a href="{{ route('profile.show', $post->user->username) }}" class="text-decoration-none text-dark">
                              {{ $post->user->name }}
                              </a>
                           </h6>
                           @if($post->category)
                           <small class="text-muted"><i class="bi bi-grid"></i> {{ $post->category->name }}</small>
                           @endif
                           <small class="text-muted"><i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}</small>
                        </div>
                     </div>
                     <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                           @if(auth()->id() == $post->user_id)
                           <li><a class="dropdown-item" href="#"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
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
                           <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-person-x me-2"></i>Block</a></li>
                           @endif
                        </ul>
                     </div>
                  </div>
                  {{-- Post Description with Read More --}}
                  @if($post->description)
                  <p class="card-text post-description" id="post-description-{{ $post->id }}" style="max-height:75px; overflow:hidden;">
                     {{ $post->description }}
                  </p>
                  @if(strlen($post->description) > 150)
                  <a href="javascript:void(0);" class="read-more text-primary" data-post-id="{{ $post->id }}">Read more</a>
                  @endif
                  @endif
               </div>
               {{-- Card Image --}}
               @if($post->image)
               <img id="img-zoomer" src="{{ asset('uploads/'.$post->image) }}" alt="Post Image" class="img-fluid" style="object-fit:cover; max-height:400px; width:100%;">
               @endif
               {{-- Card Footer: Social Actions --}}
               <div class="card-footer bg-white rounded-bottom border-0 pt-0">
                  {{-- Action Buttons - Only Icons and Counts --}}
                  <div class="d-flex justify-content-around text-muted border-top pt-2">
                     <button class="btn btn-link text-muted d-flex align-items-center like-btn" data-post-id="{{ $post->id }}">
                     <i class="bi bi-hand-thumbs-up me-1"></i> 
                     <span class="action-count">24</span>
                     </button>
                     <button class="btn btn-link text-muted d-flex align-items-center comment-toggle-btn" data-post-id="{{ $post->id }}">
                     <i class="bi bi-chat-left-text me-1"></i> 
                     <span class="action-count">8</span>
                     </button>
                     <button class="btn btn-link text-muted d-flex align-items-center share-btn" data-post-id="{{ $post->id }}">
                     <i class="bi bi-share me-1"></i> 
                     <span class="action-count">3</span>
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
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 me-2">
                                    <i class="bi bi-emoji-smile"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 me-2">
                                    <i class="bi bi-camera"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0">
                                    <i class="bi bi-image"></i>
                                    </button>
                                 </div>
                                 <button type="submit" class="btn btn-primary btn-sm">
                                 Post
                                 </button>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
                  {{-- Comments List --}}
                  <div class="comments-list" id="comments-list-{{ $post->id }}">
                     <!-- Single Comment Item -->
                     @foreach($post->comments as $comment)
                     <div class="comment-item p-3 border-bottom" data-comment-id="{{ $comment->id }}">
                        <div class="d-flex">
                           <img src="{{ asset('profile-image/' . ($comment->user->image ?? 'default.png')) }}" 
                              class="rounded-circle me-2" 
                              style="width:32px; height:32px; object-fit:cover;" 
                              alt="{{ $comment->user->name }}">
                           <div class="flex-grow-1">
                              <div class="comment-content">
                                 <h6 class="mb-1" style="font-size: 13px; font-weight: 600;">
                                    {{ $comment->user->name }}
                                 </h6>
                                 <p class="mb-0" style="font-size: 14px;">{{ $comment->content }}</p>
                              </div>
                              <div class="comment-actions-bar d-flex align-items-center mt-1">
                                 <small class="text-muted me-3">{{ $comment->created_at->diffForHumans() }}</small>
                                 
                                 @auth
                                    <button class="btn btn-link btn-sm text-muted p-0 me-2 comment-like-btn" 
                                       data-comment-id="{{ $comment->id }}">
                                    <i class="bi bi-hand-thumbs-up me-1"></i>
                                    <span class="action-count">{{ $comment->likes_count ?? 0 }}</span>
                                    </button>
                                    <button class="btn btn-link btn-sm text-muted p-0 me-2 reply-btn" data-comment-id="{{ $comment->id }}">
                                    <i class="bi bi-reply me-1"></i>
                                    Reply
                                    </button>
                                 @else
                                    <span class="text-muted me-2" style="font-size: 12px;">
                                       <i class="bi bi-hand-thumbs-up me-1"></i>
                                       <span class="action-count">{{ $comment->likes_count ?? 0 }}</span>
                                    </span>
                                    <a href="{{ route('login') }}" class="btn btn-link btn-sm text-muted p-0 me-2" style="font-size: 12px;">
                                       <i class="bi bi-reply me-1"></i>
                                       Login to Reply
                                    </a>
                                 @endauth
                              </div>
                              
                              <!-- Reply Input (Only for authenticated users) -->
                              @auth
                                 <div class="reply-input mt-2" style="display: none;" data-comment-id="{{ $comment->id }}">
                                    <div class="d-flex">
                                       <img src="{{ asset('profile-image/' . (Auth::user()->image ?? 'default.png')) }}" 
                                          class="rounded-circle me-2" 
                                          style="width:28px; height:28px; object-fit:cover;" 
                                          alt="Your Profile">
                                       <form action="{{ route('comment.store') }}" method="post">
                                          @csrf       
                                          <div class="flex-grow-1">
                                             <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                             <input type="hidden" name="post_id" value="{{ $post->id }}">
                                             <input type="hidden" name="comment_id" value="{{ $comment->id }}">
                                             <input type="text" 
                                                name="content"
                                                class="form-control form-control-sm reply-text" 
                                                placeholder="Reply to {{ $comment->user->name }}..."  
                                                data-comment-id="{{ $comment->id }}">
                                          </div>
                                          <button class="btn btn-primary btn-sm ms-2 submit-reply" data-comment-id="{{ $comment->id }}">
                                          Reply
                                          </button>
                                       </form>
                                    </div>
                                 </div>
                              @endauth
                              
                              <!-- Dynamic Replies -->
                              @if($comment->replies && $comment->replies->count() > 0)
                              <div class="replies mt-2" data-comment-id="{{ $comment->id }}">
                                 @foreach($comment->replies as $reply)
                                 <div class="reply-item d-flex mb-2" data-reply-id="{{ $reply->id }}">
                                    <img src="{{ asset('profile-image/' . ($reply->user->image ?? 'default.png')) }}" 
                                       class="rounded-circle me-2" 
                                       style="width:28px; height:28px; object-fit:cover;" 
                                       alt="{{ $reply->user->name }}">
                                    <div class="flex-grow-1">
                                       <div class="comment-content" style="font-size: 13px;">
                                          <h6 class="mb-1" style="font-size: 12px; font-weight: 600;">
                                             {{ $reply->user->name }}
                                          </h6>
                                          <p class="mb-0">{{ $reply->content }}</p>
                                       </div>
                                       <div class="comment-actions-bar d-flex align-items-center mt-1">
                                          <small class="text-muted me-2" style="font-size: 11px;">
                                          {{ $reply->created_at->diffForHumans() }}
                                          </small>
                                          
                                          @auth
                                             <button class="btn btn-link btn-sm text-muted p-0 me-2 reply-like-btn" 
                                                data-reply-id="{{ $reply->id }}" style="font-size: 11px;">
                                             <i class="bi bi-hand-thumbs-up me-1"></i>
                                             <span class="action-count">{{ $reply->likes_count ?? 0 }}</span>
                                             </button>
                                             <button class="btn btn-link btn-sm text-muted p-0 nested-reply-btn" 
                                                data-reply-to="{{ $reply->user->name }}" 
                                                data-comment-id="{{ $comment->id }}" 
                                                style="font-size: 11px;">
                                             <i class="bi bi-reply me-1"></i>
                                             Reply
                                             </button>
                                          @else
                                             <span class="text-muted me-2" style="font-size: 11px;">
                                                <i class="bi bi-hand-thumbs-up me-1"></i>
                                                <span class="action-count">{{ $reply->likes_count ?? 0 }}</span>
                                             </span>
                                             <a href="{{ route('login') }}" class="btn btn-link btn-sm text-muted p-0" style="font-size: 11px;">
                                                <i class="bi bi-reply me-1"></i>
                                                Login to Reply
                                             </a>
                                          @endauth
                                       </div>
                                    </div>
                                 </div>
                                 @endforeach
                              </div>
                              @endif
                              <!-- End Replies -->
                           </div>
                        </div>
                     </div>
                     @endforeach
                     <script>
                        // Basic JS for like and reply functionality
                        document.addEventListener('DOMContentLoaded', function() {
                            // Like button functionality
                            document.addEventListener('click', function(e) {
                                if (e.target.closest('.comment-like-btn') || e.target.closest('.reply-like-btn')) {
                                    const btn = e.target.closest('.comment-like-btn') || e.target.closest('.reply-like-btn');
                                    const actionCount = btn.querySelector('.action-count');
                                    const icon = btn.querySelector('i');
                                    
                                    if (btn.classList.contains('active')) {
                                        btn.classList.remove('active', 'text-primary');
                                        icon.className = 'bi bi-hand-thumbs-up me-1';
                                        actionCount.textContent = parseInt(actionCount.textContent) - 1;
                                    } else {
                                        btn.classList.add('active', 'text-primary');
                                        icon.className = 'bi bi-hand-thumbs-up-fill me-1';
                                        actionCount.textContent = parseInt(actionCount.textContent) + 1;
                                    }
                                }
                            });
                            
                            // Reply button functionality
                            document.addEventListener('click', function(e) {
                                if (e.target.closest('.reply-btn')) {
                                    const btn = e.target.closest('.reply-btn');
                                    const commentId = btn.dataset.commentId;
                                    const replyInput = document.querySelector(`.reply-input[data-comment-id="${commentId}"]`);
                                    
                                    if (replyInput.style.display === 'none' || !replyInput.style.display) {
                                        replyInput.style.display = 'block';
                                        replyInput.querySelector('.reply-text').focus();
                                    } else {
                                        replyInput.style.display = 'none';
                                    }
                                }
                            });
                            
                            // Nested reply button functionality
                            document.addEventListener('click', function(e) {
                                if (e.target.closest('.nested-reply-btn')) {
                                    const btn = e.target.closest('.nested-reply-btn');
                                    const replyToName = btn.dataset.replyTo;
                                    const commentId = btn.dataset.commentId;
                                    
                                    const replyInput = document.querySelector(`.reply-input[data-comment-id="${commentId}"]`);
                                    const textInput = replyInput.querySelector('.reply-text');
                                    
                                    replyInput.style.display = 'block';
                                    textInput.value = `@${replyToName} `;
                                    textInput.focus();
                                }
                            });
                        });
                     </script>
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
                        .btn-link {
                        text-decoration: none;
                        }
                        .btn-link:hover {
                        text-decoration: underline;
                        }
                     </style>
                  </div>
                  {{-- Load More Comments --}}
                  <div class="text-center p-3 border-top load-more-section" id="load-more-comments-{{ $post->id }}" style="display: none;">
                     <button class="btn btn-link text-muted load-more-comments" data-post-id="{{ $post->id }}">
                     <i class="bi bi-arrow-down-circle me-1"></i>
                     Load more comments (<span class="remaining-comments">5</span>)
                     </button>
                  </div>
               </div>
               <script>
                  document.addEventListener('DOMContentLoaded', function() {
                  // Comment toggle functionality
                  document.addEventListener('click', function(e) {
                  if (e.target.closest('.comment-toggle-btn')) {
                  const btn = e.target.closest('.comment-toggle-btn');
                  const postId = btn.dataset.postId;
                  const commentsSection = document.getElementById(`comments-section-${postId}`);
                  
                  if (commentsSection.style.display === 'none' || !commentsSection.style.display) {
                  commentsSection.style.display = 'block';
                  btn.classList.add('text-primary');
                  } else {
                  commentsSection.style.display = 'none';
                  btn.classList.remove('text-primary');
                  }
                  }
                  });
                  
                  // Comment input focus functionality
                  document.addEventListener('focus', function(e) {
                  if (e.target.classList.contains('comment-input')) {
                  const tools = e.target.closest('.flex-grow-1').querySelector('.comment-tools');
                  if (tools) {
                  tools.style.display = 'flex';
                  tools.style.removeProperty('display');
                  tools.classList.remove('d-none');
                  }
                  }
                  }, true);
                  
                  // Comment input blur functionality
                  document.addEventListener('blur', function(e) {
                  if (e.target.classList.contains('comment-input')) {
                  setTimeout(() => {
                  if (!e.target.value.trim()) {
                      const tools = e.target.closest('.flex-grow-1').querySelector('.comment-tools');
                      if (tools) {
                          tools.style.display = 'none';
                      }
                  }
                  }, 200);
                  }
                  }, true);
                  
                  // Like button functionality
                  document.addEventListener('click', function(e) {
                  if (e.target.closest('.like-btn')) {
                  const btn = e.target.closest('.like-btn');
                  const actionCount = btn.querySelector('.action-count');
                  const icon = btn.querySelector('i');
                  
                  if (btn.classList.contains('liked')) {
                  btn.classList.remove('liked', 'text-primary');
                  icon.className = 'bi bi-hand-thumbs-up me-1';
                  actionCount.textContent = parseInt(actionCount.textContent) - 1;
                  } else {
                  btn.classList.add('liked', 'text-primary');
                  icon.className = 'bi bi-hand-thumbs-up-fill me-1';
                  actionCount.textContent = parseInt(actionCount.textContent) + 1;
                  }
                  }
                  });
                  
                  // Share button functionality
                  document.addEventListener('click', function(e) {
                  if (e.target.closest('.share-btn')) {
                  const btn = e.target.closest('.share-btn');
                  const actionCount = btn.querySelector('.action-count');
                  btn.classList.add('text-success');
                  actionCount.textContent = parseInt(actionCount.textContent) + 1;
                  
                  setTimeout(() => {
                  btn.classList.remove('text-success');
                  }, 1000);
                  }
                  });
                  });
               </script>
            </div>
         </div>
         @empty
         <div class="col">
            <p>No posts found.</p>
         </div>
         @endforelse
      </div>
      <!-- Loading Spinner -->
      <div id="loading" style="display: none; text-align: center; margin: 20px 0;">
         <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
         </div>
         <p class="mt-2">Loading more posts...</p>
      </div>
      <!-- Load More Button -->
      @if($posts->hasMorePages())
      <div id="load-more-container" class="text-center mt-3">
         <button id="load-more-btn" class="btn btn-primary">Load More Posts</button>
      </div>
      @endif
   </div>
</div>
{{-- Lazy Loading JavaScript --}}
<script>
   document.addEventListener('DOMContentLoaded', function() {
       let currentPage = 1;
       let isLoading = false;
       
       const postsContainer = document.getElementById('posts-container');
       const loadingSpinner = document.getElementById('loading');
       const loadMoreBtn = document.getElementById('load-more-btn');
       const loadMoreContainer = document.getElementById('load-more-container');
   
       // Load More Button Click
       if (loadMoreBtn) {
           loadMoreBtn.addEventListener('click', function() {
               loadMorePosts();
           });
       }
   
       // Auto Load on Scroll (Optional)
       window.addEventListener('scroll', function() {
           if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
               if (!isLoading && loadMoreBtn && loadMoreBtn.style.display !== 'none') {
                   loadMorePosts();
               }
           }
       });
   
       function loadMorePosts() {
           if (isLoading) return;
           
           isLoading = true;
           currentPage++;
           
           // Show loading spinner
           loadingSpinner.style.display = 'block';
           if (loadMoreBtn) loadMoreBtn.style.display = 'none';
           
           $.ajax({
               url: '{{ route("posts.loadmore") }}',
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


<!-- Add this modal before closing body tag -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="shareModalLabel">Share Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="d-grid gap-2">
          <button class="btn btn-outline-primary share-option" data-share="facebook">
            <i class="bi bi-facebook me-2"></i>Facebook
          </button>
          <button class="btn btn-outline-info share-option" data-share="twitter">
            <i class="bi bi-twitter me-2"></i>Twitter
          </button>
          <button class="btn btn-outline-success share-option" data-share="whatsapp">
            <i class="bi bi-whatsapp me-2"></i>WhatsApp
          </button>
          <button class="btn btn-outline-secondary share-option" data-share="copy">
            <i class="bi bi-clipboard me-2"></i>Copy Link
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPostId = null;
    let shareModalInstance = null;

    // Initialize modal
    const shareModalEl = document.getElementById('shareModal');
    if (shareModalEl) {
        shareModalInstance = new bootstrap.Modal(shareModalEl);
    }

    // Share button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.share-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.share-btn');
            currentPostId = btn.dataset.postId;
            
            const postUrl = window.location.origin + '/post/' + currentPostId;
            const postTitle = 'Check out this post!';

            // Check if Web Share API is supported
            if (navigator.share) {
                navigator.share({
                    title: postTitle,
                    url: postUrl
                })
                .then(() => {
                    // Share successful - update count
                    updateShareCount(currentPostId, btn);
                })
                .catch((error) => {
                    // User cancelled or error - show custom modal
                    if (error.name !== 'AbortError') {
                        showCustomShareModal();
                    }
                });
            } else {
                // Web Share API not supported - show custom modal
                showCustomShareModal();
            }
        }
    });

    // Custom share options click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.share-option')) {
            const btn = e.target.closest('.share-option');
            const shareType = btn.dataset.share;
            const postUrl = window.location.origin + '/post/' + currentPostId;
            const postTitle = 'Check out this post!';

            switch(shareType) {
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`, '_blank', 'width=600,height=400');
                    break;
                case 'twitter':
                    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(postUrl)}&text=${encodeURIComponent(postTitle)}`, '_blank', 'width=600,height=400');
                    break;
                case 'whatsapp':
                    window.open(`https://wa.me/?text=${encodeURIComponent(postTitle + ' ' + postUrl)}`, '_blank');
                    break;
                case 'copy':
                    navigator.clipboard.writeText(postUrl).then(() => {
                        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Link Copied!';
                        setTimeout(() => {
                            btn.innerHTML = '<i class="bi bi-clipboard me-2"></i>Copy Link';
                        }, 2000);
                    });
                    break;
            }

            // Update share count
            const shareBtn = document.querySelector(`.share-btn[data-post-id="${currentPostId}"]`);
            updateShareCount(currentPostId, shareBtn);
            
            // Close modal
            if (shareModalInstance) {
                shareModalInstance.hide();
            }
        }
    });

    function showCustomShareModal() {
        if (shareModalInstance) {
            shareModalInstance.show();
        }
    }

    function updateShareCount(postId, btn) {
        $.ajax({
            url: '/posts/' + postId + '/share',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    const actionCount = btn.querySelector('.action-count');
                    actionCount.textContent = response.shares_count;
                    
                    // Visual feedback
                    btn.classList.add('text-success');
                    setTimeout(() => {
                        btn.classList.remove('text-success');
                    }, 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating share count:', error);
            }
        });
    }
});
</script>

<style>
.share-btn {
    transition: all 0.3s ease;
}

.share-btn.text-success {
    transform: scale(1.1);
}

.share-option {
    text-align: left;
    padding: 12px 20px;
    font-weight: 500;
}

.share-option:hover {
    transform: translateX(5px);
    transition: transform 0.2s ease;
}

#shareModal .modal-content {
    border-radius: 15px;
}

#shareModal .modal-header {
    padding: 20px 20px 10px 20px;
}

#shareModal .modal-body {
    padding: 10px 20px 20px 20px;
}
</style>