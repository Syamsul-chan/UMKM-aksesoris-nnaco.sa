<?php
require 'config/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Tambah kolom phone_number ke tabel users jika belum ada
    $checkCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone_number'")->fetchAll();
    if (empty($checkCol)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULL UNIQUE AFTER email");
        echo "Kolom 'phone_number' berhasil ditambahkan ke tabel users.\n";
    } else {
        echo "Kolom 'phone_number' sudah ada pada tabel users.\n";
    }

    // 2. Buat tabel otp_logs
    $sqlOtpLogs = "
    CREATE TABLE IF NOT EXISTS `otp_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NULL,
      `phone_number` VARCHAR(20) NOT NULL,
      `otp_hash` VARCHAR(255) NOT NULL,
      `expires_at` DATETIME NOT NULL,
      `is_used` TINYINT(1) NOT NULL DEFAULT 0,
      `attempts` INT NOT NULL DEFAULT 0,
      `ip_address` VARCHAR(45) NULL,
      `user_agent` TEXT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_phone_active` (`phone_number`, `is_used`, `expires_at`),
      CONSTRAINT `fk_otp_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlOtpLogs);
    echo "Tabel 'otp_logs' berhasil dibuat atau sudah ada.\n";

    // 3. Update contoh nomor telepon untuk akun yang ada
    // Admin / Owner
    $devicePhone = '08986817584'; // Nomor perangkat fonnte yang terhubung
    $pdo->exec("UPDATE users SET phone_number = '{$devicePhone}' WHERE id = 1 AND (phone_number IS NULL OR phone_number = '')");
    echo "Nomor WA uji coba {$devicePhone} disematkan ke user ID 1.\n";

} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
