<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Operator Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <!-- Stat Cards -->
    <div class="col-md-2">
        <div class="card shadow-sm border-0 bg-warning text-dark h-100">
            <div class="card-body text-center">
                <h6 class="card-title mb-2 text-black-50">Pending</h6>
                <h2 class="card-text fw-bold mb-0"><?= $counts['pending'] ?></h2>
            </div>
            <a href="<?= BASEURL ?>/operator/orders?status=pending" class="card-footer text-dark text-decoration-none text-center bg-transparent border-top border-dark border-opacity-10">
                Lihat <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card shadow-sm border-0 bg-info text-dark h-100">
            <div class="card-body text-center">
                <h6 class="card-title mb-2 text-black-50">Processing</h6>
                <h2 class="card-text fw-bold mb-0"><?= $counts['processing'] ?></h2>
            </div>
            <a href="<?= BASEURL ?>/operator/orders?status=processing" class="card-footer text-dark text-decoration-none text-center bg-transparent border-top border-dark border-opacity-10">
                Lihat <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card shadow-sm border-0 bg-primary text-white h-100">
            <div class="card-body text-center">
                <h6 class="card-title mb-2 text-white-50">Packed</h6>
                <h2 class="card-text fw-bold mb-0"><?= $counts['packed'] ?></h2>
            </div>
            <a href="<?= BASEURL ?>/operator/orders?status=packed" class="card-footer text-white text-decoration-none text-center bg-transparent border-top border-light border-opacity-25">
                Lihat <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-secondary text-white h-100">
            <div class="card-body text-center">
                <h6 class="card-title mb-2 text-white-50">Shipped (Dalam Pengiriman)</h6>
                <h2 class="card-text fw-bold mb-0"><?= $counts['shipped'] ?></h2>
            </div>
            <a href="<?= BASEURL ?>/operator/orders?status=shipped" class="card-footer text-white text-decoration-none text-center bg-transparent border-top border-light border-opacity-25">
                Lihat <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-success text-white h-100">
            <div class="card-body text-center">
                <h6 class="card-title mb-2 text-white-50">Completed (Selesai)</h6>
                <h2 class="card-text fw-bold mb-0"><?= $counts['completed'] ?></h2>
            </div>
            <a href="<?= BASEURL ?>/operator/orders?status=completed" class="card-footer text-white text-decoration-none text-center bg-transparent border-top border-light border-opacity-25">
                Lihat <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Alur Kerja Operator</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="d-flex justify-content-between align-items-center mb-3 text-muted">
                    <div class="badge bg-warning text-dark p-2 w-100 border border-dark border-opacity-10">1. Pending</div>
                    <i class="bi bi-chevron-right mx-2 fs-5"></i>
                    <div class="badge bg-info text-dark p-2 w-100 border border-dark border-opacity-10">2. Processing</div>
                    <i class="bi bi-chevron-right mx-2 fs-5"></i>
                    <div class="badge bg-primary p-2 w-100">3. Packed</div>
                    <i class="bi bi-chevron-right mx-2 fs-5"></i>
                    <div class="badge bg-secondary p-2 w-100">4. Shipped</div>
                    <i class="bi bi-chevron-right mx-2 fs-5"></i>
                    <div class="badge bg-success p-2 w-100">5. Completed</div>
                </div>
                <p class="text-start small mb-0 mt-3"><strong>Catatan:</strong> Status pesanan hanya dapat diubah maju ke tahap berikutnya. Pesanan hanya dapat dibatalkan saat masih berstatus <em>Pending</em> atau <em>Processing</em>.</p>
            </div>
        </div>
    </div>
</div>
