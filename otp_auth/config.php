<?php
/**
 * =========================================================================
 * File Konfigurasi Sistem Autentikasi & Database
 * Proyek: nnaco.sa Store
 * =========================================================================
 */

// Aktifkan mode strict typing & laporkan error selama pengembangan
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Nonaktifkan display_errors di publik untuk mencegah kebocoran info
ini_set('log_errors', '1');

// Inisialisasi Session Aman jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,      // Mencegah akses cookie via JavaScript (Anti-XSS)
        'cookie_samesite' => 'Lax',     // Perlindungan CSRF
        'use_strict_mode' => true,
    ]);
}

// -------------------------------------------------------------------------
// 1. Konfigurasi Database (MySQL via PDO)
// -------------------------------------------------------------------------
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'nnaco_sa');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// -------------------------------------------------------------------------
// 2. Konfigurasi API Gateway Fonnte WhatsApp
// -------------------------------------------------------------------------
if (!defined('FONNTE_ENDPOINT')) define('FONNTE_ENDPOINT', 'https://api.fonnte.com/send');
if (!defined('FONNTE_TOKEN')) define('FONNTE_TOKEN', '28cchAFgoLBVcTNR5vz7');

// -------------------------------------------------------------------------
// 3. Konfigurasi Kebijakan Keamanan OTP
// -------------------------------------------------------------------------
if (!defined('OTP_EXPIRATION_MINUTES')) define('OTP_EXPIRATION_MINUTES', 5);      // Masa aktif kode OTP (5 menit)
if (!defined('OTP_MAX_ATTEMPTS')) define('OTP_MAX_ATTEMPTS', 5);            // Maksimal salah input per kode OTP
if (!defined('OTP_COOLDOWN_SECONDS')) define('OTP_COOLDOWN_SECONDS', 60);       // Jeda waktu antar permintaan OTP (Anti-Spam)
if (!defined('STORE_NAME')) define('STORE_NAME', 'nnaco.sa');
if (!defined('STORE_URL')) define('STORE_URL', 'http://localhost/nnaco.sa/public');

/**
 * Mendapatkan koneksi Database PDO tunggal (Singleton pattern sederhana)
 * 
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statement native asli MySQL (Anti SQL Injection)
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("[Database Connection Error] " . $e->getMessage());
            die("Terjadi kendala koneksi ke database. Silakan hubungi administrator.");
        }
    }

    return $pdo;
}

/**
 * Helper untuk menyetel pesan notifikasi (Flash Message)
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_alert'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Helper untuk mengambil dan menghapus pesan flash
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash_alert'])) {
        $flash = $_SESSION['flash_alert'];
        unset($_SESSION['flash_alert']);
        return $flash;
    }
    return null;
}

/**
 * Helper mendapatkan IP Address pengunjung yang aman
 */
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
