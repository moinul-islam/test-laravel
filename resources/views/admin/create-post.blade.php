@extends("frontend.master")

@section('main-content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Admin: Create Post for User
                    </h4>
                </div>
                
                <div class="card-body p-4">
                    {{-- Success Message --}}
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    {{-- Error Message --}}
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.post.store') }}" method="POST" id="adminPostForm">
                        @csrf

                        {{-- User Selection --}}
                        <div class="mb-4">
                            <label for="user_search" class="form-label fw-bold">
                                Select User <span class="text-danger">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="text" 
                                    class="form-control form-control-lg" 
                                    id="user_search" 
                                    placeholder="Type user name to search..."
                                    autocomplete="off"
                                    required>
                                <input type="hidden" id="user_id" name="user_id" value="">
                                
                                <div id="user_suggestions" 
                                    style="position: absolute; top: 100%; left: 0; right: 0; 
                                           background: white; border: 1px solid #ddd; 
                                           max-height: 250px; overflow-y: auto; z-index: 1000; 
                                           display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                            @error('user_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            
                            {{-- Selected User Display --}}
                            <div id="selected_user_display" style="display: none;" class="mt-3">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="bi bi-person-check-fill me-2"></i>
                                    <strong>Selected User:</strong>
                                    <span id="selected_user_name" class="ms-2"></span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" 
                                            onclick="clearUserSelection()">
                                        Change User
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Category Selection --}}
                        <div class="mb-4">
                            <label for="category_name" class="form-label fw-bold">
                                Post Category <span class="text-danger">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="text" 
                                    class="form-control" 
                                    id="category_name" 
                                    name="category_name" 
                                    placeholder="Type to search categories..."
                                    autocomplete="off"
                                    required>
                                <input type="hidden" id="category_id" name="category_id" value="">
                                
                                <div id="category_suggestions" 
                                    style="position: absolute; top: 100%; left: 0; right: 0; 
                                           background: white; border: 1px solid #ddd; 
                                           max-height: 200px; overflow-y: auto; z-index: 1000; 
                                           display: none;">
                                </div>
                            </div>
                            @error('category_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Title Field --}}
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                placeholder="Enter product/service title..." 
                                value="{{ old('title') }}" 
                                required>
                            @error('title')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Price Field --}}
                        <div class="mb-4">
                            <label for="price" class="form-label fw-bold">
                                Price <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                class="form-control" 
                                id="price" 
                                name="price" 
                                placeholder="Enter price..." 
                                value="{{ old('price') }}" 
                                min="0" 
                                step="0.01" 
                                required>
                            @error('price')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Image Upload --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" 
                                name="photo" 
                                class="form-control" 
                                id="adminFormFile"
                                accept="image/*">
                            <input type="hidden" name="image_data" id="adminImageData">
                            
                            @error('photo')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                            
                            <div id="adminImageProcessingStatus" style="display: none;" class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 0%" 
                                         id="adminImageProgress"></div>
                                </div>
                                <small id="adminImageStatusText">Image processing...</small>
                            </div>

                            {{-- Image Preview --}}
                            <div class="mt-3">
                                <img id="adminImagePreview" 
                                     src="" 
                                     alt="Preview" 
                                     style="max-width: 200px; display: none; border-radius: 8px; border: 2px solid #ddd;">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Product or Service Description
                            </label>
                            <textarea class="form-control" 
                                id="description" 
                                name="description" 
                                rows="5" 
                                placeholder="Type your text here...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" 
                                class="btn btn-primary btn-lg" 
                                id="adminSubmitBtn" 
                                disabled>
                                <i class="bi bi-check-circle"></i> Create Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
<script>
// Users and Categories Data
const users = @json($users);
const categories = @json($categories);

// User Search Functionality
const userSearchInput = document.getElementById('user_search');
const userIdInput = document.getElementById('user_id');
const userSuggestionsDiv = document.getElementById('user_suggestions');
const selectedUserDisplay = document.getElementById('selected_user_display');
const selectedUserName = document.getElementById('selected_user_name');

userSearchInput.addEventListener('input', function() {
    const searchValue = this.value.trim().toLowerCase();
    
    if (searchValue.length > 0) {
        const filteredUsers = users.filter(user =>
            user.name.toLowerCase().includes(searchValue) ||
            (user.email && user.email.toLowerCase().includes(searchValue))
        );

        if (filteredUsers.length === 0) {
            userSuggestionsDiv.innerHTML = '<div style="padding: 10px 15px; color: #6c757d;">No users found</div>';
            userSuggestionsDiv.style.display = 'block';
            return;
        }

        const suggestionsHtml = filteredUsers.map(user => `
            <div style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;"
                 onclick="selectUser(${user.id}, '${user.name}')"
                 onmouseover="this.style.backgroundColor='#f8f9fa'"
                 onmouseout="this.style.backgroundColor='white'">
                <strong>${user.name}</strong>
                ${user.email ? `<br><small class="text-muted">${user.email}</small>` : ''}
            </div>
        `).join('');

        userSuggestionsDiv.innerHTML = suggestionsHtml;
        userSuggestionsDiv.style.display = 'block';
    } else {
        userSuggestionsDiv.style.display = 'none';
        userIdInput.value = '';
        selectedUserDisplay.style.display = 'none';
    }
    
    toggleSubmit();
});

function selectUser(id, name) {
    userIdInput.value = id;
    userSearchInput.value = name;
    selectedUserName.textContent = name;
    selectedUserDisplay.style.display = 'block';
    userSuggestionsDiv.style.display = 'none';
    toggleSubmit();
}

function clearUserSelection() {
    userSearchInput.value = '';
    userIdInput.value = '';
    selectedUserDisplay.style.display = 'none';
    userSearchInput.focus();
    toggleSubmit();
}

// Category Search Functionality
const categoryInput = document.getElementById('category_name');
const categoryIdInput = document.getElementById('category_id');
const categorySuggestionsDiv = document.getElementById('category_suggestions');

categoryInput.addEventListener('input', function() {
    const searchValue = this.value.trim();
    
    if (searchValue.length > 0) {
        const filteredCategories = categories.filter(category =>
            category.category_name.toLowerCase().includes(searchValue.toLowerCase())
        );

        if (filteredCategories.length === 0) {
            categorySuggestionsDiv.innerHTML = '<div style="padding: 10px 15px; color: #6c757d;">No matching categories. You can create new!</div>';
            categorySuggestionsDiv.style.display = 'block';
            categoryIdInput.value = '';
            toggleSubmit();
            return;
        }

        const suggestionsHtml = filteredCategories.map(category => `
            <div style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0;"
                 onclick="selectCategory(${category.id}, '${category.category_name}')"
                 onmouseover="this.style.backgroundColor='#f8f9fa'"
                 onmouseout="this.style.backgroundColor='white'">
                ${category.category_name} <small style="color: #6c757d;">(${category.cat_type})</small>
            </div>
        `).join('');

        categorySuggestionsDiv.innerHTML = suggestionsHtml;
        categorySuggestionsDiv.style.display = 'block';
        
        const exactMatch = categories.find(category => 
            category.category_name.toLowerCase() === searchValue.toLowerCase()
        );
        
        categoryIdInput.value = exactMatch ? exactMatch.id : '';
    } else {
        categorySuggestionsDiv.style.display = 'none';
        categoryIdInput.value = '';
    }
    
    toggleSubmit();
});

function selectCategory(id, name) {
    categoryInput.value = name;
    categoryIdInput.value = id;
    categorySuggestionsDiv.style.display = 'none';
    toggleSubmit();
}

// Hide suggestions on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#user_search') && !e.target.closest('#user_suggestions')) {
        userSuggestionsDiv.style.display = 'none';
    }
    if (!e.target.closest('#category_name') && !e.target.closest('#category_suggestions')) {
        categorySuggestionsDiv.style.display = 'none';
    }
});

// Form Validation
function toggleSubmit() {
    const titleInput = document.getElementById('title');
    const priceInput = document.getElementById('price');
    const submitBtn = document.getElementById('adminSubmitBtn');
    
    const hasRequiredFields = userIdInput.value !== '' && 
                            categoryInput.value.trim() !== '' &&
                            titleInput.value.trim() !== '' && 
                            priceInput.value.trim() !== '';
    
    submitBtn.disabled = !hasRequiredFields;
}

// Event listeners
document.getElementById('title').addEventListener('input', toggleSubmit);
document.getElementById('price').addEventListener('input', toggleSubmit);

// Image Processing (Reuse your existing function)
document.addEventListener('DOMContentLoaded', function() {
    setupImageProcessing('adminFormFile', 'adminImageData', 'adminImageProcessingStatus', 
                        'adminImageProgress', 'adminImageStatusText', 'adminImagePreview');
});

// Reuse your image processing function
function setupImageProcessing(inputId, dataInputId, statusId, progressId, statusTextId, previewId) {
    // আপনার existing image processing code এখানে copy করুন
    // (আপনার document এ যা আছে সেটাই ব্যবহার করুন)
}
</script>

<style>
[data-bs-theme="dark"] #user_suggestions,
[data-bs-theme="dark"] #category_suggestions {
    background: #343a40;
    border-color: #495057;
}

[data-bs-theme="dark"] #user_suggestions div:hover,
[data-bs-theme="dark"] #category_suggestions div:hover {
    background-color: #495057 !important;
}
</style>
@endsection