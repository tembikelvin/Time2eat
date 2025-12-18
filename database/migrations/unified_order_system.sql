-- Unified Order System Database Migration
-- This migration adds the necessary tables and columns for the unified order management system

-- Add missing columns to existing orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS estimated_preparation_time DATETIME NULL AFTER delivered_at,
ADD COLUMN IF NOT EXISTS estimated_delivery_time DATETIME NULL AFTER estimated_preparation_time,
ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL AFTER estimated_delivery_time,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER admin_notes,
ADD COLUMN IF NOT EXISTS delivery_distance DECIMAL(8,2) NULL AFTER updated_by,
ADD INDEX idx_orders_status (status),
ADD INDEX idx_orders_updated_at (updated_at),
ADD INDEX idx_orders_customer_status (customer_id, status),
ADD INDEX idx_orders_restaurant_status (restaurant_id, status),
ADD INDEX idx_orders_rider_status (rider_id, status);

-- Create order status history table for audit trail
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(50) NOT NULL,
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_status_history_order (order_id),
    INDEX idx_order_status_history_changed_by (changed_by),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create restaurant commissions table
CREATE TABLE IF NOT EXISTS restaurant_commissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    commission_rate DECIMAL(5,4) NOT NULL DEFAULT 0.1000,
    commission_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant_commissions_order (order_id),
    INDEX idx_restaurant_commissions_restaurant (restaurant_id),
    INDEX idx_restaurant_commissions_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Create rider earnings table
CREATE TABLE IF NOT EXISTS rider_earnings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    rider_id INT NOT NULL,
    base_fee DECIMAL(8,2) NOT NULL DEFAULT 1000.00,
    distance_fee DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    bonus_fee DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    total_earning DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rider_earnings_order (order_id),
    INDEX idx_rider_earnings_rider (rider_id),
    INDEX idx_rider_earnings_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create order refunds table
CREATE TABLE IF NOT EXISTS order_refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    refund_amount DECIMAL(10,2) NOT NULL,
    refund_reason TEXT NULL,
    processed_by INT NOT NULL,
    status ENUM('pending', 'processed', 'failed', 'cancelled') DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_refunds_order (order_id),
    INDEX idx_order_refunds_customer (customer_id),
    INDEX idx_order_refunds_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create delivery tracking table
CREATE TABLE IF NOT EXISTS delivery_tracking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status ENUM('assigned', 'in_transit', 'delivered', 'failed') DEFAULT 'assigned',
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    estimated_arrival TIMESTAMP NULL,
    notes TEXT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_delivery_tracking_order (order_id),
    INDEX idx_delivery_tracking_status (status),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Create rider locations table for real-time tracking
CREATE TABLE IF NOT EXISTS rider_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rider_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(8,2) NULL,
    speed DECIMAL(8,2) NULL,
    heading DECIMAL(5,2) NULL,
    battery_level INT NULL,
    is_online BOOLEAN DEFAULT TRUE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rider_locations_rider (rider_id),
    INDEX idx_rider_locations_updated (updated_at),
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create rider schedules table
CREATE TABLE IF NOT EXISTS rider_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rider_id INT NOT NULL,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    max_deliveries_per_hour INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rider_schedules_rider (rider_id),
    INDEX idx_rider_schedules_day (day),
    INDEX idx_rider_schedules_available (is_available),
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add commission rate to restaurants table
ALTER TABLE restaurants 
ADD COLUMN IF NOT EXISTS commission_rate DECIMAL(5,4) DEFAULT 0.1000 AFTER delivery_fee,
ADD COLUMN IF NOT EXISTS preparation_time INT DEFAULT 30 AFTER commission_rate,
ADD COLUMN IF NOT EXISTS delivery_time INT DEFAULT 30 AFTER preparation_time;

-- Create notifications table for in-app notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    action_url VARCHAR(500) NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_type (type),
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create system settings table for configuration
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_system_settings_key (setting_key),
    INDEX idx_system_settings_public (is_public),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('platform_commission_rate', '0.1000', 'float', 'Default platform commission rate (10%)', FALSE),
('default_delivery_fee', '1000.00', 'float', 'Default delivery fee in XAF', TRUE),
('max_delivery_distance', '20.0', 'float', 'Maximum delivery distance in kilometers', TRUE),
('order_auto_cancel_time', '30', 'integer', 'Auto-cancel pending orders after X minutes', FALSE),
('rider_location_update_interval', '30', 'integer', 'Rider location update interval in seconds', FALSE),
('notification_retention_days', '30', 'integer', 'Days to keep notifications before cleanup', FALSE),
('maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', TRUE),
('platform_name', 'Time2Eat', 'string', 'Platform name', TRUE),
('support_email', 'support@time2eat.com', 'string', 'Support email address', TRUE),
('support_phone', '+237123456789', 'string', 'Support phone number', TRUE);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_orders_created_status ON orders(created_at, status);
CREATE INDEX IF NOT EXISTS idx_orders_restaurant_created ON orders(restaurant_id, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_rider_created ON orders(rider_id, created_at);
CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status);

-- Update existing orders to have proper status flow
UPDATE orders SET status = 'pending' WHERE status IS NULL OR status = '';

-- Create a view for order analytics
CREATE OR REPLACE VIEW order_analytics AS
SELECT 
    DATE(o.created_at) as order_date,
    o.status,
    COUNT(*) as order_count,
    SUM(o.total_amount) as total_revenue,
    AVG(o.total_amount) as avg_order_value,
    COUNT(DISTINCT o.customer_id) as unique_customers,
    COUNT(DISTINCT o.restaurant_id) as active_restaurants,
    COUNT(DISTINCT o.rider_id) as active_riders,
    AVG(CASE 
        WHEN o.status = 'delivered' AND o.delivered_at IS NOT NULL 
        THEN TIMESTAMPDIFF(MINUTE, o.created_at, o.delivered_at) 
        ELSE NULL 
    END) as avg_delivery_time_minutes
FROM orders o
WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAYS)
GROUP BY DATE(o.created_at), o.status
ORDER BY order_date DESC, o.status;

-- Create a view for real-time dashboard metrics
CREATE OR REPLACE VIEW dashboard_metrics AS
SELECT 
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN status IN ('confirmed', 'preparing') THEN 1 END) as active_orders,
    COUNT(CASE WHEN status = 'ready' THEN 1 END) as ready_orders,
    COUNT(CASE WHEN status IN ('picked_up', 'on_the_way') THEN 1 END) as in_transit_orders,
    COUNT(CASE WHEN status = 'delivered' AND DATE(delivered_at) = CURDATE() THEN 1 END) as delivered_today,
    COUNT(CASE WHEN status = 'cancelled' AND DATE(cancelled_at) = CURDATE() THEN 1 END) as cancelled_today,
    SUM(CASE WHEN status = 'delivered' AND DATE(delivered_at) = CURDATE() THEN total_amount ELSE 0 END) as revenue_today,
    COUNT(DISTINCT CASE WHEN DATE(created_at) = CURDATE() THEN customer_id END) as active_customers_today,
    COUNT(DISTINCT CASE WHEN DATE(created_at) = CURDATE() THEN restaurant_id END) as active_restaurants_today,
    COUNT(DISTINCT CASE WHEN status IN ('picked_up', 'on_the_way') THEN rider_id END) as active_riders_now
FROM orders 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Migration completed successfully
SELECT 'Unified Order System migration completed successfully' as status;
