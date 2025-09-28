<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        @media (max-width: 331px) {
            .ms-2 {
                margin-left: 0 !important;
            }
        }

        a {
            text-decoration:none;
        }
        html, body {
            overflow-x: clip; /* পুরো পেজে horizontal scrollbar disable */
            -ms-overflow-x: clip;
            width: 100%;
        }

        .btn-group-wrapper {
                        overflow-x: auto;
                        white-space: nowrap;
                        -webkit-overflow-scrolling: touch; /* iOS smooth scroll */
                        scrollbar-width: none; /* Firefox scrollbar hide */
                    }

                    .btn-group-wrapper::-webkit-scrollbar {
                        display: none; /* Chrome, Safari scrollbar hide */
                    }

                    .btn-group .btn {
                        flex: 0 0 auto; /* বোতাম গুলো shrink হবে না */
                    }

                    .btn-group-wrapper .btn {
                        font-size: .975rem;
                    }
    </style>

    <style>
      .scroll-container {
      overflow-x: auto;
      overflow-y: hidden;
      white-space: nowrap;
      }
      @media (max-width: 768px) {
      .scroll-container {
      scrollbar-width: none;
      -ms-overflow-style: none;
      }
      .scroll-container::-webkit-scrollbar {
      display: none;
      }
      }
      @media (min-width: 769px) {
      .scroll-container::-webkit-scrollbar {
      height: 8px;
      }
      .scroll-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
      }
      .scroll-container::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 4px;
      }
      .scroll-container::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
      }
      }
      .scroll-content {
      display: inline-flex;
      gap: 15px;
      min-width: 100%;
      }
      .nav-item-custom {
      flex-shrink: 0;
      background: white;
      border-radius: 8px;
      padding: 3px 10px 4px 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 1px solid #c8c8c8;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 500;
      color: #6c757d;
      text-decoration: none;
      white-space: nowrap;
      }
      .nav-item-custom.active {
      background: #e3f0ffff;
      color: #087fffff;
      border-color: #087fffff;
      }
      .nav-icon {
      font-size: 1.1em;
      }
      .nav-item-custom:not(.active):hover {
      background: #f8f9fa;
      }

         /* Smooth scroll + offset for fixed header */
html {
    scroll-behavior: smooth;
}

 /* Force dropdown to open on left side */
 .navbar .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }

input {
    outline: none !important;
    box-shadow: none !important;
}

input:focus {
    outline: none !important;
    box-shadow: none !important;
    border-color: inherit !important;
}

/* Browser er default clear button hide করার জন্য */
input::-webkit-search-cancel-button,
input::-webkit-search-decoration {
    -webkit-appearance: none;
    appearance: none;
}

/* Firefox er clear button hide */
input::-moz-search-cancel-button {
    display: none;
}

/* IE/Edge er clear button hide */
input::-ms-clear {
    display: none;
}

        .navbar {
  --bs-navbar-padding-x: 0;
  --bs-navbar-padding-y: 0 !important;}

  .navbar-expand-lg .navbar-nav .nav-link {
    padding-right: 0;
    padding-left: 0;
  }

  .navbar-brand {
  margin-right: 0;
}


   </style>
   
    
    <!-- Firebase SDK v8 (Legacy) -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid d-flex align-items-center">
        <!-- Left: Logo -->
        <a class="navbar-brand" href="/">
            <img src="{{ asset('logo.png') }}"
                 class="rounded-circle"
                 alt="User"
                 style="width:32px; height:32px; object-fit:cover;">
        </a>
        <!-- Center: Search -->
        <form class="flex-grow-1 mx-3 container" style="width:180px;" onsubmit="handleSearch(event)">
            <div class="position-relative w-100"> 
                <input id="searchInput" class="form-control text-center"
                       type="search" placeholder="Search" aria-label="Search">
                <button type="submit" id="searchIcon" class="position-absolute end-0 top-50 translate-middle-y pe-3 border-0 bg-transparent" style="display:none;">
                    <i class="bi bi-search"></i>
                </button>
            </div> 
        </form>
        <!-- Notification Badge CSS - head section এ add করুন -->
<style>
.notification-badge {
    position: absolute;
    top: 2px;
    right: -6px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 10;
}

@media (min-width: 991px) {
    .notification-badge {
    right: 1px;    
}
}


.dropdown-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 12px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    margin-left: 10px;
}
</style>

<!-- Right: User / Guest Menu -->
<ul class="navbar-nav">
    @auth
    @php
        $userId = Auth::id();
        $hasPlacedOrders = \App\Models\Order::where('user_id', $userId)->exists();
        $hasReceivedOrders = \App\Models\Order::where('vendor_id', $userId)->exists();
        
        // Check if vendor has visited sell page recently
        $lastSeenKey = 'vendor_orders_seen_' . $userId;
        $lastSeen = session($lastSeenKey);
        
        // New pending orders count for vendor (only count orders created after last seen)
        $query = \App\Models\Order::where('vendor_id', $userId)
            ->where('status', 'pending');
            
        if ($lastSeen) {
            $query->where('created_at', '>', $lastSeen);
        }
        
        $newOrdersCount = $query->count();
    @endphp
    
    <li class="nav-item dropdown">
    <a class="nav-link d-flex align-items-center position-relative" href="javascript:void(0)" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="{{ asset('profile-image/' . (Auth::user()->image ?? 'default.png')) }}"
             class="rounded-circle"
             alt="User"
             style="width:32px; height:32px; object-fit:cover;">
        
        @if($newOrdersCount > 0)
            <span class="notification-badge">{{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}</span>
        @endif
    </a>
    <ul class="dropdown-menu position-absolute" aria-labelledby="userDropdown" style="z-index:1050;">
        <li><a class="dropdown-item" href="{{ route('dashboard') }}">Profile</a></li>
        
        @if($hasPlacedOrders)
            <li><a class="dropdown-item" href="{{ route('buy') }}">Buy</a></li>
        @endif
        
        @if($hasReceivedOrders)
            <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('sell') }}">
                    Sell
                    @if($newOrdersCount > 0)
                        <span class="dropdown-badge">{{ $newOrdersCount }}</span>
                    @endif
                </a>
            </li>
        @endif

        {{-- Delivery role এর জন্য extra link --}}
        @if(auth()->check() && auth()->user()->role === 'delivery')
            <li><a class="dropdown-item" href="{{ route('delivery.page') }}">Delivery</a></li>
        @endif
        {{-- Admin role এর জন্য extra link --}}
        @if(auth()->check() && auth()->user()->role === 'admin')
            <li><a class="dropdown-item" href="{{ route('delivery.page') }}">Delivery</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.page') }}">Admin</a></li>
        @endif
        
        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Settings</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" onsubmit="clearCartOnLogout()">
                @csrf
                <button type="submit" class="dropdown-item text-danger">Logout</button>
            </form>
        </li>
    </ul>
</li>

@endauth
    @guest
        <li class="nav-item dropdown">
            <a class="nav-link" href="javascript:void(0)" id="guestDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ asset('profile-image/default.png') }}"
                     class="rounded-circle"
                     alt="User"
                     style="width:32px; height:32px; object-fit:cover;">
            </a>
            <ul class="dropdown-menu position-absolute" aria-labelledby="guestDropdown" style="z-index:1050;">
                <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                <li><a class="dropdown-item" href="{{ route('register') }}">Signup</a></li>
            </ul>
        </li>
    @endguest
</ul>
    </div>
</nav>

<!-- Main Content -->
@yield('main-content')
@include('frontend.cart')

<footer class="py-4 mt-5 border-top">
    <div class="container text-center">
        
        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <a href="{{ url('/about-us') }}" class="text-muted text-decoration-none">
                    About
                </a>
            </li>
            <li class="list-inline-item">.</li>
            <li class="list-inline-item">
                <a href="{{ url('/privacy-policy') }}" class="text-muted text-decoration-none">
                    Privacy
                </a>
            </li>
            <li class="list-inline-item">.</li>
            <li class="list-inline-item">
                <a href="{{ url('/terms-and-condition') }}" class="text-muted text-decoration-none">
                    Terms
                </a>
            </li>
        </ul>

        <!-- Language Switcher -->
        <form method="POST" action="{{ route('set-locale') }}" class="d-inline">
            @csrf
            <select name="locale" onchange="this.form.submit()">
                <option value="en" {{ Session::get('locale', 'en') == 'en' ? 'selected' : '' }}>English</option>
                <option value="bn" {{ Session::get('locale', 'en') == 'bn' ? 'selected' : '' }}>বাংলা</option>
            </select>
        </form>

        <p class="mb-0 text-muted mt-2">eINFO &copy; {{ date('Y') }}</p>
    </div>
</footer>


<script>
    // Global variables
    window.userAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    window.Laravel = {
        user: @json(auth()->user())
    };

    // Firebase Configuration
    const firebaseConfig = {
        apiKey: "{{ env('FIREBASE_API_KEY', 'AIzaSyAuFHcuEyq070sM7Pgt4JyriybPnNEq6M4') }}",
        authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', 'einfo-e95ba.firebaseapp.com') }}",
        projectId: "{{ env('FIREBASE_PROJECT_ID', 'einfo-e95ba') }}",
        storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', 'einfo-e95ba.firebasestorage.app') }}",
        messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '438009665395') }}",
        appId: "{{ env('FIREBASE_APP_ID', '1:438009665395:web:d74475efa497609b58d706') }}",
        measurementId: "{{ env('FIREBASE_MEASUREMENT_ID', 'G-DT4NHYZG47') }}"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    // VAPID Key from Laravel env
    const vapidKey = '{{ env("FIREBASE_VAPID_KEY") }}';

    // Global FCM token variable
    let currentFCMToken = null;

    // Initialize notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeNotifications();
        attachFormListeners();
    });

    async function initializeNotifications() {
        try {
            // Check if notifications are supported
            if (!('Notification' in window)) {
                console.log('This browser does not support notifications.');
                return;
            }

            // Request permission immediately when page loads
            const permission = await Notification.requestPermission();
            
            if (permission === 'granted') {
                console.log('Notification permission granted.');
                
                // Register service worker
                if ('serviceWorker' in navigator) {
                    try {
                        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                        console.log('Service Worker registered successfully');
                        
                        // Get FCM token
                        const token = await messaging.getToken({ 
                            vapidKey: vapidKey,
                            serviceWorkerRegistration: registration
                        });
                        
                        if (token) {
                            console.log('FCM Token generated:', token);
                            currentFCMToken = token;
                            
                            // Always store token in localStorage first
                            localStorage.setItem('fcm_token', token);
                            
                            // Save token if user is authenticated
                            if (window.userAuthenticated === 'true') {
                                saveTokenToDatabase(token);
                            } else {
                                console.log('Token stored for later use (user not logged in)');
                            }
                        } else {
                            console.log('No registration token available.');
                        }
                    } catch (swError) {
                        console.error('Service Worker registration failed:', swError);
                    }
                }
            } else {
                console.log('Notification permission denied.');
            }
        } catch (error) {
            console.error('Error initializing notifications:', error);
        }
    }

    // Function to save token to database (can be called without auth check)
    function saveTokenToDatabase(token, skipAuthCheck = false) {
        // Skip auth check if explicitly told to (for registration/login)
        if (!skipAuthCheck && window.userAuthenticated !== 'true') {
            console.log('User not authenticated, storing token for later');
            localStorage.setItem('fcm_token', token);
            return;
        }

        fetch('/save-fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ fcm_token: token })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Token saved successfully:', data);
            // Remove from localStorage after successful save
            localStorage.removeItem('fcm_token');
        })
        .catch(error => {
            console.error('Error saving token:', error);
            // Keep in localStorage if save failed
            localStorage.setItem('fcm_token', token);
        });
    }

    // Attach listeners to forms
    function attachFormListeners() {
        // Get FCM token for form submission
        function getFcmToken() {
            return currentFCMToken || localStorage.getItem('fcm_token') || '';
        }

        // Add hidden FCM token field to form if not exists
        function addFcmTokenField(form) {
            let fcmField = form.querySelector('input[name="fcm_token"]');
            if (!fcmField) {
                fcmField = document.createElement('input');
                fcmField.type = 'hidden';
                fcmField.name = 'fcm_token';
                form.appendChild(fcmField);
            }
            fcmField.value = getFcmToken();
            return fcmField;
        }

        // Handle Login Form
        const loginForms = document.querySelectorAll('form[action*="login"], form#loginForm');
        loginForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                addFcmTokenField(this);
                console.log('FCM token added to login form');
            });
        });

        // Handle Registration Form
        const registerForms = document.querySelectorAll('form[action*="register"], form#registerForm');
        registerForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                addFcmTokenField(this);
                console.log('FCM token added to registration form');
            });
        });

        // Generic form handler for any auth form
        const authForms = document.querySelectorAll('form[method="POST"]');
        authForms.forEach(form => {
            // Check if it's an auth-related form
            const action = form.action || '';
            if (action.includes('login') || action.includes('register')) {
                // Skip if already handled above
                if (!form.hasAttribute('data-fcm-attached')) {
                    form.setAttribute('data-fcm-attached', 'true');
                    form.addEventListener('submit', function(e) {
                        addFcmTokenField(this);
                    });
                }
            }
        });
    }

    // Handle foreground messages - SINGLE HANDLER
    messaging.onMessage((payload) => {
        console.log('Message received in foreground:', payload);
        
        // Show browser notification manually for foreground
        if (Notification.permission === 'granted') {
            const notification = new Notification(payload.notification.title, {
                body: payload.notification.body,
                icon: payload.notification.icon || 'https://einfo.site/logo.png',
                badge: 'https://einfo.site/logo.png',
                tag: 'firebase-notification',
                requireInteraction: true,
                data: {
                    click_action: payload.notification.click_action || payload.data?.click_action || '/',
                    order_id: payload.data?.order_id || null
                }
            });
            
            notification.onclick = function() {
                window.focus();
                notification.close();
                
                // Navigate to specific page if needed
                if (notification.data && notification.data.click_action) {
                    window.location.href = notification.data.click_action;
                }
            };
            
            // Auto close after 8 seconds
            setTimeout(() => {
                notification.close();
            }, 8000);
        }
    });

    // Check for stored token after successful auth (call this after login/register)
    function handlePostAuth() {
        const storedToken = localStorage.getItem('fcm_token');
        if (storedToken) {
            console.log('Found stored FCM token after auth, saving to database...');
            // Force save with skipAuthCheck = true
            saveTokenToDatabase(storedToken, true);
        } else if (currentFCMToken) {
            console.log('Using current FCM token after auth...');
            saveTokenToDatabase(currentFCMToken, true);
        }
    }

    // Clear data on logout
    function clearCartOnLogout() {
        localStorage.removeItem('cart');
        localStorage.removeItem('fcm_token');
    }

    function handleSearch(event) {
        event.preventDefault();
        const searchTerm = document.getElementById('searchInput').value;
        if (searchTerm.trim()) {
            window.location.href = '/search?q=' + encodeURIComponent(searchTerm);
        }
    }

    // Make functions globally available
    window.handlePostAuth = handlePostAuth;
    window.saveTokenToDatabase = saveTokenToDatabase;
    window.clearCartOnLogout = clearCartOnLogout;

    // Auto-save token if user just logged in (detect by checking session)
    if (window.userAuthenticated === 'true') {
        // Check if we have a token that needs saving
        setTimeout(() => {
            const unsavedToken = localStorage.getItem('fcm_token');
            if (unsavedToken) {
                console.log('User authenticated, saving pending FCM token...');
                saveTokenToDatabase(unsavedToken, true);
            }
        }, 1000); // Small delay to ensure everything is loaded
    }
</script>


<script>
  const searchInput = document.getElementById('searchInput');
  const searchIcon = document.getElementById('searchIcon');
  
  searchInput.addEventListener('input', () => {
    if(searchInput.value.length > 0){
      searchIcon.style.display = 'block';
      searchInput.classList.remove('text-center');
      searchInput.classList.add('text-start');
    } else {
      searchIcon.style.display = 'none';
      searchInput.classList.remove('text-start');
      searchInput.classList.add('text-center');
    }
  });

  // Search function
  function handleSearch(event) {
    event.preventDefault();
    const query = searchInput.value.trim();
    if (query) {
      // Add your search logic here
      console.log('Searching for:', query);
      // Example: window.location.href = '/search?q=' + encodeURIComponent(query);
    }
  }


//   let lastScrollTop = 0;
//   const navbar = document.querySelector('.navbar');

//   window.addEventListener('scroll', function() {
//       let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      
//       if (scrollTop > lastScrollTop) {
         
//           navbar.style.transform = 'translateY(-100%)';
//           navbar.style.transition = 'transform 0.3s ease-in-out';
//       } else {
         
//           navbar.style.transform = 'translateY(0)';
//           navbar.style.transition = 'transform 0.3s ease-in-out';
//       }
      
//       lastScrollTop = scrollTop;
//   });


</script>


<!-- CSS Code - master.blade.php er <head> section e rakhben -->
<style>
/* Universal Image Zoomer Styles */
.img-zoomer-container {
    position: relative;
    display: inline-block;
    cursor: zoom-in;
    transition: transform 0.2s ease;
}

.img-zoomer-container:hover {
    transform: scale(1.02);
}

#img-zoomer {
    cursor: zoom-in;
    transition: all 0.3s ease;
}


/* Zoom Modal Styles */
.zoom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    cursor: zoom-out;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.zoom-modal.active {
    display: flex;
    opacity: 1;
}

.zoom-modal img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    transition: transform 0.3s ease;
    cursor: zoom-out;
}



/* Close Button */
.zoom-close {
    position: absolute;
    top: 0;
    right: 30px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 100000;
    transition: color 0.2s ease;
    user-select: none;
}

.zoom-close:hover {
    color: #ff4444;
}

/* Loading Animation */
.zoom-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255,255,255,0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .zoom-modal img {
        max-width: 100%;
        max-height: 100%;
    }
    
    .zoom-close {
        top: 10px;
        right: 15px;
        font-size: 30px;
    }
}
</style>

<!-- JavaScript Code - master.blade.php er closing </body> tag er age rakhben -->
<!-- JavaScript Code - master.blade.php er closing </body> tag er age rakhben -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let modal = null;
    let modalOpen = false;
    
    function createModal() {
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'zoom-modal';
            modal.innerHTML = `
                <span class="zoom-close">&times;</span>
                <div class="zoom-loading"></div>
                <img src="" alt="Zoomed">
            `;
            document.body.appendChild(modal);
        }
        return modal;
    }
    
    function openModal(imgSrc) {
        const m = createModal();
        const img = m.querySelector('img');
        const loading = m.querySelector('.zoom-loading');
        const closeBtn = m.querySelector('.zoom-close');
        
        modalOpen = true;
        window.location.hash = '#zoom';
        
        m.classList.add('active');
        loading.style.display = 'block';
        img.style.display = 'none';
        
        const tempImg = new Image();
        tempImg.onload = function() {
            img.src = this.src;
            loading.style.display = 'none';
            img.style.display = 'block';
        };
        tempImg.src = imgSrc;
        
        // Close button ONLY
        closeBtn.onclick = function() {
            closeModal();
        };
    }
    
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            modalOpen = false;
            if (window.location.hash === '#zoom') {
                history.back();
            }
        }
    }
    
    // Initialize images with lazy loading support
    function initImage(img) {
        if (img.getAttribute('data-zoom-init')) return;
        img.setAttribute('data-zoom-init', 'true');
        
        img.onclick = function() {
            let src = this.src;
            // Check for lazy loading attributes
            if (this.getAttribute('data-src')) {
                src = this.getAttribute('data-src');
            } else if (this.getAttribute('data-lazy')) {
                src = this.getAttribute('data-lazy');
            }
            openModal(src);
        };
    }
    
    // Initialize existing images
    document.querySelectorAll('#img-zoomer').forEach(initImage);
    
    // Watch for new images (AJAX/dynamic content)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    if (node.id === 'img-zoomer') {
                        initImage(node);
                    }
                    node.querySelectorAll && node.querySelectorAll('#img-zoomer').forEach(initImage);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Back button
    window.addEventListener('hashchange', function() {
        if (modalOpen && window.location.hash !== '#zoom') {
            if (modal) {
                modal.classList.remove('active');
                modalOpen = false;
            }
        }
    });
    
    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOpen) {
            closeModal();
        }
    });
});
</script>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePostModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this post? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Post</button>
            </div>
        </div>
    </div>
</div>




{{-- Lazy Loading JavaScript with Delete Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let isLoading = false;
    let postIdToDelete = null;
    
    // Get user ID if we're on a profile page (PHP থেকে)
    const userId = @json($user->id ?? null);
    
    const postsContainer = document.getElementById('posts-container');
    const loadingSpinner = document.getElementById('loading');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreContainer = document.getElementById('load-more-container');

    // Load More Button Click
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMorePosts();
        });
    }

    // Auto Load on Scroll (Optional)
    window.addEventListener('scroll', function() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
            if (!isLoading && loadMoreBtn && loadMoreBtn.style.display !== 'none') {
                loadMorePosts();
            }
        }
    });

    function loadMorePosts() {
        if (isLoading) return;
        
        isLoading = true;
        currentPage++;
        
        // Show loading spinner
        loadingSpinner.style.display = 'block';
        if (loadMoreBtn) loadMoreBtn.style.display = 'none';
        
        // Determine the correct URL based on context
        let url;
        if (userId) {
            // Profile page - load user-specific posts
            url = `/posts/load-more/${userId}`;
        } else {
            // Dashboard/Home page - load all posts
            url = '{{ route("posts.loadmore") }}';
        }
        
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                page: currentPage
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Hide loading spinner
                loadingSpinner.style.display = 'none';
                
                // Append new posts
                postsContainer.insertAdjacentHTML('beforeend', response.posts);
                
                // Initialize functionality for new posts
                initReadMore();
                
                // Show/Hide load more button
                if (response.hasMore) {
                    if (loadMoreBtn) loadMoreBtn.style.display = 'block';
                } else {
                    if (loadMoreContainer) loadMoreContainer.style.display = 'none';
                }
                
                isLoading = false;
            },
            error: function(xhr, status, error) {
                loadingSpinner.style.display = 'none';
                if (loadMoreBtn) loadMoreBtn.style.display = 'block';
                console.error('Error loading posts:', error);
                isLoading = false;
            }
        });
    }
    
    // Initialize Read More functionality using event delegation
    function initReadMore() {
        // Remove existing delegated event listener if any
        document.removeEventListener('click', handleReadMoreClick);
        // Add delegated event listener to document
        document.addEventListener('click', handleReadMoreClick);
    }
    
    // Handle read more clicks (works for both existing and new posts)
    function handleReadMoreClick(e) {
        if (e.target.classList.contains('read-more')) {
            e.preventDefault();
            const readMoreBtn = e.target;
            const para = readMoreBtn.previousElementSibling;
            
            if (para.style.maxHeight === 'none') {
                para.style.maxHeight = '75px';
                readMoreBtn.textContent = 'Read more';
            } else {
                para.style.maxHeight = 'none';
                readMoreBtn.textContent = 'Read less';
            }
        }
    }
    
    // Initialize Delete Button functionality using event delegation
    function initDeleteButtons() {
        // Remove existing delegated event listener if any
        document.removeEventListener('click', handleDeleteClick);
        // Add delegated event listener to document
        document.addEventListener('click', handleDeleteClick);
    }
    
    // Handle delete button clicks (works for both existing and new posts)
    function handleDeleteClick(e) {
        if (e.target.closest('.delete-post-btn')) {
            e.preventDefault();
            const deleteBtn = e.target.closest('.delete-post-btn');
            postIdToDelete = deleteBtn.getAttribute('data-post-id');
        }
    }
    
    // Handle confirm delete
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (postIdToDelete) {
                deletePost(postIdToDelete);
            }
        });
    }
    
    // Delete post function
    function deletePost(postId) {
        // Show loading state
        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Deleting...';
        
        fetch(`/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the post from DOM
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    postElement.remove();
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deletePostModal'));
                modal.hide();
                
                // Show success message
                showToast('Post deleted successfully!', 'success');
            } else {
                showToast(data.message || 'Failed to delete post', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Something went wrong. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = 'Delete Post';
            postIdToDelete = null;
        });
    }
    
    // Toast notification function
    function showToast(message, type = 'success') {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Create or get toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
    
    // Initialize functionality for existing posts
    initReadMore();
    initDeleteButtons();
});
</script>

<script>
    window.userAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

    window.Laravel = {
        user: @json(auth()->user())
    };
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>