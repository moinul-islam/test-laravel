<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid d-flex align-items-center">
        <!-- Left: Logo -->
        <a class="navbar-brand" href="/">
            <img src="{{ asset('logo.png') }}"
                id="site-logo"
                class="rounded-circle"
                alt="Logo"
                style="width:32px; height:32px; object-fit:cover;">
        </a>
        
        <!-- Center: Search -->
        <form class="flex-grow-1 mx-3 container" style="width:180px;" id="searchForm" onsubmit="handleSearch(event)">
            <div class="position-relative w-100"> 
                <input id="searchInput" 
                       class="form-control text-center"
                       type="text" 
                       placeholder="Search" 
                       aria-label="Search"
                       autocomplete="off"
                       oninput="handleSearchInput(this.value)"
                       onfocus="handleSearchInput(this.value)"
                       onblur="handleSearchBlur()">
                
                <button type="button" 
                        id="searchIcon" 
                        class="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent" 
                        style="display:none; z-index: 10; cursor: pointer; padding-right: 12px;"
                        onclick="handleSearchClick()">
                    <i class="bi bi-search"></i>
                </button>

                <!-- Suggestions Dropdown -->
                <div id="searchSuggestions" 
                     class="position-absolute w-100 bg-white border rounded shadow-sm mt-1" 
                     style="display:none; max-height: 400px; overflow-y: auto; z-index: 1060;">
                    <!-- Suggestions will be inserted here -->
                </div>
            </div> 
        </form>

        <!-- Notification Badge CSS -->
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

        /* Search Suggestions Styles */
        .suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
            color: inherit;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-icon {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 12px;
        }

        .suggestion-text {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .suggestion-username {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }

        .no-results-message {
            padding: 15px;
            text-align: center;
            color: #888;
            font-size: 13px;
        }
        </style>

        <!-- Right: User / Guest Menu -->
        <ul class="navbar-nav">
            @auth
            @php
                $userId = Auth::id();
                $hasPlacedOrders = \App\Models\Order::where('user_id', $userId)->exists();
                $hasReceivedOrders = \App\Models\Order::where('vendor_id', $userId)->exists();
                
                $lastSeenKey = 'vendor_orders_seen_' . $userId;
                $lastSeen = session($lastSeenKey);
                
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

                    <li><a class="dropdown-item" href="/notifications">Notification</a></li>
                    
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

                    @if(auth()->check() && auth()->user()->role === 'delivery')
                        <li><a class="dropdown-item" href="{{ route('delivery.page') }}">Delivery</a></li>
                    @endif
                    
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <li><a class="dropdown-item" href="{{ route('delivery.page') }}">Delivery</a></li>
                        <li><a class="dropdown-item" href="{{ route('contribute') }}">Contribute</a></li>
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
                    <a class="nav-link" href="javascript:void(0)" id="guestDropdown" role="button" data-bs-toggle="modal" data-bs-target="#userNavagateModal">
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

{{-- Categories Data for Search --}}
@php
    $universalCategories = \App\Models\Category::where('cat_type', 'universal')
        ->where('parent_cat_id', null)
        ->get();
    
    $allSearchableCategories = [];
    foreach($universalCategories as $uCat) {
        $productCategories = \App\Models\Category::where('parent_cat_id', $uCat->id)
            ->whereIn('cat_type', ['product', 'service','profile'])
            ->get();
        
        foreach($productCategories as $pCat) {
            // Get username from user_id
            $username = \App\Models\User::where('id', $pCat->user_id)->value('username');
            
            $allSearchableCategories[] = [
                'category_name' => $pCat->category_name,
                'slug' => $pCat->slug,
                'image' => $pCat->image,
                'username' => $username ?? '',
            ];
        }
    }
@endphp

<script>
// Store categories data in JavaScript
const searchableCategories = @json($allSearchableCategories);
const visitorLocationPath = '{{ $visitorLocationPath ?? "bd" }}';

let searchTimeout;
let isMouseOnSuggestion = false;

// Handle search input
function handleSearchInput(value) {
    clearTimeout(searchTimeout);
    const query = value.trim().toLowerCase();
    
    const searchIcon = document.getElementById('searchIcon');
    const suggestionsDiv = document.getElementById('searchSuggestions');
    
    // If empty, hide everything
    if (query === '') {
        searchIcon.style.display = 'none';
        suggestionsDiv.style.display = 'none';
        return;
    }
    
    // Debounce for better performance
    searchTimeout = setTimeout(() => {
        searchCategories(query);
    }, 200);
}

// Handle search blur
function handleSearchBlur() {
    // Delay hiding to allow click on suggestions
    setTimeout(() => {
        if (!isMouseOnSuggestion) {
            document.getElementById('searchSuggestions').style.display = 'none';
        }
    }, 200);
}

// Search in categories
function searchCategories(query) {
    const suggestionsDiv = document.getElementById('searchSuggestions');
    const searchIcon = document.getElementById('searchIcon');
    
    // Filter categories that match query
    const matches = searchableCategories.filter(cat => {
        const categoryName = cat.category_name.toLowerCase();
        const username = cat.username.toLowerCase();
        
        return categoryName.includes(query) || username.includes(query);
    });
    
    if (matches.length > 0) {
        // Show suggestions
        displaySuggestions(matches.slice(0, 8)); // Show max 8 results
        searchIcon.style.display = 'none';
    } else {
        // No matches, show search button
        suggestionsDiv.style.display = 'none';
        searchIcon.style.display = 'block';
    }
}

// Display suggestions
function displaySuggestions(categories) {
    const suggestionsDiv = document.getElementById('searchSuggestions');
    let html = '';
    
    categories.forEach(category => {
        const imageUrl = category.image 
            ? `{{ asset('icon/') }}/${category.image}` 
            : `{{ asset('profile-image/no-image.jpeg') }}`;
        
        const categoryUrl = `/${visitorLocationPath}/${category.slug}`;
        
        html += `
            <a href="${categoryUrl}" class="suggestion-item d-flex align-items-center text-decoration-none">
                <img src="${imageUrl}" alt="${category.category_name}" class="suggestion-icon">
                <div>
                    <div class="suggestion-text">${highlightMatch(category.category_name, document.getElementById('searchInput').value)}</div>
                    ${category.username ? `<div class="suggestion-username">@${highlightMatch(category.username, document.getElementById('searchInput').value)}</div>` : ''}
                </div>
            </a>
        `;
    });
    
    suggestionsDiv.innerHTML = html;
    suggestionsDiv.style.display = 'block';
    
    // Track mouse on suggestions
    suggestionsDiv.addEventListener('mouseenter', () => {
        isMouseOnSuggestion = true;
    });
    suggestionsDiv.addEventListener('mouseleave', () => {
        isMouseOnSuggestion = false;
    });
}

// Highlight matching text
function highlightMatch(text, query) {
    if (!query) return text;
    
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<strong>$1</strong>');
}

// Handle search form submission
function handleSearch(event) {
    event.preventDefault();
    const query = document.getElementById('searchInput').value.trim();
    
    console.log('Form submitted:', query); // Debug
    
    if (query) {
        // Redirect to search results page
        window.location.href = `/search?q=${encodeURIComponent(query)}`;
    }
    
    return false;
}

// Handle search icon click
function handleSearchClick() {
    const query = document.getElementById('searchInput').value.trim();
    
    
    console.log('Search icon clicked:', query); // Debug
    
    if (query) {
        // Redirect to search results page
        window.location.href = `/search?q=${encodeURIComponent(query)}`;
    } else {
        alert('Please enter something to search');
    }

    
}

// Close suggestions when clicking outside
document.addEventListener('click', function(event) {
    const searchInput = document.getElementById('searchInput');
    const suggestionsDiv = document.getElementById('searchSuggestions');
    
    if (!searchInput.contains(event.target) && !suggestionsDiv.contains(event.target)) {
        suggestionsDiv.style.display = 'none';
    }
});

// Clear cart on logout
function clearCartOnLogout() {
    localStorage.removeItem('cart');
}
</script>