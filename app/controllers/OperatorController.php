<?php

class OperatorController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Operator']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        $orderModel = $this->model('Order_model');
        $counts = $orderModel->getOrderStatusCounts();

        $data = [
            'title' => 'Operator Dashboard | nnaco_sa',
            'view' => 'operator/index',
            'counts' => $counts,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Operator Dashboard' => '#'
            ]
        ];
        $this->view('operator/index', $data);
    }

    public function products() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->products();
    }

    public function productCreate() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->productCreate();
    }

    public function productStore() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->productStore();
    }

    public function productEdit($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->productEdit($id);
    }

    public function productUpdate($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->productUpdate($id);
    }

    public function productToggle($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->productToggle($id);
    }

    // --- PROMO MANAGEMENT (DELEGATE TO ADMINCONTROLLER) ---
    public function promos() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->promos();
    }

    public function promoStore() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->promoStore();
    }

    public function promoToggle($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->promoToggle($id);
    }

    public function promoDelete($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->promoDelete($id);
    }

    public function setProductDiscount() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->setProductDiscount();
    }

    public function removeProductDiscount($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->removeProductDiscount($id);
    }

    public function orders() {
        $orderModel = $this->model('Order_model');
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $totalOrders = $orderModel->getTotalAdminOrders($filters);
        $totalPages = ceil($totalOrders / $limit);
        $orders = $orderModel->getAdminOrders($limit, $offset, $filters);

        $data = [
            'title' => 'Daftar Pesanan | nnaco_sa',
            'view' => 'operator/orders/index',
            'orders' => $orders,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ],
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/operator',
                'Pesanan' => '#'
            ]
        ];
        $this->view('operator/orders/index', $data);
    }

    public function orderDetail($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/operator/orders');
            exit;
        }

        $orderModel = $this->model('Order_model');
        
        $order = $orderModel->getOrderById($id);
        if (!$order) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pesanan tidak ditemukan.'];
            header('Location: ' . BASEURL . '/operator/orders');
            exit;
        }

        $orderDetails = $orderModel->getOrderDetails($id);

        $data = [
            'title' => 'Detail Pesanan #' . $order['id'] . ' | nnaco_sa',
            'view' => 'operator/orders/detail',
            'order' => $order,
            'orderDetails' => $orderDetails,
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/operator',
                'Pesanan' => BASEURL . '/operator/orders',
                'Detail #' . $order['id'] => '#'
            ]
        ];
        $this->view('operator/orders/detail', $data);
    }

    public function updateStatus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $orderModel = $this->model('Order_model');
            $new_status = strtolower($_POST['status']);
            
            $order = $orderModel->getOrderById($id);
            if (!$order) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pesanan tidak ditemukan.'];
                header('Location: ' . BASEURL . '/operator/orders');
                exit;
            }

            $current_status = strtolower($order['status']);
            $valid_transition = false;

            // Logika transisi state
            if ($new_status == 'cancelled') {
                if (in_array($current_status, ['pending', 'processing'])) {
                    $valid_transition = true;
                }
            } elseif ($current_status == 'pending' && $new_status == 'processing') {
                $valid_transition = true;
            } elseif ($current_status == 'processing' && $new_status == 'packed') {
                $valid_transition = true;
            } elseif ($current_status == 'packed' && $new_status == 'shipped') {
                $valid_transition = true;
            } elseif ($current_status == 'shipped' && $new_status == 'completed') {
                $valid_transition = true;
            }

            if ($valid_transition) {
                if ($orderModel->updateOrderStatus($id, $new_status) > 0) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Status pesanan berhasil diperbarui menjadi ' . strtoupper($new_status) . '.'];
                    
                    // Kalau di-cancel, kembalikan stok
                    if ($new_status == 'cancelled') {
                        $productModel = $this->model('Product_model');
                        $details = $orderModel->getOrderDetails($id);
                        foreach($details as $item) {
                            $productModel->increaseStock($item['product_id'], $item['quantity']);
                        }
                    }

                    // Kirim notifikasi WhatsApp otomatis ke pelanggan jika nomor HP terdaftar
                    if (!empty($order['phone_number'])) {
                        require_once '../otp_auth/fonnte_helper.php';
                        $waResult = sendWhatsAppOrderStatusUpdate($order['phone_number'], $order, $new_status);
                        if ($waResult['success']) {
                            $_SESSION['alert']['message'] .= ' Notifikasi WhatsApp otomatis telah dikirim ke pelanggan (' . maskPhoneNumber($order['phone_number']) . ').';
                        }
                    }
                }
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Perubahan status tidak valid (dari ' . $current_status . ' ke ' . $new_status . ').'];
            }
        }
        
        header('Location: ' . BASEURL . '/operator/orderDetail/' . $id);
        exit;
    }

    // --- CUSTOMER & USER MANAGEMENT DELEGATION ---
    public function users() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customers();
    }

    public function customers() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customers();
    }

    public function customerDetail($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customerDetail($id);
    }

    public function customerToggle($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customerToggle($id);
    }

    public function customerResetPassword($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customerResetPassword($id);
    }

    public function customerDelete($id = null) {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customerDelete($id);
    }

    public function customerStore() {
        require_once __DIR__ . '/AdminController.php';
        $admin = new AdminController();
        $admin->customerStore();
    }
}
