<?php
/**
 * Test Environment Bootstrap
 * Menginisialisasi konstanta dan autoloading untuk pengujian unit secara terisolasi
 * tanpa membutuhkan koneksi database MySQL aktif ataupun credentials eksternal.
 */

// Pastikan session aktif untuk pengujian CSRF
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Set timezone standar
date_default_timezone_set('Asia/Jakarta');

// Definisikan konstanta konfigurasi mock untuk pengujian
if (!defined('BASEURL')) {
    define('BASEURL', 'http://localhost/nnaco.sa/public');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'nnaco_sa');
}
if (!defined('AI_ENCRYPTION_KEY')) {
    define('AI_ENCRYPTION_KEY', 'test_secret_encryption_key_32_b!');
}
if (!defined('AI_API_KEY')) {
    define('AI_API_KEY', 'mock_testing_key');
}
if (!defined('AI_PROVIDER_URL')) {
    define('AI_PROVIDER_URL', 'https://api.groq.com/openai/v1/chat/completions');
}
if (!defined('AI_MODEL')) {
    define('AI_MODEL', 'llama-3.3-70b-versatile');
}

// Load Core framework files
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

// Load Services
require_once __DIR__ . '/../app/services/WhatsAppService.php';
require_once __DIR__ . '/../app/services/AiService.php';
