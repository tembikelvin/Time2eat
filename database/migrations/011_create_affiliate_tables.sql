-- ============================================================================
-- AFFILIATE SYSTEM TABLES
-- ============================================================================

-- Affiliates table
CREATE TABLE IF NOT EXISTS affiliates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    referral_code VARCHAR(20) UNIQUE NOT NULL,
    commission_rate DECIMAL(5,2) DEFAULT 5.00,
    total_earnings DECIMAL(15,2) DEFAULT 0.00,
    available_balance DECIMAL(15,2) DEFAULT 0.00,
    total_withdrawals DECIMAL(15,2) DEFAULT 0.00,
    total_referrals INT DEFAULT 0,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referral_code (referral_code),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_total_earnings (total_earnings)
);

-- Affiliate earnings table
CREATE TABLE IF NOT EXISTS affiliate_earnings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    affiliate_id INT NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('referral', 'bonus', 'commission') DEFAULT 'referral',
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_affiliate_id (affiliate_id),
    INDEX idx_order_id (order_id),
    INDEX idx_earned_at (earned_at),
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Affiliate withdrawals table
CREATE TABLE IF NOT EXISTS affiliate_withdrawals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    affiliate_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('mobile_money', 'bank_transfer', 'orange_money', 'mtn_momo') NOT NULL,
    payment_details JSON NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'processing', 'completed', 'failed') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_affiliate_id (affiliate_id),
    INDEX idx_status (status),
    INDEX idx_requested_at (requested_at),
    INDEX idx_processed_by (processed_by)
);

-- Affiliate payouts table
CREATE TABLE IF NOT EXISTS affiliate_payouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    withdrawal_id INT NOT NULL,
    affiliate_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_details JSON NOT NULL,
    transaction_id VARCHAR(100) NULL,
    status ENUM('processing', 'completed', 'failed', 'pending_manual_review') DEFAULT 'processing',
    processed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    failure_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (withdrawal_id) REFERENCES affiliate_withdrawals(id) ON DELETE CASCADE,
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    INDEX idx_withdrawal_id (withdrawal_id),
    INDEX idx_affiliate_id (affiliate_id),
    INDEX idx_status (status),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_created_at (created_at)
);

-- Add referred_by column to users table if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS referred_by VARCHAR(20) NULL,
ADD INDEX IF NOT EXISTS idx_referred_by (referred_by);

-- ============================================================================
-- AFFILIATE SYSTEM TRIGGERS
-- ============================================================================

-- Trigger to update affiliate total_referrals when a user is referred
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS update_affiliate_referrals_after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.referred_by IS NOT NULL THEN
        UPDATE affiliates 
        SET total_referrals = total_referrals + 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE referral_code = NEW.referred_by;
    END IF;
END$$

-- Trigger to update affiliate balance when earning is added
CREATE TRIGGER IF NOT EXISTS update_affiliate_balance_after_earning_insert
AFTER INSERT ON affiliate_earnings
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' THEN
        UPDATE affiliates 
        SET total_earnings = total_earnings + NEW.amount,
            available_balance = available_balance + NEW.amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.affiliate_id;
    END IF;
END$$

-- Trigger to update affiliate balance when earning status changes
CREATE TRIGGER IF NOT EXISTS update_affiliate_balance_after_earning_update
AFTER UPDATE ON affiliate_earnings
FOR EACH ROW
BEGIN
    DECLARE amount_diff DECIMAL(10,2) DEFAULT 0;
    
    -- Calculate the difference in confirmed amounts
    IF OLD.status != 'confirmed' AND NEW.status = 'confirmed' THEN
        SET amount_diff = NEW.amount;
    ELSEIF OLD.status = 'confirmed' AND NEW.status != 'confirmed' THEN
        SET amount_diff = -OLD.amount;
    END IF;
    
    -- Update affiliate balance if there's a difference
    IF amount_diff != 0 THEN
        UPDATE affiliates 
        SET total_earnings = total_earnings + amount_diff,
            available_balance = available_balance + amount_diff,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.affiliate_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- AFFILIATE SYSTEM SAMPLE DATA
-- ============================================================================

-- Insert sample affiliate commission rates settings
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('affiliate_default_commission_rate', '5.0', 'Default commission rate for new affiliates (percentage)'),
('affiliate_min_withdrawal_amount', '10000', 'Minimum withdrawal amount in XAF'),
('affiliate_max_pending_withdrawals', '3', 'Maximum number of pending withdrawals per affiliate'),
('affiliate_daily_withdrawal_limit', '100000', 'Daily withdrawal limit per affiliate in XAF'),
('affiliate_auto_approve_withdrawals', '0', 'Auto-approve withdrawals under certain amount (0=disabled)'),
('affiliate_referral_bonus', '1000', 'One-time bonus for successful referrals in XAF');

-- Create sample affiliate accounts for existing users
INSERT IGNORE INTO affiliates (user_id, referral_code, commission_rate, status)
SELECT 
    id,
    CONCAT('REF', LPAD(id, 6, '0')),
    5.00,
    'active'
FROM users 
WHERE role IN ('customer', 'vendor') 
AND id NOT IN (SELECT user_id FROM affiliates)
LIMIT 10;

-- ============================================================================
-- AFFILIATE SYSTEM VIEWS
-- ============================================================================

-- View for affiliate statistics
CREATE OR REPLACE VIEW affiliate_stats AS
SELECT 
    a.id,
    a.user_id,
    a.referral_code,
    a.commission_rate,
    a.total_earnings,
    a.available_balance,
    a.total_withdrawals,
    a.status,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    COUNT(DISTINCT ref_users.id) as actual_referrals,
    COUNT(DISTINCT ae.id) as total_earnings_count,
    COALESCE(SUM(CASE WHEN ae.earned_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN ae.amount ELSE 0 END), 0) as earnings_last_30_days,
    COALESCE(SUM(CASE WHEN ae.earned_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN ae.amount ELSE 0 END), 0) as earnings_last_7_days,
    COUNT(DISTINCT aw.id) as total_withdrawal_requests,
    COUNT(DISTINCT CASE WHEN aw.status = 'pending' THEN aw.id END) as pending_withdrawals
FROM affiliates a
JOIN users u ON a.user_id = u.id
LEFT JOIN users ref_users ON ref_users.referred_by = a.referral_code
LEFT JOIN affiliate_earnings ae ON a.id = ae.affiliate_id AND ae.status = 'confirmed'
LEFT JOIN affiliate_withdrawals aw ON a.id = aw.affiliate_id
GROUP BY a.id, a.user_id, a.referral_code, a.commission_rate, a.total_earnings, 
         a.available_balance, a.total_withdrawals, a.status, u.first_name, 
         u.last_name, u.email, u.phone;

-- View for top performing affiliates
CREATE OR REPLACE VIEW top_affiliates AS
SELECT 
    a.*,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(DISTINCT ref_users.id) as referral_count,
    COUNT(DISTINCT ae.id) as earning_transactions,
    COALESCE(AVG(ae.amount), 0) as avg_earning_per_transaction
FROM affiliates a
JOIN users u ON a.user_id = u.id
LEFT JOIN users ref_users ON ref_users.referred_by = a.referral_code
LEFT JOIN affiliate_earnings ae ON a.id = ae.affiliate_id AND ae.status = 'confirmed'
WHERE a.status = 'active'
GROUP BY a.id
HAVING a.total_earnings > 0
ORDER BY a.total_earnings DESC, referral_count DESC;

-- ============================================================================
-- AFFILIATE SYSTEM INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional indexes for better performance
CREATE INDEX IF NOT EXISTS idx_affiliate_earnings_affiliate_earned_at ON affiliate_earnings(affiliate_id, earned_at);
CREATE INDEX IF NOT EXISTS idx_affiliate_earnings_status_earned_at ON affiliate_earnings(status, earned_at);
CREATE INDEX IF NOT EXISTS idx_affiliate_withdrawals_status_requested_at ON affiliate_withdrawals(status, requested_at);
CREATE INDEX IF NOT EXISTS idx_affiliate_payouts_status_created_at ON affiliate_payouts(status, created_at);
CREATE INDEX IF NOT EXISTS idx_users_referred_by_created_at ON users(referred_by, created_at);

-- ============================================================================
-- AFFILIATE SYSTEM STORED PROCEDURES
-- ============================================================================

DELIMITER $$

-- Procedure to calculate affiliate commission for an order
CREATE PROCEDURE IF NOT EXISTS CalculateAffiliateCommission(
    IN p_order_id INT,
    IN p_customer_id INT,
    IN p_order_total DECIMAL(10,2)
)
BEGIN
    DECLARE v_referral_code VARCHAR(20);
    DECLARE v_affiliate_id INT;
    DECLARE v_commission_rate DECIMAL(5,2);
    DECLARE v_commission_amount DECIMAL(10,2);
    
    -- Get the referral code for the customer
    SELECT referred_by INTO v_referral_code
    FROM users 
    WHERE id = p_customer_id AND referred_by IS NOT NULL;
    
    -- If customer was referred, calculate commission
    IF v_referral_code IS NOT NULL THEN
        -- Get affiliate details
        SELECT id, commission_rate INTO v_affiliate_id, v_commission_rate
        FROM affiliates 
        WHERE referral_code = v_referral_code AND status = 'active';
        
        -- Calculate commission amount
        IF v_affiliate_id IS NOT NULL THEN
            SET v_commission_amount = (p_order_total * v_commission_rate / 100);
            
            -- Insert earning record
            INSERT INTO affiliate_earnings (
                affiliate_id, 
                order_id, 
                amount, 
                type, 
                status, 
                earned_at
            ) VALUES (
                v_affiliate_id,
                p_order_id,
                v_commission_amount,
                'referral',
                'confirmed',
                NOW()
            );
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- AFFILIATE SYSTEM COMPLETION
-- ============================================================================

-- ============================================================================
-- PWA PUSH NOTIFICATIONS TABLE
-- ============================================================================

-- Push subscriptions table for PWA notifications
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    endpoint TEXT NOT NULL,
    p256dh_key TEXT NULL,
    auth_key TEXT NULL,
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at),
    INDEX idx_last_used_at (last_used_at),
    UNIQUE KEY unique_user_endpoint (user_id, endpoint(255))
);

-- Log the completion of affiliate system setup
INSERT INTO settings (setting_key, setting_value, description) VALUES
('affiliate_system_installed', '1', 'Affiliate system installation completed'),
('pwa_push_notifications_enabled', '1', 'PWA push notifications system enabled'),
('vapid_public_key', 'BEl62iUYgUivxIkv69yViEuiBIa40HI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqI', 'VAPID public key for push notifications')
ON DUPLICATE KEY UPDATE
setting_value = VALUES(setting_value),
updated_at = CURRENT_TIMESTAMP;
