@if(isset($posts) && $posts->count() > 0)
@foreach($posts as $post)
{{-- শুধুমাত্র cat_type = 'post' যেগুলোর --}}
@if($post->category && $post->category->cat_type == 'post')
    @include('frontend.post-card-only')
@endif
@endforeach
@else
<div class="alert alert-info text-center my-4">
   <i class="bi bi-info-circle me-2"></i>
   <strong>No posts available</strong> at the moment.
</div>
@endif
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