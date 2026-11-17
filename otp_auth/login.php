<?php
/**
 * =========================================================================
 * Halaman Permintaan Kode OTP WhatsApp (Login Form)
 * Proyek: nnaco.sa Store
 * =========================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonnte_helper.php';

// Jika pengguna sudah login, langsung alihkan ke dashboard
if (isset($_SESSION['user'])) {
    $role = strtolower($_SESSION['user']['role'] ?? 'customer');
    if ($role === 'admin') {
        header('Location: ' . STORE_URL . '/admin');
    } elseif ($role === 'owner') {
        header('Location: ' . STORE_URL . '/owner');
    } else {
        header('Location: ' . STORE_URL);
    }
    exit;
}

// Generate CSRF Token untuk keamanan form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login WhatsApp OTP | <?= STORE_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Quicksand & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Quicksand:wght@600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --kw-bg: linear-gradient(135deg, #DDD6F3 0%, #D8D0F0 45%, #E6E0F8 100%);
            --kw-card: #FFFFFF;
            --kw-text: #382430;
            --kw-purple: #A684DC;
            --kw-purple-dark: #8A64C5;
            --kw-green-wa: #25D366;
            --kw-green-dark: #128C7E;
        }

        body {
            background: var(--kw-bg) !important;
            font-family: 'Quicksand', 'Plus Jakarta Sans', sans-serif;
            color: var(--kw-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            margin: 0;
        }

        .otp-card {
            background: var(--kw-card);
            border-radius: 28px;
            box-shadow: 0 16px 45px rgba(160, 135, 205, 0.25);
            max-width: 440px;
            width: 100%;
            padding: 36px 32px;
            border: 3px solid #FFFFFF;
            position: relative;
            overflow: hidden;
        }

        .otp-header-icon {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #E8F8F0 0%, #D2F4E2 100%);
            color: var(--kw-green-dark);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2);
        }

        .brand-badge {
            display: inline-block;
            background: #F3EEFA;
            color: var(--kw-purple);
            font-size: 0.76rem;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 50px;
            margin-bottom: 8px;
        }

        .form-control-phone {
            border-radius: 16px;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 700;
            border: 2px solid #EADBFA;
            background: #FAF8FD;
            color: var(--kw-text);
            transition: all 0.2s ease;
        }

        .form-control-phone:focus {
            background: #FFFFFF;
            border-color: var(--kw-purple);
            box-shadow: 0 0 0 4px rgba(166, 132, 220, 0.2);
            outline: none;
        }

        .input-group-text-flag {
            background: #FAF8FD;
            border: 2px solid #EADBFA;
            border-right: none;
            border-radius: 16px 0 0 16px;
            font-weight: 800;
            color: #5D4755;
            padding-left: 16px;
            padding-right: 12px;
        }

        .input-group .form-control-phone {
            border-left: none;
            border-radius: 0 16px 16px 0;
        }

        .btn-send-otp {
            background: linear-gradient(135deg, var(--kw-green-wa) 0%, var(--kw-green-dark) 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 13px 24px;
            font-size: 0.95rem;
            font-weight: 800;
            width: 100%;
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-send-otp:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.5);
            color: #FFFFFF;
        }

        .back-link {
            color: #8C7588;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--kw-purple);
        }
    </style>
</head>
<body>

<div class="otp-card">
    <div class="text-center">
        <div class="otp-header-icon">
            <i class="bi bi-whatsapp"></i>
        </div>
        <span class="brand-badge">🌸 <?= STORE_NAME ?> Autentikasi</span>
        <h4 class="fw-bold mb-1" style="color: #382430; letter-spacing: -0.4px;">Login via WhatsApp</h4>
        <p class="text-muted small mb-4">Masukkan nomor WhatsApp Anda untuk menerima 6 digit kode verifikasi login resmi.</p>
    </div>

    <!-- Tampilkan Notifikasi Flash Jika Ada -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show rounded-4 py-2 px-3 small" role="alert">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Input Nomor WhatsApp -->
    <form action="send_otp.php" method="POST">
        <!-- CSRF Token Protection -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-4">
            <label for="phone" class="form-label fw-bold small text-muted mb-2">Nomor WhatsApp Terdaftar</label>
            <div class="input-group">
                <span class="input-group-text input-group-text-flag">
                    <span class="me-1">🇮🇩</span> +62
                </span>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-control form-control-phone" 
                    placeholder="81234567890" 
                    required 
                    autofocus 
                    autocomplete="tel"
                    value="<?= htmlspecialchars($_SESSION['last_phone_input'] ?? '') ?>"
                >
            </div>
            <div class="form-text small mt-2" style="font-size: 0.75rem; color: #8C7588;">
                <i class="bi bi-info-circle me-1"></i> Format lokal (08xx) atau langsung (8xx) akan otomatis disesuaikan.
            </div>
        </div>

        <button type="submit" class="btn-send-otp mb-3">
            <i class="bi bi-send-fill"></i>
            <span>Kirim Kode OTP</span>
        </button>
    </form>

    <div class="text-center mt-3 pt-3 border-top" style="border-color: #F1EBF7 !important;">
        <a href="<?= STORE_URL ?>/auth/login" class="back-link d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Login Password Biasa</span>
        </a>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
