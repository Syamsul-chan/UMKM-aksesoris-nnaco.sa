<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';

$db = new Database();
$db->query("SHOW TABLES");
$tables = array_map(function($r) { return array_values($r)[0]; }, $db->resultSet());
echo "Tables:\n" . implode("\n", $tables) . "\n\n";

$db->query("SELECT * FROM ai_configurations");
$ai = $db->resultSet();
echo "AI Configurations count: " . count($ai) . "\n";
foreach ($ai as $a) {
    echo "ID: {$a['id']}, Name: {$a['name']}, Provider: {$a['provider']}, Model: {$a['model_name']}, Primary: {$a['is_primary']}, Active: {$a['is_active']}\n";
}

$db->query("SELECT * FROM whatsapp_otp_configs");
$wa = $db->resultSet();
echo "\nWhatsApp Configs count: " . count($wa) . "\n";
foreach ($wa as $w) {
    echo "ID: {$w['id']}, Provider: {$w['provider']}, Active: {$w['is_active']}, DevMode: {$w['is_dev_mode']}\n";
}

$db->query("SELECT COUNT(*) as total FROM ai_config_logs");
$aiLogs = $db->single()['total'];
echo "\nAI Config Logs count: {$aiLogs}\n";

$db->query("SELECT COUNT(*) as total FROM otp_audit_logs");
$otpLogs = $db->single()['total'];
echo "OTP Audit Logs count: {$otpLogs}\n";

$db->query("SELECT * FROM ai_config_logs ORDER BY id DESC LIMIT 3");
echo "\nLatest AI Config Logs:\n";
print_r($db->resultSet());

$db->query("SELECT * FROM otp_audit_logs ORDER BY id DESC LIMIT 3");
echo "\nLatest OTP Audit Logs:\n";
print_r($db->resultSet());
