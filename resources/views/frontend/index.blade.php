@extends("frontend.master")
@section('main-content')

{{-- Posts Container --}}
<div class="container mt-4">




    {{-- Include Sidebar --}}
    @include('frontend.body.sidebar')

    {{--
    @include('frontend.phonebook')
    --}}

    @php
           $countries = App\Models\Country::orderByRaw("CASE WHEN username = 'international' THEN 0 ELSE 1 END")
       ->orderBy('name') // অথবা যেকোনো column দিয়ে sort করতে চান
       ->get();
           
           // $visitorLocationPath থেকে current location খুঁজে বের করুন
           $selectedCountry = null;
           $selectedCity = null;
           $cities = collect();
           
           if(isset($visitorLocationPath) && $visitorLocationPath) {
               // প্রথমে check করুন এটা country কিনা
               $selectedCountry = App\Models\Country::where('username', $visitorLocationPath)->first();
               
               if($selectedCountry) {
                   // এটা country, তাহলে এর cities load করুন
                   $cities = App\Models\City::where('country_id', $selectedCountry->id)
                                           ->orderBy('name', 'asc')
                                           ->get();
               } else {
                   // না হলে check করুন এটা city কিনা
                   $selectedCity = App\Models\City::where('username', $visitorLocationPath)->first();
                   
                   if($selectedCity) {
                       // City পেলে এর country খুঁজুন
                       $selectedCountry = App\Models\Country::find($selectedCity->country_id);
                       
                       // এই country এর সব cities load করুন
                       if($selectedCountry) {
                           $cities = App\Models\City::where('country_id', $selectedCountry->id)
                                                   ->orderBy('name', 'asc')
                                                   ->get();
                       }
                   }
               }
           }
       @endphp

    <!-- Horizontal Scrollable Navigation -->
    <div class="scroll-container mb-4">
        <div class="scroll-content">

            <button href="" class="nav-item-custom" id="openSidebarBtn">
                <span><i class="bi bi-list"></i></span>
            </button>

            <!--
            
            <button class="nav-item-custom" data-bs-toggle="modal" data-bs-target="#fullModal">
                <span><i class="bi bi-journal-bookmark"></i></span>
            </button> 

            <button class="nav-item-custom" data-bs-toggle="modal" data-bs-target="#locationModal">
                <span><i class="bi bi-geo-alt"></i></span>
                <span>
                    @if($selectedCity)
                        {{ $selectedCity->name }}, {{ $selectedCountry->name ?? '' }}
                    @elseif($selectedCountry)
                        {{ $selectedCountry->name }}
                    @else
                        Select Location
                    @endif
                </span>
            </button>

            -->

            
           
           


            
            @php
    // Determine which categories to show in navigation
    $navCategories = collect();
    
    if(isset($category)) {
        // শুধু post type ধরবে
        if($category->parent_cat_id) {
            // যদি child হয় তাহলে parent এর child গুলো নেবে (যেগুলোতে post আছে)
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
                                ->get();
        } 
        // যদি parent হয় তাহলে এর child নেবে
        else if($category->cat_type == 'post') {
            $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
                                ->get();
        }
    }
@endphp

@php
    // Check if category is set from path parameter or query parameter
    $selectedCategorySlug = isset($category) ? $category->slug : request()->get('category');
@endphp

{{-- All Posts Link --}}
<a href="{{ url('/' . $visitorLocationPath) }}" 
   class="nav-item-custom {{ !$selectedCategorySlug ? 'active' : '' }}">
   <span><i class="bi bi-file-post"></i></span>
   <span>All Posts</span>
</a>

@if($navCategories->count() > 0)
    {{-- Show determined post categories --}}
    @foreach($navCategories as $navCat)
        <a href="{{ url('/' . $visitorLocationPath . '/' . $navCat->slug) }}" 
        class="nav-item-custom {{ $selectedCategorySlug == $navCat->slug ? 'active' : '' }}">
            <span>{{ $navCat->category_name }}</span>
        </a>
    @endforeach
@else
    {{-- Show all parent post categories --}}
    @php
        $parentCategories = \App\Models\Category::where('cat_type', 'post')
                            ->whereNull('parent_cat_id')
                            ->whereHas('posts') // শুধু যেগুলোতে post আছে
                            ->get();
    @endphp

    @foreach($parentCategories as $parentCat)
        @php
            $subCategories = \App\Models\Category::where('parent_cat_id', $parentCat->id)
                                ->where('cat_type', 'post')
                                ->whereHas('posts') // শুধু যেগুলোতে post আছে
                                ->get();
        @endphp
        
        @if($subCategories->count() > 0)
            <div class="dropdown nav-item-custom">
                <a href="#" class="nav-item-custom dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>{{ $parentCat->category_name }}</span>
                </a>
                <ul class="dropdown-menu">
                    @foreach($subCategories as $subCat)
                        <li>
                            <a class="dropdown-item" href="{{ url('/' . $visitorLocationPath . '/' . $subCat->slug) }}">
                                {{ $subCat->category_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            {{-- যদি sub না থাকে তাহলে সরাসরি link --}}
            <a href="{{ url('/' . $visitorLocationPath . '/' . $parentCat->slug) }}" 
               class="nav-item-custom {{ $selectedCategorySlug == $parentCat->slug ? 'active' : '' }}">
                <span>
                    <i class="bi {{ $parentCat->image }}"></i>
                </span>
                <span>{{ $parentCat->category_name }}</span>
            </a>
        @endif
    @endforeach
@endif
        </div>
    </div>



    <div class="row">
        
    @php
        // Fallback: use currently authenticated user and their points
        $currentUser = auth()->user();
        $points = 0;
        if ($currentUser) {
            try {
                $points = \App\Http\Controllers\PointController::get($currentUser->id);
            } catch (\Throwable $e) {
                $points = 0;
            }
        }
    @endphp
    @auth
    @if(Auth::check())
    @php
        // Check mela_ticket data for current user
        $alreadyClaimed = false;
        $ticketData = null;
        if ($currentUser) {
            $ticketData = \Illuminate\Support\Facades\DB::table('mela_ticket')
                ->where('user_id', $currentUser->id)
                ->first();
            $alreadyClaimed = $ticketData ? true : false;
        }
        $isCollectedFromModerator = $alreadyClaimed && $ticketData->moderator_id;
    @endphp

   
        <div class="col-12 mb-2">
            
            <div class="alert alert-info d-flex align-items-center shadow-sm flex-wrap" style="border-radius: 16px; background: linear-gradient(93deg, #e0f7fa 20%, #f1f8e9 100%); border: 1.5px solid #b2ebf2;">
                
                    <div style="color: #04595c; text-align:center;">
                        
                        কুতুবপুরের সোস্যাল মিডিয়া <b>#eINFO App</b> এ আমি আছি! <span style="color:#0abb87;font-weight:bold;">আপনি আছেন তো?</span>
                        <br/>
                        <div class="d-flex justify-content-center mt-2">
                            <a class="btn btn-primary"
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/app')) }}&quote={{ urlencode('কুতুবপুরের সোস্যাল মিডিয়া #eINFO App এ আমি আছি! আপনি আছেন তো?') }}"
                                target="_blank"
                                style="font-size:12px;"
                                onclick="
                                    var ua = navigator.userAgent || navigator.vendor || window.opera;
                                    var isIOS = /iPhone|iPad|iPod/i.test(ua);
                                    var isFbApp = ua.indexOf('FBAN') > -1 || ua.indexOf('FBAV') > -1;

                                    if(isIOS && isFbApp){
                                        // iPhone FB App detected: must let system handle (usually goes to in-app browser, not external apps)
                                        // window.location.href = this.href; // Optionally force
                                        return true; // Let default action happen (in-app)
                                    } else if(isIOS) {
                                        // iPhone Safari (not in FB App)
                                        // Try to open via window.open; fallback to default behavior
                                        window.open(this.href, '_blank');
                                        return false;
                                    } else {
                                        window.open(this.href, 'fbShareWindow', 'height=500,width=650,top=50,left=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no,status=no');
                                        return false;
                                    }
                                ">
                                Facebook এ শেয়ার করে জিতে নিন পুরস্কার
                            </a>
                        </div>
                    </div>
                
            </div>
        </div>
    

   
    @endif
    @endauth
    
    @guest
        <div class="col-12 mb-2">
            <div class="alert alert-info d-flex align-items-center shadow-sm flex-wrap" style="border-radius: 16px; background: linear-gradient(93deg, #e0f7fa 20%, #f1f8e9 100%); border: 1.5px solid #b2ebf2;">
                
                <div style="">
                    
                    <div style="color: #04595c;" style=" cursor:pointer;" data-bs-toggle="modal" data-bs-target="#authModal">
                   
                    <i style=" margin-right: 5px; color: #0abb87;" class="bi bi-gift-fill"></i>
             
                        কুতুবপুরের বাণিজ্য মেলার টিকেট <strong class="text-danger">ফ্রি</strong> পেতে <u>লগইন/রেজিস্টার</u> করুন!
                    </div>
                   
                    
                    
                </div>
            </div>
        </div>
        @endguest 

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

@include('frontend.location')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto scroll active item to center
    const scrollContainer = document.querySelector('.scroll-container');
    const activeItem = document.querySelector('.nav-item-custom.active');
    
    if (scrollContainer && activeItem) {
        scrollToCenter(activeItem, scrollContainer);
    }
    
    // Handle navigation clicks and scroll to center
    document.querySelectorAll('.nav-item-custom').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            document.querySelectorAll('.nav-item-custom').forEach(nav => {
                nav.classList.remove('active');
            });
            
            // Add active class to clicked item (if not a dropdown)
            if (!this.classList.contains('dropdown-toggle')) {
                this.classList.add('active');
                if (scrollContainer) {
                    scrollToCenter(this, scrollContainer);
                }
            }
        });
    });
    
});

function scrollToCenter(element, container) {
    if (!element || !container) return;
    
    const elementRect = element.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();
    
    // Calculate the position to scroll to center the element
    const elementCenter = elementRect.left + elementRect.width / 2;
    const containerCenter = containerRect.left + containerRect.width / 2;
    const scrollOffset = elementCenter - containerCenter;
    
    // Smooth scroll to center
    container.scrollBy({
        left: scrollOffset,
        behavior: 'smooth'
    });
}
</script>

@endsection


