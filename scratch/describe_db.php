<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query('DESCRIBE users');
$cols = $db->resultSet();
echo "--- USERS TABLE COLUMNS ---" . PHP_EOL;
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']}) - Null: {$c['Null']} - Key: {$c['Key']}" . PHP_EOL;
}

$db->query('DESCRIBE otp_logs');
$otpCols = $db->resultSet();
echo "--- OTP_LOGS TABLE COLUMNS ---" . PHP_EOL;
foreach ($otpCols as $c) {
    echo "{$c['Field']} ({$c['Type']}) - Null: {$c['Null']} - Key: {$c['Key']}" . PHP_EOL;
}

$db->query('SELECT id, username, email, phone_number, role_id FROM users');
$users = $db->resultSet();
echo "--- USERS SAMPLE ---" . PHP_EOL;
foreach ($users as $u) {
    echo "User #{$u['id']} ({$u['username']}) - Role: {$u['role_id']} - Email: {$u['email']} - Phone: '{$u['phone_number']}'" . PHP_EOL;
}
