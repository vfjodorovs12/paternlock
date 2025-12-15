-- Pattern Lock Tables for CodeIgniter 3
-- Version: 1.0
-- Description: Database schema for Pattern Lock authentication system

-- Table: user_patterns
-- Stores user pattern hashes and backup codes
CREATE TABLE IF NOT EXISTS `user_patterns` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `pattern_hash` varchar(64) NOT NULL COMMENT 'SHA-256 hash of pattern',
  `grid_size` tinyint(2) UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Grid size (3-20)',
  `backup_code` varchar(64) NOT NULL COMMENT 'Encrypted backup code',
  `pattern_strength` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-5 strength rating',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pattern_access_logs
-- Logs all authentication attempts
CREATE TABLE IF NOT EXISTS `pattern_access_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `status` enum('success','failed','blocked','recovered') NOT NULL,
  `attempt_type` enum('pattern','backup_code') NOT NULL DEFAULT 'pattern',
  `failure_reason` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL COMMENT 'Geo-location country',
  `city` varchar(100) DEFAULT NULL COMMENT 'Geo-location city',
  `device_fingerprint` varchar(64) DEFAULT NULL COMMENT 'Device identification',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `username` (`username`),
  KEY `ip_address` (`ip_address`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `device_fingerprint` (`device_fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pattern_lockouts
-- IP-based lockouts for brute force protection
CREATE TABLE IF NOT EXISTS `pattern_lockouts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `failed_attempts` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `is_permanent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `locked_until` (`locked_until`),
  KEY `is_permanent` (`is_permanent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pattern_settings
-- System-wide settings for pattern lock
CREATE TABLE IF NOT EXISTS `pattern_settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pattern_known_devices
-- Track known/trusted devices for notification system
CREATE TABLE IF NOT EXISTS `pattern_known_devices` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `device_fingerprint` varchar(64) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `is_trusted` tinyint(1) NOT NULL DEFAULT 0,
  `last_used` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device` (`user_id`, `device_fingerprint`),
  KEY `user_id` (`user_id`),
  KEY `is_trusted` (`is_trusted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `pattern_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('max_failed_attempts', '5', 'integer', 'Maximum failed attempts before lockout'),
('lockout_duration', '900', 'integer', 'Lockout duration in seconds (default: 15 minutes)'),
('min_pattern_points', '4', 'integer', 'Minimum number of points in pattern'),
('default_grid_size', '3', 'integer', 'Default grid size for pattern (3x3)'),
('recaptcha_enabled', '1', 'boolean', 'Enable reCAPTCHA integration'),
('recaptcha_threshold', '3', 'integer', 'Failed attempts before showing reCAPTCHA'),
('log_retention_days', '90', 'integer', 'Days to keep access logs (0 = forever)'),
('system_lockout', '0', 'boolean', 'Total system lockout (admin only)'),
('email_on_lockout', '1', 'boolean', 'Send email notification on account lockout'),
('email_on_new_device', '1', 'boolean', 'Send email notification on new device login'),
('recaptcha_version', 'v2', 'string', 'reCAPTCHA version (v2 or v3)'),
('recaptcha_v3_threshold', '0.5', 'string', 'reCAPTCHA v3 score threshold (0.0-1.0)');
