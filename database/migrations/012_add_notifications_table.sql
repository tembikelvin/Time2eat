-- ============================================================================
-- Migration: Add Notifications Table
-- Description: Adds notifications table for admin notification management
-- Date: 2024-12-19
-- ============================================================================

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL for system-wide notifications',
  `type` varchar(50) NOT NULL COMMENT 'order_update, promotion, system_alert, etc.',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL COMMENT 'Additional notification data',
  `channels` json DEFAULT NULL COMMENT 'email, sms, push, in_app',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','sent','delivered','failed','read') DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL COMMENT 'order, restaurant, user, etc.',
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_related` (`related_type`, `related_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample notifications (only if table is empty)
INSERT IGNORE INTO `notifications` (`user_id`, `type`, `title`, `message`, `priority`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(NULL, 'system_alert', 'System Maintenance Scheduled', 'Scheduled maintenance will occur tonight from 2:00 AM to 4:00 AM. Some features may be temporarily unavailable.', 'high', 'delivered', NULL, NOW(), NOW()),
(NULL, 'order_update', 'New Order Alert', 'A new high-value order (#12345) has been placed for XAF 15,000. Please review and assign a rider.', 'urgent', 'delivered', NULL, NOW(), NOW()),
(NULL, 'promotion', 'Weekend Special Promotion', 'Weekend special promotion is now live! 20% off all orders over XAF 5,000. Valid until Sunday midnight.', 'normal', 'delivered', NULL, NOW(), NOW()),
(NULL, 'user_action', 'New Restaurant Application', 'Restaurant "Bella Vista" has submitted an application for approval. Please review their documents.', 'normal', 'delivered', NULL, NOW(), NOW()),
(NULL, 'order_update', 'Delivery Completed', 'Order #12340 has been successfully delivered to customer. Payment of XAF 8,500 received.', 'low', 'delivered', NULL, NOW(), NOW()),
(NULL, 'system_alert', 'Database Backup Completed', 'Daily database backup completed successfully. Backup size: 45.2 MB. Stored in secure location.', 'low', 'delivered', NULL, NOW(), NOW()),
(NULL, 'order_update', 'Rider Assignment Required', 'Order #12346 is ready for pickup but no rider has been assigned. Please assign a rider immediately.', 'urgent', 'delivered', NULL, NOW(), NOW()),
(NULL, 'user_action', 'New Affiliate Registration', 'New affiliate "John Doe" has registered with code AFF001. Please review and approve their application.', 'normal', 'delivered', NULL, NOW(), NOW());
