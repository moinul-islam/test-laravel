@extends("frontend.master")
@section('main-content')

{{-- Posts Container --}}
<div class="container mt-4">
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

{{-- Include Sidebar --}}
@include('frontend.body.sidebar')

{{-- Sidebar Open Button --}}
<button class="btn btn-primary" id="openSidebarBtn">
    <i class="fas fa-bars"></i> Categories
</button>

@endsection


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
