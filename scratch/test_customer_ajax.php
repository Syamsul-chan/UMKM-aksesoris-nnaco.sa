<?php
/**
 * Test Suite: Customer AJAX Implementation Verification
 */

$baseUrl = 'http://localhost/nnaco.sa/public';

echo "=== TEST 1: HomeController AJAX Catalog Endpoint ===" . PHP_EOL;

$ch = curl_init("$baseUrl/?ajax=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode" . PHP_EOL;
$json = json_decode($resp, true);

if ($json && isset($json['status']) && $json['status'] === 'success') {
    echo "✓ Status: SUCCESS" . PHP_EOL;
    echo "✓ Total Products Loaded: " . count($json['products']) . PHP_EOL;
    echo "✓ Pagination Total: " . ($json['pagination']['total_products'] ?? 0) . PHP_EOL;
    echo "✓ Sample Product: " . ($json['products'][0]['name'] ?? 'N/A') . " (" . ($json['products'][0]['price_formatted'] ?? '') . ")" . PHP_EOL;
} else {
    echo "✗ Failed to parse JSON or unexpected response:" . PHP_EOL;
    echo substr($resp, 0, 500) . PHP_EOL;
}

echo PHP_EOL . "=== TEST 2: Live Search via AJAX ===" . PHP_EOL;
$ch = curl_init("$baseUrl/?ajax=1&q=jepit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$resp = curl_exec($ch);
curl_close($ch);
$json = json_decode($resp, true);

if ($json && $json['status'] === 'success') {
    echo "✓ Search 'jepit' returned " . count($json['products']) . " results" . PHP_EOL;
    foreach ($json['products'] as $p) {
        echo "  - " . $p['name'] . PHP_EOL;
    }
} else {
    echo "✗ Live search failed" . PHP_EOL;
}

echo PHP_EOL . "=== TEST 3: Category Filter via AJAX ===" . PHP_EOL;
$catId = !empty($json['categories'][0]['id']) ? $json['categories'][0]['id'] : 1;
$ch = curl_init("$baseUrl/?ajax=1&cat=$catId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$resp = curl_exec($ch);
curl_close($ch);
$json = json_decode($resp, true);

if ($json && $json['status'] === 'success') {
    echo "✓ Category $catId returned " . count($json['products']) . " products" . PHP_EOL;
} else {
    echo "✗ Category filter failed" . PHP_EOL;
}

echo PHP_EOL . "=== TEST 4: Quick Add to Cart via AJAX ===" . PHP_EOL;
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// First get a CSRF token
$ch = curl_init("$baseUrl/?ajax=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$initResp = curl_exec($ch);
curl_close($ch);
$initJson = json_decode($initResp, true);
$csrfToken = $initJson['csrf_token'] ?? '';
$targetProductId = $initJson['products'][0]['id'] ?? 1;

// Now add product to cart via AJAX
$ch = curl_init("$baseUrl/cart/add");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'product_id' => $targetProductId,
    'quantity' => 2,
    'ajax' => '1',
    'csrf_token' => $csrfToken
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$addResp = curl_exec($ch);
$addHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Cart Add HTTP Code: $addHttpCode" . PHP_EOL;
$addJson = json_decode($addResp, true);
if ($addJson && $addJson['status'] === 'success') {
    echo "✓ Quick Add Cart: SUCCESS!" . PHP_EOL;
    echo "✓ Message: " . $addJson['message'] . PHP_EOL;
    echo "✓ Live Cart Count: " . $addJson['cart_count'] . PHP_EOL;
    $csrfToken = $addJson['csrf_token'] ?? $csrfToken;
} else {
    echo "✗ Cart add failed:" . PHP_EOL;
    echo substr($addResp, 0, 500) . PHP_EOL;
}

echo PHP_EOL . "=== TEST 5: Cart Update Quantity via AJAX ===" . PHP_EOL;
$ch = curl_init("$baseUrl/cart/update");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'product_id' => $targetProductId,
    'quantity' => 3,
    'ajax' => '1',
    'csrf_token' => $csrfToken
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$updateResp = curl_exec($ch);
curl_close($ch);
$updateJson = json_decode($updateResp, true);
if ($updateJson && $updateJson['status'] === 'success') {
    echo "✓ Cart Update: SUCCESS!" . PHP_EOL;
    echo "✓ Item Subtotal: " . $updateJson['item_subtotal_formatted'] . PHP_EOL;
    echo "✓ Total: " . $updateJson['total_formatted'] . PHP_EOL;
    $csrfToken = $updateJson['csrf_token'] ?? $csrfToken;
} else {
    echo "✗ Cart update failed:" . PHP_EOL;
    echo substr($updateResp, 0, 500) . PHP_EOL;
}

echo PHP_EOL . "=== TEST 6: Cart Remove via AJAX ===" . PHP_EOL;
$ch = curl_init("$baseUrl/cart/remove/$targetProductId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'ajax' => '1',
    'csrf_token' => $csrfToken
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$removeResp = curl_exec($ch);
curl_close($ch);
$removeJson = json_decode($removeResp, true);
if ($removeJson && $removeJson['status'] === 'success') {
    echo "✓ Cart Remove: SUCCESS!" . PHP_EOL;
    echo "✓ Message: " . $removeJson['message'] . PHP_EOL;
    echo "✓ Remaining Cart Count: " . $removeJson['cart_count'] . PHP_EOL;
} else {
    echo "✗ Cart remove failed:" . PHP_EOL;
    echo substr($removeResp, 0, 500) . PHP_EOL;
}

if (file_exists($cookieFile)) unlink($cookieFile);
echo PHP_EOL . "=== ALL CUSTOMER AJAX TESTS COMPLETE ===" . PHP_EOL;
