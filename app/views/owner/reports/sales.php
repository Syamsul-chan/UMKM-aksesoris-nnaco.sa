<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Penjualan</h4>
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
                        <th class="ps-4">No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th class="text-end pe-4">Total Pendapatan (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($sales)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Tidak ada transaksi selesai pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php $grandTotal = 0; ?>
                        <?php foreach($sales as $sale): ?>
                            <?php $grandTotal += $sale['total_amount']; ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">TRX-<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d M Y, H:i', strtotime($sale['created_at'])) ?></td>
                                <td><?= htmlspecialchars($sale['username']) ?></td>
                                <td class="text-end pe-4 fw-semibold text-success"><?= number_format($sale['total_amount'], 0, ',', '.') ?></td>
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
                    'filter' => $dates['filter'],
                    'start_date' => $dates['start'],
                    'end_date' => $dates['end']
                ]);
            ?>
            <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASEURL ?>/owner/salesReport?<?= $queryString ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
