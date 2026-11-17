<?php

class AiConfigController extends Controller {

    public function __construct() {
        AuthMiddleware::check(['Admin', 'Owner']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Halaman Utama Manajemen & Konfigurasi Model AI
     */
    public function index() {
        $aiModel = $this->model('AiConfig_model');
        $configs = $aiModel->getAllConfigs();
        $primaryConfig = $aiModel->getPrimaryConfig();
        $auditLogs = $aiModel->getAuditLogs(15);

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        $baseRoute = ($role == 'owner') ? BASEURL . '/owner' : BASEURL . '/admin';

        $data = [
            'title' => 'Konfigurasi Model AI | nnaco_sa',
            'view' => 'admin/ai_config/index',
            'configs' => $configs,
            'primaryConfig' => $primaryConfig,
            'auditLogs' => $auditLogs,
            'baseRoute' => $baseRoute,
            'role' => $role,
            'csrf_token' => Controller::generateCsrfToken(),
            'breadcrumbs' => [
                'Dashboard' => $baseRoute,
                'Konfigurasi AI' => '#'
            ]
        ];

        $this->view('admin/ai_config/index', $data);
    }

    /**
     * Ambil data JSON satu konfigurasi untuk modal edit
     */
    public function getJson($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_GET['ajax']) && $_GET['ajax'] == '1');

        $aiModel = $this->model('AiConfig_model');
        $config = $aiModel->getConfigById($id);

        header('Content-Type: application/json; charset=utf-8');
        if ($config) {
            // Jangan pernah mengirim plain API Key ke frontend!
            $config['api_key'] = ''; // Kosongkan agar aman, frontend menampilkan placeholder
            echo json_encode([
                'status' => 'success',
                'data' => $config
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Konfigurasi tidak ditemukan.'
            ]);
        }
        exit;
    }

    /**
     * Simpan konfigurasi baru
     */
    public function store() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/aiConfig');
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

        $name = trim($_POST['name'] ?? '');
        $provider = trim($_POST['provider'] ?? '');
        $modelName = trim($_POST['model_name'] ?? '');
        $apiUrl = trim($_POST['api_url'] ?? '');

        if (empty($name) || empty($provider) || empty($modelName) || empty($apiUrl)) {
            $msg = 'Harap lengkapi semua kolom wajib (Nama, Provider, Nama Model, dan API URL).';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'danger', 'message' => $msg];
            header('Location: ' . BASEURL . '/admin/aiConfig');
            exit;
        }

        $aiModel = $this->model('AiConfig_model');
        $newId = $aiModel->createConfig($_POST);

        if ($newId) {
            $aiModel->logAction(
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['username'] ?? 'User',
                'CREATE',
                $newId,
                $name,
                'SUCCESS',
                "Menambahkan konfigurasi AI baru '{$name}' (Provider: {$provider}, Model: {$modelName})."
            );
            $msg = "Konfigurasi AI '{$name}' berhasil disimpan.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => $msg,
                    'csrf_token' => Controller::generateCsrfToken()
                ]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'success', 'message' => $msg];
        } else {
            $msg = 'Gagal menyimpan konfigurasi AI ke database.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'danger', 'message' => $msg];
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/aiConfig');
        exit;
    }

    /**
     * Update konfigurasi
     */
    public function update($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            header('Location: ' . BASEURL . '/admin/aiConfig');
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

        $aiModel = $this->model('AiConfig_model');
        $existing = $aiModel->getConfigById($id);
        if (!$existing) {
            $msg = 'Konfigurasi tidak ditemukan.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'danger', 'message' => $msg];
            header('Location: ' . BASEURL . '/admin/aiConfig');
            exit;
        }

        $updated = $aiModel->updateConfig($id, $_POST);
        $name = trim($_POST['name'] ?? $existing['name']);

        if ($updated) {
            $aiModel->logAction(
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['username'] ?? 'User',
                'UPDATE',
                $id,
                $name,
                'SUCCESS',
                "Memperbarui parameter konfigurasi AI '{$name}'."
            );
            $msg = "Perubahan konfigurasi AI '{$name}' berhasil disimpan.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => $msg,
                    'csrf_token' => Controller::generateCsrfToken()
                ]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'success', 'message' => $msg];
        } else {
            $msg = 'Gagal memperbarui konfigurasi AI.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            $_SESSION['alert'] = ['type' => 'danger', 'message' => $msg];
        }

        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/aiConfig');
        exit;
    }

    /**
     * Jadikan satu konfigurasi sebagai Model AI Utama
     */
    public function setPrimary($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            header('Location: ' . BASEURL . '/admin/aiConfig');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal.']);
                exit;
            }
            die('CSRF validation failed.');
        }

        $aiModel = $this->model('AiConfig_model');
        $res = $aiModel->setPrimaryConfig($id);

        if ($res['success']) {
            $aiModel->logAction(
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['username'] ?? 'User',
                'SET_PRIMARY',
                $id,
                $res['config']['name'] ?? 'Model Utama',
                'SUCCESS',
                "Menetapkan '{$res['config']['name']}' sebagai Model AI Utama yang aktif di seluruh sistem."
            );
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $res['success'] ? 'success' : 'error',
                'message' => $res['message'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $_SESSION['alert'] = [
            'type' => $res['success'] ? 'success' : 'danger',
            'message' => $res['message']
        ];
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/aiConfig');
        exit;
    }

    /**
     * Toggle status aktif/nonaktif
     */
    public function toggle($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            header('Location: ' . BASEURL . '/admin/aiConfig');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal.']);
                exit;
            }
            die('CSRF validation failed.');
        }

        $status = (int)($_POST['status'] ?? 0);
        $aiModel = $this->model('AiConfig_model');
        $res = $aiModel->toggleActive($id, $status);

        if ($res['success']) {
            $action = ($status == 1) ? 'ACTIVATE' : 'DEACTIVATE';
            $aiModel->logAction(
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['username'] ?? 'User',
                $action,
                $id,
                'Config #' . $id,
                'SUCCESS',
                $res['message']
            );
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $res['success'] ? 'success' : 'error',
                'message' => $res['message'],
                'new_status' => $res['new_status'] ?? $status,
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $_SESSION['alert'] = [
            'type' => $res['success'] ? 'success' : 'danger',
            'message' => $res['message']
        ];
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/aiConfig');
        exit;
    }

    /**
     * Hapus konfigurasi
     */
    public function delete($id = null) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            header('Location: ' . BASEURL . '/admin/aiConfig');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal.']);
                exit;
            }
            die('CSRF validation failed.');
        }

        $aiModel = $this->model('AiConfig_model');
        $res = $aiModel->deleteConfig($id);

        if ($res['success']) {
            $aiModel->logAction(
                $_SESSION['user']['id'] ?? null,
                $_SESSION['user']['username'] ?? 'User',
                'DELETE',
                $id,
                'Config #' . $id,
                'SUCCESS',
                "Menghapus konfigurasi ID #{$id}."
            );
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $res['success'] ? 'success' : 'error',
                'message' => $res['message'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $_SESSION['alert'] = [
            'type' => $res['success'] ? 'success' : 'danger',
            'message' => $res['message']
        ];
        $role = strtolower($_SESSION['user']['role'] ?? 'admin');
        header('Location: ' . BASEURL . '/' . $role . '/aiConfig');
        exit;
    }

    /**
     * Uji Koneksi ke Provider AI secara Realtime (Test Connection)
     */
    public function testConnection() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Validasi CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        $aiModel = $this->model('AiConfig_model');
        $configId = !empty($_POST['config_id']) ? (int)$_POST['config_id'] : null;

        $provider = trim($_POST['provider'] ?? '');
        $apiUrl = trim($_POST['api_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $modelName = trim($_POST['model_name'] ?? '');
        $temperature = (float)($_POST['temperature'] ?? 0.50);
        $timeout = (int)($_POST['timeout_seconds'] ?? 15);

        // Jika testing konfigurasi yang sudah ada di database dan user tidak mengetik key baru
        if ($configId && (empty($apiKey) || $apiKey === '••••••••••••••••')) {
            $existing = $aiModel->getConfigById($configId);
            if ($existing) {
                if (empty($provider)) $provider = $existing['provider'];
                if (empty($apiUrl)) $apiUrl = $existing['api_url'];
                if (empty($modelName)) $modelName = $existing['model_name'];
                $apiKey = $existing['api_key']; // Berupa ciphertext terenkripsi dari DB
            }
        }

        if (empty($provider) || empty($apiUrl) || empty($modelName)) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Parameter pengujian koneksi tidak lengkap (Provider, API URL, dan Model wajib diisi).'
            ]);
            exit;
        }

        // Jalankan pengujian koneksi
        $result = $aiModel->testConnection($provider, $apiUrl, $apiKey, $modelName, $temperature, 25, $timeout);

        // Catat ke audit log
        $statusStr = $result['success'] ? 'SUCCESS' : 'FAILED';
        $detailMsg = $result['success'] 
            ? "Uji koneksi ke provider '{$provider}' (Model: {$modelName}) BERHASIL dalam {$result['latency_ms']} ms."
            : "Uji koneksi ke provider '{$provider}' (Model: {$modelName}) GAGAL: {$result['error_message']}";

        $aiModel->logAction(
            $_SESSION['user']['id'] ?? null,
            $_SESSION['user']['username'] ?? 'User',
            'TEST_CONNECTION',
            $configId,
            $modelName,
            $statusStr,
            $detailMsg
        );

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => $result['success'] ? 'success' : 'error',
            'latency_ms' => $result['latency_ms'],
            'http_code' => $result['http_code'],
            'reply' => $result['reply'],
            'error_message' => $result['error_message'],
            'provider' => $provider,
            'model' => $modelName,
            'csrf_token' => Controller::generateCsrfToken()
        ]);
        exit;
    }
}
