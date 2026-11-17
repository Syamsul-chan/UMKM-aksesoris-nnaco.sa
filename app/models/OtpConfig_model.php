<?php

class OtpConfig_model {
    private $table = 'whatsapp_otp_configs';
    private $db;
    private $cipher = 'aes-256-cbc';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Enkripsi Access Token dengan AES-256-CBC
     */
    public function encryptToken($plainToken) {
        if (empty($plainToken)) return '';
        $key = defined('AI_ENCRYPTION_KEY') ? AI_ENCRYPTION_KEY : 'nNaCo_Sa_Ai_SecRet_KeY_2026_99x8';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($plainToken, $this->cipher, hash('sha256', $key, true), 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Dekripsi Access Token dari database
     */
    public function decryptToken($encryptedToken) {
        if (empty($encryptedToken)) return '';
        $key = defined('AI_ENCRYPTION_KEY') ? AI_ENCRYPTION_KEY : 'nNaCo_Sa_Ai_SecRet_KeY_2026_99x8';
        $data = base64_decode($encryptedToken);
        if (!$data || strpos($data, '::') === false) return '';
        list($iv, $cipherText) = explode('::', $data, 2);
        return openssl_decrypt($cipherText, $this->cipher, hash('sha256', $key, true), 0, $iv);
    }

    /**
     * Maskir token untuk tampilan UI agar aman
     */
    public function maskToken($token) {
        if (empty($token)) return 'Belum Dikonfigurasi';
        $len = strlen($token);
        if ($len <= 8) return '••••••••';
        return substr($token, 0, 4) . '••••••••••••••••' . substr($token, -4);
    }

    /**
     * Ambil konfigurasi WhatsApp OTP saat ini
     */
    public function getConfig() {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1");
        $config = $this->db->single();

        if ($config) {
            $decrypted = $this->decryptToken($config['access_token']);
            $config['decrypted_token'] = $decrypted;
            $config['masked_token'] = $this->maskToken($decrypted);
        } else {
            // Default fallback
            $config = [
                'id' => null,
                'provider' => 'dev_mock',
                'api_url' => 'https://api.fonnte.com/send',
                'access_token' => '',
                'decrypted_token' => '',
                'masked_token' => 'Belum Dikonfigurasi',
                'phone_number_id' => '',
                'waba_id' => '',
                'template_name' => 'nnaco_auth_otp',
                'otp_expiration_minutes' => 5,
                'resend_cooldown_seconds' => 60,
                'max_attempts' => 5,
                'rate_limit_per_hour' => 5,
                'is_active' => 1,
                'is_dev_mode' => 1
            ];
        }
        return $config;
    }

    /**
     * Update atau simpan konfigurasi WhatsApp OTP
     */
    public function updateConfig($data) {
        $existing = $this->getConfig();
        $provider = strtolower(trim($data['provider'] ?? 'fonnte'));
        $apiUrl = trim($data['api_url'] ?? 'https://api.fonnte.com/send');
        $phoneId = trim($data['phone_number_id'] ?? '');
        $wabaId = trim($data['waba_id'] ?? '');
        $template = trim($data['template_name'] ?? 'nnaco_auth_otp');
        $expMinutes = max(1, min(60, (int)($data['otp_expiration_minutes'] ?? 5)));
        $cooldownSec = max(10, min(300, (int)($data['resend_cooldown_seconds'] ?? 60)));
        $maxAttempts = max(1, min(10, (int)($data['max_attempts'] ?? 5)));
        $rateLimit = max(1, min(30, (int)($data['rate_limit_per_hour'] ?? 5)));
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $isDevMode = isset($data['is_dev_mode']) ? (int)$data['is_dev_mode'] : 0;

        // Token: jika diisi yang baru, enkripsi. Jika kosong, pertahankan yang lama.
        $tokenToSave = $existing['access_token'] ?? '';
        if (!empty($data['access_token']) && $data['access_token'] !== '••••••••••••••••') {
            $tokenToSave = $this->encryptToken(trim($data['access_token']));
        }

        if ($existing && !empty($existing['id'])) {
            $query = "UPDATE {$this->table} SET 
                        provider = :provider,
                        api_url = :api_url,
                        access_token = :access_token,
                        phone_number_id = :phone_id,
                        waba_id = :waba_id,
                        template_name = :template,
                        otp_expiration_minutes = :exp_minutes,
                        resend_cooldown_seconds = :cooldown_sec,
                        max_attempts = :max_attempts,
                        rate_limit_per_hour = :rate_limit,
                        is_active = :is_active,
                        is_dev_mode = :is_dev_mode,
                        updated_at = NOW()
                      WHERE id = :id";
            $this->db->query($query);
            $this->db->bind('id', (int)$existing['id']);
        } else {
            $query = "INSERT INTO {$this->table} (
                        provider, api_url, access_token, phone_number_id, waba_id, template_name,
                        otp_expiration_minutes, resend_cooldown_seconds, max_attempts, rate_limit_per_hour,
                        is_active, is_dev_mode
                      ) VALUES (
                        :provider, :api_url, :access_token, :phone_id, :waba_id, :template,
                        :exp_minutes, :cooldown_sec, :max_attempts, :rate_limit,
                        :is_active, :is_dev_mode
                      )";
            $this->db->query($query);
        }

        $this->db->bind('provider', $provider);
        $this->db->bind('api_url', $apiUrl);
        $this->db->bind('access_token', $tokenToSave);
        $this->db->bind('phone_id', $phoneId);
        $this->db->bind('waba_id', $wabaId);
        $this->db->bind('template', $template);
        $this->db->bind('exp_minutes', $expMinutes);
        $this->db->bind('cooldown_sec', $cooldownSec);
        $this->db->bind('max_attempts', $maxAttempts);
        $this->db->bind('rate_limit', $rateLimit);
        $this->db->bind('is_active', $isActive);
        $this->db->bind('is_dev_mode', $isDevMode);

        $this->db->execute();
        return $this->db->rowCount() >= 0;
    }

    /**
     * Ambil ringkasan status integrasi WhatsApp OTP untuk dashboard (Admin & Owner)
     */
    public function getIntegrationStatus() {
        $config = $this->getConfig();
        
        // Ambil pengujian koneksi terakhir dari otp_audit_logs
        $this->db->query("SELECT * FROM otp_audit_logs WHERE action = 'TEST_CONNECTION' ORDER BY created_at DESC LIMIT 1");
        $lastTest = $this->db->single();
        
        // Ambil error terakhir dari audit logs jika ada
        $this->db->query("SELECT * FROM otp_audit_logs WHERE status = 'FAILED' ORDER BY created_at DESC LIMIT 1");
        $lastError = $this->db->single();

        // Hitung metrik OTP dari otp_logs
        $this->db->query("SELECT 
            COUNT(*) as total_requested,
            SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as total_verified,
            SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) as today_requested
        FROM otp_logs");
        $metrics = $this->db->single();

        $totalReq = (int)($metrics['total_requested'] ?? 0);
        $totalVer = (int)($metrics['total_verified'] ?? 0);
        $successRate = $totalReq > 0 ? round(($totalVer / $totalReq) * 100, 1) : 100;

        $providerName = 'Fonnte';
        if (($config['provider'] ?? '') === 'meta_cloud') {
            $providerName = 'Meta Cloud API';
        } elseif (($config['provider'] ?? '') === 'web_system') {
            $providerName = 'Sistem Web Langsung';
        } elseif (($config['provider'] ?? '') === 'dev_mock') {
            $providerName = 'Simulasi Dev';
        }

        // Sanitasi pesan error agar aman
        $safeError = null;
        if ($lastError && !empty($lastError['details'])) {
            $rawError = $lastError['details'];
            $safeError = preg_replace('/(Bearer\s+)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $rawError);
            $safeError = preg_replace('/(token[=:]\s*)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $safeError);
            $safeError = htmlspecialchars(substr($safeError, 0, 180));
        }

        $isActive = !empty($config['is_active']);
        $isDevMode = !empty($config['is_dev_mode']);

        $statusLabel = 'Nonaktif';
        $statusBadge = 'secondary';
        if ($isActive) {
            if ($isDevMode) {
                $statusLabel = 'Mode Dev (Simulasi)';
                $statusBadge = 'warning';
            } else {
                $statusLabel = 'Aktif Live';
                $statusBadge = 'success';
            }
        }

        return [
            'masked_token' => $config['masked_token'] ?? '••••••••',
            'is_active' => $isActive,
            'is_dev_mode' => $isDevMode,
            'provider' => $providerName,
            'provider_code' => $config['provider'] ?? 'fonnte',
            'status_label' => $statusLabel,
            'status_badge' => $statusBadge,
            'last_test' => $lastTest,
            'last_test_time' => !empty($lastTest['created_at']) ? date('d M Y, H:i', strtotime($lastTest['created_at'])) . ' WIB' : 'Belum pernah diuji',
            'last_test_status' => $lastTest['status'] ?? 'PENDING',
            'last_test_details' => $lastTest['details'] ?? null,
            'last_error' => $safeError,
            'total_requested' => $totalReq,
            'total_verified' => $totalVer,
            'today_requested' => (int)($metrics['today_requested'] ?? 0),
            'success_rate' => $successRate,
            'cooldown_sec' => (int)($config['resend_cooldown_seconds'] ?? 60),
            'exp_minutes' => (int)($config['otp_expiration_minutes'] ?? 5)
        ];
    }
}
