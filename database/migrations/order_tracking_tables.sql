-- Order Tracking Database Tables
-- This migration adds tables for comprehensive order tracking functionality

-- Table for tracking order status changes over time
CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `status` enum('pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way', 'delivered', 'cancelled', 'refunded') NOT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for rider assignments to orders
CREATE TABLE IF NOT EXISTS `rider_assignments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `rider_id` int(11) NOT NULL,
    `status` enum('pending', 'accepted', 'rejected', 'picked_up', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `accepted_at` timestamp NULL DEFAULT NULL,
    `picked_up_at` timestamp NULL DEFAULT NULL,
    `delivered_at` timestamp NULL DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_rider_id` (`rider_id`),
    KEY `idx_status` (`status`),
    KEY `idx_assigned_at` (`assigned_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rider_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for real-time rider location tracking
CREATE TABLE IF NOT EXISTS `rider_locations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rider_id` int(11) NOT NULL,
    `latitude` decimal(10, 8) NOT NULL,
    `longitude` decimal(11, 8) NOT NULL,
    `accuracy` float DEFAULT NULL,
    `heading` float DEFAULT NULL,
    `speed` float DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_rider` (`rider_id`),
    KEY `idx_location` (`latitude`, `longitude`),
    KEY `idx_updated_at` (`updated_at`),
    FOREIGN KEY (`rider_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for messages between customers and riders
CREATE TABLE IF NOT EXISTS `order_messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `sender_id` int(11) NOT NULL,
    `recipient_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `is_read` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_sender_id` (`sender_id`),
    KEY `idx_recipient_id` (`recipient_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for order ratings and reviews
CREATE TABLE IF NOT EXISTS `order_ratings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `restaurant_id` int(11) NOT NULL,
    `rider_id` int(11) DEFAULT NULL,
    `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `review` text DEFAULT NULL,
    `delivery_rating` tinyint(1) DEFAULT NULL CHECK (`delivery_rating` >= 1 AND `delivery_rating` <= 5),
    `delivery_review` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_order_rating` (`order_id`),
    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_restaurant_id` (`restaurant_id`),
    KEY `idx_rider_id` (`rider_id`),
    KEY `idx_rating` (`rating`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rider_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add estimated delivery time to orders table if not exists
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `estimated_delivery_time` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `actual_delivery_time` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `rating` tinyint(1) DEFAULT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
ADD COLUMN IF NOT EXISTS `review` text DEFAULT NULL;

-- Add delivery coordinates to delivery_addresses table if not exists
ALTER TABLE `delivery_addresses` 
ADD COLUMN IF NOT EXISTS `latitude` decimal(10, 8) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `longitude` decimal(11, 8) DEFAULT NULL;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_orders_status` ON `orders` (`status`);
CREATE INDEX IF NOT EXISTS `idx_orders_customer_status` ON `orders` (`customer_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_orders_created_at` ON `orders` (`created_at`);

-- Insert sample order status history for existing orders (optional)
INSERT IGNORE INTO `order_status_history` (`order_id`, `status`, `notes`, `created_at`)
SELECT `id`, `status`, 'Initial status', `created_at` FROM `orders` WHERE `id` NOT IN (SELECT DISTINCT `order_id` FROM `order_status_history`);
