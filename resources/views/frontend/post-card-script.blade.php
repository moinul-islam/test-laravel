

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

<script>
// Global video initialization function
function initializeVideos(container = document) {
    // Set global mute preference
    let globalMutePref = localStorage.getItem('globalVideoMuted');
    if (globalMutePref === null) globalMutePref = "true";
    
    function setAllVideosMuted(muted) {
        container.querySelectorAll('video.post-carousel-video').forEach(video => {
            video.muted = muted;
            if (!video.paused && !muted && video.readyState >= 2) {
                video.play().catch(()=>{});
            }
        });
    }
    
    setAllVideosMuted(globalMutePref === "true");
    
    // Volume change listener (only set once globally)
    if (!window._global_video_mute_listener) {
        window._global_video_mute_listener = true;
        document.addEventListener('volumechange', function(event) {
            let target = event.target;
            if (target && target.classList && target.classList.contains('post-carousel-video')) {
                localStorage.setItem('globalVideoMuted', target.muted ? "true" : "false");
                setAllVideosMuted(target.muted);
            }
        }, true);
    }
    
    // Initialize each carousel
    container.querySelectorAll('[id^="mixedMediaCarousel-"]').forEach(carousel => {
        const carouselId = carousel.id;
        const postId = carouselId.replace('mixedMediaCarousel-', '');
        
        // Initial video autoplay
        const activeVideo = carousel.querySelector('.carousel-item.active video.post-carousel-video');
        if (activeVideo) {
            activeVideo.play().catch(()=>{});
            
            // Intersection observer for auto-pause
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        activeVideo.play().catch(()=>{});
                    } else {
                        activeVideo.pause();
                    }
                });
            }, { threshold: 0.5 });
            observer.observe(activeVideo);
            
            // Store observer reference
            activeVideo._intersectionObserver = observer;
        }
        
        // Carousel slide change handler (remove existing listeners first)
        const existingListener = carousel._slideListener;
        if (existingListener) {
            carousel.removeEventListener('slid.bs.carousel', existingListener);
        }
        
        const slideListener = function(event) {
            const videos = carousel.querySelectorAll('video.post-carousel-video');
            videos.forEach((v) => {
                v.pause();
                if (v._intersectionObserver) {
                    v._intersectionObserver.disconnect();
                }
            });
            
            const newActive = carousel.querySelector('.carousel-item.active video.post-carousel-video');
            if (newActive) {
                newActive.play().catch(()=>{});
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            newActive.play().catch(()=>{});
                        } else {
                            newActive.pause();
                        }
                    });
                }, { threshold: 0.5 });
                observer.observe(newActive);
                newActive._intersectionObserver = observer;
            }
        };
        
        carousel.addEventListener('slid.bs.carousel', slideListener);
        carousel._slideListener = slideListener; // Store reference
    });
}

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    initializeVideos();
});

// Lazy loading এর পরে call করার জন্য window এ expose করুন
window.reinitializeVideos = function(newContainer) {
    initializeVideos(newContainer || document);
};
</script>

@include('frontend.body.share-button')