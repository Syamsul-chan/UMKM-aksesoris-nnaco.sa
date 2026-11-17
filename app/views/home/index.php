<main class="d-main-content">
    
    <!-- FULL INTERACTIVE CATALOGUE WITH REALTIME AJAX (#katalog) -->
    <div class="d-katalog-container" id="katalog">
        <div class="d-katalog-header">
            <div>
                <h4 class="d-katalog-title">🌸 Koleksi Aksesoris Toko nnaco.sa</h4>
                <p class="text-muted small mb-0 fw-semibold">Temukan koleksi perhiasan & aksesoris pastel imut terlengkap.</p>
            </div>
            
            <!-- Category Pills Filter (AJAX) -->
            <div class="d-flex gap-2 flex-wrap" id="categoryPillsContainer">
                <a href="<?= BASEURL ?>#katalog" class="d-category-pill <?= empty($filters['cat']) ? 'active' : '' ?>" data-category-id="">
                    Semua Kategori
                </a>
                <?php if(!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= BASEURL ?>?cat=<?= $cat['id'] ?>#katalog" class="d-category-pill <?= (isset($filters['cat']) && $filters['cat'] == $cat['id']) ? 'active' : '' ?>" data-category-id="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar with Live Debounce AJAX & Clear Button -->
        <div class="d-search-bar-wrap position-relative mb-3">
            <form id="katalogSearchForm" onsubmit="event.preventDefault(); fetchProductsAjax(1);">
                <i id="searchIcon" class="bi bi-search d-search-icon-kawaii"></i>
                <div id="searchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute" style="left: 20px; top: 19px; display: none; width: 1.15rem; height: 1.15rem; z-index: 5;" role="status"></div>
                <input type="text" id="katalogSearchInput" class="d-search-input-kawaii" placeholder="Cari nama jepit rambut, gantungan kunci, kalung, atau cincin imut..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" autocomplete="off">
                <button type="button" id="clearSearchBtn" class="btn btn-sm text-muted position-absolute" style="right: 18px; top: 12px; display: <?= !empty($filters['q']) ? 'inline-block' : 'none' ?>; border: none; background: transparent; font-size: 1.25rem; line-height: 1;" title="Hapus pencarian">&times;</button>
            </form>
        </div>

        <!-- Realtime AJAX Status & Live Counter Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 px-1">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: #F3E8FF; color: #7C3AED; font-size: 0.74rem; font-weight: 700; padding: 5px 12px; border: 1px solid #E9D5FF;">
                    <span class="spinner-grow spinner-grow-sm text-purple" style="width: 7px; height: 7px;" role="status"></span>
                    <span>⚡ Filter & Live Search AJAX</span>
                </span>
                <span id="productCounterText" class="small text-muted fw-semibold">
                    Menampilkan <strong class="text-dark" id="countCurrent"><?= count($products) ?></strong> dari <strong class="text-dark" id="countTotal"><?= $pagination['total_products'] ?></strong> aksesoris
                </span>
            </div>
            
            <div id="activeFilterWrap" style="display: <?= (!empty($filters['q']) || !empty($filters['cat'])) ? 'block' : 'none' ?>;">
                <button type="button" id="resetAllFiltersBtn" class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1" style="background: #FFF1F2; color: #E11D48; border: 1.5px solid #FECDD3; font-size: 0.76rem;">
                    <i class="bi bi-x-circle-fill"></i> Reset Filter
                </button>
            </div>
        </div>

        <!-- Products Grid Container -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-4" id="productsGrid" style="transition: opacity 0.2s ease;">
            <?php if(empty($products)): ?>
                <div class="col-12 w-100 text-center py-5" id="emptyStateCol">
                    <i class="bi bi-heartbreak fs-1 text-muted d-block mb-2"></i>
                    <h5 class="text-muted fw-bold">Produk tidak ditemukan</h5>
                    <p class="text-muted small">Coba cari dengan kata kunci lain atau pilih kategori berbeda.</p>
                    <button type="button" class="d-category-pill active d-inline-block border-0" onclick="resetFilters()">Reset Pencarian</button>
                </div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <?php 
                        $isPromo = (!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0);
                        $hasImage = (!empty($product['image']) && file_exists('img/products/'.$product['image']));
                    ?>
                    <div class="col product-item-col" data-id="<?= $product['id'] ?>">
                        <div class="card h-100 border-0 overflow-hidden" style="border-radius: 22px; box-shadow: 0 6px 20px rgba(175, 145, 205, 0.12); background: #FFFFFF; transition: transform 0.25s, box-shadow 0.25s;">
                            <!-- Product Image & Badges -->
                            <div style="height: 180px; background: #FAF5FF; display: flex; align-items: center; justify-content: center; position: relative;">
                                <?php if($hasImage): ?>
                                    <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-height: 82%; max-width: 82%; object-fit: contain;">
                                <?php else: ?>
                                    <i class="bi bi-gem text-muted" style="font-size: 3rem; color: #D6B4FC !important;"></i>
                                <?php endif; ?>
                                
                                <?php if($isPromo): ?>
                                    <span class="badge position-absolute top-0 start-0 m-2 rounded-pill px-2.5 py-1 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #ef4444, #f43f5e); font-size: 0.72rem;">
                                        🔥 <?= $product['discount_percent'] ?>% OFF
                                    </span>
                                <?php endif; ?>
                                
                                <span class="badge position-absolute top-0 end-0 m-2 rounded-pill px-3 py-1 fw-bold" style="background: rgba(255,255,255,0.92); color: #7E708E; font-size: 0.72rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                    Stok: <?= $product['stock'] ?>
                                </span>
                            </div>

                            <!-- Product Details & Buttons -->
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="fw-bold mb-1 text-truncate" style="color: #4A2E35; font-size: 0.92rem;" title="<?= htmlspecialchars($product['name']) ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h6>
                                
                                <?php if($isPromo): ?>
                                    <div class="mb-3">
                                        <span class="text-muted text-decoration-line-through small me-1">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                        <span class="fw-bold" style="font-size: 1.05rem; color: #dc2626;">Rp <?= number_format($product['promo_price'], 0, ',', '.') ?></span>
                                    </div>
                                <?php else: ?>
                                    <p class="fw-bold mb-3" style="font-size: 1.05rem; color: #A684DC;">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Action Buttons: Beli & Quick Cart -->
                                <div class="mt-auto d-flex gap-2">
                                    <a href="<?= BASEURL ?>/home/detail/<?= $product['id'] ?>" class="d-btn-pill-buy flex-grow-1 py-2 text-center text-decoration-none">
                                        Detail
                                    </a>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button type="button" class="btn btn-sm btn-quick-cart rounded-pill px-2.5 py-2 fw-bold text-white d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #EC4899 0%, #F43F5E 100%); min-width: 44px; border: none; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.25);" title="Tambah Cepat ke Keranjang" onclick="quickAddToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', this)">
                                            <i class="bi bi-cart-plus-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- AJAX Pagination -->
        <div id="paginationContainer">
            <?php if(isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
                <div class="d-flex justify-content-center mt-3">
                    <nav>
                        <ul class="pagination pagination-sm gap-1">
                            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= ($pagination['current_page'] ?? 1) == $i ? 'active' : '' ?>">
                                    <a class="page-link rounded-circle text-center fw-bold pagination-ajax-link" style="width: 36px; height: 36px; line-height: 22px; <?= ($pagination['current_page'] ?? 1) == $i ? 'background: #A684DC; border-color: #A684DC; color: #fff;' : 'color: #7E708E;' ?>" href="javascript:void(0)" onclick="fetchProductsAjax(<?= $i ?>)">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Floating Kawaii Toast Notification Component -->
<div id="kawaiiToastBox" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; pointer-events: none;"></div>

<!-- Hidden CSRF Token for Customer Actions -->
<input type="hidden" id="customerCsrfToken" value="<?= Controller::generateCsrfToken() ?>">

<!-- =========================================================================
     JAVASCRIPT REALTIME AJAX ENGINE FOR CUSTOMER CATALOGUE
     ========================================================================= -->
<script>
(function() {
    'use strict';

    // State Variables
    let currentCategory = '<?= htmlspecialchars($filters['cat'] ?? '') ?>';
    let searchQuery = '<?= htmlspecialchars($filters['q'] ?? '') ?>';
    let currentPage = <?= (int)($pagination['current_page'] ?? 1) ?>;
    let searchDebounceTimer = null;
    let isFetching = false;

    const BASE_URL = '<?= BASEURL ?>';
    const searchInput = document.getElementById('katalogSearchInput');
    const searchSpinner = document.getElementById('searchSpinner');
    const searchIcon = document.getElementById('searchIcon');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const productsGrid = document.getElementById('productsGrid');
    const paginationContainer = document.getElementById('paginationContainer');
    const activeFilterWrap = document.getElementById('activeFilterWrap');
    const countCurrent = document.getElementById('countCurrent');
    const countTotal = document.getElementById('countTotal');
    const resetAllFiltersBtn = document.getElementById('resetAllFiltersBtn');
    const csrfTokenInput = document.getElementById('customerCsrfToken');

    // 1. Debounced Live Search Event
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value.trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchQuery.length > 0 ? 'inline-block' : 'none';
            }

            clearTimeout(searchDebounceTimer);
            showSearchLoading(true);
            searchDebounceTimer = setTimeout(function() {
                fetchProductsAjax(1);
            }, 300);
        });
    }

    // 2. Clear Search Button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            clearSearchBtn.style.display = 'none';
            fetchProductsAjax(1);
        });
    }

    // 3. Reset All Filters Button
    if (resetAllFiltersBtn) {
        resetAllFiltersBtn.addEventListener('click', function() {
            resetFilters();
        });
    }

    window.resetFilters = function() {
        currentCategory = '';
        searchQuery = '';
        if (searchInput) searchInput.value = '';
        if (clearSearchBtn) clearSearchBtn.style.display = 'none';

        // Reset pills UI
        document.querySelectorAll('#categoryPillsContainer .d-category-pill').forEach(pill => {
            if (pill.getAttribute('data-category-id') === '') {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });

        fetchProductsAjax(1);
    };

    // 4. Category Filter Click Delegation
    const categoryContainer = document.getElementById('categoryPillsContainer');
    if (categoryContainer) {
        categoryContainer.addEventListener('click', function(e) {
            const pill = e.target.closest('.d-category-pill');
            if (!pill) return;
            e.preventDefault();

            // Set active class
            document.querySelectorAll('#categoryPillsContainer .d-category-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');

            currentCategory = pill.getAttribute('data-category-id') || '';
            fetchProductsAjax(1);
        });
    }

    // 5. Main Fetch Function via AJAX
    window.fetchProductsAjax = function(page = 1) {
        if (isFetching) return;
        isFetching = true;
        currentPage = page;
        showSearchLoading(true);

        if (productsGrid) {
            if (window.NnacoSkeleton) {
                window.NnacoSkeleton.showIn(productsGrid, 8);
            } else {
                productsGrid.style.opacity = '0.5';
            }
        }

        // Build query URL
        const params = new URLSearchParams();
        params.set('ajax', '1');
        params.set('page', page);
        if (searchQuery) params.set('q', searchQuery);
        if (currentCategory) params.set('cat', currentCategory);

        const fetchUrl = BASE_URL + '?' + params.toString();

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                renderProductGrid(data.products);
                renderPagination(data.pagination);
                updateCounts(data.products.length, data.pagination.total_products);
                updateFilterToolbar();

                if (data.csrf_token && csrfTokenInput) {
                    csrfTokenInput.value = data.csrf_token;
                }

                // Update Browser URL without reload
                const cleanParams = new URLSearchParams();
                if (searchQuery) cleanParams.set('q', searchQuery);
                if (currentCategory) cleanParams.set('cat', currentCategory);
                if (page > 1) cleanParams.set('page', page);
                
                const newUrl = BASE_URL + (cleanParams.toString() ? '?' + cleanParams.toString() : '') + '#katalog';
                window.history.pushState({ q: searchQuery, cat: currentCategory, page: page }, '', newUrl);
            }
        })
        .catch(err => {
            console.error('AJAX Customer Catalog Error:', err);
            showKawaiiToast('⚠️ Terjadi kendala saat memuat katalog. Silakan coba lagi.', 'warning');
        })
        .finally(() => {
            isFetching = false;
            showSearchLoading(false);
            if (productsGrid) {
                productsGrid.style.opacity = '1.0';
            }
        });
    };

    // 6. Render Product Grid DOM
    function renderProductGrid(products) {
        if (!productsGrid) return;

        if (!products || products.length === 0) {
            productsGrid.innerHTML = `
                <div class="col-12 w-100 text-center py-5">
                    <i class="bi bi-heartbreak fs-1 text-muted d-block mb-2"></i>
                    <h5 class="text-muted fw-bold">Produk tidak ditemukan</h5>
                    <p class="text-muted small">Coba cari dengan kata kunci lain atau pilih kategori berbeda.</p>
                    <button type="button" class="d-category-pill active d-inline-block border-0" onclick="resetFilters()">Reset Pencarian</button>
                </div>
            `;
            return;
        }

        let html = '';
        products.forEach(p => {
            const hasImg = p.image_url && p.image_url.length > 0;
            const imgHtml = hasImg 
                ? `<img src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.name)}" style="max-height: 82%; max-width: 82%; object-fit: contain;">`
                : `<i class="bi bi-gem text-muted" style="font-size: 3rem; color: #D6B4FC !important;"></i>`;

            const promoBadge = p.is_promo 
                ? `<span class="badge position-absolute top-0 start-0 m-2 rounded-pill px-2.5 py-1 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #ef4444, #f43f5e); font-size: 0.72rem;">🔥 ${p.discount_percent}% OFF</span>`
                : '';

            const priceHtml = p.is_promo
                ? `<div class="mb-3">
                        <span class="text-muted text-decoration-line-through small me-1">${p.price_formatted}</span>
                        <span class="fw-bold" style="font-size: 1.05rem; color: #dc2626;">${p.promo_price_formatted}</span>
                   </div>`
                : `<p class="fw-bold mb-3" style="font-size: 1.05rem; color: #A684DC;">${p.price_formatted}</p>`;

            const quickCartBtn = p.stock > 0
                ? `<button type="button" class="btn btn-sm btn-quick-cart rounded-pill px-2.5 py-2 fw-bold text-white d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #EC4899 0%, #F43F5E 100%); min-width: 44px; border: none; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.25);" title="Tambah Cepat ke Keranjang" onclick="quickAddToCart(${p.id}, '${escapeQuote(p.name)}', this)">
                        <i class="bi bi-cart-plus-fill"></i>
                   </button>`
                : '';

            html += `
                <div class="col product-item-col" data-id="${p.id}">
                    <div class="card h-100 border-0 overflow-hidden" style="border-radius: 22px; box-shadow: 0 6px 20px rgba(175, 145, 205, 0.12); background: #FFFFFF; transition: transform 0.25s, box-shadow 0.25s;">
                        <div style="height: 180px; background: #FAF5FF; display: flex; align-items: center; justify-content: center; position: relative;">
                            ${imgHtml}
                            ${promoBadge}
                            <span class="badge position-absolute top-0 end-0 m-2 rounded-pill px-3 py-1 fw-bold" style="background: rgba(255,255,255,0.92); color: #7E708E; font-size: 0.72rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                                Stok: ${p.stock}
                            </span>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="fw-bold mb-1 text-truncate" style="color: #4A2E35; font-size: 0.92rem;" title="${escapeHtml(p.name)}">
                                ${escapeHtml(p.name)}
                            </h6>
                            ${priceHtml}
                            <div class="mt-auto d-flex gap-2">
                                <a href="${p.detail_url}" class="d-btn-pill-buy flex-grow-1 py-2 text-center text-decoration-none">
                                    Detail
                                </a>
                                ${quickCartBtn}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        productsGrid.innerHTML = html;
    }

    // 7. Render Pagination DOM
    function renderPagination(p) {
        if (!paginationContainer) return;

        if (!p || p.total_pages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="d-flex justify-content-center mt-3"><nav><ul class="pagination pagination-sm gap-1">';
        for (let i = 1; i <= p.total_pages; i++) {
            const isActive = i === p.current_page;
            const style = isActive 
                ? 'background: #A684DC; border-color: #A684DC; color: #fff;' 
                : 'color: #7E708E;';

            html += `
                <li class="page-item ${isActive ? 'active' : ''}">
                    <a class="page-link rounded-circle text-center fw-bold pagination-ajax-link" style="width: 36px; height: 36px; line-height: 22px; ${style}" href="javascript:void(0)" onclick="fetchProductsAjax(${i})">
                        ${i}
                    </a>
                </li>
            `;
        }
        html += '</ul></nav></div>';
        paginationContainer.innerHTML = html;
    }

    // 8. Update Counter and Toolbar UI
    function updateCounts(curr, total) {
        if (countCurrent) countCurrent.textContent = curr;
        if (countTotal) countTotal.textContent = total;
    }

    function updateFilterToolbar() {
        if (activeFilterWrap) {
            activeFilterWrap.style.display = (searchQuery || currentCategory) ? 'block' : 'none';
        }
    }

    function showSearchLoading(loading) {
        if (searchSpinner) searchSpinner.style.display = loading ? 'inline-block' : 'none';
        if (searchIcon) searchIcon.style.opacity = loading ? '0' : '1';
    }

    // 9. Quick Add to Cart via AJAX
    window.quickAddToCart = function(productId, productName, btnElement) {
        const originalContent = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', '1');
        formData.append('ajax', '1');
        formData.append('csrf_token', csrfTokenInput ? csrfTokenInput.value : '');

        fetch(BASE_URL + '/cart/add', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showKawaiiToast(res.message || '🌸 Produk berhasil dimasukkan ke keranjang!', 'success');
                updateCartBadges(res.cart_count);
                if (res.csrf_token && csrfTokenInput) {
                    csrfTokenInput.value = res.csrf_token;
                }
                if (btnElement) {
                    btnElement.innerHTML = '<i class="bi bi-check2"></i>';
                    btnElement.style.background = '#10B981';
                    setTimeout(() => {
                        btnElement.innerHTML = originalContent;
                        btnElement.style.background = 'linear-gradient(135deg, #EC4899 0%, #F43F5E 100%)';
                        btnElement.disabled = false;
                    }, 1400);
                }
            } else {
                showKawaiiToast(res.message || 'Gagal menambahkan produk.', 'warning');
                if (btnElement) {
                    btnElement.innerHTML = originalContent;
                    btnElement.disabled = false;
                }
            }
        })
        .catch(err => {
            console.error('AJAX Quick Cart Error:', err);
            showKawaiiToast('⚠️ Terjadi kesalahan saat menambahkan ke keranjang.', 'danger');
            if (btnElement) {
                btnElement.innerHTML = originalContent;
                btnElement.disabled = false;
            }
        });
    };

    // 10. Update Cart Badges across UI
    function updateCartBadges(count) {
        const navBadge = document.getElementById('navCartBadge');
        const sidebarBadge = document.getElementById('sidebarCartBadge');

        [navBadge, sidebarBadge].forEach(badge => {
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
                badge.style.transform = 'scale(1.35)';
                badge.style.transition = 'transform 0.25s ease';
                setTimeout(() => { badge.style.transform = 'scale(1.0)'; }, 250);
            }
        });
    }

    // 11. Kawaii Floating Toast Notification
    window.showKawaiiToast = function(message, type = 'success') {
        const toastBox = document.getElementById('kawaiiToastBox');
        if (!toastBox) return;

        const bgColors = {
            success: 'linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%)',
            warning: 'linear-gradient(135deg, #F59E0B 0%, #EF4444 100%)',
            danger: 'linear-gradient(135deg, #EF4444 0%, #DC2626 100%)'
        };

        const toastEl = document.createElement('div');
        toastEl.className = 'p-3 mb-2 text-white shadow-lg d-flex align-items-center justify-content-between gap-3';
        toastEl.style.cssText = `
            background: ${bgColors[type] || bgColors.success};
            border-radius: 20px;
            min-width: 300px;
            max-width: 380px;
            font-size: 0.88rem;
            font-weight: 600;
            pointer-events: auto;
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.35) !important;
            animation: slideInToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        `;

        toastEl.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-close-white btn-sm" aria-label="Close" onclick="this.parentElement.remove()"></button>
        `;

        toastBox.appendChild(toastEl);

        setTimeout(() => {
            toastEl.style.opacity = '0';
            toastEl.style.transform = 'translateY(15px)';
            toastEl.style.transition = 'opacity 0.35s, transform 0.35s';
            setTimeout(() => { toastEl.remove(); }, 350);
        }, 3500);
    };

    // Helper functions
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function escapeQuote(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // Handle Browser History Navigation (Back / Forward)
    window.addEventListener('popstate', function(e) {
        if (e.state) {
            searchQuery = e.state.q || '';
            currentCategory = e.state.cat || '';
            currentPage = e.state.page || 1;
            if (searchInput) searchInput.value = searchQuery;

            document.querySelectorAll('#categoryPillsContainer .d-category-pill').forEach(pill => {
                const catId = pill.getAttribute('data-category-id') || '';
                if (catId === currentCategory) {
                    pill.classList.add('active');
                } else {
                    pill.classList.remove('active');
                }
            });

            fetchProductsAjax(currentPage);
        }
    });

})();
</script>

<style>
@keyframes slideInToast {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

