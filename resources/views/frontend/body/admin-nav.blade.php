<div class="bd-4">
        @php
            // Determine active page for buttons
            $currentRouteName = Route::currentRouteName();
            $currentPath = request()->path();
        @endphp
        <div class="btn-group me-2 mb-2 flex-nowrap overflow-auto" role="group" aria-label="Admin navigation group" style="white-space:nowrap; scrollbar-width:auto; -webkit-overflow-scrolling:touch;">
            <a href="/admin/posts/approval"
                class="btn flex-shrink-0 btn-outline-danger{{ ($currentRouteName === 'admin.posts.approval') ? ' active btn-danger text-white' : '' }}">
                Post Approval 
                @if($currentRouteName === 'admin.posts.approval')
                @endif
            </a>
            <a href="/admin/create-post"
                class="btn flex-shrink-0 btn-outline-info{{ (\Illuminate\Support\Str::startsWith($currentPath, 'admin/create-post')) ? ' active btn-info text-white' : '' }}">
                Add Post 
                @if(\Illuminate\Support\Str::startsWith($currentPath, 'admin/create-post'))
                    <span class="ms-1 text-info">&#10003;</span>
                @endif
            </a>
            <a href="/admin"
                class="btn flex-shrink-0 btn-outline-primary{{ ($currentRouteName === 'admin.page') ? ' active btn-success text-white' : '' }}">
                Users 
                @if($currentRouteName === 'admin.page')
                @endif
            </a>
            <a class="btn flex-shrink-0 btn-outline-danger{{ $currentRouteName === 'contribute' ? ' active btn-danger text-white' : '' }}"
                href="{{ route('contribute') }}">
                Contribute
                @if($currentRouteName === 'contribute')
                    <span class="ms-1 text-danger">&#10003;</span>
                @endif
            </a>
            <a href="/categories"
                class="btn flex-shrink-0 btn-outline-success{{ ($currentPath === 'categories') ? ' active btn-success text-white' : '' }}">
                Categories 
                @if($currentPath === 'categories')
                    <span class="ms-1 text-success">&#10003;</span>
                @endif
            </a>
            <a class="btn flex-shrink-0 btn-outline-warning{{ $currentRouteName === 'delivery.page' ? ' active btn-warning text-white' : '' }}"
                href="{{ route('delivery.page') }}">
                Delivery
                @if($currentRouteName === 'delivery.page')
                    <span class="ms-1 text-warning">&#10003;</span>
                @endif
            </a>
            <a href="{{ route('admin.notifications.form') }}"
               class="btn flex-shrink-0 btn-outline-secondary{{ ($currentPath === 'send-notification') ? ' active btn-secondary text-white' : '' }}">
                Send Notification
                @if($currentPath === 'send-notification')
                    <span class="ms-1 text-secondary">&#10003;</span>
                @endif
            </a>       
        </div>
    </div>
    <style>
        .btn-group.flex-nowrap {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .btn-group .btn {
            min-width: 120px;
            position: relative;
        }
        .btn-group::-webkit-scrollbar {
            height: 6px;
        }
        .btn-group .btn.active {
            outline: none;
            box-shadow: 0 2px 8px rgba(44,62,80,.08);
        }
        .btn-group .btn span {
            font-size: 1.2em;
            vertical-align: middle;
        }
    </style>