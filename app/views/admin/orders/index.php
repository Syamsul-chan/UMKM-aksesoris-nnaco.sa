<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pesanan</h4>
</div>

<!-- Filter Box -->
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
        <form action="<?= BASEURL ?>/admin/orders" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Status Pesanan</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $filters['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $filters['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="completed" <?= $filters['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $filters['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filters['start_date']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filters['end_date']) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total (Rp)</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data pesanan yang sesuai.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td class="ps-4 fw-bold">TRX-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['username']) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                </td>
                                <td class="fw-bold text-success"><?= number_format($order['total_amount'], 0, ',', '.') ?></td>
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
                                    <span class="badge <?= $badgeClass ?> text-uppercase px-2 py-1">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="<?= BASEURL ?>/admin/orderDetail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if($pagination['total_pages'] > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php 
                $queryString = http_build_query([
                    'status' => $filters['status'],
                    'start_date' => $filters['start_date'],
                    'end_date' => $filters['end_date']
                ]);
            ?>
            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASEURL ?>/admin/orders?<?= $queryString ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
