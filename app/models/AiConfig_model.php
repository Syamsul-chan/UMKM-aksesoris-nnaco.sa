<?php

class AiConfig_model {
    private $table = 'ai_configurations';
    private $logTable = 'ai_config_logs';
    private $db;
    private $encryptionKey;

    public function __construct() {
        $this->db = new Database;
        $this->encryptionKey = defined('AI_ENCRYPTION_KEY') ? AI_ENCRYPTION_KEY : 'nnaco_sa_sec_ai_key_2026_99x8';
    }

    /**
     * Enkripsi API Key menggunakan AES-256-CBC
     */
    public function encryptKey($plainText) {
        if (empty($plainText)) return '';
        $iv = openssl_random_pseudo_bytes(16);
        $cipher = openssl_encrypt($plainText, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . '::' . $cipher);
    }

    /**
     * Dekripsi API Key
     */
    public function decryptKey($cipherText) {
        if (empty($cipherText)) return '';
        $raw = base64_decode($cipherText, true);
        if ($raw === false || strpos($raw, '::') === false) {
            // Jika data belum terenkripsi (plain text lama), kembalikan langsung
            return $cipherText;
        }
        list($iv, $cipher) = explode('::', $raw, 2);
        $decrypted = openssl_decrypt($cipher, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return ($decrypted !== false) ? $decrypted : '';
    }

    /**
     * Samarkan API Key untuk keamanan tampilan frontend (Masking)
     * Contoh: gsk_gsA4••••••••••••••••••••••••49hKMa
     */
    public function maskKey($key) {
        if (empty($key)) return 'Belum Diatur';
        $decrypted = $this->decryptKey($key);
        $len = strlen($decrypted);
        if ($len <= 8) {
            return str_repeat('•', 8);
        }
        $prefix = substr($decrypted, 0, 7);
        $suffix = substr($decrypted, -5);
        return $prefix . '••••••••••••••••' . $suffix;
    }

    /**
     * Ambil seluruh konfigurasi AI yang tersimpan
     */
    public function getAllConfigs() {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY is_primary DESC, is_active DESC, updated_at DESC");
        $results = $this->db->resultSet();
        foreach ($results as &$r) {
            $r['masked_key'] = $this->maskKey($r['api_key']);
            $r['has_key'] = !empty($r['api_key']);
        }
        return $results;
    }

    /**
     * Ambil satu konfigurasi berdasarkan ID
     */
    public function getConfigById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', (int)$id);
        $result = $this->db->single();
        if ($result) {
            $result['masked_key'] = $this->maskKey($result['api_key']);
            $result['has_key'] = !empty($result['api_key']);
        }
        return $result;
    }

    /**
     * Ambil konfigurasi utama (Primary & Active) yang sedang digunakan oleh sistem
     */
    public function getPrimaryConfig() {
        $this->db->query("SELECT * FROM {$this->table} WHERE is_primary = 1 AND is_active = 1 LIMIT 1");
        $result = $this->db->single();
        if (!$result) {
            // Fallback ke konfigurasi aktif pertama
            $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
            $result = $this->db->single();
        }
        if ($result) {
            $result['decrypted_key'] = $this->decryptKey($result['api_key']);
            $result['masked_key'] = $this->maskKey($result['api_key']);
        }
        return $result;
    }

    /**
     * Tambah konfigurasi baru
     */
    public function createConfig($data) {
        // Enkripsi API Key jika ada
        $encryptedKey = !empty($data['api_key']) ? $this->encryptKey(trim($data['api_key'])) : null;
        $isPrimary = !empty($data['is_primary']) ? 1 : 0;
        $isActive = !empty($data['is_active']) ? 1 : 0;

        // Jika diset sebagai primary, otomatis harus aktif
        if ($isPrimary) {
            $isActive = 1;
            // Nonaktifkan primary lama
            $this->db->query("UPDATE {$this->table} SET is_primary = 0");
            $this->db->execute();
        }

        $query = "INSERT INTO {$this->table} (
            name, provider, model_name, api_url, api_key, temperature, max_tokens, top_p, 
            timeout_seconds, max_retries, system_prompt_customer, system_prompt_insight, 
            is_active, is_primary, created_by
        ) VALUES (
            :name, :provider, :model_name, :api_url, :api_key, :temperature, :max_tokens, :top_p,
            :timeout_seconds, :max_retries, :prompt_cust, :prompt_insight,
            :is_active, :is_primary, :created_by
        )";

        $this->db->query($query);
        $this->db->bind('name', trim($data['name']));
        $this->db->bind('provider', strtolower(trim($data['provider'])));
        $this->db->bind('model_name', trim($data['model_name']));
        $this->db->bind('api_url', trim($data['api_url']));
        $this->db->bind('api_key', $encryptedKey);
        $this->db->bind('temperature', (float)($data['temperature'] ?? 0.70));
        $this->db->bind('max_tokens', (int)($data['max_tokens'] ?? 800));
        $this->db->bind('top_p', (float)($data['top_p'] ?? 1.00));
        $this->db->bind('timeout_seconds', (int)($data['timeout_seconds'] ?? 30));
        $this->db->bind('max_retries', (int)($data['max_retries'] ?? 2));
        $this->db->bind('prompt_cust', !empty($data['system_prompt_customer']) ? trim($data['system_prompt_customer']) : null);
        $this->db->bind('prompt_insight', !empty($data['system_prompt_insight']) ? trim($data['system_prompt_insight']) : null);
        $this->db->bind('is_active', $isActive);
        $this->db->bind('is_primary', $isPrimary);
        $this->db->bind('created_by', (int)($data['created_by'] ?? ($_SESSION['user']['id'] ?? null)));

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update konfigurasi
     */
    public function updateConfig($id, $data) {
        $existing = $this->getConfigById($id);
        if (!$existing) return false;

        $isPrimary = isset($data['is_primary']) ? (int)$data['is_primary'] : $existing['is_primary'];
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : $existing['is_active'];

        // Jika diset sebagai primary, otomatis aktif dan hapus status primary konfigurasi lain
        if ($isPrimary && !$existing['is_primary']) {
            $isActive = 1;
            $this->db->query("UPDATE {$this->table} SET is_primary = 0 WHERE id != :id");
            $this->db->bind('id', (int)$id);
            $this->db->execute();
        }

        // Tentukan API Key: jika user mengisi API key baru, enkripsi. Jika kosong, pertahankan yang lama
        $apiKeyToSave = $existing['api_key'];
        if (!empty($data['api_key']) && trim($data['api_key']) !== '••••••••••••••••') {
            $apiKeyToSave = $this->encryptKey(trim($data['api_key']));
        }

        $query = "UPDATE {$this->table} SET 
            name = :name,
            provider = :provider,
            model_name = :model_name,
            api_url = :api_url,
            api_key = :api_key,
            temperature = :temperature,
            max_tokens = :max_tokens,
            top_p = :top_p,
            timeout_seconds = :timeout_seconds,
            max_retries = :max_retries,
            system_prompt_customer = :prompt_cust,
            system_prompt_insight = :prompt_insight,
            is_active = :is_active,
            is_primary = :is_primary
        WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', (int)$id);
        $this->db->bind('name', trim($data['name'] ?? $existing['name']));
        $this->db->bind('provider', strtolower(trim($data['provider'] ?? $existing['provider'])));
        $this->db->bind('model_name', trim($data['model_name'] ?? $existing['model_name']));
        $this->db->bind('api_url', trim($data['api_url'] ?? $existing['api_url']));
        $this->db->bind('api_key', $apiKeyToSave);
        $this->db->bind('temperature', (float)($data['temperature'] ?? $existing['temperature']));
        $this->db->bind('max_tokens', (int)($data['max_tokens'] ?? $existing['max_tokens']));
        $this->db->bind('top_p', (float)($data['top_p'] ?? $existing['top_p']));
        $this->db->bind('timeout_seconds', (int)($data['timeout_seconds'] ?? $existing['timeout_seconds']));
        $this->db->bind('max_retries', (int)($data['max_retries'] ?? $existing['max_retries']));
        $this->db->bind('prompt_cust', isset($data['system_prompt_customer']) ? trim($data['system_prompt_customer']) : $existing['system_prompt_customer']);
        $this->db->bind('prompt_insight', isset($data['system_prompt_insight']) ? trim($data['system_prompt_insight']) : $existing['system_prompt_insight']);
        $this->db->bind('is_active', $isActive);
        $this->db->bind('is_primary', $isPrimary);

        $this->db->execute();
        return $this->db->rowCount() >= 0;
    }

    /**
     * Jadikan satu konfigurasi sebagai model AI Utama (Primary Active Model)
     */
    public function setPrimaryConfig($id) {
        $config = $this->getConfigById($id);
        if (!$config) {
            return ['success' => false, 'message' => 'Konfigurasi tidak ditemukan.'];
        }

        try {
            // 1. Reset semua is_primary ke 0
            $this->db->query("UPDATE {$this->table} SET is_primary = 0");
            $this->db->execute();

            // 2. Set konfigurasi terpilih menjadi primary dan pastikan is_active = 1
            $this->db->query("UPDATE {$this->table} SET is_primary = 1, is_active = 1 WHERE id = :id");
            $this->db->bind('id', (int)$id);
            $this->db->execute();

            return [
                'success' => true,
                'message' => "Model '{$config['name']}' ({$config['model_name']}) berhasil diaktifkan sebagai Model AI Utama toko.",
                'config' => $config
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal mengubah model utama: ' . $e->getMessage()];
        }
    }

    /**
     * Toggle status aktif/nonaktif
     */
    public function toggleActive($id, $status) {
        $config = $this->getConfigById($id);
        if (!$config) {
            return ['success' => false, 'message' => 'Konfigurasi tidak ditemukan.'];
        }

        // Cegah menonaktifkan konfigurasi yang sedang menjadi Model Utama
        if ($config['is_primary'] == 1 && $status == 0) {
            return [
                'success' => false,
                'message' => 'Konfigurasi ini sedang menjadi Model AI Utama yang aktif. Silakan pilih konfigurasi lain sebagai Model Utama terlebih dahulu sebelum menonaktifkan ini.'
            ];
        }

        $this->db->query("UPDATE {$this->table} SET is_active = :status WHERE id = :id");
        $this->db->bind('status', (int)$status);
        $this->db->bind('id', (int)$id);
        $this->db->execute();

        $label = ($status == 1) ? 'diaktifkan' : 'dinonaktifkan';
        return [
            'success' => true,
            'message' => "Konfigurasi '{$config['name']}' berhasil {$label}.",
            'new_status' => (int)$status
        ];
    }

    /**
     * Hapus konfigurasi
     */
    public function deleteConfig($id) {
        $config = $this->getConfigById($id);
        if (!$config) {
            return ['success' => false, 'message' => 'Konfigurasi tidak ditemukan.'];
        }

        if ($config['is_primary'] == 1) {
            return [
                'success' => false,
                'message' => 'Konfigurasi ini tidak dapat dihapus karena sedang aktif sebagai Model AI Utama. Silakan tetapkan konfigurasi lain sebagai Model Utama terlebih dahulu.'
            ];
        }

        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', (int)$id);
        $this->db->execute();

        return [
            'success' => true,
            'message' => "Konfigurasi '{$config['name']}' berhasil dihapus."
        ];
    }

    /**
     * Uji Koneksi ke Provider AI secara Realtime (Test Connection)
     */
    public function testConnection($provider, $apiUrl, $apiKey, $model, $temperature = 0.5, $maxTokens = 25, $timeout = 15) {
        // Dekripsi jika kunci yang diberikan berupa ciphertext dari DB
        $plainApiKey = $this->decryptKey($apiKey);
        if (empty($plainApiKey)) {
            $plainApiKey = $apiKey; // Plain input dari modal
        }

        if (empty($plainApiKey) || $plainApiKey === 'mock') {
            return [
                'success' => false,
                'http_code' => 0,
                'latency_ms' => 0,
                'reply' => '',
                'error_message' => 'API Key belum diisi atau tidak valid. Masukkan API Key yang benar.'
            ];
        }

        $provider = strtolower($provider);
        $startTime = microtime(true);

        // Siapkan Payload & Header sesuai Provider
        if ($provider === 'anthropic') {
            $payload = [
                'model' => $model,
                'max_tokens' => max(15, (int)$maxTokens),
                'messages' => [
                    ['role' => 'user', 'content' => 'Pilihlah satu kata: PONG.']
                ]
            ];
            $headers = [
                'Content-Type: application/json',
                'x-api-key: ' . $plainApiKey,
                'anthropic-version: 2023-06-01'
            ];
        } else {
            // OpenAI, Groq, dan OpenAI-Compatible default
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a connection test agent. Respond only with the exact word: PONG.'],
                    ['role' => 'user', 'content' => 'Ping!']
                ],
                'temperature' => (float)$temperature,
                'max_tokens' => max(15, (int)$maxTokens)
            ];
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $plainApiKey
            ];
        }

        $ch = curl_init(trim($apiUrl));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, max(15, (int)$timeout));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $endTime = microtime(true);
        $latencyMs = round(($endTime - $startTime) * 1000);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($response)) {
            $data = json_decode($response, true);
            $reply = '';

            if (isset($data['choices'][0]['message']['content'])) {
                $reply = trim($data['choices'][0]['message']['content']);
            } elseif (isset($data['content'][0]['text'])) { // Anthropic format
                $reply = trim($data['content'][0]['text']);
            } else {
                $reply = 'Koneksi Berhasil (Format respon valid).';
            }

            return [
                'success' => true,
                'http_code' => $httpCode,
                'latency_ms' => $latencyMs,
                'reply' => $reply,
                'error_message' => null,
                'provider' => $provider,
                'model' => $model
            ];
        }

        // Sanitasi pesan error & berikan panduan solutif spesifik (Jangan pernah membocorkan API key!)
        $parsed = json_decode($response, true);
        $providerErrorMsg = $parsed['error']['message'] ?? '';
        $providerErrorCode = $parsed['error']['code'] ?? '';

        $safeError = "HTTP {$httpCode}: ";
        if ($httpCode == 401) {
            if (strpos($apiUrl, 'anthropic.com') !== false && (strpos($plainApiKey, 'sk-proj-') !== false || strpos($plainApiKey, 'sk-') === 0)) {
                $safeError .= "Mismatch Provider! Anda memasukkan API Key OpenAI (sk-proj-...), namun URL Endpoint diarahkan ke Anthropic (Claude). Mohon ubah Provider ke 'OpenAI' dan Base URL ke 'https://api.openai.com/v1/chat/completions'.";
            } elseif (strpos($apiUrl, 'openai.com') !== false && strpos($plainApiKey, 'gsk_') === 0) {
                $safeError .= "Mismatch Provider! Anda memasukkan API Key Groq (gsk_...), namun URL Endpoint diarahkan ke OpenAI. Mohon ubah Provider ke 'Groq'.";
            } elseif (!empty($providerErrorMsg)) {
                $safeError .= "Autentikasi gagal. " . htmlspecialchars($providerErrorMsg);
            } else {
                $safeError .= "Autentikasi gagal. API Key tidak valid atau telah kadaluarsa.";
            }
        } elseif ($httpCode == 429) {
            if ($providerErrorCode === 'credit_balance_exhausted' || strpos($providerErrorMsg, 'credits remaining') !== false || strpos($providerErrorMsg, 'quota') !== false) {
                $safeError .= "Saldo / Kredit Akun OpenAI Anda $0 (credit_balance_exhausted). OpenAI memerlukan top-up billing minimal $5 di https://platform.openai.com/settings/organization/billing agar API Key dapat digunakan.";
            } elseif (!empty($providerErrorMsg)) {
                $safeError .= "Rate limit / Quota issue: " . htmlspecialchars($providerErrorMsg);
            } else {
                $safeError .= "Rate limit tercapai (Kuota API habis atau terlalu banyak permintaan).";
            }
        } elseif ($httpCode == 404) {
            $safeError .= "Endpoint API atau model '{$model}' tidak ditemukan di provider.";
            if (!empty($providerErrorMsg)) {
                $safeError .= " (" . htmlspecialchars($providerErrorMsg) . ")";
            }
        } elseif (!empty($error)) {
            $safeError .= "Koneksi cURL gagal ({$error}).";
        } else {
            if (!empty($providerErrorMsg)) {
                $safeError .= htmlspecialchars(substr($providerErrorMsg, 0, 200));
            } else {
                $safeError .= "Layanan provider menolak permintaan koneksi.";
            }
        }

        return [
            'success' => false,
            'http_code' => $httpCode,
            'latency_ms' => $latencyMs,
            'reply' => '',
            'error_message' => $safeError,
            'provider' => $provider,
            'model' => $model
        ];
    }

    /**
     * Catat riwayat perubahan ke tabel audit log
     */
    public function logAction($userId, $userName, $action, $configId, $configName, $status, $details, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $query = "INSERT INTO {$this->logTable} (
            user_id, user_name, action, config_id, config_name, status, details, ip_address
        ) VALUES (
            :user_id, :user_name, :action, :config_id, :config_name, :status, :details, :ip
        )";
        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('user_name', $userName);
        $this->db->bind('action', strtoupper($action));
        $this->db->bind('config_id', $configId);
        $this->db->bind('config_name', $configName);
        $this->db->bind('status', strtoupper($status));
        $this->db->bind('details', $details);
        $this->db->bind('ip', $ip);
        $this->db->execute();
    }

    /**
     * Ambil riwayat audit log terbaru
     */
    public function getAuditLogs($limit = 15) {
        $this->db->query("SELECT * FROM {$this->logTable} ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind('limit', (int)$limit);
        return $this->db->resultSet();
    }

    /**
     * Ambil ringkasan status integrasi AI untuk dashboard (Admin & Owner)
     */
    public function getIntegrationStatus() {
        $primary = $this->getPrimaryConfig();
        
        // Ambil pengujian koneksi terakhir
        $this->db->query("SELECT * FROM {$this->logTable} WHERE action = 'TEST_CONNECTION' ORDER BY created_at DESC LIMIT 1");
        $lastTest = $this->db->single();
        
        // Ambil riwayat error/gagal terakhir jika ada
        $this->db->query("SELECT * FROM {$this->logTable} WHERE status = 'FAILED' ORDER BY created_at DESC LIMIT 1");
        $lastError = $this->db->single();
        
        // Hitung total aktivitas / log AI
        $this->db->query("SELECT COUNT(*) as total FROM {$this->logTable}");
        $totalLogs = $this->db->single()['total'] ?? 0;

        // Hitung total model terdaftar
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $totalModels = $this->db->single()['total'] ?? 0;

        $hasPrimary = !empty($primary);
        $isActive = $hasPrimary && !empty($primary['is_active']);

        // Latency ekstraksi dari details
        $latency = null;
        if (!empty($lastTest['details']) && preg_match('/(\d+)\s*ms/', $lastTest['details'], $matches)) {
            $latency = $matches[1] . ' ms';
        }

        // Sanitasi pesan error agar aman (hilangkan kredensial jika ada)
        $safeError = null;
        if ($lastError && !empty($lastError['details'])) {
            $rawError = $lastError['details'];
            $safeError = preg_replace('/(Bearer\s+)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $rawError);
            $safeError = preg_replace('/(key[=:]\s*)[A-Za-z0-9_\-\.]{8,}/i', '$1••••••••', $safeError);
            $safeError = htmlspecialchars(substr($safeError, 0, 180));
        }

        return [
            'has_config' => $hasPrimary,
            'is_active' => $isActive,
            'primary_id' => $primary['id'] ?? null,
            'name' => $primary['name'] ?? 'Belum Ada Model Aktif',
            'provider' => !empty($primary['provider']) ? ucfirst($primary['provider']) : 'Belum Dikonfigurasi',
            'provider_code' => strtolower($primary['provider'] ?? ''),
            'model_name' => $primary['model_name'] ?? 'openai/gpt-oss-20b',
            'temperature' => $primary['temperature'] ?? 0.70,
            'max_tokens' => $primary['max_tokens'] ?? 800,
            'last_test' => $lastTest,
            'last_test_time' => !empty($lastTest['created_at']) ? date('d M Y, H:i', strtotime($lastTest['created_at'])) . ' WIB' : 'Belum pernah diuji',
            'last_test_status' => $lastTest['status'] ?? 'PENDING',
            'last_test_latency' => $latency ?? ($isActive ? 'Normal (< 1s)' : '-'),
            'last_error' => $safeError,
            'total_logs' => (int)$totalLogs,
            'total_models' => (int)$totalModels,
            'customer_assistant_status' => $isActive ? 'Aktif Siap Melayani' : 'Dinonaktifkan',
            'business_insight_status' => $isActive ? 'Aktif Siap Analisis' : 'Dinonaktifkan'
        ];
    }
}
