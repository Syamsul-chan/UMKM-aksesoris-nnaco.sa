-- Migration: Create AI Configuration & Audit Log Tables for nnaco_sa
-- Author: Senior Full-Stack Developer & Software Architect

CREATE TABLE IF NOT EXISTS `ai_configurations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `provider` VARCHAR(50) NOT NULL, -- 'groq', 'openai', 'anthropic', 'custom'
  `model_name` VARCHAR(100) NOT NULL,
  `api_url` VARCHAR(255) NOT NULL,
  `api_key` TEXT NULL, -- AES-256-CBC Encrypted
  `temperature` DECIMAL(3,2) NOT NULL DEFAULT 0.70,
  `max_tokens` INT NOT NULL DEFAULT 800,
  `top_p` DECIMAL(3,2) NOT NULL DEFAULT 1.00,
  `timeout_seconds` INT NOT NULL DEFAULT 30,
  `max_retries` INT NOT NULL DEFAULT 2,
  `system_prompt_customer` TEXT NULL,
  `system_prompt_insight` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_is_primary` (`is_primary`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_config_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `user_name` VARCHAR(100) NULL,
  `action` VARCHAR(50) NOT NULL, -- 'CREATE', 'UPDATE', 'ACTIVATE', 'DEACTIVATE', 'SET_PRIMARY', 'DELETE', 'TEST_CONNECTION'
  `config_id` INT NULL,
  `config_name` VARCHAR(100) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'SUCCESS', -- 'SUCCESS', 'FAILED'
  `details` TEXT NULL, -- Safe sanitized metadata (NO secrets)
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
