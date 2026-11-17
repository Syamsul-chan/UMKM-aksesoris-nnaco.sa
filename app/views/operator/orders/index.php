<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Daftar Pesanan</h4>
</div>

<!-- Tabs for Status -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm">
    <li class="nav-item">
        <a class="nav-link <?= empty($filters['status']) ? 'active' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders">Semua</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filters['status'] == 'pending' ? 'active bg-warning text-dark' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders?status=pending">Pending</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filters['status'] == 'processing' ? 'active bg-info text-dark' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders?status=processing">Processing</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filters['status'] == 'packed' ? 'active' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders?status=packed">Packed</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filters['status'] == 'shipped' ? 'active bg-secondary' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders?status=shipped">Shipped</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filters['status'] == 'completed' ? 'active bg-success' : 'text-dark' ?>" href="<?= BASEURL ?>/operator/orders?status=completed">Completed</a>
    </li>
</ul>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Total Pembayaran</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data pesanan di kategori ini.</td>
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
                                    <span class="badge <?= $badgeClass ?> text-uppercase px-2 py-1">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="<?= BASEURL ?>/operator/orderDetail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Proses Pesanan</a>
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
                    'status' => $filters['status']
                ]);
            ?>
            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASEURL ?>/operator/orders?<?= $queryString ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
