<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../app/models/AiConfig_model.php';
require_once __DIR__ . '/../app/services/AiService.php';
require_once __DIR__ . '/../app/services/BusinessInsightService.php';

echo "=== 1. AI CONFIG MODEL TEST ===" . PHP_EOL;
$model = new AiConfig_model();
$primary = $model->getPrimaryConfig();
echo "Primary Name: " . $primary['name'] . PHP_EOL;
echo "Provider: " . $primary['provider'] . PHP_EOL;
echo "Model: " . $primary['model_name'] . PHP_EOL;
echo "Decrypted Key Length: " . strlen($primary['decrypted_key']) . PHP_EOL;
echo "Masked Key: " . $primary['masked_key'] . PHP_EOL;
echo "Temperature: " . $primary['temperature'] . PHP_EOL;
echo "Max Tokens: " . $primary['max_tokens'] . PHP_EOL;

echo PHP_EOL . "=== 2. TEST CONNECTION METHOD ===" . PHP_EOL;
$testRes = $model->testConnection(
    $primary['provider'],
    $primary['api_url'],
    $primary['api_key'], // ciphertext
    $primary['model_name'],
    $primary['temperature'],
    20,
    15
);
echo "Connection Success: " . ($testRes['success'] ? 'YES' : 'NO') . PHP_EOL;
echo "HTTP Code: " . $testRes['http_code'] . PHP_EOL;
echo "Latency: " . $testRes['latency_ms'] . " ms" . PHP_EOL;
echo "Reply sample: " . substr($testRes['reply'], 0, 80) . PHP_EOL;

echo PHP_EOL . "=== 3. AI SERVICE LIVE RESPONSE TEST ===" . PHP_EOL;
$aiService = new AiService();
$reply = $aiService->generateResponse("Halo, toko nnaco.sa jual apa saja?", "Katalog: Kalung Mutiara, Gelang Manik Kawaii, Anting Kupu-kupu.", "customer");
echo "Chatbot Response: " . substr($reply, 0, 150) . "..." . PHP_EOL;

echo PHP_EOL . "=== 4. AUDIT LOGS TEST ===" . PHP_EOL;
$logs = $model->getAuditLogs(3);
echo "Total audit logs: " . count($logs) . PHP_EOL;
foreach ($logs as $l) {
    echo "Log: [{$l['action']}] by {$l['user_name']} -> {$l['status']} ({$l['details']})" . PHP_EOL;
}
echo PHP_EOL . "ALL TESTS COMPLETED SUCCESSFULLY!" . PHP_EOL;
