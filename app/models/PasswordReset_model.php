<?php

class PasswordReset_model {
    private $table = 'password_resets';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Cek cooldown permintaan reset password
     */
    public function checkCooldown($identity, $cooldownSeconds = 60) {
        $this->db->query("SELECT created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago 
                          FROM {$this->table} 
                          WHERE identity = :identity 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('identity', $identity);
        $lastReq = $this->db->single();

        if ($lastReq && isset($lastReq['seconds_ago'])) {
            $secondsAgo = (int)$lastReq['seconds_ago'];
            if ($secondsAgo < $cooldownSeconds) {
                return [
                    'in_cooldown' => true,
                    'remaining_seconds' => ($cooldownSeconds - $secondsAgo)
                ];
            }
        }

        return ['in_cooldown' => false, 'remaining_seconds' => 0];
    }

    /**
     * Buat kode verifikasi reset kata sandi baru (Zero-Plaintext di database)
     */
    public function createResetChallenge($userId, $identity, $expMinutes = 15, $ip = null) {
        // 1. Nonaktifkan kode lama yang belum terpakai untuk identitas ini
        $this->db->query("UPDATE {$this->table} SET is_used = 1 
                          WHERE identity = :identity AND is_used = 0");
        $this->db->bind('identity', $identity);
        $this->db->execute();

        // 2. Buat kode verifikasi 6 digit acak
        $plainCode = (string)random_int(100000, 999999);

        // 3. Hash kode dengan algoritma BCRYPT
        $hash = password_hash($plainCode, PASSWORD_BCRYPT);
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        // 4. Simpan ke database
        $query = "INSERT INTO {$this->table} (user_id, identity, token_hash, expires_at, is_used, attempts, ip_address) 
                  VALUES (:user_id, :identity, :token_hash, DATE_ADD(NOW(), INTERVAL :exp_minutes MINUTE), 0, 0, :ip)";
        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('identity', $identity);
        $this->db->bind('token_hash', $hash);
        $this->db->bind('exp_minutes', (int)$expMinutes);
        $this->db->bind('ip', $ip);
        $this->db->execute();

        return [
            'success' => true,
            'plain_code' => $plainCode,
            'challenge_id' => $this->db->lastInsertId(),
            'expires_in_sec' => ($expMinutes * 60)
        ];
    }

    /**
     * Verifikasi kode 6-digit reset password
     */
    public function verifyResetChallenge($identity, $inputCode, $maxAttempts = 5) {
        $this->db->query("SELECT *, 
                          (expires_at <= NOW()) as is_expired 
                          FROM {$this->table} 
                          WHERE identity = :identity AND is_used = 0 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('identity', $identity);
        $challenge = $this->db->single();

        if (!$challenge) {
            return [
                'success' => false,
                'message' => 'Permintaan reset kata sandi tidak ditemukan atau kode sudah tidak berlaku. Silakan minta kode baru.'
            ];
        }

        // Cek apakah sudah kedaluwarsa
        if (!empty($challenge['is_expired'])) {
            return [
                'success' => false,
                'message' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode verifikasi baru.'
            ];
        }

        // Cek batas percobaan
        $attempts = (int)$challenge['attempts'];
        if ($attempts >= $maxAttempts) {
            $this->markUsed($challenge['id']);
            return [
                'success' => false,
                'message' => 'Batas percobaan salah telah habis. Demi keamanan akun, silakan minta kode verifikasi baru.'
            ];
        }

        // Verifikasi hash kriptografis BCRYPT
        if (!password_verify($inputCode, $challenge['token_hash'])) {
            $newAttempts = $attempts + 1;
            $remaining = $maxAttempts - $newAttempts;

            $this->db->query("UPDATE {$this->table} SET attempts = :attempts WHERE id = :id");
            $this->db->bind('attempts', $newAttempts);
            $this->db->bind('id', $challenge['id']);
            $this->db->execute();

            if ($remaining <= 0) {
                $this->markUsed($challenge['id']);
                return [
                    'success' => false,
                    'message' => 'Kode verifikasi salah. Batas percobaan habis. Silakan minta kode baru.'
                ];
            }

            return [
                'success' => false,
                'message' => "Kode verifikasi salah. Sisa kesempatan mencoba: {$remaining}x."
            ];
        }

        // Kode valid
        return [
            'success' => true,
            'record_id' => $challenge['id'],
            'user_id' => $challenge['user_id']
        ];
    }

    /**
     * Tandai token telah digunakan
     */
    public function markUsed($id) {
        $this->db->query("UPDATE {$this->table} SET is_used = 1 WHERE id = :id");
        $this->db->bind('id', (int)$id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
