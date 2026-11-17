<?php
    $userRole = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = $baseRoute ?? (($userRole == 'operator') ? BASEURL . '/operator' : (($userRole == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin'));
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <span class="fs-3">🏷️</span>
            <h4 class="fw-bold mb-0 text-dark">Pusat Kelola Promo & Diskon</h4>
            <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-3 py-1" style="background-color: #f3e8ff; color: #7e22ce;">
                <?= ucfirst($userRole) ?> Access Only
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">Kelola diskon produk langsung (harga coret) dan kupon voucher toko untuk pelanggan.</p>
    </div>
</div>

<!-- NAV TABS -->
<ul class="nav nav-pills mb-4 gap-2 p-1 bg-white rounded-pill shadow-sm d-inline-flex border" id="promoTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-semibold" id="tab-diskon-btn" data-bs-toggle="pill" data-bs-target="#tab-diskon" type="button" role="tab">
            🏷️ Diskon Produk (<?= count($promoProducts) ?> Aktif)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-semibold" id="tab-kupon-btn" data-bs-toggle="pill" data-bs-target="#tab-kupon" type="button" role="tab">
            🎟️ Kupon Voucher (<?= count($promos) ?> Total)
        </button>
    </li>
</ul>

<div class="tab-content" id="promoTabContent">
    <!-- ========================================== -->
    <!-- TAB 1: PROMO DISKON PRODUK (HARGA CORET) -->
    <!-- ========================================== -->
    <div class="tab-pane fade show active" id="tab-diskon" role="tabpanel">
        <div class="row g-4 mb-4">
            <!-- QUICK SET PROMO FORM -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">⚡ Pasang Diskon Cepat</h6>
                        <small class="text-muted">Pilih produk dan tentukan persentase diskon</small>
                    </div>
                    <div class="card-body pt-0">
                        <form action="<?= $baseRoute ?>/setProductDiscount" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Pilih Produk</label>
                                <select name="product_id" id="quickProductId" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-discount="<?= $p['discount_percent'] ?? 0 ?>">
                                            <?= htmlspecialchars($p['name']) ?> (Rp <?= number_format($p['price'], 0, ',', '.') ?>)
                                            <?= (!empty($p['is_promo']) && $p['discount_percent'] > 0) ? " [Diskon {$p['discount_percent']}%]" : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Besar Diskon (%)</label>
                                <div class="input-group">
                                    <input type="number" name="discount_percent" id="quickDiscountInput" class="form-control rounded-start-3" min="1" max="99" value="15" required placeholder="1-99">
                                    <span class="input-group-text bg-white fw-bold text-primary rounded-end-3">%</span>
                                </div>
                            </div>

                            <!-- PREVIEW BOX -->
                            <div class="p-3 mb-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Harga Asli:</span>
                                    <span class="fw-semibold text-muted text-decoration-line-through small" id="quickOriginalPrice">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-danger fw-bold small">Harga Promo:</span>
                                    <span class="fw-bold text-danger fs-6" id="quickPromoPrice">Rp 0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                🚀 Terapkan Diskon ke Produk
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ACTIVE DISCOUNT PRODUCTS TABLE -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">🏷️ Daftar Produk Berdiskon (Harga Coret)</h6>
                            <small class="text-muted">Produk di bawah ini sedang aktif harga promonya</small>
                        </div>
                        <span class="badge bg-danger rounded-pill px-3 py-2"><?= count($promoProducts) ?> Produk Diskon</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Produk</th>
                                        <th>Harga Asli</th>
                                        <th>Diskon</th>
                                        <th>Harga Promo</th>
                                        <th class="pe-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($promoProducts)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <div style="font-size: 2.5rem;" class="mb-2">🛍️</div>
                                                <div class="fw-semibold">Belum ada produk dengan diskon aktif</div>
                                                <small>Gunakan formulir di samping kiri atau menu edit produk untuk memasang diskon.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($promoProducts as $p): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <?php if($p['image']): ?>
                                                            <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="rounded-3 me-3" style="width: 44px; height: 44px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded-3 text-muted d-flex align-items-center justify-content-center me-3 border" style="width: 44px; height: 44px; font-size: 0.75rem;">IMG</div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($p['category_name']) ?> &bull; Stok: <?= $p['stock'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted text-decoration-line-through">
                                                        Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger rounded-pill px-2 py-1 fw-bold">
                                                        -<?= $p['discount_percent'] ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-danger">
                                                        Rp <?= number_format($p['promo_price'], 0, ',', '.') ?>
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="<?= $baseRoute ?>/productEdit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Edit Produk & Diskon">
                                                            ✏️ Edit
                                                        </a>
                                                        <form action="<?= $baseRoute ?>/removeProductDiscount/<?= $p['id'] ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus promo diskon untuk produk ini?');" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Hapus Promo">
                                                                ✕ Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: KUPON VOUCHER TOKO (KODE PROMO) -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="tab-kupon" role="tabpanel">
        <div class="row g-4 mb-4">
            <!-- CREATE VOUCHER FORM -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">➕ Buat Kupon Voucher Baru</h6>
                        <small class="text-muted">Kupon voucher dapat diklaim pembeli di keranjang</small>
                    </div>
                    <div class="card-body pt-0">
                        <form action="<?= $baseRoute ?>/promoStore" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Nama Promo</label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Promo Gajian Berkah" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Kode Voucher (Huruf Kapital)</label>
                                <input type="text" name="code" class="form-control rounded-3 text-uppercase fw-bold" placeholder="Contoh: GAJIAN20" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small text-muted">Tipe Diskon</label>
                                    <select name="discount_type" class="form-select rounded-3">
                                        <option value="percentage">Persen (%)</option>
                                        <option value="fixed">Nominal (Rp)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small text-muted">Nilai Potongan</label>
                                    <input type="number" name="discount_value" class="form-control rounded-3" placeholder="10 / 20000" min="1" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Tanggal Mulai</label>
                                <input type="datetime-local" name="start_date" class="form-control rounded-3" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Tanggal Berakhir</label>
                                <input type="datetime-local" name="end_date" class="form-control rounded-3" value="<?= date('Y-m-d\TH:i', strtotime('+30 days')) ?>" required>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="voucherActive" value="1" checked>
                                <label class="form-check-label fw-semibold small" for="voucherActive">Langsung Aktifkan Kupon</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                💾 Simpan Kupon Promo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- VOUCHER LIST TABLE -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">🎟️ Daftar Kupon Voucher Toko</h6>
                            <small class="text-muted">Semua voucher promo yang tersedia di sistem</small>
                        </div>
                        <span class="badge bg-purple-subtle text-purple rounded-pill px-3 py-2 border" style="background-color: #f3e8ff; color: #7e22ce;">
                            <?= count($promos) ?> Voucher
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Kode & Nama</th>
                                        <th>Diskon</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($promos)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <div style="font-size: 2.5rem;" class="mb-2">🎟️</div>
                                                <div class="fw-semibold">Belum ada kupon promo dibuat</div>
                                                <small>Gunakan formulir di samping untuk menambahkan kupon baru.</small>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($promos as $promo): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark font-monospace fs-6 text-primary">
                                                        <?= htmlspecialchars($promo['code']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($promo['name']) ?></small>
                                                </td>
                                                <td>
                                                    <?php if($promo['discount_type'] == 'percentage'): ?>
                                                        <span class="badge bg-danger rounded-pill px-2 py-1 fw-bold">
                                                            <?= (int)$promo['discount_value'] ?>% OFF
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success rounded-pill px-2 py-1 fw-bold">
                                                            Rp <?= number_format($promo['discount_value'], 0, ',', '.') ?> OFF
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="d-block text-muted">
                                                        <?= date('d M Y', strtotime($promo['start_date'])) ?> s/d
                                                    </small>
                                                    <small class="fw-semibold text-dark">
                                                        <?= date('d M Y', strtotime($promo['end_date'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if($promo['is_active']): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                            ● Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                                            ○ Nonaktif
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="pe-4 text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <!-- TOGGLE STATUS -->
                                                        <form action="<?= $baseRoute ?>/promoToggle/<?= $promo['id'] ?>" method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                                                            <button type="submit" class="btn btn-sm <?= $promo['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?> rounded-pill px-2 py-1" title="<?= $promo['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                                <?= $promo['is_active'] ? 'Off' : 'On' ?>
                                                            </button>
                                                        </form>
                                                        <!-- DELETE -->
                                                        <form action="<?= $baseRoute ?>/promoDelete/<?= $promo['id'] ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon voucher ini?');" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Hapus Kupon">
                                                                🗑️
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickSelect = document.getElementById('quickProductId');
    const quickDiscount = document.getElementById('quickDiscountInput');
    const quickOrig = document.getElementById('quickOriginalPrice');
    const quickPromo = document.getElementById('quickPromoPrice');

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function calculateQuick() {
        if (!quickSelect || !quickDiscount) return;
        const selectedOpt = quickSelect.options[quickSelect.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            quickOrig.textContent = 'Rp 0';
            quickPromo.textContent = 'Rp 0';
            return;
        }

        const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
        let discount = parseInt(quickDiscount.value) || 0;
        if (discount < 1) discount = 1;
        if (discount > 99) discount = 99;

        quickOrig.textContent = formatRupiah(price);
        const promo = price * (1 - (discount / 100));
        quickPromo.textContent = formatRupiah(promo);
    }

    if (quickSelect) {
        quickSelect.addEventListener('change', function() {
            const selectedOpt = quickSelect.options[quickSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const existingDisc = parseInt(selectedOpt.getAttribute('data-discount')) || 0;
                if (existingDisc > 0) {
                    quickDiscount.value = existingDisc;
                }
            }
            calculateQuick();
        });
    }

    if (quickDiscount) {
        quickDiscount.addEventListener('input', calculateQuick);
    }
});
</script>
