<?php
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'app/models/AiConfig_model.php';

$model = new AiConfig_model();
$primary = $model->getPrimaryConfig();

$modelsToTest = [
    'llama-3.3-70b-versatile',
    'llama-3.1-8b-instant',
    'mixtral-8x7b-32768'
];

foreach ($modelsToTest as $m) {
    echo "Testing $m..." . PHP_EOL;
    $res = $model->testConnection('groq', 'https://api.groq.com/openai/v1/chat/completions', $primary['api_key'], $m, 0.70, 20, 15);
    echo "  Success: " . ($res['success'] ? 'YES' : 'NO') . " | HTTP: {$res['http_code']} | Latency: {$res['latency_ms']} ms" . PHP_EOL;
}



