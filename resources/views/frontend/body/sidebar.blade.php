<!-- Sidebar -->
<div class="category-sidebar" id="categorySidebar">
    <div class="sidebar-header">
        <h5>All Categories</h5>
        <button class="btn-close" id="closeSidebarBtn">&times;</button>
    </div>
    
    <div class="sidebar-body">
        <div class="accordion" id="categoryAccordion">
            @php
                $universalCategories = \App\Models\Category::where('cat_type', 'universal')->where('parent_cat_id', null)->get();
                $currentCategoryId = null;
                $currentParentId = null;
                $currentGrandParentId = null;
                
                // Get current category from URL
                if(request()->route('slug')) {
                    $currentCategory = \App\Models\Category::where('slug', request()->route('slug'))->first();
                    if($currentCategory) {
                        $currentCategoryId = $currentCategory->id;
                        $currentParentId = $currentCategory->parent_cat_id;
                        
                        // Find grandparent if exists
                        if($currentParentId) {
                            $parentCategory = \App\Models\Category::find($currentParentId);
                            if($parentCategory) {
                                $currentGrandParentId = $parentCategory->parent_cat_id;
                            }
                        }
                    }
                }
            @endphp
            
            @foreach($universalCategories as $universalCategory)
                @php
                    $allSubCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->get();
                    $isActiveParent = ($currentGrandParentId == $universalCategory->id || $currentParentId == $universalCategory->id || $currentCategoryId == $universalCategory->id);
                @endphp
                
                @if($allSubCategories->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $universalCategory->id }}">
                        <button class="accordion-button {{ $isActiveParent ? '' : 'collapsed' }}" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $universalCategory->id }}" 
                                aria-expanded="{{ $isActiveParent ? 'true' : 'false' }}" 
                                aria-controls="collapse{{ $universalCategory->id }}">
                            <img src="{{ $universalCategory->image ? asset('icon/' . $universalCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                 alt="{{ $universalCategory->category_name }}"
                                 style="width: 24px; height: 24px; margin-right: 10px; object-fit: cover; border-radius: 4px;">
                            @t($universalCategory->category_name)
                        </button>
                    </h2>
                    <div id="collapse{{ $universalCategory->id }}" 
                         class="accordion-collapse collapse {{ $isActiveParent ? 'show' : '' }}" 
                         aria-labelledby="heading{{ $universalCategory->id }}" 
                         data-bs-parent="#categoryAccordion">
                        <div class="accordion-body">
                            <ul class="list-unstyled">
                                @foreach($allSubCategories as $subCategory)
                                    @php
                                        $subSubCategories = \App\Models\Category::where('parent_cat_id', $subCategory->id)->get();
                                        $isActiveSubParent = ($currentParentId == $subCategory->id || $currentCategoryId == $subCategory->id);
                                    @endphp
                                    
                                    <li>
                                        @if($subSubCategories->count() > 0)
                                            <div class="sub-accordion-item">
                                                <div class="sub-accordion-wrapper">
                                                    <a href="{{ route('products.category', [$visitorLocationPath, $subCategory->slug]) }}" 
                                                       class="sub-category-link {{ $currentCategoryId == $subCategory->id ? 'active' : '' }}">
                                                        <img src="{{ $subCategory->image ? asset('icon/' . $subCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                             alt="{{ $subCategory->category_name }}"
                                                             style="width: 20px; height: 20px; margin-right: 10px; object-fit: cover; border-radius: 3px;">
                                                        <span>@t($subCategory->category_name)</span>
                                                    </a>
                                                    <button class="collapse-toggle {{ $isActiveSubParent ? '' : 'collapsed' }}" type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#subCollapse{{ $subCategory->id }}" 
                                                            aria-expanded="{{ $isActiveSubParent ? 'true' : 'false' }}">
                                                        <i class="fas fa-chevron-down sub-arrow"></i>
                                                    </button>
                                                </div>
                                                
                                                <div class="collapse sub-collapse {{ $isActiveSubParent ? 'show' : '' }}" id="subCollapse{{ $subCategory->id }}">
                                                    <ul class="list-unstyled sub-sub-list">
                                                        @foreach($subSubCategories as $subSubCategory)
                                                            <li>
                                                                <a href="{{ route('products.category', [$visitorLocationPath, $subSubCategory->slug]) }}" 
                                                                   class="d-flex align-items-center text-decoration-none sub-sub-link {{ $currentCategoryId == $subSubCategory->id ? 'active' : '' }}">
                                                                    <img src="{{ $subSubCategory->image ? asset('icon/' . $subSubCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                                         alt="{{ $subSubCategory->category_name }}"
                                                                         style="width: 18px; height: 18px; margin-right: 8px; object-fit: cover; border-radius: 3px;">
                                                                    <span>@t($subSubCategory->category_name)</span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('products.category', [$visitorLocationPath, $subCategory->slug]) }}" 
                                               class="d-flex align-items-center text-decoration-none sub-link {{ $currentCategoryId == $subCategory->id ? 'active' : '' }}">
                                                <img src="{{ $subCategory->image ? asset('icon/' . $subCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                     alt="{{ $subCategory->category_name }}"
                                                     style="width: 20px; height: 20px; margin-right: 10px; object-fit: cover; border-radius: 3px;">
                                                <span>@t($subCategory->category_name)</span>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
/* Sidebar Styles */
.category-sidebar {
    position: fixed;
    top: 0;
    left: -350px;
    width: 350px;
    height: 100vh;
    background: #fff;
    box-shadow: 2px 0 15px rgba(0,0,0,0.1);
    z-index: 1060;
    transition: left 0.3s ease;
    overflow-y: auto;
}

.category-sidebar.active {
    left: 0;
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.sidebar-header h5 {
    margin: 0;
    font-weight: 600;
    color: #333;
}

.btn-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close:hover {
    color: #000;
}



.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(0,0,0,0.5);
    z-index: 1055;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

/* Main Accordion Styles */
.accordion-item {
    border: none;
    margin-bottom: 10px;
}

.accordion-button {
    background: #fff;
    color: #333;
    font-weight: 500;
    padding: 15px 10px;
    border: none;
}

.accordion-button:not(.collapsed) {
    background: #fff;
    color: #ff6b6b;
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border: none;
}

.accordion-button::after {
    background-size: 1.2rem;
}

.accordion-body {
    padding: 0;
    position: relative;
}

/* Vertical line for subcategories */
.accordion-body::before {
    content: '';
    position: absolute;
    left: 25px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.accordion-body ul {
    margin: 0;
    padding: 0;
}

.accordion-body li {
    padding: 0;
}

/* Sub-category Link Styles */
.sub-link {
    display: flex;
    align-items: center;
    padding: 12px 15px 12px 35px;
    color: #555;
    transition: all 0.2s;
    position: relative;
}

.sub-link:hover {
    color: #ff6b6b;
}

.sub-link.active {
    color: #ff6b6b;
    font-weight: 500;
}

.sub-link span {
    font-size: 14px;
}

/* Sub-accordion (for nested categories) */
.sub-accordion-item {
    position: relative;
}

.sub-accordion-wrapper {
    display: flex;
    align-items: center;
    position: relative;
    transition: all 0.2s;
}

.sub-category-link {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 12px 15px 12px 35px;
    color: #555;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
}

.sub-category-link:hover {
    color: #ff6b6b;
}

.sub-category-link.active {
    color: #ff6b6b;
    font-weight: 500;
}

.collapse-toggle {
    background: none;
    border: none;
    padding: 12px 15px;
    cursor: pointer;
    color: #555;
    transition: all 0.2s;
}

.collapse-toggle:hover {
    color: #ff6b6b;
}

.sub-arrow {
    font-size: 12px;
    transition: transform 0.3s;
}

.collapse-toggle:not(.collapsed) .sub-arrow {
    transform: rotate(180deg);
}

.collapse-toggle:not(.collapsed) {
    color: #ff6b6b;
}

/* Sub-sub category list */
.sub-sub-list {
    padding-left: 15px;
    background: transparent;
    position: relative;
}

/* Vertical line for sub-sub categories */
.sub-sub-list::before {
    content: '';
    position: absolute;
    left: 45px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.sub-sub-link {
    display: flex;
    align-items: center;
    padding: 10px 15px 10px 65px;
    color: #666;
    transition: all 0.2s;
    font-size: 13px;
}

.sub-sub-link:hover {
    color: #ff6b6b;
}

.sub-sub-link.active {
    color: #ff6b6b;
    font-weight: 500;
}



[data-bs-theme="dark"] .sidebar-header h5 {
    color: #dee2e6;
}

[data-bs-theme="dark"] .btn-close {
    color: #adb5bd;
}

[data-bs-theme="dark"] .btn-close:hover {
    color: #fff;
}

[data-bs-theme="dark"] .accordion-button {
    color: #dee2e6;
}

[data-bs-theme="dark"] .accordion-button:not(.collapsed) {
    color: #ff6b6b;
}


[data-bs-theme="dark"] .sub-link {
    color: #adb5bd;
}

[data-bs-theme="dark"] .sub-link:hover {
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-link.active {
    color: #ff6b6b;
}


[data-bs-theme="dark"] .sub-category-link {
    color: #adb5bd;
}

[data-bs-theme="dark"] .sub-category-link:hover {
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-category-link.active {
    color: #ff6b6b;
    background: #2b2b2b;
}

[data-bs-theme="dark"] .collapse-toggle {
    color: #adb5bd;
}

[data-bs-theme="dark"] .collapse-toggle:hover {
    color: #ff6b6b;
}

[data-bs-theme="dark"] .collapse-toggle:not(.collapsed) {
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-sub-list {
    background: transparent;
}

[data-bs-theme="dark"] .sub-sub-list::before {
    background: #404040;
}

[data-bs-theme="dark"] .sub-sub-link {
    color: #999;
}

[data-bs-theme="dark"] .sub-sub-link:hover {
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-sub-link.active {
    color: #ff6b6b;
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .category-sidebar {
        width: 280px;
        left: -280px;
    }
    
    #openSidebarBtn {
        top: 70px;
        left: 10px;
        padding: 8px 12px;
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openSidebarBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const overlay = document.getElementById('sidebarOverlay');
    const sidebar = document.getElementById('categorySidebar');
    
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
</script>