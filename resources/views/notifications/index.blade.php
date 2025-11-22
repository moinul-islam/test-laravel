@extends("frontend.master")
@section('main-content')

<style>
/* Notification Page Styles */
.notification-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.notification-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.notification-header h1 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
    margin: 0;
}

.mark-all-btn {
    background: none;
    border: none;
    color: #2563eb;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.5rem 1rem;
}

.mark-all-btn:hover {
    color: #1d4ed8;
}

.notification-list {
    border-top: 1px solid #e5e7eb;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unseen {
    background-color: #eff6ff;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.sender-images {
    display: flex;
    margin-left: -0.5rem;
}

.sender-images img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
    margin-left: -0.5rem;
    object-fit: cover;
}

.notification-text {
    flex: 1;
}

.notification-text a {
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    line-height: 1.5;
}

.notification-text a:hover {
    text-decoration: underline;
}

.notification-text strong {
    color: #111827;
    font-weight: 600;
}

.notification-text .post-title {
    color: #6b7280;
}

.notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

.unseen-dot {
    width: 8px;
    height: 8px;
    background-color: #2563eb;
    border-radius: 50%;
    margin-top: 0.5rem;
    flex-shrink: 0;
}

.empty-state {
    padding: 3rem;
    text-align: center;
    color: #6b7280;
}

.empty-state svg {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    color: #d1d5db;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 500;
    margin: 0 0 0.25rem;
}

.empty-state p {
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
}

@media (max-width: 768px) {
    .notification-container {
        padding: 0;
        margin: 1rem auto;
    }
    
    .notification-card {
        border-radius: 0;
    }
    
    .notification-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .notification-item {
        padding: 0.75rem;
    }
    
    .sender-images img {
        width: 36px;
        height: 36px;
    }
}
</style>

<div class="container mt-4">
    <div class="notification-card">
        <!-- Header -->
        <div class="notification-header">
            <h1>Notifications</h1>
            @if($unseenCount > 0)
                <button onclick="markAllAsSeen()" class="mark-all-btn">
                    Mark all as read ({{ $unseenCount }})
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="notification-list">
            @forelse($groupedNotifications as $group)
                <div class="notification-item {{ !$group['all_seen'] ? 'unseen' : '' }}" 
                     data-notification-ids="{{ json_encode($group['notification_ids']) }}">
                    
                    <div class="notification-content">
                        <!-- Sender Images -->
                        <div class="sender-images">
                            @foreach($group['senders']->take(3) as $sender)
                                <img src="{{ $sender->image ? asset('profile-image/' . $sender->image) : 'https://cdn-icons-png.flaticon.com/512/219/219983.png' }}" 
                                     alt="{{ $sender->name }}">
                            @endforeach
                        </div>

                        <!-- Notification Text -->
                        <div class="notification-text">
                            @php
                                $sendersCount = $group['count'];
                                $firstSender = $group['senders'][0];
                                $secondSender = $group['senders'][1] ?? null;
                            @endphp

                            @if($group['type'] === 'post_like')
                                <a href="{{ url('/post/' . $group['post']->slug) }}" 
                                   onclick="markNotificationSeen(event, {{ json_encode($group['notification_ids']) }})">
                                    <strong>{{ $firstSender->name }}</strong>
                                    @if($sendersCount > 1)
                                        @if($sendersCount == 2)
                                            and <strong>{{ $secondSender->name }}</strong>
                                        @else
                                            and <strong>{{ $sendersCount - 1 }} others</strong>
                                        @endif
                                    @endif
                                    liked your post
                                    @if($group['post'])
                                        <span class="post-title">"{{ Str::limit($group['post']->title, 30) }}"</span>
                                    @endif
                                </a>

                            @elseif($group['type'] === 'comment')
                                <a href="{{ url('/post/' . $group['post']->slug . '#comment-' . $group['comment_id']) }}" 
                                   onclick="markNotificationSeen(event, {{ json_encode($group['notification_ids']) }})">
                                    <strong>{{ $firstSender->name }}</strong>
                                    @if($sendersCount > 1)
                                        @if($sendersCount == 2)
                                            and <strong>{{ $secondSender->name }}</strong>
                                        @else
                                            and <strong>{{ $sendersCount - 1 }} others</strong>
                                        @endif
                                    @endif
                                    commented on your post
                                    @if($group['post'])
                                        <span class="post-title">"{{ Str::limit($group['post']->title, 30) }}"</span>
                                    @endif
                                </a>

                            @elseif($group['type'] === 'comment_reply')
                                <a href="{{ url('/post/' . $group['post']->slug . '#comment-' . $group['comment_id']) }}" 
                                   onclick="markNotificationSeen(event, {{ json_encode($group['notification_ids']) }})">
                                    <strong>{{ $firstSender->name }}</strong>
                                    @if($sendersCount > 1)
                                        @if($sendersCount == 2)
                                            and <strong>{{ $secondSender->name }}</strong>
                                        @else
                                            and <strong>{{ $sendersCount - 1 }} others</strong>
                                        @endif
                                    @endif
                                    replied to your comment
                                </a>

                            @elseif($group['type'] === 'comment_like')
                                <a href="{{ url('/post/' . $group['post']->slug . '#comment-' . $group['comment_id']) }}" 
                                   onclick="markNotificationSeen(event, {{ json_encode($group['notification_ids']) }})">
                                    <strong>{{ $firstSender->name }}</strong>
                                    @if($sendersCount > 1)
                                        @if($sendersCount == 2)
                                            and <strong>{{ $secondSender->name }}</strong>
                                        @else
                                            and <strong>{{ $sendersCount - 1 }} others</strong>
                                        @endif
                                    @endif
                                    liked your comment
                                </a>

                            @elseif($group['type'] === 'post_reply')
                                <a href="{{ url('/post/' . $group['post']->slug . '#comment-' . $group['comment_id']) }}" 
                                   onclick="markNotificationSeen(event, {{ json_encode($group['notification_ids']) }})">
                                    <strong>{{ $firstSender->name }}</strong>
                                    @if($sendersCount > 1)
                                        @if($sendersCount == 2)
                                            and <strong>{{ $secondSender->name }}</strong>
                                        @else
                                            and <strong>{{ $sendersCount - 1 }} others</strong>
                                        @endif
                                    @endif
                                    replied on your post
                                </a>
                            @endif

                            <div class="notification-time">
                                {{ $group['latest_time']->diffForHumans() }}
                            </div>
                        </div>

                        <!-- Unseen Indicator -->
                        @if(!$group['all_seen'])
                            <div class="unseen-dot"></div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h3>No notifications yet</h3>
                    <p>When someone likes or comments, you'll see it here</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function markNotificationSeen(event, notificationIds) {
    fetch('{{ route("notifications.markAsSeen") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            notification_ids: notificationIds
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Notification marked as seen:', data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function markAllAsSeen() {
    if (!confirm('Mark all notifications as read?')) return;
    
    fetch('{{ route("notifications.markAllSeen") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

setInterval(() => {
    fetch('{{ route("notifications.unseenCount") }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
            } else if (badge) {
                badge.classList.add('hidden');
            }
        })
        .catch(error => console.error('Error:', error));
}, 30000);
</script>
@endsection