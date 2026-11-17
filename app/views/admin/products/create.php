<?php
    $userRole = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = ($userRole == 'owner') ? BASEURL . '/owner' : (($userRole == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin');
    $canManagePromo = ($userRole == 'admin' || $userRole == 'operator' || $userRole == 'owner');
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Tambah Produk Baru</h5>
            </div>
            <div class="card-body p-4 pt-0">
                <form action="<?= $baseRoute ?>/productStore" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Bando Bunny Kawaii" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select rounded-3" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="priceInput" name="price" class="form-control rounded-3" placeholder="Contoh: 25000" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control rounded-3" placeholder="Contoh: 50" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Gambar Produk</label>
                            <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, WEBP (Opsional)</small>
                        </div>
                    </div>

                    <?php if ($canManagePromo): ?>
                    <!-- KELOLA PROMO & DISKON (KHUSUS ADMIN & OPERATOR) -->
                    <div class="card mb-4 border-0 rounded-4" style="background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%); border: 1.5px dashed #c084fc !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size: 1.4rem;">🏷️</span>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Promo Diskon Produk (Harga Coret)</h6>
                                        <small class="text-muted">Aktifkan untuk langsung memberikan diskon harga pada produk ini</small>
                                    </div>
                                </div>
                                <div class="form-check form-switch fs-5 mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_promo" id="isPromoCheck" value="1">
                                </div>
                            </div>

                            <div id="promoDetailsSection" class="mt-3 pt-3 border-top" style="display: none;">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold small text-secondary mb-1">Besar Diskon (%)</label>
                                        <div class="input-group">
                                            <input type="number" id="discountPercentInput" name="discount_percent" class="form-control rounded-start-3" value="0" min="0" max="99" placeholder="Contoh: 15">
                                            <span class="input-group-text bg-white fw-bold text-primary rounded-end-3">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold small text-secondary mb-1">Simulasi Harga Coret ke Pembeli</label>
                                        <div class="p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between shadow-sm">
                                            <div>
                                                <small class="text-muted d-block">Harga Normal</small>
                                                <span class="text-muted text-decoration-line-through fw-semibold" id="originalPricePreview">Rp 0</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-danger fw-bold d-block">Harga Promo</small>
                                                <span class="fw-bold text-danger fs-5" id="discountedPricePreview">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control rounded-3" rows="5" placeholder="Tuliskan deskripsi lengkap produk..."></textarea>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveCheck" checked>
                        <label class="form-check-label" for="isActiveCheck">Langsung Aktifkan Produk</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $baseRoute ?>/products" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isPromoCheck = document.getElementById('isPromoCheck');
    const promoDetails = document.getElementById('promoDetailsSection');
    const priceInput = document.getElementById('priceInput');
    const discountInput = document.getElementById('discountPercentInput');
    const originalPreview = document.getElementById('originalPricePreview');
    const discountedPreview = document.getElementById('discountedPricePreview');

    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    function updateSimulation() {
        if (!priceInput || !discountInput) return;
        const price = parseFloat(priceInput.value) || 0;
        let discount = parseInt(discountInput.value) || 0;
        if (discount < 0) discount = 0;
        if (discount > 99) discount = 99;

        if (originalPreview) originalPreview.textContent = formatRupiah(price);
        
        const discounted = price * (1 - (discount / 100));
        if (discountedPreview) discountedPreview.textContent = formatRupiah(discounted);
    }

    if (isPromoCheck) {
        isPromoCheck.addEventListener('change', function() {
            if (this.checked) {
                promoDetails.style.display = 'block';
                if (!discountInput.value || parseInt(discountInput.value) === 0) {
                    discountInput.value = 10;
                }
                updateSimulation();
            } else {
                promoDetails.style.display = 'none';
                discountInput.value = 0;
                updateSimulation();
            }
        });
    }

    if (priceInput) priceInput.addEventListener('input', updateSimulation);
    if (discountInput) discountInput.addEventListener('input', updateSimulation);
});
</script>
