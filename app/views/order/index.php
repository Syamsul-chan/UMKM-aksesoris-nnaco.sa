<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Pesanan Saya</h4>
            <a href="<?= BASEURL ?>" class="btn btn-outline-primary">Lanjut Belanja</a>
        </div>

        <?php if(empty($orders)): ?>
            <div class="card shadow-sm border-0 py-5 text-center">
                <div class="card-body">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-box-seam text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84l-6.5-2.6v7.922l6.5 2.6zM15 4.625l-6.5 2.6v7.922l6.5-2.6V4.625z"/>
                    </svg>
                    <h5 class="text-muted">Anda belum memiliki pesanan</h5>
                    <p class="text-muted mb-0">Pesanan Anda akan muncul di sini setelah Anda melakukan checkout.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No. Order</th>
                                    <th>Tanggal Transaksi</th>
                                    <th>Total Pembayaran</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($order['id']) ?></td>
                                        <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                        <td class="fw-bold text-success">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                                        <td>
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
                                            <span class="badge <?= $badgeClass ?> px-2 py-1 text-uppercase">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <a href="<?= BASEURL ?>/order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
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
</div>
