@extends("frontend.master")
@section('main-content')

{{-- Posts Container --}}
<div class="container mt-4">




    {{-- Include Sidebar --}}
    @include('frontend.body.sidebar')

    @include('frontend.phonebook')
    
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
            <!-- <button href="" class="nav-item-custom" id="openSidebarBtn">
                <span><i class="bi bi-list"></i></span>
            </button> -->
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

            
           
           


            
        
            @php
                // Determine which categories to show in navigation
                $navCategories = collect();
                
                if(isset($category)) {
                    // শুধু post type ধরবে
                    if($category->parent_cat_id) {
                        // যদি child হয় তাহলে parent এর child গুলো নেবে
                        $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                            ->where('cat_type', 'post')
                                            ->get();
                    } 
                    // যদি parent হয় তাহলে এর child নেবে
                    else if($category->cat_type == 'post') {
                        $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                                            ->where('cat_type', 'post')
                                            ->get();
                    }
                }
            @endphp

            @if($navCategories->count() > 0)
                {{-- Show determined post categories --}}
                @foreach($navCategories as $navCat)
                    <a href="{{ route('products.category', [$visitorLocationPath, $navCat->slug]) }}" 
                    class="nav-item-custom {{ isset($category) && $category->id == $navCat->id ? 'active' : '' }}">
                        <span>{{ $navCat->category_name }}</span>
                    </a>
                @endforeach
            @else
                {{-- Show all parent post categories --}}
                @php
                    $parentCategories = \App\Models\Category::where('cat_type', 'post')
                                        ->whereNull('parent_cat_id')
                                        ->get();
                @endphp

                @foreach($parentCategories as $parentCat)
                    @php
                        $subCategories = \App\Models\Category::where('parent_cat_id', $parentCat->id)
                                            ->where('cat_type', 'post')
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
                                        <a class="dropdown-item" href="{{ route('products.category', [$visitorLocationPath, $subCat->slug]) }}">
                                            {{ $subCat->category_name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        {{-- যদি sub না থাকে তাহলে সরাসরি link --}}
                        <a href="{{ route('products.category', [$visitorLocationPath, $parentCat->slug]) }}" class="nav-item-custom">
                            <!-- @if($parentCat->image)
                                <img src="{{ asset('icon/' . $parentCat->image) }}" alt="{{ $parentCat->category_name }}" style="width:24px; height:24px; object-fit:contain; margin-right:6px;">
                            @endif -->
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

@endsection


