<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/AiConfig_model.php';
require_once __DIR__ . '/../app/models/OtpConfig_model.php';

$aiModel = new AiConfig_model();
$aiStatus = $aiModel->getIntegrationStatus();
echo "AI Status:\n";
print_r($aiStatus);

$otpConfigModel = new OtpConfig_model();
$otpStatus = $otpConfigModel->getIntegrationStatus();
echo "\nOTP Status:\n";
print_r($otpStatus);
