@if(isset($posts) && $posts->count() > 0)
    @foreach($posts as $post)
        <div class="mb-4 post-item" data-post-id="{{ $post->id }}">
            <div class="card">
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
                                    <small class="text-muted"><i class="bi bi-grid"></i> {{ $post->category->category_name }}</small>
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
                    <h2>{{ $post->title }}</h2>
                    @if($post->image)
                        <img id="img-zoomer" src="{{ asset('uploads/'.$post->image) }}" alt="Post Image" class="img-fluid" style="object-fit:cover; max-height:400px; width:100%;">
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-info text-center my-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>No posts available</strong> at the moment.
    </div>
@endif



<script>
// ✅ Scroll-based Lazy Loading
document.addEventListener('DOMContentLoaded', function() {
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
            } else {
                hasMorePages.value = '0';
                if (loadingSpinner) {
                    loadingSpinner.remove();
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
});
</script>