<!-- ============================================================
     BEAUTIFUL CATEGORY SIDEBAR — Redesigned
     Drop-in replacement for your existing sidebar partial.
     All Blade / Laravel logic is preserved exactly.
     Only the markup structure + CSS + JS were improved.
============================================================ -->

<!-- Sidebar -->
<div class="cat-sidebar" id="categorySidebar">

    {{-- ── Header ── --}}
    <div class="cat-sidebar__header">
        <div class="cat-sidebar__header-inner">
            <span class="cat-sidebar__logo-dot"></span>
            <h5 class="cat-sidebar__title">Business Categories</h5>
        </div>
        <button class="cat-sidebar__close" id="closeSidebarBtn" aria-label="Close">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M1 1l16 16M17 1L1 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    {{-- ── Body ── --}}
    <div class="cat-sidebar__body">
        <div class="cat-sidebar__accordion" id="categoryAccordion">

            @php
                /* ── Helpers (unchanged logic) ── */
                function getAllDescendantCategoryIds($categoryId) {
                    $categoryIds = [$categoryId];
                    $children = \App\Models\Category::where('parent_cat_id', $categoryId)->get();
                    foreach($children as $child) {
                        $categoryIds = array_merge($categoryIds, getAllDescendantCategoryIds($child->id));
                    }
                    return $categoryIds;
                }

                function categoryHasData($category) {
                    $categoryIds = getAllDescendantCategoryIds($category->id);
                    $hasPosts    = \App\Models\Post::whereIn('category_id', $categoryIds)->exists();
                    $hasUsers    = \App\Models\User::whereIn('category_id', $categoryIds)->exists();
                    return $hasPosts || $hasUsers;
                }

                $universalCategories = \App\Models\Category::where('cat_type', 'universal')
                    ->where('parent_cat_id', null)->get();

                $currentCategoryId   = null;
                $currentParentId     = null;
                $currentGrandParentId = null;

                if (request()->route('slug')) {
                    $currentCategory = \App\Models\Category::where('slug', request()->route('slug'))->first();
                    if ($currentCategory) {
                        $currentCategoryId = $currentCategory->id;
                        $currentParentId   = $currentCategory->parent_cat_id;
                        if ($currentParentId) {
                            $parentCategory = \App\Models\Category::find($currentParentId);
                            if ($parentCategory) $currentGrandParentId = $parentCategory->parent_cat_id;
                        }
                    }
                }

                $universalCategories = $universalCategories->filter(fn($c) => categoryHasData($c));
            @endphp

            @foreach($universalCategories as $universalCategory)
                @php
                    // Only get direct children with cat_type profile
                    $profileSubs = \App\Models\Category::where('parent_cat_id', $universalCategory->id)
                        ->where('cat_type', 'profile')
                        ->get()
                        ->filter(fn($s) => categoryHasData($s));

                    $isActiveParent = (
                        $currentGrandParentId == $universalCategory->id ||
                        $currentParentId      == $universalCategory->id ||
                        $currentCategoryId    == $universalCategory->id
                    );
                @endphp

                @if($profileSubs->count() > 0)
                <div class="cat-group {{ $isActiveParent ? 'is-open' : '' }}"
                     data-group-id="{{ $universalCategory->id }}">

                    {{-- ── Group header button ── --}}
                    <button class="cat-group__toggle"
                            type="button"
                            aria-expanded="{{ $isActiveParent ? 'true' : 'false' }}"
                            aria-controls="group-{{ $universalCategory->id }}">

                        <span class="cat-group__icon-wrap">
                            <img src="{{ $universalCategory->image ? asset('icon/'.$universalCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                 alt="{{ $universalCategory->category_name }}"
                                 class="cat-group__icon">
                        </span>

                        <span class="cat-group__label">@t($universalCategory->category_name)</span>

                        <span class="cat-group__arrow">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </button>

                    {{-- ── Collapsible panel ── --}}
                    <div class="cat-group__panel" id="group-{{ $universalCategory->id }}">
                        <ul class="cat-list">
                            @foreach($profileSubs as $subCategory)
                                @php
                                    $subSubCategories = \App\Models\Category::where('parent_cat_id', $subCategory->id)->get()
                                        ->filter(fn($ss) => categoryHasData($ss));

                                    $isActiveSubParent = (
                                        $currentParentId  == $subCategory->id ||
                                        $currentCategoryId == $subCategory->id
                                    );
                                @endphp

                                <li class="cat-list__item">
                                    @if($subSubCategories->count() > 0)
                                        {{-- Sub-category with children --}}
                                        <div class="cat-sub {{ $isActiveSubParent ? 'is-open' : '' }}"
                                             data-sub-id="{{ $subCategory->id }}">

                                            <div class="cat-sub__row">
                                                <a href="{{ route('products.category', [$visitorLocationPath, $subCategory->slug]) }}"
                                                   class="cat-sub__link {{ $currentCategoryId == $subCategory->id ? 'is-active' : '' }}">
                                                    <img src="{{ $subCategory->image ? asset('icon/'.$subCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                         alt="{{ $subCategory->category_name }}"
                                                         class="cat-sub__icon">
                                                    <span>@t($subCategory->category_name)</span>
                                                </a>

                                                <button class="cat-sub__toggle"
                                                        type="button"
                                                        aria-expanded="{{ $isActiveSubParent ? 'true' : 'false' }}"
                                                        aria-label="Expand {{ $subCategory->category_name }}">
                                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                                        <path d="M1.5 3.5l3.5 3 3.5-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            <ul class="cat-sub__panel {{ $isActiveSubParent ? 'is-open' : '' }}">
                                                @foreach($subSubCategories as $subSubCategory)
                                                    <li>
                                                        <a href="{{ route('products.category', [$visitorLocationPath, $subSubCategory->slug]) }}"
                                                           class="cat-deep__link {{ $currentCategoryId == $subSubCategory->id ? 'is-active' : '' }}">
                                                            <img src="{{ $subSubCategory->image ? asset('icon/'.$subSubCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                                 alt="{{ $subSubCategory->category_name }}"
                                                                 class="cat-deep__icon">
                                                            <span>@t($subSubCategory->category_name)</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        {{-- Leaf sub-category --}}
                                        <a href="{{ route('products.category', [$visitorLocationPath, $subCategory->slug]) }}"
                                           class="cat-leaf__link {{ $currentCategoryId == $subCategory->id ? 'is-active' : '' }}">
                                            <img src="{{ $subCategory->image ? asset('icon/'.$subCategory->image) : asset('profile-image/no-image.jpeg') }}"
                                                 alt="{{ $subCategory->category_name }}"
                                                 class="cat-leaf__icon">
                                            <span>@t($subCategory->category_name)</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- Overlay -->
<div class="cat-overlay" id="sidebarOverlay"></div>


{{-- ══════════════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════════════ --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700&display=swap');

/* ── CSS Variables ── */
:root {
    --cs-bg:           #ffffff;
    --cs-bg-hover:     #fdf6f0;
    --cs-border:       #f0ebe4;
    --cs-accent:       #e8622a;
    --cs-accent-soft:  #fef0e9;
    --cs-text-primary: #1c1410;
    --cs-text-muted:   #857d77;
    --cs-text-link:    #4a3f38;
    --cs-shadow:       0 8px 40px rgba(0,0,0,.12);
    --cs-icon-radius:  6px;
    --cs-width:        320px;
    --cs-transition:   .28s cubic-bezier(.4,0,.2,1);
}

[data-bs-theme="dark"] {
    --cs-bg:           #181512;
    --cs-bg-hover:     #231e1a;
    --cs-border:       #2e2822;
    --cs-accent:       #f0743c;
    --cs-accent-soft:  #2e1e12;
    --cs-text-primary: #f0ebe4;
    --cs-text-muted:   #7a7068;
    --cs-text-link:    #c8bfb7;
}

/* ── Sidebar Shell ── */
.cat-sidebar {
    position: fixed;
    top: 0;
    left: calc(-1 * var(--cs-width) - 20px);
    width: var(--cs-width);
    height: 100vh;
    background: var(--cs-bg);
    box-shadow: var(--cs-shadow);
    z-index: 1060;
    display: flex;
    flex-direction: column;
    transition: left var(--cs-transition);
    font-family: 'DM Sans', sans-serif;
    border-right: 1px solid var(--cs-border);
}

.cat-sidebar.active { left: 0; }

/* ── Overlay ── */
.cat-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    backdrop-filter: blur(3px);
    z-index: 1055;
    opacity: 0;
    pointer-events: none;
    transition: opacity var(--cs-transition);
}
.cat-overlay.active {
    opacity: 1;
    pointer-events: all;
}

/* ── Header ── */
.cat-sidebar__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 20px 18px;
    border-bottom: 1px solid var(--cs-border);
    flex-shrink: 0;
    background: var(--cs-bg);
    position: sticky;
    top: 0;
    z-index: 5;
}

.cat-sidebar__header-inner {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cat-sidebar__logo-dot {
    display: block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--cs-accent);
    box-shadow: 0 0 0 3px var(--cs-accent-soft);
    flex-shrink: 0;
}

.cat-sidebar__title {
    font-family: 'Syne', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--cs-text-primary);
    margin: 0;
    letter-spacing: .01em;
}

.cat-sidebar__close {
    background: none;
    border: 1px solid var(--cs-border);
    border-radius: 8px;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: var(--cs-text-muted);
    transition: all .2s;
    flex-shrink: 0;
}

.cat-sidebar__close:hover {
    background: var(--cs-accent);
    border-color: var(--cs-accent);
    color: #fff;
    transform: rotate(90deg);
}

/* ── Body ── */
.cat-sidebar__body {
    flex: 1;
    overflow-y: auto;
    padding: 12px 0 24px;
    scrollbar-width: thin;
    scrollbar-color: var(--cs-border) transparent;
}

.cat-sidebar__body::-webkit-scrollbar { width: 4px; }
.cat-sidebar__body::-webkit-scrollbar-track { background: transparent; }
.cat-sidebar__body::-webkit-scrollbar-thumb { background: var(--cs-border); border-radius: 4px; }

/* ── Category Group ── */
.cat-group { border-bottom: 1px solid var(--cs-border); }

.cat-group__toggle {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 18px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    color: var(--cs-text-primary);
    transition: background var(--cs-transition);
}

.cat-group__toggle:hover { background: var(--cs-bg-hover); }

.cat-group.is-open .cat-group__toggle {
    background: var(--cs-accent-soft);
}

.cat-group__icon-wrap {
    width: 32px; height: 32px;
    border-radius: var(--cs-icon-radius);
    background: var(--cs-border);
    overflow: hidden;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: background var(--cs-transition);
}

.cat-group.is-open .cat-group__icon-wrap {
    background: var(--cs-accent);
    box-shadow: 0 4px 12px rgba(232,98,42,.3);
}

.cat-group__icon {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}

.cat-group__label {
    flex: 1;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--cs-text-link);
    letter-spacing: .01em;
    line-height: 1.3;
}

.cat-group.is-open .cat-group__label {
    color: var(--cs-accent);
    font-weight: 600;
}

.cat-group__arrow {
    color: var(--cs-text-muted);
    transition: transform var(--cs-transition);
    flex-shrink: 0;
}

.cat-group.is-open .cat-group__arrow {
    transform: rotate(180deg);
    color: var(--cs-accent);
}

/* ── Group Panel ── */
.cat-group__panel {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows var(--cs-transition);
}

.cat-group.is-open .cat-group__panel {
    grid-template-rows: 1fr;
}

.cat-group__panel > .cat-list {
    overflow: hidden;
    list-style: none;
    margin: 0;
    padding: 4px 0 8px;
    position: relative;
}

/* Accent bar on left */
.cat-group__panel > .cat-list::before {
    content: '';
    position: absolute;
    left: 27px; top: 0; bottom: 0;
    width: 1px;
    background: linear-gradient(to bottom, transparent, var(--cs-accent) 20%, var(--cs-border) 80%, transparent);
    opacity: .4;
}

/* ── Leaf link (no children) ── */
.cat-leaf__link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px 9px 48px;
    color: var(--cs-text-muted);
    text-decoration: none;
    font-size: 13px;
    transition: all .2s;
    border-radius: 0;
    position: relative;
}

.cat-leaf__link:hover {
    color: var(--cs-accent);
    background: var(--cs-bg-hover);
}

.cat-leaf__link.is-active {
    color: var(--cs-accent);
    font-weight: 500;
    background: var(--cs-accent-soft);
}

.cat-leaf__link.is-active::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--cs-accent);
    border-radius: 0 3px 3px 0;
}

.cat-leaf__icon {
    width: 18px; height: 18px;
    border-radius: 4px;
    object-fit: cover;
    flex-shrink: 0;
    opacity: .7;
    transition: opacity .2s;
}

.cat-leaf__link:hover .cat-leaf__icon,
.cat-leaf__link.is-active .cat-leaf__icon { opacity: 1; }

/* ── Sub-category (has children) ── */
.cat-sub {}

.cat-sub__row {
    display: flex;
    align-items: center;
}

.cat-sub__link {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 4px 9px 48px;
    color: var(--cs-text-muted);
    text-decoration: none;
    font-size: 13px;
    transition: color .2s;
}

.cat-sub__link:hover  { color: var(--cs-accent); }
.cat-sub__link.is-active { color: var(--cs-accent); font-weight: 500; }

.cat-sub__icon {
    width: 18px; height: 18px;
    border-radius: 4px;
    object-fit: cover;
    flex-shrink: 0;
    opacity: .7;
}

.cat-sub__link:hover .cat-sub__icon,
.cat-sub__link.is-active .cat-sub__icon { opacity: 1; }

.cat-sub__toggle {
    width: 34px; height: 34px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--cs-text-muted);
    display: flex; align-items: center; justify-content: center;
    border-radius: 6px;
    margin-right: 6px;
    transition: all .2s;
    flex-shrink: 0;
}

.cat-sub__toggle:hover,
.cat-sub.is-open .cat-sub__toggle {
    background: var(--cs-accent-soft);
    color: var(--cs-accent);
}

.cat-sub__toggle svg {
    transition: transform var(--cs-transition);
}

.cat-sub.is-open .cat-sub__toggle svg {
    transform: rotate(180deg);
}

/* ── Sub panel ── */
.cat-sub__panel {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows var(--cs-transition);
    background: linear-gradient(to right, var(--cs-accent-soft) 0%, transparent 6px);
}

.cat-sub__panel.is-open {
    grid-template-rows: 1fr;
}

.cat-sub__panel > li:first-child { overflow: hidden; }
.cat-sub__panel li { overflow: visible; }

/* Workaround: wrap content */
.cat-sub__panel { overflow: hidden; }

/* ── Deep link (3rd level) ── */
.cat-deep__link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px 8px 62px;
    color: #a09890;
    text-decoration: none;
    font-size: 12.5px;
    transition: all .2s;
    position: relative;
}

.cat-deep__link::before {
    content: '·';
    position: absolute;
    left: 52px;
    color: var(--cs-border);
}

.cat-deep__link:hover {
    color: var(--cs-accent);
    background: var(--cs-bg-hover);
}

.cat-deep__link.is-active {
    color: var(--cs-accent);
    font-weight: 500;
}

.cat-deep__icon {
    width: 16px; height: 16px;
    border-radius: 3px;
    object-fit: cover;
    flex-shrink: 0;
    opacity: .65;
}

.cat-deep__link:hover .cat-deep__icon,
.cat-deep__link.is-active .cat-deep__icon { opacity: 1; }

/* ── Mobile ── */
@media (max-width: 576px) {
    :root { --cs-width: 280px; }

    .cat-group__toggle { padding: 12px 14px; }
    .cat-leaf__link,
    .cat-sub__link     { padding-left: 42px; }
    .cat-deep__link    { padding-left: 56px; }
}
</style>


{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar  = document.getElementById('categorySidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const openBtn  = document.getElementById('openSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        /* ── Open / Close ── */
        function open()  {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        openBtn  && openBtn.addEventListener('click', open);
        closeBtn && closeBtn.addEventListener('click', close);
        overlay  && overlay.addEventListener('click', close);

        /* ── Top-level group accordion ── */
        document.querySelectorAll('.cat-group__toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const group     = btn.closest('.cat-group');
                const isOpen    = group.classList.contains('is-open');

                // Close all siblings
                group.closest('.cat-sidebar__accordion')
                     .querySelectorAll('.cat-group')
                     .forEach(function (g) {
                         g.classList.remove('is-open');
                         g.querySelector('.cat-group__toggle').setAttribute('aria-expanded', 'false');
                     });

                if (!isOpen) {
                    group.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        /* ── Sub-category toggle (3rd level) ── */
        document.querySelectorAll('.cat-sub__toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const sub    = btn.closest('.cat-sub');
                const panel  = sub.querySelector('.cat-sub__panel');
                const isOpen = sub.classList.contains('is-open');

                sub.classList.toggle('is-open', !isOpen);
                panel && panel.classList.toggle('is-open', !isOpen);
                btn.setAttribute('aria-expanded', (!isOpen).toString());
            });
        });

        /* ── Keyboard: close on Escape ── */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) close();
        });
    });
})();
</script>