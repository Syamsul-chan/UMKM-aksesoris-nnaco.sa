-- =========================================================================
-- SQL Migration: Sistem Autentikasi WhatsApp OTP (Fonnte API)
-- Proyek Toko Online: nnaco.sa
-- =========================================================================

-- 1. Modifikasi Tabel `users`: Tambahkan kolom nomor WhatsApp jika belum ada
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `phone_number` VARCHAR(20) NULL UNIQUE AFTER `email`;

-- 2. Pembuatan Tabel `otp_logs` untuk Pencatatan & Validasi OTP
CREATE TABLE IF NOT EXISTS `otp_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL COMMENT 'Relasi ke tabel users (NULL jika nomor belum terdaftar)',
  `phone_number` VARCHAR(20) NOT NULL COMMENT 'Nomor tujuan format internasional 628xxx',
  `otp_hash` VARCHAR(255) NOT NULL COMMENT 'Hash BCRYPT kode OTP 6 digit (Zero-Plaintext Security)',
  `expires_at` DATETIME NOT NULL COMMENT 'Waktu kedaluwarsa OTP (5 menit sejak generate)',
  `is_used` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Belum digunakan, 1: Sudah berhasil verifikasi',
  `attempts` INT NOT NULL DEFAULT 0 COMMENT 'Jumlah percobaan input salah untuk mitigasi brute force',
  `ip_address` VARCHAR(45) NULL COMMENT 'Alamat IP peminta OTP',
  `user_agent` TEXT NULL COMMENT 'Informasi browser/perangkat',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- Indexing untuk optimasi query pencarian OTP aktif
  INDEX `idx_phone_active` (`phone_number`, `is_used`, `expires_at`),
  INDEX `idx_created_at` (`created_at`),
  
  -- Foreign Key opsional dengan cascade saat user dihapus
  CONSTRAINT `fk_otp_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
