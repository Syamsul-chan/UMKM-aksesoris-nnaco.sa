<?php

class OrderController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Customer']);
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        $userId = $_SESSION['user']['id'];
        $orderModel = $this->model('Order_model');
        
        $orders = $orderModel->getOrdersByUserId($userId);

        $data = [
            'title' => 'Pesanan Saya | nnaco_sa',
            'view' => 'order/index',
            'orders' => $orders,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Dashboard' => BASEURL . '/customer',
                'Pesanan Saya' => '#'
            ]
        ];

        $this->view('order/index', $data);
    }

    public function myOrders() {
        $this->index();
    }

    public function detail($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $orderModel = $this->model('Order_model');
        $reviewModel = $this->model('Review_model');

        // Prevent viewing other user's order
        $order = $orderModel->getOrderByIdAndUser($id, $userId);
        
        if (!$order) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pesanan tidak ditemukan atau Anda tidak berhak melihatnya.'];
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        $orderDetails = $orderModel->getOrderDetails($id);
        
        // Add review status for each item
        foreach ($orderDetails as &$item) {
            $item['has_reviewed'] = $reviewModel->hasReviewedProduct($userId, $item['product_id']);
        }

        $data = [
            'title' => 'Detail Pesanan #' . $order['id'] . ' | nnaco_sa',
            'view' => 'order/detail',
            'order' => $order,
            'orderDetails' => $orderDetails,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Pesanan Saya' => BASEURL . '/order',
                'Detail #' . $order['id'] => '#'
            ]
        ];

        $this->view('order/detail', $data);
    }
}
