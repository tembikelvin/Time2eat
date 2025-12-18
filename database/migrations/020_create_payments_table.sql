-- Migration: Create payments table for Tranzak integration
-- This migration creates the payments table to track payment transactions

CREATE TABLE IF NOT EXISTS `payments` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `transaction_id` varchar(255) DEFAULT NULL COMMENT 'Tranzak transaction ID',
    `amount` decimal(10,2) NOT NULL COMMENT 'Payment amount in XAF',
    `currency` varchar(3) NOT NULL DEFAULT 'XAF',
    `status` enum('pending','processing','success','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `payment_method` varchar(50) NOT NULL DEFAULT 'tranzak',
    `payment_provider` varchar(50) NOT NULL DEFAULT 'tranzak',
    `customer_email` varchar(100) DEFAULT NULL,
    `customer_phone` varchar(20) DEFAULT NULL,
    `customer_name` varchar(100) DEFAULT NULL,
    `payment_url` text DEFAULT NULL COMMENT 'Payment authorization URL',
    `return_url` text DEFAULT NULL,
    `notify_url` text DEFAULT NULL,
    `response_data` json DEFAULT NULL COMMENT 'Raw response from payment provider',
    `failure_reason` text DEFAULT NULL,
    `processed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_transaction_id` (`transaction_id`),
    KEY `idx_payments_order_id` (`order_id`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_created_at` (`created_at`),
    KEY `idx_payments_customer_email` (`customer_email`),
    CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add payment status to orders table if not exists
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER `status`,
ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN IF NOT EXISTS `payment_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `payment_method`;

-- Add index for payment status queries
CREATE INDEX IF NOT EXISTS `idx_orders_payment_status` ON `orders` (`payment_status`);
CREATE INDEX IF NOT EXISTS `idx_orders_payment_method` ON `orders` (`payment_method`);

-- Add foreign key constraint for payment_id
ALTER TABLE `orders` 
ADD CONSTRAINT IF NOT EXISTS `orders_payment_id_foreign` 
FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;
