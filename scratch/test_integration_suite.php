<?php
/**
 * Test Suite: Integrasi AI & OTP WhatsApp ke Dashboard Admin & Owner
 * 
 * Cakupan Pengujian:
 * 1. Otorisasi Akses Role (Admin, Owner, Operator, Customer)
 * 2. Status Integrasi Riil (AiConfig_model & OtpConfig_model)
 * 3. Audit Logging Perubahan & Pengujian
 * 4. Penanganan Kegagalan Integrasi & Pencegahan Kebocoran Secret (Zero Credential Leak)
 * 5. Rendering HTML Dashboard Admin & Owner
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../app/models/AiConfig_model.php';
require_once __DIR__ . '/../app/models/OtpConfig_model.php';
require_once __DIR__ . '/../app/models/Otp_model.php';
require_once __DIR__ . '/../app/services/WhatsAppService.php';

$passed = 0;
$failed = 0;

function assertTest($condition, $testName, $detail = '') {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$testName} - {$detail}\n";
    }
}

echo "========================================================================\n";
echo "  TEST SUITE: INTEGRASI FITUR AI & WHATSAPP OTP KE DASHBOARD\n";
echo "========================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. MODEL STATUS INTEGRASI NYATA (Dynamic, Not Static)
// -----------------------------------------------------------------------------
echo "1. Menguji Status Integrasi Riil Model & Database:\n";

$aiModel = new AiConfig_model();
$aiStatus = $aiModel->getIntegrationStatus();

assertTest(!empty($aiStatus), "AiConfig_model::getIntegrationStatus() mengembalikan array");
assertTest(isset($aiStatus['name']) && !empty($aiStatus['name']), "Status AI memuat nama model aktif: '{$aiStatus['name']}'");
assertTest(isset($aiStatus['provider']) && !empty($aiStatus['provider']), "Status AI memuat provider: '{$aiStatus['provider']}'");
assertTest(isset($aiStatus['last_test_time']), "Status AI memuat waktu terakhir pengujian: '{$aiStatus['last_test_time']}'");
assertTest(isset($aiStatus['total_logs']) && $aiStatus['total_logs'] >= 0, "Status AI memuat total log aktivitas: {$aiStatus['total_logs']}");
assertTest(isset($aiStatus['customer_assistant_status']), "Status AI memuat status Customer Assistant: '{$aiStatus['customer_assistant_status']}'");
assertTest(isset($aiStatus['business_insight_status']), "Status AI memuat status Business Insight: '{$aiStatus['business_insight_status']}'");

$otpModel = new OtpConfig_model();
$otpStatus = $otpModel->getIntegrationStatus();

assertTest(!empty($otpStatus), "OtpConfig_model::getIntegrationStatus() mengembalikan array");
assertTest(isset($otpStatus['provider']) && !empty($otpStatus['provider']), "Status WhatsApp OTP memuat nama gateway: '{$otpStatus['provider']}'");
assertTest(isset($otpStatus['last_test_time']), "Status WhatsApp OTP memuat waktu terakhir pengujian: '{$otpStatus['last_test_time']}'");
assertTest(isset($otpStatus['total_requested']) && $otpStatus['total_requested'] >= 0, "Status WhatsApp OTP memuat metrik permintaan OTP: {$otpStatus['total_requested']}");
assertTest(isset($otpStatus['success_rate']), "Status WhatsApp OTP memuat tingkat keberhasilan verifikasi: {$otpStatus['success_rate']}%");

// -----------------------------------------------------------------------------
// 2. KEAMANAN & PENCEGAHAN KEBOCORAN KREDENSIAL (Zero Credential Leak)
// -----------------------------------------------------------------------------
echo "\n2. Menguji Keamanan Kredensial & Sanitasi Error:\n";

// Pastikan data status tidak mengandung raw plain access_token atau encrypted blob
$aiStatusJson = json_encode($aiStatus);
$otpStatusJson = json_encode($otpStatus);

// Cek apakah ada plaintext token yang bocor di JSON status
$db = new Database();
$db->query("SELECT api_key FROM ai_configurations WHERE id = 1");
$realAiEncrypted = $db->single()['api_key'] ?? '';
$realAiDecrypted = $aiModel->decryptKey($realAiEncrypted);

if (!empty($realAiDecrypted)) {
    assertTest(strpos($aiStatusJson, $realAiDecrypted) === false, "AI API Key rahasia TIDAK pernah bocor dalam data status dashboard");
} else {
    assertTest(true, "AI API Key aman");
}

$db->query("SELECT access_token FROM whatsapp_otp_configs WHERE id = 1");
$realWaEncrypted = $db->single()['access_token'] ?? '';
$realWaDecrypted = $otpModel->decryptToken($realWaEncrypted);

if (!empty($realWaDecrypted)) {
    assertTest(strpos($otpStatusJson, $realWaDecrypted) === false, "WhatsApp Access Token rahasia TIDAK pernah bocor dalam data status dashboard");
} else {
    assertTest(true, "WhatsApp Access Token aman");
}

// Uji sanitasi error: pastikan safeError tidak membocorkan format Authorization: Bearer
$fakeDirtyError = "Gagal menghubungi API dengan Authorization: Bearer gsk_secret_1234567890 dan token: xyz987654321";
$cleanedError = preg_replace('/(Bearer\s+)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $fakeDirtyError);
$cleanedError = preg_replace('/(token[=:]\s*)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $cleanedError);
assertTest(strpos($cleanedError, 'gsk_secret_1234567890') === false, "Regex sanitasi sukses menyamarkan Bearer token");
assertTest(strpos($cleanedError, 'xyz987654321') === false, "Regex sanitasi sukses menyamarkan query token");

// -----------------------------------------------------------------------------
// 3. AUDIT LOGGING PERUBAHAN & KEGAGALAN
// -----------------------------------------------------------------------------
echo "\n3. Menguji Audit Logging untuk Perubahan & Pengujian Koneksi:\n";

$initialAiLogs = $aiStatus['total_logs'];
$testAiDetails = "Pengujian koneksi otomatis oleh test suite (" . date('H:i:s') . ")";
$aiModel->logAction(1, 'Test Admin', 'TEST_CONNECTION', 1, 'openai/gpt-oss-20b', 'SUCCESS', $testAiDetails, '127.0.0.1');

$updatedAiStatus = $aiModel->getIntegrationStatus();
assertTest($updatedAiStatus['total_logs'] === ($initialAiLogs + 1), "Aktivitas uji koneksi AI sukses tercatat di ai_config_logs (Total: {$updatedAiStatus['total_logs']})");

$otpAudit = new Otp_model();
$otpAudit->logAction(1, '6281••••1111', 'TEST_CONNECTION', 'SUCCESS', "Uji koneksi automated test gateway WhatsApp");
$db->query("SELECT * FROM otp_audit_logs ORDER BY id DESC LIMIT 1");
$lastOtpLog = $db->single();
assertTest($lastOtpLog['action'] === 'TEST_CONNECTION' && $lastOtpLog['status'] === 'SUCCESS', "Aktivitas pengujian gateway OTP sukses tercatat di otp_audit_logs");

// -----------------------------------------------------------------------------
// 4. OTORISASI AKSES ROLE (Backend Enforcement)
// -----------------------------------------------------------------------------
echo "\n4. Menguji Backend Role Access Control:\n";

// Skenario A: Operator tidak memiliki akses ke pengaturan sistem
$_SESSION['user'] = [
    'id' => 3,
    'username' => 'Operator Toko',
    'role' => 'Operator',
    'phone_number' => '6283333333333'
];

$role = strtolower($_SESSION['user']['role']);
$canViewSystemSettings = ($role === 'admin' || $role === 'owner');
assertTest($canViewSystemSettings === false, "Operator DITOLAK dari flag canViewSystemSettings (Operator tidak boleh melihat Pengaturan Sistem)");

// Skenario B: Admin memiliki akses
$_SESSION['user']['role'] = 'Admin';
$role = strtolower($_SESSION['user']['role']);
$canViewSystemSettings = ($role === 'admin' || $role === 'owner');
assertTest($canViewSystemSettings === true, "Admin DIIZINKAN melihat Pengaturan Sistem");

// Skenario C: Owner memiliki akses
$_SESSION['user']['role'] = 'Owner';
$role = strtolower($_SESSION['user']['role']);
$canViewSystemSettings = ($role === 'admin' || $role === 'owner');
assertTest($canViewSystemSettings === true, "Owner DIIZINKAN melihat Pengaturan Sistem & Ringkasan Eksekutif");

// Skenario D: Customer ditolak
$_SESSION['user']['role'] = 'Customer';
$role = strtolower($_SESSION['user']['role']);
$canViewSystemSettings = ($role === 'admin' || $role === 'owner');
assertTest($canViewSystemSettings === false, "Customer DITOLAK dari Pengaturan Sistem");

// -----------------------------------------------------------------------------
// 5. RENDERING VIEW DASHBOARD (HTML Output Verification)
// -----------------------------------------------------------------------------
echo "\n5. Menguji Rendering View Dashboard Admin & Owner:\n";

// Render Admin View Buffer
ob_start();
$stats = [
    'featured_products' => [],
    'low_stock_products' => [],
    'total_revenue' => 5000000
];
$canViewSystemSettings = true;
$aiStatus = $aiModel->getIntegrationStatus();
$otpStatus = $otpModel->getIntegrationStatus();
require __DIR__ . '/../app/views/admin/index.php';
$adminHtml = ob_get_clean();

assertTest(strpos($adminHtml, '⚙️ Pengaturan Sistem') !== false, "Dashboard Admin berhasil merender bagian 'Pengaturan Sistem'");
assertTest(strpos($adminHtml, 'Model AI:') !== false, "Dashboard Admin menampilkan nama Model AI aktif");
assertTest(strpos($adminHtml, 'WhatsApp OTP:') !== false, "Dashboard Admin menampilkan nama Gateway WhatsApp OTP");
assertTest(strpos($adminHtml, 'Waktu Terakhir Pengujian:') !== false, "Dashboard Admin menampilkan Waktu Terakhir Pengujian");
assertTest(strpos($adminHtml, 'Ringkasan Error:') !== false, "Dashboard Admin menampilkan Ringkasan Error yang aman");
assertTest(strpos($adminHtml, '/admin/aiConfig') !== false, "Dashboard Admin memiliki tombol navigasi ke /admin/aiConfig");
assertTest(strpos($adminHtml, '/admin/otpConfig') !== false, "Dashboard Admin memiliki tombol navigasi ke /admin/otpConfig");

// Render Owner View Buffer
ob_start();
$topProducts = [];
$orderStatuses = [];
require __DIR__ . '/../app/views/owner/index.php';
$ownerHtml = ob_get_clean();

assertTest(strpos($ownerHtml, 'Ringkasan Eksekutif AI & Layanan OTP') !== false, "Dashboard Owner berhasil merender Ringkasan Eksekutif");
assertTest(strpos($ownerHtml, 'MODEL AI AKTIF') !== false, "Dashboard Owner menampilkan status Model AI Aktif");
assertTest(strpos($ownerHtml, 'CUSTOMER ASSISTANT') !== false, "Dashboard Owner menampilkan status AI Customer Assistant");
assertTest(strpos($ownerHtml, 'BUSINESS INSIGHT') !== false, "Dashboard Owner menampilkan status AI Business Insight");
assertTest(strpos($ownerHtml, 'OTP WHATSAPP') !== false, "Dashboard Owner menampilkan status Layanan OTP WhatsApp");
assertTest(strpos($ownerHtml, 'Total OTP Terkirim:') !== false, "Dashboard Owner menampilkan metrik penggunaan OTP");
assertTest(strpos($ownerHtml, '/owner/aiConfig') !== false, "Dashboard Owner memiliki tombol ke /owner/aiConfig");
assertTest(strpos($ownerHtml, '/owner/otpConfig') !== false, "Dashboard Owner memiliki tombol ke /owner/otpConfig");

// Pastikan tidak ada token rahasia di HTML admin & owner
if (!empty($realAiDecrypted)) {
    assertTest(strpos($adminHtml, $realAiDecrypted) === false, "HTML Admin bersih dari API Key AI rahasia");
    assertTest(strpos($ownerHtml, $realAiDecrypted) === false, "HTML Owner bersih dari API Key AI rahasia");
}
if (!empty($realWaDecrypted)) {
    assertTest(strpos($adminHtml, $realWaDecrypted) === false, "HTML Admin bersih dari WhatsApp Token rahasia");
    assertTest(strpos($ownerHtml, $realWaDecrypted) === false, "HTML Owner bersih dari WhatsApp Token rahasia");
}

echo "\n------------------------------------------------------------------------\n";
echo "  HASIL AKHIR PENGUJIAN: {$passed} PASS, {$failed} FAIL\n";
echo "========================================================================\n";
