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
            @endphp
            
            @foreach($universalCategories as $universalCategory)
                @php
                    $allSubCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->get();
                @endphp
                
                @if($allSubCategories->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $universalCategory->id }}">
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $universalCategory->id }}" 
                                aria-expanded="false" 
                                aria-controls="collapse{{ $universalCategory->id }}">
                            <img src="{{ $universalCategory->image ? asset('icon/' . $universalCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                 alt="{{ $universalCategory->category_name }}"
                                 style="width: 24px; height: 24px; margin-right: 10px; object-fit: cover; border-radius: 4px;">
                            @t($universalCategory->category_name)
                        </button>
                    </h2>
                    <div id="collapse{{ $universalCategory->id }}" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="heading{{ $universalCategory->id }}" 
                         data-bs-parent="#categoryAccordion">
                        <div class="accordion-body">
                            <ul class="list-unstyled">
                                @foreach($allSubCategories as $subCategory)
                                    @php
                                        $subSubCategories = \App\Models\Category::where('parent_cat_id', $subCategory->id)->get();
                                    @endphp
                                    
                                    <li>
                                        @if($subSubCategories->count() > 0)
                                            <div class="sub-accordion-item">
                                                <button class="sub-accordion-button collapsed" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#subCollapse{{ $subCategory->id }}" 
                                                        aria-expanded="false">
                                                    <img src="{{ $subCategory->image ? asset('icon/' . $subCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                         alt="{{ $subCategory->category_name }}"
                                                         style="width: 20px; height: 20px; margin-right: 10px; object-fit: cover; border-radius: 3px;">
                                                    <span>@t($subCategory->category_name)</span>
                                                    <i class="fas fa-chevron-down sub-arrow"></i>
                                                </button>
                                                
                                                <div class="collapse sub-collapse" id="subCollapse{{ $subCategory->id }}">
                                                    <ul class="list-unstyled sub-sub-list">
                                                        @foreach($subSubCategories as $subSubCategory)
                                                            <li>
                                                                <a href="{{ route('products.category', [$visitorLocationPath, $subSubCategory->slug]) }}" 
                                                                   class="d-flex align-items-center text-decoration-none sub-sub-link">
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
                                               class="d-flex align-items-center text-decoration-none sub-link">
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

.sidebar-body {
    padding: 15px;
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
    border-bottom: 1px solid #e0e0e0;
}

.accordion-button {
    background: #fff;
    color: #333;
    font-weight: 500;
    padding: 15px 10px;
    border: none;
}

.accordion-button:not(.collapsed) {
    background: #f8f9fa;
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
    padding: 12px 15px;
    color: #555;
    transition: all 0.2s;
    border-bottom: 1px solid #f0f0f0;
}

.sub-link:hover {
    background: #f8f9fa;
    color: #ff6b6b;
    padding-left: 20px;
}

.sub-link span {
    font-size: 14px;
}

/* Sub-accordion (for nested categories) */
.sub-accordion-item {
    border-bottom: 1px solid #f0f0f0;
}

.sub-accordion-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: none;
    border: none;
    color: #555;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.sub-accordion-button:hover {
    background: #f8f9fa;
    color: #ff6b6b;
    padding-left: 20px;
}

.sub-accordion-button span {
    flex: 1;
}

.sub-arrow {
    font-size: 12px;
    transition: transform 0.3s;
    margin-left: auto;
}

.sub-accordion-button:not(.collapsed) .sub-arrow {
    transform: rotate(180deg);
}

.sub-accordion-button:not(.collapsed) {
    color: #ff6b6b;
    background: #f8f9fa;
}

/* Sub-sub category list */
.sub-sub-list {
    padding-left: 15px;
    background: #fafafa;
}

.sub-sub-link {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #666;
    transition: all 0.2s;
    font-size: 13px;
}

.sub-sub-link:hover {
    background: #f0f0f0;
    color: #ff6b6b;
    padding-left: 20px;
}

/* Dark Mode for Sidebar */
[data-bs-theme="dark"] .category-sidebar {
    background: #1a1a1a;
}

[data-bs-theme="dark"] .sidebar-header {
    background: #2b2b2b;
    border-bottom-color: #404040;
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
    background: #1a1a1a;
    color: #dee2e6;
}

[data-bs-theme="dark"] .accordion-button:not(.collapsed) {
    background: #2b2b2b;
    color: #ff6b6b;
}

[data-bs-theme="dark"] .accordion-item {
    border-bottom-color: #404040;
}

[data-bs-theme="dark"] .sub-link {
    color: #adb5bd;
    border-bottom-color: #2b2b2b;
}

[data-bs-theme="dark"] .sub-link:hover {
    background: #2b2b2b;
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-accordion-item {
    border-bottom-color: #2b2b2b;
}

[data-bs-theme="dark"] .sub-accordion-button {
    color: #adb5bd;
}

[data-bs-theme="dark"] .sub-accordion-button:hover {
    background: #2b2b2b;
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-accordion-button:not(.collapsed) {
    background: #2b2b2b;
    color: #ff6b6b;
}

[data-bs-theme="dark"] .sub-sub-list {
    background: #252525;
}

[data-bs-theme="dark"] .sub-sub-link {
    color: #999;
}

[data-bs-theme="dark"] .sub-sub-link:hover {
    background: #2b2b2b;
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
    
    // Sub-accordion toggle
    const subAccordionButtons = document.querySelectorAll('.sub-accordion-button');
    subAccordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('collapsed');
        });
    });
});
</script>
