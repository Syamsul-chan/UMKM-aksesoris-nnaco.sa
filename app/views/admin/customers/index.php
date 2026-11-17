<?php
    $userRole = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = $baseRoute ?? (($userRole == 'owner') ? BASEURL . '/owner' : (($userRole == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin'));
    $filters = $filters ?? [
        'q' => $search ?? '',
        'status' => 'all',
        'order_filter' => 'all',
        'sort' => 'newest'
    ];
    $stats = $stats ?? [
        'total' => count($customers ?? []),
        'active' => 0,
        'blocked' => 0,
        'with_orders' => 0,
        'total_spent' => 0
    ];
    $csrfToken = Controller::generateCsrfToken();
?>

<!-- Custom Styles for Customer Management -->
<style>
    /* Fix Bootstrap modal stacking context when inside .d-shell */
    .modal {
        z-index: 1060 !important;
    }
    .modal-backdrop {
        z-index: 1050 !important;
    }
    .modal-dialog {
        z-index: 1065 !important;
    }
    .kw-user-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #ede7f6;
        box-shadow: 0 10px 25px rgba(166, 132, 220, 0.08);
        padding: 22px;
        transition: all 0.3s ease;
    }
    .kw-user-card:hover {
        box-shadow: 0 12px 30px rgba(166, 132, 220, 0.14);
    }
    .kw-stat-box {
        background: #ffffff;
        border-radius: 18px;
        padding: 18px 20px;
        border: 1px solid #ede7f6;
        box-shadow: 0 6px 18px rgba(166, 132, 220, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .kw-stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(166, 132, 220, 0.12);
    }
    .kw-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
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
    .kw-avatar-init {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #BEA5DD 0%, #A684DC 100%);
        box-shadow: 0 4px 10px rgba(166, 132, 220, 0.25);
        font-size: 0.95rem;
    }
    .kw-toast-alert {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 99999;
        min-width: 280px;
        max-width: 400px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18);
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
            <h4 class="fw-bold mb-0 text-dark">Manajemen User (Customer)</h4>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.78rem;">
                <i class="bi bi-person-check-fill me-1"></i> Data Customer Terdaftar
            </span>
        </div>
        <p class="text-muted small mb-0">Pantau siapa saja customer yang terdaftar, status keaktifan akun, kontak WhatsApp, dan riwayat belanja.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="kw-btn-add d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCustomer">
            <i class="bi bi-person-plus-fill"></i> Tambah Customer
        </button>
    </div>
</div>

<!-- Metrics Cards Summary -->
<div class="row g-3 mb-4">
    <!-- Total Customer -->
    <div class="col-6 col-lg-3">
        <div class="kw-stat-box">
            <div class="kw-stat-icon" style="background: #f3ebf7; color: #8e62cf;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Customer</div>
                <h4 class="fw-bold text-dark mb-0" id="statTotalCustomers"><?= $stats['total'] ?></h4>
            </div>
        </div>
    </div>

    <!-- Customer Aktif -->
    <div class="col-6 col-lg-3">
        <div class="kw-stat-box">
            <div class="kw-stat-icon" style="background: #dcfce7; color: #16a34a;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Customer Aktif</div>
                <h4 class="fw-bold text-success mb-0" id="statActiveCustomers"><?= $stats['active'] ?></h4>
            </div>
        </div>
    </div>

    <!-- Customer Diblokir / Dikeluarkan -->
    <div class="col-6 col-lg-3">
        <div class="kw-stat-box">
            <div class="kw-stat-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="bi bi-slash-circle-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Diblokir / Dikeluarkan</div>
                <h4 class="fw-bold text-danger mb-0" id="statBlockedCustomers"><?= $stats['blocked'] ?></h4>
            </div>
        </div>
    </div>

    <!-- Total Belanja Customer -->
    <div class="col-6 col-lg-3">
        <div class="kw-stat-box">
            <div class="kw-stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="bi bi-bag-heart-fill"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Belanja Masuk</div>
                <h5 class="fw-bold text-dark mb-0" id="statTotalSpent">Rp <?= number_format($stats['total_spent'], 0, ',', '.') ?></h5>
            </div>
        </div>
    </div>
</div>

<!-- Main Filter Card (Realtime AJAX) -->
<div class="kw-user-card mb-4">
    <div class="row g-3 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-5 col-lg-4">
            <label class="form-label small fw-bold text-muted mb-1">Pencarian Customer</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-3 text-muted" style="border-color: #e2d9f3;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="userSearchInput" class="form-control kw-input border-start-0 rounded-0" 
                       placeholder="Cari nama, email, nomor HP/WA..." 
                       value="<?= htmlspecialchars($filters['q']) ?>" autocomplete="off">
                <button type="button" id="userSearchClear" class="btn bg-white border border-start-0 text-muted rounded-end-3" 
                        style="border-color: #e2d9f3; display: <?= !empty($filters['q']) ? 'block' : 'none' ?>;" title="Hapus pencarian">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="col-6 col-md-3 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Status Akun</label>
            <select id="userStatusSelect" class="form-select kw-select">
                <option value="all" <?= ($filters['status'] === 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="active" <?= ($filters['status'] === 'active' || $filters['status'] === '1') ? 'selected' : '' ?>>🟢 Aktif</option>
                <option value="inactive" <?= ($filters['status'] === 'inactive' || $filters['status'] === 'blocked' || $filters['status'] === '0') ? 'selected' : '' ?>>🚫 Diblokir / Nonaktif</option>
            </select>
        </div>

        <!-- Orders Filter -->
        <div class="col-6 col-md-4 col-lg-2">
            <label class="form-label small fw-bold text-muted mb-1">Aktivitas Belanja</label>
            <select id="userOrderFilter" class="form-select kw-select">
                <option value="all">Semua Transaksi</option>
                <option value="has_orders">🛍️ Pernah Belanja</option>
                <option value="no_orders">📦 Belum Belanja</option>
            </select>
        </div>

        <!-- Sorting -->
        <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Urutkan</label>
            <select id="userSortSelect" class="form-select kw-select">
                <option value="newest" <?= ($filters['sort'] === 'newest') ? 'selected' : '' ?>>🕒 Terbaru Mendaftar</option>
                <option value="oldest" <?= ($filters['sort'] === 'oldest') ? 'selected' : '' ?>>⏳ Terlama Mendaftar</option>
                <option value="name_asc" <?= ($filters['sort'] === 'name_asc') ? 'selected' : '' ?>>🔤 Nama A - Z</option>
                <option value="name_desc" <?= ($filters['sort'] === 'name_desc') ? 'selected' : '' ?>>🔤 Nama Z - A</option>
                <option value="spent_desc" <?= ($filters['sort'] === 'spent_desc') ? 'selected' : '' ?>>💎 Total Belanja Terbanyak</option>
                <option value="orders_desc" <?= ($filters['sort'] === 'orders_desc') ? 'selected' : '' ?>>📊 Pesanan Terbanyak</option>
            </select>
        </div>
    </div>

    <!-- Quick Chips & Status Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top border-light-subtle gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-bold text-muted me-1"><i class="bi bi-funnel-fill text-primary"></i> Filter Cepat:</span>
            <button type="button" class="kw-chip active" data-chip="all">Semua Customer</button>
            <button type="button" class="kw-chip" data-chip="active">🟢 Customer Aktif</button>
            <button type="button" class="kw-chip" data-chip="blocked">🚫 Diblokir / Dikeluarkan</button>
            <button type="button" class="kw-chip" data-chip="has_orders">🛍️ Pernah Belanja</button>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="d-flex align-items-center gap-2">
                <div id="userSpinner" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-normal" style="font-size: 0.82rem;">
                    Menampilkan <strong id="userCountBadge"><?= $pagination['total_customers'] ?? count($customers) ?></strong> customer
                </span>
            </div>

            <button type="button" id="userResetBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-flex align-items-center gap-1">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
            </button>
        </div>
    </div>
</div>

<!-- Table Card Container -->
<div class="kw-table-card mb-4" id="userTableCard">
    <div class="table-responsive">
        <table class="table kw-table align-middle mb-0" id="usersTable">
            <thead>
                <tr>
                    <th class="ps-4" style="min-width: 250px;">Customer</th>
                    <th style="min-width: 170px;">Kontak / WhatsApp</th>
                    <th style="min-width: 170px;">Aktivitas Belanja</th>
                    <th style="min-width: 130px;">Status Akun</th>
                    <th class="pe-4 text-center" style="min-width: 200px;">Aksi Admin</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php if(empty($customers)): ?>
                    <tr id="emptyUserRow">
                        <td colspan="5" class="text-center py-5">
                            <div class="display-6 text-muted mb-2">｡•́︿•̀｡</div>
                            <h6 class="fw-bold text-dark">Belum ada customer ditemukan</h6>
                            <p class="text-muted small mb-3">Tidak ada data customer yang sesuai dengan filter pencarian.</p>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="resetUserFilters()">Reset Filter</button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($customers as $c): ?>
                        <tr id="customer-row-<?= $c['id'] ?>">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="kw-avatar-init me-3">
                                        <?= strtoupper(substr($c['username'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">
                                            <?= htmlspecialchars($c['username']) ?>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.82rem;">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($c['email']) ?>
                                        </div>
                                        <div class="text-secondary small" style="font-size: 0.72rem;">
                                            <i class="bi bi-calendar-event"></i> Terdaftar: <?= date('d M Y, H:i', strtotime($c['created_at'])) ?> WIB
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if(!empty($c['phone_number'])): ?>
                                    <?php 
                                        $waClean = preg_replace('/[^0-9]/', '', $c['phone_number']);
                                        if (substr($waClean, 0, 1) === '0') $waClean = '62' . substr($waClean, 1);
                                    ?>
                                    <div class="fw-semibold text-dark">
                                        <?= htmlspecialchars($c['phone_number']) ?>
                                    </div>
                                    <a href="https://wa.me/<?= $waClean ?>?text=Halo%20kak%20<?= urlencode($c['username']) ?>,%20kami%20dari%20nnaco.sa%20Aksesoris..." 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-success rounded-pill px-2 py-0 mt-1 d-inline-flex align-items-center gap-1" 
                                       style="font-size: 0.74rem;">
                                        <i class="bi bi-whatsapp"></i> Chat WhatsApp
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Belum mengisi No. WA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-receipt"></i> <?= (int)($c['total_orders'] ?? 0) ?> Pesanan
                                    </span>
                                </div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.85rem;">
                                    Rp <?= number_format($c['total_spent'] ?? 0, 0, ',', '.') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $c['is_active'] ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-1" 
                                      id="badge-cust-<?= $c['id'] ?>" style="font-size: 0.8rem;">
                                    <?= $c['is_active'] ? '🟢 Aktif' : '🚫 Diblokir' ?>
                                </span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <!-- Detail Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info rounded-start-pill px-2 btn-cust-detail" 
                                            data-id="<?= $c['id'] ?>" 
                                            title="Lihat Detail Profil & Alamat Customer">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>

                                    <!-- Toggle Status (Keluarkan / Blokir / Aktifkan) -->
                                    <button type="button" 
                                            class="btn btn-sm <?= $c['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> px-2 btn-cust-toggle" 
                                            data-id="<?= $c['id'] ?>" 
                                            data-name="<?= htmlspecialchars($c['username'], ENT_QUOTES) ?>" 
                                            data-status="<?= $c['is_active'] ? '1' : '0' ?>"
                                            title="<?= $c['is_active'] ? 'Keluarkan / Blokir Akun' : 'Aktifkan Kembali Akun' ?>">
                                        <span class="spinner-border spinner-border-sm me-1 cust-spinner" style="display: none;" role="status"></span>
                                        <span class="cust-toggle-text"><?= $c['is_active'] ? 'Keluarkan' : 'Aktifkan' ?></span>
                                    </button>

                                    <!-- Reset Password -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning px-2 btn-cust-reset" 
                                            data-id="<?= $c['id'] ?>" 
                                            data-name="<?= htmlspecialchars($c['username'], ENT_QUOTES) ?>" 
                                            title="Reset Password Customer">
                                        <i class="bi bi-key-fill"></i>
                                    </button>

                                    <!-- Delete Customer -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary rounded-end-pill px-2 btn-cust-delete" 
                                            data-id="<?= $c['id'] ?>" 
                                            data-name="<?= htmlspecialchars($c['username'], ENT_QUOTES) ?>" 
                                            title="Hapus Customer">
                                        <i class="bi bi-trash"></i>
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
<div id="userPaginationWrapper" class="d-flex justify-content-center mb-4">
    <?php if(($pagination['total_pages'] ?? 1) > 1): ?>
        <nav aria-label="Navigasi Halaman Customer">
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

<!-- MODAL: Detail Customer -->
<div class="modal fade" id="modalCustomerDetail" tabindex="-1" aria-labelledby="modalCustomerDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0" style="background: #faf8ff; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="kw-avatar-init" id="modalDetailAvatar" style="width: 50px; height: 50px; font-size: 1.2rem;">CU</div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalDetailName">Nama Customer</h5>
                        <p class="text-muted small mb-0" id="modalDetailEmail">email@domain.com</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary Chips in Modal -->
                <div class="row g-2 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Status Akun:</span>
                            <div class="fw-bold mt-1" id="modalDetailStatus">🟢 Aktif</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Nomor WhatsApp:</span>
                            <div class="fw-bold mt-1" id="modalDetailPhone">-</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small">Total Belanja:</span>
                            <div class="fw-bold text-primary mt-1" id="modalDetailSpent">Rp 0</div>
                        </div>
                    </div>
                </div>

                <!-- Addresses Section -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Alamat Pengiriman Tersimpan
                    </h6>
                    <div id="modalDetailAddresses" class="small">
                        <span class="text-muted">Memuat alamat...</span>
                    </div>
                </div>

                <!-- Orders Section -->
                <div>
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-receipt-cutoff text-primary me-1"></i> Riwayat 10 Pesanan Terakhir
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Order</th>
                                    <th>Tanggal</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalDetailOrders">
                                <tr><td colspan="4" class="text-center text-muted">Belum ada pesanan.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Reset Password -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-key-fill text-warning me-1"></i> Reset Password Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted small mb-3">
                    Anda akan mengatur ulang password untuk akun customer <strong id="resetCustomerName">User</strong>.
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password Baru</label>
                    <div class="input-group">
                        <input type="text" id="resetNewPasswordInput" class="form-control kw-input" placeholder="Masukkan password baru...">
                        <button type="button" class="btn btn-outline-secondary" id="btnGeneratePassword" title="Acak Password">
                            <i class="bi bi-shuffle"></i> Acak
                        </button>
                    </div>
                    <small class="text-muted">Biarkan kosong untuk membuat password acak otomatis (e.g. nnaco5821).</small>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" id="btnSubmitResetPassword">
                    <span class="spinner-border spinner-border-sm me-1" id="spinnerResetPassword" style="display: none;"></span>
                    Reset Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Tambah Customer Baru -->
<div class="modal fade" id="modalAddCustomer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="<?= $baseRoute ?>/customerStore" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Customer Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap Customer <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control kw-input" placeholder="Contoh: Ananya Salsabila" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control kw-input" placeholder="contoh: ananya@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone_number" class="form-control kw-input" placeholder="Contoh: 08123456789 atau 628123456789">
                        <small class="text-muted">Format 08... otomatis dikonversi ke 628...</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Akun <span class="text-danger">*</span></label>
                        <input type="text" name="password" class="form-control kw-input" value="password123" required>
                        <small class="text-muted">Default: password123 (Customer bisa mengganti via profilnya).</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="kw-btn-add px-4">Simpan Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Realtime Toast Notification -->
<div id="userToast" class="toast align-items-center text-white bg-dark border-0 kw-toast-alert" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex p-2">
        <div class="toast-body d-flex align-items-center gap-2">
            <i id="userToastIcon" class="bi bi-check-circle-fill text-success fs-5"></i>
            <span id="userToastMessage" class="small">Perubahan data customer berhasil disimpan.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<!-- JavaScript Client-Side User Management Engine (Realtime AJAX) -->
<script>
(function() {
    const BASE_ROUTE = "<?= $baseRoute ?>";
    let csrfToken = "<?= $csrfToken ?>";
    let currentPage = <?= (int)($pagination['current_page'] ?? 1) ?>;
    let debounceTimer = null;
    let selectedCustomerId = null;

    // CRITICAL FIX: Move all modal elements to document.body to prevent backdrop z-index trap inside .d-shell
    function teleportModalsToBody() {
        ['modalCustomerDetail', 'modalResetPassword', 'modalAddCustomer'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', teleportModalsToBody);
    } else {
        teleportModalsToBody();
    }

    // DOM Elements
    const searchInput = document.getElementById('userSearchInput');
    const searchClear = document.getElementById('userSearchClear');
    const statusSelect = document.getElementById('userStatusSelect');
    const orderFilterSelect = document.getElementById('userOrderFilter');
    const sortSelect = document.getElementById('userSortSelect');
    const resetBtn = document.getElementById('userResetBtn');
    const tableBody = document.getElementById('userTableBody');
    const tableCard = document.getElementById('userTableCard');
    const paginationWrapper = document.getElementById('userPaginationWrapper');
    const countBadge = document.getElementById('userCountBadge');
    const spinner = document.getElementById('userSpinner');
    const chips = document.querySelectorAll('.kw-chip');

    // Stats Elements
    const statTotal = document.getElementById('statTotalCustomers');
    const statActive = document.getElementById('statActiveCustomers');
    const statBlocked = document.getElementById('statBlockedCustomers');
    const statSpent = document.getElementById('statTotalSpent');

    // Toast
    const toastEl = document.getElementById('userToast');
    const toastMsg = document.getElementById('userToastMessage');
    const toastIcon = document.getElementById('userToastIcon');
    let toastInstance = null;
    if (window.bootstrap && bootstrap.Toast) {
        toastInstance = new bootstrap.Toast(toastEl, { delay: 4000 });
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

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
    }

    // Main Fetch Function via AJAX
    function fetchUsers(page = 1) {
        currentPage = page;
        spinner.style.display = 'inline-block';
        tableCard.classList.add('loading-shimmer');

        const params = new URLSearchParams({
            ajax: '1',
            q: searchInput.value.trim(),
            status: statusSelect.value,
            order_filter: orderFilterSelect.value,
            sort: sortSelect.value,
            page: currentPage
        });

        // Sync browser URL
        const cleanUrlParams = new URLSearchParams(params);
        cleanUrlParams.delete('ajax');
        const newUrl = `${BASE_ROUTE}/users?${cleanUrlParams.toString()}`;
        window.history.pushState(null, '', newUrl);

        fetch(`${BASE_ROUTE}/users?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                if (data.csrf_token) csrfToken = data.csrf_token;
                renderUserRows(data.customers || []);
                renderPagination(data.pagination);
                countBadge.textContent = data.pagination.total_customers;

                if (data.stats) {
                    if (statTotal) statTotal.textContent = data.stats.total;
                    if (statActive) statActive.textContent = data.stats.active;
                    if (statBlocked) statBlocked.textContent = data.stats.blocked;
                    if (statSpent) statSpent.textContent = 'Rp ' + formatRupiah(data.stats.total_spent);
                }
            } else {
                showToast('Gagal memuat data customer.', false);
            }
        })
        .catch(err => {
            console.error('AJAX Customer Filter Error:', err);
            showToast('Terjadi kesalahan memfilter data customer.', false);
        })
        .finally(() => {
            spinner.style.display = 'none';
            tableCard.classList.remove('loading-shimmer');
        });
    }

    // Render Table Rows Dynamically
    function renderUserRows(customers) {
        tableBody.innerHTML = '';

        if (!customers || customers.length === 0) {
            tableBody.innerHTML = `
                <tr id="emptyUserRow">
                    <td colspan="5" class="text-center py-5">
                        <div class="display-6 text-muted mb-2">｡•́︿•̀｡</div>
                        <h6 class="fw-bold text-dark">Tidak ada customer ditemukan</h6>
                        <p class="text-muted small mb-3">Tidak ada data customer yang sesuai dengan filter pencarian.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="window.resetUserFilters()">Reset Filter</button>
                    </td>
                </tr>
            `;
            return;
        }

        customers.forEach(c => {
            const row = document.createElement('tr');
            row.id = `customer-row-${c.id}`;

            const initials = escapeHtml((c.username || 'CU').substring(0, 2).toUpperCase());
            const isActive = (parseInt(c.is_active, 10) === 1);
            const statusBadge = isActive 
                ? '<span class="badge bg-success rounded-pill px-3 py-1" id="badge-cust-' + c.id + '" style="font-size: 0.8rem;">🟢 Aktif</span>' 
                : '<span class="badge bg-danger rounded-pill px-3 py-1" id="badge-cust-' + c.id + '" style="font-size: 0.8rem;">🚫 Diblokir</span>';

            const toggleBtnClass = isActive ? 'btn-outline-danger' : 'btn-outline-success';
            const toggleBtnText = isActive ? 'Keluarkan' : 'Aktifkan';

            let waHtml = '<span class="text-muted small fst-italic">Belum mengisi No. WA</span>';
            if (c.phone_number) {
                let clean = c.phone_number.replace(/[^0-9]/g, '');
                if (clean.startsWith('0')) clean = '62' + clean.substring(1);
                waHtml = `
                    <div class="fw-semibold text-dark">${escapeHtml(c.phone_number)}</div>
                    <a href="https://wa.me/${clean}?text=Halo%20kak%20${encodeURIComponent(c.username)},%20kami%20dari%20nnaco.sa%20Aksesoris..." 
                       target="_blank" 
                       class="btn btn-sm btn-outline-success rounded-pill px-2 py-0 mt-1 d-inline-flex align-items-center gap-1" 
                       style="font-size: 0.74rem;">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp
                    </a>
                `;
            }

            const totalOrders = parseInt(c.total_orders || 0, 10);
            const totalSpent = parseFloat(c.total_spent || 0);

            row.innerHTML = `
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="kw-avatar-init me-3">${initials}</div>
                        <div>
                            <div class="fw-bold text-dark mb-0">${escapeHtml(c.username)}</div>
                            <div class="text-muted small" style="font-size: 0.82rem;">
                                <i class="bi bi-envelope"></i> ${escapeHtml(c.email)}
                            </div>
                            <div class="text-secondary small" style="font-size: 0.72rem;">
                                <i class="bi bi-calendar-event"></i> Terdaftar: ${formatDate(c.created_at)}
                            </div>
                        </div>
                    </div>
                </td>
                <td>${waHtml}</td>
                <td>
                    <div>
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                            <i class="bi bi-receipt"></i> ${totalOrders} Pesanan
                        </span>
                    </div>
                    <div class="fw-bold text-dark mt-1" style="font-size: 0.85rem;">
                        Rp ${formatRupiah(totalSpent)}
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td class="pe-4 text-center">
                    <div class="btn-group" role="group">
                        <button type="button" 
                                class="btn btn-sm btn-outline-info rounded-start-pill px-2 btn-cust-detail" 
                                data-id="${c.id}" 
                                title="Lihat Detail Profil & Alamat Customer">
                            <i class="bi bi-eye"></i> Detail
                        </button>
                        <button type="button" 
                                class="btn btn-sm ${toggleBtnClass} px-2 btn-cust-toggle" 
                                data-id="${c.id}" 
                                data-name="${escapeHtml(c.username)}" 
                                data-status="${isActive ? '1' : '0'}"
                                title="${isActive ? 'Keluarkan / Blokir Akun' : 'Aktifkan Kembali Akun'}">
                            <span class="spinner-border spinner-border-sm me-1 cust-spinner" style="display: none;" role="status"></span>
                            <span class="cust-toggle-text">${toggleBtnText}</span>
                        </button>
                        <button type="button" 
                                class="btn btn-sm btn-outline-warning px-2 btn-cust-reset" 
                                data-id="${c.id}" 
                                data-name="${escapeHtml(c.username)}" 
                                title="Reset Password Customer">
                            <i class="bi bi-key-fill"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-sm btn-outline-secondary rounded-end-pill px-2 btn-cust-delete" 
                                data-id="${c.id}" 
                                data-name="${escapeHtml(c.username)}" 
                                title="Hapus Customer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    // Render Pagination
    function renderPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            paginationWrapper.innerHTML = '';
            return;
        }

        const totalPages = pagination.total_pages;
        const current = pagination.current_page;

        let html = `
            <nav aria-label="Navigasi Halaman Customer">
                <ul class="pagination pagination-sm shadow-sm gap-1 mb-0">
                    <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                        <a class="page-link rounded-pill px-3" href="#" data-page="${Math.max(1, current - 1)}">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
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

    // TOGGLE STATUS: Keluarkan / Blokir / Aktifkan Customer (AJAX)
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.btn-cust-toggle');
        if (!toggleBtn) return;

        e.preventDefault();
        const id = toggleBtn.dataset.id;
        const name = toggleBtn.dataset.name;
        const currentStatus = parseInt(toggleBtn.dataset.status, 10);
        const newStatus = (currentStatus === 1) ? 0 : 1;

        const actionText = (currentStatus === 1) 
            ? `mengeluarkan/memblokir customer "${name}"?\n(Akun ini tidak akan bisa login atau belanja di nnaco.sa)` 
            : `mengaktifkan kembali akun customer "${name}"?`;

        if (!confirm(`Apakah Anda yakin ingin ${actionText}`)) {
            return;
        }

        const spinnerSpan = toggleBtn.querySelector('.cust-spinner');
        const textSpan = toggleBtn.querySelector('.cust-toggle-text');

        toggleBtn.disabled = true;
        if (spinnerSpan) spinnerSpan.style.display = 'inline-block';

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('status', newStatus);
        formData.append('ajax', '1');

        fetch(`${BASE_ROUTE}/customerToggle/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                if (result.csrf_token) csrfToken = result.csrf_token;
                toggleBtn.dataset.status = newStatus;
                const badge = document.getElementById(`badge-cust-${id}`);

                if (newStatus === 1) {
                    if (badge) {
                        badge.className = 'badge bg-success rounded-pill px-3 py-1';
                        badge.textContent = '🟢 Aktif';
                    }
                    toggleBtn.className = 'btn btn-sm btn-outline-danger px-2 btn-cust-toggle';
                    if (textSpan) textSpan.textContent = 'Keluarkan';
                    showToast(`✨ Akun "${name}" berhasil diaktifkan kembali.`, true);
                } else {
                    if (badge) {
                        badge.className = 'badge bg-danger rounded-pill px-3 py-1';
                        badge.textContent = '🚫 Diblokir';
                    }
                    toggleBtn.className = 'btn btn-sm btn-outline-success px-2 btn-cust-toggle';
                    if (textSpan) textSpan.textContent = 'Aktifkan';
                    showToast(`🚫 Akun "${name}" berhasil dikeluarkan/diblokir.`, true);
                }

                // Update summary counters
                if (newStatus === 1) {
                    if (statActive) statActive.textContent = parseInt(statActive.textContent || 0, 10) + 1;
                    if (statBlocked) statBlocked.textContent = Math.max(0, parseInt(statBlocked.textContent || 0, 10) - 1);
                } else {
                    if (statActive) statActive.textContent = Math.max(0, parseInt(statActive.textContent || 0, 10) - 1);
                    if (statBlocked) statBlocked.textContent = parseInt(statBlocked.textContent || 0, 10) + 1;
                }
            } else {
                showToast(result.message || 'Gagal mengubah status akun customer.', false);
            }
        })
        .catch(err => {
            console.error('AJAX Toggle Error:', err);
            showToast('Terjadi kesalahan koneksi server.', false);
        })
        .finally(() => {
            toggleBtn.disabled = false;
            if (spinnerSpan) spinnerSpan.style.display = 'none';
        });
    });

    // DETAIL MODAL TRIGGER (AJAX)
    document.addEventListener('click', function(e) {
        const detailBtn = e.target.closest('.btn-cust-detail');
        if (!detailBtn) return;

        teleportModalsToBody();

        const id = detailBtn.dataset.id;
        const modalEl = document.getElementById('modalCustomerDetail');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('modalDetailName').textContent = 'Memuat...';
        document.getElementById('modalDetailEmail').textContent = '';
        document.getElementById('modalDetailAddresses').innerHTML = '<span class="text-muted">Memuat data alamat...</span>';
        document.getElementById('modalDetailOrders').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Memuat data pesanan...</td></tr>';

        modal.show();

        fetch(`${BASE_ROUTE}/customerDetail/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.customer) {
                const c = data.customer;
                document.getElementById('modalDetailAvatar').textContent = (c.username || 'CU').substring(0, 2).toUpperCase();
                document.getElementById('modalDetailName').textContent = c.username;
                document.getElementById('modalDetailEmail').textContent = c.email + ' (Terdaftar: ' + formatDate(c.created_at) + ')';
                document.getElementById('modalDetailStatus').innerHTML = (c.is_active == 1) 
                    ? '<span class="text-success fw-bold">🟢 Aktif</span>' 
                    : '<span class="text-danger fw-bold">🚫 Diblokir / Dikeluarkan</span>';
                document.getElementById('modalDetailPhone').textContent = c.phone_number || 'Belum diisi';
                document.getElementById('modalDetailSpent').textContent = 'Rp ' + formatRupiah(c.total_spent || 0) + ' (' + (c.total_orders || 0) + ' pesanan)';

                // Render Addresses
                const addrWrap = document.getElementById('modalDetailAddresses');
                if (c.addresses && c.addresses.length > 0) {
                    addrWrap.innerHTML = c.addresses.map(a => `
                        <div class="p-2 mb-2 bg-light border rounded-3">
                            <div class="fw-bold">${escapeHtml(a.city || '')}, ${escapeHtml(a.province || '')}</div>
                            <div class="text-muted">${escapeHtml(a.address_line || '')} (Kode Pos: ${escapeHtml(a.postal_code || '-')})</div>
                            <div class="text-secondary small">Telp: ${escapeHtml(a.phone_number || '-')}</div>
                        </div>
                    `).join('');
                } else {
                    addrWrap.innerHTML = '<span class="text-muted fst-italic">Customer belum menyimpan alamat pengiriman.</span>';
                }

                // Render Orders
                const orderWrap = document.getElementById('modalDetailOrders');
                if (c.orders && c.orders.length > 0) {
                    orderWrap.innerHTML = c.orders.map(o => `
                        <tr>
                            <td><strong>#${o.id}</strong></td>
                            <td>${formatDate(o.created_at)}</td>
                            <td>Rp ${formatRupiah(o.total_amount)}</td>
                            <td><span class="badge bg-secondary rounded-pill">${escapeHtml(o.status)}</span></td>
                        </tr>
                    `).join('');
                } else {
                    orderWrap.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Customer ini belum pernah membuat pesanan.</td></tr>';
                }
            } else {
                showToast('Gagal mengambil data detail customer.', false);
            }
        })
        .catch(err => {
            console.error('Detail Error:', err);
            showToast('Terjadi kesalahan memuat detail.', false);
        });
    });

    // RESET PASSWORD MODAL TRIGGER
    document.addEventListener('click', function(e) {
        const resetBtn = e.target.closest('.btn-cust-reset');
        if (!resetBtn) return;

        teleportModalsToBody();

        selectedCustomerId = resetBtn.dataset.id;
        document.getElementById('resetCustomerName').textContent = resetBtn.dataset.name;
        document.getElementById('resetNewPasswordInput').value = '';

        const modalEl = document.getElementById('modalResetPassword');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // Generate Random Password
    document.getElementById('btnGeneratePassword').addEventListener('click', function() {
        const randomPass = 'nnaco' + Math.floor(1000 + Math.random() * 9000);
        document.getElementById('resetNewPasswordInput').value = randomPass;
    });

    // Submit Reset Password via AJAX
    document.getElementById('btnSubmitResetPassword').addEventListener('click', function() {
        if (!selectedCustomerId) return;

        const newPass = document.getElementById('resetNewPasswordInput').value.trim();
        const submitBtn = this;
        const spinnerReset = document.getElementById('spinnerResetPassword');

        submitBtn.disabled = true;
        spinnerReset.style.display = 'inline-block';

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('new_password', newPass);
        formData.append('ajax', '1');

        fetch(`${BASE_ROUTE}/customerResetPassword/${selectedCustomerId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                if (result.csrf_token) csrfToken = result.csrf_token;
                bootstrap.Modal.getInstance(document.getElementById('modalResetPassword')).hide();
                showToast(`🔑 Password berhasil diubah: "${result.new_password}"`, true);
                alert(`Password untuk customer berhasil diperbarui!\n\nPassword Baru: ${result.new_password}\n\nSilakan berikan password baru ini kepada customer.`);
            } else {
                showToast(result.message || 'Gagal mereset password.', false);
            }
        })
        .catch(err => {
            console.error('Reset Password Error:', err);
            showToast('Terjadi kesalahan saat mereset password.', false);
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinnerReset.style.display = 'none';
        });
    });

    // DELETE CUSTOMER (AJAX)
    document.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.btn-cust-delete');
        if (!delBtn) return;

        const id = delBtn.dataset.id;
        const name = delBtn.dataset.name;

        if (!confirm(`Apakah Anda yakin ingin MENGHAPUS PERMANEN customer "${name}"?\n\nPerhatian: Jika customer sudah pernah melakukan transaksi/pesanan, akun tidak akan dihapus melainkan dialihkan ke opsi Diblokir/Nonaktif.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('ajax', '1');

        fetch(`${BASE_ROUTE}/customerDelete/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.csrf_token) csrfToken = result.csrf_token;
            if (result.status === 'success') {
                showToast(result.message, true);
                const row = document.getElementById(`customer-row-${id}`);
                if (row) row.remove();
                fetchUsers(currentPage);
            } else {
                alert(`PEMBERITAHUAN:\n\n${result.message}`);
            }
        })
        .catch(err => {
            console.error('Delete Error:', err);
            showToast('Gagal menghapus customer.', false);
        });
    });

    // Pagination Link Clicks
    paginationWrapper.addEventListener('click', function(e) {
        const link = e.target.closest('a[data-page]');
        if (!link) return;

        e.preventDefault();
        const page = parseInt(link.dataset.page, 10);
        if (!isNaN(page) && page !== currentPage) {
            fetchUsers(page);
            tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // Live Search with Debounce
    searchInput.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchUsers(1);
        }, 300);
    });

    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        fetchUsers(1);
        searchInput.focus();
    });

    // Select Changes
    statusSelect.addEventListener('change', () => {
        updateActiveChip();
        fetchUsers(1);
    });
    orderFilterSelect.addEventListener('change', () => {
        updateActiveChip();
        fetchUsers(1);
    });
    sortSelect.addEventListener('change', () => fetchUsers(1));

    // Chips Handler
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            chips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.chip;
            if (type === 'all') {
                statusSelect.value = 'all';
                orderFilterSelect.value = 'all';
            } else if (type === 'active') {
                statusSelect.value = 'active';
                orderFilterSelect.value = 'all';
            } else if (type === 'blocked') {
                statusSelect.value = 'inactive';
                orderFilterSelect.value = 'all';
            } else if (type === 'has_orders') {
                statusSelect.value = 'all';
                orderFilterSelect.value = 'has_orders';
            }

            fetchUsers(1);
        });
    });

    function updateActiveChip() {
        chips.forEach(c => c.classList.remove('active'));
        if (statusSelect.value === 'all' && orderFilterSelect.value === 'all') {
            const allChip = document.querySelector('.kw-chip[data-chip="all"]');
            if (allChip) allChip.classList.add('active');
        }
    }

    // Reset Function
    window.resetUserFilters = function() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        statusSelect.value = 'all';
        orderFilterSelect.value = 'all';
        sortSelect.value = 'newest';

        chips.forEach(c => c.classList.remove('active'));
        const allChip = document.querySelector('.kw-chip[data-chip="all"]');
        if (allChip) allChip.classList.add('active');

        fetchUsers(1);
    };

    resetBtn.addEventListener('click', window.resetUserFilters);

})();
</script>
