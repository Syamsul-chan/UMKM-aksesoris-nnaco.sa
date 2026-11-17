<?php

class Otp_model {
    private $table = 'otp_logs';
    private $logTable = 'otp_audit_logs';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Cek apakah nomor telepon sedang dalam masa jeda / cooldown kirim ulang
     */
    public function checkCooldown($phone, $cooldownSeconds = 60) {
        $this->db->query("SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as diff_seconds FROM {$this->table} 
                          WHERE phone_number = :phone 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phone);
        $last = $this->db->single();

        if ($last && isset($last['diff_seconds'])) {
            $diff = (int)$last['diff_seconds'];
            if ($diff >= 0 && $diff < $cooldownSeconds) {
                return [
                    'in_cooldown' => true,
                    'remaining_seconds' => ($cooldownSeconds - $diff)
                ];
            }
        }

        return ['in_cooldown' => false, 'remaining_seconds' => 0];
    }

    /**
     * Cek rate limiting per nomor telepon dan IP address
     */
    public function checkRateLimit($phone, $ip, $maxPerHour = 5) {
        $this->db->query("SELECT COUNT(*) as count_phone FROM {$this->table} 
                          WHERE phone_number = :phone 
                          AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $this->db->bind('phone', $phone);
        $resPhone = $this->db->single();

        if ($resPhone && $resPhone['count_phone'] >= $maxPerHour) {
            return [
                'allowed' => false,
                'reason' => "Batas maksimal permintaan OTP ({$maxPerHour}x per jam) untuk nomor ini telah tercapai. Silakan coba lagi nanti."
            ];
        }

        if (!empty($ip)) {
            $this->db->query("SELECT COUNT(*) as count_ip FROM {$this->table} 
                              WHERE ip_address = :ip 
                              AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $this->db->bind('ip', $ip);
            $resIp = $this->db->single();

            // Batas per IP sedikit lebih longgar untuk mengakomodasi NAT/shared network (15x per jam)
            if ($resIp && $resIp['count_ip'] >= ($maxPerHour * 3)) {
                return [
                    'allowed' => false,
                    'reason' => "Terlalu banyak permintaan OTP dari alamat IP Anda. Silakan coba beberapa saat lagi."
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Buat OTP Challenge baru dengan hash BCRYPT
     * 
     * @return array ['success' => bool, 'plain_otp' => string, 'expires_at' => string, 'challenge_id' => int]
     */
    public function createChallenge($phone, $userId = null, $expirationMinutes = 5, $ip = null, $userAgent = null) {
        // 1. Batalkan semua OTP lama yang belum digunakan untuk nomor ini
        $this->db->query("UPDATE {$this->table} SET is_used = 1 
                          WHERE phone_number = :phone AND is_used = 0");
        $this->db->bind('phone', $phone);
        $this->db->execute();

        // 2. Generate OTP acak 6 digit secara kriptografis
        $plainOtp = (string)random_int(100000, 999999);

        // 3. Hash OTP menggunakan algoritma BCRYPT (Zero-Plaintext di database)
        $otpHashed = password_hash($plainOtp, PASSWORD_BCRYPT, ['cost' => 10]);

        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $userAgent = $userAgent ?? substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        // 4. Simpan ke database dengan DATE_ADD(NOW(), INTERVAL :exp_minutes MINUTE)
        $query = "INSERT INTO {$this->table} (
                    user_id, phone_number, otp_hash, expires_at, is_used, attempts, ip_address, user_agent
                  ) VALUES (
                    :user_id, :phone, :otp_hash, DATE_ADD(NOW(), INTERVAL :exp_minutes MINUTE), 0, 0, :ip, :ua
                  )";
        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('phone', $phone);
        $this->db->bind('otp_hash', $otpHashed);
        $this->db->bind('exp_minutes', (int)$expirationMinutes);
        $this->db->bind('ip', $ip);
        $this->db->bind('ua', $userAgent);
        $this->db->execute();

        $challengeId = $this->db->lastInsertId();

        return [
            'success' => true,
            'plain_otp' => $plainOtp,
            'expires_at' => date('Y-m-d H:i:s', time() + ($expirationMinutes * 60)),
            'challenge_id' => $challengeId
        ];
    }

    /**
     * Verifikasi kode OTP masukan pengguna secara aman
     *
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function verifyOtp($phone, $userInputOtp, $maxAttempts = 5) {
        // 1. Ambil OTP aktif terakhir untuk nomor ini dan cek expired secara native di database
        $this->db->query("SELECT *, (CASE WHEN expires_at < NOW() THEN 1 ELSE 0 END) as is_expired 
                          FROM {$this->table} 
                          WHERE phone_number = :phone 
                          AND is_used = 0 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phone);
        $challenge = $this->db->single();

        if (!$challenge) {
            return [
                'success' => false,
                'message' => 'Tidak ada permintaan OTP aktif untuk nomor ini. Silakan minta kode OTP baru.'
            ];
        }

        // 2. Cek apakah sudah kedaluwarsa
        if (!empty($challenge['is_expired'])) {
            $this->db->query("UPDATE {$this->table} SET is_used = 1 WHERE id = :id");
            $this->db->bind('id', $challenge['id']);
            $this->db->execute();

            return [
                'success' => false,
                'message' => 'Kode OTP telah kedaluwarsa. Silakan minta kode OTP baru.'
            ];
        }

        // 3. Cek batas percobaan salah (mitigasi brute-force)
        if ($challenge['attempts'] >= $maxAttempts) {
            $this->db->query("UPDATE {$this->table} SET is_used = 1 WHERE id = :id");
            $this->db->bind('id', $challenge['id']);
            $this->db->execute();

            return [
                'success' => false,
                'message' => "Batas maksimal percobaan salah ({$maxAttempts}x) telah terlampaui. Kode OTP ini dibatalkan demi keamanan."
            ];
        }

        // 4. Tambah counter attempts salah terlebih dahulu (Atomic tracking)
        $newAttempts = $challenge['attempts'] + 1;
        $this->db->query("UPDATE {$this->table} SET attempts = :attempts WHERE id = :id");
        $this->db->bind('attempts', $newAttempts);
        $this->db->bind('id', $challenge['id']);
        $this->db->execute();

        // 5. Verifikasi hash secara timing-safe
        if (!password_verify((string)$userInputOtp, $challenge['otp_hash'])) {
            $remaining = $maxAttempts - $newAttempts;
            if ($remaining <= 0) {
                // Invalidate
                $this->db->query("UPDATE {$this->table} SET is_used = 1 WHERE id = :id");
                $this->db->bind('id', $challenge['id']);
                $this->db->execute();
                return [
                    'success' => false,
                    'message' => 'Kode OTP salah. Batas percobaan habis. Silakan minta kode baru.'
                ];
            }
            return [
                'success' => false,
                'message' => "Kode OTP salah. Sisa kesempatan mencoba: {$remaining}x."
            ];
        }

        // 6. Valid! Tandai OTP sudah digunakan (Single-Use Guarantee)
        $this->db->query("UPDATE {$this->table} SET is_used = 1 WHERE id = :id");
        $this->db->bind('id', $challenge['id']);
        $this->db->execute();

        // 7. Ambil data User dari database
        require_once __DIR__ . '/User_model.php';
        $userModel = new User_model();
        $user = $userModel->getUserByPhone($phone);

        return [
            'success' => true,
            'message' => 'Verifikasi kode OTP berhasil.',
            'user' => $user
        ];
    }

    /**
     * Catat audit log autentikasi OTP
     */
    public function logAction($userId, $phoneMasked, $action, $status, $details, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $query = "INSERT INTO {$this->logTable} (
                    user_id, phone_masked, action, status, details, ip_address
                  ) VALUES (
                    :user_id, :phone, :action, :status, :details, :ip
                  )";
        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('phone', $phoneMasked);
        $this->db->bind('action', strtoupper($action));
        $this->db->bind('status', strtoupper($status));
        $this->db->bind('details', $details);
        $this->db->bind('ip', $ip);
        $this->db->execute();
    }

    /**
     * Ambil riwayat audit log autentikasi OTP
     */
    public function getAuditLogs($limit = 20) {
        $this->db->query("SELECT * FROM {$this->logTable} ORDER BY created_at DESC LIMIT :limit");
        $this->db->bind('limit', (int)$limit);
        return $this->db->resultSet();
    }
}
