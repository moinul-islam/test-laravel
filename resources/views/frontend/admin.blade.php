@extends('frontend.master')
@section('main-content')
<div class="py-4 ms-3 me-3">
    <div class="mb-4">
        <a href="/categories" class="btn btn-outline-success">Categories</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>SN</th>                                  
                        <th>Name</th>                                  
                        <th>Job Title</th>
                        <th>Category</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Area</th>
                        <th>Email</th>
                        <th>Verified</th>
                        <th>Phone</th>                      
                        <th>Verified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    @php
                        if ($user->email_verified == 0 || $user->phone_verified == 0) {
                            $rowClass = ''; // যেকোনো একটার মান 0 হলে normal
                        } else {
                            $rowClass = 'table-danger'; // দুইটার কোনোটাই 0 না হলে danger
                        }
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ ($users->total() - ($users->firstItem() - 1)) - $loop->index }}</td>
                        <td>
                            <a href="/{{ $user->username }}">
                                {{ $user->name }} <span class="badge bg-primary">{{ $user->role }}</span>
                            </a>
                            <br>
                            <small style="font-size: 11px;">{!! $user->created_at->timezone('Asia/Dhaka')->format('d M Y') !!} - {!! $user->created_at->timezone('Asia/Dhaka')->format('h:i A') !!}</small>
                        </td>
                        <td>{{ $user->job_title ?? 'N/A' }}</td>
                        <td>{{ $user->category->category_name ?? 'N/A' }}</td>
                        <td>{{ $user->country->name ?? 'N/A' }}</td>
                        <td>{{ $user->city->name ?? 'N/A' }}</td>
                        <td>{{ $user->area ?? 'N/A' }}</td>
                        <td>{{ $user->email ?? 'N/A' }}</td>
                        <td>
                            @if($user->email_verified === 0)
                                0
                            @else
                                {{ $user->email_verified ?? 'N/A' }}
                            @endif
                        </td>
                        <td>{{ $user->phone_number ?? 'N/A' }}</td>
                        <td>
                            @if($user->phone_verified === 0)
                                0
                            @else
                                {{ $user->phone_verified ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editUser({{ $user->id }})">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
       
        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-3">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div id="loadingSpinner" class="text-center" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    
                    <div id="userFormContent" style="display: none;">
                        <!-- Profile Image -->
                        <div class="mb-3 text-center">
                            <img id="userImage" src="" alt="Profile" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                            <div>
                                <input type="file" name="image" class="form-control" accept="image/*" onchange="previewModalImage(this)">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="userName" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" id="userUsername" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="userEmail" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone_number" id="userPhone" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Job Title or Business Category</label>
                                    <div style="position: relative;">
                                        <input type="text" 
                                            name="job_title" 
                                            id="userJobTitle" 
                                            class="form-control"
                                            placeholder="Type to search categories or enter custom job title"
                                            autocomplete="off">
                                        <input type="hidden" id="userCategoryId" name="category_id">
                                        <div id="modalCategorySuggestions" 
                                            style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1050; display: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                    <small class="text-muted">Select from suggestions or type your custom job title</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Category</label>
                                    <input type="text" id="currentCategoryDisplay" class="form-control" readonly style="background-color: #f8f9fa;">
                                    <small class="text-muted">Shows selected category if any</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select name="country_id" id="userCountry" class="form-select" required onchange="loadCities(this.value)">
                                        <option value="">Select Country</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city_id" id="userCity" class="form-select" required>
                                        <option value="">Select City</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Area/Address</label>
                            <input type="text" name="area" id="userArea" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Verified</label>
                                    <select name="email_verified" id="emailVerified" class="form-select">
                                        <option value="">Not Set</option>
                                        <option value="0">No (0)</option>
                                        <option value="1">Yes (1)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Verified</label>
                                    <select name="phone_verified" id="phoneVerified" class="form-select">
                                        <option value="">Not Set</option>
                                        <option value="0">No (0)</option>
                                        <option value="1">Yes (1)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Service Hours -->
                        <div class="mb-3">
                            <label class="form-label">Service Hours</label>
                            <div id="serviceHoursContainer">
                                @php
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                @endphp
                                @foreach($days as $day)
                                <div class="row mb-2">
                                    <div class="col-3">
                                        <label class="form-label small">{{ ucfirst($day) }}</label>
                                    </div>
                                    <div class="col-3">
                                        <select name="service_hr[{{ $day }}][status]" id="status_{{ $day }}" class="form-select form-select-sm" onchange="toggleModalTimeInputs('{{ $day }}')">
                                            <option value="open">Open</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-3" id="{{ $day }}_modal_open_time">
                                        <input type="time" name="service_hr[{{ $day }}][open]" id="open_{{ $day }}" class="form-control form-control-sm" value="09:00">
                                    </div>
                                    <div class="col-3" id="{{ $day }}_modal_close_time">
                                        <input type="time" name="service_hr[{{ $day }}][close]" id="close_{{ $day }}" class="form-control form-control-sm" value="18:00">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let modalCategories = [];

function editUser(userId) {
    currentUserId = userId;
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    
    // Show loading spinner
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('userFormContent').style.display = 'none';
    
    // Show modal
    modal.show();
    
    // Fetch user data
    fetch(`/admin/users/${userId}/data`)
        .then(response => response.json())
        .then(data => {
            // Hide loading spinner
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('userFormContent').style.display = 'block';
            
            // Store categories for searching
            modalCategories = data.categories;
            
            // Update form action
            document.getElementById('editUserForm').action = `/admin/users/${userId}`;
            
            // Populate form fields
            document.getElementById('userName').value = data.user.name || '';
            document.getElementById('userUsername').value = data.user.username || '';
            document.getElementById('userEmail').value = data.user.email || '';
            document.getElementById('userPhone').value = data.user.phone_number || '';
            document.getElementById('userArea').value = data.user.area || '';
            document.getElementById('emailVerified').value = data.user.email_verified !== null ? data.user.email_verified : '';
            document.getElementById('phoneVerified').value = data.user.phone_verified !== null ? data.user.phone_verified : '';
            
            // Handle job title and category
            if (data.user.category_id && data.user.category) {
                document.getElementById('userJobTitle').value = data.user.category.category_name;
                document.getElementById('userCategoryId').value = data.user.category_id;
                document.getElementById('currentCategoryDisplay').value = data.user.category.category_name;
            } else if (data.user.job_title) {
                document.getElementById('userJobTitle').value = data.user.job_title;
                document.getElementById('userCategoryId').value = '';
                document.getElementById('currentCategoryDisplay').value = 'Custom: ' + data.user.job_title;
            } else {
                document.getElementById('userJobTitle').value = '';
                document.getElementById('userCategoryId').value = '';
                document.getElementById('currentCategoryDisplay').value = 'None';
            }
            
            // Set profile image
            const imagePath = data.user.image ? `/profile-image/${data.user.image}` : '/profile-image/default.png';
            document.getElementById('userImage').src = imagePath;
            
            // Populate countries
            const countrySelect = document.getElementById('userCountry');
            countrySelect.innerHTML = '<option value="">Select Country</option>';
            data.countries.forEach(country => {
                countrySelect.innerHTML += `<option value="${country.id}" ${country.id == data.user.country_id ? 'selected' : ''}>${country.name}</option>`;
            });
            
            // Populate cities if country is selected
            if (data.user.country_id) {
                loadCities(data.user.country_id, data.user.city_id);
            }
            
            // Populate service hours
            if (data.user.service_hr) {
                const serviceHours = JSON.parse(data.user.service_hr);
                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                
                days.forEach(day => {
                    if (serviceHours[day]) {
                        if (serviceHours[day] === 'closed') {
                            document.getElementById(`status_${day}`).value = 'closed';
                            document.getElementById(`${day}_modal_open_time`).style.display = 'none';
                            document.getElementById(`${day}_modal_close_time`).style.display = 'none';
                        } else {
                            document.getElementById(`status_${day}`).value = 'open';
                            document.getElementById(`open_${day}`).value = serviceHours[day].open || '09:00';
                            document.getElementById(`close_${day}`).value = serviceHours[day].close || '18:00';
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user data');
        });
}

// Category search functionality
document.addEventListener('DOMContentLoaded', function() {
    const jobTitleInput = document.getElementById('userJobTitle');
    const categoryIdInput = document.getElementById('userCategoryId');
    const suggestionsDiv = document.getElementById('modalCategorySuggestions');
    const currentCategoryDisplay = document.getElementById('currentCategoryDisplay');
    
    if (jobTitleInput) {
        jobTitleInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            
            if (searchValue.length > 0) {
                const filteredCategories = modalCategories.filter(category =>
                    category.category_name.toLowerCase().includes(searchValue.toLowerCase())
                );
                
                if (filteredCategories.length === 0) {
                    suggestionsDiv.innerHTML = '<div style="padding: 10px 15px; color: #6c757d;">No matching categories. This will be saved as custom job title.</div>';
                    suggestionsDiv.style.display = 'block';
                    categoryIdInput.value = '';
                    currentCategoryDisplay.value = 'Custom: ' + searchValue;
                } else {
                    const suggestionsHtml = filteredCategories.map(category => `
                        <div style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;"
                             onmouseover="this.style.backgroundColor='#f8f9fa'"
                             onmouseout="this.style.backgroundColor='white'"
                             onclick="selectModalCategory(${category.id}, '${category.category_name.replace(/'/g, "\\'")}')">
                            ${category.category_name}
                        </div>
                    `).join('');
                    
                    suggestionsDiv.innerHTML = suggestionsHtml;
                    suggestionsDiv.style.display = 'block';
                }
                
                // Check for exact match
                const exactMatch = modalCategories.find(category => 
                    category.category_name.toLowerCase() === searchValue.toLowerCase()
                );
                
                if (exactMatch) {
                    categoryIdInput.value = exactMatch.id;
                    currentCategoryDisplay.value = exactMatch.category_name;
                } else {
                    categoryIdInput.value = '';
                    currentCategoryDisplay.value = searchValue ? 'Custom: ' + searchValue : 'None';
                }
            } else {
                suggestionsDiv.style.display = 'none';
                categoryIdInput.value = '';
                currentCategoryDisplay.value = 'None';
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#userJobTitle') && !e.target.closest('#modalCategorySuggestions')) {
                suggestionsDiv.style.display = 'none';
            }
        });
    }
});

function selectModalCategory(id, name) {
    document.getElementById('userJobTitle').value = name;
    document.getElementById('userCategoryId').value = id;
    document.getElementById('currentCategoryDisplay').value = name;
    document.getElementById('modalCategorySuggestions').style.display = 'none';
}

function loadCities(countryId, selectedCityId = null) {
    if (!countryId) {
        document.getElementById('userCity').innerHTML = '<option value="">Select City</option>';
        return;
    }
    
    fetch(`/get-cities/${countryId}`)
        .then(response => response.json())
        .then(cities => {
            const citySelect = document.getElementById('userCity');
            citySelect.innerHTML = '<option value="">Select City</option>';
            cities.forEach(city => {
                citySelect.innerHTML += `<option value="${city.id}" ${city.id == selectedCityId ? 'selected' : ''}>${city.name}</option>`;
            });
        });
}

function toggleModalTimeInputs(day) {
    const status = document.getElementById(`status_${day}`).value;
    const openTime = document.getElementById(`${day}_modal_open_time`);
    const closeTime = document.getElementById(`${day}_modal_close_time`);
    
    if (status === 'closed') {
        openTime.style.display = 'none';
        closeTime.style.display = 'none';
    } else {
        openTime.style.display = 'block';
        closeTime.style.display = 'block';
    }
}

function previewModalImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('userImage').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Handle form submission
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error updating user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating user');
    });
});
</script>

<!-- Add this in your header/layout file -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection