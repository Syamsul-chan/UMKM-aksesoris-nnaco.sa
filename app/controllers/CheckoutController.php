<?php

class CheckoutController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Customer']);
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        if (empty($_SESSION['cart'])) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Keranjang Anda kosong.'];
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $addressModel = $this->model('Address_model');
        $productModel = $this->model('Product_model');

        $addresses = $addressModel->getAddressesByUserId($userId);
        
        $cartItems = [];
        $total = 0;
        $isValid = true;

        foreach ($_SESSION['cart'] as $id => $quantity) {
            $product = $productModel->getProductById($id);
            if ($product && $product['is_active']) {
                if ($quantity > $product['stock']) {
                    $isValid = false;
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Stok produk "' . $product['name'] . '" tidak mencukupi.'];
                }
                $effectivePrice = (!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0) ? (float)$product['promo_price'] : (float)$product['price'];
                $subtotal = $effectivePrice * $quantity;
                $total += $subtotal;
                $cartItems[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $effectivePrice,
                    'original_price' => (float)$product['price'],
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            } else {
                $isValid = false;
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Terdapat produk yang tidak aktif di keranjang.'];
            }
        }

        if (!$isValid) {
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        $data = [
            'title' => 'Checkout | nnaco_sa',
            'view' => 'checkout/index',
            'addresses' => $addresses,
            'cartItems' => $cartItems,
            'total' => $total,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Keranjang' => BASEURL . '/cart',
                'Checkout' => '#'
            ],
            'layout' => 'customer'
        ];

        $this->view('checkout/index', $data);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASEURL . '/checkout');
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrf_token)) {
            die('CSRF token validation failed.');
        }

        if (empty($_SESSION['cart'])) {
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $addressId = $_POST['address_id'] ?? null;
        $newAddress = $_POST['new_address'] ?? null;
        $paymentMethod = $_POST['payment_method'] ?? '';

        $addressModel = $this->model('Address_model');
        $addressStr = '';

        if ($newAddress && !empty($_POST['address_line']) && !empty($_POST['city'])) {
            $addressData = [
                'user_id' => $userId,
                'address_line' => $_POST['address_line'],
                'city' => $_POST['city'],
                'province' => $_POST['province'] ?? '',
                'postal_code' => $_POST['postal_code'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? ''
            ];
            $addressId = $addressModel->addAddress($addressData);
            $addressStr = $_POST['address_line'] . ', ' . $_POST['city'];
        } elseif ($addressId) {
            $address = $addressModel->getAddressById($addressId);
            if ($address && $address['user_id'] == $userId) {
                $addressStr = $address['address_line'] . ', ' . $address['city'];
            }
        }

        if (empty($addressStr)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Silakan pilih atau tambahkan alamat pengiriman.'];
            header('Location: ' . BASEURL . '/checkout');
            exit;
        }

        if (empty($paymentMethod)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pilih metode pembayaran.'];
            header('Location: ' . BASEURL . '/checkout');
            exit;
        }

        $productModel = $this->model('Product_model');
        $orderModel = $this->model('Order_model');

        $cartItems = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $quantity) {
            $product = $productModel->getProductById($id);
            if (!$product || !$product['is_active'] || $quantity > $product['stock']) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Validasi stok gagal pada saat proses checkout.'];
                header('Location: ' . BASEURL . '/cart');
                exit;
            }
            $effectivePrice = (!empty($product['is_promo']) && !empty($product['discount_percent']) && $product['discount_percent'] > 0) ? (float)$product['promo_price'] : (float)$product['price'];
            $subtotal = $effectivePrice * $quantity;
            $total += $subtotal;
            $cartItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $effectivePrice,
                'quantity' => $quantity
            ];
        }

        $orderId = $orderModel->createOrder($userId, $addressStr, $paymentMethod, $cartItems, $total);

        if ($orderId) {
            $_SESSION['cart'] = []; // Empty cart
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Pesanan berhasil dibuat.'];
            header('Location: ' . BASEURL . '/checkout/success/' . $orderId);
            exit;
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Terjadi kesalahan saat memproses pesanan. Coba lagi.'];
            header('Location: ' . BASEURL . '/checkout');
            exit;
        }
    }

    public function success($orderId = null) {
        if (!$orderId) {
            header('Location: ' . BASEURL);
            exit;
        }

        $orderModel = $this->model('Order_model');
        // Retrieve order details maybe... (can be added later)
        
        $data = [
            'title' => 'Order Success | nnaco_sa',
            'view' => 'checkout/success',
            'order_id' => $orderId,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Checkout' => BASEURL . '/checkout',
                'Success' => '#'
            ]
        ];

        $this->view('checkout/success', $data);
    }
}
