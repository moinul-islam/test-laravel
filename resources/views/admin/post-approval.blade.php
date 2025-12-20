@extends('frontend.master')
@section('main-content')

<div class="container-fluid py-4">
@include('frontend.body.admin-nav')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Post Approval Management</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <form method="GET" action="{{ route('admin.posts.approval') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-50">Filter</button>
                                    <a href="{{ route('admin.posts.approval') }}" class="btn btn-secondary w-50">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Posts Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>
                                        @if($post->image)
                                            <img src="{{ asset('uploads/' . $post->image) }}" alt="{{ $post->title }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($post->title, 40) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $post->slug }}</small>
                                    </td>
                                    <td>
                                        @if($post->category)
                                            <span class="badge bg-info">{{ $post->category->name ?? $post->category->category_name }}</span>
                                        @elseif($post->new_category)
                                            <span class="badge bg-warning">{{ $post->new_category }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($post->discount_price && $post->discount_until && $post->discount_until > now())
                                            <del class="text-muted">৳{{ number_format($post->price) }}</del>
                                            <br>
                                            <strong class="text-success">৳{{ number_format($post->discount_price) }}</strong>
                                        @else
                                            <strong>৳{{ number_format($post->price) }}</strong>
                                        @endif
                                    </td>
                                    <td>{{ $post->created_at->format('d M Y') }}<br><small class="text-muted">{{ $post->created_at->format('h:i A') }}</small></td>
                                    <td>
                                        @if($post->status == 1)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($post->status == 0)
                                            <button type="button" class="btn btn-sm btn-success" onclick="updateStatus({{ $post->id }}, 1)">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-warning" onclick="updateStatus({{ $post->id }}, 0)">
                                                <i class="fas fa-times"></i> Pending
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $post->id }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="{{ route('post.details', $post->slug) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
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
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted mb-0">No posts found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}
.btn-group .btn {
    border-radius: 0;
}
.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}
.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
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
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating status');
        });
    }
}
</script>

@endsection