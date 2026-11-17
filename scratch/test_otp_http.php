<?php

echo "==========================================================" . PHP_EOL;
echo "  HTTP INTEGRATION TEST SUITE: WHATSAPP OTP AUTHENTICATION" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

function curlRequest($url, $method = 'GET', $data = [], $cookieFile = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $body,
        'redirect' => $redirectUrl,
        'error' => $err
    ];
}

$cookieCust = __DIR__ . '/cookie_cust.txt';
$cookieAdmin = __DIR__ . '/cookie_admin.txt';
$cookieOwner = __DIR__ . '/cookie_owner.txt';
@unlink($cookieCust);
@unlink($cookieAdmin);
@unlink($cookieOwner);

$baseUrl = 'http://localhost/nnaco.sa/public';

// -------------------------------------------------------------------------
// STEP 1: GET /auth/otp Page
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 1] GET /auth/otp Page" . PHP_EOL;
$res1 = curlRequest("{$baseUrl}/auth/otp", 'GET', [], $cookieCust);
assert($res1['code'] === 200, "Expected HTTP 200 on /auth/otp");
assert(strpos($res1['body'], 'Login WhatsApp OTP') !== false, "Title not found");
echo "  ✓ Status Code: {$res1['code']} OK" . PHP_EOL;
echo "  ✓ Page Title 'Login WhatsApp OTP' rendered." . PHP_EOL;

// Extract CSRF token
preg_match('/id="csrfTokenRequest"\s+value="([^"]+)"/', $res1['body'], $m);
$csrf = $m[1] ?? '';
assert(!empty($csrf), "CSRF Token must not be empty");
echo "  ✓ CSRF Token obtained: {$csrf}" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 2: POST /auth/sendOtp with Invalid Phone Format
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 2] POST /auth/sendOtp with Invalid Phone" . PHP_EOL;
$res2 = curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '12345'
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json2 = json_decode($res2['body'], true);
assert($json2['status'] === 'error', "Should fail on invalid phone");
echo "  ✓ Response: {$json2['message']}" . PHP_EOL;
echo "  ✓ Invalid phone format rejected safely." . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 3: POST /auth/sendOtp with Unregistered Phone (Auto-Register Customer)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 3] POST /auth/sendOtp with Unregistered Phone (Auto-Register)" . PHP_EOL;
$res3 = curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '087777777777'
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json3 = json_decode($res3['body'], true);
assert($json3['status'] === 'success', "Should return success for auto-registered customer");
assert(strpos($json3['message'], 'Nomor WhatsApp baru berhasil disiapkan') !== false || strpos($json3['message'], 'Kode OTP') !== false, "Auto-register message check");
echo "  ✓ Response: {$json3['message']}" . PHP_EOL;
echo "  ✓ Auto-Register for new customer phone confirmed!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 4: POST /auth/sendOtp for Registered Customer (08986817584)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 4] POST /auth/sendOtp for Registered Customer (08986817584)" . PHP_EOL;

// Reset previous challenges for this test phone to guarantee fresh cooldown
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
$dbInit = new Database();
$dbInit->query("DELETE FROM otp_logs WHERE phone_number IN ('628986817584', '6282222222222', '6281111111111')");
$dbInit->execute();

$res4 = curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '08986817584'
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json4 = json_decode($res4['body'], true);
assert($json4['status'] === 'success', "Expected OTP sent success");
echo "  ✓ Status: {$json4['status']} | Message: {$json4['message']}" . PHP_EOL;
echo "  ✓ Masked Phone: {$json4['phone_masked']} | Cooldown: {$json4['cooldown']}s" . PHP_EOL;
if (!empty($json4['csrf_token'])) $csrf = $json4['csrf_token'];

// -------------------------------------------------------------------------
// STEP 5: POST /auth/sendOtp Immediate Repeat (Cooldown Check)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 5] Immediate Repeat Request (Cooldown Check)" . PHP_EOL;
$res5 = curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '08986817584'
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json5 = json_decode($res5['body'], true);
assert($json5['status'] === 'error', "Cooldown must block repeat request");
assert(strpos($json5['message'], 'tunggu') !== false, "Cooldown message expected");
echo "  ✓ Blocked: {$json5['message']}" . PHP_EOL;
echo "  ✓ Cooldown rate-limit verified!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 6: POST /auth/verifyOtp with Wrong Code
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 6] POST /auth/verifyOtp with Wrong Code" . PHP_EOL;
$res6 = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '628986817584',
    'otp_code' => '000000'
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json6 = json_decode($res6['body'], true);
assert($json6['status'] === 'error', "Wrong code must fail");
echo "  ✓ Response: {$json6['message']}" . PHP_EOL;
echo "  ✓ Wrong code rejected and attempt tracked." . PHP_EOL;
if (!empty($json6['csrf_token'])) $csrf = $json6['csrf_token'];

// -------------------------------------------------------------------------
// STEP 7: POST /auth/verifyOtp with Valid Code for Customer
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 7] POST /auth/verifyOtp with Valid Code for Customer" . PHP_EOL;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
$db = new Database();
$db->query("SELECT id FROM otp_logs WHERE phone_number = '628986817584' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$row = $db->single();
$challengeId = $row['id'];
$knownSecret = '889911';
$bcryptHash = password_hash($knownSecret, PASSWORD_BCRYPT);
$db->query("UPDATE otp_logs SET otp_hash = :hash, attempts = 0 WHERE id = :id");
$db->bind('hash', $bcryptHash);
$db->bind('id', $challengeId);
$db->execute();

$res7 = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '628986817584',
    'otp_code' => $knownSecret
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json7 = json_decode($res7['body'], true);
assert($json7['status'] === 'success', "Valid OTP should login customer");
assert(strpos($json7['redirect'], 'nnaco.sa') !== false, "Customer redirected to store root");
echo "  ✓ Response: {$json7['message']}" . PHP_EOL;
echo "  ✓ Target Redirect: {$json7['redirect']}" . PHP_EOL;
echo "  ✓ Customer authenticated successfully!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 8: Replay Attack Test
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 8] Replay Attack Test" . PHP_EOL;
$res8 = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
    'csrf_token' => $csrf,
    'phone_number' => '628986817584',
    'otp_code' => $knownSecret
], $cookieCust, ['X-Requested-With: XMLHttpRequest']);
$json8 = json_decode($res8['body'], true);
assert($json8['status'] === 'error', "Replay must be rejected");
echo "  ✓ Replay rejected: {$json8['message']}" . PHP_EOL;
echo "  ✓ Single-Use Guarantee confirmed!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 9: Admin Login via WhatsApp OTP (Phone: 6282222222222)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 9] Admin Authentication via WhatsApp OTP (6282222222222)" . PHP_EOL;
$resAdminOtp = curlRequest("{$baseUrl}/auth/otp", 'GET', [], $cookieAdmin);
preg_match('/id="csrfTokenRequest"\s+value="([^"]+)"/', $resAdminOtp['body'], $m);
$adminCsrf = $m[1] ?? '';

// Send OTP
$resAdminSend = curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $adminCsrf,
    'phone_number' => '082222222222'
], $cookieAdmin, ['X-Requested-With: XMLHttpRequest']);

// Set known code for admin challenge
$db->query("SELECT id FROM otp_logs WHERE phone_number = '6282222222222' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$adminRow = $db->single();
$adminCode = '774411';
$adminHash = password_hash($adminCode, PASSWORD_BCRYPT);
$db->query("UPDATE otp_logs SET otp_hash = :h, attempts = 0 WHERE id = :id");
$db->bind('h', $adminHash);
$db->bind('id', $adminRow['id']);
$db->execute();

$resAdminVerify = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
    'csrf_token' => $adminCsrf,
    'phone_number' => '6282222222222',
    'otp_code' => $adminCode
], $cookieAdmin, ['X-Requested-With: XMLHttpRequest']);
$jsonAdminVerify = json_decode($resAdminVerify['body'], true);
assert($jsonAdminVerify['status'] === 'success', "Admin OTP verify must succeed");
assert(strpos($jsonAdminVerify['redirect'], '/admin') !== false, "Admin redirected to /admin");
echo "  ✓ Admin Login: {$jsonAdminVerify['message']}" . PHP_EOL;
echo "  ✓ Redirect: {$jsonAdminVerify['redirect']}" . PHP_EOL;
echo "  ✓ Role: {$jsonAdminVerify['user']['role']}" . PHP_EOL;

// Access /admin/otpConfig with Admin Session
$resAdminCfg = curlRequest("{$baseUrl}/admin/otpConfig", 'GET', [], $cookieAdmin);
assert($resAdminCfg['code'] === 200, "Admin must be allowed to access /admin/otpConfig");
assert(strpos($resAdminCfg['body'], 'Pengaturan WhatsApp OTP Gateway') !== false, "Header not found");
echo "  ✓ Admin successfully opened /admin/otpConfig (HTTP 200 OK)!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 10: Owner Login via WhatsApp OTP (Phone: 6281111111111)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 10] Owner Authentication via WhatsApp OTP (6281111111111)" . PHP_EOL;
$resOwnerOtp = curlRequest("{$baseUrl}/auth/otp", 'GET', [], $cookieOwner);
preg_match('/id="csrfTokenRequest"\s+value="([^"]+)"/', $resOwnerOtp['body'], $m);
$ownerCsrf = $m[1] ?? '';

// Send OTP
curlRequest("{$baseUrl}/auth/sendOtp", 'POST', [
    'csrf_token' => $ownerCsrf,
    'phone_number' => '081111111111'
], $cookieOwner, ['X-Requested-With: XMLHttpRequest']);

$db->query("SELECT id FROM otp_logs WHERE phone_number = '6281111111111' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$ownerRow = $db->single();
$ownerCode = '992233';
$ownerHash = password_hash($ownerCode, PASSWORD_BCRYPT);
$db->query("UPDATE otp_logs SET otp_hash = :h, attempts = 0 WHERE id = :id");
$db->bind('h', $ownerHash);
$db->bind('id', $ownerRow['id']);
$db->execute();

$resOwnerVerify = curlRequest("{$baseUrl}/auth/verifyOtp", 'POST', [
    'csrf_token' => $ownerCsrf,
    'phone_number' => '6281111111111',
    'otp_code' => $ownerCode
], $cookieOwner, ['X-Requested-With: XMLHttpRequest']);
$jsonOwnerVerify = json_decode($resOwnerVerify['body'], true);
assert($jsonOwnerVerify['status'] === 'success', "Owner OTP verify must succeed");
assert(strpos($jsonOwnerVerify['redirect'], '/owner') !== false, "Owner redirected to /owner");
echo "  ✓ Owner Login: {$jsonOwnerVerify['message']}" . PHP_EOL;
echo "  ✓ Redirect: {$jsonOwnerVerify['redirect']}" . PHP_EOL;
echo "  ✓ Role: {$jsonOwnerVerify['user']['role']}" . PHP_EOL;

// Access /owner/otpConfig with Owner Session
$resOwnerCfg = curlRequest("{$baseUrl}/owner/otpConfig", 'GET', [], $cookieOwner);
assert($resOwnerCfg['code'] === 200, "Owner must be allowed to access /owner/otpConfig");
assert(strpos($resOwnerCfg['body'], 'Pengaturan WhatsApp OTP Gateway') !== false, "Header not found");
echo "  ✓ Owner successfully opened /owner/otpConfig (HTTP 200 OK)!" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 11: Test Connection AJAX Endpoint (/otpConfig/testConnection)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 11] Test Connection AJAX Endpoint (/otpConfig/testConnection)" . PHP_EOL;
$resTestConn = curlRequest("{$baseUrl}/otpConfig/testConnection", 'POST', [
    'csrf_token' => $adminCsrf,
    'test_phone' => '8986817584'
], $cookieAdmin, ['X-Requested-With: XMLHttpRequest']);
$jsonTestConn = json_decode($resTestConn['body'], true);
assert($jsonTestConn['status'] === 'success', "Test connection must succeed");
echo "  ✓ Test Connection Status: {$jsonTestConn['status']}" . PHP_EOL;
echo "  ✓ Latency: {$jsonTestConn['latency_ms']} ms" . PHP_EOL;
echo "  ✓ Provider: {$jsonTestConn['provider']}" . PHP_EOL;
echo "  ✓ Message: {$jsonTestConn['message']}" . PHP_EOL;

// -------------------------------------------------------------------------
// STEP 12: Unauthorized Access Check (Customer accessing /admin/otpConfig)
// -------------------------------------------------------------------------
echo PHP_EOL . "[STEP 12] Unauthorized Access Check (Customer -> /admin/otpConfig)" . PHP_EOL;
$resUnauth = curlRequest("{$baseUrl}/admin/otpConfig", 'GET', [], $cookieCust);
assert($resUnauth['code'] === 403 || $resUnauth['code'] === 302 || strpos($resUnauth['body'], 'Unauthorized') !== false, "Customer must be blocked!");
echo "  ✓ Customer blocked from admin OTP config: HTTP {$resUnauth['code']}" . PHP_EOL;

// Clean up test cookies
@unlink($cookieCust);
@unlink($cookieAdmin);
@unlink($cookieOwner);

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "  ALL 12 END-TO-END HTTP TESTS PASSED WITH 100% SUCCESS!" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
