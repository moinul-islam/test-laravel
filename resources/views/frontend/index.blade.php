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
            <button href="" class="nav-item-custom" data-bs-toggle="modal" data-bs-target="#fullModal">
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
                    // Determine if current category is profile or product/service
                    $isProfile = ($category->cat_type == 'profile');
                    
                    // If category is a child (has parent), show siblings
                    if($category->parent_cat_id) {
                        if($isProfile) {
                            // Show only profile siblings
                            $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                ->where('cat_type', 'profile')
                                ->get();
                        } else {
                            // Show both product AND service siblings
                            $navCategories = \App\Models\Category::where('parent_cat_id', $category->parent_cat_id)
                                ->whereIn('cat_type', ['product', 'service','post'])
                                ->get();
                        }
                    } 
                    // If category is parent (universal), show its children
                    else if($category->cat_type == 'universal') {
                        $navCategories = \App\Models\Category::where('parent_cat_id', $category->id)
                            ->whereIn('cat_type', ['product', 'service', 'profile'])
                            ->get();
                    }
                }
            @endphp
            
            @if($navCategories->count() > 0)
                {{-- Show determined categories --}}
                @foreach($navCategories as $navCat)
                    <a href="{{ route('products.category',[$visitorLocationPath, $navCat->slug]) }}" 
                    class="nav-item-custom {{ isset($category) && $category->id == $navCat->id ? 'active' : '' }}">
                        <span>{{ $navCat->category_name }}</span>
                    </a>
                @endforeach
            @else
                {{-- Show main parent categories as dropdown --}}
                @php
                    $parentCategories = \App\Models\Category::where('cat_type', 'universal')
                                                        ->whereNull('parent_cat_id')
                                                        ->get();
                @endphp
                
                @foreach($parentCategories as $parentCat)
                    @php
                        $subCategories = \App\Models\Category::where('parent_cat_id', $parentCat->id)
                                                        ->whereIn('cat_type', ['product', 'service', 'profile'])
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


