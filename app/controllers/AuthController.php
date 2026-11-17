<?php

class AuthController extends Controller {

    public function index() {
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    public function login() {
        // Cek jika sudah login
        AuthMiddleware::checkGuest();

        $data = [
            'title' => 'Login | nnaco_sa',
            'view' => 'auth/login',
            'layout' => 'auth'
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Email/Username dan Password wajib diisi.'];
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            $userModel = $this->model('User_model');
            $user = $userModel->getUserByIdentity($email);

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role_name']
                ];
                
                // Redirect ke dashboard sesuai role
                AuthMiddleware::redirectDashboard($user['role_name']);
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Email/Username atau Password salah, atau akun tidak aktif.'];
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }
        }

        // Render view login
        $this->view('auth/login', $data);
    }

    public function register() {
        AuthMiddleware::checkGuest();

        $data = [
            'title' => 'Register | nnaco_sa',
            'view' => 'auth/register',
            'layout' => 'auth'
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Controller::verifyCsrfToken($csrf_token)) {
                die('CSRF token validation failed.');
            }

            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $rawPhone = trim($_POST['phone_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Semua field wajib diisi.'];
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }

            if ($password !== $confirm_password) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Konfirmasi password tidak cocok.'];
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }

            $userModel = $this->model('User_model');
            // Cek email apakah sudah ada
            $existingUser = $userModel->getUserByEmail($email);
            if ($existingUser) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Email sudah terdaftar.'];
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }

            // Normalisasi & cek nomor HP jika diisi
            $normalizedPhone = null;
            if (!empty($rawPhone)) {
                require_once __DIR__ . '/../services/WhatsAppService.php';
                $normalizedPhone = WhatsAppService::normalizePhone($rawPhone);
                if (!WhatsAppService::isValidIndonesianPhone($normalizedPhone)) {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Format nomor WhatsApp tidak valid. Gunakan format 08xxx atau 628xxx (10-15 digit).'];
                    header('Location: ' . BASEURL . '/auth/register');
                    exit;
                }

                $existingPhone = $userModel->getUserByPhone($normalizedPhone);
                if ($existingPhone) {
                    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Nomor WhatsApp sudah digunakan oleh akun lain.'];
                    header('Location: ' . BASEURL . '/auth/register');
                    exit;
                }
            }

            // Dapatkan role_id untuk Customer
            $db = new Database;
            $db->query("SELECT id FROM roles WHERE name = 'Customer'");
            $role = $db->single();

            if (!$role) {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Role Customer tidak ditemukan.'];
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }

            $userData = [
                'role_id' => $role['id'],
                'username' => $username,
                'email' => $email,
                'phone_number' => $normalizedPhone,
                'password' => $password
            ];

            if ($userModel->createUser($userData) > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Registrasi berhasil, silakan login.'];
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            } else {
                $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Registrasi gagal.'];
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }
        }

        $this->view('auth/register', $data);
    }

    public function logout() {
        session_unset();
        session_destroy();
        
        // Start again for flash message if needed
        session_start();
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Anda berhasil logout.'];
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    // =========================================================================
    // --- LUPA KATA SANDI / FORGOT PASSWORD ---
    // =========================================================================

    /**
     * Halaman Lupa Kata Sandi
     */
    public function forgotPassword() {
        AuthMiddleware::checkGuest();

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        $data = [
            'title' => 'Lupa Kata Sandi | nnaco.sa Aksesoris',
            'view' => 'auth/forgot_password',
            'layout' => 'auth',
            'otpConfig' => $otpConfig,
            'csrf_token' => Controller::generateCsrfToken()
        ];

        $this->view('auth/forgot_password', $data);
    }

    /**
     * AJAX Endpoint: Permintaan Kirim Kode Verifikasi Reset Kata Sandi
     */
    public function sendResetCode() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        $identity = trim($_POST['identity'] ?? '');
        if (empty($identity)) {
            echo json_encode(['status' => 'error', 'message' => 'Alamat email atau nomor WhatsApp wajib diisi.']);
            exit;
        }

        $userModel = $this->model('User_model');
        $user = $userModel->getUserByIdentity($identity);

        if (!$user) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Akun dengan email atau nomor WhatsApp tersebut tidak ditemukan. Silakan periksa kembali atau daftar akun baru.',
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        if (empty($user['is_active'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Akun Anda sedang dinonaktifkan oleh Administrator. Silakan hubungi customer service.',
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        require_once __DIR__ . '/../models/PasswordReset_model.php';
        $resetModel = new PasswordReset_model();

        // 1. Cek jeda kirim ulang (Cooldown 60s)
        $cooldown = $resetModel->checkCooldown($identity, 60);
        if ($cooldown['in_cooldown']) {
            echo json_encode([
                'status' => 'error',
                'message' => "Mohon tunggu {$cooldown['remaining_seconds']} detik sebelum meminta kode verifikasi kembali.",
                'remaining_cooldown' => $cooldown['remaining_seconds'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        // 2. Buat token challenge (Berlaku 15 menit)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $challenge = $resetModel->createResetChallenge($user['id'], $identity, 15, $ip);

        // 3. Kirimkan kode verifikasi
        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../services/WhatsAppService.php';
        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        $destDesc = $identity;
        $sentViaWa = false;

        // Cari nomor WhatsApp tujuan
        $targetPhone = !empty($user['phone_number']) ? $user['phone_number'] : '';
        if (empty($targetPhone) && WhatsAppService::isValidIndonesianPhone(WhatsAppService::normalizePhone($identity))) {
            $targetPhone = WhatsAppService::normalizePhone($identity);
        }

        if (!empty($targetPhone) && !empty($otpConfig['is_active'])) {
            $waService = new WhatsAppService($otpConfig);
            $sendRes = $waService->sendPasswordResetOtp($targetPhone, $challenge['plain_code'], 15);
            $maskedPhone = WhatsAppService::maskPhone($targetPhone);
            $destDesc = "WhatsApp {$maskedPhone}";
            $sentViaWa = $sendRes['success'];
        } else {
            // Mask email jika via email
            if (str_contains($identity, '@')) {
                $parts = explode('@', $identity);
                $name = $parts[0];
                $domain = $parts[1] ?? '';
                $maskedName = substr($name, 0, 2) . str_repeat('•', max(3, strlen($name) - 2));
                $destDesc = "Email {$maskedName}@{$domain}";
            }
        }

        $response = [
            'status' => 'success',
            'message' => $sentViaWa 
                ? "Kode verifikasi 6-digit berhasil dikirimkan ke {$destDesc}." 
                : "Kode verifikasi 6-digit berhasil dibuat untuk akun Anda ({$destDesc}).",
            'identity' => $identity,
            'dest_desc' => $destDesc,
            'expires_in' => $challenge['expires_in_sec'],
            'cooldown' => 60,
            'csrf_token' => Controller::generateCsrfToken()
        ];

        // Jika dev mode aktif, sertakan hint untuk kemudahan pengujian
        if (!empty($otpConfig['is_dev_mode'])) {
            $response['dev_code'] = $challenge['plain_code'];
            $response['dev_hint'] = "Mode Simulasi Dev Aktif: Kode Verifikasi Reset adalah [{$challenge['plain_code']}]";
        }

        echo json_encode($response);
        exit;
    }

    /**
     * AJAX Endpoint: Verifikasi Kode & Simpan Kata Sandi Baru
     */
    public function submitResetPassword() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        $identity = trim($_POST['identity'] ?? '');
        $code = trim($_POST['reset_code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($identity) || empty($code) || empty($password) || empty($confirmPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Kata sandi baru minimal terdiri dari 6 karakter.']);
            exit;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Konfirmasi kata sandi baru tidak cocok.']);
            exit;
        }

        require_once __DIR__ . '/../models/PasswordReset_model.php';
        $resetModel = new PasswordReset_model();
        $verifyRes = $resetModel->verifyResetChallenge($identity, $code);

        if (!$verifyRes['success']) {
            echo json_encode([
                'status' => 'error',
                'message' => $verifyRes['message'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $userId = $verifyRes['user_id'];
        $userModel = $this->model('User_model');
        $affected = $userModel->resetPassword($userId, $password);

        if ($affected >= 0) {
            // Tandai challenge selesai digunakan
            $resetModel->markUsed($verifyRes['record_id']);

            // Set alert flash message untuk halaman login
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.'
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Kata sandi berhasil diperbarui! Mengalihkan ke halaman login...',
                'redirect' => BASEURL . '/auth/login'
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui kata sandi di sistem database. Silakan coba kembali.'
            ]);
            exit;
        }
    }

    // =========================================================================
    // --- WHATSAPP OTP AUTHENTICATION ---
    // =========================================================================

    /**
     * Halaman Login WhatsApp OTP
     */
    public function otp() {
        AuthMiddleware::checkGuest();

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        $data = [
            'title' => 'Login WhatsApp OTP | nnaco_sa',
            'view' => 'auth/otp_login',
            'layout' => 'auth',
            'otpConfig' => $otpConfig,
            'csrf_token' => Controller::generateCsrfToken()
        ];

        $this->view('auth/otp_login', $data);
    }

    /**
     * AJAX Endpoint: Permintaan Kirim Kode OTP ke WhatsApp
     */
    public function sendOtp() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../models/Otp_model.php';
        require_once __DIR__ . '/../services/WhatsAppService.php';

        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        // Cek apakah layanan OTP sedang aktif
        if (empty($otpConfig['is_active'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Layanan login dengan WhatsApp OTP sedang dinonaktifkan sementara oleh Administrator. Silakan gunakan login Email & Password.'
            ]);
            exit;
        }

        $rawPhone = trim($_POST['phone_number'] ?? '');
        if (empty($rawPhone)) {
            echo json_encode(['status' => 'error', 'message' => 'Nomor WhatsApp wajib diisi.']);
            exit;
        }

        $normalizedPhone = WhatsAppService::normalizePhone($rawPhone);
        if (!WhatsAppService::isValidIndonesianPhone($normalizedPhone)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format nomor WhatsApp tidak valid. Masukkan nomor yang diawali 08 atau 628 dengan panjang 10-15 digit.'
            ]);
            exit;
        }

        $otpModel = new Otp_model();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $maskedPhone = WhatsAppService::maskPhone($normalizedPhone);

        // 1. Cek Cooldown (Jeda waktu antar permintaan OTP)
        $cooldownSec = (int)($otpConfig['resend_cooldown_seconds'] ?? 60);
        $cooldownCheck = $otpModel->checkCooldown($normalizedPhone, $cooldownSec);
        if ($cooldownCheck['in_cooldown']) {
            echo json_encode([
                'status' => 'error',
                'message' => "Mohon tunggu {$cooldownCheck['remaining_seconds']} detik sebelum meminta kode OTP kembali.",
                'remaining_cooldown' => $cooldownCheck['remaining_seconds']
            ]);
            exit;
        }

        // 2. Cek Rate Limiting (Maksimal per jam)
        $rateLimitPerHour = (int)($otpConfig['rate_limit_per_hour'] ?? 5);
        $rateLimitCheck = $otpModel->checkRateLimit($normalizedPhone, $ip, $rateLimitPerHour);
        if (!$rateLimitCheck['allowed']) {
            $otpModel->logAction(null, $maskedPhone, 'RATE_LIMITED', 'FAILED', $rateLimitCheck['reason'], $ip);
            echo json_encode([
                'status' => 'error',
                'message' => $rateLimitCheck['reason']
            ]);
            exit;
        }

        // 3. Periksa apakah akun terdaftar dan aktif
        $userModel = $this->model('User_model');
        $user = $userModel->getUserByPhone($normalizedPhone);

        $expMinutes = (int)($otpConfig['otp_expiration_minutes'] ?? 5);

        // Jika nomor belum terdaftar, otomatis daftarkan sebagai Customer baru (Seamless Auto-Register)
        $isNewUser = false;
        if (!$user) {
            $user = $userModel->registerCustomerByPhone($normalizedPhone);
            if ($user) {
                $isNewUser = true;
                $otpModel->logAction(
                    $user['id'],
                    $maskedPhone,
                    'AUTO_REGISTER_CUSTOMER',
                    'SUCCESS',
                    "Nomor baru WhatsApp otomatis didaftarkan sebagai Customer baru.",
                    $ip
                );
            }
        }

        if (!$user) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menyiapkan akun baru untuk nomor ini. Silakan coba kembali.'
            ]);
            exit;
        }

        if (empty($user['is_active'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Akun dengan nomor ini sedang dinonaktifkan oleh Administrator. Silakan hubungi customer service.'
            ]);
            exit;
        }

        // 4. Buat OTP Challenge baru (Terenkripsi BCRYPT di DB)
        $challenge = $otpModel->createChallenge($normalizedPhone, $user['id'], $expMinutes, $ip);

        // 5. Kirimkan pesan melalui WhatsApp Service
        $waService = new WhatsAppService($otpConfig);
        $sendResult = $waService->sendOtp($normalizedPhone, $challenge['plain_otp'], $expMinutes);

        // Catat ke audit log
        $statusStr = $sendResult['success'] ? 'SUCCESS' : 'FAILED';
        $otpModel->logAction(
            $user['id'],
            $maskedPhone,
            'REQUEST_OTP',
            $statusStr,
            "Pengiriman OTP ke WhatsApp ({$sendResult['provider']}): " . $sendResult['message'],
            $ip
        );

        if ($sendResult['success']) {
            $isWebDirect = ($sendResult['provider'] === 'web_system');

            $successMsg = $isWebDirect 
                ? 'Kode OTP 6-digit berhasil diproses oleh sistem web nnaco.sa.'
                : ($isNewUser 
                    ? 'Nomor WhatsApp baru berhasil disiapkan! Kode OTP 6-digit telah dikirimkan ke WhatsApp Anda.' 
                    : 'Kode OTP 6-digit berhasil dikirim ke nomor WhatsApp Anda.');

            $response = [
                'status' => 'success',
                'message' => $successMsg,
                'phone_masked' => $maskedPhone,
                'cooldown' => $cooldownSec,
                'expires_in' => ($expMinutes * 60),
                'csrf_token' => Controller::generateCsrfToken(),
                'provider' => $sendResult['provider'] ?? 'fonnte'
            ];

            echo json_encode($response);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal mengirim pesan OTP ke WhatsApp: ' . $sendResult['message'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: Verifikasi Kode OTP & Login Otomatis
     */
    public function verifyOtp() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Controller::verifyCsrfToken($csrfToken)) {
            echo json_encode(['status' => 'error', 'message' => 'Validasi keamanan CSRF token gagal. Silakan muat ulang halaman.']);
            exit;
        }

        require_once __DIR__ . '/../models/OtpConfig_model.php';
        require_once __DIR__ . '/../models/Otp_model.php';
        require_once __DIR__ . '/../services/WhatsAppService.php';

        $configModel = new OtpConfig_model();
        $otpConfig = $configModel->getConfig();

        $rawPhone = trim($_POST['phone_number'] ?? '');
        $inputOtp = trim($_POST['otp_code'] ?? '');

        if (empty($rawPhone) || empty($inputOtp)) {
            echo json_encode(['status' => 'error', 'message' => 'Nomor WhatsApp dan kode OTP 6-digit wajib diisi.']);
            exit;
        }

        $normalizedPhone = WhatsAppService::normalizePhone($rawPhone);
        $otpModel = new Otp_model();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $maskedPhone = WhatsAppService::maskPhone($normalizedPhone);

        $maxAttempts = (int)($otpConfig['max_attempts'] ?? 5);
        $verifyResult = $otpModel->verifyOtp($normalizedPhone, $inputOtp, $maxAttempts);

        if (!$verifyResult['success']) {
            $otpModel->logAction(null, $maskedPhone, 'VERIFY_FAIL', 'FAILED', $verifyResult['message'], $ip);
            echo json_encode([
                'status' => 'error',
                'message' => $verifyResult['message'],
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        $user = $verifyResult['user'];
        if (!$user) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Akun pengguna tidak ditemukan di sistem.',
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        if (empty($user['is_active'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Akun Anda sedang dinonaktifkan oleh Administrator.',
                'csrf_token' => Controller::generateCsrfToken()
            ]);
            exit;
        }

        // --- BUAT SESI AUTENTIKASI ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerasi session ID untuk proteksi Session Fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone_number' => $user['phone_number'],
            'role' => $user['role_name']
        ];

        // Tentukan redirect URL sesuai Role dari Backend
        $roleLower = strtolower($user['role_name']);
        $redirectUrl = match($roleLower) {
            'owner' => BASEURL . '/owner',
            'admin' => BASEURL . '/admin',
            'operator' => BASEURL . '/operator',
            default => BASEURL . '/'
        };

        $otpModel->logAction(
            $user['id'],
            $maskedPhone,
            'VERIFY_SUCCESS',
            'SUCCESS',
            "Autentikasi WhatsApp OTP berhasil untuk user '{$user['username']}' (Role: {$user['role_name']}).",
            $ip
        );

        echo json_encode([
            'status' => 'success',
            'message' => "Autentikasi berhasil! Mengalihkan ke dashboard...",
            'redirect' => $redirectUrl,
            'user' => [
                'username' => $user['username'],
                'role' => $user['role_name']
            ]
        ]);
        exit;
    }
}
