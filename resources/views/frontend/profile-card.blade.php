{{-- File Location: resources/views/frontend/partials/profile-card.blade.php --}}

<div class="">
   <div class="col-12">
      <div class="card">
         <div class="card-body text-center">
            


         


         @php
use Carbon\Carbon;

$serviceHours = json_decode($user->service_hr, true) ?? [];
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

$todayName = strtolower(now()->setTimezone('Asia/Dhaka')->format('l'));
$todayData = $serviceHours[$todayName] ?? null;

function readableTime($time) {
    if(!$time) return '—';
    return Carbon::createFromFormat('H:i', $time)->format('g:i A');
}

// Initial Top-right badge
$isOpen = false;
$now = now()->setTimezone('Asia/Dhaka');
if(is_array($todayData) && isset($todayData['open'], $todayData['close'])) {
    $openTime = Carbon::createFromFormat('H:i', $todayData['open'], 'Asia/Dhaka');
    $closeTime = Carbon::createFromFormat('H:i', $todayData['close'], 'Asia/Dhaka');
    if($now->between($openTime, $closeTime)) {
        $isOpen = true;
    }
}
@endphp

{{-- Top-right Open/Closed badge --}}
@if($user->category_id)
<span id="openStatusBadge" class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} position-absolute top-0 end-0 m-2"
      data-bs-toggle="modal"
      data-bs-target="#hoursModal-{{ $user->id }}"
      style="cursor:pointer;">
    {{ $isOpen ? 'Open now' : 'Closed now' }}
</span>
@endif

{{-- Modal --}}
<div class="modal fade" id="hoursModal-{{ $user->id }}" tabindex="-1" aria-labelledby="hoursModalLabel-{{ $user->id }}" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="hoursModalLabel-{{ $user->id }}">Opening Hours</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="list-unstyled">
            @foreach($days as $day)
                @php
                    $val = $serviceHours[$day] ?? null;
                    $highlightToday = ($day === $todayName);
                @endphp
                <div class="d-flex justify-content-between py-1" style="{{ $highlightToday ? 'font-weight:bold;' : '' }}">
                    <strong class="text-capitalize">{{ $day }}</strong>
                    @if(is_string($val) && strtolower($val)==='closed')
                        <span class="text-muted">Closed</span>
                    @elseif(is_array($val) && isset($val['open'], $val['close']))
                        <span>{{ readableTime($val['open']) }} — {{ readableTime($val['close']) }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            @endforeach
        </div>

        <hr>

        {{-- Today summary with live badge --}}
        <div id="todaySummary">
            @php
                $todayText = '<div class="small text-muted">No data for today</div>';
                if(isset($serviceHours[$todayName])) {
                    $t = $serviceHours[$todayName];
                    if(is_string($t) && strtolower($t)==='closed') {
                        $todayText = '<div><strong>Today:</strong> Closed <span class="badge bg-danger ms-2">Closed</span></div>';
                    } elseif(is_array($t) && isset($t['open'], $t['close'])) {
                        $todayText = "<div><strong>Today:</strong> ".readableTime($t['open'])." — ".readableTime($t['close'])." <span id='todayBadge' class='badge ".(now()->setTimezone('Asia/Dhaka')->between(Carbon::createFromFormat('H:i',$t['open'],'Asia/Dhaka'), Carbon::createFromFormat('H:i',$t['close'],'Asia/Dhaka'))?'bg-success':'bg-danger')." ms-2'>".(now()->setTimezone('Asia/Dhaka')->between(Carbon::createFromFormat('H:i',$t['open'],'Asia/Dhaka'), Carbon::createFromFormat('H:i',$t['close'],'Asia/Dhaka'))?'Open':'Closed')."</span></div>";
                    }
                }
            @endphp
            {!! $todayText !!}
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- JS for live badge updates --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceHours = @json($serviceHours);
    const todayName = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();

    const topBadge = document.getElementById('openStatusBadge');
    const todayBadge = document.getElementById('todayBadge');

    function parseTime(timeStr) {
        const [hour, minute] = timeStr.split(':').map(Number);
        return { hour, minute };
    }

    function isOpenNow(dayData) {
        if (!dayData || typeof dayData === 'string' && dayData.toLowerCase() === 'closed') return false;
        const now = new Date();
        const { hour: oh, minute: om } = parseTime(dayData.open);
        const { hour: ch, minute: cm } = parseTime(dayData.close);
        const openTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), oh, om);
        const closeTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), ch, cm);
        return now >= openTime && now <= closeTime;
    }

    function updateBadges() {
        const todayData = serviceHours[todayName];
        const open = isOpenNow(todayData);

        // Top-right badge
        topBadge.textContent = open ? 'Open now' : 'Closed now';
        topBadge.className = 'badge position-absolute top-0 end-0 m-2 ' + (open ? 'bg-success' : 'bg-danger');

        // Modal today badge
        if(todayBadge){
            todayBadge.textContent = open ? 'Open' : 'Closed';
            todayBadge.className = 'badge ms-2 ' + (open ? 'bg-success' : 'bg-danger');
        }
    }

    updateBadges();
    setInterval(updateBadges, 60000);
});
</script>



         
@php
    $points = $user->users_points ?? 0;
@endphp

            <img id="img-zoomer" src="{{ $user->image ? asset('profile-image/'.$user->image) : 'https://cdn-icons-png.flaticon.com/512/219/219983.png' }}"
               class="rounded-circle mb-3"
               alt="Profile Photo"
               style="width:100px; height:100px; object-fit:cover;">
            <h4 class="mb-2">{{ $user->name }}
@if($points < 1000)
    <span data-bs-toggle="modal" data-bs-target="#pointModal" style="cursor:pointer">
        🏆
    </span>
@elseif($points >= 1000 && $points < 10000)
    <span title="Level 1" data-bs-toggle="modal" data-bs-target="#pointModal" style="cursor:pointer">🥉</span>
@elseif($points >= 10000 && $points < 100000)
    <span title="Level 2" data-bs-toggle="modal" data-bs-target="#pointModal" style="cursor:pointer">🥈</span>
@elseif($points >= 100000 && $points < 1000000)
    <span title="Level 3" data-bs-toggle="modal" data-bs-target="#pointModal" style="cursor:pointer">🥇</span>
@elseif($points >= 1000000)
    <span class="badge bg-warning rounded-circle p-1" data-bs-toggle="modal" data-bs-target="#pointModal" style="cursor:pointer">✔</span>
@endif
            </h4>

            


           




<div class="modal fade" id="pointModal">
 <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
   <div class="modal-header">
    <h5>User Points</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
   </div>
   <div class="modal-body text-center">
    <h2>{{ $points }}</h2>
    <p>Total Points</p>
   </div>
  </div>
 </div>
</div>

            
            @if($user->category && $user->category->category_name)
            <p class="text-muted mb-2">
               <i class="bi bi-grid me-1"></i>{{ $user->category->category_name }}
            </p>
            @elseif($user->job_title)
            <p class="text-muted">
               <i class="bi bi-grid me-1"></i>{{ $user->job_title }}
            </p>
            @endif
            
            @if($user->area)
            <p class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $user->area }}</p>
            @endif
            
            <!-- Post Count -->
            <div class="row text-center mt-3">
               <div class="col border-end">
                  <h5 class="mb-0">{{ $posts->total() }}</h5>
                  <small class="text-muted">Posts</small>
               </div>
               <div class="col text-center{{ $user->category_id ? ' border-end' : '' }}">
                  <h5 class="mb-0" id="followersCount-{{ $user->id }}">{{ $user->followers()->count() ?? 0 }}</h5>
                  <small class="text-muted">Followers</small>
               </div>
               @if($user->category_id)
   <div class="col">
      <h5 class="mb-0">
         <!-- <i class="bi bi-star-fill text-warning"></i> -->
         {{ number_format($user->getAverageRating(), 1) }} ({{ $user->getTotalReviews() }})
      </h5>
      <small class="text-muted">
          Reviews
      </small>
   </div>
@endif
            </div>
            
            {{-- Action Buttons Based on User Type --}}
            <div class="mt-3">
               @auth
                  @if(Auth::id() === $user->id)
                     {{-- Own Profile - Show Add Post Button --}}
                     <button type="button" class="btn btn-primary btn-sm" onclick="checkProfileAndOpenModal()" id="addPostBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Post
                     </button>
                  @else
                     {{-- Other's Profile - Show Follow/Message Buttons --}}
                     @php
                     $isFollowing = auth()->user() && auth()->user()->following->contains($user->id);
                     @endphp
                     <button id="followBtn-{{ $user->id }}" class="btn btn-{{ $isFollowing ? 'danger' : 'primary' }} btn-sm"
                        onclick="toggleFollow({{ $user->id }})">
                        <i class="bi {{ $isFollowing ? 'bi-person-dash' : 'bi-person-plus' }} me-1"></i>
                        {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                     </button>
                  @endif
               @endauth
               
               {{-- Common Contact Buttons (Always Show) --}}
               @if($user->category_id)
               <a href="tel:{{ $user->phone_number }}">
                    <button class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="bi bi-telephone"></i>
                    </button>
               </a>
               @endif
               <a href="mailto:{{ $user->email }}">
                <button class="btn btn-outline-secondary btn-sm ms-2">
                    <i class="bi bi-envelope"></i>
                </button>
               </a>
               <!-- <button class="btn btn-outline-secondary btn-sm ms-2">
                  <i class="bi bi-globe"></i>
               </button> -->
               <button class="btn btn-outline-secondary btn-sm ms-2" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-three-dots"></i>
               </button>

               {{-- Dropdown Menu --}}
               <div class="dropdown position-static">
                  <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                     <li>
                        <a class="dropdown-item" href="#" onclick="copyProfileLink(event)">Share</a>
                     </li>
                     @auth
                        @if(Auth::id() !== $user->id)
                           <li><a class="dropdown-item" href="#">Report</a></li>
                           <li><a class="dropdown-item text-danger" href="#">Block</a></li>
                        @endif
                     @endauth
                  </ul>
               </div>

               <style>
                .card {
                    overflow: visible !important;
                }
                .dropdown-menu {
                    z-index: 9999 !important;
                }
                </style>
               
               {{-- Show profile incomplete message only for own profile --}}
               @auth
                  @if(Auth::id() === $user->id)
                     <div class="mt-3 mb-0" id="myProfileAlert" style="display: none;" onclick="checkProfileAndOpenModal()"></div>
                  @endif
               @endauth
               
               {{-- Guest user alert --}}
               @guest
               <div class="alert alert-info mt-3 mb-0">
                  Please <a href="{{ route('register') }}">register</a> or <a href="{{ route('login') }}">login</a> to interact with posts.
               </div>
               @endguest
               
               
            </div>
         </div>
      </div>
   </div>
</div>

<div id="toast" style="
   position: fixed;
   bottom: 30px;
   left: 50%;
   transform: translateX(-50%);
   background: #333;
   color: #fff;
   padding: 10px 20px;
   border-radius: 6px;
   width: 175px;
   text-align:center;
   display: none;
   z-index: 9999;
   font-size: 14px;
   white-space: nowrap; /* এক লাইনে থাকবে */
   box-shadow: 0 4px 8px rgba(0,0,0,0.3);
">
   Profile link copied!
</div>

{{-- JavaScript Functions --}}
<script>
// Copy profile link function
function copyProfileLink(e) {
    e.preventDefault();
    let profileLink = window.location.href;

    // www বাদ দেওয়া
    profileLink = profileLink.replace("://www.", "://");

    navigator.clipboard.writeText(profileLink)
        .then(() => {
            let toast = document.getElementById('toast');
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        })
        .catch(err => {
            console.error("Failed to copy: ", err);
        });
}


@auth
    @if(Auth::id() === $user->id)
        // Profile completeness check functions
        function isProfileComplete(user) {
            if (!user) return false;
            
            if (!user.image || user.image.trim() === '') return false;
            
            return true;
        }
        
        function showProfileIncompleteMessage(targetElementId) {
            const user = window.currentUser || @json(auth()->user());
            
            const missingFields = [];
            const fieldLabels = {
                'image': 'Profile Photo'
            };
            
            if (!user.image || user.image.trim() === '') {
                missingFields.push(fieldLabels.image);
            }
            
            const targetElement = document.getElementById(targetElementId);
            
            if (!targetElement) {
                console.error(`Element with ID '${targetElementId}' not found`);
                return;
            }
            
            if (missingFields.length > 0) {
                let message = 'If you want to add post please update ';
                
                if (missingFields.length === 1) {
                    message += missingFields[0];
                } else if (missingFields.length === 2) {
                    message += missingFields[0] + ' and ' + missingFields[1];
                } else {
                    message += missingFields.slice(0, -1).join(', ') + ' and ' + missingFields[missingFields.length - 1];
                }
                
                targetElement.innerHTML = `
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Profile Incomplete:</strong> ${message}
                `;
                targetElement.style.display = 'block';
                
                if (!targetElement.classList.contains('alert')) {
                    targetElement.className += ' alert alert-info';
                }
            } else {
                targetElement.style.display = 'none';
            }
            
            return missingFields.length === 0;
        }
        
        function checkProfileAndOpenModal() {
            const alertDiv = document.getElementById('myProfileAlert');
            if (alertDiv && window.getComputedStyle(alertDiv).display !== 'none') {
                window.location.href = '/profile';
                return;
            }
            
            const user = window.currentUser || @json(auth()->user());
            if (user && isProfileComplete(user)) {
                const modal = new bootstrap.Modal(document.getElementById('createPostModal'));
                modal.show();
            } else {
                window.location.href = '/profile';
            }
        }
        
        // Show profile message on load
        document.addEventListener('DOMContentLoaded', function() {
            showProfileIncompleteMessage('myProfileAlert');
        });
    @endif
    
    @if(Auth::id() !== $user->id)
        // Follow toggle function
        function toggleFollow(userId) {
            let btn = document.getElementById('followBtn-' + userId);
            let followersCountEl = document.getElementById('followersCount-' + userId);
        
            let isFollowing = btn.classList.contains('btn-danger');
            let url = isFollowing ? '/unfollow/' + userId : '/follow/' + userId;
        
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('btn-primary');
                    btn.classList.toggle('btn-danger');
        
                    let icon = btn.querySelector('i');
                    icon.classList.toggle('bi-person-plus');
                    icon.classList.toggle('bi-person-dash');
        
                    btn.textContent = isFollowing ? 'Follow' : 'Unfollow';
                    btn.prepend(icon);
        
                    let count = parseInt(followersCountEl.textContent);
                    followersCountEl.textContent = isFollowing ? count - 1 : count + 1;
                } else if(data.error) {
                    alert(data.error);
                }
            })
            .catch(err => console.error(err));
        }
    @endif
@endauth
</script>