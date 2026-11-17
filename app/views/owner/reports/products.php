<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Produk Terlaris</h4>
    <a href="<?= BASEURL ?>/owner" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body py-2">
        <span class="text-muted small">Menampilkan data untuk filter: <strong><?= ucfirst(str_replace('_', ' ', $dates['filter'])) ?></strong> 
        (<?= date('d M Y', strtotime($dates['start'])) ?> - <?= date('d M Y', strtotime($dates['end'])) ?>)</span>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Peringkat</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Total Terjual (Unit)</th>
                        <th class="text-end pe-4">Total Pendapatan (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada penjualan produk pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php $rank = 1; ?>
                        <?php foreach($products as $product): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $rank++ ?></td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="text-center"><?= $product['total_sold'] ?></td>
                                <td class="text-end pe-4 fw-semibold text-success"><?= number_format($product['revenue'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
