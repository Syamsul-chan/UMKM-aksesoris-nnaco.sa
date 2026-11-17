<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/OtpConfig_model.php';
require_once __DIR__ . '/../app/models/Otp_model.php';
require_once __DIR__ . '/../app/models/User_model.php';
require_once __DIR__ . '/../app/services/WhatsAppService.php';

echo "==========================================================" . PHP_EOL;
echo "  UNIT & INTEGRATION TEST SUITE: WHATSAPP OTP AUTHENTICATION" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

$db = new Database();

// --- 1. PHONE NORMALIZATION & VALIDATION ---
echo PHP_EOL . "[TEST 1] Phone Normalization & Validation" . PHP_EOL;
$tests = [
    '08986817584' => '628986817584',
    '+6281234567890' => '6281234567890',
    '812-3456-7890' => '6281234567890',
    '6281234567890' => '6281234567890'
];
foreach ($tests as $input => $expected) {
    $norm = WhatsAppService::normalizePhone($input);
    $isValid = WhatsAppService::isValidIndonesianPhone($norm);
    assert($norm === $expected, "Normalization mismatch: {$norm} != {$expected}");
    assert($isValid === true, "Valid phone marked invalid: {$norm}");
    echo "  ✓ Input '{$input}' -> '{$norm}' (Valid: YES)" . PHP_EOL;
}
assert(WhatsAppService::isValidIndonesianPhone('02112345') === false);
assert(WhatsAppService::isValidIndonesianPhone('12345') === false);
echo "  ✓ Invalid numbers correctly rejected." . PHP_EOL;

// --- 2. CONFIGURATION & TOKEN ENCRYPTION ---
echo PHP_EOL . "[TEST 2] Configuration Model & AES-256 Token Encryption" . PHP_EOL;
$configModel = new OtpConfig_model();
$cfg = $configModel->getConfig();
echo "  ✓ Provider: " . $cfg['provider'] . PHP_EOL;
echo "  ✓ API URL: " . $cfg['api_url'] . PHP_EOL;
echo "  ✓ Masked Token: " . $cfg['masked_token'] . PHP_EOL;
echo "  ✓ Plain/Decrypted Token length: " . strlen($cfg['decrypted_token']) . " chars" . PHP_EOL;
echo "  ✓ Expiration: " . $cfg['otp_expiration_minutes'] . " minutes" . PHP_EOL;
echo "  ✓ Cooldown: " . $cfg['resend_cooldown_seconds'] . " seconds" . PHP_EOL;
echo "  ✓ Max Attempts: " . $cfg['max_attempts'] . " attempts" . PHP_EOL;

$sampleSecret = "my_super_secret_wa_token_12345";
$encrypted = $configModel->encryptToken($sampleSecret);
$decrypted = $configModel->decryptToken($encrypted);
assert($decrypted === $sampleSecret, "AES-256 Encryption/Decryption mismatch!");
echo "  ✓ AES-256-CBC token encryption/decryption verified!" . PHP_EOL;

// --- 3. OTP CHALLENGE CREATION & HASH SECURITY ---
echo PHP_EOL . "[TEST 3] Cryptographic OTP Generation & Zero-Plaintext Hash" . PHP_EOL;
$otpModel = new Otp_model();
$testPhone = '6289999999999';

$challenge = $otpModel->createChallenge($testPhone, null, 5, '127.0.0.1', 'PHPUnit-CLI');
assert(strlen($challenge['plain_otp']) === 6, "OTP length must be 6 digits!");
assert(ctype_digit($challenge['plain_otp']), "OTP must be numeric!");
echo "  ✓ Generated OTP: " . $challenge['plain_otp'] . " (Length: 6 digits)" . PHP_EOL;

// Check database to ensure plaintext OTP is NEVER saved
$db->query("SELECT otp_hash, is_used, attempts FROM otp_logs WHERE id = :id");
$db->bind('id', $challenge['challenge_id']);
$dbRecord = $db->single();
assert(str_starts_with($dbRecord['otp_hash'], '$2y$'), "OTP in DB must be BCRYPT hash!");
assert(strpos($dbRecord['otp_hash'], $challenge['plain_otp']) === false, "CRITICAL: Plaintext OTP found in hash!");
echo "  ✓ Database stores BCRYPT hash: " . substr($dbRecord['otp_hash'], 0, 25) . "..." . PHP_EOL;
echo "  ✓ Zero-Plaintext security rule verified!" . PHP_EOL;

// --- 4. COOLDOWN LOGIC ---
echo PHP_EOL . "[TEST 4] Resend Cooldown Enforcement" . PHP_EOL;
$cdCheck = $otpModel->checkCooldown($testPhone, 60);
assert($cdCheck['in_cooldown'] === true, "Must be in cooldown right after creation!");
assert($cdCheck['remaining_seconds'] > 50, "Cooldown remaining must be > 50s!");
echo "  ✓ Cooldown active: {$cdCheck['remaining_seconds']}s remaining. Spam resend blocked." . PHP_EOL;

// --- 5. WRONG ATTEMPTS & BRUTE-FORCE INVALIDATION ---
echo PHP_EOL . "[TEST 5] Brute-Force Mitigation & Attempt Counter" . PHP_EOL;
$maxAttempts = 3; // Test with 3 max attempts
for ($i = 1; $i <= $maxAttempts; $i++) {
    $res = $otpModel->verifyOtp($testPhone, '000000', $maxAttempts);
    assert($res['success'] === false, "Wrong OTP must fail!");
    echo "  ✓ Attempt #{$i} with wrong OTP rejected: {$res['message']}" . PHP_EOL;
}

// Next attempt should say attempts exceeded
$resExceeded = $otpModel->verifyOtp($testPhone, $challenge['plain_otp'], $maxAttempts);
assert($resExceeded['success'] === false, "Must be locked out after max attempts!");
echo "  ✓ Challenge invalidated after max attempts exceeded!" . PHP_EOL;

// --- 6. SUCCESSFUL VERIFICATION & SINGLE-USE GUARANTEE ---
echo PHP_EOL . "[TEST 6] Successful Verification & Single-Use Replay Protection" . PHP_EOL;
// Use user Erlang's phone: 628986817584
$userPhone = '628986817584';
$userChallenge = $otpModel->createChallenge($userPhone, 5, 5, '127.0.0.1', 'CLI-Tester');
$validOtp = $userChallenge['plain_otp'];

$verifySuccess = $otpModel->verifyOtp($userPhone, $validOtp, 5);
assert($verifySuccess['success'] === true, "Valid OTP must succeed!");
assert(!empty($verifySuccess['user']), "Associated user record must be returned!");
assert($verifySuccess['user']['email'] === 'Erlangucum@gmail.com', "User mismatch!");
echo "  ✓ OTP verified successfully for user: " . $verifySuccess['user']['username'] . " (Role: " . $verifySuccess['user']['role_name'] . ")" . PHP_EOL;

// Try to use the EXACT SAME OTP again (Replay Attack)
$replayAttempt = $otpModel->verifyOtp($userPhone, $validOtp, 5);
assert($replayAttempt['success'] === false, "Replay attack must fail!");
echo "  ✓ Replay attempt immediately rejected: {$replayAttempt['message']}" . PHP_EOL;
echo "  ✓ Single-Use Guarantee verified!" . PHP_EOL;

// --- 7. WHATSAPP GATEWAY TEST (DEV MOCK & FONNTE) ---
echo PHP_EOL . "[TEST 7] WhatsApp Service Delivery & Test Connection" . PHP_EOL;
$waService = new WhatsAppService($cfg);
$sendRes = $waService->sendOtp($userPhone, '123456', 5);
assert($sendRes['success'] === true, "Send OTP should succeed!");
echo "  ✓ Send OTP status: " . ($sendRes['success'] ? 'SUCCESS' : 'FAILED') . " (Provider: {$sendRes['provider']})" . PHP_EOL;

$testConn = $waService->testConnection($userPhone);
echo "  ✓ Gateway Test Connection: " . ($testConn['success'] ? 'SUCCESS' : 'FAILED') . " (Latency: {$testConn['latency_ms']} ms)" . PHP_EOL;
echo "  ✓ Response message: " . $testConn['message'] . PHP_EOL;

// --- 8. AUDIT LOGGING ---
echo PHP_EOL . "[TEST 8] Audit Trail Logging" . PHP_EOL;
$otpModel->logAction(5, '6289••••7584', 'VERIFY_SUCCESS', 'SUCCESS', 'Unit test verification completed.', '127.0.0.1');
$logs = $otpModel->getAuditLogs(3);
assert(count($logs) > 0, "Audit logs must not be empty!");
echo "  ✓ Latest Audit Log: [{$logs[0]['action']}] by {$logs[0]['phone_masked']} -> {$logs[0]['status']}" . PHP_EOL;

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "  ALL 8 UNIT & INTEGRATION TESTS PASSED WITH 100% SUCCESS!" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
