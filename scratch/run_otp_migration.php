<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$sql = file_get_contents(__DIR__ . '/../database/create_otp_tables.sql');

// Split and execute statements
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        try {
            $db->query($stmt);
            $db->execute();
        } catch (Exception $e) {
            echo "Notice on stmt: " . $e->getMessage() . PHP_EOL;
        }
    }
}
echo "Migration SQL executed successfully!" . PHP_EOL;

// Inisialisasi default record di whatsapp_otp_configs jika belum ada
$db->query("SELECT COUNT(*) as cnt FROM whatsapp_otp_configs");
$row = $db->single();
if ($row['cnt'] == 0) {
    // Encrypt default fonnte token using AES-256
    $defaultToken = '28cchAFgoLBVcTNR5vz7';
    $encKey = defined('AI_ENCRYPTION_KEY') ? AI_ENCRYPTION_KEY : 'nNaCo_Sa_Ai_SecRet_KeY_2026_99x8';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($defaultToken, 'aes-256-cbc', hash('sha256', $encKey, true), 0, $iv);
    $storedToken = base64_encode($iv . '::' . $encrypted);

    $db->query("INSERT INTO whatsapp_otp_configs (
        provider, api_url, access_token, phone_number_id, waba_id, template_name,
        otp_expiration_minutes, resend_cooldown_seconds, max_attempts, rate_limit_per_hour,
        is_active, is_dev_mode
    ) VALUES (
        'fonnte', 'https://api.fonnte.com/send', :token, '100609349258284', '100609349258280', 'nnaco_auth_otp',
        5, 60, 5, 5,
        1, 0
    )");
    $db->bind('token', $storedToken);
    $db->execute();
    echo "Default WhatsApp OTP config seeded!" . PHP_EOL;
}

// Update demo users phone numbers
$db->query("UPDATE users SET phone_number = '6281111111111' WHERE id = 1 AND (phone_number IS NULL OR phone_number = '')");
$db->execute();
$db->query("UPDATE users SET phone_number = '6282222222222' WHERE id = 2 AND (phone_number IS NULL OR phone_number = '')");
$db->execute();
$db->query("UPDATE users SET phone_number = '6283333333333' WHERE id = 3 AND (phone_number IS NULL OR phone_number = '')");
$db->execute();
$db->query("UPDATE users SET phone_number = '6284444444444' WHERE id = 4 AND (phone_number IS NULL OR phone_number = '')");
$db->execute();
echo "Updated demo users phone numbers successfully!" . PHP_EOL;
