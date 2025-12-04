@extends("frontend.master")
@section('main-content')
<!-- Page Header -->
<div class="container mt-4">
   <div class="">
      @php
      $passwordError = $errors->userDeletion->first('password');
      @endphp
      @if($passwordError)
      <div class="text-danger mt-2 mb-4">
         {{ $passwordError }}
      </div>
      @endif
      <!-- Update Profile Information -->
      <div class="card mb-4">
         <div class="card-body">
            <h5 class="card-title">Update Profile Information</h5>
            @if($errors->any() || session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
               <i class="fas fa-exclamation-triangle me-2"></i>
               <strong>Please fix the following errors:</strong>
               <ul class="mb-0 mt-2">
                  @if(session('error'))
                  <li>{{ session('error') }}</li>
                  @endif
                  @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                  @endforeach
               </ul>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
               @csrf
               
               <div class="mb-3">
                  <!-- Image Preview Container -->
                  <div class="mb-3 d-flex justify-content-center" id="imagePreviewContainer">
                     <img src="{{ asset('profile-image/' . (Auth::user()->image ?? 'default.png')) }}" 
                        alt="Current Profile" 
                        class="rounded-circle" 
                        id="profileImagePreview"
                        style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #dee2e6;">
                  </div>
                  <input type="file" 
                     name="image" 
                     id="image" 
                     class="form-control" 
                     accept="image/*"
                     onchange="previewImage(this)">
                  
                  <!-- Error display -->
                  <div class="text-danger mt-1" id="imageError" style="display: none;"></div>
               </div>
               <script>
                  function previewImage(input) {
                      const file = input.files[0];
                      const preview = document.getElementById('profileImagePreview');
                      const status = document.getElementById('imageStatus');
                      const errorDiv = document.getElementById('imageError');
                      
                      // Clear any previous errors
                      errorDiv.style.display = 'none';
                      errorDiv.textContent = '';
                      
                      if (file) {
                          // Validate file type
                          const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                          if (!validTypes.includes(file.type)) {
                              errorDiv.textContent = 'Please select a valid image file (JPEG, PNG, GIF, WebP)';
                              errorDiv.style.display = 'block';
                              input.value = ''; // Clear the input
                              return;
                          }
                          
                          // Validate file size (2MB limit)
                          const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                          if (file.size > maxSize) {
                              errorDiv.textContent = 'Image size must be less than 2MB';
                              errorDiv.style.display = 'block';
                              input.value = ''; // Clear the input
                              return;
                          }
                          
                          // Create FileReader to preview image
                          const reader = new FileReader();
                          
                          reader.onload = function(e) {
                              preview.src = e.target.result;
                              status.textContent = 'New image selected: ' + file.name;
                              status.classList.remove('text-muted');
                              status.classList.add('text-success');
                              
                              // Add a subtle animation
                              preview.style.transform = 'scale(0.9)';
                              setTimeout(() => {
                                  preview.style.transition = 'transform 0.2s ease';
                                  preview.style.transform = 'scale(1)';
                              }, 50);
                          };
                          
                          reader.onerror = function() {
                              errorDiv.textContent = 'Error reading the image file';
                              errorDiv.style.display = 'block';
                              input.value = ''; // Clear the input
                          };
                          
                          reader.readAsDataURL(file);
                      } else {
                          // Reset to default if no file selected
                          preview.src = 'https://via.placeholder.com/80x80/6c757d/ffffff?text=Default';
                          status.textContent = 'Current profile image';
                          status.classList.remove('text-success');
                          status.classList.add('text-muted');
                      }
                  }
                  
                  // Optional: Add drag and drop functionality
                  const imageInput = document.getElementById('image');
                  const imageContainer = document.getElementById('imagePreviewContainer');
                  
                  imageContainer.addEventListener('dragover', function(e) {
                      e.preventDefault();
                      imageContainer.style.backgroundColor = '#f8f9fa';
                      imageContainer.style.border = '2px dashed #007bff';
                  });
                  
                  imageContainer.addEventListener('dragleave', function(e) {
                      e.preventDefault();
                      imageContainer.style.backgroundColor = '';
                      imageContainer.style.border = '';
                  });
                  
                  imageContainer.addEventListener('drop', function(e) {
                      e.preventDefault();
                      imageContainer.style.backgroundColor = '';
                      imageContainer.style.border = '';
                      
                      const files = e.dataTransfer.files;
                      if (files.length > 0) {
                          imageInput.files = files;
                          previewImage(imageInput);
                      }
                  });
               </script>
               <!-- Name -->
               <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                  @error('name')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               
               
              
               
             
               <!-- Email -->
               <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}">
                  @error('email')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
                  @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                  <div class="mt-2">
                     <p class="text-muted small">
                        Your email address is unverified.
                    <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 text-decoration-underline small">
                    Click here to re-send the verification email.
                    </button>
                    </form>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                    <p class="text-success small mt-1">
                    A new verification link has been sent to your email address.
                    </p>
                    @endif
                    </div>
                    @endif
                    </div>
            


<div class="mb-3">
    <label class="form-label d-block">Address</label>
    <div class="row">
        <!-- Country -->
        <div class="col-6 col-right">
            <select id="country" name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                <option value="">Select Country</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}" {{ old('country_id', auth()->user()->country_id) == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
            @error('country_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- City -->
        <div class="col-6 col-left">
            <select id="city" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                <option value="">Select City</option>
                @if(auth()->user()->city_id && isset($cities))
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id', auth()->user()->city_id) == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('city_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Area -->
        <div class="col-12 col-area">
            <input type="text" name="area_id" id="area_id" class="form-control @error('area_id') is-invalid @enderror"
                   value="{{ old('area_id', auth()->user()->area_id) }}" placeholder="Enter Area">
            @error('area_id')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>



<!-- Username -->
<div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" 
                     name="username" 
                     id="username" 
                     class="form-control @error('username') is-invalid @enderror" 
                     value="{{ old('username', auth()->user()->username) }}" 
                     placeholder="Enter your username (minimum 4 characters)"
                     minlength="4">
                  <!-- <small class="form-text text-muted">Username must be at least 4 characters long and can contain letters, numbers, dashes and underscores.</small> -->
                  @error('username')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>




<style>

.col-right {
    padding-right: 0;
}
.col-right option {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
}
.col-left {
    padding-left: 0;
}

.col-area input {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}


.col-right .select2-container--bootstrap4 .select2-selection {
    border-radius: 0 !important;
    border-top-left-radius: 8px !important;
    border-bottom: 0;
    border-right: 0;
}

/* City select - remove left radius */
.col-left .select2-container--bootstrap4 .select2-selection {
    border-radius: 0 !important;
    border-top-right-radius: 8px !important;
    border-bottom: 0;
}


/* ============ Dark Mode Styles ============ */
[data-bs-theme="dark"] .form-label {
    color: #dee2e6;
}

[data-bs-theme="dark"] .form-select,
[data-bs-theme="dark"] .form-control {
    background-color: #2b3035;
    border-color: #495057;
    color: #dee2e6;
}

[data-bs-theme="dark"] .form-select option {
    background-color: #2b3035;
    color: #dee2e6;
}

[data-bs-theme="dark"] .form-control::placeholder {
    color: #6c757d;
}

[data-bs-theme="dark"] .form-select:focus,
[data-bs-theme="dark"] .form-control:focus {
    background-color: #2b3035;
    border-color: #6c757d;
    color: #dee2e6;
}

/* Select2 dark mode support */
[data-bs-theme="dark"] .select2-container--bootstrap4 .select2-selection {
    background-color: #2b3035;
    border-color: #495057;
    color: #dee2e6;
}

[data-bs-theme="dark"] .select2-container--bootstrap4 .select2-selection__rendered {
    color: #dee2e6;
}

[data-bs-theme="dark"] .select2-container--bootstrap4 .select2-dropdown {
    background-color: #2b3035;
    border-color: #495057;
}

[data-bs-theme="dark"] .select2-container--bootstrap4 .select2-results__option {
    color: #dee2e6;
}

[data-bs-theme="dark"] .select2-container--bootstrap4 .select2-results__option--highlighted {
    background-color: #495057;
}
 
</style>

<!-- Select2 with Bootstrap 5 theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize select2 with Bootstrap theme
        $('#country').select2({
            placeholder: "Select Country",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });
        $('#city').select2({
            placeholder: "Select City",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });

        // When country changes, fetch cities
        $('#country').on('change', function() {
            var country_id = $(this).val();
            $('#city').html('<option value="">Select City</option>');
            $('#city').val(null).trigger('change');
            if(country_id) {
                $.ajax({
                    url: '/get-cities/' + country_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        var cityOptions = '<option value="">Select City</option>';
                        $.each(data, function(key, value) {
                            cityOptions += '<option value="' + value.id + '">' + value.name + '</option>';
                        });
                        $('#city').html(cityOptions);
                        $('#city').val(null).trigger('change');
                    }
                });
            }
        });
    });
</script>



            <button type="submit" class="btn btn-primary">Update Profile</button>
            @if (session('status') === 'profile-updated')
            <span class="text-success ms-2">Profile updated successfully!</span>
            @endif
            </form>
         </div>
      </div>








       <!-- Switch to Business -->
       <div class="card mb-4" id="businessProfile">
         <div class="card-body">
            <h5 class="card-title">Update Business Profile Information</h5>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
               @csrf
               <input type="hidden" name="update_business" value="1">  <!-- এই লাইনটা যোগ করুন -->
              <!-- Job Title or Business Category with Suggestions -->
              <div class="mb-3">
                  <label for="job_title" class="form-label">Job Title or Business Category</label>
                  <div style="position: relative;">
                     <input type="text" 
                        name="job_title" 
                        id="job_title" 
                        class="form-control @error('job_title') is-invalid @enderror" 
                        value="{{ old('job_title', auth()->user()->job_title ?? (auth()->user()->category ? auth()->user()->category->category_name : '')) }}" 
                        placeholder="e.g. Software Engineer, Restaurant Owner, etc."
                        autocomplete="off">
                     <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id', auth()->user()->category_id) }}">
                     <div id="job_suggestions" 
                        style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;">
                     </div>
                  </div>
                  @error('job_title')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               <script>
                  // Job title categories data from backend (only profile type categories)
                  const jobCategories = @json($categories ?? []);
                  const jobTitleInput = document.getElementById('job_title');
                  const categoryIdInput = document.getElementById('category_id');
                  const jobSuggestionsDiv = document.getElementById('job_suggestions');
                  let filteredJobCategories = [];
                  
                  function showJobSuggestions(searchTerm) {
                      if (searchTerm.length === 0) {
                          jobSuggestionsDiv.style.display = 'none';
                          return;
                      }
                  
                      filteredJobCategories = jobCategories.filter(category =>
                          category.category_name.toLowerCase().includes(searchTerm.toLowerCase())
                      );
                  
                      if (filteredJobCategories.length === 0) {
                          jobSuggestionsDiv.innerHTML = '<div style="padding: 10px 15px; color: #6c757d;">No matching categories found. You can enter your custom job title!</div>';
                          jobSuggestionsDiv.style.display = 'block';
                          return;
                      }
                  
                      const suggestionsHtml = filteredJobCategories.map(category => `
                          <div style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;"
                               onclick="selectJobCategory(${category.id}, '${category.category_name}')"
                               onmouseover="this.style.backgroundColor='#f8f9fa'"
                               onmouseout="this.style.backgroundColor='white'">
                              ${category.category_name}
                          </div>
                      `).join('');
                  
                      jobSuggestionsDiv.innerHTML = suggestionsHtml;
                      jobSuggestionsDiv.style.display = 'block';
                  }
                  
                  function selectJobCategory(id, name) {
                      jobTitleInput.value = name;
                      categoryIdInput.value = id;
                      jobSuggestionsDiv.style.display = 'none';
                  }
                  
                  jobTitleInput.addEventListener('input', function() {
                      const searchValue = this.value.trim();
                      
                      if (searchValue.length > 0) {
                          showJobSuggestions(searchValue);
                          
                          // Check if typed value exactly matches any existing category
                          const exactMatch = jobCategories.find(category => 
                              category.category_name.toLowerCase() === searchValue.toLowerCase()
                          );
                          
                          if (exactMatch) {
                              categoryIdInput.value = exactMatch.id; // Set existing category ID
                          } else {
                              categoryIdInput.value = ''; // Clear category_id for custom job title
                          }
                      } else {
                          jobSuggestionsDiv.style.display = 'none';
                          categoryIdInput.value = '';
                      }
                  });
                  
                  // Hide suggestions when clicking outside
                  document.addEventListener('click', function(e) {
                      if (!e.target.closest('#job_title') && !e.target.closest('#job_suggestions')) {
                          jobSuggestionsDiv.style.display = 'none';
                      }
                  });
               </script>

               <!-- Phone Number -->
               <div class="mb-3">
                  <label for="phone_number" class="form-label">Phone Number</label>
                  <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', auth()->user()->phone_number) }}" placeholder="Enter your phone number">
                  @error('phone_number')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>

               <!-- Location -->
               <div class="mb-3">
                  <label for="area" class="form-label">Location</label>
                  <input type="text" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', auth()->user()->area) }}" placeholder="Add location">
                  @error('area')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>


                <!-- Service Hours -->
                <div class="mb-3">
                  <label class="form-label">Service Hours</label>
                  @php
                  $serviceHr = auth()->user()->service_hr ? json_decode(auth()->user()->service_hr, true) : [];
                  $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                  @endphp
                  @foreach($days as $day)
                  <div class="row mb-2">
                     <div class="col-12">
                        <label class="form-label">{{ ucfirst($day) }}</label>
                     </div>
                     <div class="col-4">
                        <select name="service_hr[{{ $day }}][status]" class="form-select time-display" onchange="toggleTimeInputs('{{ $day }}')">
                        <option value="open" {{ isset($serviceHr[$day]) && is_array($serviceHr[$day]) ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ isset($serviceHr[$day]) && $serviceHr[$day] === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                     </div>
                     <div class="col-4" id="{{ $day }}_open_time" style="{{ isset($serviceHr[$day]) && $serviceHr[$day] === 'closed' ? 'display:none' : '' }}">
                        <div class="custom-time-dropdown">
                           <div class="time-display form-control" onclick="toggleTimeDropdown('{{ $day }}_open')">
                              <span id="{{ $day }}_open_display">{{ isset($serviceHr[$day]['open']) ? date('g:i A', strtotime($serviceHr[$day]['open'])) : '9:00 AM' }}</span>
                           </div>
                           <div class="time-dropdown" id="{{ $day }}_open_dropdown">
                              <!-- Time options will be populated by JavaScript -->
                           </div>
                           <input type="hidden" name="service_hr[{{ $day }}][open]" id="{{ $day }}_open_input" value="{{ isset($serviceHr[$day]['open']) ? $serviceHr[$day]['open'] : '09:00' }}">
                        </div>
                     </div>
                     <div class="col-4" id="{{ $day }}_close_time" style="{{ isset($serviceHr[$day]) && $serviceHr[$day] === 'closed' ? 'display:none' : '' }}">
                        <div class="custom-time-dropdown">
                           <div class="time-display form-control" onclick="toggleTimeDropdown('{{ $day }}_close')">
                              <span id="{{ $day }}_close_display">{{ isset($serviceHr[$day]['close']) ? date('g:i A', strtotime($serviceHr[$day]['close'])) : '6:00 PM' }}</span>
                           </div>
                           <div class="time-dropdown" id="{{ $day }}_close_dropdown">
                              <!-- Time options will be populated by JavaScript -->
                           </div>
                           <input type="hidden" name="service_hr[{{ $day }}][close]" id="{{ $day }}_close_input" value="{{ isset($serviceHr[$day]['close']) ? $serviceHr[$day]['close'] : '18:00' }}">
                        </div>
                     </div>
                  </div>
                  @endforeach
               </div>
               <style>
                  .custom-time-dropdown {
                  position: relative;
                  }
                  .time-dropdown {
                  position: absolute;
                  top: 100%;
                  left: 0;
                  right: 0;
                  background: white;
                  border: 1px solid #ced4da;
                  border-radius: 0.375rem;
                  max-height: 200px;
                  overflow-y: auto;
                  z-index: 1000;
                  display: none;
                  }
                  .time-option {
                  padding: 8px 12px;
                  cursor: pointer;
                  border-bottom: 1px solid #f8f9fa;
                  }
                  .time-option:hover {
                  background-color: #e9ecef;
                  }
                  .time-display {
                  font-size: 10px;
                  }
               </style>
               <script>
                  // Generate time options (30 min intervals)
                  function generateTimeOptions() {
                      const times = [];
                      for (let hour = 0; hour < 24; hour++) {
                          for (let min = 0; min < 60; min += 30) {
                              const time24 = `${hour.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`;
                              const hour12 = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
                              const ampm = hour < 12 ? 'AM' : 'PM';
                              const time12 = `${hour12}:${min.toString().padStart(2, '0')} ${ampm}`;
                              times.push({ value: time24, display: time12 });
                          }
                      }
                      return times;
                  }
                  
                  // Populate dropdown with time options
                  function populateTimeDropdown(dropdownId) {
                      const dropdown = document.getElementById(dropdownId);
                      if (!dropdown || dropdown.children.length > 0) return;
                      
                      const times = generateTimeOptions();
                      times.forEach(time => {
                          const option = document.createElement('div');
                          option.className = 'time-option';
                          option.textContent = time.display;
                          option.onclick = () => selectTime(dropdownId, time.value, time.display);
                          dropdown.appendChild(option);
                      });
                  }
                  
                  // Toggle time dropdown
                  function toggleTimeDropdown(fieldId) {
                      const dropdown = document.getElementById(fieldId + '_dropdown');
                      const isVisible = dropdown.style.display === 'block';
                      
                      // Hide all dropdowns
                      document.querySelectorAll('.time-dropdown').forEach(d => d.style.display = 'none');
                      
                      if (!isVisible) {
                          populateTimeDropdown(fieldId + '_dropdown');
                          dropdown.style.display = 'block';
                      }
                  }
                  
                  // Select time from dropdown
                  function selectTime(dropdownId, value, display) {
                      const fieldId = dropdownId.replace('_dropdown', '');
                      document.getElementById(fieldId + '_display').textContent = display;
                      document.getElementById(fieldId + '_input').value = value;
                      document.getElementById(dropdownId).style.display = 'none';
                  }
                  
                  // Toggle time inputs based on status
                  function toggleTimeInputs(day) {
                      const status = document.querySelector(`select[name="service_hr[${day}][status]"]`).value;
                      const openTime = document.getElementById(`${day}_open_time`);
                      const closeTime = document.getElementById(`${day}_close_time`);
                      
                      if (status === 'closed') {
                          openTime.style.display = 'none';
                          closeTime.style.display = 'none';
                      } else {
                          openTime.style.display = 'block';
                          closeTime.style.display = 'block';
                      }
                  }
                  
                  // Close dropdowns when clicking outside
                  document.addEventListener('click', function(e) {
                      if (!e.target.closest('.custom-time-dropdown')) {
                          document.querySelectorAll('.time-dropdown').forEach(d => d.style.display = 'none');
                      }
                  });
               </script>
               <script>
                  function toggleTimeInputs(day) {
                      const status = document.querySelector(`select[name="service_hr[${day}][status]"]`).value;
                      const openTime = document.getElementById(`${day}_open_time`);
                      const closeTime = document.getElementById(`${day}_close_time`);
                      
                      if (status === 'closed') {
                          openTime.style.display = 'none';
                          closeTime.style.display = 'none';
                      } else {
                          openTime.style.display = 'block';
                          closeTime.style.display = 'block';
                      }
                  }
               </script>
               
               
              
               <button type="submit" class="btn btn-success">Update Business Profile</button>
            @if (session('status') === 'profile-updated')
            <span class="text-success ms-2">Profile updated successfully!</span>
            @endif
            </form>
         </div>
      </div>










      <!-- Update Password -->
      <div class="card mb-4">
         <div class="card-body">
            <h5 class="card-title">Update Password</h5>
            <form method="POST" action="{{ route('password.update') }}">
               @csrf
               @method('PUT')
               <!-- Current Password -->
               <div class="mb-3">
                  <label for="current_password" class="form-label">Current Password</label>
                  <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror">
                  @error('current_password')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               <!-- New Password -->
               <div class="mb-3">
                  <label for="password" class="form-label">New Password</label>
                  <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                  @error('password')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               <!-- Confirm Password -->
               <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirm New Password</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                  @error('password_confirmation')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               <button type="submit" class="btn btn-warning">Update Password</button>
               @if (session('status') === 'password-updated')
               <span class="text-success ms-2">Password updated successfully!</span>
               @endif
            </form>
         </div>
      </div>
      <!-- Delete User -->
      <div class="card mb-4 border-danger">
         <div class="card-body">
            @include("profile.partials.delete-user-form")
         </div>
      </div>
   </div>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
          const countrySelect = document.getElementById('country');
          const citySelect = document.getElementById('city');
      
          countrySelect.addEventListener('change', function() {
              const countryId = this.value;
              
              if(countryId) {
                  fetch(`/get-cities/${countryId}`, {
                      method: 'GET',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-Requested-With': 'XMLHttpRequest'
                      }
                  })
                  .then(response => response.json())
                  .then(data => {
                      citySelect.innerHTML = '<option value="">Select City</option>';
                      data.forEach(function(city) {
                          citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                      });
                  })
                  .catch(error => {
                      console.error('Error:', error);
                  });
              } else {
                  citySelect.innerHTML = '<option value="">Select City</option>';
              }
          });
      });
   </script>
</div>
@endsection
