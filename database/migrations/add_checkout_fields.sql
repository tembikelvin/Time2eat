-- Add cash_on_delivery_enabled to users table
ALTER TABLE users 
ADD COLUMN cash_on_delivery_enabled TINYINT(1) DEFAULT 1 COMMENT 'Whether customer can use cash on delivery' AFTER push_notifications;

-- Add payment_method to orders table
ALTER TABLE orders 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash_on_delivery' COMMENT 'Payment method: cash_on_delivery, mobile_money, card, etc.' AFTER payment_status;

-- Add location fields to orders table for GPS coordinates
ALTER TABLE orders 
MODIFY COLUMN delivery_address JSON COMMENT 'Delivery address with GPS coordinates, text address, and type';

