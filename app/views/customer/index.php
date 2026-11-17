<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold">Customer Dashboard</h4>
                <p class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</p>
                <div class="alert alert-info mt-3">
                    Ini adalah area khusus Customer. Anda memiliki akses ke fitur belanja.
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center py-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-box-seam text-primary mb-3" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.84l-6.5-2.6v7.922l6.5 2.6zM15 4.625l-6.5 2.6v7.922l6.5-2.6V4.625z"/>
                                </svg>
                                <h5 class="fw-bold">Pesanan Saya</h5>
                                <p class="text-muted small">Lihat riwayat transaksi dan status pesanan Anda.</p>
                                <a href="<?= BASEURL ?>/order" class="btn btn-outline-primary btn-sm px-4">Lihat Pesanan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
