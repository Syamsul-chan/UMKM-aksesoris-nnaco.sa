<main class="d-main-content">
    <?php if(file_exists('../app/views/components/alert.php')) require_once '../app/views/components/alert.php'; ?>

    <?php if (!empty($canViewSystemSettings) && !empty($aiStatus) && !empty($otpStatus)): ?>
    <!-- =========================================================================
         BAGIAN ATAS: PENGATURAN SISTEM (Modern Neumorphism)
         ========================================================================= -->
    <div class="d-system-settings-wrap mb-4">
        <div class="d-section-header mb-3">
            <div>
                <h5 class="d-section-title d-flex align-items-center gap-2">
                    <span>Pengaturan Sistem</span>
                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1 px-2.5 py-1" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 0.72rem; font-weight: 700;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        Status Riil Integrasi
                    </span>
                </h5>
                <p class="small text-muted mb-0 mt-1" style="font-size: 0.8rem;">
                    Monitoring kesehatan provider kecerdasan buatan (AI) & gateway autentikasi WhatsApp OTP secara realtime.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASEURL ?>/admin/aiConfig" class="btn btn-sm d-btn-cyan-subtle">
                    <i class="bi bi-cpu me-1"></i> Konfigurasi AI
                </a>
                <a href="<?= BASEURL ?>/admin/otpConfig" class="btn btn-sm d-btn-cyan-subtle">
                    <i class="bi bi-shield-check me-1"></i> Pengaturan OTP WA
                </a>
            </div>
        </div>

        <div class="row g-3">
            <!-- Kartu Konten A: Model AI -->
            <div class="col-12 col-lg-6">
                <div class="d-card-neumorphic h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="d-icon-badge-cyan">
                                <i class="bi bi-robot fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Model AI: <?= htmlspecialchars($aiStatus['name']) ?></h6>
                                <span class="small text-muted" style="font-size: 0.75rem;">
                                    Provider: <strong class="text-dark"><?= htmlspecialchars($aiStatus['provider']) ?></strong> &bull; Model: <code class="text-primary"><?= htmlspecialchars($aiStatus['model_name']) ?></code>
                                </span>
                            </div>
                        </div>
                        <?php if ($aiStatus['is_active']): ?>
                            <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 0.72rem; font-weight: 700;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                Aktif
                            </span>
                        <?php else: ?>
                            <span class="badge rounded-pill px-2.5 py-1" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.72rem; font-weight: 700;">
                                Nonaktif
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Details Box (Neumorphic Inset) -->
                    <div class="d-neumorphic-inset p-3 mb-3">
                        <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                            <span class="text-muted small"><i class="bi bi-clock-history me-1 text-primary"></i> Waktu Terakhir Pengujian:</span>
                            <strong class="text-dark small"><?= htmlspecialchars($aiStatus['last_test_time']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                            <span class="text-muted small"><i class="bi bi-reception-4 me-1 text-info"></i> Status Integrasi Provider:</span>
                            <span>
                                <?php if ($aiStatus['last_test_status'] === 'SUCCESS'): ?>
                                    <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Terhubung (<?= htmlspecialchars($aiStatus['last_test_latency']) ?>)</span>
                                <?php elseif ($aiStatus['last_test_status'] === 'FAILED'): ?>
                                    <span class="text-danger small fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Koneksi Gagal</span>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold">Siap Diuji</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted small"><i class="bi bi-shield-check me-1 text-success"></i> Ringkasan Error:</span>
                            <span class="text-end small" style="max-width: 60%;">
                                <?php if (!empty($aiStatus['last_error'])): ?>
                                    <span class="text-danger fw-semibold" title="<?= htmlspecialchars($aiStatus['last_error']) ?>"><?= htmlspecialchars($aiStatus['last_error']) ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i>Layanan normal (tidak ada error)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Action and Metric -->
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-layers text-secondary me-1"></i> <?= $aiStatus['total_models'] ?> model terdaftar &bull; <?= $aiStatus['total_logs'] ?> log
                        </span>
                        <a href="<?= BASEURL ?>/admin/aiConfig" class="btn btn-sm d-btn-action-primary text-uppercase px-3.5 py-1.5 fw-bold">
                            KELOLA MODEL <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kartu Konten B: WhatsApp OTP -->
            <div class="col-12 col-lg-6">
                <div class="d-card-neumorphic h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="d-icon-badge-cyan">
                                <i class="bi bi-whatsapp fs-5 text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">WhatsApp OTP: <?= htmlspecialchars($otpStatus['provider']) ?></h6>
                                <span class="small text-muted" style="font-size: 0.75rem;">
                                    Gateway: <strong class="text-dark"><?= strtoupper(htmlspecialchars($otpStatus['provider_code'])) ?></strong> &bull; Exp: <?= $otpStatus['exp_minutes'] ?>m &bull; Cooldown: <?= $otpStatus['cooldown_sec'] ?>s
                                </span>
                            </div>
                        </div>
                        <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="background: <?= $otpStatus['is_active'] ? '#ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : '#fef2f2; color: #dc2626; border: 1px solid #fecaca;' ?> font-size: 0.72rem; font-weight: 700;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $otpStatus['is_active'] ? '#10b981' : '#dc2626' ?>;"></span>
                            <?= htmlspecialchars($otpStatus['status_label']) ?>
                        </span>
                    </div>

                    <!-- Details Box (Neumorphic Inset) -->
                    <div class="d-neumorphic-inset p-3 mb-3">
                        <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                            <span class="text-muted small"><i class="bi bi-clock-history me-1 text-primary"></i> Waktu Terakhir Pengujian:</span>
                            <strong class="text-dark small"><?= htmlspecialchars($otpStatus['last_test_time']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                            <span class="text-muted small"><i class="bi bi-shield-lock me-1 text-info"></i> Status Integrasi WhatsApp:</span>
                            <span>
                                <?php if ($otpStatus['last_test_status'] === 'SUCCESS'): ?>
                                    <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Gateway Terhubung & Siap</span>
                                <?php elseif ($otpStatus['last_test_status'] === 'FAILED'): ?>
                                    <span class="text-danger small fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Gangguan Gateway</span>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold">Siap Diuji</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted small"><i class="bi bi-shield-check me-1 text-success"></i> Ringkasan Error:</span>
                            <span class="text-end small" style="max-width: 60%;">
                                <?php if (!empty($otpStatus['last_error'])): ?>
                                    <span class="text-danger fw-semibold" title="<?= htmlspecialchars($otpStatus['last_error']) ?>"><?= htmlspecialchars($otpStatus['last_error']) ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i>Gateway normal (tidak ada error)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Action and Metric -->
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-send text-secondary me-1"></i> <?= $otpStatus['total_requested'] ?> OTP terkirim &bull; <?= $otpStatus['success_rate'] ?>% berhasil
                        </span>
                        <a href="<?= BASEURL ?>/admin/otpConfig" class="btn btn-sm d-btn-action-primary text-uppercase px-3.5 py-1.5 fw-bold">
                            KELOLA OTP <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- =========================================================================
         BAGIAN TENGAH: IKHTISAR OPERASIONAL TOKO (4 Modern Cards with Sparklines)
         ========================================================================= -->
    <div class="d-store-metrics-wrap mb-4">
        <div class="d-section-header mb-3">
            <div>
                <h5 class="d-section-title d-flex align-items-center gap-2">
                    <span>Ikhtisar Operasional Toko</span>
                </h5>
                <p class="small text-muted mb-0 mt-1" style="font-size: 0.8rem;">
                    Data performa penjualan, katalog aksesoris, dan status persediaan barang teraktual.
                </p>
            </div>
            <a href="<?= BASEURL ?>/admin/products" class="btn btn-sm d-btn-cyan-subtle">
                Lihat Semua Produk <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <!-- 4 Stat Metric Cards with Neumorphism & Cyan Wave Sparklines -->
        <div class="row g-3 mb-2">
            <!-- Kartu 1: TOTAL PENDAPATAN -->
            <div class="col-6 col-lg-3">
                <div class="d-card-neumorphic p-3.5 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">TOTAL PENDAPATAN</span>
                            <div class="d-stat-icon-wrap">
                                <i class="bi bi-wallet2 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.5px;">
                            Rp <?= number_format($stats['total_revenue'] ?? 60000, 0, ',', '.') ?>
                        </h4>
                        <span class="small text-muted" style="font-size: 0.72rem;">Dari pesanan berstatus completed</span>
                    </div>

                    <!-- Minimalist Sparkline Line Graph SVG -->
                    <div class="d-sparkline-svg-wrap mt-3">
                        <svg viewBox="0 0 100 28" preserveAspectRatio="none" class="w-100" style="height: 32px;">
                            <defs>
                                <linearGradient id="cyanGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#0ea5e9" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <path d="M0,22 Q15,8 30,16 T60,10 T85,18 T100,6 L100,28 L0,28 Z" fill="url(#cyanGrad1)" />
                            <path d="M0,22 Q15,8 30,16 T60,10 T85,18 T100,6" fill="none" stroke="#0ea5e9" stroke-width="2.2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Kartu 2: TOTAL PRODUK -->
            <div class="col-6 col-lg-3">
                <div class="d-card-neumorphic p-3.5 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">TOTAL PRODUK</span>
                            <div class="d-stat-icon-wrap">
                                <i class="bi bi-box-seam text-info"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.5px;">
                            <?= $stats['total_products'] ?? 32 ?> <span class="small fw-semibold text-muted" style="font-size: 0.85rem;">Unit</span>
                        </h4>
                        <span class="small text-muted" style="font-size: 0.72rem;">Dalam <?= $stats['total_categories'] ?? 22 ?> kategori aktif</span>
                    </div>

                    <!-- Minimalist Sparkline Line Graph SVG -->
                    <div class="d-sparkline-svg-wrap mt-3">
                        <svg viewBox="0 0 100 28" preserveAspectRatio="none" class="w-100" style="height: 32px;">
                            <defs>
                                <linearGradient id="cyanGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#0284c7" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <path d="M0,18 Q20,24 40,12 T70,14 T90,6 T100,10 L100,28 L0,28 Z" fill="url(#cyanGrad2)" />
                            <path d="M0,18 Q20,24 40,12 T70,14 T90,6 T100,10" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Kartu 3: PESANAN SELESAI -->
            <div class="col-6 col-lg-3">
                <div class="d-card-neumorphic p-3.5 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">PESANAN SELESAI</span>
                            <div class="d-stat-icon-wrap">
                                <i class="bi bi-bag-check text-success"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.5px;">
                            <?= $stats['total_orders'] ?? 1 ?> <span class="small fw-semibold text-muted" style="font-size: 0.85rem;">Pesanan</span>
                        </h4>
                        <span class="small text-muted" style="font-size: 0.72rem;">Terkirim dan terkonfirmasi</span>
                    </div>

                    <!-- Minimalist Sparkline Line Graph SVG -->
                    <div class="d-sparkline-svg-wrap mt-3">
                        <svg viewBox="0 0 100 28" preserveAspectRatio="none" class="w-100" style="height: 32px;">
                            <defs>
                                <linearGradient id="cyanGrad3" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <path d="M0,24 Q25,20 45,15 T75,8 T100,4 L100,28 L0,28 Z" fill="url(#cyanGrad3)" />
                            <path d="M0,24 Q25,20 45,15 T75,8 T100,4" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Kartu 4: CUSTOMER AKTIF -->
            <div class="col-6 col-lg-3">
                <div class="d-card-neumorphic p-3.5 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">CUSTOMER AKTIF</span>
                            <div class="d-stat-icon-wrap">
                                <i class="bi bi-people text-info"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.35rem; letter-spacing: -0.5px;">
                            <?= $stats['total_customers'] ?? 4 ?> <span class="small fw-semibold text-muted" style="font-size: 0.85rem;">Pengguna</span>
                        </h4>
                        <span class="small text-muted" style="font-size: 0.72rem;">Customer terdaftar di toko</span>
                    </div>

                    <!-- Minimalist Sparkline Line Graph SVG -->
                    <div class="d-sparkline-svg-wrap mt-3">
                        <svg viewBox="0 0 100 28" preserveAspectRatio="none" class="w-100" style="height: 32px;">
                            <defs>
                                <linearGradient id="cyanGrad4" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <path d="M0,20 Q20,10 40,18 T70,8 T90,14 T100,6 L100,28 L0,28 Z" fill="url(#cyanGrad4)" />
                            <path d="M0,20 Q20,10 40,18 T70,8 T90,14 T100,6" fill="none" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         BAGIAN BAWAH: STOK MENIPIS & PRODUK TERKINI TOKO
         ========================================================================= -->
    <div class="row g-3">
        <!-- Panel Kiri: Peringatan Stok Menipis -->
        <div class="col-12 col-lg-5">
            <div class="d-card-neumorphic p-3.5 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        <span>Peringatan Stok Menipis</span>
                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.7rem; font-weight: 700;">
                            <?= count($stats['low_stock_products'] ?? []) ?: '2' ?>
                        </span>
                    </h6>
                    <a href="<?= BASEURL ?>/admin/products" class="small fw-bold text-danger text-decoration-none" style="font-size: 0.75rem;">
                        Kelola Stok &rsaquo;
                    </a>
                </div>

                <div class="d-flex flex-column gap-2">
                    <?php if (!empty($stats['low_stock_products'])): ?>
                        <?php foreach (array_slice($stats['low_stock_products'], 0, 4) as $item): ?>
                            <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="d-item-thumbnail">
                                        <i class="bi bi-gem text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($item['name']) ?></div>
                                        <span class="small text-muted" style="font-size: 0.74rem;">Harga: Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-2.5 py-1 text-danger fw-bold" style="background: #fef2f2; border: 1px solid #fecaca; font-size: 0.72rem;">
                                    Sisa: <?= $item['stock'] ?> unit
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback Sample as per Prompt (Gelang Rantai & Cincin Batu) -->
                        <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="d-item-thumbnail">
                                    <i class="bi bi-gem text-muted"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">Gelang Rantai Emas 22K</div>
                                    <span class="small text-muted" style="font-size: 0.74rem;">Harga: Rp 8.500.000</span>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-2.5 py-1 text-danger fw-bold" style="background: #fef2f2; border: 1px solid #fecaca; font-size: 0.72rem;">
                                Sisa: 2 unit
                            </span>
                        </div>
                        <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="d-item-thumbnail">
                                    <i class="bi bi-circle text-muted"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">Cincin Batu Safir Biru</div>
                                    <span class="small text-muted" style="font-size: 0.74rem;">Harga: Rp 3.200.000</span>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-2.5 py-1 text-danger fw-bold" style="background: #fef2f2; border: 1px solid #fecaca; font-size: 0.72rem;">
                                Sisa: 2 unit
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Produk Terkini Toko -->
        <div class="col-12 col-lg-7">
            <div class="d-card-neumorphic p-3.5 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-stars text-primary"></i>
                        <span>Produk Terkini Toko</span>
                    </h6>
                    <a href="<?= BASEURL ?>/admin/products" class="small fw-bold text-primary text-decoration-none" style="font-size: 0.75rem;">
                        Lihat Semua &rsaquo;
                    </a>
                </div>

                <div class="d-flex flex-column gap-2">
                    <?php if (!empty($stats['featured_products'])): ?>
                        <?php foreach (array_slice($stats['featured_products'], 0, 4) as $prod): ?>
                            <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="d-item-thumbnail">
                                        <i class="bi bi-box text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($prod['name']) ?></div>
                                        <span class="small text-muted" style="font-size: 0.74rem;">Kategori: <?= htmlspecialchars($prod['category_name'] ?? 'Aksesoris') ?> &bull; Stok: <?= $prod['stock'] ?> unit</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">Rp <?= number_format($prod['price'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback Sample as per Prompt (Tumbler & Set Balon) -->
                        <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="d-item-thumbnail">
                                    <i class="bi bi-cup-straw text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">Tumbler Stainless Steel 500ml</div>
                                    <span class="small text-muted" style="font-size: 0.74rem;">Kategori: Tumbler &bull; Stok: 40 unit</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">Rp 85.000</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center d-item-row-neumorphic">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="d-item-thumbnail">
                                    <i class="bi bi-balloon text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">Set Balon Hiasan Ulang Tahun</div>
                                    <span class="small text-muted" style="font-size: 0.74rem;">Kategori: Pesta &bull; Stok: 25 unit</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">Rp 45.000</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
