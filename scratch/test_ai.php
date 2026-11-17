<?php
require_once 'config/config.php';
require_once 'app/services/AiService.php';

$s = new AiService;
$reply = $s->generateResponse('Halo apakah ada kalung emas?', 'Kalung Emas Murni harga 1.500.000 stok 10');
echo "REPLY: " . $reply . PHP_EOL;
