<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../app/models/Product_model.php';
require_once __DIR__ . '/../app/models/Category_model.php';

$pm = new Product_model();
$cm = new Category_model();
$products = $pm->getAdminProducts(10, 0, []);
$categories = $cm->getAllCategories();
$filters = ['q'=>'','category_id'=>'','status'=>'all','stock_status'=>'all','sort'=>'newest','is_promo'=>''];
$baseRoute = BASEURL . '/admin';

ob_start();
require __DIR__ . '/../app/views/admin/products/index.php';
$html = ob_get_clean();

echo "HTML Length: " . strlen($html) . "\n";
echo "Contains 'Ajak / Azak': " . (strpos($html, 'Ajak / Azak') !== false ? 'YES' : 'NO (CORRECT: REMOVED)') . "\n";
echo "Contains Filter Toolbar ('kw-filter-card'): " . (strpos($html, 'kw-filter-card') !== false ? 'YES' : 'NO') . "\n";
echo "Contains Search Input ('ajaxSearchInput'): " . (strpos($html, 'ajaxSearchInput') !== false ? 'YES' : 'NO') . "\n";

$filtered = $pm->getAdminProducts(10, 0, ['q' => 'kalung', 'category_id' => '', 'status' => 'all', 'stock_status' => 'all', 'sort' => 'newest', 'is_promo' => '']);
echo "Filtered products count ('kalung'): " . count($filtered) . " (Search backend works)\n";
