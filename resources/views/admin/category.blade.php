@extends('frontend.master')
@section('main-content')

<div class="py-4 container-fluid">
    
@include('frontend.body.admin-nav')
    <div class="row">
        <!-- Add/Edit Category Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="formTitle">Add New Category</h5>
                </div>
                <div class="card-body">
                    <form id="categoryForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="categoryId" name="category_id">
                        <input type="hidden" id="formMethod" name="_method" value="POST">
                        
                        <!-- Category Name -->
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('category_name') is-invalid @enderror" 
                                   id="category_name" name="category_name" value="{{ old('category_name') }}" required>
                            @error('category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug') }}" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Parent Category -->
                        <div class="mb-3">
                            <label for="parent_cat_id" class="form-label">Parent Category</label>
                            <select class="form-select @error('parent_cat_id') is-invalid @enderror" id="parent_cat_id" name="parent_cat_id">
                                <option value="">-- Select Parent Category --</option>
                                @php
                                    function renderCategoryOptions($categories, $parentId = null, $prefix = '') {
                                        foreach($categories as $category) {
                                            if($category->parent_cat_id == $parentId) {
                                                echo '<option value="'.$category->id.'">'.$prefix.$category->category_name.'</option>';
                                                renderCategoryOptions($categories, $category->id, $prefix.'&nbsp;&nbsp;&nbsp;&nbsp;');
                                            }
                                        }
                                    }
                                @endphp
                                @php renderCategoryOptions($categories); @endphp
                            </select>
                            @error('parent_cat_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Type -->
                        <div class="mb-3">
                            <label for="cat_type" class="form-label">Category Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('cat_type') is-invalid @enderror" id="cat_type" name="cat_type" required>
                                <option value="">-- Select Category Type --</option>
                                <option value="universal">Universal</option>
                                <option value="profile">Profile</option>
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                            @error('cat_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Category Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div class="mb-3" id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="ri-add-line me-1"></i> Add Category
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;" onclick="resetForm()">
                                <i class="ri-close-line me-1"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Categories List</h5>
                    <!-- Filter Dropdown & Search Bar -->
                    <div class="d-flex gap-2 align-items-center">
                        <input type="text" class="form-control form-control-sm" id="searchCategory" placeholder="Search category..." style="width: 200px;">
                        <select class="form-select form-select-sm" id="filterCategory" style="width: auto;">
                            <option value="">All Categories</option>
                            @php renderCategoryOptions($categories); @endphp
                        </select>
                        <select class="form-select form-select-sm" id="filterType" style="width: auto;">
                            <option value="">All Types</option>
                            <option value="universal">Universal</option>
                            <option value="profile">Profile</option>
                            <option value="product">Product</option>
                            <option value="service">Service</option>
                        </select>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('searchCategory');
                        const table = document.getElementById('categoriesTable');
                        if(searchInput && table) {
                            searchInput.addEventListener('input', function() {
                                const filter = this.value.toLowerCase();
                                const rows = table.querySelectorAll('tbody tr');
                                rows.forEach(row => {
                                    const nameCell = row.querySelector('td:nth-child(2)');
                                    if(nameCell) {
                                        const text = nameCell.textContent.toLowerCase();
                                        row.style.display = text.includes(filter) ? '' : 'none';
                                    }
                                });
                            });
                        }
                    });
                </script>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Category Name</th>
                                    <th>Type</th>
                                    <th>Parent</th>
                                    <th>Subcategories</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    function renderCategoryRow($categories, $parentId = null, $level = 0) {
                                        foreach($categories as $category) {
                                            if($category->parent_cat_id == $parentId) {
                                                $indentStyle = $level > 0 ? 'padding-left: ' . ($level * 20) . 'px;' : '';
                                                echo '<tr data-parent-id="'.$category->parent_cat_id.'" data-category-type="'.$category->cat_type.'">';
                                                
                                                // Image column
                                                echo '<td>';
                                                if($category->image) {
                                                    echo '<img src="'.asset('icon/' . $category->image).'" alt="'.$category->category_name.'" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">';
                                                } else {
                                                    echo '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="ri-image-line text-muted"></i></div>';
                                                }
                                                echo '</td>';
                                                
                                                // Category name column with indentation
                                                echo '<td style="'.$indentStyle.'">';
                                                if($level > 0) {
                                                    echo str_repeat('└─ ', 1);
                                                }
                                                echo '<strong>'.$category->category_name.'</strong><br><small class="text-muted">'.$category->slug.'</small>';
                                                echo '</td>';
                                                
                                                // Type column
                                                echo '<td>';
                                                $badgeClass = '';
                                                switch($category->cat_type) {
                                                    case 'universal': $badgeClass = 'bg-primary'; break;
                                                    case 'profile': $badgeClass = 'bg-success'; break;
                                                    case 'product': $badgeClass = 'bg-warning'; break;
                                                    case 'service': $badgeClass = 'bg-info'; break;
                                                }
                                                echo '<span class="badge '.$badgeClass.'">'.ucfirst($category->cat_type).'</span>';
                                                echo '</td>';
                                                
                                                // Parent column
                                                echo '<td>';
                                                if($category->parent) {
                                                    echo '<span class="text-muted">'.$category->parent->category_name.'</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                echo '</td>';
                                                
                                                // Subcategories count
                                                echo '<td>';
                                                if($category->children->count() > 0) {
                                                    echo '<span class="badge bg-secondary">'.$category->children->count().'</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                echo '</td>';
                                                
                                                // Action buttons
                                                echo '<td>';
                                                echo '<div class="btn-group btn-group-sm">';
                                                echo '<button type="button" class="btn btn-outline-primary" onclick="editCategory('.$category->id.', \''.$category->category_name.'\', \''.$category->slug.'\', '.($category->parent_cat_id ?? 'null').', \''.$category->cat_type.'\', \''.$category->image.'\')">';
                                                echo '<i class="bi bi-pencil"></i>';
                                                echo '</button>';
                                                echo '<button type="button" class="btn btn-outline-danger" onclick="deleteCategory('.$category->id.')">';
                                                echo '<i class="bi bi-trash"></i>';
                                                echo '</button>';
                                                echo '</div>';
                                                echo '</td>';
                                                
                                                echo '</tr>';
                                                
                                                // Recursively render children
                                                renderCategoryRow($categories, $category->id, $level + 1);
                                            }
                                        }
                                    }
                                @endphp
                                
                                @if($categories->count() > 0)
                                    @php renderCategoryRow($categories); @endphp
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ri-folder-line display-4"></i>
                                                <p class="mt-2">No categories found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    // Auto-generate slug from category name
    document.getElementById('category_name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .trim('-'); // Remove leading/trailing hyphens
        document.getElementById('slug').value = slug;
    });

    // Image preview
    document.getElementById('image').addEventListener('change', function() {
        const file = this.files[0];
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Edit category function
    function editCategory(id, name, slug, parentId, catType, image) {
        document.getElementById('formTitle').textContent = 'Edit Category';
        document.getElementById('categoryId').value = id;
        document.getElementById('category_name').value = name;
        document.getElementById('slug').value = slug;
        document.getElementById('parent_cat_id').value = parentId || '';
        document.getElementById('cat_type').value = catType;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('categoryForm').action = '/categories/' + id;
        document.getElementById('submitBtn').innerHTML = '<i class="ri-save-line me-1"></i> Update Category';
        document.getElementById('cancelBtn').style.display = 'block';
        
        // Show current image if exists
        if (image) {
            document.getElementById('previewImg').src = '/icon/' + image;
            document.getElementById('imagePreview').style.display = 'block';
        }
        
        // Remove the category being edited from parent dropdown to prevent self-reference
        const parentSelect = document.getElementById('parent_cat_id');
        const options = parentSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value == id) {
                option.style.display = 'none';
            } else {
                option.style.display = 'block';
            }
        });
        
        // Scroll to form
        document.getElementById('categoryForm').scrollIntoView({ behavior: 'smooth' });
    }

    // Delete category function
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category?')) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = '/categories/' + id;
            deleteForm.submit();
        }
    }

    // Reset form function
    function resetForm() {
        document.getElementById('formTitle').textContent = 'Add New Category';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('categoryForm').action = '/categories';
        document.getElementById('submitBtn').innerHTML = '<i class="ri-add-line me-1"></i> Add Category';
        document.getElementById('cancelBtn').style.display = 'none';
        document.getElementById('imagePreview').style.display = 'none';
        
        // Show all options in parent dropdown
        const parentSelect = document.getElementById('parent_cat_id');
        const options = parentSelect.querySelectorAll('option');
        options.forEach(option => {
            option.style.display = 'block';
        });
    }

    // Filter functionality
    document.getElementById('filterCategory').addEventListener('change', function() {
        filterTable();
    });

    document.getElementById('filterType').addEventListener('change', function() {
        filterTable();
    });

    function filterTable() {
        const categoryFilter = document.getElementById('filterCategory').value;
        const typeFilter = document.getElementById('filterType').value;
        const tableRows = document.querySelectorAll('#categoriesTable tbody tr');

        tableRows.forEach(row => {
            let showRow = true;
            
            // Filter by parent category
            if (categoryFilter !== '') {
                const rowParentId = row.getAttribute('data-parent-id');
                if (rowParentId !== categoryFilter && rowParentId !== null) {
                    // Also check if this category or any of its ancestors match
                    let matchFound = false;
                    if (getRowById(categoryFilter)) {
                        matchFound = isChildOf(row, categoryFilter);
                    }
                    if (!matchFound) {
                        showRow = false;
                    }
                }
            }
            
            // Filter by type
            if (typeFilter !== '') {
                const rowType = row.getAttribute('data-category-type');
                if (rowType !== typeFilter) {
                    showRow = false;
                }
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }

    function getRowById(categoryId) {
        return document.querySelector(`#categoriesTable tbody tr[data-category-id="${categoryId}"]`);
    }

    function isChildOf(row, parentId) {
        const rowParentId = row.getAttribute('data-parent-id');
        if (rowParentId === parentId) {
            return true;
        }
        if (rowParentId === null) {
            return false;
        }
        const parentRow = getRowById(rowParentId);
        if (parentRow) {
            return isChildOf(parentRow, parentId);
        }
        return false;
    }
</script>

@endsection