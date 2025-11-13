<div class="modal fade" id="locationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog"> <!-- 👈 Fullscreen modal -->
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="fullModalLabel">Change Location</h5>
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      </div>
      
      <div class="modal-body">

      

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
   
       <div class="">
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
   
       <div class="">
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


</div>
      
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
   