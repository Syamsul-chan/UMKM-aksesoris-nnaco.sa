<?php

class OtpConfigController extends Controller {

    public function __construct() {
        AuthMiddleware::check(['Admin', 'Owner']);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Halaman Pengaturan WhatsApp OTP Gateway
     */
    public function index() {
        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../models/Otp_model.php';

        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        $otpModel = new Otp_model();
        $auditLogs = $otpModel->getAuditLogs(25);

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $baseRoute = ($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin';

        $data = [
            'title' => 'Pengaturan WhatsApp OTP | nnaco_sa',
            'view' => 'admin/otp_config/index',
            'otpConfig' => $otpConfig,
            'auditLogs' => $auditLogs,
            'baseRoute' => $baseRoute,
            'role' => $role,
            'csrf_token' => Controller::generateCsrfToken(),
            'breadcrumbs' => [
                'Dashboard' => $baseRoute,
                'Pengaturan OTP WA' => '#'
            ]
        ];

        $this->view('admin/otp_config/index', $data);
    }

    /**
     * Simpan Perubahan Pengaturan WhatsApp OTP Gateway
     */
    public function update() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/otpConfig');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal.']);
                exit;
            }
            die('CSRF token validation failed.');
        }

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../models/Otp_model.php';

        $configModel = new OtpConfig_model();
        $success = $configModel->updateConfig($_POST);

        $otpModel = new Otp_model();
        $provider = htmlspecialchars($_POST['provider'] ?? 'fonnte');
        $otpModel->logAction(
            $_SESSION['user']['id'] ?? null,
            $_SESSION['user']['username'] ?? 'User',
            'SETTINGS_UPDATED',
            $success ? 'SUCCESS' : 'FAILED',
            "Memperbarui pengaturan WhatsApp OTP Gateway (Provider: {$provider})."
        );

        $msg = $success ? 'Pengaturan WhatsApp OTP berhasil diperbarui.' : 'Gagal memperbarui pengaturan.';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $msg,
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $_SESSION['alert'] = ['type' => $success ? 'success' : 'danger', 'message' => $msg];
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/otpConfig');
        exit;
    }

    /**
     * AJAX Endpoint: Uji Koneksi Gateway WhatsApp (Test Connection)
     */
    public function testConnection() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../models/Otp_model.php';
        require_once __DIR__ . '/../services/WhatsAppService.php';

        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        // Jika form mengirim konfigurasi sementara saat pengujian
        if (!empty($_POST['provider'])) {
            $otpConfig['provider'] = trim($_POST['provider']);
        }
        if (!empty($_POST['api_url'])) {
            $otpConfig['api_url'] = trim($_POST['api_url']);
        }
        if (!empty($_POST['access_token']) && $_POST['access_token'] !== '••••••••••••••••') {
            $otpConfig['decrypted_token'] = trim($_POST['access_token']);
        }
        if (isset($_POST['phone_number_id'])) {
            $otpConfig['phone_number_id'] = trim($_POST['phone_number_id']);
        }
        if (isset($_POST['is_dev_mode'])) {
            $otpConfig['is_dev_mode'] = (int)$_POST['is_dev_mode'];
        }

        $targetPhone = trim($_POST['test_phone'] ?? '');
        $waService = new WhatsAppService($otpConfig);
        $result = $waService->testConnection($targetPhone);

        $otpModel = new Otp_model();
        $maskedPhone = !empty($targetPhone) ? WhatsAppService::maskPhone($targetPhone) : 'System Ping';
        $statusStr = $result['success'] ? 'SUCCESS' : 'FAILED';

        $otpModel->logAction(
            $_SESSION['user']['id'] ?? null,
            $maskedPhone,
            'TEST_CONNECTION',
            $statusStr,
            "Uji koneksi ke gateway WhatsApp ({$result['provider']}): {$result['message']}"
        );

        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'latency_ms' => $result['latency_ms'] ?? 0,
            'http_code' => $result['http_code'] ?? 0,
            'provider' => $result['provider'] ?? '',
            'message' => $result['message'],
            'csrf_token' => Controller::generateCsrfToken()
        ]);
        exit;
    }
}
