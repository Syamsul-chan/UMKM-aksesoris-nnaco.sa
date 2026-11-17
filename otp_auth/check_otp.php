<?php
/**
 * =========================================================================
 * Proses Validasi Kode OTP & Inisialisasi Session Login
 * File: check_otp.php
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

// 2. Validasi CSRF Token
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    setFlash('danger', 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba kembali.');
    header('Location: verify.php');
    exit;
}

// 3. Pastikan nomor WhatsApp ada dalam session verifikasi
$phone = $_SESSION['otp_phone'] ?? '';
if (empty($phone)) {
    setFlash('danger', 'Sesi verifikasi Anda telah berakhir. Silakan masukkan nomor kembali.');
    header('Location: login.php');
    exit;
}

// 4. Ambil dan sanitasi kode OTP yang diinput
$inputOtp = trim($_POST['otp_code'] ?? '');
$inputOtp = preg_replace('/[^0-9]/', '', $inputOtp);

if (strlen($inputOtp) !== 6) {
    setFlash('danger', 'Kode OTP harus berupa 6 digit angka lengkap.');
    header('Location: verify.php');
    exit;
}

// 5. Hubungkan ke Database PDO
$pdo = getDbConnection();

// 6. Ambil record OTP aktif terbaru untuk nomor ini
$stmtOtp = $pdo->prepare('
    SELECT * 
    FROM otp_logs 
    WHERE phone_number = :phone 
      AND is_used = 0 
    ORDER BY id DESC 
    LIMIT 1
');

$stmtOtp->execute([':phone' => $phone]);
$otpLog = $stmtOtp->fetch();

// 7. Validasi: Apakah ada permintaan OTP aktif?
if (!$otpLog) {
    setFlash('danger', 'Tidak ada kode OTP aktif yang menunggu verifikasi untuk nomor ini. Silakan minta kode baru.');
    header('Location: login.php');
    exit;
}

$otpId = (int) $otpLog['id'];
$attempts = (int) $otpLog['attempts'];

// 8. Mitigasi Serangan Brute Force: Batasi maksimal percobaan salah (default: 5x)
if ($attempts >= OTP_MAX_ATTEMPTS) {
    // Nonaktifkan OTP log ini
    $stmtExpire = $pdo->prepare('UPDATE otp_logs SET is_used = 1 WHERE id = :id');
    $stmtExpire->execute([':id' => $otpId]);

    setFlash('danger', 'Anda telah melebihi batas percobaan salah (' . OTP_MAX_ATTEMPTS . 'x). Kode OTP dibatalkan demi keamanan akun. Silakan minta kode baru.');
    header('Location: login.php');
    exit;
}

// 9. Validasi Waktu Kedaluwarsa (5 Menit)
$now = time();
$expiresTimestamp = strtotime($otpLog['expires_at']);

if ($now > $expiresTimestamp) {
    // Tandai OTP telah kedaluwarsa
    $stmtExpire = $pdo->prepare('UPDATE otp_logs SET is_used = 1 WHERE id = :id');
    $stmtExpire->execute([':id' => $otpId]);

    setFlash('danger', 'Kode OTP telah kedaluwarsa (masa aktif 5 menit telah habis). Silakan klik Kirim Ulang untuk menerima kode baru.');
    header('Location: verify.php');
    exit;
}

// 10. Verifikasi Kecocokan Kode OTP Menggunakan Algoritma Kriptografi (password_verify)
$isValidOtp = password_verify($inputOtp, $otpLog['otp_hash']);

if (!$isValidOtp) {
    // Tambah counter percobaan salah
    $newAttempts = $attempts + 1;
    $stmtUpdateAttempts = $pdo->prepare('UPDATE otp_logs SET attempts = :attempts WHERE id = :id');
    $stmtUpdateAttempts->execute([
        ':attempts' => $newAttempts,
        ':id'       => $otpId,
    ]);

    $remainingAttempts = OTP_MAX_ATTEMPTS - $newAttempts;

    if ($remainingAttempts <= 0) {
        // Batalkan OTP jika batas tercapai
        $stmtExpire = $pdo->prepare('UPDATE otp_logs SET is_used = 1 WHERE id = :id');
        $stmtExpire->execute([':id' => $otpId]);

        setFlash('danger', 'Kode OTP salah. Batas percobaan habis (' . OTP_MAX_ATTEMPTS . 'x). Silakan minta kode OTP baru.');
        header('Location: login.php');
    } else {
        setFlash('danger', "Kode OTP yang Anda masukkan salah. Sisa kesempatan mencoba: {$remainingAttempts} kali.");
        header('Location: verify.php');
    }
    exit;
}

// 11. Sukses Verifikasi: Tandai OTP sebagai telah digunakan (is_used = 1)
$stmtSuccess = $pdo->prepare('
    UPDATE otp_logs 
    SET is_used = 1 
    WHERE id = :id
');
$stmtSuccess->execute([':id' => $otpId]);

// 12. Ambil Profil Lengkap Pengguna Berdasarkan user_id yang Terkait
$userId = (int) $otpLog['user_id'];
$stmtUser = $pdo->prepare('
    SELECT users.*, roles.name AS role_name 
    FROM users 
    JOIN roles ON users.role_id = roles.id 
    WHERE users.id = :user_id AND users.is_active = 1 
    LIMIT 1
');
$stmtUser->execute([':user_id' => $userId]);
$user = $stmtUser->fetch();

if (!$user) {
    setFlash('danger', 'Akun pengguna tidak ditemukan atau berstatus nonaktif.');
    header('Location: login.php');
    exit;
}

// 13. Keamanan Sesi: Regenerate Session ID untuk mencegah Session Fixation Attack
session_regenerate_id(true);

// 14. Inisialisasi Session Pengguna Sesuai Standar Aplikasi nnaco.sa
$_SESSION['user'] = [
    'id'       => (int) $user['id'],
    'username' => $user['username'],
    'email'    => $user['email'],
    'role'     => $user['role_name'],
    'phone'    => $user['phone_number'] ?? $phone,
];

// Bersihkan data sementara proses OTP dari Session
unset(
    $_SESSION['otp_phone'],
    $_SESSION['otp_user_id'],
    $_SESSION['otp_user_name'],
    $_SESSION['otp_user_role'],
    $_SESSION['otp_sent_at'],
    $_SESSION['otp_expires_at'],
    $_SESSION['last_phone_input']
);

// 15. Set Notifikasi Selamat Datang
$_SESSION['alert'] = [
    'type'    => 'success',
    'message' => 'Selamat datang kembali, ' . htmlspecialchars($user['username']) . '! Login berhasil menggunakan kode WhatsApp OTP.'
];

// 16. Arahkan ke Dashboard Sesuai Role Pengguna
$role = strtolower($user['role_name']);

switch ($role) {
    case 'admin':
        header('Location: ' . STORE_URL . '/admin');
        break;
    case 'owner':
        header('Location: ' . STORE_URL . '/owner');
        break;
    case 'operator':
        header('Location: ' . STORE_URL . '/operator');
        break;
    case 'customer':
        header('Location: ' . STORE_URL . '/customer');
        break;
    default:
        header('Location: ' . STORE_URL);
        break;
}
exit;
