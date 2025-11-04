@extends("frontend.master")
@section('main-content')
<!-- <h1>location : {{ $visitorLocationName }}</h1> -->
<style>
   /* প্রতিটি section এর উপরে offset */
   .grid-section {
   scroll-margin-top: 80px; /* এখানে 80px হচ্ছে header এর height */
   }
   /* Hide on small screens */
   @media (max-width: 991px) {
   .category-hidden-sm {
   display: none !important;
   }
   }
   /* Hide on large screens */
   @media (min-width: 992px) {
   .category-hidden-lg {
   display: none !important;
   }
   }
   /* Ensure flexbox order works */
   .row {
   display: flex;
   flex-wrap: wrap;
   }
   /* Section titles */
   [data-bs-theme="dark"] .section-title {
   color: #dee2e6 !important;
   }
   /* Category card text */
   [data-bs-theme="dark"] .text-center span {
   color: #dee2e6 !important;
   }
   [data-bs-theme="dark"] .text-decoration-none span {
   color: #dee2e6 !important;
   }
   /* See More/Less buttons background */
   [data-bs-theme="dark"] .mx-auto {
   background: #343a40 !important;
   }
   /* See More/Less text */
   [data-bs-theme="dark"] #toggleTextSm,
   [data-bs-theme="dark"] #toggleTextLg,
   [data-bs-theme="dark"] span[id^="prodToggleTextSm"],
   [data-bs-theme="dark"] span[id^="prodToggleTextLg"] {
   color: #adb5bd !important;
   }
   /* Profile tags (badges) */
   [data-bs-theme="dark"] .badge.bg-light {
   background-color: #495057 !important;
   color: #dee2e6 !important;
   border-color: #6c757d !important;
   }
   [data-bs-theme="dark"] .badge.bg-secondary {
   background-color: #6c757d !important;
   color: #fff !important;
   }
   /* Links - prevent turning white */
   [data-bs-theme="dark"] a.text-decoration-none {
   color: inherit;
   }
   /* Image containers in dark mode */
   [data-bs-theme="dark"] .mx-auto.mb-2 {
   background: #343a40 !important;
   border-radius: 8px;
   }
   /* Select dropdowns (if location section is enabled) */
   [data-bs-theme="dark"] .form-select {
   background-color: #2b3035;
   border-color: #495057;
   color: #dee2e6;
   }
   [data-bs-theme="dark"] .form-select option {
   background-color: #2b3035;
   color: #dee2e6;
   }
</style>
<!-- //////////////////////////////////for location start///////////////////////////////// 
   <div class="mt-4">
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
   
       <div class="col-4">
           <div class="mb-3">
               <select class="form-select" id="countrySelect">
                   <option value="">Country</option>
                   @foreach($countries as $country)
                       <option value="{{$country->username}}" data-id="{{$country->id}}"
                           @if($selectedCountry && $selectedCountry->id == $country->id) selected @endif>
                           {{$country->name}}
                       </option>
                   @endforeach
               </select>
           </div>
       </div>
   
       <div class="col-4">
           <div class="mb-3" id="cityContainer">
               <select class="form-select" id="citySelect" @if($cities->count() == 0) disabled @endif>
                   <option value="">City</option>
                   @foreach($cities as $city)
                       <option value="{{$city->username}}" data-id="{{$city->id}}"
                           @if($selectedCity && $selectedCity->id == $city->id) selected @endif>
                           {{$city->name}}
                       </option>
                   @endforeach
               </select>
           </div>
       </div>
   
   </div>
   
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
   <meta name="csrf-token" content="{{ csrf_token() }}">
   
   <script>
       $(document).ready(function () {
           
           // Country select change - Load cities and redirect
           $('#countrySelect').on('change', function () {
               var selectedCountry = $(this).val();
               if (selectedCountry) {
                   const countryId = $(this).find(':selected').data('id');
                   
                   // Prepare location data for saving
                   const locationData = {
                       country_id: countryId,
                       reset_city: true
                   };
                   
                   // Fetch cities for this country
                   $.ajax({
                       url: '/get-cities/' + countryId,
                       method: 'GET',
                       success: function(cities) {
                           // Enable and populate city dropdown
                           $('#citySelect').prop('disabled', false);
                           $('#citySelect').find('option:not(:first)').remove();
                           
                           if (cities && cities.length > 0) {
                               cities.forEach(city => {
                                   $('#citySelect').append(
                                       `<option value="${city.username}" data-id="${city.id}">${city.name}</option>`
                                   );
                               });
                           }
                           
                           // Save location and redirect to country page
                           saveVisitorLocation(locationData, selectedCountry);
                       }
                   });
               } else {
                   $('#citySelect').prop('disabled', true).find('option:not(:first)').remove();
               }
           });
           
           // City select change - Redirect to city
           $('#citySelect').on('change', function () {
               var selectedCity = $(this).val();
               
               if (selectedCity) {
                   const cityId = $(this).find(':selected').data('id');
                   
                   // Prepare location data for saving
                   const locationData = {
                       city_id: cityId
                   };
                   
                   // Save location and redirect to city page
                   saveVisitorLocation(locationData, selectedCity);
               }
           });
       });
   
       // Save visitor location to server and redirect
       function saveVisitorLocation(locationData, redirectTo = null) {
           console.log('Saving location:', locationData, 'Redirect to:', redirectTo);
           
           fetch('/save-location', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   'X-Requested-With': 'XMLHttpRequest'
               },
               body: JSON.stringify(locationData)
           })
           .then(response => {
               console.log('Response status:', response.status);
               return response.json();
           })
           .then(data => {
               console.log('Response data:', data);
               if (data.success) {
                   console.log('Location successfully saved');
                   
                   // Redirect to the selected location
                   if (redirectTo) {
                       window.location.href = '/' + redirectTo;
                   }
               }
           })
           .catch(error => {
               console.error('Error saving location:', error);
               
               // Still redirect even if save fails
               if (redirectTo) {
                   window.location.href = '/' + redirectTo;
               }
           });
       }
   </script>
   </div>
    //////////////////////////////////for location end///////////////////////////////// -->
<div class="mt-4">
   <section class="grid-section mb-4">
      <div class="container">
         <div class="row">
            <div class="col-12">
               <h2 class="section-title fw-bold text-dark text-center mb-2">@t('Categories')</h2>
            </div>
         </div>
         <center class="mb-3">
            <a href="{{ route('discount_wise_product',$visitorLocationPath) }}">@t('Discount & Offers')</a> | 
            <a href="{{ route('notice',$visitorLocationPath) }}">@t('Notice')</a>     
         </center>
         <div class="row g-3 g-md-4">
            @php
            $universalCategories = \App\Models\Category::where('cat_type', 'universal')->where('parent_cat_id', null)->get();
            $totalCategories = $universalCategories->count();
            // Cards per row: 3 for sm, 4 for lg
            $cardsPerRowSm = 4;
            $cardsPerRowLg = 6;
            // Calculate complete rows
            $completeRowsSm = intval($totalCategories / $cardsPerRowSm);
            $completeRowsLg = intval($totalCategories / $cardsPerRowLg);
            // Calculate remaining cards in last row
            $remainingSm = $totalCategories % $cardsPerRowSm;
            $remainingLg = $totalCategories % $cardsPerRowLg;
            // Show "See More" only if the last row is incomplete
            $needSeeMoreSm = $remainingSm > 0;
            $needSeeMoreLg = $remainingLg > 0;
            // Position where "See More" should appear (last card of last complete row)
            $seeMorePositionSm = $needSeeMoreSm ? ($completeRowsSm * $cardsPerRowSm) - 1 : -1;
            $seeMorePositionLg = $needSeeMoreLg ? ($completeRowsLg * $cardsPerRowLg) - 1 : -1;
            @endphp
            @foreach($universalCategories as $index => $category)
            @php
            // Last complete row এর শেষ card এবং incomplete row এর cards hide করতে হবে
            $hiddenOnSm = $needSeeMoreSm && $index > $seeMorePositionSm;
            $hiddenOnLg = $needSeeMoreLg && $index > $seeMorePositionLg;
            // See More button এর জায়গায় last complete row এর শেষ card টাও hide
            if($needSeeMoreSm && $index == $seeMorePositionSm) $hiddenOnSm = true;
            if($needSeeMoreLg && $index == $seeMorePositionLg) $hiddenOnLg = true;
            @endphp
            {{-- Regular category card --}}
            <div class="col-3 col-sm-3 col-lg-2 text-center mb-3 category-card 
               @if($hiddenOnSm) category-hidden-sm @endif 
               @if($hiddenOnLg) category-hidden-lg @endif"
               data-index="{{ $index }}">
               <a href="#{{ $category->slug }}" class="text-decoration-none d-block">
                  <!-- Icon container -->
                  <div class="mx-auto mb-2 border rounded d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; overflow: hidden;">
                     <img src="{{ $category->image ? asset('icon/' . $category->image) : asset('profile-image/no-image.jpeg') }}"
                        alt="{{ $category->category_name }}" 
                        style="width: 80%; height: 80%; object-fit: cover;">
                  </div>
                  <!-- Category Name -->
                  <span class="d-block text-truncate" style="font-size: 12px; color: #111;">
                  @t($category->category_name)
                  </span>
               </a>
            </div>
            {{-- Show "See More" button at the same position --}}
            @if($needSeeMoreSm && $index == $seeMorePositionSm)
            <div class="col-3 col-sm-3 d-lg-none text-center mb-3 toggle-btn-sm" id="toggleBtnSm">
               <a href="javascript:void(0);" class="text-decoration-none" onclick="toggleCategoriesSm()">
                  <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f5f5f5; border-radius: 8px; cursor: pointer;">
                     <span style="font-size: 11px; color: #888;" id="toggleTextSm">See More</span>
                  </div>
               </a>
            </div>
            @endif
            {{-- Show "See More" button for LG --}}
            @if($needSeeMoreLg && $index == $seeMorePositionLg)
            <div class="d-none d-lg-block col-lg-2 text-center mb-3 toggle-btn-lg" id="toggleBtnLg">
               <a href="javascript:void(0);" class="text-decoration-none" onclick="toggleCategoriesLg()">
                  <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f5f5f5; border-radius: 8px; cursor: pointer;">
                     <span style="font-size: 14px; color: #888;" id="toggleTextLg">See More</span>
                  </div>
               </a>
            </div>
            @endif
            @endforeach
         </div>
      </div>
   </section>
   @foreach($universalCategories as $universalCategory)
   @php
   $profileCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->where('cat_type', 'profile')->get();
   $productCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->whereIn('cat_type', ['product', 'service','post'])->get();
   // If profile categories are more than product categories, swap them
   if($profileCategories->count() > $productCategories->count()) {
   $tempProfile = $profileCategories;
   $profileCategories = $productCategories;
   $productCategories = $tempProfile;
   }
   @endphp
   @if($productCategories->count() > 0)
   <section class="grid-section mb-4" id="{{ $universalCategory->slug }}">
      <div class="container">
         <div class="row mb-4 mt-4">
            <div class="col-12 mt-4">
               <div class="d-flex justify-content-between align-items-start flex-wrap border-bottom">
                  <h2 class="section-title fw-bold text-dark text-start">
                     @t($universalCategory->category_name)
                  </h2>
                  @if($profileCategories->count() > 0)
                  @php
                  $maxTagsFirstLine = 2;
                  $showSeeMore = $profileCategories->count() > $maxTagsFirstLine;
                  @endphp
                  <div class="d-flex flex-wrap justify-content-end gap-1" id="profileTags-{{ $universalCategory->id }}">
                     @foreach($profileCategories as $index => $profileCat)
                     <a href="{{ route('products.category', [$visitorLocationPath, $profileCat->slug]) }}" 
                        class="badge rounded bg-light text-dark border px-2 py-1 mb-1 profile-tag-{{ $universalCategory->id }} 
                        @if($showSeeMore && $index >= $maxTagsFirstLine) d-none extra-tag-{{ $universalCategory->id }} @endif"
                        style="font-size: 11px; font-weight: 500; line-height: 1.1;">
                     @t($profileCat->category_name)
                     </a>
                     @endforeach
                     @if($showSeeMore)
                     <a href="javascript:void(0);" 
                        class="badge rounded bg-secondary text-white px-2 py-1 mb-1"
                        id="seeMoreProfileTagsBtn-{{ $universalCategory->id }}"
                        style="font-size: 11px; font-weight: 500; line-height: 1.1;"
                        onclick="showAllProfileTags('{{ $universalCategory->id }}')">
                     See More
                     </a>
                     <a href="javascript:void(0);" 
                        class="badge rounded bg-secondary text-white px-2 py-1 mb-1 d-none"
                        id="seeLessProfileTagsBtn-{{ $universalCategory->id }}"
                        style="font-size: 11px; font-weight: 500; line-height: 1.1;"
                        onclick="showLessProfileTags('{{ $universalCategory->id }}')">
                     See Less
                     </a>
                     @endif
                  </div>
                  @endif
               </div>
            </div>
            <script>
               function showAllProfileTags(sectionId) {
                   document.querySelectorAll('.extra-tag-' + sectionId).forEach(tag => tag.classList.remove('d-none'));
                   document.getElementById('seeMoreProfileTagsBtn-' + sectionId).style.display = 'none';
                   document.getElementById('seeLessProfileTagsBtn-' + sectionId).classList.remove('d-none');
               }
               
               function showLessProfileTags(sectionId) {
                   document.querySelectorAll('.extra-tag-' + sectionId).forEach(tag => tag.classList.add('d-none'));
                   document.getElementById('seeMoreProfileTagsBtn-' + sectionId).style.display = '';
                   document.getElementById('seeLessProfileTagsBtn-' + sectionId).classList.add('d-none');
               }
            </script>
         </div>
         <div class="row g-3 g-md-4">
            @php
            $sectionId = 'section_' . $universalCategory->id;
            $totalProductCats = $productCategories->count();
            // Cards per row: 3 for sm, 4 for lg
            $prodCardsPerRowSm = 4;
            $prodCardsPerRowLg = 6;
            // Calculate complete rows
            $prodCompleteRowsSm = intval($totalProductCats / $prodCardsPerRowSm);
            $prodCompleteRowsLg = intval($totalProductCats / $prodCardsPerRowLg);
            // Calculate remaining cards in last row
            $prodRemainingSm = $totalProductCats % $prodCardsPerRowSm;
            $prodRemainingLg = $totalProductCats % $prodCardsPerRowLg;
            // Show "See More" only if the last row is incomplete
            $prodNeedSeeMoreSm = $prodRemainingSm > 0;
            $prodNeedSeeMoreLg = $prodRemainingLg > 0;
            // Position where "See More" should appear (last card of last complete row)
            $prodSeeMorePositionSm = $prodNeedSeeMoreSm ? ($prodCompleteRowsSm * $prodCardsPerRowSm) - 1 : -1;
            $prodSeeMorePositionLg = $prodNeedSeeMoreLg ? ($prodCompleteRowsLg * $prodCardsPerRowLg) - 1 : -1;
            @endphp
            @foreach($productCategories as $prodIndex => $productCat)
            @php
            // Hide logic - last complete row এর শেষ card এবং incomplete row
            $prodHiddenOnSm = $prodNeedSeeMoreSm && $prodIndex > $prodSeeMorePositionSm;
            $prodHiddenOnLg = $prodNeedSeeMoreLg && $prodIndex > $prodSeeMorePositionLg;
            // See More button এর জায়গায় card টাও hide
            if($prodNeedSeeMoreSm && $prodIndex == $prodSeeMorePositionSm) $prodHiddenOnSm = true;
            if($prodNeedSeeMoreLg && $prodIndex == $prodSeeMorePositionLg) $prodHiddenOnLg = true;
            @endphp
            {{-- Regular product category card --}}
            <div class="col-3 col-sm-3 col-lg-2 text-center mb-3 product-category-card-{{ $sectionId }}
               @if($prodHiddenOnSm) product-hidden-sm-{{ $sectionId }} @endif 
               @if($prodHiddenOnLg) product-hidden-lg-{{ $sectionId }} @endif"
               data-prod-index="{{ $prodIndex }}">
               <a href="{{ route('products.category', [$visitorLocationPath, $productCat->slug]) }}" class="text-decoration-none d-block">
                  <div class="mx-auto mb-2 border rounded d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; overflow: hidden;">
                     <img src="{{ $productCat->image ? asset('icon/' . $productCat->image) : asset('profile-image/no-image.jpeg') }}"
                        alt="{{ $productCat->category_name }}"
                        style="width: 60%; height: 60%; object-fit: cover;">
                  </div>
                  <span class="d-block text-truncate" style="font-size: 12px; color: #111;">
                  @t($productCat->category_name)
                  </span>
               </a>
            </div>
            {{-- Show "See More" button for SM --}}
            @if($prodNeedSeeMoreSm && $prodIndex == $prodSeeMorePositionSm)
            <div class="col-3 col-sm-3 d-lg-none text-center mb-3 prod-toggle-btn-sm-{{ $sectionId }}" id="prodToggleBtnSm{{ $sectionId }}">
               <a href="javascript:void(0);" class="text-decoration-none d-block" onclick="toggleProductCategoriesSm('{{ $sectionId }}', {{ $prodSeeMorePositionSm }})">
                  <div class="mx-auto mb-2 border rounded border-dashed d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; cursor: pointer;">
                     <img src="{{asset('icon/swipe-down.gif')}}" 
                        alt="Toggle" 
                        style="width: 60%; height: 60%; object-fit: cover;">
                  </div>
                  <span class="d-block text-truncate" style="font-size: 12px; color: #888;" id="prodToggleTextSm{{ $sectionId }}">See More</span>
               </a>
            </div>
            @endif
            {{-- Show "See More" button for LG --}}
            @if($prodNeedSeeMoreLg && $prodIndex == $prodSeeMorePositionLg)
            <div class="d-none d-lg-block col-lg-2 text-center mb-3 prod-toggle-btn-lg-{{ $sectionId }}" id="prodToggleBtnLg{{ $sectionId }}">
               <a href="javascript:void(0);" class="text-decoration-none d-block" onclick="toggleProductCategoriesLg('{{ $sectionId }}', {{ $prodSeeMorePositionLg }})">
                  <div class="mx-auto mb-2 border rounded border-dashed d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; cursor: pointer;">
                     <img src="{{asset('icon/swipe-down.gif')}}" 
                        alt="Toggle" 
                        style="width: 60%; height: 60%; object-fit: cover;">
                  </div>
                  <span class="d-block text-truncate" style="font-size: 12px; color: #888;" id="prodToggleTextLg{{ $sectionId }}">See More</span>
               </a>
            </div>
            @endif
            @endforeach
         </div>
      </div>
   </section>
   @endif
   @endforeach
</div>
<style>
   .border-dashed {
   border-style: dashed !important;         /* optional: match Bootstrap rounded */
   }
   /* Dynamic styles for product sections */
   @foreach($universalCategories as $universalCategory)
   @php
   $sectionId = 'section_' . $universalCategory->id;
   @endphp
   /* Hide on small screens for section {{ $sectionId }} */
   @media (max-width: 991px) {
   .product-hidden-sm-{{ $sectionId }} {
   display: none !important;
   }
   }
   /* Hide on large screens for section {{ $sectionId }} */
   @media (min-width: 992px) {
   .product-hidden-lg-{{ $sectionId }} {
   display: none !important;
   }
   }
   @endforeach
</style>
<script>
   // Main categories toggle functions
   let expandedSm = false;
   let expandedLg = false;
   
   function toggleCategoriesSm() {
       expandedSm = !expandedSm;
       const toggleBtn = document.getElementById('toggleBtnSm');
       const toggleText = document.getElementById('toggleTextSm');
       
       if (expandedSm) {
           // Show hidden categories
           document.querySelectorAll('.category-hidden-sm').forEach(function(card) {
               card.classList.remove('category-hidden-sm');
               card.style.display = 'block';
           });
           
           // Change button text to "See Less"
           toggleText.textContent = 'See Less';
           
           // Move button to the end
           const row = toggleBtn.parentElement;
           row.appendChild(toggleBtn);
       } else {
           // Hide categories again
           document.querySelectorAll('.category-card').forEach(function(card) {
               const index = parseInt(card.getAttribute('data-index'));
               if (index >= {{ $seeMorePositionSm ?? -1 }}) {
                   card.classList.add('category-hidden-sm');
               }
           });
           
           // Change button text back to "See More"
           toggleText.textContent = 'See More';
           
           // Move button back to original position
           const row = toggleBtn.parentElement;
           const cards = row.querySelectorAll('.category-card');
           let insertPosition = null;
           
           cards.forEach(function(card) {
               const index = parseInt(card.getAttribute('data-index'));
               if (index === {{ $seeMorePositionSm ?? -1 }}) {
                   insertPosition = card;
               }
           });
           
           if (insertPosition && insertPosition.nextSibling) {
               row.insertBefore(toggleBtn, insertPosition.nextSibling);
           }
       }
   }
   
   function toggleCategoriesLg() {
       expandedLg = !expandedLg;
       const toggleBtn = document.getElementById('toggleBtnLg');
       const toggleText = document.getElementById('toggleTextLg');
       
       if (expandedLg) {
           // Show hidden categories
           document.querySelectorAll('.category-hidden-lg').forEach(function(card) {
               card.classList.remove('category-hidden-lg');
               card.style.display = 'block';
           });
           
           // Change button text to "See Less"
           toggleText.textContent = 'See Less';
           
           // Move button to the end
           const row = toggleBtn.parentElement;
           row.appendChild(toggleBtn);
       } else {
           // Hide categories again
           document.querySelectorAll('.category-card').forEach(function(card) {
               const index = parseInt(card.getAttribute('data-index'));
               if (index >= {{ $seeMorePositionLg ?? -1 }}) {
                   card.classList.add('category-hidden-lg');
               }
           });
           
           // Change button text back to "See More"
           toggleText.textContent = 'See More';
           
           // Move button back to original position
           const row = toggleBtn.parentElement;
           const cards = row.querySelectorAll('.category-card');
           let insertPosition = null;
           
           cards.forEach(function(card) {
               const index = parseInt(card.getAttribute('data-index'));
               if (index === {{ $seeMorePositionLg ?? -1 }}) {
                   insertPosition = card;
               }
           });
           
           if (insertPosition && insertPosition.nextSibling) {
               row.insertBefore(toggleBtn, insertPosition.nextSibling);
           }
       }
   }
   
   // Product categories toggle functions
   let productExpandedSm = {};
   let productExpandedLg = {};
   
   function toggleProductCategoriesSm(sectionId, seeMorePosition) {
       productExpandedSm[sectionId] = !productExpandedSm[sectionId];
       const toggleBtn = document.getElementById('prodToggleBtnSm' + sectionId);
       const toggleText = document.getElementById('prodToggleTextSm' + sectionId);
       
       if (productExpandedSm[sectionId]) {
           // Show hidden categories
           document.querySelectorAll('.product-hidden-sm-' + sectionId).forEach(function(card) {
               card.classList.remove('product-hidden-sm-' + sectionId);
               card.style.display = 'block';
           });
           
           // Change button text to "See Less"
           toggleText.textContent = 'See Less';
           
           // Move button to the end
           const row = toggleBtn.parentElement;
           row.appendChild(toggleBtn);
       } else {
           // Hide categories again
           document.querySelectorAll('.product-category-card-' + sectionId).forEach(function(card) {
               const index = parseInt(card.getAttribute('data-prod-index'));
               if (index >= seeMorePosition) {
                   card.classList.add('product-hidden-sm-' + sectionId);
               }
           });
           
           // Change button text back to "See More"
           toggleText.textContent = 'See More';
           
           // Move button back to original position
           const row = toggleBtn.parentElement;
           const cards = row.querySelectorAll('.product-category-card-' + sectionId);
           let insertPosition = null;
           
           cards.forEach(function(card) {
               const index = parseInt(card.getAttribute('data-prod-index'));
               if (index === seeMorePosition) {
                   insertPosition = card;
               }
           });
           
           if (insertPosition && insertPosition.nextSibling) {
               row.insertBefore(toggleBtn, insertPosition.nextSibling);
           }
       }
   }
   
   function toggleProductCategoriesLg(sectionId, seeMorePosition) {
       productExpandedLg[sectionId] = !productExpandedLg[sectionId];
       const toggleBtn = document.getElementById('prodToggleBtnLg' + sectionId);
       const toggleText = document.getElementById('prodToggleTextLg' + sectionId);
       
       if (productExpandedLg[sectionId]) {
           // Show hidden categories
           document.querySelectorAll('.product-hidden-lg-' + sectionId).forEach(function(card) {
               card.classList.remove('product-hidden-lg-' + sectionId);
               card.style.display = 'block';
           });
           
           // Change button text to "See Less"
           toggleText.textContent = 'See Less';
           
           // Move button to the end
           const row = toggleBtn.parentElement;
           row.appendChild(toggleBtn);
       } else {
           // Hide categories again
           document.querySelectorAll('.product-category-card-' + sectionId).forEach(function(card) {
               const index = parseInt(card.getAttribute('data-prod-index'));
               if (index >= seeMorePosition) {
                   card.classList.add('product-hidden-lg-' + sectionId);
               }
           });
           
           // Change button text back to "See More"
           toggleText.textContent = 'See More';
           
           // Move button back to original position
           const row = toggleBtn.parentElement;
           const cards = row.querySelectorAll('.product-category-card-' + sectionId);
           let insertPosition = null;
           
           cards.forEach(function(card) {
               const index = parseInt(card.getAttribute('data-prod-index'));
               if (index === seeMorePosition) {
                   insertPosition = card;
               }
           });
           
           if (insertPosition && insertPosition.nextSibling) {
               row.insertBefore(toggleBtn, insertPosition.nextSibling);
           }
       }
   }
</script>
@endsection
