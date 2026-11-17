<main class="d-main-content">
    <?php if(file_exists('../app/views/components/alert.php')) require_once '../app/views/components/alert.php'; ?>

    <?php if (!empty($aiStatus) && !empty($otpStatus)): ?>
    <!-- =========================================================================
         EXECUTIVE SUMMARY: AI Intelligence & WhatsApp Security (Owner)
         ========================================================================= -->
    <div class="d-owner-ai-summary-wrap mb-2">
        <div class="d-card-white-box p-3.5" style="border: 2px solid #F0E6F8; border-radius: 24px; background: #FFFFFF; box-shadow: 0 8px 24px rgba(180, 150, 220, 0.1);">
            
            <!-- Top Header Row -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2.5 border-bottom" style="border-color: #F4EFF8 !important;">
                <div class="d-flex align-items-center gap-2.5">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: linear-gradient(135deg, #FFE5EC 0%, #FFB6C1 100%); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 4px 10px rgba(255, 117, 160, 0.25);">
                        👑
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                            <span>Ringkasan Eksekutif AI & Layanan OTP</span>
                            <span class="badge rounded-pill" style="background: #FAF5FF; color: #7C3AED; border: 1px solid #D8B4FE; font-size: 0.7rem; font-weight: 800;">Owner View</span>
                        </h5>
                        <p class="small text-muted m-0 mt-0.5" style="font-size: 0.77rem;">
                            Ikhtisar performa asisten cerdas toko, intelijen bisnis, dan gerbang autentikasi OTP WhatsApp.
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASEURL ?>/owner/aiConfig" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5" style="background: #F3E8FF; color: #7C3AED; border: 1.5px solid #D8B4FE; font-size: 0.78rem;">
                        <i class="bi bi-cpu-fill text-purple"></i> <span>Kelola AI</span>
                    </a>
                    <a href="<?= BASEURL ?>/owner/otpConfig" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5" style="background: #DCFCE7; color: #166534; border: 1.5px solid #86EFAC; font-size: 0.78rem;">
                        <i class="bi bi-whatsapp text-success"></i> <span>Kelola OTP WA</span>
                    </a>
                </div>
            </div>

            <!-- 4 Service Status Columns -->
            <div class="row g-2.5 mb-3">
                <!-- 1. Model AI Aktif -->
                <div class="col-6 col-lg-3">
                    <div class="p-2.5 rounded-3 h-100" style="background: #FAF5FF; border: 1.5px solid #F3E8FF;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-muted" style="font-size: 0.72rem;">MODEL AI AKTIF</span>
                            <span class="badge rounded-pill" style="background: <?= $aiStatus['is_active'] ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;' ?> font-size: 0.65rem; font-weight: 800;">
                                <?= $aiStatus['is_active'] ? 'AKTIF' : 'NONAKTIF' ?>
                            </span>
                        </div>
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;" title="<?= htmlspecialchars($aiStatus['name']) ?>">
                            <?= htmlspecialchars($aiStatus['name']) ?>
                        </div>
                        <div class="small text-muted mt-0.5" style="font-size: 0.72rem;">
                            <?= htmlspecialchars($aiStatus['provider']) ?> &bull; <code><?= htmlspecialchars($aiStatus['model_name']) ?></code>
                        </div>
                    </div>
                </div>

                <!-- 2. Status AI Customer Assistant -->
                <div class="col-6 col-lg-3">
                    <div class="p-2.5 rounded-3 h-100" style="background: #EFF6FF; border: 1.5px solid #DBEAFE;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-muted" style="font-size: 0.72rem;">CUSTOMER ASSISTANT</span>
                            <span class="badge rounded-pill" style="background: <?= $aiStatus['is_active'] ? '#DBEAFE; color: #1E40AF;' : '#FEE2E2; color: #991B1B;' ?> font-size: 0.65rem; font-weight: 800;">
                                <?= $aiStatus['is_active'] ? 'ONLINE' : 'OFFLINE' ?>
                            </span>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            <?= $aiStatus['is_active'] ? 'Siap Melayani 24/7' : 'Dinonaktifkan' ?>
                        </div>
                        <div class="small text-muted mt-0.5" style="font-size: 0.72rem;">
                            Chatbot rekomendasi & katalog aktif
                        </div>
                    </div>
                </div>

                <!-- 3. Status AI Business Insight -->
                <div class="col-6 col-lg-3">
                    <div class="p-2.5 rounded-3 h-100" style="background: #FFF7ED; border: 1.5px solid #FFEDD5;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-muted" style="font-size: 0.72rem;">BUSINESS INSIGHT</span>
                            <span class="badge rounded-pill" style="background: <?= $aiStatus['is_active'] ? '#FFEDD5; color: #9A3412;' : '#FEE2E2; color: #991B1B;' ?> font-size: 0.65rem; font-weight: 800;">
                                <?= $aiStatus['is_active'] ? 'READY' : 'OFF' ?>
                            </span>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            <?= $aiStatus['is_active'] ? 'Analisis Otomatis Siap' : 'Dinonaktifkan' ?>
                        </div>
                        <div class="small text-muted mt-0.5" style="font-size: 0.72rem;">
                            Menganalisis omzet, tren & stok
                        </div>
                    </div>
                </div>

                <!-- 4. Status Layanan OTP WhatsApp -->
                <div class="col-6 col-lg-3">
                    <div class="p-2.5 rounded-3 h-100" style="background: #F0FDF4; border: 1.5px solid #DCFCE7;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-muted" style="font-size: 0.72rem;">OTP WHATSAPP</span>
                            <span class="badge rounded-pill" style="background: <?= $otpStatus['is_active'] ? ($otpStatus['is_dev_mode'] ? '#FEF3C7; color: #92400E;' : '#DCFCE7; color: #166534;') : '#FEE2E2; color: #991B1B;' ?> font-size: 0.65rem; font-weight: 800;">
                                <?= htmlspecialchars($otpStatus['status_label']) ?>
                            </span>
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                            <?= htmlspecialchars($otpStatus['provider']) ?> Gateway
                        </div>
                        <div class="small text-muted mt-0.5" style="font-size: 0.72rem;">
                            Tingkat sukses: <strong class="text-success"><?= $otpStatus['success_rate'] ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrics & Sanitized Error Info Strip -->
            <div class="d-flex flex-wrap align-items-center justify-content-between p-2.5 rounded-3" style="background: #FAF8FC; border: 1px solid #F1EBFA; font-size: 0.78rem;">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="text-muted">
                        <i class="bi bi-bar-chart-fill text-primary me-1"></i> Log AI: <strong class="text-dark"><?= $aiStatus['total_logs'] ?></strong>
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-whatsapp text-success me-1"></i> Total OTP Terkirim: <strong class="text-dark"><?= $otpStatus['total_requested'] ?></strong>
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-shield-check text-info me-1"></i> OTP Terverifikasi: <strong class="text-dark"><?= $otpStatus['total_verified'] ?></strong> (<?= $otpStatus['success_rate'] ?>%)
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-calendar-check text-warning me-1"></i> Hari Ini: <strong class="text-dark"><?= $otpStatus['today_requested'] ?></strong> OTP
                    </span>
                </div>

                <div class="mt-2 mt-md-0">
                    <?php if (!empty($aiStatus['last_error']) || !empty($otpStatus['last_error'])): ?>
                        <span class="badge px-2.5 py-1 text-wrap" style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; font-weight: 600;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                            Peringatan Layanan: <?= htmlspecialchars($aiStatus['last_error'] ?? $otpStatus['last_error']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge px-2.5 py-1" style="background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; font-weight: 600;">
                            <i class="bi bi-check-circle-fill me-1 text-success"></i> Semua layanan AI & OTP beroperasi normal tanpa kendala
                        </span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- =========================================================================
         IKHTISAR KEUANGAN & PERFORMA BISNIS (Executive View)
         ========================================================================= -->
    <div class="d-owner-metrics-wrap mt-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="d-section-title d-flex align-items-center gap-2">
                    <span>📊 Ikhtisar Keuangan & Penjualan Toko</span>
                </h5>
                <p class="small text-muted mb-0 mt-1" style="font-size: 0.78rem;">
                    Pantauan omzet, rata-rata transaksi, dan tren produk aksesoris terlaris.
                </p>
            </div>

            <!-- Date Filter Pills -->
            <div class="d-flex flex-wrap gap-1 bg-white p-1 rounded-pill" style="border: 1.5px solid #F0E6F8; box-shadow: 0 2px 8px rgba(180, 150, 220, 0.08);">
                <a href="<?= BASEURL ?>/owner?filter=today" class="btn btn-sm rounded-pill px-3 py-1 fw-bold <?= ($dates['filter'] ?? '') === 'today' ? 'btn-primary' : 'text-muted' ?>" style="font-size: 0.75rem;">Hari Ini</a>
                <a href="<?= BASEURL ?>/owner?filter=this_week" class="btn btn-sm rounded-pill px-3 py-1 fw-bold <?= ($dates['filter'] ?? '') === 'this_week' ? 'btn-primary' : 'text-muted' ?>" style="font-size: 0.75rem;">Minggu Ini</a>
                <a href="<?= BASEURL ?>/owner?filter=this_month" class="btn btn-sm rounded-pill px-3 py-1 fw-bold <?= ($dates['filter'] ?? '') === 'this_month' ? 'btn-primary' : 'text-muted' ?>" style="font-size: 0.75rem;">Bulan Ini</a>
                <a href="<?= BASEURL ?>/owner?filter=this_year" class="btn btn-sm rounded-pill px-3 py-1 fw-bold <?= ($dates['filter'] ?? '') === 'this_year' ? 'btn-primary' : 'text-muted' ?>" style="font-size: 0.75rem;">Tahun Ini</a>
            </div>
        </div>

        <!-- 4 Stat Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="d-card-white-box p-3 h-100" style="border: 2px solid #FFE4E6; border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted" style="font-size: 0.75rem;">TOTAL OMZET</span>
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #FFE4E6; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">💰</div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                        Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                    </h5>
                    <span class="small text-muted" style="font-size: 0.72rem;">Pendapatan pesanan completed</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="d-card-white-box p-3 h-100" style="border: 2px solid #E0E7FF; border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted" style="font-size: 0.75rem;">TOTAL TRANSAKSI</span>
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #E0E7FF; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">📦</div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                        <?= $stats['total_orders'] ?? 0 ?> <span class="small fw-semibold text-muted" style="font-size: 0.8rem;">Pesanan</span>
                    </h5>
                    <span class="small text-muted" style="font-size: 0.72rem;">Seluruh status pesanan</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="d-card-white-box p-3 h-100" style="border: 2px solid #DCFCE7; border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted" style="font-size: 0.75rem;">RATA-RATA PESANAN (AOV)</span>
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #DCFCE7; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">📈</div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                        Rp <?= number_format($stats['aov'] ?? 0, 0, ',', '.') ?>
                    </h5>
                    <span class="small text-muted" style="font-size: 0.72rem;">Nilai rata-rata keranjang</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="d-card-white-box p-3 h-100" style="border: 2px solid #FEF3C7; border-radius: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted" style="font-size: 0.75rem;">TOTAL PELANGGAN</span>
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #FEF3C7; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">👥</div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                        <?= $stats['total_customers'] ?? 0 ?> <span class="small fw-semibold text-muted" style="font-size: 0.8rem;">User</span>
                    </h5>
                    <span class="small text-muted" style="font-size: 0.72rem;">Customer terdaftar</span>
                </div>
            </div>
        </div>

        <!-- 2 Side-by-side panels: Produk Terlaris & Kategori Terlaris -->
        <div class="row g-3">
            <!-- Produk Terlaris -->
            <div class="col-12 col-lg-6">
                <div class="d-card-white-box p-3.5 h-100" style="border: 2px solid #F3E8FF; border-radius: 22px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span>🏆 Produk Paling Laris</span>
                        </h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Top 5</span>
                    </div>

                    <?php if (!empty($topProducts)): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($topProducts as $idx => $tp): ?>
                                <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center" style="background: #FAF8FD; border: 1px solid #F1EBFA;">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= $idx === 0 ? '#FEF08A' : ($idx === 1 ? '#E2E8F0' : '#FFEDD5') ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; color: #713F12;">
                                            #<?= $idx + 1 ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($tp['name']) ?></div>
                                            <span class="small text-muted" style="font-size: 0.72rem;">Terjual: <strong><?= $tp['total_sold'] ?> unit</strong></span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">Rp <?= number_format($tp['revenue'] ?? 0, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <span style="font-size: 2rem;">📦</span>
                            <p class="fw-bold text-dark mt-2 mb-0" style="font-size: 0.88rem;">Belum ada data penjualan</p>
                            <span class="small text-muted" style="font-size: 0.75rem;">Data akan terisi otomatis seiring pesanan selesai.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kategori Terlaris & Status Pesanan -->
            <div class="col-12 col-lg-6">
                <div class="d-card-white-box p-3.5 h-100" style="border: 2px solid #E0F2FE; border-radius: 22px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span>🏷️ Kategori Terlaris</span>
                        </h6>
                    </div>

                    <?php if (!empty($topCategories)): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($topCategories as $idx => $tc): ?>
                                <div class="p-2.5 rounded-3 d-flex justify-content-between align-items-center" style="background: #F0F9FF; border: 1px solid #E0F2FE;">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #BAE6FD; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                            🎀
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($tc['category_name']) ?></div>
                                            <span class="small text-muted" style="font-size: 0.72rem;">Terjual: <strong><?= $tc['total_sold'] ?> unit</strong></span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">Rp <?= number_format($tc['revenue'] ?? 0, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <span style="font-size: 2rem;">🏷️</span>
                            <p class="fw-bold text-dark mt-2 mb-0" style="font-size: 0.88rem;">Belum ada data kategori</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
