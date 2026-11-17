<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

$db = new Database();
$db->query('SELECT NOW() as mysql_now, UNIX_TIMESTAMP(NOW()) as mysql_unix, UNIX_TIMESTAMP(created_at) as last_created_unix, created_at FROM otp_logs ORDER BY id DESC LIMIT 1');
print_r($db->single());
echo 'PHP date: ' . date('Y-m-d H:i:s') . PHP_EOL;
echo 'PHP time(): ' . time() . PHP_EOL;
echo 'PHP date_default_timezone: ' . date_default_timezone_get() . PHP_EOL;
