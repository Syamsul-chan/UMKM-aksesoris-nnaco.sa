<div class="row">
    <div class="col-md-8">
        <h4 class="mb-4">Checkout</h4>
        
        <form action="<?= BASEURL ?>/checkout/process" method="POST" id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?= Controller::generateCsrfToken() ?>">
            <!-- 1. Alamat Pengiriman -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">1. Alamat Pengiriman</h5>
                    
                    <?php if (!empty($addresses)): ?>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold small">Pilih Alamat Tersimpan</label>
                            <select name="address_id" class="form-select" id="savedAddressSelect">
                                <option value="">-- Gunakan alamat baru --</option>
                                <?php foreach($addresses as $index => $addr): ?>
                                    <option value="<?= $addr['id'] ?>" <?= $index === 0 ? 'selected' : '' ?>><?= htmlspecialchars($addr['address_line'] . ', ' . $addr['city']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div id="newAddressForm" class="<?= !empty($addresses) ? 'd-none' : '' ?>">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="new_address" value="1" id="newAddressCheck" <?= empty($addresses) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="newAddressCheck">
                                Tambah Alamat Baru
                            </label>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted fw-semibold small">Alamat Lengkap</label>
                                <textarea name="address_line" class="form-control" rows="2" placeholder="Jalan, RT/RW, Desa/Kelurahan"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold small">Kecamatan / Daerah (Cirebon)</label>
                                <select name="city" class="form-select" required>
                                    <option value="">-- Pilih Daerah --</option>
                                    <optgroup label="Kota Cirebon">
                                        <option value="Harjamukti, Kota Cirebon">Harjamukti</option>
                                        <option value="Kejaksan, Kota Cirebon">Kejaksan</option>
                                        <option value="Kesambi, Kota Cirebon">Kesambi</option>
                                        <option value="Lemahwungkuk, Kota Cirebon">Lemahwungkuk</option>
                                        <option value="Pekalipan, Kota Cirebon">Pekalipan</option>
                                    </optgroup>
                                    <optgroup label="Kabupaten Cirebon">
                                        <option value="Arjawinangun, Kab. Cirebon">Arjawinangun</option>
                                        <option value="Astanajapura, Kab. Cirebon">Astanajapura</option>
                                        <option value="Babakan, Kab. Cirebon">Babakan</option>
                                        <option value="Beber, Kab. Cirebon">Beber</option>
                                        <option value="Ciledug, Kab. Cirebon">Ciledug</option>
                                        <option value="Ciwaringin, Kab. Cirebon">Ciwaringin</option>
                                        <option value="Depok, Kab. Cirebon">Depok</option>
                                        <option value="Dukupuntang, Kab. Cirebon">Dukupuntang</option>
                                        <option value="Gebang, Kab. Cirebon">Gebang</option>
                                        <option value="Gegesik, Kab. Cirebon">Gegesik</option>
                                        <option value="Gempol, Kab. Cirebon">Gempol</option>
                                        <option value="Greged, Kab. Cirebon">Greged</option>
                                        <option value="Gunungjati, Kab. Cirebon">Gunungjati</option>
                                        <option value="Jamblang, Kab. Cirebon">Jamblang</option>
                                        <option value="Kaliwedi, Kab. Cirebon">Kaliwedi</option>
                                        <option value="Kapetakan, Kab. Cirebon">Kapetakan</option>
                                        <option value="Karangsembung, Kab. Cirebon">Karangsembung</option>
                                        <option value="Karangwareng, Kab. Cirebon">Karangwareng</option>
                                        <option value="Kedawung, Kab. Cirebon">Kedawung</option>
                                        <option value="Klangenan, Kab. Cirebon">Klangenan</option>
                                        <option value="Lemahabang, Kab. Cirebon">Lemahabang</option>
                                        <option value="Losari, Kab. Cirebon">Losari</option>
                                        <option value="Mundu, Kab. Cirebon">Mundu</option>
                                        <option value="Pabedilan, Kab. Cirebon">Pabedilan</option>
                                        <option value="Pabuaran, Kab. Cirebon">Pabuaran</option>
                                        <option value="Palimanan, Kab. Cirebon">Palimanan</option>
                                        <option value="Pangenan, Kab. Cirebon">Pangenan</option>
                                        <option value="Panguragan, Kab. Cirebon">Panguragan</option>
                                        <option value="Pasaleman, Kab. Cirebon">Pasaleman</option>
                                        <option value="Plered, Kab. Cirebon">Plered</option>
                                        <option value="Plumbon, Kab. Cirebon">Plumbon</option>
                                        <option value="Sedong, Kab. Cirebon">Sedong</option>
                                        <option value="Sumber, Kab. Cirebon">Sumber</option>
                                        <option value="Suranenggala, Kab. Cirebon">Suranenggala</option>
                                        <option value="Susukan, Kab. Cirebon">Susukan</option>
                                        <option value="Susukanlebak, Kab. Cirebon">Susukanlebak</option>
                                        <option value="Talun, Kab. Cirebon">Talun</option>
                                        <option value="Tengahtani, Kab. Cirebon">Tengahtani</option>
                                        <option value="Waled, Kab. Cirebon">Waled</option>
                                        <option value="Weru, Kab. Cirebon">Weru</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold small">Provinsi</label>
                                <input type="text" name="province" class="form-control" value="Jawa Barat" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold small">Kode Pos</label>
                                <input type="text" name="postal_code" class="form-control" placeholder="12345">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold small">Nomor Telepon</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="08123456789">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Metode Pembayaran -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">2. Metode Pembayaran</h5>
                    
                    <div class="list-group">
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" value="Transfer Bank" checked style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content">
                                <strong>Transfer Bank (Manual)</strong>
                                <small class="d-block text-muted">Pembayaran melalui rekening bank BCA/Mandiri.</small>
                            </span>
                        </label>
                        <label class="list-group-item d-flex gap-3">
                            <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" value="COD" style="font-size: 1.375em;">
                            <span class="pt-1 form-checked-content">
                                <strong>Cash on Delivery (COD)</strong>
                                <small class="d-block text-muted">Bayar saat pesanan tiba di tujuan.</small>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Ringkasan Order -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Ringkasan Order</h5>
                
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                            </div>
                            <span class="text-muted">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold h5 mb-0">Total</span>
                    <span class="fw-bold h5 mb-0 text-success">Rp <?= number_format($total ?? 0, 0, ',', '.') ?></span>
                </div>
                
                <button type="button" class="btn btn-primary w-100 py-2 fw-semibold" onclick="document.getElementById('checkoutForm').submit();">Buat Pesanan</button>
            </div>
        </div>
    </div>
</div>

<script>
    const selectAddress = document.getElementById('savedAddressSelect');
    const newAddressForm = document.getElementById('newAddressForm');
    const newAddressCheck = document.getElementById('newAddressCheck');
    
    if(selectAddress) {
        selectAddress.addEventListener('change', function() {
            if(this.value === "") {
                newAddressForm.classList.remove('d-none');
                newAddressCheck.checked = true;
            } else {
                newAddressForm.classList.add('d-none');
                newAddressCheck.checked = false;
            }
        });
    }
</script>
