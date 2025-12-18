<?php
/**
 * Database Installation Script
 * Creates the Time2Eat database and basic tables
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

echo "Time2Eat Database Installation\n";
echo "==============================\n\n";

try {
    // Connect to MySQL without database
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '" . DB_NAME . "' created\n";
    
    // Use the database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Create basic tables
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `role` enum('customer','vendor','rider','admin') NOT NULL DEFAULT 'customer',
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `email_verified_at` timestamp NULL DEFAULT NULL,
                `email_verification_token` varchar(255) DEFAULT NULL,
                `last_login_at` timestamp NULL DEFAULT NULL,
                `avatar` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`),
                UNIQUE KEY `username` (`username`),
                KEY `role` (`role`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'categories' => "
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `slug` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `image` varchar(255) DEFAULT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'restaurants' => "
            CREATE TABLE IF NOT EXISTS `restaurants` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `category_id` int(11) NOT NULL,
                `name` varchar(100) NOT NULL,
                `slug` varchar(100) NOT NULL,
                `description` text DEFAULT NULL,
                `image` varchar(255) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `email` varchar(100) DEFAULT NULL,
                `address` text DEFAULT NULL,
                `location` varchar(100) DEFAULT NULL,
                `latitude` decimal(10,8) DEFAULT NULL,
                `longitude` decimal(11,8) DEFAULT NULL,
                `delivery_fee` decimal(10,2) NOT NULL DEFAULT 500.00,
                `delivery_time` varchar(20) DEFAULT '30-45 min',
                `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `is_featured` tinyint(1) NOT NULL DEFAULT 0,
                `is_open` tinyint(1) NOT NULL DEFAULT 1,
                `opening_hours` json DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`),
                KEY `user_id` (`user_id`),
                KEY `category_id` (`category_id`),
                KEY `is_active` (`is_active`),
                KEY `is_featured` (`is_featured`),
                CONSTRAINT `restaurants_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `restaurants_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'failed_logins' => "
            CREATE TABLE IF NOT EXISTS `failed_logins` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(100) NOT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `email` (`email`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'remember_tokens' => "
            CREATE TABLE IF NOT EXISTS `remember_tokens` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `token` varchar(255) NOT NULL,
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `token` (`token`),
                KEY `user_id` (`user_id`),
                KEY `expires_at` (`expires_at`),
                CONSTRAINT `remember_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "✓ Table '{$tableName}' created\n";
    }
    
    // Insert default categories
    $defaultCategories = [
        ['Fast Food', 'fast-food', 'Quick and delicious fast food options'],
        ['African Cuisine', 'african-cuisine', 'Traditional African dishes'],
        ['Chinese', 'chinese', 'Authentic Chinese cuisine'],
        ['Pizza', 'pizza', 'Fresh pizzas with various toppings'],
        ['Drinks & Beverages', 'drinks-beverages', 'Refreshing drinks and beverages'],
        ['Desserts', 'desserts', 'Sweet treats and desserts']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description) VALUES (?, ?, ?)");
    foreach ($defaultCategories as $category) {
        $stmt->execute($category);
    }
    echo "✓ Default categories inserted\n";
    
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password, role, is_active, email_verified_at) 
        VALUES ('admin', 'admin@time2eat.com', ?, 'admin', 1, NOW())
    ");
    $stmt->execute([$adminPassword]);
    echo "✓ Admin user created (admin@time2eat.com / admin123)\n";
    
    echo "\n✅ Database installation completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Copy .env.example to .env and update your configuration\n";
    echo "2. Run 'composer install' to install dependencies\n";
    echo "3. Access your application at " . APP_URL . "\n";
    echo "4. Login as admin: admin@time2eat.com / admin123\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
