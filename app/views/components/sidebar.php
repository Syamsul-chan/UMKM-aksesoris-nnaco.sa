<?php 
    $role = isset($_SESSION['user']['role']) ? strtolower($_SESSION['user']['role']) : 'customer';
    $currentView = $view ?? '';
    $isOwner = ($role == 'owner');
    $isAdmin = ($role == 'admin');
    $isOperator = ($role == 'operator');
    $isStaff = ($isAdmin || $isOwner || $isOperator);
    
    $productLink = $isStaff ? ($isOwner ? BASEURL . '/owner/products' : ($isOperator ? BASEURL . '/operator/products' : BASEURL . '/admin/products')) : BASEURL . '#katalog';
    $orderLink = $isStaff ? ($isOwner ? BASEURL . '/owner/salesReport' : ($isOperator ? BASEURL . '/operator/orders' : BASEURL . '/admin/orders')) : BASEURL . '/order';
    $canManagePromo = ($isAdmin || $isOperator || $isOwner);
    $promoLink = $isOwner ? BASEURL . '/owner/promos' : ($isOperator ? BASEURL . '/operator/promos' : BASEURL . '/admin/promos');
    $usersLink = BASEURL . '/' . ($isStaff ? $role : 'admin') . '/users';
    $aiLink = BASEURL . '/' . ($isStaff ? $role : 'admin') . '/aiConfig';
    $otpLink = BASEURL . '/' . ($isStaff ? $role : 'admin') . '/otpConfig';
?>
<aside class="d-sidebar" id="dashboardSidebar">
    <div>
        <!-- Brand Header: Beruang Kecil Bersih + Profil Pengguna + Text -->
        <div class="d-sidebar-header mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-brand-bear-clean" title="nnaco.sa Mascot">
                    🐻
                </div>
                <div class="d-sidebar-header-action" title="Notifikasi Toko">
                    <i class="bi bi-bell text-secondary fs-6"></i>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2.5 p-2 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div class="d-user-avatar-minimal">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <h5 class="d-brand-title text-truncate mb-0">nnaco.sa</h5>
                    <p class="d-brand-sub text-truncate mb-0">Store Dashboard</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu (10 Menu Sesuai Permintaan) -->
        <ul class="d-nav-menu">
            <!-- 1. Dashboard -->
            <li class="d-nav-item <?= ($currentView == 'home/index' || (strpos($currentView, 'index') !== false && strpos($currentView, 'product') === false && strpos($currentView, 'order') === false && strpos($currentView, 'promo') === false && strpos($currentView, 'ai_config') === false && strpos($currentView, 'otp_config') === false)) ? 'active' : '' ?>">
                <a href="<?= BASEURL ?>/<?= $isStaff ? $role : '' ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-grid-fill d-nav-icon"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
            </li>

            <!-- 2. Produk & Layanan -->
            <li class="d-nav-item <?= strpos($currentView, 'product') !== false ? 'active' : '' ?>">
                <a href="<?= $productLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-box-seam d-nav-icon"></i>
                        <span>Produk & Layanan</span>
                    </div>
                </a>
            </li>

            <?php if ($canManagePromo && $role !== 'customer'): ?>
            <!-- 3. Kelola Promo (Khusus Staff: Owner, Admin, Operator) -->
            <li class="d-nav-item <?= strpos($currentView, 'promo') !== false ? 'active' : '' ?>">
                <a href="<?= $promoLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-ticket-perforated d-nav-icon"></i>
                        <span>Kelola Promo</span>
                    </div>
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdmin || $isOwner) && $role !== 'customer'): ?>
            <!-- 4. Manajemen User (Khusus Admin & Owner) -->
            <li class="d-nav-item <?= (strpos($currentView, 'customer') !== false || strpos($currentView, 'user') !== false) ? 'active' : '' ?>">
                <a href="<?= $usersLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-people d-nav-icon"></i>
                        <span>Manajemen User</span>
                    </div>
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdmin || $isOwner) && $role !== 'customer'): ?>
            <!-- 5. Pengaturan (Khusus Admin & Owner) -->
            <li class="d-nav-item <?= strpos($currentView, 'setting') !== false ? 'active' : '' ?>">
                <a href="<?= $aiLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-gear d-nav-icon"></i>
                        <span>Pengaturan</span>
                    </div>
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdmin || $isOwner) && $role !== 'customer'): ?>
            <!-- 6. Konfigurasi AI (Khusus Admin & Owner) -->
            <li class="d-nav-item <?= (strpos($currentView, 'ai_config') !== false || strpos($currentView, 'aiConfig') !== false) ? 'active' : '' ?>">
                <a href="<?= $aiLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-cpu d-nav-icon"></i>
                        <span>Konfigurasi AI</span>
                    </div>
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdmin || $isOwner) && $role !== 'customer'): ?>
            <!-- 7. Pengaturan OTP (Khusus Admin & Owner) -->
            <li class="d-nav-item <?= (strpos($currentView, 'otp_config') !== false || strpos($currentView, 'otpConfig') !== false) ? 'active' : '' ?>">
                <a href="<?= $otpLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-shield-check d-nav-icon"></i>
                        <span>Pengaturan OTP</span>
                    </div>
                </a>
            </li>
            <?php endif; ?>

            <!-- 8. Dokumen Pesanan -->
            <li class="d-nav-item <?= strpos($currentView, 'order') !== false ? 'active' : '' ?>">
                <a href="<?= $orderLink ?>">
                    <div class="d-nav-link-left">
                        <i class="bi bi-file-earmark-text d-nav-icon"></i>
                        <span>Dokumen Pesanan</span>
                    </div>
                </a>
            </li>

            <!-- 9. Keranjang (Cart) -->
            <li class="d-nav-item <?= strpos($currentView, 'cart') !== false ? 'active' : '' ?>">
                <a href="<?= BASEURL ?>/cart" class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-nav-link-left">
                        <i class="bi bi-cart3 d-nav-icon"></i>
                        <span>Keranjang (Cart)</span>
                    </div>
                    <?php $sbCartTotal = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                    <span id="sidebarCartBadge" class="badge rounded-pill bg-info text-dark" style="display: <?= $sbCartTotal > 0 ? 'inline-block' : 'none' ?>; font-size: 0.7rem; padding: 3px 8px; font-weight: 700;"><?= $sbCartTotal ?></span>
                </a>
            </li>

            <!-- 10. Pesan Chat (AI) -->
            <li class="d-nav-item">
                <a href="javascript:void(0)" onclick="if(window.toggleChatbot) window.toggleChatbot(); else alert('Chatbot AI nnaco.sa siap melayani!');">
                    <div class="d-nav-link-left">
                        <i class="bi bi-chat-left-dots d-nav-icon"></i>
                        <span>Pesan Chat (AI)</span>
                    </div>
                </a>
            </li>
        </ul>

        <!-- Light/Dark Mode Toggle Minimalist -->
        <div class="d-mode-toggle-wrap" id="themeToggleBtn" onclick="toggleDashboardTheme()" style="cursor: pointer;" title="Klik untuk beralih mode Dark / Light">
            <span class="d-flex align-items-center gap-2" id="themeLabelText">
                <i class="bi bi-sun text-warning" id="themeIcon"></i> 
                <span id="themeModeName">Light Mode</span>
            </span>
            <div class="d-cloud-switch" id="themeSwitchVisual" title="Ganti Mode Tema"></div>
        </div>
    </div>

    <!-- Bottom User/Status Info -->
    <div style="text-align: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
        <?php if(!isset($_SESSION['user'])): ?>
            <a href="<?= BASEURL ?>/auth/login" class="d-btn-pill-buy w-100 py-2">Masuk Akun</a>
        <?php else: ?>
            <a href="<?= BASEURL ?>/auth/logout" class="text-danger fw-bold text-decoration-none small d-flex align-items-center justify-content-center gap-1">
                <i class="bi bi-box-arrow-right"></i> Logout (<?= htmlspecialchars($_SESSION['user']['username']) ?>)
            </a>
        <?php endif; ?>
    </div>
</aside>
