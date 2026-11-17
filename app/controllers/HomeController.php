<?php

class HomeController extends Controller {
    public function index() {
        $productModel = $this->model('Product_model');
        $categoryModel = $this->model('Category_model');
        $adminModel = $this->model('Admin_model');
        
        $filters = [
            'q' => $_GET['q'] ?? '',
            'cat' => $_GET['cat'] ?? '',
            'min' => $_GET['min'] ?? '',
            'max' => $_GET['max'] ?? ''
        ];

        // Pagination
        $limit = 12;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $totalProducts = $productModel->getTotalProducts($filters);
        $totalPages = ceil($totalProducts / $limit);
        
        $products = $productModel->getPaginatedProducts($limit, $offset, $filters);
        $categories = $categoryModel->getAllCategories();
        $stats = $adminModel->getDashboardStats();

        // Deteksi Request AJAX
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            $formattedProducts = [];
            foreach ($products as $p) {
                $hasImage = (!empty($p['image']) && file_exists('img/products/' . $p['image']));
                $imageUrl = $hasImage ? BASEURL . '/img/products/' . htmlspecialchars($p['image']) : '';
                $isPromo = (!empty($p['is_promo']) && !empty($p['discount_percent']) && (int)$p['discount_percent'] > 0);
                $effectivePrice = $isPromo ? (float)$p['promo_price'] : (float)$p['price'];

                $formattedProducts[] = [
                    'id' => (int)$p['id'],
                    'name' => $p['name'],
                    'price' => (float)$p['price'],
                    'promo_price' => (float)($p['promo_price'] ?? $p['price']),
                    'effective_price' => $effectivePrice,
                    'price_formatted' => 'Rp ' . number_format($p['price'], 0, ',', '.'),
                    'promo_price_formatted' => 'Rp ' . number_format($p['promo_price'] ?? $p['price'], 0, ',', '.'),
                    'is_promo' => $isPromo,
                    'discount_percent' => (int)($p['discount_percent'] ?? 0),
                    'stock' => (int)$p['stock'],
                    'category_name' => $p['category_name'] ?? '',
                    'category_id' => (int)($p['category_id'] ?? 0),
                    'image' => $p['image'] ?? '',
                    'image_url' => $imageUrl,
                    'detail_url' => BASEURL . '/home/detail/' . $p['id']
                ];
            }

            echo json_encode([
                'status' => 'success',
                'products' => $formattedProducts,
                'categories' => $categories,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_products' => $totalProducts,
                    'limit' => $limit
                ],
                'cart_count' => isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0,
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $data = [
            'title' => 'Dashboard | nnaco_sa',
            'view' => 'home/index',
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_products' => $totalProducts
            ],
            'layout' => 'main'
        ];
        
        $this->view('home/index', $data);
    }

    public function detail($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL);
            exit;
        }

        $productModel = $this->model('Product_model');
        $reviewModel = $this->model('Review_model');

        $product = $productModel->getProductById($id);
        
        if (!$product) {
            header('Location: ' . BASEURL);
            exit;
        }

        $ratingData = $reviewModel->getAverageRating($id);
        $reviews = $reviewModel->getReviewsByProductId($id);
        $hasReviewed = false;

        if (isset($_SESSION['user']['id'])) {
            $hasReviewed = $reviewModel->hasReviewedProduct($_SESSION['user']['id'], $id);
        }

        $data = [
            'title' => $product['name'] . ' | nnaco_sa',
            'view' => 'home/detail',
            'product' => $product,
            'rating' => $ratingData,
            'ratingData' => $ratingData,
            'reviews' => $reviews,
            'hasReviewed' => $hasReviewed,
            'layout' => 'customer'
        ];

        $this->view('home/detail', $data);
    }
}
