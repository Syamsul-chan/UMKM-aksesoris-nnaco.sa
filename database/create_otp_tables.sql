-- =========================================================================
-- SQL Migration: Sistem Autentikasi WhatsApp OTP & Pengaturan Gateway
-- Proyek: nnaco.sa Store
-- =========================================================================

-- 1. Pastikan kolom phone_number ada di tabel users
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `phone_number` VARCHAR(20) NULL UNIQUE AFTER `email`;

-- 2. Pastikan tabel otp_logs ada dengan indeks yang tepat
CREATE TABLE IF NOT EXISTS `otp_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL COMMENT 'Relasi ke tabel users (NULL jika nomor belum terdaftar saat request)',
  `phone_number` VARCHAR(20) NOT NULL COMMENT 'Nomor tujuan format 628xxx',
  `otp_hash` VARCHAR(255) NOT NULL COMMENT 'Hash BCRYPT kode OTP 6 digit',
  `expires_at` DATETIME NOT NULL COMMENT 'Waktu kedaluwarsa OTP',
  `is_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Belum digunakan, 1: Sudah berhasil verifikasi',
  `attempts` INT NOT NULL DEFAULT 0 COMMENT 'Jumlah percobaan input salah',
  `ip_address` VARCHAR(45) NULL COMMENT 'Alamat IP peminta OTP',
  `user_agent` TEXT NULL COMMENT 'Informasi browser/perangkat',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_phone_active` (`phone_number`, `is_used`, `expires_at`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Konfigurasi & Pengaturan WhatsApp OTP Gateway
CREATE TABLE IF NOT EXISTS `whatsapp_otp_configs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `provider` VARCHAR(50) NOT NULL DEFAULT 'fonnte' COMMENT 'fonnte, meta_cloud, dev_mock',
  `api_url` VARCHAR(255) NOT NULL DEFAULT 'https://api.fonnte.com/send',
  `access_token` TEXT NULL COMMENT 'API Token / Bearer Token terenkripsi AES-256',
  `phone_number_id` VARCHAR(100) NULL COMMENT 'Untuk WhatsApp Cloud API Meta',
  `waba_id` VARCHAR(100) NULL COMMENT 'WhatsApp Business Account ID',
  `template_name` VARCHAR(100) NULL DEFAULT 'nnaco_auth_otp' COMMENT 'Template WhatsApp jika diwajibkan',
  `otp_expiration_minutes` INT NOT NULL DEFAULT 5 COMMENT 'Masa berlaku OTP dalam menit (default 5 menit)',
  `resend_cooldown_seconds` INT NOT NULL DEFAULT 60 COMMENT 'Jeda kirim ulang dalam detik (default 60 detik)',
  `max_attempts` INT NOT NULL DEFAULT 5 COMMENT 'Batas salah percobaan verifikasi per kode',
  `rate_limit_per_hour` INT NOT NULL DEFAULT 5 COMMENT 'Maksimal permintaan OTP per nomor per jam',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Aktif, 0: Nonaktif',
  `is_dev_mode` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Mode simulasi dev lokal, 0: Mode produksi live',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Audit Log Autentikasi OTP
CREATE TABLE IF NOT EXISTS `otp_audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `phone_masked` VARCHAR(30) NOT NULL,
  `action` VARCHAR(50) NOT NULL COMMENT 'REQUEST_OTP, VERIFY_SUCCESS, VERIFY_FAIL, EXPIRED, MAX_ATTEMPTS, RATE_LIMITED, SETTINGS_UPDATED, TEST_CONNECTION',
  `status` VARCHAR(20) NOT NULL DEFAULT 'SUCCESS' COMMENT 'SUCCESS, FAILED',
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
