<?php

require_once '../app/services/BusinessInsightService.php';

class InsightController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check(['Owner']);
    }

    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $filter = $input['filter'] ?? '30_hari';

        // Calculate Dates
        $start_date = null;
        $end_date = date('Y-m-d');
        $periodLabel = "";

        if ($filter == '7_hari') {
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $periodLabel = "7 Hari Terakhir";
        } elseif ($filter == '30_hari') {
            $start_date = date('Y-m-d', strtotime('-30 days'));
            $periodLabel = "30 Hari Terakhir";
        } elseif ($filter == '3_bulan') {
            $start_date = date('Y-m-d', strtotime('-3 months'));
            $periodLabel = "3 Bulan Terakhir";
        } elseif ($filter == 'custom') {
            $start_date = $input['start_date'] ?? date('Y-m-01');
            $end_date = $input['end_date'] ?? date('Y-m-d');
            $periodLabel = "Custom ($start_date s/d $end_date)";
        } else {
            $start_date = date('Y-m-d', strtotime('-30 days'));
            $periodLabel = "30 Hari Terakhir";
        }

        // Fetch Metrics from Model
        $ownerModel = $this->model('Owner_model');
        
        $stats = $ownerModel->getOverviewStats($start_date, $end_date);
        $topProducts = $ownerModel->getTopProducts($start_date, $end_date, 3); // Get top 3 for AI
        
        // Custom query to get Low Stock items directly (instead of full stock report pagination)
        $db = new Database();
        $db->query('SELECT name, stock FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC');
        $lowStock = $db->resultSet();

        $metrics = [
            'stats' => $stats,
            'topProducts' => $topProducts,
            'lowStock' => $lowStock
        ];

        // Call Service
        $insightService = new BusinessInsightService();
        $insightResult = $insightService->analyze($metrics, $periodLabel);

        // Return JSON
        echo json_encode([
            'status' => 'success',
            'insight' => $insightResult
        ]);
    }
}
