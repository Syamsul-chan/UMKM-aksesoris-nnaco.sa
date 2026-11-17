<?php
require_once 'c:/xampp/htdocs/nnaco.sa/config/config.php';
require_once 'c:/xampp/htdocs/nnaco.sa/core/Database.php';

$baseUrl = 'http://localhost/nnaco.sa/public';
$cookieFile = tempnam(sys_get_temp_dir(), 'test_forgot_cookie_');

function curlReq($url, $method = 'GET', $data = [], $cookie = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
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

echo "==========================================================\n";
echo "  TEST SUITE: FITUR LUPA KATA SANDI (FORGOT PASSWORD)\n";
echo "==========================================================\n\n";

// 1. Verifikasi Halaman Login memiliki Link Lupa Kata Sandi
echo "[TEST 1] GET /auth/login - Cek Link 'Lupa Kata Sandi'\n";
$resLogin = curlReq("{$baseUrl}/auth/login", 'GET', [], $cookieFile);
assert($resLogin['code'] === 200, "Login page HTTP 200");
assert(strpos($resLogin['body'], '/auth/forgotPassword') !== false, "Must contain link to /auth/forgotPassword");
assert(strpos($resLogin['body'], 'Lupa kata sandi?') !== false, "Must contain text 'Lupa kata sandi?'");
echo "  ✓ HTTP 200 OK\n";
echo "  ✓ Link /auth/forgotPassword ditemukan di halaman login!\n\n";

// 2. Verifikasi Halaman Forgot Password
echo "[TEST 2] GET /auth/forgotPassword - Cek UI Halaman Reset\n";
$resForgot = curlReq("{$baseUrl}/auth/forgotPassword", 'GET', [], $cookieFile);
assert($resForgot['code'] === 200, "Forgot password page HTTP 200");
assert(strpos($resForgot['body'], 'Lupa Kata Sandi') !== false, "Must contain title");
assert(strpos($resForgot['body'], 'identityInput') !== false, "Must contain identity input field");
preg_match('/id="csrfTokenRequest"\s+value="([^"]+)"/', $resForgot['body'], $m);
$csrf = $m[1] ?? '';
assert(!empty($csrf), "Must contain valid CSRF token");
echo "  ✓ HTTP 200 OK\n";
echo "  ✓ CSRF Token diperoleh: {$csrf}\n\n";

// 3. Request Reset dengan Identitas Tidak Terdaftar
echo "[TEST 3] POST /auth/sendResetCode dengan email tidak terdaftar\n";
$resUnreg = curlReq("{$baseUrl}/auth/sendResetCode", 'POST', [
    'csrf_token' => $csrf,
    'identity' => 'user_palsu_999@nnaco.sa'
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonUnreg = json_decode($resUnreg['body'], true);
assert($jsonUnreg['status'] === 'error', "Should fail for unregistered user");
assert(strpos($jsonUnreg['message'], 'tidak ditemukan') !== false, "Should indicate not found");
echo "  ✓ Ditolak secara aman: {$jsonUnreg['message']}\n\n";

// 4. Request Reset dengan Akun Terdaftar (Budi Customer: budi@gmail.com / 6284444444444)
echo "[TEST 4] POST /auth/sendResetCode dengan akun valid (budi@gmail.com)\n";
// Bersihkan riwayat lama untuk budi
$db = new Database();
$db->query("DELETE FROM password_resets WHERE identity = 'budi@gmail.com'");
$db->execute();

$resValid = curlReq("{$baseUrl}/auth/sendResetCode", 'POST', [
    'csrf_token' => $csrf,
    'identity' => 'budi@gmail.com'
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonValid = json_decode($resValid['body'], true);
assert($jsonValid['status'] === 'success', "Should succeed for valid user");
assert(!empty($jsonValid['expires_in']), "Must have expires_in");
echo "  ✓ Status: success\n";
echo "  ✓ Pesan: {$jsonValid['message']}\n";
echo "  ✓ Destinasi: {$jsonValid['dest_desc']}\n";

// Cek di database
$db->query("SELECT * FROM password_resets WHERE identity = 'budi@gmail.com' AND is_used = 0 ORDER BY id DESC LIMIT 1");
$rowReset = $db->single();
assert(!empty($rowReset), "Database record must be created");
assert(str_starts_with($rowReset['token_hash'], '$2y$'), "Token must be hashed with BCRYPT");
echo "  ✓ Database token tersimpan aman dengan hash BCRYPT!\n\n";

// 5. Cek Cooldown (Spam Protection)
echo "[TEST 5] POST /auth/sendResetCode Ulang Langsung (Cooldown Check)\n";
$resCooldown = curlReq("{$baseUrl}/auth/sendResetCode", 'POST', [
    'csrf_token' => $jsonValid['csrf_token'],
    'identity' => 'budi@gmail.com'
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonCooldown = json_decode($resCooldown['body'], true);
assert($jsonCooldown['status'] === 'error', "Should be blocked by cooldown");
assert(strpos($jsonCooldown['message'], 'detik') !== false, "Must mention remaining seconds");
echo "  ✓ Terproteksi Cooldown: {$jsonCooldown['message']}\n\n";

// 6. Submit Reset dengan Kode Salah
echo "[TEST 6] POST /auth/submitResetPassword dengan kode salah\n";
$resWrong = curlReq("{$baseUrl}/auth/submitResetPassword", 'POST', [
    'csrf_token' => $jsonCooldown['csrf_token'],
    'identity' => 'budi@gmail.com',
    'reset_code' => '000000',
    'password' => 'passwordBaru123',
    'confirm_password' => 'passwordBaru123'
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonWrong = json_decode($resWrong['body'], true);
assert($jsonWrong['status'] === 'error', "Should fail on wrong code");
assert(strpos($jsonWrong['message'], 'salah') !== false, "Must indicate wrong code");
echo "  ✓ Percobaan salah ditolak: {$jsonWrong['message']}\n\n";

// 7. Submit Reset dengan Kode Benar
echo "[TEST 7] POST /auth/submitResetPassword dengan kode yang benar\n";
// Update hash ke known code '654321' untuk pengujian
$knownCode = '654321';
$db->query("UPDATE password_resets SET token_hash = :h, attempts = 0 WHERE id = :id");
$db->bind('h', password_hash($knownCode, PASSWORD_BCRYPT));
$db->bind('id', $rowReset['id']);
$db->execute();

$newPassTest = 'BudiBaru2026!';
$resSubmit = curlReq("{$baseUrl}/auth/submitResetPassword", 'POST', [
    'csrf_token' => $jsonWrong['csrf_token'],
    'identity' => 'budi@gmail.com',
    'reset_code' => $knownCode,
    'password' => $newPassTest,
    'confirm_password' => $newPassTest
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonSubmit = json_decode($resSubmit['body'], true);
assert($jsonSubmit['status'] === 'success', "Reset password must succeed");
assert($jsonSubmit['redirect'] === "{$baseUrl}/auth/login", "Redirects to login");
echo "  ✓ Status: success\n";
echo "  ✓ Pesan: {$jsonSubmit['message']}\n";
echo "  ✓ Redirect: {$jsonSubmit['redirect']}\n\n";

// 8. Verifikasi Database & Single-Use Protection
echo "[TEST 8] Verifikasi Single-Use Replay Protection & Database Hash\n";
// Pastikan token sudah ditandai used
$db->query("SELECT is_used FROM password_resets WHERE id = :id");
$db->bind('id', $rowReset['id']);
$checkUsed = $db->single();
assert((int)$checkUsed['is_used'] === 1, "Token must be marked as used");

// Coba gunakan token yang sama lagi
$resReplay = curlReq("{$baseUrl}/auth/submitResetPassword", 'POST', [
    'csrf_token' => $csrf,
    'identity' => 'budi@gmail.com',
    'reset_code' => $knownCode,
    'password' => 'LainLagi123',
    'confirm_password' => 'LainLagi123'
], $cookieFile, ['X-Requested-With: XMLHttpRequest']);
$jsonReplay = json_decode($resReplay['body'], true);
assert($jsonReplay['status'] === 'error', "Replay must be rejected");
echo "  ✓ Replay attack langsung ditolak (Single-Use Token terjamin)!\n\n";

// 9. Verifikasi Login dengan Password Baru
echo "[TEST 9] Coba Login dengan Password Baru ($newPassTest)\n";
// Login via POST /auth/login
$resLoginTest = curlReq("{$baseUrl}/auth/login", 'POST', [
    'csrf_token' => $csrf,
    'email' => 'budi@gmail.com',
    'password' => $newPassTest
], $cookieFile);
assert($resLoginTest['code'] === 302, "Successful login should redirect (HTTP 302)");
echo "  ✓ Login dengan kata sandi baru BERHASIL (HTTP 302 Redirect)!\n\n";

// Kembalikan password budi ke password demo awal
$db->query("UPDATE users SET password = :p WHERE email = 'budi@gmail.com'");
$db->bind('p', password_hash('password', PASSWORD_BCRYPT));
$db->execute();
echo "  ✓ Password Budi dikembalikan ke default untuk menjaga kestabilan data demo.\n";

echo "\n==========================================================\n";
echo "  ALL 9 TESTS FOR FITUR LUPA KATA SANDI PASSED (100%)!\n";
echo "==========================================================\n";
