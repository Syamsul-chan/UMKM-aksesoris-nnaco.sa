<?php
/**
 * =========================================================================
 * Halaman Verifikasi Kode OTP WhatsApp
 * File: verify.php
 * Proyek: nnaco.sa Store
 * =========================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonnte_helper.php';

// Jika pengguna sudah login, alihkan ke dashboard
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

// Pastikan nomor telepon ada dalam session, jika tidak arahkan kembali ke login
$phone = $_SESSION['otp_phone'] ?? '';
if (empty($phone)) {
    setFlash('warning', 'Silakan masukkan nomor WhatsApp Anda terlebih dahulu.');
    header('Location: login.php');
    exit;
}

// Generate CSRF Token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Hitung sisa masa aktif OTP (5 menit)
$expiresAt = $_SESSION['otp_expires_at'] ?? (time() + (OTP_EXPIRATION_MINUTES * 60));
$remainingSeconds = max(0, $expiresAt - time());

// Hitung sisa waktu jeda kirim ulang (cooldown 60 detik)
$sentAt = $_SESSION['otp_sent_at'] ?? time();
$cooldownRemaining = max(0, OTP_COOLDOWN_SECONDS - (time() - $sentAt));

$maskedPhone = maskPhoneNumber($phone);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Kode OTP WhatsApp | <?= STORE_NAME ?></title>
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
            max-width: 460px;
            width: 100%;
            padding: 36px 30px;
            border: 3px solid #FFFFFF;
            position: relative;
        }

        .otp-header-icon {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #EBF5FE 0%, #E2EDFD 100%);
            color: #4A90E2;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 20px rgba(74, 144, 226, 0.2);
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

        .phone-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #E8F8F0;
            color: var(--kw-green-dark);
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.88rem;
            margin: 8px 0 14px 0;
        }

        /* 6-Digit OTP Box Grid */
        .otp-inputs-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin: 22px 0;
        }

        .otp-box {
            width: 100%;
            height: 56px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            border: 2px solid #EADBFA;
            background: #FAF8FD;
            border-radius: 14px;
            color: #382430;
            transition: all 0.2s ease;
        }

        .otp-box:focus {
            background: #FFFFFF;
            border-color: var(--kw-purple);
            box-shadow: 0 0 0 4px rgba(166, 132, 220, 0.22);
            outline: none;
            transform: translateY(-2px);
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--kw-purple) 0%, var(--kw-purple-dark) 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 13px 24px;
            font-size: 0.95rem;
            font-weight: 800;
            width: 100%;
            box-shadow: 0 6px 18px rgba(166, 132, 220, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(166, 132, 220, 0.55);
            color: #FFFFFF;
        }

        .btn-resend {
            color: var(--kw-green-dark);
            background: #E8F8F0;
            border: 1px solid #C4ECD5;
            font-size: 0.82rem;
            font-weight: 800;
            border-radius: 50px;
            padding: 7px 16px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-resend:hover:not(:disabled) {
            background: var(--kw-green-wa);
            color: #FFFFFF;
            border-color: var(--kw-green-wa);
        }

        .btn-resend:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            background: #F1F3F5;
            border-color: #DEE2E6;
            color: #6C757D;
        }

        .timer-badge {
            font-weight: 800;
            color: #DC3545;
            background: #FFF0F2;
            padding: 3px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .change-number-link {
            color: #8C7588;
            font-size: 0.8rem;
            text-decoration: underline;
            font-weight: 700;
        }

        .change-number-link:hover {
            color: var(--kw-purple);
        }
    </style>
</head>
<body>

<div class="otp-card">
    <div class="text-center">
        <div class="otp-header-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <span class="brand-badge">🌸 Verifikasi Keamanan</span>
        <h4 class="fw-bold mb-1" style="color: #382430; letter-spacing: -0.4px;">Masukkan Kode OTP</h4>
        <p class="text-muted small mb-1">Kode 6 digit telah dikirim melalui WhatsApp ke:</p>
        
        <div>
            <span class="phone-badge">
                <i class="bi bi-whatsapp"></i>
                <span><?= htmlspecialchars($maskedPhone) ?></span>
            </span>
        </div>
        <a href="login.php" class="change-number-link">Bukan nomor Anda? Ganti Nomor</a>
    </div>

    <!-- Notifikasi Flash Alert -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show rounded-4 py-2 px-3 small mt-3" role="alert">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Verifikasi OTP -->
    <form action="check_otp.php" method="POST" id="otpForm" class="mt-2">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <!-- Hidden full combined OTP string for fallback -->
        <input type="hidden" name="otp_code" id="fullOtpCode" value="">

        <!-- 6 Input Kotak Kode OTP -->
        <div class="otp-inputs-grid">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <input 
                    type="text" 
                    class="otp-box" 
                    maxlength="1" 
                    pattern="[0-9]" 
                    inputmode="numeric" 
                    autocomplete="one-time-code"
                    required
                    data-index="<?= $i ?>"
                    <?= $i === 0 ? 'autofocus' : '' ?>
                >
            <?php endfor; ?>
        </div>

        <!-- Indikator Masa Kedaluwarsa OTP -->
        <div class="text-center small mb-3 text-muted">
            Masa berlaku kode: 
            <span class="timer-badge" id="expirationTimer">05:00</span>
        </div>

        <button type="submit" class="btn-verify mb-3" id="submitBtn">
            <i class="bi bi-check-circle-fill"></i>
            <span>Verifikasi & Masuk</span>
        </button>
    </form>

    <!-- Opsi Kirim Ulang OTP -->
    <div class="text-center pt-3 border-top" style="border-color: #F1EBF7 !important;">
        <p class="small text-muted mb-2">Belum menerima pesan WhatsApp?</p>
        <form action="send_otp.php" method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
            <button type="submit" class="btn-resend" id="resendBtn" disabled>
                <i class="bi bi-arrow-clockwise"></i>
                <span id="resendText">Kirim Ulang (<?= $cooldownRemaining ?>s)</span>
            </button>
        </form>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const otpBoxes = document.querySelectorAll('.otp-box');
    const fullOtpInput = document.getElementById('fullOtpCode');
    const otpForm = document.getElementById('otpForm');

    // Focus ke input pertama
    if (otpBoxes[0]) {
        otpBoxes[0].focus();
    }

    // Tangani Input Digit per Digit
    otpBoxes.forEach((box, index) => {
        box.addEventListener('input', (e) => {
            const val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val;

            if (val && index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            }

            checkAndSyncOtp();
        });

        // Tangani navigasi Backspace
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                otpBoxes[index - 1].focus();
            }
        });

        // Tangani Paste 6 digit sekaligus
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            const digitsOnly = pasteData.replace(/[^0-9]/g, '');

            if (digitsOnly.length > 0) {
                for (let i = 0; i < otpBoxes.length; i++) {
                    otpBoxes[i].value = digitsOnly[i] || '';
                }
                const nextIndex = Math.min(digitsOnly.length, otpBoxes.length - 1);
                otpBoxes[nextIndex].focus();
                checkAndSyncOtp();

                // Auto-submit jika pas 6 digit di-paste
                if (digitsOnly.length === 6) {
                    setTimeout(() => otpForm.submit(), 300);
                }
            }
        });
    });

    function checkAndSyncOtp() {
        let code = '';
        otpBoxes.forEach(box => code += box.value);
        fullOtpInput.value = code;

        // Auto submit jika semua 6 digit sudah terisi
        if (code.length === 6) {
            otpForm.submit();
        }
    }

    otpForm.addEventListener('submit', function (e) {
        let code = '';
        otpBoxes.forEach(box => code += box.value);
        fullOtpInput.value = code;

        if (code.length !== 6) {
            e.preventDefault();
            alert('Silakan lengkapi 6 digit kode OTP Anda.');
            return false;
        }
    });

    // -------------------------------------------------------------
    // Timer 1: Hitung Mundur Kedaluwarsa OTP (5 Menit)
    // -------------------------------------------------------------
    let remainingSeconds = <?= $remainingSeconds ?>;
    const timerElem = document.getElementById('expirationTimer');

    function updateExpirationTimer() {
        if (remainingSeconds <= 0) {
            timerElem.textContent = 'Kedaluwarsa';
            timerElem.style.background = '#F8D7DA';
            timerElem.style.color = '#721C24';
            return;
        }

        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        timerElem.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        remainingSeconds--;
    }

    updateExpirationTimer();
    const expInterval = setInterval(updateExpirationTimer, 1000);

    // -------------------------------------------------------------
    // Timer 2: Hitung Mundur Tombol Kirim Ulang OTP (Cooldown 60s)
    // -------------------------------------------------------------
    let cooldown = <?= $cooldownRemaining ?>;
    const resendBtn = document.getElementById('resendBtn');
    const resendText = document.getElementById('resendText');

    function updateCooldown() {
        if (cooldown <= 0) {
            resendBtn.disabled = false;
            resendText.textContent = 'Kirim Ulang Kode OTP';
            return;
        }

        resendBtn.disabled = true;
        resendText.textContent = `Kirim Ulang (${cooldown}s)`;
        cooldown--;
        setTimeout(updateCooldown, 1000);
    }

    updateCooldown();
});
</script>
</body>
</html>
