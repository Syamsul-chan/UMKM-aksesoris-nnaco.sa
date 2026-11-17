<?php

class CartController extends Controller {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index() {
        $productModel = $this->model('Product_model');
        $cartItems = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $quantity) {
            $product = $productModel->getProductById($id);
            if ($product && $product['is_active']) {
                $isPromo = (!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0);
                $effectivePrice = $isPromo ? (float)$product['promo_price'] : (float)$product['price'];
                $subtotal = $effectivePrice * $quantity;
                $total += $subtotal;
                $cartItems[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $effectivePrice,
                    'original_price' => (float)$product['price'],
                    'is_promo' => $isPromo,
                    'discount_percent' => $product['discount_percent'] ?? 0,
                    'image' => $product['image'],
                    'quantity' => $quantity,
                    'stock' => $product['stock'],
                    'subtotal' => $subtotal
                ];
            } else {
                // Remove inactive products from cart
                unset($_SESSION['cart'][$id]);
            }
        }

        $data = [
            'title' => 'Keranjang Belanja | nnaco_sa',
            'view' => 'cart/index',
            'cartItems' => $cartItems,
            'total' => $total,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Keranjang' => '#'
            ],
            'layout' => 'customer'
        ];

        $this->view('cart/index', $data);
    }

    public function add() {
        $isAjax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF Token gagal. Silakan muat ulang halaman.']);
                    exit;
                }
                die('CSRF token validation failed.');
            }

            $id = $_POST['product_id'] ?? null;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

            if ($id && $quantity > 0) {
                $productModel = $this->model('Product_model');
                $product = $productModel->getProductById($id);

                if ($product && $product['is_active'] && $product['stock'] > 0) {
                    $currentQty = $_SESSION['cart'][$id] ?? 0;
                    $newQty = $currentQty + $quantity;

                    if ($newQty > $product['stock']) {
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode([
                                'status' => 'warning', 
                                'message' => 'Stok produk tidak mencukupi (sisa ' . $product['stock'] . ' unit).'
                            ]);
                            exit;
                        }
                        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Stok tidak mencukupi.'];
                    } else {
                        $_SESSION['cart'][$id] = $newQty;
                        $cartCount = array_sum($_SESSION['cart']);
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode([
                                'status' => 'success',
                                'message' => '🌸 "' . $product['name'] . '" berhasil ditambahkan ke keranjang!',
                                'product_name' => $product['name'],
                                'cart_count' => $cartCount,
                                'csrf_token' => Controller::generateCsrfToken()
                            ]);
                            exit;
                        }
                        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang.'];
                    }
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['status' => 'danger', 'message' => 'Produk tidak tersedia atau stok habis.']);
                        exit;
                    }
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Produk tidak tersedia atau stok habis.'];
                }
            }
        }
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASEURL . '/cart'));
        exit;
    }

    public function update() {
        $isAjax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF Token gagal.']);
                    exit;
                }
                die('CSRF token validation failed.');
            }

            $id = $_POST['product_id'] ?? null;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

            if ($id) {
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$id]);
                    if ($isAjax) {
                        $this->sendCartAjaxResponse('Produk dihapus dari keranjang.');
                    }
                    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Produk dihapus dari keranjang.'];
                } else {
                    $productModel = $this->model('Product_model');
                    $product = $productModel->getProductById($id);

                    if ($product && $quantity <= $product['stock']) {
                        $_SESSION['cart'][$id] = $quantity;
                        if ($isAjax) {
                            $this->sendCartAjaxResponse('Keranjang belanja diperbarui.', $id);
                        }
                        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Keranjang diperbarui.'];
                    } else {
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['status' => 'warning', 'message' => 'Stok tidak mencukupi (sisa ' . ($product['stock'] ?? 0) . ' unit).']);
                            exit;
                        }
                        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Stok tidak mencukupi.'];
                    }
                }
            }
        }
        header('Location: ' . BASEURL . '/cart');
        exit;
    }

    public function remove($id = null) {
        $isAjax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($id && isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            if ($isAjax) {
                $this->sendCartAjaxResponse('Produk telah dihapus dari keranjang.');
            }
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Produk dihapus dari keranjang.'];
        }
        header('Location: ' . BASEURL . '/cart');
        exit;
    }

    private function sendCartAjaxResponse($message, $targetProductId = null) {
        $productModel = $this->model('Product_model');
        $total = 0;
        $targetSubtotal = 0;

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $p = $productModel->getProductById($pid);
            if ($p && $p['is_active']) {
                $isPromo = (!empty($p['is_promo']) && !empty($p['discount_percent']) && (int)$p['discount_percent'] > 0);
                $effPrice = $isPromo ? (float)$p['promo_price'] : (float)$p['price'];
                $sub = $effPrice * $qty;
                $total += $sub;
                if ($targetProductId && $pid == $targetProductId) {
                    $targetSubtotal = $sub;
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'cart_count' => array_sum($_SESSION['cart']),
            'total' => $total,
            'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            'item_subtotal' => $targetSubtotal,
            'item_subtotal_formatted' => 'Rp ' . number_format($targetSubtotal, 0, ',', '.'),
            'csrf_token' => Controller::generateCsrfToken()
        ]);
        exit;
    }
}
