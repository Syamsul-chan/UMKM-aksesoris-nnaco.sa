<div class="row justify-content-center">
    <div class="col-md-8 text-center mt-5">
        <div class="card shadow-sm border-0 p-5">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h2 class="fw-bold mb-3">Pesanan Berhasil Dibuat!</h2>
            <p class="text-muted fs-5 mb-4">Terima kasih telah berbelanja di nnaco_sa. Nomor pesanan Anda adalah <strong>#<?= htmlspecialchars($order_id) ?></strong>.</p>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= BASEURL ?>" class="btn btn-outline-primary px-4">Kembali ke Beranda</a>
                <a href="<?= BASEURL ?>/customer" class="btn btn-primary px-4">Lihat Pesanan Saya</a>
            </div>
        </div>
    </div>
</div>
