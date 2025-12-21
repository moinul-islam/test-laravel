@extends('frontend.master')
@section('main-content')

<div class="container-fluid py-4">
    @if(auth()->check() && auth()->user()->role === 'admin')
        @include('frontend.body.admin-nav')
    @endif

    <div class="row mb-3">
        <div class="col-12">
            {{-- Filter section - only for date and status --}}
            <form method="GET" action="{{ route('admin.posts.approval') }}" class="d-flex flex-wrap align-items-end gap-2 mb-3">
                <div>
                    <label class="form-label mb-1" for="date">Date</label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        class="form-control" 
                        style="min-width:150px;"
                        value="{{ request('date', \Carbon\Carbon::now()->toDateString()) }}"
                        required
                    >
                </div>
                <div>
                    <label class="form-label mb-1" for="status">Status</label>
                    <select name="status" id="status" class="form-select" style="min-width:110px;">
                        <option value="">All</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.posts.approval', ['date' => \Carbon\Carbon::now()->toDateString()]) }}" class="btn btn-secondary">Today</a>
                </div>
            </form>
            <div class="alert alert-info py-2 px-3 small mb-0">
                <i class="bi bi-info-circle text-primary"></i>
                এখানে শুধুমাত্র আজকের পোস্টগুলো দেখা যাবে। পুরাতন পোস্ট দেখতে চাইলে, উপরের তারিখ সিলেক্ট করুন।
            </div>
        </div>
    </div>

    <div class="row g-3">
        @forelse($posts as $post)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card shadow-sm h-100 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between card-body pb-1">
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
                                    <small class="text-muted">
                                        <i class="{{ $post->category->image }}"></i>
                                        {{ $post->category->category_name }}
                                    </small>
                                @endif
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('post.details', $post->slug) }}" target="_blank">
                                        <i class="bi bi-eye me-2"></i>View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); openEditModal({{ $post->id }})">
                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @php
                        $maxLength = 200;
                        $desc = $post->description;
                        $descLength = mb_strlen($desc);
                        $hasDesc = $descLength > 0;
                        $bodyStyle = 'padding-top:0;';
                        if(!$hasDesc) $bodyStyle .= 'padding-bottom:0;';
                    @endphp
                    <div class="card-body pt-2 pb-2" style="{{ $bodyStyle }}">
                        <!-- Description -->
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

                    @php
                        // Mixed media
                        $media = null; $isSingleImage = false;
                        $allMedia = [];
                        if ($post->image) {
                            if (is_string($post->image)) {
                                if (str_starts_with($post->image, '{') || str_starts_with($post->image, '[')) {
                                    $media = json_decode($post->image, true);
                                } else {
                                    $isSingleImage = true;
                                }
                            } elseif (is_array($post->image)) {
                                $media = $post->image;
                            }
                        }
                        if ($media) {
                            if (isset($media['images']) && is_array($media['images'])) {
                                foreach ($media['images'] as $img) { $allMedia[] = ['type' => 'image', 'file' => $img]; }
                            }
                            if (isset($media['videos']) && is_array($media['videos'])) {
                                foreach ($media['videos'] as $vid) { $allMedia[] = ['type' => 'video', 'file' => $vid]; }
                            }
                        }
                    @endphp
                    @if($isSingleImage)
                        <img src="{{ asset('uploads/'.$post->image) }}"
                             alt="Post Image"
                             class="img-fluid"
                             style="object-fit:cover; max-height:260px; width:100%;">
                    @elseif($media && count($allMedia) > 0)
                        <div class="media-container">
                            <div id="mixedMediaCarousel-{{ $post->id }}" class="carousel slide" data-bs-ride="false">
                                <div class="carousel-inner">
                                    @foreach($allMedia as $index => $item)
                                        <div class="carousel-item @if($index === 0) active @endif">
                                            @if($item['type'] === 'image')
                                                <img src="{{ asset('uploads/' . $item['file']) }}"
                                                     alt="Post Image {{ $index + 1 }}"
                                                     class="img-fluid d-block w-100"
                                                     style="width: 100%; {{ count($allMedia) === 1 ? 'max-height:260px;' : 'height:260px;' }} object-fit: cover; cursor: pointer;"
                                                     onclick="openImageModal('{{ asset('uploads/' . $item['file']) }}')">
                                            @elseif($item['type'] === 'video')
                                                <div style="background:#000;max-height:260px;overflow:hidden;display:flex;align-items:center;">
                                                    <video
                                                        src="{{ asset('uploads/' . $item['file']) }}"
                                                        class="w-100 post-carousel-video"
                                                        controls
                                                        controlsList="nodownload"
                                                        style="max-height:260px;object-fit:contain;width:100%;background:#000;border-radius:0;margin-bottom:0;"
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
                        </div>
                    @endif

                    <div class="card-body pt-2 pb-2">
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @if($post->status == 0)
                                <button type="button" class="btn btn-sm btn-success" onclick="updateStatus({{ $post->id }}, 1)">
                                    <i class="bi bi-check2"></i> Approve
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus({{ $post->id }}, 0)">
                                    <i class="bi bi-arrow-counterclockwise"></i> Pending
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-primary" onclick="openEditModal({{ $post->id }})">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <a href="{{ route('post.details', $post->slug) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>
                    </div>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $post->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.posts.update', $post->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Post: {{ Str::limit($post->title, 30) }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name ?? $category->category_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="0" {{ $post->status == 0 ? 'selected' : '' }}>Pending</option>
                                                <option value="1" {{ $post->status == 1 ? 'selected' : '' }}>Approved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    {{-- End Edit Modal --}}
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center py-4 mb-0">এদিনের কোন পোস্ট পাওয়া যায়নি। (No posts found for this day.)</div>
            </div>
        @endforelse
    </div>
    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links('pagination::bootstrap-4') }}
    </div>
</div>

<style>
.card { border-radius: 8px; }
.card-body { font-size: 1rem; }
.card img, .carousel img { border-radius: 4px; }
.badge.bg-warning { color: #644f00 !important; }
.btn-group .btn, .btn { border-radius: 0.25rem; }
.media-container { min-height: 146px; }
</style>

<script>
function updateStatus(postId, status) {
    if (confirm('Are you sure you want to ' + (status == 1 ? 'approve' : 'set as pending') + ' this post?')) {
        fetch(`/admin/posts/${postId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status');
            }
        })
        .catch(error => {
            alert('Error updating status');
        });
    }
}
function openEditModal(id) {
    let modal = document.getElementById('editModal'+id);
    if (modal) {
        let bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}
function openImageModal(src) {
    // TODO: Optional - implement global image modal if needed.
}
</script>

@endsection