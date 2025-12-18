-- ============================================================================
-- SECURITY ENHANCEMENT MIGRATION
-- Migration: 017_create_security_tables.sql
-- Description: Security tables for rate limiting, action logging, and security monitoring
-- ============================================================================

-- Rate limiting table
CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(255) NOT NULL COMMENT 'Unique key for rate limiting (action:identifier)',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rate_limits_key_index` (`rate_key`),
  KEY `rate_limits_ip_index` (`ip_address`),
  KEY `rate_limits_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Action logging table for security monitoring
CREATE TABLE `action_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `request_data` json DEFAULT NULL,
  `response_status` int(11) DEFAULT NULL,
  `execution_time` decimal(8,3) DEFAULT NULL COMMENT 'Execution time in milliseconds',
  `memory_usage` int(11) DEFAULT NULL COMMENT 'Memory usage in bytes',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `action_logs_user_id_index` (`user_id`),
  KEY `action_logs_action_index` (`action`),
  KEY `action_logs_resource_index` (`resource_type`, `resource_id`),
  KEY `action_logs_ip_index` (`ip_address`),
  KEY `action_logs_created_at_index` (`created_at`),
  CONSTRAINT `action_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security events table
CREATE TABLE `security_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `request_uri` varchar(500) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_data` json DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `security_events_type_index` (`event_type`),
  KEY `security_events_severity_index` (`severity`),
  KEY `security_events_ip_index` (`ip_address`),
  KEY `security_events_user_id_index` (`user_id`),
  KEY `security_events_resolved_index` (`is_resolved`),
  KEY `security_events_created_at_index` (`created_at`),
  CONSTRAINT `security_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_events_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed login attempts table
CREATE TABLE `failed_login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_blocked` tinyint(1) DEFAULT 0,
  `blocked_until` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_login_attempts_email_index` (`email`),
  KEY `failed_login_attempts_ip_index` (`ip_address`),
  KEY `failed_login_attempts_attempted_at_index` (`attempted_at`),
  KEY `failed_login_attempts_blocked_index` (`is_blocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session security table
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_sessions_user_id_index` (`user_id`),
  KEY `user_sessions_last_activity_index` (`last_activity`),
  KEY `user_sessions_is_active_index` (`is_active`),
  CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CAPTCHA attempts table
CREATE TABLE `captcha_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `captcha_token` varchar(64) NOT NULL,
  `user_input` varchar(10) DEFAULT NULL,
  `is_valid` tinyint(1) DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `captcha_attempts_session_index` (`session_id`),
  KEY `captcha_attempts_ip_index` (`ip_address`),
  KEY `captcha_attempts_token_index` (`captcha_token`),
  KEY `captcha_attempts_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP whitelist/blacklist table
CREATE TABLE `ip_access_control` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `ip_range` varchar(50) DEFAULT NULL COMMENT 'CIDR notation for IP ranges',
  `access_type` enum('whitelist','blacklist') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_access_control_unique` (`ip_address`, `access_type`),
  KEY `ip_access_control_type_index` (`access_type`),
  KEY `ip_access_control_active_index` (`is_active`),
  KEY `ip_access_control_expires_index` (`expires_at`),
  CONSTRAINT `ip_access_control_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security configuration table
CREATE TABLE `security_config` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `security_config_key_unique` (`config_key`),
  KEY `security_config_active_index` (`is_active`),
  CONSTRAINT `security_config_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add security-related columns to existing users table
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `failed_login_attempts` int(11) DEFAULT 0 COMMENT 'Number of consecutive failed login attempts',
ADD COLUMN IF NOT EXISTS `locked_until` timestamp NULL DEFAULT NULL COMMENT 'Account locked until this time',
ADD COLUMN IF NOT EXISTS `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Last successful login time',
ADD COLUMN IF NOT EXISTS `last_login_ip` varchar(45) DEFAULT NULL COMMENT 'IP address of last login',
ADD COLUMN IF NOT EXISTS `password_changed_at` timestamp NULL DEFAULT NULL COMMENT 'When password was last changed',
ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT 'Whether 2FA is enabled',
ADD COLUMN IF NOT EXISTS `two_factor_secret` varchar(255) DEFAULT NULL COMMENT 'Encrypted 2FA secret',
ADD COLUMN IF NOT EXISTS `backup_codes` json DEFAULT NULL COMMENT 'Encrypted backup codes for 2FA',
ADD COLUMN IF NOT EXISTS `security_questions` json DEFAULT NULL COMMENT 'Encrypted security questions and answers';

-- Add indexes for new columns
ALTER TABLE `users` 
ADD INDEX IF NOT EXISTS `users_failed_attempts_index` (`failed_login_attempts`),
ADD INDEX IF NOT EXISTS `users_locked_until_index` (`locked_until`),
ADD INDEX IF NOT EXISTS `users_last_login_index` (`last_login_at`),
ADD INDEX IF NOT EXISTS `users_two_factor_index` (`two_factor_enabled`);

-- Insert default security configuration
INSERT INTO `security_config` (`config_key`, `config_value`, `description`) VALUES
('max_login_attempts', '5', 'Maximum failed login attempts before account lockout'),
('lockout_duration', '900', 'Account lockout duration in seconds (15 minutes)'),
('session_timeout', '7200', 'Session timeout in seconds (2 hours)'),
('password_min_length', '8', 'Minimum password length'),
('password_require_special', '1', 'Require special characters in passwords'),
('captcha_enabled', '1', 'Enable CAPTCHA on login/registration'),
('rate_limit_enabled', '1', 'Enable rate limiting'),
('security_headers_enabled', '1', 'Enable security headers'),
('log_all_actions', '0', 'Log all user actions (performance impact)'),
('email_security_alerts', '1', 'Send email alerts for security events');

-- Create views for security monitoring
CREATE OR REPLACE VIEW `security_dashboard` AS
SELECT 
    DATE(created_at) as event_date,
    event_type,
    severity,
    COUNT(*) as event_count,
    COUNT(DISTINCT ip_address) as unique_ips,
    COUNT(DISTINCT user_id) as affected_users
FROM `security_events`
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), event_type, severity
ORDER BY event_date DESC, event_count DESC;

CREATE OR REPLACE VIEW `failed_login_summary` AS
SELECT 
    DATE(attempted_at) as attempt_date,
    COUNT(*) as total_attempts,
    COUNT(DISTINCT email) as unique_emails,
    COUNT(DISTINCT ip_address) as unique_ips,
    COUNT(CASE WHEN is_blocked = 1 THEN 1 END) as blocked_attempts
FROM `failed_login_attempts`
WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(attempted_at)
ORDER BY attempt_date DESC;

CREATE OR REPLACE VIEW `rate_limit_summary` AS
SELECT 
    DATE(created_at) as limit_date,
    SUBSTRING_INDEX(rate_key, ':', 1) as action_type,
    COUNT(*) as total_requests,
    COUNT(DISTINCT ip_address) as unique_ips
FROM `rate_limits`
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(created_at), SUBSTRING_INDEX(rate_key, ':', 1)
ORDER BY limit_date DESC, total_requests DESC;

-- Create triggers for automatic security monitoring
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `log_failed_login_security_event` 
AFTER INSERT ON `failed_login_attempts` 
FOR EACH ROW 
BEGIN
    DECLARE attempt_count INT DEFAULT 0;
    
    -- Count recent failed attempts from same IP
    SELECT COUNT(*) INTO attempt_count
    FROM `failed_login_attempts`
    WHERE ip_address = NEW.ip_address
    AND attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
    
    -- Log security event if too many attempts
    IF attempt_count >= 5 THEN
        INSERT INTO `security_events` (
            event_type, severity, description, ip_address, user_agent, additional_data
        ) VALUES (
            'multiple_failed_logins',
            'high',
            CONCAT('Multiple failed login attempts detected from IP: ', NEW.ip_address),
            NEW.ip_address,
            NEW.user_agent,
            JSON_OBJECT('attempt_count', attempt_count, 'email', NEW.email)
        );
    END IF;
END$$

CREATE TRIGGER IF NOT EXISTS `update_user_login_stats` 
AFTER UPDATE ON `users` 
FOR EACH ROW 
BEGIN
    -- Log successful login if last_login_at was updated
    IF NEW.last_login_at != OLD.last_login_at AND NEW.last_login_at IS NOT NULL THEN
        INSERT INTO `action_logs` (
            user_id, action, ip_address, user_agent
        ) VALUES (
            NEW.id, 'user_login', NEW.last_login_ip, 'system'
        );
    END IF;
    
    -- Log account lockout
    IF NEW.locked_until != OLD.locked_until AND NEW.locked_until IS NOT NULL THEN
        INSERT INTO `security_events` (
            event_type, severity, description, user_id, ip_address, additional_data
        ) VALUES (
            'account_locked',
            'medium',
            CONCAT('User account locked due to failed login attempts: ', NEW.email),
            NEW.id,
            NEW.last_login_ip,
            JSON_OBJECT('locked_until', NEW.locked_until, 'failed_attempts', NEW.failed_login_attempts)
        );
    END IF;
END$$

DELIMITER ;

-- Create stored procedures for security operations
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS `CleanupSecurityLogs`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Clean old rate limit entries (older than 24 hours)
    DELETE FROM `rate_limits` WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    -- Clean old failed login attempts (older than 30 days)
    DELETE FROM `failed_login_attempts` WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- Clean old CAPTCHA attempts (older than 24 hours)
    DELETE FROM `captcha_attempts` WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    -- Clean resolved security events (older than 90 days)
    DELETE FROM `security_events` 
    WHERE is_resolved = 1 AND resolved_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- Clean old action logs (older than 90 days)
    DELETE FROM `action_logs` WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    COMMIT;
END$$

CREATE PROCEDURE IF NOT EXISTS `GetSecurityStats`(IN days_back INT)
BEGIN
    SELECT 
        'Total Security Events' as metric,
        COUNT(*) as value
    FROM `security_events`
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
    
    UNION ALL
    
    SELECT 
        'Failed Login Attempts' as metric,
        COUNT(*) as value
    FROM `failed_login_attempts`
    WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
    
    UNION ALL
    
    SELECT 
        'Rate Limited Requests' as metric,
        COUNT(*) as value
    FROM `rate_limits`
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
    
    UNION ALL
    
    SELECT 
        'Unique Threat IPs' as metric,
        COUNT(DISTINCT ip_address) as value
    FROM `security_events`
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
        AND severity IN ('high', 'critical');
END$$

DELIMITER ;

-- Create event scheduler for automatic cleanup (if enabled)
-- SET GLOBAL event_scheduler = ON;

-- CREATE EVENT IF NOT EXISTS `security_cleanup_daily`
-- ON SCHEDULE EVERY 1 DAY
-- STARTS CURRENT_TIMESTAMP
-- DO CALL CleanupSecurityLogs();

-- Migration completion marker
INSERT INTO `migrations` (`migration`, `batch`) VALUES 
('017_create_security_tables', 17);
