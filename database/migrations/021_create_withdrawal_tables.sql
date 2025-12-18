-- Create withdrawals table
CREATE TABLE IF NOT EXISTS withdrawals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    withdrawal_type ENUM('affiliate', 'rider', 'restaurant') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    account_details JSON NULL,
    status ENUM('pending', 'approved', 'rejected', 'processing') NOT NULL DEFAULT 'pending',
    withdrawal_reference VARCHAR(50) NOT NULL UNIQUE,
    processed_by BIGINT UNSIGNED NULL,
    processed_at TIMESTAMP NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_withdrawal_type (withdrawal_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_withdrawal_reference (withdrawal_reference),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_balances table
CREATE TABLE IF NOT EXISTS user_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    balance_type ENUM('affiliate', 'rider', 'restaurant') NOT NULL,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_balance (user_id, balance_type),
    INDEX idx_user_id (user_id),
    INDEX idx_balance_type (balance_type),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create withdrawal_logs table
CREATE TABLE IF NOT EXISTS withdrawal_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    withdrawal_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_withdrawal_id (withdrawal_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (withdrawal_id) REFERENCES withdrawals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create earnings table to track earnings for each user type
CREATE TABLE IF NOT EXISTS earnings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    earning_type ENUM('affiliate', 'rider', 'restaurant') NOT NULL,
    source_type ENUM('order', 'commission', 'bonus', 'refund') NOT NULL,
    source_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_earning_type (earning_type),
    INDEX idx_source_type (source_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default minimum withdrawal amounts into site_settings
INSERT INTO site_settings (`key`, value, type, `group`, description, is_public, created_at, updated_at)
VALUES 
    ('min_withdrawal_affiliate', '5000', 'integer', 'withdrawal', 'Minimum withdrawal amount for affiliates (XAF)', false, NOW(), NOW()),
    ('min_withdrawal_rider', '2000', 'integer', 'withdrawal', 'Minimum withdrawal amount for riders (XAF)', false, NOW(), NOW()),
    ('min_withdrawal_restaurant', '10000', 'integer', 'withdrawal', 'Minimum withdrawal amount for restaurants (XAF)', false, NOW(), NOW()),
    ('withdrawal_processing_fee', '0', 'decimal', 'withdrawal', 'Processing fee for withdrawals (XAF)', false, NOW(), NOW()),
    ('withdrawal_auto_approve_limit', '50000', 'integer', 'withdrawal', 'Auto-approve withdrawals below this amount (XAF)', false, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();
