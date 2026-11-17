<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Stok Inventaris</h4>
    <a href="<?= BASEURL ?>/owner" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($stocks)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data produk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($stocks as $stock): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $stock['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($stock['name']) ?></td>
                                <td><?= htmlspecialchars($stock['category_name']) ?></td>
                                <td class="text-center">
                                    <?php if($stock['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if($stock['stock'] <= 5): ?>
                                        <span class="badge bg-danger fs-6"><?= $stock['stock'] ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold"><?= $stock['stock'] ?></span>
                                    <?php endif; ?>
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
            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASEURL ?>/owner/stockReport?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
