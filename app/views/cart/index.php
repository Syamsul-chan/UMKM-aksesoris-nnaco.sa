<div class="row" id="cartMainContainer">
    <div class="col-md-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0 fw-bold" style="color: #4A2E35;">🧺 Keranjang Belanja</h4>
            <span class="badge rounded-pill" style="background: #F3E8FF; color: #7C3AED; font-size: 0.72rem; font-weight: 700; padding: 5px 12px;">
                ⚡ Realtime AJAX
            </span>
        </div>
        
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info border-0 rounded-4 p-4 text-center" style="background: #FAF5FF; color: #7C3AED;">
                <i class="bi bi-cart-x fs-1 d-block mb-2 text-purple"></i>
                <h5 class="fw-bold">Keranjang Anda masih kosong</h5>
                <p class="small text-muted mb-3">Ayo temukan aksesoris imut favorit Anda di katalog!</p>
                <a href="<?= BASEURL ?>" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold" style="background: linear-gradient(135deg, #A684DC 0%, #7C3AED 100%); border: none;">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead style="background: #F8F5FC; color: #7E708E; font-size: 0.85rem;">
                                <tr>
                                    <th class="ps-4 py-3">Produk</th>
                                    <th class="py-3">Harga</th>
                                    <th width="160" class="py-3">Kuantitas</th>
                                    <th class="py-3">Subtotal</th>
                                    <th class="pe-4 text-end py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <?php foreach ($cartItems as $item): ?>
                                <tr id="cartRow-<?= $item['id'] ?>">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <?php if($item['image']): ?>
                                                <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-3 shadow-sm" style="width: 52px; height: 52px; object-fit: contain; background: #FAF5FF; margin-right: 15px; padding: 4px;">
                                            <?php else: ?>
                                                <div class="rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 52px; height: 52px; margin-right: 15px; background: #FAF5FF;"><i class="bi bi-gem fs-4"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #4A2E35; font-size: 0.92rem;"><?= htmlspecialchars($item['name']) ?></h6>
                                                <small class="text-muted" style="font-size: 0.75rem;">Stok: <span id="itemStock-<?= $item['id'] ?>"><?= $item['stock'] ?></span></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <?php if (!empty($item['is_promo'])): ?>
                                            <span class="text-muted text-decoration-line-through small d-block" style="font-size: 0.72rem;">Rp <?= number_format($item['original_price'], 0, ',', '.') ?></span>
                                            <span class="fw-bold text-danger">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                            <span class="badge bg-danger rounded-pill px-2 py-0" style="font-size: 0.65rem;">-<?= $item['discount_percent'] ?>%</span>
                                        <?php else: ?>
                                            <span class="fw-semibold" style="color: #7E708E;">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <form action="<?= BASEURL ?>/cart/update" method="POST" class="d-flex align-items-center gap-1 cart-update-form" data-product-id="<?= $item['id'] ?>" onsubmit="event.preventDefault(); submitCartUpdate(this);">
                                            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control form-control-sm text-center fw-bold" style="width: 65px; border-radius: 10px;" onchange="submitCartUpdate(this.form)">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Perbarui"><i class="bi bi-check2"></i></button>
                                        </form>
                                    </td>
                                    <td class="fw-bold text-success py-3" id="subtotal-<?= $item['id'] ?>">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                    <td class="pe-4 text-end py-3">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Hapus produk" onclick="submitCartRemove(<?= $item['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="border-radius: 20px;">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-3" style="color: #4A2E35;">Ringkasan Belanja</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Harga</span>
                    <span class="fw-bold text-dark" id="summaryTotalTop">Rp <?= number_format($total ?? 0, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-muted small">
                    <span>Estimasi Pengiriman</span>
                    <span class="text-success fw-bold">Dihitung saat checkout</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold h5 mb-0" style="color: #4A2E35;">Total Belanja</span>
                    <span class="fw-bold h5 mb-0 text-success" id="summaryTotalBottom">Rp <?= number_format($total ?? 0, 0, ',', '.') ?></span>
                </div>
                
                <div id="checkoutBtnWrap">
                    <?php if (!empty($cartItems)): ?>
                        <a href="<?= BASEURL ?>/checkout" class="btn w-100 py-2.5 fw-bold text-white rounded-pill shadow-sm" style="background: linear-gradient(135deg, #EC4899 0%, #A684DC 100%);">
                            Lanjut ke Checkout &rsaquo;
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 py-2.5 fw-semibold rounded-pill" disabled>Lanjut ke Checkout</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    const BASE_URL = '<?= BASEURL ?>';

    window.submitCartUpdate = function(form) {
        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch(form.action, {
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
                const pid = formData.get('product_id');
                const subEl = document.getElementById('subtotal-' + pid);
                if (subEl && res.item_subtotal_formatted) {
                    subEl.textContent = res.item_subtotal_formatted;
                }
                updateCartTotals(res.total_formatted);
                updateCartBadges(res.cart_count);
            } else {
                alert(res.message || 'Gagal memperbarui kuantitas.');
            }
        })
        .catch(err => {
            console.error('AJAX Cart Update Error:', err);
        });
    };

    window.submitCartRemove = function(productId) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) return;

        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('csrf_token', '<?= Controller::generateCsrfToken() ?>');

        fetch(BASE_URL + '/cart/remove/' + productId, {
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
                const row = document.getElementById('cartRow-' + productId);
                if (row) {
                    row.style.transition = 'opacity 0.25s, transform 0.25s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        row.remove();
                        if (res.cart_count === 0) {
                            location.reload();
                        }
                    }, 250);
                }
                updateCartTotals(res.total_formatted);
                updateCartBadges(res.cart_count);
            }
        })
        .catch(err => {
            console.error('AJAX Cart Remove Error:', err);
        });
    };

    function updateCartTotals(totalFormatted) {
        const topEl = document.getElementById('summaryTotalTop');
        const bottomEl = document.getElementById('summaryTotalBottom');
        if (topEl) topEl.textContent = totalFormatted;
        if (bottomEl) bottomEl.textContent = totalFormatted;
    }

    function updateCartBadges(count) {
        const navBadge = document.getElementById('navCartBadge');
        const sidebarBadge = document.getElementById('sidebarCartBadge');
        [navBadge, sidebarBadge].forEach(badge => {
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        });
    }
})();
</script>
