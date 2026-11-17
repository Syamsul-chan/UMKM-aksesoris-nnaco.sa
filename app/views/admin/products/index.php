<?php
    $userRole = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = $baseRoute ?? (($userRole == 'owner') ? BASEURL . '/owner' : (($userRole == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin'));
    $categories = $categories ?? [];
    $filters = $filters ?? [
        'q' => $search ?? '',
        'category_id' => '',
        'status' => 'all',
        'stock_status' => 'all',
        'sort' => 'newest',
        'is_promo' => ''
    ];
    $csrfToken = Controller::generateCsrfToken();
?>

<!-- Custom Styling for AJAX Product Management -->
<style>
    .kw-filter-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #ede7f6;
        box-shadow: 0 10px 25px rgba(166, 132, 220, 0.08);
        padding: 22px;
        transition: all 0.3s ease;
    }
    .kw-filter-card:hover {
        box-shadow: 0 12px 30px rgba(166, 132, 220, 0.14);
    }
    .kw-input, .kw-select {
        border-radius: 12px;
        border: 1.5px solid #e2d9f3;
        padding: 10px 14px;
        font-size: 0.92rem;
        background-color: #faf8ff;
        color: #4A2E35;
        transition: all 0.2s ease;
    }
    .kw-input:focus, .kw-select:focus {
        border-color: #a684dc;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(166, 132, 220, 0.2);
        outline: none;
    }
    .kw-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e0d4f5;
        background: #f7f3ff;
        color: #6a4c9c;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    .kw-chip:hover {
        background: #ebe2fc;
        transform: translateY(-1px);
        color: #4a2e7c;
    }
    .kw-chip.active {
        background: linear-gradient(135deg, #a684dc 0%, #8e62cf 100%);
        color: #ffffff;
        border-color: #8e62cf;
        box-shadow: 0 4px 10px rgba(166, 132, 220, 0.35);
    }
    .kw-badge-ajax {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .kw-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseAnimation 2s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .kw-table-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #ede7f6;
        box-shadow: 0 10px 25px rgba(166, 132, 220, 0.06);
        overflow: hidden;
        position: relative;
    }
    .kw-table thead {
        background: #fbf9fe;
        border-bottom: 2px solid #ede7f6;
    }
    .kw-table thead th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6b5876;
        font-weight: 700;
        padding: 16px 20px;
        border: none;
    }
    .kw-table tbody tr {
        transition: background-color 0.15s ease;
        border-bottom: 1px solid #f6f2fa;
    }
    .kw-table tbody tr:hover {
        background-color: #fcfaff;
    }
    .kw-table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        color: #382430;
    }
    .kw-btn-add {
        background: linear-gradient(135deg, #a684dc 0%, #8e62cf 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 12px rgba(166, 132, 220, 0.3);
        transition: all 0.25s ease;
        text-decoration: none;
    }
    .kw-btn-add:hover {
        background: linear-gradient(135deg, #9670d4 0%, #7d4ec7 100%);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(166, 132, 220, 0.4);
    }
    .kw-empty-box {
        padding: 50px 20px;
        text-align: center;
    }
    .kw-toast-alert {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 9999;
        min-width: 280px;
        max-width: 380px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-radius: 14px;
        display: none;
        animation: slideUpFade 0.3s ease;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .loading-shimmer {
        opacity: 0.4;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-dark">Manajemen Produk</h4>
            <span class="kw-badge-ajax">
                <span class="kw-pulse-dot"></span> Filter AJAX
            </span>
        </div>
        <p class="text-muted small mb-0">Kelola katalog produk, harga, stok, dan status secara realtime tanpa reload halaman.</p>
    </div>
    <a href="<?= $baseRoute ?>/productCreate" class="kw-btn-add d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle-fill"></i> Tambah Produk
    </a>
</div>


<!-- Main AJAX Filter Toolbar -->
<div class="kw-filter-card mb-4">
    <!-- Top Filter Controls -->
    <div class="row g-3 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-lg-4">
            <label class="form-label small fw-bold text-muted mb-1">Pencarian Cepat</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-3 text-muted" style="border-color: #e2d9f3;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="ajaxSearchInput" class="form-control kw-input border-start-0 rounded-0" 
                       placeholder="Cari nama atau deskripsi produk..." 
                       value="<?= htmlspecialchars($filters['q']) ?>" autocomplete="off">
                <button type="button" id="ajaxSearchClear" class="btn bg-white border border-start-0 text-muted rounded-end-3" 
                        style="border-color: #e2d9f3; display: <?= !empty($filters['q']) ? 'block' : 'none' ?>;" title="Hapus pencarian">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </div>

        <!-- Category Dropdown -->
        <div class="col-6 col-md-3 col-lg-2">
            <label class="form-label small fw-bold text-muted mb-1">Kategori</label>
            <select id="ajaxCategorySelect" class="form-select kw-select">
                <option value="">Semua Kategori</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Status Dropdown -->
        <div class="col-6 col-md-3 col-lg-2">
            <label class="form-label small fw-bold text-muted mb-1">Status Produk</label>
            <select id="ajaxStatusSelect" class="form-select kw-select">
                <option value="all" <?= ($filters['status'] === 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="active" <?= ($filters['status'] === 'active' || $filters['status'] === '1') ? 'selected' : '' ?>>🟢 Aktif</option>
                <option value="inactive" <?= ($filters['status'] === 'inactive' || $filters['status'] === '0') ? 'selected' : '' ?>>⚪ Nonaktif</option>
            </select>
        </div>

        <!-- Stock Status Dropdown -->
        <div class="col-6 col-md-3 col-lg-2">
            <label class="form-label small fw-bold text-muted mb-1">Status Stok</label>
            <select id="ajaxStockSelect" class="form-select kw-select">
                <option value="all" <?= ($filters['stock_status'] === 'all') ? 'selected' : '' ?>>Semua Stok</option>
                <option value="available" <?= ($filters['stock_status'] === 'available') ? 'selected' : '' ?>>📦 Tersedia (&gt; 0)</option>
                <option value="low" <?= ($filters['stock_status'] === 'low') ? 'selected' : '' ?>>⚠️ Stok Menipis (&le; 5)</option>
                <option value="empty" <?= ($filters['stock_status'] === 'empty') ? 'selected' : '' ?>>❌ Habis (0)</option>
            </select>
        </div>

        <!-- Sorting Dropdown -->
        <div class="col-6 col-md-3 col-lg-2">
            <label class="form-label small fw-bold text-muted mb-1">Urutkan</label>
            <select id="ajaxSortSelect" class="form-select kw-select">
                <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>🕒 Terbaru</option>
                <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>⏳ Terlama</option>
                <option value="price_asc" <?= ($filters['sort'] === 'price_asc') ? 'selected' : '' ?>>💰 Harga Terendah</option>
                <option value="price_desc" <?= ($filters['sort'] === 'price_desc') ? 'selected' : '' ?>>💎 Harga Tertinggi</option>
                <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>🔤 Nama A - Z</option>
                <option value="name_desc" <?= ($filters['sort'] === 'name_desc') ? 'selected' : '' ?>>🔤 Nama Z - A</option>
                <option value="stock_desc" <?= ($filters['sort'] === 'stock_desc') ? 'selected' : '' ?>>📊 Stok Terbanyak</option>
                <option value="stock_asc" <?= ($filters['sort'] === 'stock_asc') ? 'selected' : '' ?>>📉 Stok Tersedikit</option>
            </select>
        </div>
    </div>

    <!-- Quick Filter Chips & Reset Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top border-light-subtle gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-bold text-muted me-1"><i class="bi bi-funnel-fill text-primary"></i> Filter Cepat:</span>
            <button type="button" class="kw-chip active" data-chip="all">Semua</button>
            <button type="button" class="kw-chip" data-chip="low_stock">⚠️ Stok Menipis (&le; 5)</button>
            <button type="button" class="kw-chip" data-chip="out_of_stock">❌ Stok Habis</button>
            <button type="button" class="kw-chip" data-chip="promo">🏷️ Produk Promo</button>
            <button type="button" class="kw-chip" data-chip="inactive">⚪ Nonaktif</button>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <!-- AJAX Fetch Indicator & Product Counter -->
            <div class="d-flex align-items-center gap-2">
                <div id="ajaxSpinner" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-normal" style="font-size: 0.82rem;">
                    Menampilkan <strong id="productCountBadge"><?= $pagination['total_products'] ?? count($products) ?></strong> produk
                </span>
            </div>

            <!-- Reset Button -->
            <button type="button" id="ajaxResetBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-flex align-items-center gap-1">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
            </button>
        </div>
    </div>
</div>

<!-- Table Card Container -->
<div class="kw-table-card mb-4" id="tableCardContainer">
    <div class="table-responsive">
        <table class="table kw-table align-middle mb-0" id="productsTable">
            <thead>
                <tr>
                    <th class="ps-4" style="min-width: 260px;">Produk</th>
                    <th style="min-width: 140px;">Kategori</th>
                    <th style="min-width: 130px;">Harga</th>
                    <th style="min-width: 90px;">Stok</th>
                    <th style="min-width: 100px;">Status</th>
                    <th class="pe-4 text-center" style="min-width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                <?php if(empty($products)): ?>
                    <tr id="emptyTableRow">
                        <td colspan="6" class="kw-empty-box">
                            <div class="text-center py-4">
                                <div class="display-6 text-muted mb-2">｡•́︿•̀｡</div>
                                <h6 class="fw-bold text-dark">Tidak ada produk ditemukan</h6>
                                <p class="text-muted small mb-3">Coba gunakan kata kunci atau ubah pengaturan filter di atas.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="resetAllFilters()">Reset Filter</button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                        <tr id="product-row-<?= $product['id'] ?>" class="product-row">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($product['image'])): ?>
                                        <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($product['image']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="rounded-3 me-3 border" 
                                             style="width: 44px; height: 44px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='https://placehold.co/80x80/f3ebf7/8e62cf?text=nnaco';">
                                    <?php else: ?>
                                        <div class="rounded-3 text-white d-flex align-items-center justify-content-center me-3 fw-bold" 
                                             style="width: 44px; height: 44px; font-size: 0.8rem; background: linear-gradient(135deg, #BEA5DD, #A684DC);">
                                            <?= strtoupper(substr($product['name'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($product['name']) ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </div>
                                        <?php if(!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.7rem;">
                                                <i class="bi bi-tag-fill"></i> Diskon <?= $product['discount_percent'] ?>%
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" style="font-size: 0.8rem;">
                                    <?= htmlspecialchars($product['category_name'] ?? 'Umum') ?>
                                </span>
                            </td>
                            <td>
                                <?php if(!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                                    <?php 
                                        $finalPrice = round($product['price'] * (1 - ($product['discount_percent'] / 100))); 
                                    ?>
                                    <div class="fw-bold text-danger">Rp <?= number_format($finalPrice, 0, ',', '.') ?></div>
                                    <div class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </div>
                                <?php else: ?>
                                    <div class="fw-semibold text-dark">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($product['stock'] <= 0): ?>
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                        Habis (0)
                                    </span>
                                <?php elseif($product['stock'] <= 5): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                        ⚠️ <?= $product['stock'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="fw-semibold text-secondary">
                                        <?= $product['stock'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill px-2 py-1" 
                                      id="badge-status-<?= $product['id'] ?>" style="font-size: 0.78rem;">
                                    <?= $product['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group shadow-0" role="group">
                                    <a href="<?= $baseRoute ?>/productEdit/<?= $product['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary rounded-start-pill px-2" title="Edit Produk">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm <?= $product['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-end-pill px-2 btn-ajax-toggle" 
                                            data-id="<?= $product['id'] ?>" 
                                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                            data-status="<?= $product['is_active'] ? '1' : '0' ?>"
                                            title="Ubah Status (AJAX)">
                                        <span class="spinner-border spinner-border-sm me-1 toggle-spinner" style="display: none;" role="status"></span>
                                        <span class="toggle-text"><?= $product['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- AJAX Pagination Container -->
<div id="ajaxPaginationWrapper" class="d-flex justify-content-center mb-4">
    <?php if(($pagination['total_pages'] ?? 1) > 1): ?>
        <nav aria-label="Navigasi Halaman Produk">
            <ul class="pagination pagination-sm shadow-sm gap-1 mb-0">
                <li class="page-item <?= ($pagination['current_page'] <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill px-3" href="#" data-page="<?= max(1, $pagination['current_page'] - 1) ?>">
                        <i class="bi bi-chevron-left"></i> Prev
                    </a>
                </li>
                <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?= ($i == $pagination['current_page']) ? 'active' : '' ?>">
                        <a class="page-link rounded-circle text-center" style="width: 34px; height: 34px; line-height: 20px;" href="#" data-page="<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($pagination['current_page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill px-3" href="#" data-page="<?= min($pagination['total_pages'], $pagination['current_page'] + 1) ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Floating Realtime AJAX Toast Alert -->
<div id="ajaxToast" class="toast align-items-center text-white bg-dark border-0 kw-toast-alert" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex p-2">
        <div class="toast-body d-flex align-items-center gap-2">
            <i id="ajaxToastIcon" class="bi bi-check-circle-fill text-success fs-5"></i>
            <span id="ajaxToastMessage" class="small">Perubahan status berhasil disimpan via AJAX.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<!-- JavaScript Client-Side AJAX Engine -->
<script>
(function() {
    // Configuration & State
    const BASE_ROUTE = "<?= $baseRoute ?>";
    const BASE_URL = "<?= BASEURL ?>";
    let csrfToken = "<?= $csrfToken ?>";
    let currentPage = <?= (int)($pagination['current_page'] ?? 1) ?>;
    let debounceTimer = null;
    let activePromoOnly = <?= !empty($filters['is_promo']) ? 'true' : 'false' ?>;

    // DOM Elements
    const searchInput = document.getElementById('ajaxSearchInput');
    const searchClear = document.getElementById('ajaxSearchClear');
    const categorySelect = document.getElementById('ajaxCategorySelect');
    const statusSelect = document.getElementById('ajaxStatusSelect');
    const stockSelect = document.getElementById('ajaxStockSelect');
    const sortSelect = document.getElementById('ajaxSortSelect');
    const resetBtn = document.getElementById('ajaxResetBtn');
    const tableBody = document.getElementById('productsTableBody');
    const tableCard = document.getElementById('tableCardContainer');
    const paginationWrapper = document.getElementById('ajaxPaginationWrapper');
    const countBadge = document.getElementById('productCountBadge');
    const spinner = document.getElementById('ajaxSpinner');
    const chips = document.querySelectorAll('.kw-chip');
    const toastEl = document.getElementById('ajaxToast');
    const toastMsg = document.getElementById('ajaxToastMessage');
    const toastIcon = document.getElementById('ajaxToastIcon');
    let toastInstance = null;

    if (window.bootstrap && bootstrap.Toast) {
        toastInstance = new bootstrap.Toast(toastEl, { delay: 3500 });
    }

    function showToast(message, isSuccess = true) {
        if (!toastInstance) return;
        toastMsg.textContent = message;
        if (isSuccess) {
            toastEl.className = 'toast align-items-center text-white bg-dark border-0 kw-toast-alert';
            toastIcon.className = 'bi bi-check-circle-fill text-success fs-5';
        } else {
            toastEl.className = 'toast align-items-center text-white bg-danger border-0 kw-toast-alert';
            toastIcon.className = 'bi bi-exclamation-triangle-fill text-warning fs-5';
        }
        toastInstance.show();
    }

    // Escape HTML helper to prevent XSS
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Format Rupiah helper
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Main AJAX Fetch Function
    function fetchProducts(page = 1) {
        currentPage = page;
        spinner.style.display = 'inline-block';
        tableCard.classList.add('loading-shimmer');

        const params = new URLSearchParams({
            ajax: '1',
            q: searchInput.value.trim(),
            category_id: categorySelect.value,
            status: statusSelect.value,
            stock_status: stockSelect.value,
            sort: sortSelect.value,
            is_promo: activePromoOnly ? '1' : '',
            page: currentPage
        });

        // Sync browser URL without reloading
        const cleanUrlParams = new URLSearchParams(params);
        cleanUrlParams.delete('ajax');
        const newUrl = `${BASE_ROUTE}/products?${cleanUrlParams.toString()}`;
        window.history.pushState(null, '', newUrl);

        fetch(`${BASE_ROUTE}/products?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }
                renderTableRows(data.products || []);
                renderPagination(data.pagination);
                countBadge.textContent = data.pagination.total_products;
            } else {
                showToast('Gagal memuat data produk.', false);
            }
        })
        .catch(err => {
            console.error('AJAX Filter Error:', err);
            showToast('Terjadi kesalahan saat memfilter produk.', false);
        })
        .finally(() => {
            spinner.style.display = 'none';
            tableCard.classList.remove('loading-shimmer');
        });
    }

    // Render Table Rows Dynamically
    function renderTableRows(products) {
        tableBody.innerHTML = '';

        if (!products || products.length === 0) {
            tableBody.innerHTML = `
                <tr id="emptyTableRow">
                    <td colspan="6" class="kw-empty-box">
                        <div class="text-center py-4">
                            <div class="display-6 text-muted mb-2">｡•́︿•̀｡</div>
                            <h6 class="fw-bold text-dark">Tidak ada produk ditemukan</h6>
                            <p class="text-muted small mb-3">Tidak ada produk yang cocok dengan kombinasi filter yang dipilih.</p>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="window.resetAllFilters()">Reset Filter</button>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        products.forEach(product => {
            const row = document.createElement('tr');
            row.id = `product-row-${product.id}`;
            row.className = 'product-row';

            // Image or Initials
            let imageHtml = '';
            if (product.image) {
                imageHtml = `<img src="${BASE_URL}/img/products/${escapeHtml(product.image)}" 
                                  alt="${escapeHtml(product.name)}" 
                                  class="rounded-3 me-3 border" 
                                  style="width: 44px; height: 44px; object-fit: cover;"
                                  onerror="this.onerror=null; this.src='https://placehold.co/80x80/f3ebf7/8e62cf?text=nnaco';">`;
            } else {
                const initials = escapeHtml((product.name || 'PR').substring(0, 2).toUpperCase());
                imageHtml = `<div class="rounded-3 text-white d-flex align-items-center justify-content-center me-3 fw-bold" 
                                  style="width: 44px; height: 44px; font-size: 0.8rem; background: linear-gradient(135deg, #BEA5DD, #A684DC);">
                                ${initials}
                             </div>`;
            }

            // Promo Badge
            let promoBadge = '';
            let priceHtml = '';
            const isPromo = (product.is_promo == 1 && product.discount_percent > 0);
            if (isPromo) {
                promoBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.7rem;">
                                <i class="bi bi-tag-fill"></i> Diskon ${product.discount_percent}%
                              </span>`;
                const finalPrice = Math.round(product.price * (1 - (product.discount_percent / 100)));
                priceHtml = `
                    <div class="fw-bold text-danger">Rp ${formatRupiah(finalPrice)}</div>
                    <div class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">
                        Rp ${formatRupiah(product.price)}
                    </div>
                `;
            } else {
                priceHtml = `<div class="fw-semibold text-dark">Rp ${formatRupiah(product.price)}</div>`;
            }

            // Stock Badge
            let stockHtml = '';
            const stockNum = parseInt(product.stock, 10);
            if (stockNum <= 0) {
                stockHtml = `<span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.75rem;">Habis (0)</span>`;
            } else if (stockNum <= 5) {
                stockHtml = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;">⚠️ ${stockNum}</span>`;
            } else {
                stockHtml = `<span class="fw-semibold text-secondary">${stockNum}</span>`;
            }

            // Active Status Badge & Button
            const isActive = (parseInt(product.is_active, 10) === 1);
            const statusBadgeClass = isActive ? 'bg-success' : 'bg-secondary';
            const statusText = isActive ? 'Aktif' : 'Nonaktif';
            const toggleBtnClass = isActive ? 'btn-outline-danger' : 'btn-outline-success';
            const toggleBtnText = isActive ? 'Nonaktifkan' : 'Aktifkan';

            row.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        ${imageHtml}
                        <div>
                            <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="${escapeHtml(product.name)}">
                                ${escapeHtml(product.name)}
                            </div>
                            ${promoBadge}
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" style="font-size: 0.8rem;">
                        ${escapeHtml(product.category_name || 'Umum')}
                    </span>
                </td>
                <td>${priceHtml}</td>
                <td>${stockHtml}</td>
                <td>
                    <span class="badge ${statusBadgeClass} rounded-pill px-2 py-1" id="badge-status-${product.id}" style="font-size: 0.78rem;">
                        ${statusText}
                    </span>
                </td>
                <td class="pe-4 text-center">
                    <div class="btn-group shadow-0" role="group">
                        <a href="${BASE_ROUTE}/productEdit/${product.id}" class="btn btn-sm btn-outline-primary rounded-start-pill px-2" title="Edit Produk">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <button type="button" 
                                class="btn btn-sm ${toggleBtnClass} rounded-end-pill px-2 btn-ajax-toggle" 
                                data-id="${product.id}" 
                                data-name="${escapeHtml(product.name)}"
                                data-status="${isActive ? '1' : '0'}"
                                title="Ubah Status (AJAX)">
                            <span class="spinner-border spinner-border-sm me-1 toggle-spinner" style="display: none;" role="status"></span>
                            <span class="toggle-text">${toggleBtnText}</span>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    // Render AJAX Pagination
    function renderPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            paginationWrapper.innerHTML = '';
            return;
        }

        const totalPages = pagination.total_pages;
        const current = pagination.current_page;

        let html = `
            <nav aria-label="Navigasi Halaman Produk">
                <ul class="pagination pagination-sm shadow-sm gap-1 mb-0">
                    <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                        <a class="page-link rounded-pill px-3" href="#" data-page="${Math.max(1, current - 1)}">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            // Keep page links reasonable if many pages
            if (totalPages > 7) {
                if (i !== 1 && i !== totalPages && Math.abs(i - current) > 2) {
                    if (Math.abs(i - current) === 3) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    continue;
                }
            }

            html += `
                <li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link rounded-circle text-center" style="width: 34px; height: 34px; line-height: 20px;" href="#" data-page="${i}">
                        ${i}
                    </a>
                </li>
            `;
        }

        html += `
                    <li class="page-item ${current >= totalPages ? 'disabled' : ''}">
                        <a class="page-link rounded-pill px-3" href="#" data-page="${Math.min(totalPages, current + 1)}">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        `;

        paginationWrapper.innerHTML = html;
    }

    // AJAX Toggle Product Status (Aktifkan / Nonaktifkan)
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.btn-ajax-toggle');
        if (!toggleBtn) return;

        e.preventDefault();
        const id = toggleBtn.dataset.id;
        const name = toggleBtn.dataset.name;
        const currentStatus = parseInt(toggleBtn.dataset.status, 10);
        const newStatus = (currentStatus === 1) ? 0 : 1;

        const spinnerSpan = toggleBtn.querySelector('.toggle-spinner');
        const textSpan = toggleBtn.querySelector('.toggle-text');

        // Confirm change
        const actionLabel = (currentStatus === 1) ? 'menonaktifkan' : 'mengaktifkan';
        if (!confirm(`Apakah Anda yakin ingin ${actionLabel} produk "${name}"?`)) {
            return;
        }

        // Disable button & show spinner
        toggleBtn.disabled = true;
        if (spinnerSpan) spinnerSpan.style.display = 'inline-block';

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('status', newStatus);
        formData.append('ajax', '1');

        fetch(`${BASE_ROUTE}/productToggle/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP Status: ' + res.status);
            return res.json();
        })
        .then(result => {
            if (result.status === 'success') {
                if (result.csrf_token) {
                    csrfToken = result.csrf_token;
                }

                // Update row UI
                toggleBtn.dataset.status = newStatus;
                const badge = document.getElementById(`badge-status-${id}`);

                if (newStatus === 1) {
                    if (badge) {
                        badge.className = 'badge bg-success rounded-pill px-2 py-1';
                        badge.textContent = 'Aktif';
                    }
                    toggleBtn.className = 'btn btn-sm btn-outline-danger rounded-end-pill px-2 btn-ajax-toggle';
                    if (textSpan) textSpan.textContent = 'Nonaktifkan';
                    showToast(`✨ Produk "${name}" sekarang Aktif (Live AJAX).`, true);
                } else {
                    if (badge) {
                        badge.className = 'badge bg-secondary rounded-pill px-2 py-1';
                        badge.textContent = 'Nonaktif';
                    }
                    toggleBtn.className = 'btn btn-sm btn-outline-success rounded-end-pill px-2 btn-ajax-toggle';
                    if (textSpan) textSpan.textContent = 'Aktifkan';
                    showToast(`⚪ Produk "${name}" dinonaktifkan (Live AJAX).`, true);
                }
            } else {
                showToast(result.message || 'Gagal mengubah status produk.', false);
            }
        })
        .catch(err => {
            console.error('AJAX Toggle Error:', err);
            showToast('Terjadi kesalahan saat mengubah status produk.', false);
        })
        .finally(() => {
            toggleBtn.disabled = false;
            if (spinnerSpan) spinnerSpan.style.display = 'none';
        });
    });

    // Pagination Link Clicks via AJAX
    paginationWrapper.addEventListener('click', function(e) {
        const link = e.target.closest('a[data-page]');
        if (!link) return;

        e.preventDefault();
        const page = parseInt(link.dataset.page, 10);
        if (!isNaN(page) && page !== currentPage) {
            fetchProducts(page);
            tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // Search Input with Debounce (300ms)
    searchInput.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchProducts(1);
        }, 300);
    });

    // Clear Search Input
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        fetchProducts(1);
        searchInput.focus();
    });

    // Select Change Handlers
    categorySelect.addEventListener('change', () => fetchProducts(1));
    statusSelect.addEventListener('change', () => {
        updateActiveChip();
        fetchProducts(1);
    });
    stockSelect.addEventListener('change', () => {
        updateActiveChip();
        fetchProducts(1);
    });
    sortSelect.addEventListener('change', () => fetchProducts(1));

    // Quick Filter Chips Handler
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            chips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.chip;
            activePromoOnly = false;

            if (type === 'all') {
                statusSelect.value = 'all';
                stockSelect.value = 'all';
            } else if (type === 'low_stock') {
                stockSelect.value = 'low';
                statusSelect.value = 'all';
            } else if (type === 'out_of_stock') {
                stockSelect.value = 'empty';
                statusSelect.value = 'all';
            } else if (type === 'promo') {
                activePromoOnly = true;
                statusSelect.value = 'all';
                stockSelect.value = 'all';
            } else if (type === 'inactive') {
                statusSelect.value = 'inactive';
                stockSelect.value = 'all';
            }

            fetchProducts(1);
        });
    });

    function updateActiveChip() {
        // Un-highlight chips if user adjusts specific dropdowns
        chips.forEach(c => c.classList.remove('active'));
        if (statusSelect.value === 'all' && stockSelect.value === 'all' && !activePromoOnly) {
            const allChip = document.querySelector('.kw-chip[data-chip="all"]');
            if (allChip) allChip.classList.add('active');
        }
    }

    // Reset All Filters
    window.resetAllFilters = function() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        categorySelect.value = '';
        statusSelect.value = 'all';
        stockSelect.value = 'all';
        sortSelect.value = 'newest';
        activePromoOnly = false;

        chips.forEach(c => c.classList.remove('active'));
        const allChip = document.querySelector('.kw-chip[data-chip="all"]');
        if (allChip) allChip.classList.add('active');

        fetchProducts(1);
    };

    resetBtn.addEventListener('click', window.resetAllFilters);

})();
</script>
