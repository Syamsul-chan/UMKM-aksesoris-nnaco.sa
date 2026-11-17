<?php

class OwnerController extends Controller {
    public function __construct() {
        AuthMiddleware::check(['Owner']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getFilterDates() {
        $filter = $_GET['filter'] ?? 'this_month';
        $start_date = null;
        $end_date = null;

        if ($filter == 'today') {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        } elseif ($filter == 'this_week') {
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($filter == 'this_month') {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        } elseif ($filter == 'this_year') {
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
        } elseif ($filter == 'custom') {
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
        }

        return ['filter' => $filter, 'start' => $start_date, 'end' => $end_date];
    }

    public function index() {
        $ownerModel = $this->model('Owner_model');
        $dates = $this->getFilterDates();

        $stats = $ownerModel->getOverviewStats($dates['start'], $dates['end']);
        $topProducts = $ownerModel->getTopProducts($dates['start'], $dates['end'], 5);
        $topCategories = $ownerModel->getTopCategories($dates['start'], $dates['end'], 5);
        $orderStatuses = $ownerModel->getOrdersByStatus($dates['start'], $dates['end']);
        $salesPeriod = $ownerModel->getSalesByPeriod($dates['start'], $dates['end']);

        // Ambil status integrasi nyata AI & OTP untuk Ringkasan Eksekutif Owner
        $aiConfigModel = $this->model('AiConfig_model');
        $aiStatus = $aiConfigModel->getIntegrationStatus();

        $otpConfigModel = $this->model('OtpConfig_model');
        $otpStatus = $otpConfigModel->getIntegrationStatus();

        $data = [
            'title' => 'Owner Dashboard | nnaco_sa',
            'view' => 'owner/index',
            'dates' => $dates,
            'stats' => $stats,
            'topProducts' => $topProducts,
            'topCategories' => $topCategories,
            'orderStatuses' => $orderStatuses,
            'salesPeriod' => json_encode($salesPeriod),
            'aiStatus' => $aiStatus,
            'otpStatus' => $otpStatus,
            'breadcrumbs' => [
                'Home' => BASEURL,
                'Owner Dashboard' => '#'
            ]
        ];
        $this->view('owner/index', $data);
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

    public function salesReport() {
        $ownerModel = $this->model('Owner_model');
        $dates = $this->getFilterDates();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $totalSales = $ownerModel->getTotalSalesReport($dates['start'], $dates['end']);
        $totalPages = ceil($totalSales / $limit);
        $sales = $ownerModel->getSalesReport($dates['start'], $dates['end'], $limit, $offset);

        $data = [
            'title' => 'Laporan Penjualan | nnaco_sa',
            'view' => 'owner/reports/sales',
            'dates' => $dates,
            'sales' => $sales,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ],
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/owner',
                'Laporan Penjualan' => '#'
            ]
        ];
        $this->view('owner/reports/sales', $data);
    }

    public function productReport() {
        $ownerModel = $this->model('Owner_model');
        $dates = $this->getFilterDates();

        $topProducts = $ownerModel->getTopProducts($dates['start'], $dates['end'], 100); // Get top 100 for report

        $data = [
            'title' => 'Laporan Produk Terlaris | nnaco_sa',
            'view' => 'owner/reports/products',
            'dates' => $dates,
            'products' => $topProducts,
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/owner',
                'Laporan Produk' => '#'
            ]
        ];
        $this->view('owner/reports/products', $data);
    }

    public function stockReport() {
        $ownerModel = $this->model('Owner_model');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $totalStock = $ownerModel->getTotalStockReport();
        $totalPages = ceil($totalStock / $limit);
        $stocks = $ownerModel->getStockReport($limit, $offset);

        $data = [
            'title' => 'Laporan Stok Barang | nnaco_sa',
            'view' => 'owner/reports/stock',
            'stocks' => $stocks,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages
            ],
            'breadcrumbs' => [
                'Dashboard' => BASEURL . '/owner',
                'Laporan Stok' => '#'
            ]
        ];
        $this->view('owner/reports/stock', $data);
    }

    // =========================================================================
    // --- AI CONFIGURATION DELEGATION ---
    // =========================================================================
    public function aiConfig() {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->index();
    }

    public function aiConfigGet($id = null) {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->getJson($id);
    }

    public function aiConfigStore() {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->store();
    }

    public function aiConfigUpdate($id = null) {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->update($id);
    }

    public function aiConfigSetPrimary($id = null) {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->setPrimary($id);
    }

    public function aiConfigToggle($id = null) {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->toggle($id);
    }

    public function aiConfigDelete($id = null) {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->delete($id);
    }

    public function aiConfigTest() {
        require_once __DIR__ . '/AiConfigController.php';
        $controller = new AiConfigController();
        $controller->testConnection();
    }

    // =========================================================================
    // --- WHATSAPP OTP CONFIGURATION DELEGATION ---
    // =========================================================================
    public function otpConfig() {
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->index();
    }

    public function otpConfigUpdate() {
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->update();
    }

    public function otpConfigTest() {
        require_once __DIR__ . '/OtpConfigController.php';
        $controller = new OtpConfigController();
        $controller->testConnection();
    }

    // =========================================================================
    // --- PROMO MANAGEMENT DELEGATION (OWNER ACCESS) ---
    // =========================================================================
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
}
