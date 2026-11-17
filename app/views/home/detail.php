<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <?php if($product['image']): ?>
            <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 100%; object-fit: cover;">
        <?php else: ?>
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width: 100%; height: 400px; font-size: 1.5rem;">
                No Image Available
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASEURL ?>">Katalog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['category_name']) ?></li>
            </ol>
        </nav>
        
        <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h2>
        
        <div class="d-flex align-items-center mb-3">
            <div class="text-warning me-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                </svg>
                <span class="text-dark ms-1 fw-bold"><?= $rating['rating'] > 0 ? $rating['rating'] : 'Belum ada rating' ?></span>
            </div>
            <span class="text-muted">(<?= $rating['count'] ?> ulasan)</span>
        </div>
        
        <?php if (!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold fs-6">
                        🔥 Diskon <?= $product['discount_percent'] ?>%
                    </span>
                    <span class="text-muted text-decoration-line-through fs-5">
                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                    </span>
                </div>
                <div class="fw-bold text-danger display-6" style="font-weight: 800;">
                    Rp <?= number_format($product['promo_price'], 0, ',', '.') ?>
                </div>
            </div>
        <?php else: ?>
            <h3 class="fw-bold text-primary mb-4">Rp <?= number_format($product['price'], 0, ',', '.') ?></h3>
        <?php endif; ?>
        
        <div class="mb-4">
            <h6 class="fw-bold">Deskripsi Produk:</h6>
            <p class="text-muted" style="white-space: pre-line;"><?= htmlspecialchars($product['description']) ?></p>
        </div>
        
        <div class="card bg-light border-0 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-semibold">Stok Tersedia:</span>
                    <?php if($product['stock'] > 0): ?>
                        <span class="badge bg-success px-3 py-2"><?= $product['stock'] ?> pcs</span>
                    <?php else: ?>
                        <span class="badge bg-danger px-3 py-2">Habis</span>
                    <?php endif; ?>
                </div>
                
                <form id="detailAddToCartForm" action="<?= BASEURL ?>/cart/add" method="POST" onsubmit="event.preventDefault(); submitDetailCart(this);">
                    <input type="hidden" name="csrf_token" id="detailCsrfToken" value="<?= Controller::generateCsrfToken() ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="col-form-label fw-semibold">Jumlah:</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?> style="border-radius: 12px; width: 80px;">
                        </div>
                        <div class="col">
                            <button type="submit" id="detailAddCartBtn" class="btn btn-primary-modern w-100 fw-bold rounded-pill" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus-fill me-1"></i>
                                <span id="detailBtnText">Tambah ke Keranjang</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<!-- Floating Kawaii Toast Box for Detail Page -->
<div id="detailToastBox" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; pointer-events: none;"></div>

<script>
function submitDetailCart(form) {
    const btn = document.getElementById('detailAddCartBtn');
    const btnText = document.getElementById('detailBtnText');
    const originalText = btnText.innerHTML;
    
    btn.disabled = true;
    btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menambahkan...';

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
            btnText.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Berhasil Ditambahkan!';
            btn.style.background = '#10B981';
            
            // Update badges
            const navBadge = document.getElementById('navCartBadge');
            const sidebarBadge = document.getElementById('sidebarCartBadge');
            [navBadge, sidebarBadge].forEach(badge => {
                if (badge) {
                    badge.textContent = res.cart_count;
                    badge.style.display = res.cart_count > 0 ? 'inline-block' : 'none';
                    badge.style.transform = 'scale(1.35)';
                    setTimeout(() => { badge.style.transform = 'scale(1.0)'; }, 250);
                }
            });

            if (res.csrf_token) {
                document.getElementById('detailCsrfToken').value = res.csrf_token;
            }

            showDetailToast(res.message || '🌸 Produk berhasil masuk ke keranjang belanja!', 'success');

            setTimeout(() => {
                btnText.innerHTML = originalText;
                btn.style.background = '';
                btn.disabled = false;
            }, 1800);
        } else {
            showDetailToast(res.message || 'Gagal menambahkan produk.', 'warning');
            btnText.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error('AJAX Detail Cart Error:', err);
        showDetailToast('⚠️ Terjadi kendala saat menambahkan ke keranjang.', 'danger');
        btnText.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showDetailToast(msg, type = 'success') {
    const box = document.getElementById('detailToastBox');
    if (!box) return;

    const bgColors = {
        success: 'linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%)',
        warning: 'linear-gradient(135deg, #F59E0B 0%, #EF4444 100%)',
        danger: 'linear-gradient(135deg, #EF4444 0%, #DC2626 100%)'
    };

    const el = document.createElement('div');
    el.className = 'p-3 mb-2 text-white shadow-lg d-flex align-items-center justify-content-between gap-3';
    el.style.cssText = `
        background: ${bgColors[type] || bgColors.success};
        border-radius: 20px;
        min-width: 300px;
        max-width: 380px;
        font-size: 0.88rem;
        font-weight: 600;
        pointer-events: auto;
        box-shadow: 0 10px 25px rgba(236, 72, 153, 0.35) !important;
    `;
    el.innerHTML = `<span>${msg}</span><button type="button" class="btn-close btn-close-white btn-sm" onclick="this.parentElement.remove()"></button>`;
    box.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.35s';
        setTimeout(() => el.remove(), 350);
    }, 3500);
}
</script>
