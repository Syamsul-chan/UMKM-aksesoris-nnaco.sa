<?php
    $role = strtolower($_SESSION['user']['role'] ?? 'admin');
    $baseRoute = $baseRoute ?? (($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin');
    $csrfToken = $csrf_token ?? Controller::generateCsrfToken();
?>

<style>
/* Smooth scrollbar & fix viewport height untuk Modal AI Config */
#modalConfigForm .modal-dialog-scrollable {
    max-height: calc(100vh - 40px);
}
#modalConfigForm .modal-content {
    max-height: calc(100vh - 50px);
    display: flex;
    flex-direction: column;
}
#modalConfigForm .modal-body {
    overflow-y: auto !important;
    max-height: calc(100vh - 210px);
    scrollbar-width: thin;
    scrollbar-color: #c084fc #f1f5f9;
}
#modalConfigForm .modal-body::-webkit-scrollbar {
    width: 7px;
}
#modalConfigForm .modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 8px;
}
#modalConfigForm .modal-body::-webkit-scrollbar-thumb {
    background: #c084fc;
    border-radius: 8px;
}
#modalConfigForm .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a855f7;
}
</style>

<div class="ai-config-page">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fs-2">🤖</span>
                <h3 class="fw-bold mb-0 text-dark">Konfigurasi Model AI</h3>
                <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Admin & Owner Only
                </span>
                <?php if ($primaryConfig): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 0.5rem; height: 0.5rem;" role="status"></span>
                        Model Utama Aktif: <?= htmlspecialchars($primaryConfig['name']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Ada Model Utama Aktif
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0 mt-2">
                Pusat kontrol provider AI, model LLM, parameter generasi, kredensial terenkripsi AES-256, dan pengujian koneksi realtime untuk sistem <strong>nnaco_sa</strong>.
            </p>
        </div>

        <!-- Action Header Buttons -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold shadow-sm" onclick="openQuickTestModal()">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Koneksi AI
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%); border: none;" onclick="openCreateModal()">
                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Konfigurasi
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Model Utama Aktif -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Model AI Utama</span>
                    <span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-1" style="background: #ede9fe; color: #6d28d9;">
                        Primary Engine
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #8b5cf6, #ec4899); font-size: 24px;">
                        🧠
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><?= $primaryConfig ? htmlspecialchars($primaryConfig['name']) : 'Tidak Ada' ?></h5>
                        <small class="text-muted">
                            Provider: <span class="fw-semibold text-primary"><?= $primaryConfig ? strtoupper($primaryConfig['provider']) : '-' ?></span> | 
                            <?= $primaryConfig ? htmlspecialchars($primaryConfig['model_name']) : 'N/A' ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Konfigurasi & Preset -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Total Preset Model</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Tersedia</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #10b981, #06b6d4); font-size: 24px;">
                        ⚙️
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($configs) ?> Preset</h4>
                        <small class="text-muted">
                            <?= count(array_filter($configs, fn($c) => !empty($c['is_active']))) ?> aktif siap digunakan
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Keamanan & Enkripsi Kunci -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">Standar Keamanan</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">Enkripsi Aktif</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-white" style="width: 52px; height: 52px; background: linear-gradient(135deg, #3b82f6, #6366f1); font-size: 24px;">
                        🛡️
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">AES-256-CBC</h6>
                        <small class="text-muted">Master Secret di luar database & API Key selalu dimaskir di UI</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Model Showcase Hero Banner (If Primary Config Exists) -->
    <?php if ($primaryConfig): ?>
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden" style="border-left: 6px solid #8b5cf6 !important; background: #ffffff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;">
                            ⭐ ACTIVE PRIMARY ENGINE
                        </span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Siap Melayani Chatbot & Analitik
                        </span>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">
                        <?= htmlspecialchars($primaryConfig['name']) ?> 
                        <span class="text-muted fs-6 fw-normal">(<?= htmlspecialchars($primaryConfig['model_name']) ?>)</span>
                    </h4>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-link-45deg me-1"></i> Base URL: <code class="text-dark bg-light px-2 py-1 rounded"><?= htmlspecialchars($primaryConfig['api_url']) ?></code>
                    </p>

                    <!-- Parameter Badges Grid -->
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-thermometer-half text-danger me-1"></i> Temperature: <strong><?= number_format($primaryConfig['temperature'], 2) ?></strong>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-chat-square-text text-primary me-1"></i> Max Tokens: <strong><?= (int)$primaryConfig['max_tokens'] ?></strong>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-sliders text-success me-1"></i> Top-p: <strong><?= number_format($primaryConfig['top_p'] ?? 1.0, 2) ?></strong>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-stopwatch text-warning me-1"></i> Timeout: <strong><?= (int)$primaryConfig['timeout_seconds'] ?>s</strong>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-arrow-repeat text-secondary me-1"></i> Retries: <strong><?= (int)$primaryConfig['max_retries'] ?>x</strong>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                            <i class="bi bi-key-fill text-muted me-1"></i> Key: <code class="text-muted"><?= htmlspecialchars($primaryConfig['masked_key']) ?></code>
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="p-3 rounded-4 bg-light border text-center text-lg-end">
                        <div class="small text-muted mb-1">Status Koneksi Realtime:</div>
                        <div id="heroConnectionStatus" class="mb-3">
                            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill">
                                <i class="bi bi-broadcast me-1"></i> Provider Siap
                            </span>
                        </div>
                        <div class="d-flex gap-2 justify-content-lg-end justify-content-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-semibold" onclick="testSpecificConfig(<?= $primaryConfig['id'] ?>, '<?= htmlspecialchars(addslashes($primaryConfig['name'])) ?>', 'hero')">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Tes Model Ini
                            </button>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold" onclick="editConfig(<?= $primaryConfig['id'] ?>)">
                                <i class="bi bi-pencil-square me-1"></i> Edit Parameter
                            </button>
                        </div>

                        <!-- Quick Hot-Swap Switcher Dropdown -->
                        <div class="pt-2 border-top text-start">
                            <label class="form-label small fw-bold text-muted mb-1 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-arrow-repeat text-primary me-1"></i> Ganti Cepat AI (Hot-Swap):</span>
                                <span class="badge bg-purple-subtle text-purple" style="font-size: 10px;">1-Klik Aktif</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <select class="form-select rounded-start-pill fw-semibold border-primary text-dark" id="heroQuickSwitchSelect">
                                    <?php foreach ($configs as $qCfg): ?>
                                        <?php if (!empty($qCfg['is_active'])): ?>
                                            <option value="<?= $qCfg['id'] ?>" data-name="<?= htmlspecialchars($qCfg['name']) ?>" data-model="<?= htmlspecialchars($qCfg['model_name']) ?>" <?= !empty($qCfg['is_primary']) ? 'selected' : '' ?>>
                                                <?= !empty($qCfg['is_primary']) ? '⭐ [AKTIF] ' : '🔄 ' ?><?= htmlspecialchars($qCfg['name']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary rounded-end-pill px-3 fw-bold" type="button" onclick="handleHeroQuickSwitch()" title="Terapkan Model AI Terpilih">
                                    Ganti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Nav Pills Tabs: Daftar Konfigurasi vs Riwayat Audit Log -->
    <ul class="nav nav-pills mb-4 gap-2 p-1 bg-white rounded-pill shadow-sm d-inline-flex border" id="aiConfigTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-semibold" id="tab-presets-btn" data-bs-toggle="pill" data-bs-target="#tab-presets" type="button" role="tab">
                📋 Daftar Preset Model (<?= count($configs) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-semibold" id="tab-audit-btn" data-bs-toggle="pill" data-bs-target="#tab-audit" type="button" role="tab">
                📜 Riwayat Audit Log (<?= count($auditLogs) ?>)
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="aiConfigTabContent">
        <!-- TAB 1: PRESET DAFTAR KONFIGURASI -->
        <div class="tab-pane fade show active" id="tab-presets" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Daftar Konfigurasi Model AI Tersimpan</h6>
                        <small class="text-muted">Hanya 1 model yang dapat menjadi model aktif utama di seluruh toko (Customer Chatbot & Business Insight)</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-purple-subtle text-purple border border-purple rounded-pill px-3 py-2 small fw-semibold" style="background: #f3e8ff; color: #6d28d9;">
                            <i class="bi bi-arrow-repeat me-1"></i> Klik tombol <strong>"Ganti ke AI Ini"</strong> untuk berganti engine AI secara instan (Multi-LLM Hot Swap)
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Status</th>
                                <th>Nama Konfigurasi & Model</th>
                                <th>Provider & Endpoint</th>
                                <th>API Key (Terenkripsi)</th>
                                <th>Parameter Generasi</th>
                                <th>Status Aktif</th>
                                <th class="text-end pe-4" style="min-width: 250px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($configs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2">🤖</div>
                                        <div class="fw-bold">Belum Ada Konfigurasi Model AI</div>
                                        <p class="small mb-3">Klik tombol di bawah untuk menambahkan konfigurasi model pertama Anda.</p>
                                        <button class="btn btn-primary rounded-pill px-4" onclick="openCreateModal()">Tambah Model</button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($configs as $cfg): ?>
                                    <tr class="<?= !empty($cfg['is_primary']) ? 'table-primary bg-opacity-10' : '' ?>">
                                        <!-- Status Badge -->
                                        <td class="ps-4">
                                            <?php if (!empty($cfg['is_primary'])): ?>
                                                <span class="badge rounded-pill px-2 py-1 text-white shadow-sm" style="background: linear-gradient(135deg, #8b5cf6, #ec4899); font-size: 11px;" title="Model AI Utama Aktif Saat Ini">
                                                    ⭐ UTAMA (AKTIF)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    Cadangan
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Nama Konfigurasi & Model -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <div class="fw-bold text-dark mb-0">
                                                        <?= htmlspecialchars($cfg['name']) ?>
                                                        <?php if (!empty($cfg['is_primary'])): ?>
                                                            <i class="bi bi-patch-check-fill text-primary ms-1" title="Model Utama Aktif"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <code class="text-purple fw-semibold" style="color: #7c3aed;"><?= htmlspecialchars($cfg['model_name']) ?></code>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Provider & Endpoint -->
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <?php 
                                                    $prov = strtolower($cfg['provider']);
                                                    $provBadgeBg = ($prov === 'groq') ? '#f3e8ff' : (($prov === 'openai') ? '#e0f2fe' : (($prov === 'anthropic') ? '#fef3c7' : '#f1f5f9'));
                                                    $provBadgeCol = ($prov === 'groq') ? '#7e22ce' : (($prov === 'openai') ? '#0369a1' : (($prov === 'anthropic') ? '#b45309' : '#475569'));
                                                ?>
                                                <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: <?= $provBadgeBg ?>; color: <?= $provBadgeCol ?>; font-size: 11px;">
                                                    <?= strtoupper(htmlspecialchars($cfg['provider'])) ?>
                                                </span>
                                            </div>
                                            <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($cfg['api_url']) ?>">
                                                <?= htmlspecialchars($cfg['api_url']) ?>
                                            </div>
                                        </td>

                                        <!-- Masked API Key -->
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 11px;">
                                                    <i class="bi bi-lock-fill text-muted me-1"></i>
                                                    <?= htmlspecialchars($cfg['masked_key'] ?? '••••••••') ?>
                                                </span>
                                            </div>
                                            <small class="text-muted" style="font-size: 10px;">AES-256 Terenkripsi</small>
                                        </td>

                                        <!-- Parameter Generasi -->
                                        <td>
                                            <div class="small">
                                                <span class="text-muted">Temp:</span> <strong class="text-dark"><?= number_format($cfg['temperature'], 2) ?></strong> | 
                                                <span class="text-muted">Tokens:</span> <strong class="text-dark"><?= (int)$cfg['max_tokens'] ?></strong>
                                            </div>
                                            <div class="text-muted small" style="font-size: 11px;">
                                                Top-p: <?= number_format($cfg['top_p'] ?? 1.0, 2) ?> | Time: <?= (int)$cfg['timeout_seconds'] ?>s
                                            </div>
                                        </td>

                                        <!-- Status Aktif / Switch -->
                                        <td>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggle_<?= $cfg['id'] ?>" 
                                                    <?= !empty($cfg['is_active']) ? 'checked' : '' ?> 
                                                    onchange="toggleActive(<?= $cfg['id'] ?>, this.checked, '<?= htmlspecialchars(addslashes($cfg['name'])) ?>')">
                                                <label class="form-check-label small <?= !empty($cfg['is_active']) ? 'text-success fw-semibold' : 'text-muted' ?>" for="toggle_<?= $cfg['id'] ?>" id="lbl_toggle_<?= $cfg['id'] ?>">
                                                    <?= !empty($cfg['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                                </label>
                                            </div>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="text-end pe-4">
                                            <div class="btn-group gap-1" role="group">
                                                <!-- Tombol Test Connection -->
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1" 
                                                    title="Uji Koneksi Provider" 
                                                    onclick="testSpecificConfig(<?= $cfg['id'] ?>, '<?= htmlspecialchars(addslashes($cfg['name'])) ?>', 'table')">
                                                    <i class="bi bi-lightning-charge-fill text-warning"></i> Tes
                                                </button>

                                                <!-- Tombol Jadikan Utama / Status Aktif -->
                                                <?php if (empty($cfg['is_primary'])): ?>
                                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-sm" 
                                                        style="background: linear-gradient(135deg, #7c3aed, #4f46e5); border: none;"
                                                        title="Beralih dan Aktifkan Model AI Ini" 
                                                        onclick="confirmSetPrimary(<?= $cfg['id'] ?>, '<?= htmlspecialchars(addslashes($cfg['name'])) ?>', '<?= htmlspecialchars(addslashes($cfg['model_name'])) ?>')">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Ganti ke AI Ini
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold d-inline-flex align-items-center" title="Model ini sedang aktif melayani sistem">
                                                        <i class="bi bi-check2-circle me-1"></i> Sedang Dipakai
                                                    </span>
                                                <?php endif; ?>

                                                <!-- Tombol Edit -->
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" 
                                                    title="Edit Konfigurasi" 
                                                    onclick="editConfig(<?= $cfg['id'] ?>)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- Tombol Hapus (Disabled jika Primary) -->
                                                <?php if (empty($cfg['is_primary'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                        title="Hapus Konfigurasi" 
                                                        onclick="confirmDelete(<?= $cfg['id'] ?>, '<?= htmlspecialchars(addslashes($cfg['name'])) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-light border text-muted rounded-pill px-2 py-1" 
                                                        disabled title="Model utama tidak dapat dihapus. Ganti model utama terlebih dahulu.">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: AUDIT LOG RIWAYAT KONFIGURASI -->
        <div class="tab-pane fade" id="tab-audit" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Audit Trail & Riwayat Operasi AI</h6>
                        <small class="text-muted">Mencatat seluruh aksi penambahan, perubahan parameter, pergantian model aktif, dan pengujian koneksi tanpa mencatat kunci rahasia</small>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small">
                        Keamanan Sistem Audit
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 170px;">Waktu</th>
                                <th>User / Operator</th>
                                <th>Aksi</th>
                                <th>Target Konfigurasi</th>
                                <th>Status</th>
                                <th class="pe-4">Catatan Operasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada rekaman audit log.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td class="ps-4 small text-muted font-monospace">
                                            <?= htmlspecialchars(date('d M Y H:i:s', strtotime($log['created_at']))) ?>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></span>
                                            <span class="text-muted small">(ID: #<?= (int)($log['user_id'] ?? 0) ?>)</span>
                                        </td>
                                        <td>
                                            <?php 
                                                $action = $log['action'];
                                                $actionColor = match($action) {
                                                    'SET_PRIMARY' => 'purple',
                                                    'CREATE' => 'success',
                                                    'UPDATE' => 'info',
                                                    'DELETE' => 'danger',
                                                    'TEST_CONNECTION' => 'warning',
                                                    default => 'secondary'
                                                };
                                            ?>
                                            <span class="badge bg-<?= $actionColor ?>-subtle text-<?= $actionColor ?> border border-<?= $actionColor ?>-subtle rounded-pill px-2 py-1 font-monospace" style="font-size: 11px;">
                                                <?= htmlspecialchars($action) ?>
                                            </span>
                                        </td>
                                        <td class="small fw-semibold text-dark">
                                            <?= htmlspecialchars($log['config_name'] ?? ('Config #' . $log['config_id'])) ?>
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
                                        <td class="pe-4 small text-muted" style="max-width: 320px;">
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
<!-- MODAL 1: FORM TAMBAH / EDIT KONFIGURASI AI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalConfigForm" tabindex="-1" aria-labelledby="modalConfigFormTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-height: calc(100vh - 40px); margin-top: 20px; margin-bottom: 20px;">
        <form id="formAiConfig" onsubmit="submitConfigForm(event)" class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="max-height: calc(100vh - 50px); display: flex; flex-direction: column;">
            <!-- Modal Header -->
            <div class="modal-header border-0 py-3 px-4 text-white flex-shrink-0" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">⚙️</span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalConfigFormTitle">Tambah Konfigurasi Model AI</h5>
                        <small class="text-white-50">Atur provider, nama model, kunci rahasia, parameter dan prompt sistem</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <input type="hidden" name="csrf_token" id="formCsrfToken" value="<?= $csrfToken ?>">
            <input type="hidden" name="id" id="configId" value="">

            <div class="modal-body p-4" style="overflow-y: auto; flex: 1 1 auto; max-height: calc(100vh - 210px);">
                    <!-- Nav Tabs inside Modal for grouping -->
                    <ul class="nav nav-pills mb-3 gap-1 p-1 bg-light rounded-pill border" id="modalFormTabs" role="tablist">
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link active rounded-pill py-2 fw-semibold w-100" id="tab-form-cred-btn" data-bs-toggle="pill" data-bs-target="#tab-form-cred" type="button" role="tab">
                                🔑 Provider & Kunci
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link rounded-pill py-2 fw-semibold w-100" id="tab-form-params-btn" data-bs-toggle="pill" data-bs-target="#tab-form-params" type="button" role="tab">
                                🎛️ Parameter Model
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link rounded-pill py-2 fw-semibold w-100" id="tab-form-prompts-btn" data-bs-toggle="pill" data-bs-target="#tab-form-prompts" type="button" role="tab">
                                💬 System Prompts
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2" id="modalFormTabContent">
                        <!-- SECTION 1: PROVIDER & CREDENTIALS -->
                        <div class="tab-pane fade show active" id="tab-form-cred" role="tabpanel">
                            <!-- Preset Auto-Fill Quick Buttons -->
                            <div class="p-3 mb-3 rounded-4 bg-light border">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-2">⚡ Template Cepat Provider:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 shadow-sm" onclick="applyPreset('groq_gpt20b')">
                                        ⚡ Groq (GPT-OSS 20B)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 shadow-sm" onclick="applyPreset('groq_gpt120b')">
                                        ⚡ Groq (GPT-OSS 120B)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 shadow-sm" onclick="applyPreset('openai_4o_mini')">
                                        🟢 OpenAI (GPT-4o Mini)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1 shadow-sm" onclick="applyPreset('anthropic_claude')">
                                        🟣 Anthropic (Claude 3.5 Sonnet)
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Nama Konfigurasi -->
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold small text-dark">Nama Konfigurasi / Label <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="cfgName" class="form-control rounded-3" placeholder="Contoh: Groq - GPT-OSS 20B (Produksi)" required>
                                    <small class="text-muted">Nama penanda untuk mengenali profil konfigurasi ini.</small>
                                </div>

                                <!-- Pilihan Provider -->
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small text-dark">Provider AI <span class="text-danger">*</span></label>
                                    <select name="provider" id="cfgProvider" class="form-select rounded-3" onchange="onProviderChange()" required>
                                        <option value="groq">Groq (Ultra-Fast)</option>
                                        <option value="openai">OpenAI</option>
                                        <option value="anthropic">Anthropic (Claude)</option>
                                        <option value="custom">Custom (OpenAI-Compatible)</option>
                                    </select>
                                </div>

                                <!-- Nama Model -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Nama Model AI <span class="text-danger">*</span></label>
                                    <input type="text" name="model_name" id="cfgModelName" class="form-control rounded-3" list="modelSuggestions" placeholder="Contoh: openai/gpt-oss-20b" required>
                                    <datalist id="modelSuggestions">
                                        <option value="openai/gpt-oss-20b">Groq Active: openai/gpt-oss-20b</option>
                                        <option value="openai/gpt-oss-120b">Groq Active: openai/gpt-oss-120b</option>
                                        <option value="gpt-4o-mini">OpenAI: gpt-4o-mini</option>
                                        <option value="gpt-4o">OpenAI: gpt-4o</option>
                                        <option value="claude-3-5-sonnet-20241022">Anthropic: claude-3-5-sonnet-20241022</option>
                                    </datalist>
                                    <small class="text-muted">Identifier model yang valid pada provider terpilih.</small>
                                </div>

                                <!-- Base URL API -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Base URL Endpoint API <span class="text-danger">*</span></label>
                                    <input type="url" name="api_url" id="cfgApiUrl" class="form-control rounded-3" placeholder="https://api.groq.com/openai/v1/chat/completions" required>
                                    <small class="text-muted">URL endpoint API chat completion provider.</small>
                                </div>

                                <!-- API Key dengan Enkripsi & Visibility Toggle -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark d-flex justify-content-between align-items-center">
                                        <span>API Secret Key <span class="text-danger" id="apiKeyRequiredStar">*</span></span>
                                        <span class="badge bg-purple-subtle text-purple rounded-pill px-2 py-1 small" style="background: #f3e8ff; color: #7e22ce;">
                                            <i class="bi bi-shield-lock me-1"></i> AES-256 Enkripsi Otomatis
                                        </span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-3">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" name="api_key" id="cfgApiKey" class="form-control border-start-0 border-end-0 font-monospace" placeholder="Masukkan API Key (contoh: gsk_... atau sk-...)">
                                        <button type="button" class="btn btn-outline-secondary border-start-0 rounded-end-3" onclick="toggleKeyVisibility('cfgApiKey', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" id="apiKeyHelpText">
                                        Kunci rahasia API disimpan secara aman di database menggunakan algoritma enkripsi AES-256-CBC. Kunci tidak pernah diekspos secara utuh ke frontend.
                                    </small>
                                </div>

                                <!-- Status Aktif & Tetapkan sebagai Utama -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="cfgIsActive" value="1" checked>
                                            <label class="form-check-label fw-semibold text-dark" for="cfgIsActive">Status Aktif</label>
                                        </div>
                                        <small class="text-muted d-block">Konfigurasi yang dinonaktifkan tidak dapat digunakan sebagai model aktif toko.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" name="is_primary" id="cfgIsPrimary" value="1">
                                            <label class="form-check-label fw-semibold text-dark" for="cfgIsPrimary">⭐ Tetapkan sebagai Model AI Utama</label>
                                        </div>
                                        <small class="text-muted d-block">Akan otomatis menggantikan model aktif utama sebelumnya di seluruh toko.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: MODEL PARAMETERS -->
                        <div class="tab-pane fade" id="tab-form-params" role="tabpanel">
                            <div class="row g-3">
                                <!-- Temperature -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold small text-dark mb-0">
                                                🌡️ Temperature: <span class="badge bg-primary text-white rounded-pill px-2 py-1" id="tempBadge">0.70</span>
                                            </label>
                                            <small class="text-muted">Kreativitas Jawaban</small>
                                        </div>
                                        <input type="range" class="form-range" min="0" max="2" step="0.05" id="cfgTempRange" value="0.70" oninput="syncTemp(this.value)">
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span>0.0 (Presisi/Faktual)</span>
                                            <span>1.0 (Seimbang)</span>
                                            <span>2.0 (Kreatif)</span>
                                        </div>
                                        <input type="hidden" name="temperature" id="cfgTemperature" value="0.70">
                                    </div>
                                </div>

                                <!-- Top-p -->
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold small text-dark mb-0">
                                                🎯 Top-P: <span class="badge bg-secondary text-white rounded-pill px-2 py-1" id="toppBadge">0.90</span>
                                            </label>
                                            <small class="text-muted">Nucleus Sampling</small>
                                        </div>
                                        <input type="range" class="form-range" min="0.05" max="1" step="0.05" id="cfgToppRange" value="0.90" oninput="syncTopp(this.value)">
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span>0.1 (Fokus)</span>
                                            <span>0.9 (Rekomendasi)</span>
                                            <span>1.0 (Penuh)</span>
                                        </div>
                                        <input type="hidden" name="top_p" id="cfgTopP" value="0.90">
                                    </div>
                                </div>

                                <!-- Maximum Output Tokens -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Maximum Output Tokens <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="max_tokens" id="cfgMaxTokens" class="form-control rounded-start-3" min="64" max="8192" step="64" value="1024" required>
                                        <span class="input-group-text bg-light rounded-end-3">tokens</span>
                                    </div>
                                    <small class="text-muted">Batas panjang maksimal token balasan teks dari model AI (1024 rekomendasi).</small>
                                </div>

                                <!-- Request Timeout -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Timeout <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="timeout_seconds" id="cfgTimeout" class="form-control rounded-start-3" min="5" max="120" value="20" required>
                                        <span class="input-group-text bg-light rounded-end-3">detik</span>
                                    </div>
                                    <small class="text-muted">Batas waktu koneksi.</small>
                                </div>

                                <!-- Max Retries -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Max Retries</label>
                                    <div class="input-group">
                                        <input type="number" name="max_retries" id="cfgMaxRetries" class="form-control rounded-start-3" min="0" max="5" value="2">
                                        <span class="input-group-text bg-light rounded-end-3">kali</span>
                                    </div>
                                    <small class="text-muted">Percobaan ulang jika gagal.</small>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: SYSTEM PROMPTS -->
                        <div class="tab-pane fade" id="tab-form-prompts" role="tabpanel">
                            <!-- Prompt 1: AI Customer Assistant -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold small text-dark mb-0">
                                        🛍️ System Prompt: AI Customer Assistant
                                    </label>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0" onclick="resetCustomerPrompt()">
                                        <i class="bi bi-arrow-counterclockwise"></i> Kembalikan Default
                                    </button>
                                </div>
                                <small class="text-muted d-block mb-2">Instruksi kepribadian dan batasan AI saat melayani pelanggan di obrolan website.</small>
                                <textarea name="system_prompt_customer" id="cfgPromptCustomer" class="form-control font-monospace small" rows="5" placeholder="Kosongkan untuk menggunakan prompt bawaan sistem..."></textarea>
                            </div>

                            <!-- Prompt 2: AI Business Insight -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold small text-dark mb-0">
                                        📈 System Prompt: AI Business Insight (Owner Analytics)
                                    </label>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary p-0" onclick="resetInsightPrompt()">
                                        <i class="bi bi-arrow-counterclockwise"></i> Kembalikan Default
                                    </button>
                                </div>
                                <small class="text-muted d-block mb-2">Instruksi untuk AI saat menganalisis tren omzet, produk terlaris, dan insight bisnis bagi Owner.</small>
                                <textarea name="system_prompt_insight" id="cfgPromptInsight" class="form-control font-monospace small" rows="5" placeholder="Kosongkan untuk menggunakan prompt bawaan sistem..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Live Test Feedback Alert Area inside Modal -->
                    <div id="modalTestResultAlert" class="mt-3 d-none"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0 px-4 py-3 bg-light d-flex justify-content-between align-items-center flex-shrink-0">
                    <button type="button" class="btn btn-outline-warning text-dark rounded-pill px-3 py-2 fw-semibold" id="btnTestInsideModal" onclick="testConnectionFromModal()">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Test Connection
                    </button>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3 py-2 fw-semibold" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" id="btnSaveConfig" style="background: linear-gradient(135deg, #a78bfa 0%, #ec4899 100%); border: none;">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: QUICK TEST CONNECTION RUNNER -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalQuickTest" tabindex="-1" aria-labelledby="modalQuickTestTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">⚡</span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalQuickTestTitle">Uji Koneksi Provider AI</h5>
                        <small class="text-white-50">Mengirim ping real-time untuk memvalidasi respon & latensi model</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">Pilih Konfigurasi yang Akan Diuji:</label>
                    <select id="quickTestSelectConfig" class="form-select rounded-3">
                        <?php foreach($configs as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= !empty($c['is_primary']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['model_name']) ?>) <?= !empty($c['is_primary']) ? '[⭐ UTAMA]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Test Result Container -->
                <div id="quickTestLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-warning mb-2" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="fw-semibold text-dark mb-0">Menghubungi endpoint provider AI...</p>
                    <small class="text-muted">Mengukur latensi bolak-balik (round-trip latency)</small>
                </div>

                <div id="quickTestResult" class="d-none"></div>
            </div>

            <div class="modal-footer border-0 px-4 py-3 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-3 py-2" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2 shadow-sm" id="btnRunQuickTest" onclick="runQuickTestFromSelect()">
                    <i class="bi bi-play-fill me-1"></i> Mulai Uji Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: KONFIRMASI GANTI MODEL AI UTAMA -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalConfirmPrimary" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-star-fill text-warning me-2"></i> Konfirmasi Ganti Model Utama
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="fs-1 mb-2">⭐</div>
                <h5 class="fw-bold text-dark mb-2" id="confirmPrimaryTitle">Jadikan Model Utama?</h5>
                <p class="text-muted small mb-3" id="confirmPrimaryDesc">
                    Anda akan mengalihkan Model AI Utama toko ke konfigurasi ini. Seluruh percakapan Chatbot Customer dan Analitik Insight Owner akan langsung menggunakan model baru ini.
                </p>
                <div class="alert alert-warning border-0 rounded-3 small text-start mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Penting:</strong> Sistem memastikan hanya 1 model utama yang aktif pada satu waktu secara konsisten.
                </div>
            </div>
            <div class="modal-footer border-0 px-4 py-3 bg-light justify-content-center gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 py-2" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" id="btnConfirmPrimarySubmit" onclick="executeSetPrimary()">
                    Ya, Jadikan Model Utama
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: KONFIRMASI HAPUS KONFIGURASI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-trash-fill me-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="fs-1 text-danger mb-2">🗑️</div>
                <h5 class="fw-bold text-dark mb-2" id="confirmDeleteTitle">Hapus Konfigurasi Ini?</h5>
                <p class="text-muted small mb-0" id="confirmDeleteDesc">
                    Konfigurasi ini akan dihapus dari daftar preset. Aksi ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="modal-footer border-0 px-4 py-3 bg-light justify-content-center gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 py-2" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-semibold" id="btnConfirmDeleteSubmit" onclick="executeDelete()">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TOAST NOTIFICATION CONTAINER -->
<!-- ========================================================================= -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 2000;">
    <div id="aiToast" class="toast align-items-center text-white border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span id="aiToastIcon" class="fs-5">✨</span>
                <span id="aiToastMessage" class="fw-semibold">Notifikasi</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CLIENT-SIDE CONTROLLER JAVASCRIPT -->
<!-- ========================================================================= -->
<script>
    // Konstanta Routing
    const BASE_URL = '<?= BASEURL ?>';
    const BASE_ROUTE = '<?= $baseRoute ?>';
    let currentCsrf = '<?= $csrfToken ?>';

    // State Variabel Modal
    let targetPrimaryId = null;
    let targetDeleteId = null;

    // Default System Prompts
    const DEFAULT_PROMPT_CUSTOMER = `Anda adalah Customer Assistant AI resmi, ramah, dan profesional untuk toko online aksesoris 'nnaco_sa'.
Anda bertugas membantu pelanggan seputar belanja perhiasan & aksesoris, rekomendasi produk, cek stok, harga, promo, dan pengiriman.

KEBIJAKAN KEAMANAN & PRIVASI DATA:
1. DILARANG KERAS membeberkan omzet internal toko atau data akun pengguna lain kepada customer!
2. Jika ditanya data user atau pemilik, tolak dengan ramah demi menjaga privasi dan keamanan.
3. Sapa pengguna dengan hangat, gunakan Bahasa Indonesia yang ramah dan solutif.`;

    const DEFAULT_PROMPT_INSIGHT = `Anda adalah Business Analyst AI untuk toko aksesoris 'nnaco_sa'.
Tugas Anda adalah menganalisis data ringkasan bisnis dan memberikan Insight Bisnis yang sangat ringkas, padat, dan profesional.
Insight harus berisi:
- Kinerja penjualan (Total Revenue & Orders)
- Tren produk terlaris
- Peringatan stok menipis
- Rekomendasi bisnis singkat yang masuk akal`;

    // Presets Template Data
    const PRESETS = {
        'groq_gpt20b': {
            name: 'Groq - GPT-OSS 20B (Rekomendasi Cepat)',
            provider: 'groq',
            model_name: 'openai/gpt-oss-20b',
            api_url: 'https://api.groq.com/openai/v1/chat/completions',
            temp: 0.70,
            tokens: 1024,
            topp: 0.90,
            timeout: 20
        },
        'groq_gpt120b': {
            name: 'Groq - GPT-OSS 120B (High Intelligence)',
            provider: 'groq',
            model_name: 'openai/gpt-oss-120b',
            api_url: 'https://api.groq.com/openai/v1/chat/completions',
            temp: 0.70,
            tokens: 2048,
            topp: 0.90,
            timeout: 30
        },
        'openai_4o_mini': {
            name: 'OpenAI - GPT-4o Mini (Hemat & Cepat)',
            provider: 'openai',
            model_name: 'gpt-4o-mini',
            api_url: 'https://api.openai.com/v1/chat/completions',
            temp: 0.60,
            tokens: 1024,
            topp: 0.90,
            timeout: 25
        },
        'anthropic_claude': {
            name: 'Anthropic - Claude 3.5 Sonnet',
            provider: 'anthropic',
            model_name: 'claude-3-5-sonnet-20241022',
            api_url: 'https://api.anthropic.com/v1/messages',
            temp: 0.50,
            tokens: 1024,
            topp: 0.90,
            timeout: 30
        }
    };

    // Teleport Modals ke document.body agar tidak terjebak stacking context d-shell
    document.addEventListener('DOMContentLoaded', function() {
        const modals = ['modalConfigForm', 'modalQuickTest', 'modalConfirmPrimary', 'modalConfirmDelete'];
        modals.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });
    });

    // Helper Toast Notification
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('aiToast');
        const iconEl = document.getElementById('aiToastIcon');
        const msgEl = document.getElementById('aiToastMessage');

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

    // Toggle Eye Kunci API
    function toggleKeyVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Sinkronisasi Slider Range dengan Number Badges
    function syncTemp(val) {
        document.getElementById('tempBadge').textContent = parseFloat(val).toFixed(2);
        document.getElementById('cfgTemperature').value = val;
    }

    function syncTopp(val) {
        document.getElementById('toppBadge').textContent = parseFloat(val).toFixed(2);
        document.getElementById('cfgTopP').value = val;
    }

    // Terapkan Template Cepat Preset
    function applyPreset(key) {
        const p = PRESETS[key];
        if (!p) return;

        document.getElementById('cfgName').value = p.name;
        document.getElementById('cfgProvider').value = p.provider;
        document.getElementById('cfgModelName').value = p.model_name;
        document.getElementById('cfgApiUrl').value = p.api_url;
        
        syncTemp(p.temp);
        document.getElementById('cfgTempRange').value = p.temp;
        
        syncTopp(p.topp);
        document.getElementById('cfgToppRange').value = p.topp;
        
        document.getElementById('cfgMaxTokens').value = p.tokens;
        document.getElementById('cfgTimeout').value = p.timeout;

        onProviderChange();
        showToast('Template ' + p.name + ' diterapkan!', 'info');
    }

    // Deteksi Pergantian Provider untuk Auto-fill Endpoint Default
    function onProviderChange() {
        const provider = document.getElementById('cfgProvider').value;
        const urlInput = document.getElementById('cfgApiUrl');

        if (provider === 'groq' && (!urlInput.value || urlInput.value.includes('openai.com') || urlInput.value.includes('anthropic.com'))) {
            urlInput.value = 'https://api.groq.com/openai/v1/chat/completions';
        } else if (provider === 'openai' && (!urlInput.value || urlInput.value.includes('groq.com') || urlInput.value.includes('anthropic.com'))) {
            urlInput.value = 'https://api.openai.com/v1/chat/completions';
        } else if (provider === 'anthropic' && (!urlInput.value || urlInput.value.includes('groq.com') || urlInput.value.includes('openai.com'))) {
            urlInput.value = 'https://api.anthropic.com/v1/messages';
        }
    }

    // Auto-detect Provider & Base URL saat user mengetik atau menempelkan API Key
    document.addEventListener('DOMContentLoaded', function() {
        const keyInput = document.getElementById('cfgApiKey');
        if (keyInput) {
            keyInput.addEventListener('input', function() {
                const val = this.value.trim();
                const provSelect = document.getElementById('cfgProvider');
                const urlInput = document.getElementById('cfgApiUrl');
                const modelInput = document.getElementById('cfgModelName');
                const nameInput = document.getElementById('cfgName');

                if (val.startsWith('sk-proj-') || (val.startsWith('sk-') && !val.startsWith('sk-ant-') && val.length > 20)) {
                    // OpenAI Key terdeteksi!
                    if (provSelect.value !== 'openai' || urlInput.value.includes('anthropic.com') || urlInput.value.includes('groq.com')) {
                        provSelect.value = 'openai';
                        urlInput.value = 'https://api.openai.com/v1/chat/completions';
                        if (!modelInput.value || modelInput.value.includes('claude') || modelInput.value.includes('groq') || modelInput.value.includes('oss')) {
                            modelInput.value = 'gpt-4o-mini';
                        }
                        if (!nameInput.value || nameInput.value.toLowerCase().includes('anthropic') || nameInput.value.toLowerCase().includes('claude')) {
                            nameInput.value = 'OpenAI - GPT-4o Mini';
                        }
                        showToast('🔑 Terdeteksi API Key OpenAI. Provider & Endpoint otomatis disesuaikan!', 'info');
                    }
                } else if (val.startsWith('gsk_')) {
                    // Groq Key terdeteksi!
                    if (provSelect.value !== 'groq' || urlInput.value.includes('anthropic.com') || urlInput.value.includes('openai.com')) {
                        provSelect.value = 'groq';
                        urlInput.value = 'https://api.groq.com/openai/v1/chat/completions';
                        if (!modelInput.value || modelInput.value.startsWith('gpt-') || modelInput.value.includes('claude')) {
                            modelInput.value = 'openai/gpt-oss-120b';
                        }
                        showToast('⚡ Terdeteksi API Key Groq. Provider & Endpoint otomatis disesuaikan!', 'info');
                    }
                } else if (val.startsWith('sk-ant-')) {
                    // Anthropic Key terdeteksi!
                    if (provSelect.value !== 'anthropic' || urlInput.value.includes('openai.com') || urlInput.value.includes('groq.com')) {
                        provSelect.value = 'anthropic';
                        urlInput.value = 'https://api.anthropic.com/v1/messages';
                        if (!modelInput.value || !modelInput.value.includes('claude')) {
                            modelInput.value = 'claude-3-5-sonnet-20241022';
                        }
                        showToast('🟣 Terdeteksi API Key Anthropic Claude. Provider & Endpoint otomatis disesuaikan!', 'info');
                    }
                }
            });
        }
    });

    function resetCustomerPrompt() {
        document.getElementById('cfgPromptCustomer').value = DEFAULT_PROMPT_CUSTOMER;
    }

    function resetInsightPrompt() {
        document.getElementById('cfgPromptInsight').value = DEFAULT_PROMPT_INSIGHT;
    }

    // Buka Modal Tambah Konfigurasi
    function openCreateModal() {
        document.getElementById('formAiConfig').reset();
        document.getElementById('configId').value = '';
        document.getElementById('modalConfigFormTitle').textContent = 'Tambah Konfigurasi Model AI Baru';
        document.getElementById('apiKeyRequiredStar').style.display = 'inline';
        document.getElementById('cfgApiKey').required = true;
        document.getElementById('cfgApiKey').placeholder = 'Masukkan API Key rahasia (contoh: gsk_... atau sk-...)';
        document.getElementById('apiKeyHelpText').innerHTML = 'Kunci rahasia API akan otomatis dienkripsi dengan standar AES-256 sebelum disimpan ke database.';
        
        // Reset slider ranges
        syncTemp(0.70);
        document.getElementById('cfgTempRange').value = 0.70;
        syncTopp(0.90);
        document.getElementById('cfgToppRange').value = 0.90;
        document.getElementById('cfgMaxTokens').value = 1024;
        document.getElementById('cfgTimeout').value = 20;
        document.getElementById('cfgMaxRetries').value = 2;
        document.getElementById('cfgIsActive').checked = true;
        document.getElementById('cfgIsPrimary').checked = false;

        document.getElementById('modalTestResultAlert').className = 'mt-3 d-none';
        document.getElementById('modalTestResultAlert').innerHTML = '';

        // Tab default
        const firstTab = new bootstrap.Tab(document.getElementById('tab-form-cred-btn'));
        firstTab.show();

        const modal = new bootstrap.Modal(document.getElementById('modalConfigForm'));
        modal.show();
    }

    // Buka Modal Edit Konfigurasi
    function editConfig(id) {
        fetch(`${BASE_URL}/aiConfig/getJson/${id}?ajax=1`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const c = res.data;
                    document.getElementById('configId').value = c.id;
                    document.getElementById('modalConfigFormTitle').textContent = `Edit Konfigurasi: ${c.name}`;
                    document.getElementById('cfgName').value = c.name;
                    document.getElementById('cfgProvider').value = c.provider;
                    document.getElementById('cfgModelName').value = c.model_name;
                    document.getElementById('cfgApiUrl').value = c.api_url;

                    // API Key tidak wajib saat edit
                    document.getElementById('apiKeyRequiredStar').style.display = 'none';
                    document.getElementById('cfgApiKey').required = false;
                    document.getElementById('cfgApiKey').value = '';
                    document.getElementById('cfgApiKey').placeholder = '•••••••••••••••• (Biarkan kosong jika tidak diubah)';
                    document.getElementById('apiKeyHelpText').innerHTML = 'Kunci API saat ini: <code class="text-purple">' + (c.masked_key || 'Terenkripsi') + '</code>. Masukkan kunci baru hanya jika Anda ingin menggantinya.';

                    syncTemp(c.temperature || 0.70);
                    document.getElementById('cfgTempRange').value = c.temperature || 0.70;
                    
                    syncTopp(c.top_p || 0.90);
                    document.getElementById('cfgToppRange').value = c.top_p || 0.90;

                    document.getElementById('cfgMaxTokens').value = c.max_tokens || 1024;
                    document.getElementById('cfgTimeout').value = c.timeout_seconds || 20;
                    document.getElementById('cfgMaxRetries').value = c.max_retries || 2;
                    document.getElementById('cfgIsActive').checked = (parseInt(c.is_active) === 1);
                    document.getElementById('cfgIsPrimary').checked = (parseInt(c.is_primary) === 1);

                    document.getElementById('cfgPromptCustomer').value = c.system_prompt_customer || '';
                    document.getElementById('cfgPromptInsight').value = c.system_prompt_insight || '';

                    document.getElementById('modalTestResultAlert').className = 'mt-3 d-none';
                    document.getElementById('modalTestResultAlert').innerHTML = '';

                    const firstTab = new bootstrap.Tab(document.getElementById('tab-form-cred-btn'));
                    firstTab.show();

                    const modal = new bootstrap.Modal(document.getElementById('modalConfigForm'));
                    modal.show();
                } else {
                    showToast(res.message || 'Gagal memuat konfigurasi', 'danger');
                }
            })
            .catch(err => {
                showToast('Error koneksi: ' + err.message, 'danger');
            });
    }

    // Submit Simpan Konfigurasi (Tambah / Edit)
    function submitConfigForm(e) {
        e.preventDefault();
        const form = document.getElementById('formAiConfig');
        const formData = new FormData(form);
        formData.append('ajax', '1');
        formData.append('csrf_token', currentCsrf);

        const configId = document.getElementById('configId').value;
        const targetUrl = configId ? `${BASE_URL}/aiConfig/update/${configId}` : `${BASE_URL}/aiConfig/store`;
        const btnSave = document.getElementById('btnSaveConfig');

        btnSave.disabled = true;
        btnSave.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

        fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Konfigurasi`;

            if (res.csrf_token) currentCsrf = res.csrf_token;

            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfigForm'));
                if (modal) modal.hide();
                showToast(res.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(res.message || 'Gagal menyimpan konfigurasi.', 'danger');
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Konfigurasi`;
            showToast('Terjadi kesalahan: ' + err.message, 'danger');
        });
    }

    // Test Connection Langsung dari Form Modal
    function testConnectionFromModal() {
        const btn = document.getElementById('btnTestInsideModal');
        const alertArea = document.getElementById('modalTestResultAlert');

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('config_id', document.getElementById('configId').value);
        formData.append('provider', document.getElementById('cfgProvider').value);
        formData.append('api_url', document.getElementById('cfgApiUrl').value);
        formData.append('api_key', document.getElementById('cfgApiKey').value);
        formData.append('model_name', document.getElementById('cfgModelName').value);
        formData.append('temperature', document.getElementById('cfgTemperature').value);
        formData.append('timeout_seconds', document.getElementById('cfgTimeout').value);

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menguji...`;

        alertArea.className = 'mt-3 alert alert-info rounded-3 py-2 small d-flex align-items-center gap-2';
        alertArea.innerHTML = `<span class="spinner-grow spinner-grow-sm text-info"></span> Mengirim permintaan ping ke provider...`;
        alertArea.classList.remove('d-none');

        fetch(`${BASE_URL}/aiConfig/testConnection`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-lightning-charge-fill text-warning me-1"></i> Test Connection`;

            if (res.csrf_token) currentCsrf = res.csrf_token;

            if (res.status === 'success') {
                alertArea.className = 'mt-3 alert alert-success rounded-3 py-2 small';
                alertArea.innerHTML = `
                    <div class="fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Koneksi Berhasil! (HTTP ${res.http_code})</div>
                    <div>Latensi: <strong>${res.latency_ms} ms</strong> | Provider: <strong>${res.provider}</strong> | Model: <strong>${res.model}</strong></div>
                    <div class="mt-1 text-muted">Respon AI: <em>"${res.reply || 'OK'}"</em></div>
                `;
                showToast(`Koneksi berhasil! Latensi: ${res.latency_ms} ms`, 'success');
            } else {
                alertArea.className = 'mt-3 alert alert-danger rounded-3 py-2 small';
                alertArea.innerHTML = `
                    <div class="fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Uji Koneksi Gagal (HTTP ${res.http_code || 0})</div>
                    <div>Pesan Error: ${res.error_message || 'Tidak dapat terhubung ke provider.'}</div>
                `;
                showToast('Uji koneksi gagal. Periksa endpoint dan kredensial.', 'danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-lightning-charge-fill text-warning me-1"></i> Test Connection`;
            alertArea.className = 'mt-3 alert alert-danger rounded-3 py-2 small';
            alertArea.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Error: ${err.message}`;
            showToast('Gagal menghubungi server.', 'danger');
        });
    }

    // Modal Uji Koneksi Cepat (Quick Test Modal)
    function openQuickTestModal() {
        document.getElementById('quickTestLoading').classList.add('d-none');
        document.getElementById('quickTestResult').classList.add('d-none');
        document.getElementById('quickTestResult').innerHTML = '';

        const modal = new bootstrap.Modal(document.getElementById('modalQuickTest'));
        modal.show();
    }

    function runQuickTestFromSelect() {
        const configId = document.getElementById('quickTestSelectConfig').value;
        const cfgText = document.getElementById('quickTestSelectConfig').selectedOptions[0].text;
        testSpecificConfig(configId, cfgText, 'quickModal');
    }

    // Uji Koneksi Konfigurasi Spesifik (Bisa dipanggil dari tabel, banner, atau quick modal)
    function testSpecificConfig(configId, name, source = 'table') {
        const loading = document.getElementById('quickTestLoading');
        const resultArea = document.getElementById('quickTestResult');
        const heroStatus = document.getElementById('heroConnectionStatus');

        if (source === 'hero' && heroStatus) {
            heroStatus.innerHTML = `<span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2 rounded-pill"><span class="spinner-border spinner-border-sm me-1"></span> Menguji...</span>`;
        }

        if (source !== 'quickModal') {
            const selectEl = document.getElementById('quickTestSelectConfig');
            if (selectEl) selectEl.value = configId;
            openQuickTestModal();
        }

        loading.classList.remove('d-none');
        resultArea.classList.add('d-none');

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('config_id', configId);

        fetch(`${BASE_URL}/aiConfig/testConnection`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            loading.classList.add('d-none');
            resultArea.classList.remove('d-none');

            if (res.csrf_token) currentCsrf = res.csrf_token;

            if (res.status === 'success') {
                resultArea.innerHTML = `
                    <div class="alert alert-success border-0 rounded-4 p-3 mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4 text-success">✅</span>
                            <div>
                                <h6 class="fw-bold mb-0 text-success">Koneksi Berhasil! (HTTP ${res.http_code})</h6>
                                <small class="text-muted">Target: ${name}</small>
                            </div>
                        </div>
                        <div class="row g-2 mb-2 text-center">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border">
                                    <small class="text-muted d-block">Latensi</small>
                                    <strong class="text-success fs-6">${res.latency_ms} ms</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border">
                                    <small class="text-muted d-block">Provider</small>
                                    <strong class="text-dark fs-6">${res.provider.toUpperCase()}</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border">
                                    <small class="text-muted d-block">Status</small>
                                    <strong class="text-success fs-6">Online</strong>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 bg-white rounded-3 border small">
                            <span class="fw-bold text-dark d-block mb-1">Respon Contoh Provider:</span>
                            <span class="text-muted font-monospace">"${res.reply || 'OK'}"</span>
                        </div>
                    </div>
                `;
                showToast(`Tes ${name} BERHASIL (${res.latency_ms} ms)`, 'success');

                if (heroStatus) {
                    heroStatus.innerHTML = `<span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill"><i class="bi bi-broadcast me-1"></i> Terhubung (${res.latency_ms} ms)</span>`;
                }
            } else {
                resultArea.innerHTML = `
                    <div class="alert alert-danger border-0 rounded-4 p-3 mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fs-4 text-danger">❌</span>
                            <div>
                                <h6 class="fw-bold mb-0 text-danger">Koneksi Gagal (HTTP ${res.http_code || 0})</h6>
                                <small class="text-muted">Target: ${name}</small>
                            </div>
                        </div>
                        <div class="p-3 bg-white rounded-3 border text-danger small">
                            <strong>Pesan Error:</strong><br>
                            ${res.error_message || 'Terjadi kesalahan saat menghubungi provider.'}
                        </div>
                        <small class="text-muted d-block mt-2">
                            Tips: Pastikan Base URL benar, kuota API key mencukupi, dan nama model didukung oleh provider.
                        </small>
                    </div>
                `;
                showToast(`Tes ${name} GAGAL: ${res.error_message}`, 'danger');

                if (heroStatus) {
                    heroStatus.innerHTML = `<span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Terputus</span>`;
                }
            }
        })
        .catch(err => {
            loading.classList.add('d-none');
            resultArea.classList.remove('d-none');
            resultArea.innerHTML = `
                <div class="alert alert-danger rounded-4 p-3">
                    <strong>Error Jaringan:</strong> ${err.message}
                </div>
            `;
            showToast('Error pengujian koneksi: ' + err.message, 'danger');
        });
    }

    // Toggle Status Aktif
    function toggleActive(id, isChecked, name) {
        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('status', isChecked ? 1 : 0);
        formData.append('ajax', '1');

        fetch(`${BASE_URL}/aiConfig/toggle/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.csrf_token) currentCsrf = res.csrf_token;
            if (res.status === 'success') {
                const lbl = document.getElementById(`lbl_toggle_${id}`);
                if (lbl) {
                    lbl.textContent = isChecked ? 'Aktif' : 'Nonaktif';
                    lbl.className = `form-check-label small ${isChecked ? 'text-success fw-semibold' : 'text-muted'}`;
                }
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Gagal mengubah status', 'danger');
                document.getElementById(`toggle_${id}`).checked = !isChecked;
            }
        })
        .catch(err => {
            document.getElementById(`toggle_${id}`).checked = !isChecked;
            showToast('Error koneksi: ' + err.message, 'danger');
        });
    }

    function handleHeroQuickSwitch() {
        const sel = document.getElementById('heroQuickSwitchSelect');
        if (!sel) return;
        const opt = sel.selectedOptions[0];
        if (!opt) return;
        const id = sel.value;
        const name = opt.getAttribute('data-name') || opt.text;
        const model = opt.getAttribute('data-model') || '';
        confirmSetPrimary(id, name, model);
    }

    // Konfirmasi & Eksekusi Set Primary
    function confirmSetPrimary(id, name, model) {
        targetPrimaryId = id;
        document.getElementById('confirmPrimaryTitle').textContent = `Jadikan '${name}' Sebagai Model Utama?`;
        document.getElementById('confirmPrimaryDesc').innerHTML = `
            Model <strong>${model}</strong> akan segera menjadi mesin penggerak aktif untuk seluruh obrolan Customer Chatbot dan analisa Business Insight Toko.
        `;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmPrimary'));
        modal.show();
    }

    function executeSetPrimary() {
        if (!targetPrimaryId) return;

        const btn = document.getElementById('btnConfirmPrimarySubmit');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Mengaktifkan...`;

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('ajax', '1');

        fetch(`${BASE_URL}/aiConfig/setPrimary/${targetPrimaryId}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `Ya, Jadikan Model Utama`;
            if (res.csrf_token) currentCsrf = res.csrf_token;

            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmPrimary'));
                if (modal) modal.hide();
                showToast(res.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast(res.message || 'Gagal menetapkan model utama', 'danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `Ya, Jadikan Model Utama`;
            showToast('Error koneksi: ' + err.message, 'danger');
        });
    }

    // Konfirmasi & Eksekusi Hapus Konfigurasi
    function confirmDelete(id, name) {
        targetDeleteId = id;
        document.getElementById('confirmDeleteTitle').textContent = `Hapus '${name}'?`;
        document.getElementById('confirmDeleteDesc').textContent = `Preset konfigurasi '${name}' akan dihapus permanen dari sistem.`;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
        modal.show();
    }

    function executeDelete() {
        if (!targetDeleteId) return;

        const btn = document.getElementById('btnConfirmDeleteSubmit');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...`;

        const formData = new FormData();
        formData.append('csrf_token', currentCsrf);
        formData.append('ajax', '1');

        fetch(`${BASE_URL}/aiConfig/delete/${targetDeleteId}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `Ya, Hapus`;
            if (res.csrf_token) currentCsrf = res.csrf_token;

            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmDelete'));
                if (modal) modal.hide();
                showToast(res.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast(res.message || 'Gagal menghapus konfigurasi.', 'danger');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `Ya, Hapus`;
            showToast('Error koneksi: ' + err.message, 'danger');
        });
    }
</script>
