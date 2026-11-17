<?php

class ReviewController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Customer']);
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function add($product_id = null) {
        if (!$product_id) {
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $orderModel = $this->model('Order_model');
        $reviewModel = $this->model('Review_model');
        $productModel = $this->model('Product_model');

        $product = $productModel->getProductById($product_id);
        
        if (!$product) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Produk tidak ditemukan.'];
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        // Validate if user has purchased the product
        if (!$orderModel->hasPurchasedProduct($userId, $product_id)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Anda hanya dapat memberikan ulasan pada produk yang telah Anda beli dan status pesanannya sudah selesai.'];
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        // Validate if user has already reviewed the product
        if ($reviewModel->hasReviewedProduct($userId, $product_id)) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Anda sudah memberikan ulasan untuk produk ini.'];
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $rating = (int)$_POST['rating'];
            $comment = trim($_POST['comment'] ?? '');

            if ($rating < 1 || $rating > 5) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Rating harus antara 1 dan 5.'];
            } else {
                $data = [
                    'user_id' => $userId,
                    'product_id' => $product_id,
                    'rating' => $rating,
                    'comment' => $comment
                ];
                
                if ($reviewModel->addReview($data) > 0) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Ulasan berhasil ditambahkan. Terima kasih!'];
                    header('Location: ' . BASEURL . '/order');
                    exit;
                } else {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menambahkan ulasan.'];
                }
            }
        }

        $data = [
            'title' => 'Tulis Ulasan | nnaco_sa',
            'view' => 'review/add',
            'product' => $product,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Pesanan Saya' => BASEURL . '/order',
                'Tulis Ulasan' => '#'
            ]
        ];

        $this->view('review/add', $data);
    }
}
