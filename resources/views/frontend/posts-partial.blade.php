@if(isset($posts) && $posts->count() > 0)
@foreach($posts as $post)
{{-- শুধুমাত্র cat_type = 'post' যেগুলোর --}}
@if($post->category && $post->category->cat_type == 'post')
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
                  <small class="text-muted"><i class="bi bi-grid"></i> {{ $post->category->category_name }}</small>
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
         @if($post->image)
         <img id="img-zoomer" src="{{ asset('uploads/'.$post->image) }}" alt="Post Image" class="img-fluid" style="object-fit:cover; max-height:400px; width:100%;">
         @endif
         {{-- Card Footer: Social Actions --}}
         <div class="bg-white rounded-bottom border-0 pt-0">
            {{-- Action Buttons --}}
            <div class="d-flex justify-content-around text-muted border-top pt-2 pb-2">
               <button class="btn text-muted d-flex align-items-center like-btn {{ $post->isLikedBy(Auth::id()) ? 'liked text-primary' : '' }}" 
                  data-post-id="{{ $post->id }}">
               <i class="bi {{ $post->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i> 
               <span class="action-count">{{ $post->likesCount() }}</span>
               </button>
               <button class="btn text-muted d-flex align-items-center comment-toggle-btn" data-post-id="{{ $post->id }}">
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
                           <button class="btn btn-sm text-muted p-0 me-2 comment-like-btn {{ $comment->isLikedBy(Auth::id()) ? 'active text-primary' : '' }}" 
                              data-comment-id="{{ $comment->id }}">
                           <i class="bi {{ $comment->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i>
                           <span class="action-count">{{ $comment->likesCount() }}</span>
                           </button>
                           <button class="btn btn-sm text-muted p-0 me-2 reply-btn" data-comment-id="{{ $comment->id }}">
                           <i class="bi bi-reply me-1"></i>
                           Reply
                           </button>
                           @else
                           <span class="text-muted me-2" style="font-size: 12px;">
                           <i class="bi bi-hand-thumbs-up me-1"></i>
                           <span class="action-count">{{ $comment->likes_count ?? 0 }}</span>
                           </span>
                           <a href="{{ route('login') }}" class="btn btn-sm text-muted p-0 me-2" style="font-size: 12px;">
                           <i class="bi bi-reply me-1"></i>
                           Login to Reply
                           </a>
                           @endauth
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
                                    <button class="btn btn-sm text-muted p-0 me-2 reply-like-btn {{ $reply->isLikedBy(Auth::id()) ? 'active text-primary' : '' }}" 
                                       data-reply-id="{{ $reply->id }}" 
                                       style="font-size: 11px;">
                                    <i class="bi {{ $reply->isLikedBy(Auth::id()) ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }} me-1"></i>
                                    <span class="action-count">{{ $reply->likesCount() }}</span>
                                    </button>
                                    <button class="btn btn-sm text-muted p-0 nested-reply-btn" 
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
                                    <a href="{{ route('login') }}" class="btn btn-sm text-muted p-0" style="font-size: 11px;">
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
@endif
@endforeach
@else
<div class="alert alert-info text-center my-4">
   <i class="bi bi-info-circle me-2"></i>
   <strong>No posts available</strong> at the moment.
</div>
@endif




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
       // ===== LAZY LOADING =====
       let isLoading = false;
       let hasMorePages = document.getElementById('has-more-pages');
       let currentPageInput = document.getElementById('current-page');
       
       if (hasMorePages && hasMorePages.value === '1') {
           window.addEventListener('scroll', function() {
               if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                   if (!isLoading && hasMorePages.value === '1') {
                       loadMorePosts();
                   }
               }
           });
       }
       
       function loadMorePosts() {
           isLoading = true;
           const currentPage = parseInt(currentPageInput.value);
           const nextPage = currentPage + 1;
           const loadingSpinner = document.getElementById('loading-spinner');
           
           if (loadingSpinner) {
               loadingSpinner.style.display = 'block';
           }
           
           fetch(`{{ url()->current() }}?page=${nextPage}`, {
               method: 'GET',
               headers: {
                   'X-Requested-With': 'XMLHttpRequest',
                   'Accept': 'application/json'
               }
           })
           .then(response => response.json())
           .then(data => {
               if (loadingSpinner) {
                   loadingSpinner.style.display = 'none';
               }
               
               if (data.posts) {
                   const container = document.getElementById('posts-container');
                   const tempDiv = document.createElement('div');
                   tempDiv.innerHTML = data.posts;
                   
                   while (tempDiv.firstChild) {
                       container.appendChild(tempDiv.firstChild);
                   }
                   
                   currentPageInput.value = nextPage;
                   
                   if (!data.hasMore) {
                       hasMorePages.value = '0';
                       if (loadingSpinner) {
                           loadingSpinner.remove();
                       }
                   }
                   
                   isLoading = false;
               }
           })
           .catch(error => {
               console.error('Error:', error);
               if (loadingSpinner) {
                   loadingSpinner.style.display = 'none';
               }
               isLoading = false;
           });
       }
   
       // ===== COMMENT TOGGLE =====
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
       
    
       
       // ===== LIKE BUTTON (POST) =====
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
       
       // ===== LIKE BUTTON (COMMENT/REPLY) =====
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
       
       // ===== SHARE BUTTON =====
     
       
       // ===== REPLY BUTTON =====
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
       
       // ===== NESTED REPLY BUTTON =====
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
</script>



<!-- Custom Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg" style="font-size: 1.25rem; cursor: pointer;" data-bs-dismiss="modal" aria-label="Close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="share-options">
                    <button class="share-option-btn" data-platform="facebook">
                        <i class="bi bi-facebook"></i> Facebook
                    </button>
                    <button class="share-option-btn" data-platform="twitter">
                        <i class="bi bi-twitter"></i> Twitter
                    </button>
                    <button class="share-option-btn" data-platform="whatsapp">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </button>
                    <button class="share-option-btn" data-platform="linkedin">
                        <i class="bi bi-linkedin"></i> LinkedIn
                    </button>
                </div>
                <div class="mt-3">
                    <label class="form-label">Or copy link:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareUrl" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

// JavaScript Code
document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll('.share-btn');
    const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
    const shareUrlInput = document.getElementById('shareUrl');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    
    let currentShareData = {};

    shareButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            const postUrl = this.dataset.postUrl || window.location.href;
            const postTitle = this.dataset.postTitle || document.title;
            
            currentShareData = {
                title: postTitle,
                text: `Check out this post: ${postTitle}`,
                url: postUrl
            };

            // Try to use native Web Share API
            if (navigator.share) {
                try {
                    await navigator.share(currentShareData);
                    console.log('Shared successfully');
                } catch (err) {
                    // User cancelled or error occurred
                    if (err.name !== 'AbortError') {
                        // Show custom modal if share fails
                        showCustomShareModal(postUrl);
                    }
                }
            } else {
                // Native share not supported, show custom modal
                showCustomShareModal(postUrl);
            }
        });
    });

    function showCustomShareModal(url) {
        shareUrlInput.value = url;
        shareModal.show();
    }

    // Handle social media sharing
    document.querySelectorAll('.share-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = encodeURIComponent(currentShareData.url);
            const text = encodeURIComponent(currentShareData.text);
            let shareUrl = '';

            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${text}%20${url}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                    break;
            }

            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });

    // Copy link functionality
    copyLinkBtn.addEventListener('click', function() {
        shareUrlInput.select();
        document.execCommand('copy');
        
        // Show feedback
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check"></i> Copied!';
        this.classList.add('btn-success');
        this.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-secondary');
        }, 2000);
    });
});

</script>
<style>
.share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.share-option-btn {
    padding: 12px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
}

.share-option-btn:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    transform: translateY(-2px);
}

.share-option-btn i {
    font-size: 20px;
}

.share-option-btn[data-platform="facebook"]:hover {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.share-option-btn[data-platform="twitter"]:hover {
    background: #1da1f2;
    color: white;
    border-color: #1da1f2;
}

.share-option-btn[data-platform="whatsapp"]:hover {
    background: #25d366;
    color: white;
    border-color: #25d366;
}

.share-option-btn[data-platform="linkedin"]:hover {
    background: #0077b5;
    color: white;
    border-color: #0077b5;
}

#shareUrl {
    font-size: 14px;
}
</style>
