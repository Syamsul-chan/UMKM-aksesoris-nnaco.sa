<?php
    $role = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = $baseRoute ?? (($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin');
    $csrfToken = $csrf_token ?? Controller::generateCsrfToken();
?>

<div class="otp-config-page">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fs-2">📲</span>
                <h3 class="fw-bold mb-0 text-dark">Pengaturan WhatsApp OTP Gateway</h3>
                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Admin & Owner Only
                </span>
                <?php if (!empty($otpConfig['is_active'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 0.5rem; height: 0.5rem;" role="status"></span>
                        Layanan OTP: Aktif
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-pause-circle-fill me-1"></i> Layanan OTP: Nonaktif
                    </span>
                <?php endif; ?>

                <?php if (!empty($otpConfig['is_dev_mode'])): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-code-slash me-1"></i> Mode Simulasi Dev
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0 mt-2">
                Atur integrasi WhatsApp Business Platform Cloud API / Fonnte, masa berlaku OTP, cooldown resend, mitigasi brute-force, dan riwayat audit login OTP sistem <strong>nnaco_sa</strong>.
            </p>
        </div>

        <!-- Action Header Buttons -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold shadow-sm" onclick="openTestModal()">
                <i class="bi bi-whatsapp me-1"></i> Uji Gateway WhatsApp
            </button>
            <a href="<?= BASEURL ?>/auth/otp" target="_blank" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Login OTP
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Provider Aktif -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Provider Gateway</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                        <?= strtoupper($otpConfig['provider'] ?? 'FONNTE') ?>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #10b981, #059669); font-size: 24px;">
                        💬
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <?= ($otpConfig['provider'] === 'web_system') ? 'Sistem Web Langsung' : (($otpConfig['provider'] === 'meta_cloud') ? 'WhatsApp Cloud API' : (($otpConfig['provider'] === 'dev_mock') ? 'Simulasi Development' : 'Fonnte Gateway')) ?>
                        </h5>
                        <small class="text-muted">
                            Token: <code class="text-dark bg-white px-2 py-0.5 rounded border"><?= htmlspecialchars($otpConfig['masked_token'] ?? '••••') ?></code>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Kebijakan Keamanan OTP -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Parameter Keamanan</span>
                    <span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-1" style="background: #ede9fe; color: #6d28d9;">
                        Mitigasi Brute Force
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8b5cf6, #ec4899); font-size: 24px;">
                        🛡️
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            Berlaku: <?= (int)$otpConfig['otp_expiration_minutes'] ?> Mnt | Cooldown: <?= (int)$otpConfig['resend_cooldown_seconds'] ?>s
                        </h6>
                        <small class="text-muted">Max Salah: <?= (int)$otpConfig['max_attempts'] ?>x | Limit: <?= (int)$otpConfig['rate_limit_per_hour'] ?>x/jam</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Standar Enkripsi & Anti-Enumerasi -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Proteksi Akun</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">Zero-Plaintext</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #3b82f6, #6366f1); font-size: 24px;">
                        🔒
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">BCRYPT Hash & AES-256</h6>
                        <small class="text-muted">Kode OTP tidak disimpan plaintext & anti-account enumeration</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs: Form Pengaturan vs Riwayat Audit Log -->
    <ul class="nav nav-pills mb-4 gap-2 p-1 bg-white rounded-pill shadow-sm d-inline-flex border" id="otpTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-semibold" id="tab-settings-btn" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab">
                ⚙️ Pengaturan Gateway & Keamanan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-semibold" id="tab-audit-btn" data-bs-toggle="pill" data-bs-target="#tab-audit" type="button" role="tab">
                📜 Riwayat Audit Log OTP (<?= count($auditLogs) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="otpTabContent">
        <!-- TAB 1: FORM PENGATURAN -->
        <div class="tab-pane fade show active" id="tab-settings" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Form Konfigurasi WhatsApp Gateway</h6>
                        <small class="text-muted">Kredensial disimpan terenkripsi AES-256 dan hanya digunakan oleh backend server</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 fw-semibold" onclick="openTestModal()">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Koneksi Gateway
                    </button>
                </div>

                <div class="card-body p-4 pt-2">
                    <form id="formOtpSettings" onsubmit="handleSaveSettings(event)">
                        <input type="hidden" name="csrf_token" id="settingsCsrfToken" value="<?= $csrfToken ?>">

                        <!-- BAGIAN 1: PROVIDER & KREDENSIAL -->
                        <div class="p-3 mb-4 rounded-4 bg-light border">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-hdd-network-fill text-primary me-2"></i>1. Informasi Provider & Kredensial API
                            </h6>

                            <div class="row g-3">
                                <!-- Pilihan Provider -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-dark">Pilihan Provider WhatsApp <span class="text-danger">*</span></label>
                                    <select name="provider" id="cfgProvider" class="form-select rounded-3" onchange="toggleProviderFields()">
                                        <option value="web_system" <?= ($otpConfig['provider'] === 'web_system') ? 'selected' : '' ?>>🌐 Sistem Web Langsung (Direct Web OTP - Rekomendasi)</option>
                                        <option value="fonnte" <?= ($otpConfig['provider'] === 'fonnte') ? 'selected' : '' ?>>Fonnte Gateway (WhatsApp Gateway)</option>
                                        <option value="meta_cloud" <?= ($otpConfig['provider'] === 'meta_cloud') ? 'selected' : '' ?>>Meta WhatsApp Business Cloud API</option>
                                        <option value="dev_mock" <?= ($otpConfig['provider'] === 'dev_mock') ? 'selected' : '' ?>>Mode Simulasi Development / Mock</option>
                                    </select>
                                </div>

                                <!-- API URL -->
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small text-dark">API Endpoint URL <span class="text-danger">*</span></label>
                                    <input type="url" name="api_url" id="cfgApiUrl" class="form-control rounded-3" value="<?= htmlspecialchars($otpConfig['api_url'] ?? '') ?>" required>
                                </div>

                                <!-- API Access Token -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark d-flex justify-content-between align-items-center">
                                        <span>API Access Token / Bearer Token</span>
                                        <span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-1 small" style="background: #f3e8ff; color: #7e22ce;">
                                            <i class="bi bi-shield-lock me-1"></i> Terenkripsi AES-256
                                        </span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                            <i class="bi bi-key text-muted"></i>
                                        </span>
                                        <input type="password" name="access_token" id="cfgAccessToken" class="form-control border-start-0 border-end-0 font-monospace" placeholder="•••••••••••••••• (Biarkan kosong jika tidak diubah)">
                                        <button type="button" class="btn btn-outline-secondary border-start-0 rounded-end-3" onclick="toggleTokenVisibility()">
                                            <i class="bi bi-eye" id="tokenEyeIcon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        Token saat ini: <code class="text-purple"><?= htmlspecialchars($otpConfig['masked_token']) ?></code>. Token disimpan aman dan tidak diekspos ke frontend.
                                    </small>
                                </div>

                                <!-- Field khusus Meta Cloud API -->
                                <div id="metaCloudFields" class="row g-3 mt-1 <?= ($otpConfig['provider'] === 'meta_cloud') ? '' : 'd-none' ?>">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-dark">Phone Number ID (Meta Graph API)</label>
                                        <input type="text" name="phone_number_id" id="cfgPhoneId" class="form-control rounded-3" value="<?= htmlspecialchars($otpConfig['phone_number_id'] ?? '') ?>" placeholder="Contoh: 100609349258284">
                                        <small class="text-muted">ID Nomor Telepon dari WhatsApp App Dashboard Meta.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-dark">WABA ID (WhatsApp Business Account ID)</label>
                                        <input type="text" name="waba_id" id="cfgWabaId" class="form-control rounded-3" value="<?= htmlspecialchars($otpConfig['waba_id'] ?? '') ?>" placeholder="Contoh: 100609349258280">
                                        <small class="text-muted">ID Akun Bisnis WhatsApp Anda.</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-dark">Nama Template OTP (Meta Approved Template)</label>
                                        <input type="text" name="template_name" id="cfgTemplateName" class="form-control rounded-3" value="<?= htmlspecialchars($otpConfig['template_name'] ?? 'nnaco_auth_otp') ?>" placeholder="Contoh: nnaco_auth_otp">
                                        <small class="text-muted">Gunakan template kategori 'AUTHENTICATION' yang telah disetujui Meta, atau isi 'none' jika menggunakan pesan teks langsung.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 2: KEBIJAKAN KEAMANAN & RATE LIMITING -->
                        <div class="p-3 mb-4 rounded-4 bg-light border">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-shield-check text-success me-2"></i>2. Kebijakan Keamanan, Waktu & Rate Limiting
                            </h6>

                            <div class="row g-3">
                                <!-- Masa Berlaku OTP -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Masa Berlaku OTP <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="otp_expiration_minutes" class="form-control rounded-start-3" min="1" max="60" value="<?= (int)($otpConfig['otp_expiration_minutes'] ?? 5) ?>" required>
                                        <span class="input-group-text bg-white rounded-end-3">menit</span>
                                    </div>
                                    <small class="text-muted">Standar: 5 menit.</small>
                                </div>

                                <!-- Cooldown Kirim Ulang -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Cooldown Kirim Ulang <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="resend_cooldown_seconds" class="form-control rounded-start-3" min="10" max="300" value="<?= (int)($otpConfig['resend_cooldown_seconds'] ?? 60) ?>" required>
                                        <span class="input-group-text bg-white rounded-end-3">detik</span>
                                    </div>
                                    <small class="text-muted">Jeda tombol kirim ulang (60 detik).</small>
                                </div>

                                <!-- Batas Percobaan Salah -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Max Percobaan Salah <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="max_attempts" class="form-control rounded-start-3" min="1" max="10" value="<?= (int)($otpConfig['max_attempts'] ?? 5) ?>" required>
                                        <span class="input-group-text bg-white rounded-end-3">kali</span>
                                    </div>
                                    <small class="text-muted">Batas salah input per OTP (5x).</small>
                                </div>

                                <!-- Rate Limit per Jam -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Batas Request per Jam <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="rate_limit_per_hour" class="form-control rounded-start-3" min="1" max="30" value="<?= (int)($otpConfig['rate_limit_per_hour'] ?? 5) ?>" required>
                                        <span class="input-group-text bg-white rounded-end-3">kali</span>
                                    </div>
                                    <small class="text-muted">Anti spam per nomor.</small>
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 3: STATUS OPERASIONAL -->
                        <div class="p-3 mb-4 rounded-4 bg-light border">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-toggle-on text-warning me-2"></i>3. Status Layanan & Mode Simulasi
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0 fs-5" type="checkbox" name="is_active" id="cfgIsActive" value="1" <?= !empty($otpConfig['is_active']) ? 'checked' : '' ?>>
                                        <div>
                                            <label class="form-check-label fw-bold text-dark d-block" for="cfgIsActive">Status Layanan OTP Aktif</label>
                                            <small class="text-muted">Jika dinonaktifkan, pengguna hanya dapat masuk menggunakan email & password.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0 fs-5" type="checkbox" name="is_dev_mode" id="cfgIsDevMode" value="1" <?= !empty($otpConfig['is_dev_mode']) ? 'checked' : '' ?>>
                                        <div>
                                            <label class="form-check-label fw-bold text-dark d-block" for="cfgIsDevMode">Mode Simulasi Development (Testing Lokal)</label>
                                            <small class="text-muted">Aktifkan saat pengujian tanpa memotong kuota saldo pesan WhatsApp produksi.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm" id="btnSaveSettings" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                <i class="bi bi-check-circle-fill me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 2: AUDIT LOG RIWAYAT AUTENTIKASI -->
        <div class="tab-pane fade" id="tab-audit" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Audit Trail Autentikasi WhatsApp OTP</h6>
                        <small class="text-muted">Mencatat kejadian request OTP, verifikasi berhasil/gagal, rate limit, dan perubahan konfigurasi gateway</small>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small">
                        Pencatatan Tanpa Plaintext
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 170px;">Waktu</th>
                                <th>Target / User</th>
                                <th>Aksi</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th class="pe-4">Catatan Operasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada rekaman audit log OTP.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td class="ps-4 small text-muted font-monospace">
                                            <?= htmlspecialchars(date('d M Y H:i:s', strtotime($log['created_at']))) ?>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark font-monospace"><?= htmlspecialchars($log['phone_masked']) ?></span>
                                            <?php if (!empty($log['user_id'])): ?>
                                                <span class="text-muted small">(User #<?= (int)$log['user_id'] ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $action = $log['action'];
                                                $actionBadge = match($action) {
                                                    'VERIFY_SUCCESS' => 'badge bg-success-subtle text-success border border-success-subtle',
                                                    'VERIFY_FAIL' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
                                                    'REQUEST_OTP' => 'badge bg-primary-subtle text-primary border border-primary-subtle',
                                                    'RATE_LIMITED' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
                                                    'SETTINGS_UPDATED' => 'badge bg-purple-subtle text-purple border border-purple-subtle',
                                                    'TEST_CONNECTION' => 'badge bg-info-subtle text-info border border-info-subtle',
                                                    default => 'badge bg-secondary-subtle text-secondary'
                                                };
                                            ?>
                                            <span class="<?= $actionBadge ?> rounded-pill px-2 py-1 font-monospace" style="font-size: 11px;">
                                                <?= htmlspecialchars($action) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['status'] === 'SUCCESS'): ?>
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    <i class="bi bi-check-circle me-1"></i> SUCCESS
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    <i class="bi bi-x-circle me-1"></i> FAILED
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small font-monospace text-muted">
                                            <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                                        </td>
                                        <td class="pe-4 small text-muted" style="max-width: 340px;">
                                            <?= htmlspecialchars($log['details'] ?? '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UJI KONEKSI GATEWAY WHATSAPP -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTestGateway" tabindex="-1" aria-labelledby="modalTestGatewayTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">⚡</span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalTestGatewayTitle">Uji Gateway WhatsApp</h5>
                        <small class="text-white-50">Kirim ping/pesan uji coba untuk memverifikasi kesiapan gateway</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">Nomor WhatsApp Pengujian:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light rounded-start-3">🇮🇩 +62</span>
                        <input type="tel" id="testTargetPhone" class="form-control rounded-end-3" placeholder="Contoh: 8986817584" value="8986817584">
                    </div>
                    <small class="text-muted">Masukkan nomor Anda untuk menerima notifikasi uji koneksi.</small>
                </div>

                <div id="testGatewayLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-success mb-2" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="fw-semibold text-dark mb-0">Menghubungi Gateway WhatsApp...</p>
                    <small class="text-muted">Mengukur latensi bolak-balik koneksi API</small>
                </div>

                <div id="testGatewayResult" class="d-none"></div>
            </div>

            <div class="modal-footer border-0 px-4 py-3 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-3 py-2" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm" id="btnExecuteTest" onclick="executeGatewayTest()">
                    <i class="bi bi-play-fill me-1"></i> Mulai Uji Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TOAST NOTIFICATION CONTAINER -->
<!-- ========================================================================= -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 2000;">
    <div id="otpToast" class="toast align-items-center text-white border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span id="otpToastIcon" class="fs-5">✨</span>
                <span id="otpToastMessage" class="fw-semibold">Notifikasi</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= BASEURL ?>';
    let currentCsrf = '<?= $csrfToken ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalTestGateway');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        toggleProviderFields();
    });

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('otpToast');
        const iconEl = document.getElementById('otpToastIcon');
        const msgEl = document.getElementById('otpToastMessage');

        toastEl.className = 'toast align-items-center text-white border-0 rounded-4 shadow-lg';
        if (type === 'success') {
            toastEl.classList.add('bg-success');
            iconEl.textContent = '✅';
        } else if (type === 'danger' || type === 'error') {
            toastEl.classList.add('bg-danger');
            iconEl.textContent = '❌';
        } else if (type === 'warning') {
            toastEl.classList.add('bg-warning', 'text-dark');
            iconEl.textContent = '⚠️';
        } else {
            toastEl.classList.add('bg-primary');
            iconEl.textContent = 'ℹ️';
        }

        msgEl.textContent = message;
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    function toggleTokenVisibility() {
        const input = document.getElementById('cfgAccessToken');
        const icon = document.getElementById('tokenEyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    function toggleProviderFields() {
        const provider = document.getElementById('cfgProvider').value;
        const metaFields = document.getElementById('metaCloudFields');
        const apiUrlInput = document.getElementById('cfgApiUrl');

        if (provider === 'meta_cloud') {
            metaFields.classList.remove('d-none');
            if (apiUrlInput.value.includes('fonnte.com') || apiUrlInput.value.includes('nnaco') || !apiUrlInput.value) {
                apiUrlInput.value = 'https://graph.facebook.com/v21.0';
            }
        } else if (provider === 'web_system') {
            metaFields.classList.add('d-none');
            apiUrlInput.value = 'http://localhost/nnaco.sa/api/otp-web';
        } else if (provider === 'dev_mock') {
            metaFields.classList.add('d-none');
            apiUrlInput.value = 'http://localhost/mock-whatsapp';
        } else {
            metaFields.classList.add('d-none');
            if (apiUrlInput.value.includes('facebook.com') || apiUrlInput.value.includes('mock') || apiUrlInput.value.includes('nnaco') || !apiUrlInput.value) {
                apiUrlInput.value = 'https://api.fonnte.com/send';
            }
        }
    }

    function handleSaveSettings(e) {
        e.preventDefault();
        const form = document.getElementById('formOtpSettings');
        const formData = new FormData(form);
        formData.append('ajax', '1');
        formData.append('csrf_token', currentCsrf);

        const btn = document.getElementById('btnSaveSettings');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

        fetch(`${BASE_URL}/otpConfig/update`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Pengaturan`;

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('settingsCsrfToken').value = currentCsrf;
            }

            if (res.status === 'success') {
                showToast(res.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menyimpan pengaturan.', 'danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Pengaturan`;
            showToast('Terjadi kesalahan: ' + err.message, 'danger');
        });
    }

    function openTestModal() {
        document.getElementById('testGatewayLoading').classList.add('d-none');
        document.getElementById('testGatewayResult').classList.add('d-none');
        document.getElementById('testGatewayResult').innerHTML = '';

        const modal = new bootstrap.Modal(document.getElementById('modalTestGateway'));
        modal.show();
    }

    function executeGatewayTest() {
        const testPhone = document.getElementById('testTargetPhone').value.trim();
        const loading = document.getElementById('testGatewayLoading');
        const resultArea = document.getElementById('testGatewayResult');
        const btn = document.getElementById('btnExecuteTest');

        btn.disabled = true;
        loading.classList.remove('d-none');
        resultArea.classList.add('d-none');

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('test_phone', testPhone);
        formData.append('provider', document.getElementById('cfgProvider').value);
        formData.append('api_url', document.getElementById('cfgApiUrl').value);
        formData.append('access_token', document.getElementById('cfgAccessToken').value);
        formData.append('phone_number_id', document.getElementById('cfgPhoneId') ? document.getElementById('cfgPhoneId').value : '');
        formData.append('is_dev_mode', document.getElementById('cfgIsDevMode').checked ? 1 : 0);

        fetch(`${BASE_URL}/otpConfig/testConnection`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            loading.classList.add('d-none');
            resultArea.classList.remove('d-none');

            if (res.csrf_token) {
                currentCsrf = res.csrf_token;
                document.getElementById('settingsCsrfToken').value = currentCsrf;
            }

            if (res.status === 'success') {
                resultArea.innerHTML = `
                    <div class="alert alert-success border-0 rounded-4 p-3 mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4 text-success">✅</span>
                            <div>
                                <h6 class="fw-bold mb-0 text-success">Koneksi Gateway Berhasil!</h6>
                                <small class="text-muted">Provider: <strong>${res.provider.toUpperCase()}</strong></small>
                            </div>
                        </div>
                        <div class="row g-2 mb-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-3 border">
                                    <small class="text-muted d-block">Latensi</small>
                                    <strong class="text-success fs-6">${res.latency_ms} ms</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-white rounded-3 border">
                                    <small class="text-muted d-block">Status HTTP</small>
                                    <strong class="text-success fs-6">HTTP ${res.http_code}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 bg-white rounded-3 border small text-muted">
                            ${res.message}
                        </div>
                    </div>
                `;
                showToast(`Uji koneksi gateway WhatsApp BERHASIL (${res.latency_ms} ms)!`, 'success');
            } else {
                resultArea.innerHTML = `
                    <div class="alert alert-danger border-0 rounded-4 p-3 mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4 text-danger">❌</span>
                            <div>
                                <h6 class="fw-bold mb-0 text-danger">Uji Gateway Gagal (HTTP ${res.http_code || 0})</h6>
                                <small class="text-muted">Provider: ${res.provider}</small>
                            </div>
                        </div>
                        <div class="p-3 bg-white rounded-3 border text-danger small">
                            ${res.message}
                        </div>
                    </div>
                `;
                showToast('Uji gateway gagal. Periksa token dan konfigurasi.', 'danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            loading.classList.add('d-none');
            resultArea.classList.remove('d-none');
            resultArea.innerHTML = `
                <div class="alert alert-danger rounded-4 p-3">
                    <strong>Error:</strong> ${err.message}
                </div>
            `;
            showToast('Error koneksi: ' + err.message, 'danger');
        });
    }
</script>
