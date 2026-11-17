<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

$baseUrl = 'http://localhost/nnaco.sa/public';
$cookieAdmin = tempnam(sys_get_temp_dir(), 'admin_dash_cookie_');
$cookieOwner = tempnam(sys_get_temp_dir(), 'owner_dash_cookie_');
$cookieOp = tempnam(sys_get_temp_dir(), 'op_dash_cookie_');

function curlRequest($url, $method = 'GET', $data = [], $cookieFile = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'redirect' => $redirect];
}

function loginUserViaOtp($phone, $cookieFile, $baseUrl) {
    $db = new Database();
    $db->query("DELETE FROM otp_logs WHERE phone_number = :p");
    $db->bind('p', $phone);
    $db->execute();

    // 1. Dapatkan CSRF
    $resOtpPage = curlRequest("{$baseUrl}/auth/otp", 'GET', [], $cookieFile);
    preg_match('/id="csrfTokenRequest"\s+value="([^"]+)"/', $resOtpPage['body'], $m);
    $csrf = $m[1] ?? '';

    // 2. Kirim OTP
    curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
        'csrf_token' => $csrf,
        'phone_number' => $phone
    ], $cookieFile, ['X-Requested-With: XMLHttpRequest']);

    // 3. Set known secret OTP
    $knownCode = '123456';
    $db->query("SELECT id FROM otp_logs WHERE phone_number = :phone AND is_used = 0 ORDER BY id DESC LIMIT 1");
    $db->bind('phone', $phone);
    $row = $db->single();
    if ($row) {
        $db->query("UPDATE otp_logs SET otp_hash = :h, attempts = 0 WHERE id = :id");
        $db->bind('h', password_hash($knownCode, PASSWORD_BCRYPT));
        $db->bind('id', $row['id']);
        $db->execute();
    }

    // 4. Verifikasi OTP
    $resVerify = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
        'csrf_token' => $csrf,
        'phone_number' => $phone,
        'otp_code' => $knownCode
    ], $cookieFile, ['X-Requested-With: XMLHttpRequest']);

    return json_decode($resVerify['body'], true);
}

echo "==========================================================\n";
echo "  HTTP DASHBOARD TESTS FOR ADMIN & OWNER\n";
echo "==========================================================\n\n";

// 1. Admin Login & View Dashboard
$resAdminLogin = loginUserViaOtp('6282222222222', $cookieAdmin, $baseUrl);
echo "1. Admin Login Status: " . ($resAdminLogin['status'] ?? 'fail') . "\n";

$resAdminDash = curlRequest("{$baseUrl}/admin", 'GET', [], $cookieAdmin);
echo "   GET /admin HTTP Code: " . $resAdminDash['code'] . "\n";
echo "   Contains '⚙️ Pengaturan Sistem': " . (strpos($resAdminDash['body'], 'Pengaturan Sistem') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'Model AI:': " . (strpos($resAdminDash['body'], 'Model AI:') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'WhatsApp OTP:': " . (strpos($resAdminDash['body'], 'WhatsApp OTP:') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains Link to '/admin/aiConfig': " . (strpos($resAdminDash['body'], '/admin/aiConfig') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains Link to '/admin/otpConfig': " . (strpos($resAdminDash['body'], '/admin/otpConfig') !== false ? 'YES' : 'NO') . "\n";

// 2. Owner Login & View Dashboard
$resOwnerLogin = loginUserViaOtp('6281111111111', $cookieOwner, $baseUrl);
echo "\n2. Owner Login Status: " . ($resOwnerLogin['status'] ?? 'fail') . "\n";

$resOwnerDash = curlRequest("{$baseUrl}/owner", 'GET', [], $cookieOwner);
echo "   GET /owner HTTP Code: " . $resOwnerDash['code'] . "\n";
echo "   Contains 'Ringkasan Eksekutif AI & Layanan OTP': " . (strpos($resOwnerDash['body'], 'Ringkasan Eksekutif AI & Layanan OTP') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'MODEL AI AKTIF': " . (strpos($resOwnerDash['body'], 'MODEL AI AKTIF') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'CUSTOMER ASSISTANT': " . (strpos($resOwnerDash['body'], 'CUSTOMER ASSISTANT') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'BUSINESS INSIGHT': " . (strpos($resOwnerDash['body'], 'BUSINESS INSIGHT') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains 'OTP WHATSAPP': " . (strpos($resOwnerDash['body'], 'OTP WHATSAPP') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains Link to '/owner/aiConfig': " . (strpos($resOwnerDash['body'], '/owner/aiConfig') !== false ? 'YES' : 'NO') . "\n";
echo "   Contains Link to '/owner/otpConfig': " . (strpos($resOwnerDash['body'], '/owner/otpConfig') !== false ? 'YES' : 'NO') . "\n";

// 3. Operator Access Test (Must NOT see Pengaturan Sistem, and 403 on aiConfig)
$resOpLogin = loginUserViaOtp('6283333333333', $cookieOp, $baseUrl);
echo "\n3. Operator Login Status: " . ($resOpLogin['status'] ?? 'fail') . "\n";

$resOpDash = curlRequest("{$baseUrl}/operator", 'GET', [], $cookieOp);
echo "   GET /operator HTTP Code: " . $resOpDash['code'] . "\n";
echo "   Operator sees 'Pengaturan Sistem': " . (strpos($resOpDash['body'], 'Pengaturan Sistem') !== false ? 'YES (UNEXPECTED)' : 'NO (CORRECT: HIDDEN)') . "\n";

$resOpAi = curlRequest("{$baseUrl}/admin/aiConfig", 'GET', [], $cookieOp);
echo "   Operator GET /admin/aiConfig HTTP Code: " . $resOpAi['code'] . " (Expected: 403)\n";

$resOpOtp = curlRequest("{$baseUrl}/admin/otpConfig", 'GET', [], $cookieOp);
echo "   Operator GET /admin/otpConfig HTTP Code: " . $resOpOtp['code'] . " (Expected: 403)\n";

echo "\nALL HTTP DASHBOARD VERIFICATIONS COMPLETED!\n";
