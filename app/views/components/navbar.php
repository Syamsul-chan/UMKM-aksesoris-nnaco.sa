<?php
    $isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['username']);
    $username = $isLoggedIn ? $_SESSION['user']['username'] : 'Oda Dink';
    $role = $isLoggedIn ? strtolower($_SESSION['user']['role']) : 'admin';
    $roleDisplay = $isLoggedIn ? ucfirst($_SESSION['user']['role']) : 'Super Admin';
    $avatarImg = file_exists('img/avatar-oda.jpg') ? BASEURL . '/img/avatar-oda.jpg' : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&h=100&fit=crop&crop=faces';
?>
<header class="dash-navbar">
    <!-- Left: Hamburger & Dashboard Title with Pastel Sparkles -->
    <div class="dash-nav-left">
        <h2 class="dash-nav-title">
            <i class="bi bi-list me-1" style="font-size: 1.6rem; cursor: pointer;"></i>
            <span>Dashboard</span>
            <span class="dash-nav-sparkle">✦</span>
        </h2>
    </div>

    <!-- Right: Chatbot Icon, Notifications with Pill Badge, User Profile Dropdown -->
    <div class="dash-nav-right">
        <!-- AI Chatbot Bubble Button -->
        <a href="javascript:void(0)" class="dash-circle-btn" onclick="if(window.toggleChatbot) window.toggleChatbot(); else alert('Chatbot AI siap!');" title="Buka AI Chatbot">
            <i class="bi bi-chat-dots-fill" style="color: #B388FF;"></i>
        </a>

        <!-- Shopping Cart with Realtime Live Badge -->
        <?php 
            $navCartTotal = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
        ?>
        <a href="<?= BASEURL ?>/cart" class="dash-circle-btn position-relative" title="Keranjang Belanja">
            <i class="bi bi-bag-heart-fill" style="color: #EC4899;"></i>
            <span id="navCartBadge" class="dash-bell-pill-badge" style="background: linear-gradient(135deg, #EC4899 0%, #F43F5E 100%); display: <?= $navCartTotal > 0 ? 'inline-block' : 'none' ?>;"><?= $navCartTotal ?></span>
        </a>

        <!-- Notification Bell with Pink Badge -->
        <a href="javascript:void(0)" class="dash-circle-btn" title="Notifikasi Toko">
            <i class="bi bi-bell-fill" style="color: #7E708E;"></i>
            <span class="dash-bell-pill-badge">55</span>
        </a>

        <!-- User Profile Pill Dropdown -->
        <div class="dropdown">
            <a href="javascript:void(0)" class="dash-user-pill dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $avatarImg ?>" alt="<?= htmlspecialchars($username) ?>" class="dash-user-avatar">
                <div class="dash-user-info d-none d-sm-block text-start">
                    <div class="dash-user-name"><?= htmlspecialchars($username) ?></div>
                    <div class="dash-user-role"><?= htmlspecialchars($roleDisplay) ?></div>
                </div>
                <i class="bi bi-chevron-down ms-1 text-muted" style="font-size: 0.75rem;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="border-radius: 20px; min-width: 210px; margin-top: 10px; box-shadow: 0 10px 30px rgba(180, 150, 220, 0.2) !important;">
                <li><h6 class="dropdown-header text-uppercase fw-bold text-muted small"><?= htmlspecialchars($roleDisplay) ?></h6></li>
                <li><a class="dropdown-item py-2 fw-semibold" href="<?= BASEURL ?>" target="_blank"><i class="bi bi-shop me-2 text-primary"></i>Kunjungi Toko</a></li>
                <?php if ($role === 'admin' || $role === 'owner' || $role === 'operator'): ?>
                    <li><a class="dropdown-item py-2 fw-semibold" href="<?= BASEURL ?>/<?= $role ?>/products"><i class="bi bi-grid me-2 text-info"></i>Kelola Produk</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <?php if ($isLoggedIn): ?>
                    <li><a class="dropdown-item py-2 fw-semibold text-danger" href="<?= BASEURL ?>/auth/logout"><i class="bi bi-box-arrow-left me-2"></i>Keluar / Logout</a></li>
                <?php else: ?>
                    <li><a class="dropdown-item py-2 fw-semibold text-primary" href="<?= BASEURL ?>/auth/login"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk / Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>
