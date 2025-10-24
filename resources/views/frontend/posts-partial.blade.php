
<div class="mb-4 post-item" data-post-id="{{ $post->id }}">
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
                <h2>
                    {{ $post->title }}
                </h2>
                {{-- Post Description with Read More --}}
                @if($post->description)
                    <p class="card-text post-description" style="max-height:75px; overflow:hidden;">
                        {{ $post->description }}
                    </p>
                    @if(strlen($post->description) > 150)
                        <a href="javascript:void(0);" class="read-more text-primary">Read more</a>
                    @endif
                @endif
            </div>
            {{-- Card Image --}}
            @if($post->image)
                <img id="img-zoomer" src="{{ asset('uploads/'.$post->image) }}" alt="Post Image" class="img-fluid" style="object-fit:cover; max-height:400px; width:100%;">
            @endif
            
        </div>
    </div>
