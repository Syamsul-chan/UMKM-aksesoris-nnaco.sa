<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Detail Pesanan #<?= htmlspecialchars($order['id']) ?></h5>
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
                                <th>Subtotal</th>
                                <th class="pe-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orderDetails as $item): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold">
                                        <a href="<?= BASEURL ?>/home/detail/<?= $item['product_id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="fw-bold">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                    <td class="pe-4 text-center">
                                        <?php if(strtolower($order['status']) == 'completed'): ?>
                                            <?php if(!$item['has_reviewed']): ?>
                                                <a href="<?= BASEURL ?>/review/add/<?= $item['product_id'] ?>" class="btn btn-sm btn-outline-success">Beri Ulasan</a>
                                            <?php else: ?>
                                                <span class="badge bg-light text-success border border-success">Selesai Diulas</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Menunggu Selesai</span>
                                        <?php endif; ?>
                                    </td>
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
    </div>
</div>
