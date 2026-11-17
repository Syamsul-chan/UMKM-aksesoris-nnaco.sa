<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Tulis Ulasan Produk</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <?php if($product['image']): ?>
                        <img src="<?= BASEURL ?>/img/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="rounded shadow-sm me-3" style="width: 60px; height: 60px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">IMG</div>
                    <?php endif; ?>
                    <div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                        <span class="text-muted small">Bagaimana penilaian Anda terhadap produk ini?</span>
                    </div>
                </div>

                <form action="<?= BASEURL ?>/review/add/<?= $product['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
                    <div class="mb-4 text-center">
                        <label class="form-label fw-semibold d-block mb-3">Rating</label>
                        <div class="rating-input d-flex justify-content-center flex-row-reverse gap-2">
                            <input type="radio" name="rating" value="5" id="star5" required class="d-none">
                            <label for="star5" class="star" title="5 Bintang">&#9733;</label>
                            
                            <input type="radio" name="rating" value="4" id="star4" class="d-none">
                            <label for="star4" class="star" title="4 Bintang">&#9733;</label>
                            
                            <input type="radio" name="rating" value="3" id="star3" class="d-none">
                            <label for="star3" class="star" title="3 Bintang">&#9733;</label>
                            
                            <input type="radio" name="rating" value="2" id="star2" class="d-none">
                            <label for="star2" class="star" title="2 Bintang">&#9733;</label>
                            
                            <input type="radio" name="rating" value="1" id="star1" class="d-none">
                            <label for="star1" class="star" title="1 Bintang">&#9733;</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Komentar Ulasan</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Ceritakan pengalaman Anda menggunakan produk ini..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">Kirim Ulasan</button>
                        <a href="<?= BASEURL ?>/order" class="btn btn-outline-secondary py-2">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS for simple star rating */
    .rating-input .star {
        font-size: 2.5rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s ease-in-out;
    }

    .rating-input .star:hover,
    .rating-input .star:hover ~ .star,
    .rating-input input:checked ~ .star {
        color: #ffc107; /* Bootstrap warning color (yellow) */
    }
</style>
