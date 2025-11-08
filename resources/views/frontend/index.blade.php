@extends("frontend.master")
@section('main-content')

<style>
/* ===== Global Styles ===== */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --text-dark: #1e293b;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
    --bg-light: #f8fafc;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

/* Section Offset for Anchor Links */
.grid-section {
    scroll-margin-top: 100px;
    padding: 2rem 0;
}

/* ===== Section Title ===== */
.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), #60a5fa);
    border-radius: 2px;
}

/* ===== Top Links ===== */
.top-links {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.top-links a {
    color: var(--text-dark);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: var(--transition);
    position: relative;
}

.top-links a:hover {
    color: var(--primary-color);
    background: var(--bg-light);
}

.top-links a::before {
    content: '•';
    position: absolute;
    right: -0.75rem;
    color: var(--border-color);
}

.top-links a:last-child::before {
    display: none;
}

/* ===== Category Cards ===== */
.category-card-wrapper {
    transition: var(--transition);
}

.category-card {
    text-align: center;
    padding: 1rem;
    border-radius: 12px;
    transition: var(--transition);
    background: white;
    border: 1px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-color);
}

.category-icon-wrapper {
    width: 70px;
    height: 70px;
    margin: 0 auto 0.75rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid var(--border-color);
    transition: var(--transition);
    overflow: hidden;
}

.category-card:hover .category-icon-wrapper {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: var(--primary-color);
}

.category-icon-wrapper img {
    width: 65%;
    height: 65%;
    object-fit: contain;
}

.category-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-dark);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-align: center;
}

/* ===== Profile Tags (Badges) ===== */
.profile-tags-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.profile-tag {
    padding: 0.4rem 0.85rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    background: white;
    border: 1.5px solid var(--border-color);
    color: var(--text-dark);
    text-decoration: none;
    transition: var(--transition);
    white-space: nowrap;
}

.profile-tag:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.tag-toggle-btn {
    padding: 0.4rem 0.85rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    background: var(--secondary-color);
    border: 1.5px solid var(--secondary-color);
    color: white;
    cursor: pointer;
    transition: var(--transition);
}

.tag-toggle-btn:hover {
    background: #475569;
    border-color: #475569;
}

/* ===== See More/Less Toggle Button ===== */
.toggle-button-wrapper {
    text-align: center;
}

.toggle-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    margin: 0 auto;
    border-radius: 12px;
    border: 2px dashed var(--border-color);
    background: var(--bg-light);
    cursor: pointer;
    transition: var(--transition);
}

.toggle-button:hover {
    border-color: var(--primary-color);
    background: #eff6ff;
}

.toggle-button img {
    width: 50%;
    height: 50%;
    object-fit: contain;
    margin-bottom: 0.25rem;
}

.toggle-text {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* ===== Responsive Utilities ===== */
@media (max-width: 991px) {
    .category-hidden-sm {
        display: none !important;
    }
}

@media (min-width: 992px) {
    .category-hidden-lg {
        display: none !important;
    }
}

/* ===== Dark Mode Support ===== */
[data-bs-theme="dark"] {
    --text-dark: #f1f5f9;
    --text-muted: #94a3b8;
    --border-color: #334155;
    --bg-light: #1e293b;
}

[data-bs-theme="dark"] .section-title,
[data-bs-theme="dark"] .category-name,
[data-bs-theme="dark"] .top-links a {
    color: var(--text-dark);
}

[data-bs-theme="dark"] .category-card {
    background: #0f172a;
    border-color: var(--border-color);
}

[data-bs-theme="dark"] .category-icon-wrapper {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-color: var(--border-color);
}

[data-bs-theme="dark"] .profile-tag {
    background: #1e293b;
    border-color: var(--border-color);
    color: var(--text-dark);
}

[data-bs-theme="dark"] .profile-tag:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

[data-bs-theme="dark"] .toggle-button {
    background: #1e293b;
    border-color: var(--border-color);
}

[data-bs-theme="dark"] .toggle-button:hover {
    border-color: var(--primary-color);
    background: #1e3a8a;
}

/* ===== Mobile Optimization ===== */
@media (max-width: 576px) {
    .section-title {
        font-size: 1.5rem;
    }
    
    .category-icon-wrapper {
        width: 60px;
        height: 60px;
    }
    
    .category-name {
        font-size: 0.8rem;
    }
    
    .profile-tag {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
    }
}
</style>

<div class="container mt-4">
   
    <!-- ===== Categories Section ===== -->
    <section class="grid-section mb-5">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title">@t('Categories')</h2>
            </div>
        </div>
        
        <div class="top-links">
            <a href="{{ route('discount_wise_product',$visitorLocationPath) }}">@t('Discount & Offers')</a>
            <a href="{{ route('notice',$visitorLocationPath) }}">@t('Notice')</a>
        </div>
        
        <div class="row g-3">
            @php
                $universalCategories = \App\Models\Category::where('cat_type', 'universal')->where('parent_cat_id', null)->get();
                $totalCategories = $universalCategories->count();
                
                $cardsPerRowSm = 4;
                $cardsPerRowLg = 6;
                
                $completeRowsSm = intval($totalCategories / $cardsPerRowSm);
                $completeRowsLg = intval($totalCategories / $cardsPerRowLg);
                
                $remainingSm = $totalCategories % $cardsPerRowSm;
                $remainingLg = $totalCategories % $cardsPerRowLg;
                
                $needSeeMoreSm = $remainingSm > 0;
                $needSeeMoreLg = $remainingLg > 0;
                
                $seeMorePositionSm = $needSeeMoreSm ? ($completeRowsSm * $cardsPerRowSm) - 1 : -1;
                $seeMorePositionLg = $needSeeMoreLg ? ($completeRowsLg * $cardsPerRowLg) - 1 : -1;
            @endphp

            @foreach($universalCategories as $index => $category)
                @php
                    $hiddenOnSm = $needSeeMoreSm && $index > $seeMorePositionSm;
                    $hiddenOnLg = $needSeeMoreLg && $index > $seeMorePositionLg;
                    
                    if($needSeeMoreSm && $index == $seeMorePositionSm) $hiddenOnSm = true;
                    if($needSeeMoreLg && $index == $seeMorePositionLg) $hiddenOnLg = true;
                @endphp

                <div class="col-3 col-sm-3 col-lg-2 category-card-wrapper 
                    @if($hiddenOnSm) category-hidden-sm @endif 
                    @if($hiddenOnLg) category-hidden-lg @endif"
                    data-index="{{ $index }}">
                    <a href="#{{ $category->slug }}" class="text-decoration-none">
                        <div class="">
                            <div class="category-icon-wrapper">
                                <img src="{{ $category->image ? asset('icon/' . $category->image) : asset('profile-image/no-image.jpeg') }}"
                                     alt="{{ $category->category_name }}">
                            </div>
                            <span class="category-name">@t($category->category_name)</span>
                        </div>
                    </a>
                </div>

                @if($needSeeMoreSm && $index == $seeMorePositionSm)
                    <div class="col-3 col-sm-3 d-lg-none toggle-btn-sm" id="toggleBtnSm">
                        <div class="toggle-button-wrapper">
                            <a href="javascript:void(0);" onclick="toggleCategoriesSm()">
                                <div class="toggle-button">
                                    <img src="{{asset('icon/swipe-down.gif')}}" alt="Toggle">
                                    <span class="toggle-text" id="toggleTextSm">See More</span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                @if($needSeeMoreLg && $index == $seeMorePositionLg)
                    <div class="d-none d-lg-block col-lg-2 toggle-btn-lg" id="toggleBtnLg">
                        <div class="toggle-button-wrapper">
                            <a href="javascript:void(0);" onclick="toggleCategoriesLg()">
                                <div class="toggle-button">
                                    <img src="{{asset('icon/swipe-down.gif')}}" alt="Toggle">
                                    <span class="toggle-text" id="toggleTextLg">See More</span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    <!-- ===== Product Categories Sections ===== -->
    @foreach($universalCategories as $universalCategory)
        @php
            $profileCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->where('cat_type', 'profile')->get();
            $productCategories = \App\Models\Category::where('parent_cat_id', $universalCategory->id)->whereIn('cat_type', ['product', 'service','post'])->get();
            
            if($profileCategories->count() > $productCategories->count()) {
                $tempProfile = $profileCategories;
                $profileCategories = $productCategories;
                $productCategories = $tempProfile;
            }
        @endphp
        
        @if($productCategories->count() > 0)
        <section class="grid-section mb-5" id="{{ $universalCategory->slug }}">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="section-title">@t($universalCategory->category_name)</h2>
                </div>
                
                @if($profileCategories->count() > 0)
                    @php
                        $maxTagsFirstLine = 5;
                        $showSeeMore = $profileCategories->count() > $maxTagsFirstLine;
                    @endphp
                    <div class="col-12">
                        <div class="profile-tags-wrapper" id="profileTags-{{ $universalCategory->id }}">
                            @foreach($profileCategories as $index => $profileCat)
                                <a href="{{ route('products.category', [$visitorLocationPath, $profileCat->slug]) }}" 
                                   class="profile-tag profile-tag-{{ $universalCategory->id }} 
                                       @if($showSeeMore && $index >= $maxTagsFirstLine) d-none extra-tag-{{ $universalCategory->id }} @endif">
                                    @t($profileCat->category_name)
                                </a>
                            @endforeach

                            @if($showSeeMore)
                                <a href="javascript:void(0);" 
                                   class="tag-toggle-btn"
                                   id="seeMoreProfileTagsBtn-{{ $universalCategory->id }}"
                                   onclick="showAllProfileTags('{{ $universalCategory->id }}')">
                                    See More
                                </a>
                                <a href="javascript:void(0);" 
                                   class="tag-toggle-btn d-none"
                                   id="seeLessProfileTagsBtn-{{ $universalCategory->id }}"
                                   onclick="showLessProfileTags('{{ $universalCategory->id }}')">
                                    See Less
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="row g-3 mt-2">
                @php
                    $sectionId = 'section_' . $universalCategory->id;
                    $totalProductCats = $productCategories->count();
                    
                    $prodCardsPerRowSm = 4;
                    $prodCardsPerRowLg = 6;
                    
                    $prodCompleteRowsSm = intval($totalProductCats / $prodCardsPerRowSm);
                    $prodCompleteRowsLg = intval($totalProductCats / $prodCardsPerRowLg);
                    
                    $prodRemainingSm = $totalProductCats % $prodCardsPerRowSm;
                    $prodRemainingLg = $totalProductCats % $prodCardsPerRowLg;
                    
                    $prodNeedSeeMoreSm = $prodRemainingSm > 0;
                    $prodNeedSeeMoreLg = $prodRemainingLg > 0;
                    
                    $prodSeeMorePositionSm = $prodNeedSeeMoreSm ? ($prodCompleteRowsSm * $prodCardsPerRowSm) - 1 : -1;
                    $prodSeeMorePositionLg = $prodNeedSeeMoreLg ? ($prodCompleteRowsLg * $prodCardsPerRowLg) - 1 : -1;
                @endphp

                @foreach($productCategories as $prodIndex => $productCat)
                    @php
                        $prodHiddenOnSm = $prodNeedSeeMoreSm && $prodIndex > $prodSeeMorePositionSm;
                        $prodHiddenOnLg = $prodNeedSeeMoreLg && $prodIndex > $prodSeeMorePositionLg;
                        
                        if($prodNeedSeeMoreSm && $prodIndex == $prodSeeMorePositionSm) $prodHiddenOnSm = true;
                        if($prodNeedSeeMoreLg && $prodIndex == $prodSeeMorePositionLg) $prodHiddenOnLg = true;
                    @endphp

                    <div class="col-3 col-sm-3 col-lg-2 product-category-card-{{ $sectionId }}
                        @if($prodHiddenOnSm) product-hidden-sm-{{ $sectionId }} @endif 
                        @if($prodHiddenOnLg) product-hidden-lg-{{ $sectionId }} @endif"
                        data-prod-index="{{ $prodIndex }}">
                        <a href="{{ route('products.category', [$visitorLocationPath, $productCat->slug]) }}" class="text-decoration-none">
                            <div class="category-card">
                                <div class="category-icon-wrapper">
                                    <img src="{{ $productCat->image ? asset('icon/' . $productCat->image) : asset('profile-image/no-image.jpeg') }}"
                                         alt="{{ $productCat->category_name }}">
                                </div>
                                <span class="category-name">@t($productCat->category_name)</span>
                            </div>
                        </a>
                    </div>

                    @if($prodNeedSeeMoreSm && $prodIndex == $prodSeeMorePositionSm)
                        <div class="col-3 col-sm-3 d-lg-none prod-toggle-btn-sm-{{ $sectionId }}" id="prodToggleBtnSm{{ $sectionId }}">
                            <div class="toggle-button-wrapper">
                                <a href="javascript:void(0);" onclick="toggleProductCategoriesSm('{{ $sectionId }}', {{ $prodSeeMorePositionSm }})">
                                    <div class="toggle-button">
                                        <img src="{{asset('icon/swipe-down.gif')}}" alt="Toggle">
                                        <span class="toggle-text" id="prodToggleTextSm{{ $sectionId }}">See More</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($prodNeedSeeMoreLg && $prodIndex == $prodSeeMorePositionLg)
                        <div class="d-none d-lg-block col-lg-2 prod-toggle-btn-lg-{{ $sectionId }}" id="prodToggleBtnLg{{ $sectionId }}">
                            <div class="toggle-button-wrapper">
                                <a href="javascript:void(0);" onclick="toggleProductCategoriesLg('{{ $sectionId }}', {{ $prodSeeMorePositionLg }})">
                                    <div class="toggle-button">
                                        <img src="{{asset('icon/swipe-down.gif')}}" alt="Toggle">
                                        <span class="toggle-text" id="prodToggleTextLg{{ $sectionId }}">See More</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
        @endif
    @endforeach

</div>

<style>
    /* Dynamic styles for product sections */
    @foreach($universalCategories as $universalCategory)
        @php
            $sectionId = 'section_' . $universalCategory->id;
        @endphp
        
        @media (max-width: 991px) {
            .product-hidden-sm-{{ $sectionId }} {
                display: none !important;
            }
        }
        
        @media (min-width: 992px) {
            .product-hidden-lg-{{ $sectionId }} {
                display: none !important;
            }
        }
    @endforeach
</style>

<script>
    // Profile tags toggle functions
    function showAllProfileTags(sectionId) {
        document.querySelectorAll('.extra-tag-' + sectionId).forEach(tag => {
            tag.classList.remove('d-none');
        });
        document.getElementById('seeMoreProfileTagsBtn-' + sectionId).style.display = 'none';
        document.getElementById('seeLessProfileTagsBtn-' + sectionId).classList.remove('d-none');
    }

    function showLessProfileTags(sectionId) {
        document.querySelectorAll('.extra-tag-' + sectionId).forEach(tag => {
            tag.classList.add('d-none');
        });
        document.getElementById('seeMoreProfileTagsBtn-' + sectionId).style.display = '';
        document.getElementById('seeLessProfileTagsBtn-' + sectionId).classList.add('d-none');
    }

    // Main categories toggle
    let expandedSm = false;
    let expandedLg = false;

    function toggleCategoriesSm() {
        expandedSm = !expandedSm;
        const toggleBtn = document.getElementById('toggleBtnSm');
        const toggleText = document.getElementById('toggleTextSm');
        
        if (expandedSm) {
            document.querySelectorAll('.category-hidden-sm').forEach(card => {
                card.classList.remove('category-hidden-sm');
                card.style.display = 'block';
            });
            toggleText.textContent = 'See Less';
            toggleBtn.parentElement.appendChild(toggleBtn);
        } else {
            document.querySelectorAll('.category-card-wrapper').forEach(card => {
                const index = parseInt(card.getAttribute('data-index'));
                if (index >= {{ $seeMorePositionSm ?? -1 }}) {
                    card.classList.add('category-hidden-sm');
                }
            });
            toggleText.textContent = 'See More';
            
            const row = toggleBtn.parentElement;
            const cards = row.querySelectorAll('.category-card-wrapper');
            let insertPosition = null;
            
            cards.forEach(card => {
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
            document.querySelectorAll('.category-hidden-lg').forEach(card => {
                card.classList.remove('category-hidden-lg');
                card.style.display = 'block';
            });
            toggleText.textContent = 'See Less';
            toggleBtn.parentElement.appendChild(toggleBtn);
        } else {
            document.querySelectorAll('.category-card-wrapper').forEach(card => {
                const index = parseInt(card.getAttribute('data-index'));
                if (index >= {{ $seeMorePositionLg ?? -1 }}) {
                    card.classList.add('category-hidden-lg');
                }
            });
            toggleText.textContent = 'See More';
            
            const row = toggleBtn.parentElement;
            const cards = row.querySelectorAll('.category-card-wrapper');
            let insertPosition = null;
            
            cards.forEach(card => {
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

    // Product categories toggle
    let productExpandedSm = {};
    let productExpandedLg = {};

    function toggleProductCategoriesSm(sectionId, seeMorePosition) {
        productExpandedSm[sectionId] = !productExpandedSm[sectionId];
        const toggleBtn = document.getElementById('prodToggleBtnSm' + sectionId);
        const toggleText = document.getElementById('prodToggleTextSm' + sectionId);
        
        if (productExpandedSm[sectionId]) {
            document.querySelectorAll('.product-hidden-sm-' + sectionId).forEach(card => {
                card.classList.remove('product-hidden-sm-' + sectionId);
                card.style.display = 'block';
            });
            toggleText.textContent = 'See Less';
            toggleBtn.parentElement.appendChild(toggleBtn);
        } else {
            document.querySelectorAll('.product-category-card-' + sectionId).forEach(card => {
                const index = parseInt(card.getAttribute('data-prod-index'));
                if (index >= seeMorePosition) {
                    card.classList.add('product-hidden-sm-' + sectionId);
                }
            });
            toggleText.textContent = 'See More';
            
            const row = toggleBtn.parentElement;
            const cards = row.querySelectorAll('.product-category-card-' + sectionId);
            let insertPosition = null;
            
            cards.forEach(card => {
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
            document.querySelectorAll('.product-hidden-lg-' + sectionId).forEach(card => {
                card.classList.remove('product-hidden-lg-' + sectionId);
                card.style.display = 'block';
            });
            toggleText.textContent = 'See Less';
            toggleBtn.parentElement.appendChild(toggleBtn);
        } else {
            document.querySelectorAll('.product-category-card-' + sectionId).forEach(card => {
                const index = parseInt(card.getAttribute('data-prod-index'));
                if (index >= seeMorePosition) {
                    card.classList.add('product-hidden-lg-' + sectionId);
                }
            });
            toggleText.textContent = 'See More';
            
            const row = toggleBtn.parentElement;
            const cards = row.querySelectorAll('.product-category-card-' + sectionId);
            let insertPosition = null;
            
            cards.forEach(card => {
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