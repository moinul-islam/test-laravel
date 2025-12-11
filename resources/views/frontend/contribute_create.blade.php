@extends("frontend.master")
@section('main-content')
<!-- Page Header -->
<div class="container mt-4">
@include('frontend.body.admin-nav')
   <div class="">
      @php
      $passwordError = $errors->userDeletion->first('password');
      @endphp
      @if($passwordError)
      <div class="text-danger mt-2 mb-4">
         {{ $passwordError }}
      </div>
      @endif
      <!-- Create New User Account -->
      <div class="card mb-4">
         <div class="card-body">
            <h5 class="card-title">Create New User Account</h5>
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
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
               <i class="fas fa-check-circle me-2"></i>
               {{ session('success') }}
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <form method="POST" action="{{ route('contribute.store') }}" enctype="multipart/form-data">
               @csrf
               
               <input type="hidden" name="contributor" value="{{ Auth::id() }}">
               
               <div class="mb-3">
                  <!-- Image Preview Container -->
                  <div class="mb-3 d-flex justify-content-center" id="imagePreviewContainer">
                     <img src="{{ asset('profile-image/default.png') }}" 
                        alt="Profile Preview" 
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
                      const errorDiv = document.getElementById('imageError');
                      
                      errorDiv.style.display = 'none';
                      errorDiv.textContent = '';
                      
                      if (file) {
                          const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                          if (!validTypes.includes(file.type)) {
                              errorDiv.textContent = 'Please select a valid image file (JPEG, PNG, GIF, WebP)';
                              errorDiv.style.display = 'block';
                              input.value = '';
                              return;
                          }
                          
                          const maxSize = 2 * 1024 * 1024;
                          if (file.size > maxSize) {
                              errorDiv.textContent = 'Image size must be less than 2MB';
                              errorDiv.style.display = 'block';
                              input.value = '';
                              return;
                          }
                          
                          const reader = new FileReader();
                          
                          reader.onload = function(e) {
                              preview.src = e.target.result;
                              preview.style.transform = 'scale(0.9)';
                              setTimeout(() => {
                                  preview.style.transition = 'transform 0.2s ease';
                                  preview.style.transform = 'scale(1)';
                              }, 50);
                          };
                          
                          reader.onerror = function() {
                              errorDiv.textContent = 'Error reading the image file';
                              errorDiv.style.display = 'block';
                              input.value = '';
                          };
                          
                          reader.readAsDataURL(file);
                      }
                  }
               </script>
               
               <!-- Name -->
               <div class="mb-3">
                  <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Enter full name">
                  @error('name')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               <!-- Phone Number -->
               <div class="mb-3">
                  <label for="phone_number" class="form-label">Phone Number</label>
                  <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}" placeholder="Enter phone number">
                  @error('phone_number')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
              
               <div class="mb-3">
                   <label class="form-label">Address <span class="text-danger">*</span></label>
                   <div class="row">
                       <!-- Country -->
                       <div class="col-6 col-right">
                           <select id="country" name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                               <option value="">Select Country</option>
                               @foreach($countries as $country)
                                   <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
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
                           <select id="city" name="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
                               <option value="">Select City</option>
                           </select>
                           @error('city_id')
                               <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                       </div>

                       <!-- Area -->
                       <div class="col-12 col-area">
                           <input type="text" name="area" id="area" class="form-control @error('area') is-invalid @enderror"
                               value="{{ old('area') }}" placeholder="Enter area/locality">
                           @error('area')
                               <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                       </div>
                   </div>
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

.col-left .select2-container--bootstrap4 .select2-selection {
    border-radius: 0 !important;
    border-top-right-radius: 8px !important;
    border-bottom: 0;
}

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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
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

               <input type="hidden" name="create_business" value="1">
               
              <!-- Job Title or Business Category -->
              <div class="mb-3">
                  <label for="job_title" class="form-label">Job Title or Business Category</label>
                  <div style="position: relative;">
                     <input type="text" 
                        name="job_title" 
                        id="job_title" 
                        class="form-control @error('job_title') is-invalid @enderror" 
                        value="{{ old('job_title') }}" 
                        placeholder="e.g. Software Engineer, Restaurant Owner, etc."
                        autocomplete="off">
                     <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id') }}">
                     <div id="job_suggestions" 
                        style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;">
                     </div>
                  </div>
                  @error('job_title')
                  <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>
               
               <script>
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
                          
                          const exactMatch = jobCategories.find(category => 
                              category.category_name.toLowerCase() === searchValue.toLowerCase()
                          );
                          
                          if (exactMatch) {
                              categoryIdInput.value = exactMatch.id;
                          } else {
                              categoryIdInput.value = '';
                          }
                      } else {
                          jobSuggestionsDiv.style.display = 'none';
                          categoryIdInput.value = '';
                      }
                  });
                  
                  document.addEventListener('click', function(e) {
                      if (!e.target.closest('#job_title') && !e.target.closest('#job_suggestions')) {
                          jobSuggestionsDiv.style.display = 'none';
                      }
                  });
               </script>

                <!-- Service Hours -->
                <div class="mb-3">
                  <label class="form-label">Service Hours</label>
                  @php
                  $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                  @endphp
                  @foreach($days as $day)
                  <div class="row mb-2">
                     <div class="col-12">
                        <label class="form-label">{{ ucfirst($day) }}</label>
                     </div>
                     <div class="col-4">
                        <select name="service_hr[{{ $day }}][status]" class="form-select time-display" onchange="toggleTimeInputs('{{ $day }}')">
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        </select>
                     </div>
                     <div class="col-4" id="{{ $day }}_open_time">
                        <div class="custom-time-dropdown">
                           <div class="time-display form-control" onclick="toggleTimeDropdown('{{ $day }}_open')">
                              <span id="{{ $day }}_open_display">9:00 AM</span>
                           </div>
                           <div class="time-dropdown" id="{{ $day }}_open_dropdown"></div>
                           <input type="hidden" name="service_hr[{{ $day }}][open]" id="{{ $day }}_open_input" value="09:00">
                        </div>
                     </div>
                     <div class="col-4" id="{{ $day }}_close_time">
                        <div class="custom-time-dropdown">
                           <div class="time-display form-control" onclick="toggleTimeDropdown('{{ $day }}_close')">
                              <span id="{{ $day }}_close_display">6:00 PM</span>
                           </div>
                           <div class="time-dropdown" id="{{ $day }}_close_dropdown"></div>
                           <input type="hidden" name="service_hr[{{ $day }}][close]" id="{{ $day }}_close_input" value="18:00">
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
                  cursor: pointer;
                  }
               </style>
               
               <script>
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
                  
                  function toggleTimeDropdown(fieldId) {
                      const dropdown = document.getElementById(fieldId + '_dropdown');
                      const isVisible = dropdown.style.display === 'block';
                      
                      document.querySelectorAll('.time-dropdown').forEach(d => d.style.display = 'none');
                      
                      if (!isVisible) {
                          populateTimeDropdown(fieldId + '_dropdown');
                          dropdown.style.display = 'block';
                      }
                  }
                  
                  function selectTime(dropdownId, value, display) {
                      const fieldId = dropdownId.replace('_dropdown', '');
                      document.getElementById(fieldId + '_display').textContent = display;
                      document.getElementById(fieldId + '_input').value = value;
                      document.getElementById(dropdownId).style.display = 'none';
                  }
                  
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
                  
                  document.addEventListener('click', function(e) {
                      if (!e.target.closest('.custom-time-dropdown')) {
                          document.querySelectorAll('.time-dropdown').forEach(d => d.style.display = 'none');
                      }
                  });
               </script>
               
               <button type="submit" class="btn btn-success">Create User Account</button>
            </form>
         </div>
      </div>
   </div>
</div>
@endsection