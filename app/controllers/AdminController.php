<?php

class AdminController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Admin', 'Owner', 'Operator']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- DASHBOARD ---
    public function index() {
        $adminModel = $this->model('Admin_model');
        $stats = $adminModel->getDashboardStats();

        // Status integrasi sistem nyata untuk Dashboard Admin (khusus Admin & Owner)
        $userRole = strtolower($_SESSION['user']['role'] ?? '');
        $canViewSystemSettings = ($userRole === 'admin' || $userRole === 'owner');

        $aiStatus = null;
        $otpStatus = null;
        if ($canViewSystemSettings) {
            $aiConfigModel = $this->model('AiConfig_model');
            $aiStatus = $aiConfigModel->getIntegrationStatus();

            $otpConfigModel = $this->model('OtpConfig_model');
            $otpStatus = $otpConfigModel->getIntegrationStatus();
        }

        $data = [
            'title' => 'Admin Dashboard | nnaco_sa',
            'view' => 'admin/index',
            'stats' => $stats,
            'canViewSystemSettings' => $canViewSystemSettings,
            'aiStatus' => $aiStatus,
            'otpStatus' => $otpStatus,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Admin Dashboard' => '#'
            ]
        ];
        $this->view('admin/index', $data);
    }

    // --- PRODUCTS ---
    public function products() {
        $productModel = $this->model('Product_model');
        $categoryModel = $this->model('Category_model');

        $filters = [
            'q' => $_GET['q'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? 'all',
            'stock_status' => $_GET['stock_status'] ?? 'all',
            'sort' => $_GET['sort'] ?? 'newest',
            'is_promo' => $_GET['is_promo'] ?? ''
        ];

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalProducts = $productModel->getTotalAdminProducts($filters);
        $totalPages = ceil($totalProducts / $limit);
        $products = $productModel->getAdminProducts($limit, $offset, $filters);
        $categories = $categoryModel->getAllCategories();

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $baseRoute = ($role == 'owner') ? BASEURL . '/owner' : (($role == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin');

        // Check if this is an AJAX request
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'products' => $products,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_products' => $totalProducts,
                    'limit' => $limit
                ],
                'baseRoute' => $baseRoute,
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $data = [
            'title' => 'Manajemen Produk | nnaco_sa',
            'view' => 'admin/products/index',
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'search' => $filters['q'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_products' => $totalProducts
            ],
            'baseRoute' => $baseRoute,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Dashboard' => $baseRoute,
                'Produk' => '#'
            ]
        ];
        $this->view('admin/products/index', $data);
    }

    public function productCreate() {
        $categoryModel = $this->model('Category_model');
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $dashUrl = ($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin';
        $prodUrl = ($role == 'owner') ? BASEURL . '/owner/products' : BASEURL . '/admin/products';

        $data = [
            'title' => 'Tambah Produk | nnaco_sa',
            'view' => 'admin/products/create',
            'categories' => $categoryModel->getAllCategories(),
            'breadcrumbs' => [
                'Dashboard' => $dashUrl,
                'Produk' => $prodUrl,
                'Tambah' => '#'
            ]
        ];
        $this->view('admin/products/create', $data);
    }

    public function productStore() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $productModel = $this->model('Product_model');
            
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                
                // Mime validation
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']) && in_array($mime, $allowedMimes)) {
                    $imageName = time() . '_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../public/img/products/' . $imageName);
                } else {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Format gambar tidak valid atau terdeteksi file berbahaya.'];
                    header('Location: ' . BASEURL . '/admin/productCreate');
                    exit;
                }
            }

            $discountPercent = isset($_POST['discount_percent']) ? max(0, min(100, (int)$_POST['discount_percent'])) : 0;
            $isPromo = ($discountPercent > 0 && isset($_POST['is_promo'])) ? 1 : 0;

            $data = [
                'name' => $_POST['name'],
                'category_id' => $_POST['category_id'],
                'price' => $_POST['price'],
                'discount_percent' => $discountPercent,
                'is_promo' => $isPromo,
                'stock' => $_POST['stock'],
                'description' => $_POST['description'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'image' => $imageName
            ];

            if ($productModel->createProduct($data) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Produk berhasil ditambahkan.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menambahkan produk.'];
            }
            $role = strtolower($_SESSION['user']['role'] ?? 'admin');
            $redirectUrl = ($role == 'owner') ? BASEURL . '/owner/products' : (($role == 'operator') ? BASEURL . '/operator/products' : BASEURL . '/admin/products');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function productEdit($id = null) {
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $dashUrl = ($role == 'owner') ? BASEURL . '/owner' : (($role == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin');
        $prodUrl = ($role == 'owner') ? BASEURL . '/owner/products' : (($role == 'operator') ? BASEURL . '/operator/products' : BASEURL . '/admin/products');

        if (!$id) {
            header('Location: ' . $prodUrl);
            exit;
        }
        $productModel = $this->model('Product_model');
        $categoryModel = $this->model('Category_model');
        
        $product = $productModel->getProductById($id);
        
        $data = [
            'title' => 'Edit Produk | nnaco_sa',
            'view' => 'admin/products/edit',
            'product' => $product,
            'categories' => $categoryModel->getAllCategories(),
            'breadcrumbs' => [
                'Dashboard' => $dashUrl,
                'Produk' => $prodUrl,
                'Edit' => '#'
            ]
        ];
        $this->view('admin/products/edit', $data);
    }

    public function productUpdate($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $productModel = $this->model('Product_model');
            
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                
                // Mime validation
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']) && in_array($mime, $allowedMimes)) {
                    $imageName = time() . '_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../public/img/products/' . $imageName);
                } else {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Format gambar tidak valid atau terdeteksi file berbahaya.'];
                    header('Location: ' . BASEURL . '/admin/productEdit/' . $id);
                    exit;
                }
            }

            $discountPercent = isset($_POST['discount_percent']) ? max(0, min(100, (int)$_POST['discount_percent'])) : 0;
            $isPromo = ($discountPercent > 0 && isset($_POST['is_promo'])) ? 1 : 0;

            $data = [
                'name' => $_POST['name'],
                'category_id' => $_POST['category_id'],
                'price' => $_POST['price'],
                'discount_percent' => $discountPercent,
                'is_promo' => $isPromo,
                'stock' => $_POST['stock'],
                'description' => $_POST['description'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'image' => $imageName
            ];

            if ($productModel->updateProduct($id, $data) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Produk berhasil diupdate.'];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Tidak ada perubahan pada produk.'];
            }
            $role = strtolower($_SESSION['user']['role'] ?? 'admin');
            $redirectUrl = ($role == 'owner') ? BASEURL . '/owner/products' : (($role == 'operator') ? BASEURL . '/operator/products' : BASEURL . '/admin/products');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function productToggle($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed.']);
                    exit;
                }
                die('CSRF token validation failed.');
            }

            $productModel = $this->model('Product_model');
            $status = (int)$_POST['status'];
            $success = ($productModel->toggleStatus($id, $status) > 0);
            if ($success) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Status produk diperbarui.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => $success ? 'success' : 'error',
                    'new_status' => $status,
                    'csrf_token' => Controller::generateCsrfToken()
                ]);
                exit;
            }
        }
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $fallbackUrl = ($role == 'owner') ? BASEURL . '/owner/products' : (($role == 'operator') ? BASEURL . '/operator/products' : BASEURL . '/admin/products');
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallbackUrl));
        exit;
    }

    // --- CATEGORIES ---
    public function categories() {
        $categoryModel = $this->model('Category_model');
        $search = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalCategories = $categoryModel->getTotalAdminCategories($search);
        $totalPages = ceil($totalCategories / $limit);
        $categories = $categoryModel->getAdminCategories($limit, $offset, $search);

        $data = [
            'title' => 'Manajemen Kategori | nnaco_sa',
            'view' => 'admin/categories/index',
            'categories' => $categories,
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ],
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/admin',
                'Kategori' => '#'
            ]
        ];
        $this->view('admin/categories/index', $data);
    }

    public function categoryStore() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $categoryModel = $this->model('Category_model');
            $data = ['name' => $_POST['name']];
            if ($categoryModel->createCategory($data) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Kategori ditambahkan.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menambahkan kategori.'];
            }
        }
        header('Location: ' . BASEURL . '/admin/categories');
        exit;
    }

    public function categoryUpdate($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $categoryModel = $this->model('Category_model');
            $data = ['name' => $_POST['name']];
            if ($categoryModel->updateCategory($id, $data) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Kategori diperbarui.'];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Tidak ada perubahan.'];
            }
        }
        header('Location: ' . BASEURL . '/admin/categories');
        exit;
    }

    public function categoryDelete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $categoryModel = $this->model('Category_model');
            if ($categoryModel->deleteCategory($id)) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Kategori dihapus.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Kategori tidak dapat dihapus karena masih terkait dengan produk.'];
            }
        }
        header('Location: ' . BASEURL . '/admin/categories');
        exit;
    }

    // --- CUSTOMERS / USERS MANAGEMENT ---
    public function users() {
        $this->customers();
    }

    public function customers() {
        $userModel = $this->model('User_model');

        $filters = [
            'q' => $_GET['q'] ?? '',
            'status' => $_GET['status'] ?? 'all',
            'order_filter' => $_GET['order_filter'] ?? 'all',
            'sort' => $_GET['sort'] ?? 'newest'
        ];

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalCustomers = $userModel->getTotalCustomers($filters);
        $totalPages = ceil($totalCustomers / $limit);
        $customers = $userModel->getCustomers($limit, $offset, $filters);
        $stats = $userModel->getCustomerStatsSummary();

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $baseRoute = ($role == 'owner') ? BASEURL . '/owner' : (($role == 'operator') ? BASEURL . '/operator' : BASEURL . '/admin');

        // Check if this is an AJAX request
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'customers' => $customers,
                'filters' => $filters,
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_customers' => $totalCustomers,
                    'limit' => $limit
                ],
                'baseRoute' => $baseRoute,
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $data = [
            'title' => 'Manajemen User & Customer | nnaco_sa',
            'view' => 'admin/customers/index',
            'customers' => $customers,
            'stats' => $stats,
            'filters' => $filters,
            'search' => $filters['q'],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_customers' => $totalCustomers
            ],
            'baseRoute' => $baseRoute,
            'breadcrumbs' => [
                'Dashboard' => $baseRoute,
                'Manajemen User' => '#'
            ]
        ];
        $this->view('admin/customers/index', $data);
    }

    public function customerDetail($id = null) {
        $userModel = $this->model('User_model');
        $customer = $userModel->getCustomerDetails($id);

        header('Content-Type: application/json; charset=utf-8');
        if ($customer) {
            echo json_encode([
                'status' => 'success',
                'customer' => $customer
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data customer tidak ditemukan.'
            ]);
        }
        exit;
    }

    public function customerToggle($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal.']);
                    exit;
                }
                die('CSRF token validation failed.');
            }

            $userModel = $this->model('User_model');
            $status = (int)$_POST['status'];
            $success = ($userModel->toggleUserStatus($id, $status) > 0);
            
            $statusLabel = ($status == 1) ? 'diaktifkan' : 'dinonaktifkan / diblokir';
            if ($success) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "Akun customer berhasil {$statusLabel}."];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => $success ? 'success' : 'error',
                    'new_status' => $status,
                    'message' => "Akun customer berhasil {$statusLabel}.",
                    'csrf_token' => Controller::generateCsrfToken()
                ]);
                exit;
            }
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $fallback = ($role == 'owner') ? BASEURL . '/owner/users' : BASEURL . '/admin/users';
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback));
        exit;
    }

    public function customerResetPassword($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF gagal.']);
                    exit;
                }
                die('CSRF validation failed.');
            }

            $newPassword = trim($_POST['new_password'] ?? '');
            if (empty($newPassword)) {
                $newPassword = 'nnaco' . rand(1000, 9999);
            }

            $userModel = $this->model('User_model');
            $affected = $userModel->resetPassword($id, $newPassword);

            if ($isAjax) {
                header('Content-Type: application/json');
                if ($affected >= 0) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Password customer berhasil direset.',
                        'new_password' => $newPassword,
                        'csrf_token' => Controller::generateCsrfToken()
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal mereset password.']);
                }
                exit;
            }

            $_SESSION['alert'] = ['type' => 'success', 'message' => "Password baru: {$newPassword}"];
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $fallback = ($role == 'owner') ? BASEURL . '/owner/users' : BASEURL . '/admin/users';
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback));
        exit;
    }

    public function customerDelete($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF gagal.']);
                    exit;
                }
                die('CSRF validation failed.');
            }

            $userModel = $this->model('User_model');
            $result = $userModel->deleteCustomer($id);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => $result['success'] ? 'success' : 'error',
                    'message' => $result['message'],
                    'has_orders' => $result['has_orders'] ?? false,
                    'csrf_token' => Controller::generateCsrfToken()
                ]);
                exit;
            }

            $_SESSION['alert'] = [
                'type' => $result['success'] ? 'success' : 'danger',
                'message' => $result['message']
            ];
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $fallback = ($role == 'owner') ? BASEURL . '/owner/users' : BASEURL . '/admin/users';
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback));
        exit;
    }

    public function customerStore() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $userModel = $this->model('User_model');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $phone = trim($_POST['phone_number'] ?? '');
            $password = trim($_POST['password'] ?? 'password123');

            // Format phone to 62...
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62' . substr($phone, 1);
                }
            }

            // Check if email exists
            if ($userModel->getUserByEmail($email)) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Email customer sudah terdaftar. Gunakan email lain.'];
            } else {
                $created = $userModel->createUser([
                    'role_id' => 1,
                    'username' => $username,
                    'email' => $email,
                    'phone_number' => $phone,
                    'password' => $password,
                    'is_active' => 1
                ]);

                if ($created > 0) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => "Customer '{$username}' berhasil ditambahkan."];
                } else {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menambahkan customer baru.'];
                }
            }
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $fallback = ($role == 'owner') ? BASEURL . '/owner/users' : BASEURL . '/admin/users';
        header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback));
        exit;
    }

    // --- ORDERS ---
    public function orders() {
        $orderModel = $this->model('Order_model');
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalOrders = $orderModel->getTotalAdminOrders($filters);
        $totalPages = ceil($totalOrders / $limit);
        $orders = $orderModel->getAdminOrders($limit, $offset, $filters);

        $data = [
            'title' => 'Manajemen Pesanan | nnaco_sa',
            'view' => 'admin/orders/index',
            'orders' => $orders,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ],
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/admin',
                'Pesanan' => '#'
            ]
        ];
        $this->view('admin/orders/index', $data);
    }

    public function orderDetail($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/admin/orders');
            exit;
        }

        $orderModel = $this->model('Order_model');
        
        $order = $orderModel->getOrderById($id);
        if (!$order) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pesanan tidak ditemukan.'];
            header('Location: ' . BASEURL . '/admin/orders');
            exit;
        }

        $orderDetails = $orderModel->getOrderDetails($id);

        $data = [
            'title' => 'Detail Pesanan #' . $order['id'] . ' | nnaco_sa',
            'view' => 'admin/orders/detail',
            'order' => $order,
            'orderDetails' => $orderDetails,
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/admin',
                'Pesanan' => BASEURL . '/admin/orders',
                'Detail #' . $order['id'] => '#'
            ]
        ];
        $this->view('admin/orders/detail', $data);
    }

    public function updateStatus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $orderModel = $this->model('Order_model');
            $new_status = strtolower(trim($_POST['status'] ?? ''));
            
            $order = $orderModel->getOrderById($id);
            if (!$order) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Pesanan tidak ditemukan.'];
                header('Location: ' . BASEURL . '/admin/orders');
                exit;
            }

            $current_status = strtolower($order['status']);
            $allowed_statuses = ['pending', 'processing', 'packed', 'shipped', 'completed', 'cancelled'];

            if (!in_array($new_status, $allowed_statuses)) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Status pesanan tidak valid.'];
                header('Location: ' . BASEURL . '/admin/orderDetail/' . $id);
                exit;
            }

            if ($orderModel->updateOrderStatus($id, $new_status) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Status pesanan berhasil diperbarui menjadi ' . strtoupper($new_status) . '.'];
                
                // Kalau di-cancel, kembalikan stok
                if ($new_status == 'cancelled' && $current_status != 'cancelled') {
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
            } else {
                $_SESSION['alert'] = ['type' => 'info', 'message' => 'Status pesanan sudah dalam status ' . strtoupper($new_status) . '.'];
            }
        }
        
        header('Location: ' . BASEURL . '/admin/orderDetail/' . $id);
        exit;
    }

    // =========================================================================
    // SISTEM MANAJEMEN PROMO (ADMIN, OPERATOR & OWNER)
    // =========================================================================

    public function promos() {
        $role = strtolower($_SESSION['user']['role'] ?? '');
        if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
            require_once '../app/controllers/ErrorController.php';
            $err = new ErrorController();
            $err->forbidden();
            exit;
        }

        $promoModel = $this->model('Promo_model');
        $productModel = $this->model('Product_model');

        $promos = $promoModel->getAllPromos();
        $products = $productModel->getAllProducts();
        $promoProducts = $productModel->getPromoProducts();

        $baseRoute = ($role == 'operator') ? BASEURL . '/operator' : (($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin');

        $data = [
            'title' => 'Kelola Promo & Diskon | nnaco_sa',
            'view' => 'admin/promos/index',
            'promos' => $promos,
            'products' => $products,
            'promoProducts' => $promoProducts,
            'baseRoute' => $baseRoute,
            'breadcrumbs' => [
                'Dashboard' => $baseRoute,
                'Kelola Promo' => '#'
            ]
        ];

        $this->view('admin/promos/index', $data);
    }

    public function promoStore() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $role = strtolower($_SESSION['user']['role'] ?? '');
            if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
                die('Unauthorized.');
            }

            $promoModel = $this->model('Promo_model');
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'code' => strtoupper(trim($_POST['code'] ?? '')),
                'discount_type' => $_POST['discount_type'] ?? 'percentage',
                'discount_value' => floatval($_POST['discount_value'] ?? 0),
                'start_date' => $_POST['start_date'] ?? date('Y-m-d H:i:s'),
                'end_date' => $_POST['end_date'] ?? date('Y-m-d 23:59:59', strtotime('+30 days')),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($promoModel->createPromo($data) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "Kupon promo [{$data['code']}] berhasil dibuat."];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal membuat kupon promo atau kode sudah digunakan.'];
            }

            $redirectUrl = ($role == 'operator') ? BASEURL . '/operator/promos' : (($role == 'owner') ? BASEURL . '/owner/promos' : BASEURL . '/admin/promos');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function promoToggle($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $role = strtolower($_SESSION['user']['role'] ?? '');
            if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
                die('Unauthorized.');
            }

            $promoModel = $this->model('Promo_model');
            if ($promoModel->togglePromo($id) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Status aktif kupon promo berhasil diperbarui.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal memperbarui status kupon.'];
            }

            $redirectUrl = ($role == 'operator') ? BASEURL . '/operator/promos' : (($role == 'owner') ? BASEURL . '/owner/promos' : BASEURL . '/admin/promos');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function promoDelete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $role = strtolower($_SESSION['user']['role'] ?? '');
            if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
                die('Unauthorized.');
            }

            $promoModel = $this->model('Promo_model');
            if ($promoModel->deletePromo($id) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Kupon promo berhasil dihapus.'];
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menghapus kupon promo.'];
            }

            $redirectUrl = ($role == 'operator') ? BASEURL . '/operator/promos' : (($role == 'owner') ? BASEURL . '/owner/promos' : BASEURL . '/admin/promos');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function setProductDiscount() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $role = strtolower($_SESSION['user']['role'] ?? '');
            if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
                die('Unauthorized.');
            }

            $productId = (int)($_POST['product_id'] ?? 0);
            $discount = (int)($_POST['discount_percent'] ?? 0);

            $productModel = $this->model('Product_model');
            if ($productModel->setProductPromo($productId, $discount) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "Diskon produk {$discount}% berhasil diterapkan."];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Gagal menerapkan diskon atau tidak ada perubahan.'];
            }

            $redirectUrl = ($role == 'operator') ? BASEURL . '/operator/promos' : (($role == 'owner') ? BASEURL . '/owner/promos' : BASEURL . '/admin/promos');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function removeProductDiscount($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $role = strtolower($_SESSION['user']['role'] ?? '');
            if ($role !== 'admin' && $role !== 'operator' && $role !== 'owner') {
                die('Unauthorized.');
            }

            $productModel = $this->model('Product_model');
            if ($productModel->removeProductPromo($id) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Promo diskon produk berhasil dihapus.'];
            }

            $redirectUrl = ($role == 'operator') ? BASEURL . '/operator/promos' : (($role == 'owner') ? BASEURL . '/owner/promos' : BASEURL . '/admin/promos');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    // =========================================================================
    // --- AI CONFIGURATION (Admin & Owner Only) ---
    // =========================================================================
    private function checkAiConfigAccess() {
        $role = strtolower($_SESSION['user']['role'] ?? '');
        if ($role !== 'admin' && $role !== 'owner') {
            http_response_code(403);
            die('Unauthorized: Hanya Admin dan Owner yang berhak mengakses Pengaturan Sistem (Konfigurasi AI & OTP WhatsApp).');
        }
    }

    public function aiConfig() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->index();
    }

    public function aiConfigGet($id = null) {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->getJson($id);
    }

    public function aiConfigStore() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->store();
    }

    public function aiConfigUpdate($id = null) {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->update($id);
    }

    public function aiConfigSetPrimary($id = null) {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->setPrimary($id);
    }

    public function aiConfigToggle($id = null) {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->toggle($id);
    }

    public function aiConfigDelete($id = null) {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->delete($id);
    }

    public function aiConfigTest() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->testConnection();
    }

    // =========================================================================
    // --- WHATSAPP OTP CONFIGURATION (Admin & Owner Only) ---
    // =========================================================================
    public function otpConfig() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->index();
    }

    public function otpConfigUpdate() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->update();
    }

    public function otpConfigTest() {
        $this->checkAiConfigAccess();
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->testConnection();
    }
}
