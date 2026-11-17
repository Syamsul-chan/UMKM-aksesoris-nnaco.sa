<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Detail Pesanan TRX-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                <?php 
                    $badgeClass = 'bg-secondary';
                    switch(strtolower($order['status'])) {
                        case 'pending': $badgeClass = 'bg-warning text-dark'; break;
                        case 'processing': $badgeClass = 'bg-info text-dark'; break;
                        case 'shipped': $badgeClass = 'bg-primary'; break;
                        case 'completed': $badgeClass = 'bg-success'; break;
                        case 'cancelled': $badgeClass = 'bg-danger'; break;
                    }
                ?>
                <span class="badge <?= $badgeClass ?> px-3 py-2 text-uppercase" style="font-size: 0.85rem;">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th class="pe-4 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orderDetails as $item): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold">
                                        <a href="<?= BASEURL ?>/home/detail/<?= $item['product_id'] ?>" class="text-decoration-none text-dark" target="_blank">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="pe-4 fw-bold text-end">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3 pe-4">
                <span class="text-muted me-3">Total Pembayaran:</span>
                <span class="fs-4 fw-bold text-success">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- ADMIN ACTIONS: UPDATE STATUS PESANAN -->
        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Update Status Pesanan</h6>
                    <?php if(!empty($order['phone_number'])): ?>
                        <span class="badge bg-success small" title="Notifikasi WhatsApp Otomatis Aktif">
                            <i class="bi bi-whatsapp"></i> WA Aktif
                        </span>
                    <?php endif; ?>
                </div>

                <?php $currentStatus = strtolower($order['status']); ?>
                
                <form action="<?= BASEURL ?>/admin/updateStatus/<?= $order['id'] ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui status pesanan ini? Notifikasi WhatsApp akan otomatis dikirim ke pelanggan.');">
                    <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label small text-white-50">Pilih Status Baru:</label>
                        <select name="status" class="form-select fw-semibold" required>
                            <option value="pending" <?= $currentStatus == 'pending' ? 'selected' : '' ?>>⏳ Pending (Menunggu Verifikasi)</option>
                            <option value="processing" <?= $currentStatus == 'processing' ? 'selected' : '' ?>>👩‍🍳 Processing (Sedang Diproses)</option>
                            <option value="packed" <?= $currentStatus == 'packed' ? 'selected' : '' ?>>📦 Packed (Selesai Dikemas)</option>
                            <option value="shipped" <?= $currentStatus == 'shipped' ? 'selected' : '' ?>>🚚 Shipped (Dalam Pengiriman)</option>
                            <option value="completed" <?= $currentStatus == 'completed' ? 'selected' : '' ?>>✅ Completed (Pesanan Selesai)</option>
                            <option value="cancelled" <?= $currentStatus == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled (Batalkan Pesanan)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-light w-100 fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Simpan Status Baru
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi Customer</h6>
                <p class="mb-1"><i class="bi bi-person me-2"></i> <?= htmlspecialchars($order['username']) ?></p>
                <p class="mb-0"><i class="bi bi-envelope me-2"></i> <a href="mailto:<?= htmlspecialchars($order['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($order['email']) ?></a></p>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi Pengiriman</h6>
                <p class="text-muted mb-0" style="white-space: pre-line;"><?= htmlspecialchars($order['shipping_address']) ?></p>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Informasi Transaksi</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tanggal:</span>
                    <span class="fw-semibold"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">No. Transaksi:</span>
                    <span class="fw-semibold">TRX-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                </div>
            </div>
        </div>

        <div class="d-grid">
            <a href="<?= BASEURL ?>/admin/orders" class="btn btn-outline-secondary">Kembali ke Daftar Pesanan</a>
        </div>
    </div>
</div>
