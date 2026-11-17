<?php
/**
 * =========================================================================
 * Proses Pembuatan & Pengiriman Kode OTP via WhatsApp Fonnte API
 * File: send_otp.php
 * Proyek: nnaco.sa Store
 * =========================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonnte_helper.php';

// 1. Pastikan request dikirim dengan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 2. Validasi CSRF Token untuk mencegah serangan Cross-Site Request Forgery
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    setFlash('danger', 'Sesi tidak valid atau telah kedaluwarsa. Silakan refresh halaman dan coba lagi.');
    header('Location: login.php');
    exit;
}

// 3. Ambil dan validasi input nomor WhatsApp
$rawPhone = trim($_POST['phone'] ?? '');
if (empty($rawPhone)) {
    // Jika nomor dikirim dari tombol "Kirim Ulang" di verify.php, ambil dari session
    $rawPhone = $_SESSION['otp_phone'] ?? '';
}

if (empty($rawPhone)) {
    setFlash('danger', 'Nomor WhatsApp wajib diisi.');
    header('Location: login.php');
    exit;
}

// Simpan nomor input terakhir di session untuk kemudahan UX
$_SESSION['last_phone_input'] = $rawPhone;

try {
    // Format otomatis ke format standar internasional 628xxx
    $formattedPhone = formatPhoneNumber($rawPhone);
} catch (InvalidArgumentException $e) {
    setFlash('danger', $e->getMessage());
    header('Location: login.php');
    exit;
}

// Variasi nomor untuk pencarian fleksibel di database (628xxx vs 08xxx)
$zeroPhone = '0' . substr($formattedPhone, 2);

// 4. Hubungkan ke Database PDO & Periksa apakah nomor terdaftar
$pdo = getDbConnection();

$stmtUser = $pdo->prepare('
    SELECT users.*, roles.name AS role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.id 
    WHERE (users.phone_number = :formatted OR users.phone_number = :zero OR users.phone_number = :raw)
      AND users.is_active = 1 
    LIMIT 1
');

$stmtUser->execute([
    ':formatted' => $formattedPhone,
    ':zero'      => $zeroPhone,
    ':raw'       => $rawPhone,
]);

$user = $stmtUser->fetch();

if (!$user) {
    setFlash('danger', 'Nomor WhatsApp (' . htmlspecialchars($rawPhone) . ') tidak terdaftar atau akun nonaktif. Silakan periksa kembali atau hubungi admin.');
    header('Location: login.php');
    exit;
}

// Standarisasi nomor di database jika sebelumnya masih format 08xxx
if ($user['phone_number'] !== $formattedPhone) {
    $stmtUpdatePhone = $pdo->prepare('UPDATE users SET phone_number = :phone WHERE id = :id');
    $stmtUpdatePhone->execute([':phone' => $formattedPhone, ':id' => $user['id']]);
}

// 5. Anti-Spam / Cooldown Rate Limiting: Cegah spam pengiriman OTP berulang dalam 60 detik
$stmtCooldown = $pdo->prepare('
    SELECT created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed_seconds 
    FROM otp_logs 
    WHERE phone_number = :phone 
      AND created_at > (NOW() - INTERVAL :cooldown SECOND)
    ORDER BY id DESC 
    LIMIT 1
');

$stmtCooldown->execute([
    ':phone'    => $formattedPhone,
    ':cooldown' => OTP_COOLDOWN_SECONDS,
]);

$cooldownCheck = $stmtCooldown->fetch();

if ($cooldownCheck) {
    $elapsed = (int) $cooldownCheck['elapsed_seconds'];
    $remaining = max(1, OTP_COOLDOWN_SECONDS - $elapsed);
    setFlash('warning', "Mohon tunggu {$remaining} detik sebelum meminta kode OTP WhatsApp baru.");
    $_SESSION['otp_phone'] = $formattedPhone;
    header('Location: verify.php');
    exit;
}

// 6. Batalkan / Nonaktifkan kode OTP sebelumnya yang belum digunakan untuk nomor ini
$stmtInvalidate = $pdo->prepare('
    UPDATE otp_logs 
    SET is_used = 1 
    WHERE phone_number = :phone AND is_used = 0
');
$stmtInvalidate->execute([':phone' => $formattedPhone]);

// 7. Generate Kode OTP 6-Digit Acak Kriptografis Aman
try {
    $otpCode = (string) random_int(100000, 999999);
} catch (Exception $e) {
    $otpCode = str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

// 8. Hash Kode OTP Menggunakan Algoritma BCRYPT (Zero-Plaintext Security)
$otpHashed = password_hash($otpCode, PASSWORD_BCRYPT);

// Waktu kedaluwarsa 5 menit ke depan
$expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRATION_MINUTES * 60));
$clientIp  = getClientIp();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// 9. Simpan State OTP ke Tabel Database `otp_logs`
$stmtInsertOtp = $pdo->prepare('
    INSERT INTO otp_logs (
        user_id, 
        phone_number, 
        otp_hash, 
        expires_at, 
        is_used, 
        attempts, 
        ip_address, 
        user_agent, 
        created_at
    ) VALUES (
        :user_id, 
        :phone_number, 
        :otp_hash, 
        :expires_at, 
        0, 
        0, 
        :ip_address, 
        :user_agent, 
        NOW()
    )
');

$stmtInsertOtp->execute([
    ':user_id'      => $user['id'],
    ':phone_number' => $formattedPhone,
    ':otp_hash'     => $otpHashed,
    ':expires_at'   => $expiresAt,
    ':ip_address'   => $clientIp,
    ':user_agent'   => substr($userAgent, 0, 500),
]);

$otpLogId = (int) $pdo->lastInsertId();

// 10. Kirim Pesan WhatsApp Melalui Fonnte API Gateway
$sendResult = sendWhatsAppOTP($formattedPhone, $otpCode, OTP_EXPIRATION_MINUTES);

// 11. Evaluasi Hasil Pengiriman Fonnte API
if (!$sendResult['success']) {
    // Jika gagal mengirim pesan (kuota habis, koneksi error, dll), batalkan record OTP
    $stmtRollback = $pdo->prepare('DELETE FROM otp_logs WHERE id = :id');
    $stmtRollback->execute([':id' => $otpLogId]);

    setFlash('danger', 'Gagal mengirim pesan OTP WhatsApp: ' . $sendResult['message'] . '. Silakan periksa kembali koneksi atau kuota WhatsApp Gateway.');
    header('Location: login.php');
    exit;
}

// 12. Set Session State untuk Tahap Verifikasi
$_SESSION['otp_phone']       = $formattedPhone;
$_SESSION['otp_user_id']     = (int) $user['id'];
$_SESSION['otp_user_name']   = $user['username'];
$_SESSION['otp_user_role']   = $user['role_name'];
$_SESSION['otp_sent_at']     = time();
$_SESSION['otp_expires_at']  = time() + (OTP_EXPIRATION_MINUTES * 60);

// Set pesan sukses flash
$maskedNumber = maskPhoneNumber($formattedPhone);
setFlash('success', "Kode verifikasi 6 digit telah berhasil dikirim ke nomor WhatsApp {$maskedNumber}. Silakan periksa pesan Anda.");

// Arahkan ke halaman verifikasi OTP
header('Location: verify.php');
exit;
