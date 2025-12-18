<?php

declare(strict_types=1);

/**
 * Time2Eat Database Migration Script
 * Comprehensive database setup with schema validation and sample data
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Database Migration Manager
 */
class DatabaseMigrator
{
    private PDO $pdo;
    private bool $verbose;
    private array $stats = [];
    
    public function __construct(bool $verbose = true)
    {
        $this->verbose = $verbose;
        $this->stats = [
            'tables_created' => 0,
            'indexes_created' => 0,
            'triggers_created' => 0,
            'sample_records' => 0,
            'errors' => 0
        ];
    }
    
    /**
     * Run complete migration
     */
    public function migrate(): bool
    {
        $this->output("🚀 Time2Eat Database Migration Started", 'cyan');
        $this->output("=====================================", 'cyan');
        
        try {
            $this->connectDatabase();
            $this->createTables();
            $this->createIndexes();
            $this->createTriggers();
            $this->insertSampleData();
            $this->validateSchema();
            $this->showStats();
            
            $this->output("\n✅ Migration completed successfully!", 'green');
            return true;
            
        } catch (Exception $e) {
            $this->output("\n❌ Migration failed: " . $e->getMessage(), 'red');
            return false;
        }
    }
    
    /**
     * Connect to database
     */
    private function connectDatabase(): void
    {
        $this->output("\n📡 Connecting to database...", 'yellow');
        
        try {
            $this->pdo = Database::getInstance()->getConnection();
            $this->output("✅ Database connected successfully", 'green');
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create all tables
     */
    private function createTables(): void
    {
        $this->output("\n📋 Creating database tables...", 'yellow');
        
        $schemaFile = __DIR__ . '/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $sql = file_get_contents($schemaFile);
        $statements = $this->parseSqlStatements($sql);
        
        foreach ($statements as $statement) {
            if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                
                try {
                    $this->pdo->exec($statement);
                    $this->stats['tables_created']++;
                    $this->output("  ✓ Created table: $tableName", 'green');
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $this->stats['errors']++;
                        $this->output("  ❌ Error creating table $tableName: " . $e->getMessage(), 'red');
                    } else {
                        $this->output("  ⚠️  Table $tableName already exists", 'yellow');
                    }
                }
            }
        }
    }
    
    /**
     * Create additional indexes
     */
    private function createIndexes(): void
    {
        $this->output("\n🔍 Creating performance indexes...", 'yellow');
        
        $indexes = [
            'idx_orders_customer_status_date' => 'CREATE INDEX idx_orders_customer_status_date ON orders (customer_id, status, created_at)',
            'idx_orders_restaurant_status_date' => 'CREATE INDEX idx_orders_restaurant_status_date ON orders (restaurant_id, status, created_at)',
            'idx_menu_items_restaurant_available' => 'CREATE INDEX idx_menu_items_restaurant_available ON menu_items (restaurant_id, is_available, sort_order)',
            'idx_reviews_reviewable_rating' => 'CREATE INDEX idx_reviews_reviewable_rating ON reviews (reviewable_type, reviewable_id, rating, status)',
            'idx_payments_user_status_date' => 'CREATE INDEX idx_payments_user_status_date ON payments (user_id, status, created_at)',
        ];
        
        foreach ($indexes as $name => $sql) {
            try {
                $this->pdo->exec($sql);
                $this->stats['indexes_created']++;
                $this->output("  ✓ Created index: $name", 'green');
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $this->stats['errors']++;
                    $this->output("  ❌ Error creating index $name: " . $e->getMessage(), 'red');
                }
            }
        }
    }
    
    /**
     * Create database triggers
     */
    private function createTriggers(): void
    {
        $this->output("\n⚡ Creating database triggers...", 'yellow');
        
        $triggers = [
            'update_restaurant_rating' => "
                CREATE TRIGGER update_restaurant_rating_after_review_insert
                AFTER INSERT ON reviews
                FOR EACH ROW
                BEGIN
                    IF NEW.reviewable_type = 'restaurant' AND NEW.status = 'approved' THEN
                        UPDATE restaurants 
                        SET rating = (
                            SELECT AVG(rating) FROM reviews 
                            WHERE reviewable_type = 'restaurant' AND reviewable_id = NEW.reviewable_id AND status = 'approved'
                        ),
                        total_reviews = (
                            SELECT COUNT(*) FROM reviews 
                            WHERE reviewable_type = 'restaurant' AND reviewable_id = NEW.reviewable_id AND status = 'approved'
                        )
                        WHERE id = NEW.reviewable_id;
                    END IF;
                END
            ",
            'update_order_total' => "
                CREATE TRIGGER update_order_total_after_item_insert
                AFTER INSERT ON order_items
                FOR EACH ROW
                BEGIN
                    UPDATE orders 
                    SET subtotal = (SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id)
                    WHERE id = NEW.order_id;
                    
                    UPDATE orders 
                    SET total_amount = subtotal + delivery_fee + service_fee + tax_amount - discount_amount
                    WHERE id = NEW.order_id;
                END
            "
        ];
        
        foreach ($triggers as $name => $sql) {
            try {
                $this->pdo->exec($sql);
                $this->stats['triggers_created']++;
                $this->output("  ✓ Created trigger: $name", 'green');
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $this->stats['errors']++;
                    $this->output("  ❌ Error creating trigger $name: " . $e->getMessage(), 'red');
                }
            }
        }
    }
    
    /**
     * Insert sample data
     */
    private function insertSampleData(): void
    {
        $this->output("\n📊 Inserting sample data...", 'yellow');
        
        // Categories
        $categories = [
            ['Fast Food', 'fast-food', 'Quick and delicious fast food options', 'fast-food', 1],
            ['African Cuisine', 'african-cuisine', 'Traditional African dishes', 'restaurant', 2],
            ['Chinese', 'chinese', 'Authentic Chinese cuisine', 'rice-bowl', 3],
            ['Pizza', 'pizza', 'Fresh pizzas with various toppings', 'pizza', 4],
            ['Beverages', 'beverages', 'Refreshing drinks and beverages', 'coffee', 5],
            ['Desserts', 'desserts', 'Sweet treats and desserts', 'cake', 6],
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO categories (name, slug, description, icon, sort_order, is_active) 
            VALUES (?, ?, ?, ?, ?, TRUE)
        ");
        
        foreach ($categories as $category) {
            $stmt->execute($category);
            $this->stats['sample_records']++;
        }
        
        // Site settings
        $settings = [
            ['site_name', 'Time2Eat', 'string', 'general', 'Website name', TRUE],
            ['site_description', 'Bamenda Food Delivery Platform', 'string', 'general', 'Website description', TRUE],
            ['contact_email', 'info@time2eat.cm', 'string', 'contact', 'Contact email address', TRUE],
            ['contact_phone', '+237 6XX XXX XXX', 'string', 'contact', 'Contact phone number', TRUE],
            ['delivery_fee', '500', 'integer', 'business', 'Default delivery fee in XAF', FALSE],
            ['commission_rate', '0.15', 'float', 'business', 'Platform commission rate (15%)', FALSE],
            ['currency', 'XAF', 'string', 'business', 'Default currency', TRUE],
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO site_settings (`key`, `value`, `type`, `group`, `description`, `is_public`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
            $this->stats['sample_records']++;
        }
        
        $this->output("  ✓ Inserted sample data", 'green');
    }
    
    /**
     * Validate schema
     */
    private function validateSchema(): void
    {
        $this->output("\n🔍 Validating database schema...", 'yellow');
        
        $expectedTables = [
            'users', 'user_profiles', 'categories', 'restaurants', 'menu_items', 'menu_item_variants',
            'orders', 'order_items', 'order_status_history', 'deliveries', 'rider_schedules', 'rider_locations',
            'affiliates', 'affiliate_referrals', 'affiliate_payouts', 'payment_methods', 'payments',
            'reviews', 'review_votes', 'popup_notifications', 'messages', 'wishlists', 'cart_items',
            'disputes', 'site_settings', 'logs', 'coupons', 'coupon_usages', 'analytics', 'daily_stats'
        ];
        
        $stmt = $this->pdo->query("SHOW TABLES");
        $actualTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $missingTables = array_diff($expectedTables, $actualTables);
        
        if (empty($missingTables)) {
            $this->output("  ✅ All " . count($expectedTables) . " tables validated", 'green');
        } else {
            $this->output("  ⚠️  Missing tables: " . implode(', ', $missingTables), 'yellow');
        }
    }
    
    /**
     * Show migration statistics
     */
    private function showStats(): void
    {
        $this->output("\n📈 Migration Statistics", 'magenta');
        $this->output("======================", 'magenta');
        $this->output("Tables Created: " . $this->stats['tables_created'], 'white');
        $this->output("Indexes Created: " . $this->stats['indexes_created'], 'white');
        $this->output("Triggers Created: " . $this->stats['triggers_created'], 'white');
        $this->output("Sample Records: " . $this->stats['sample_records'], 'white');
        $this->output("Errors: " . $this->stats['errors'], $this->stats['errors'] > 0 ? 'red' : 'white');
    }
    
    /**
     * Parse SQL statements from file
     */
    private function parseSqlStatements(string $sql): array
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = explode(';', $sql);
        
        return array_filter(array_map('trim', $statements), function($stmt) {
            return !empty($stmt) && !preg_match('/^(SET|START|COMMIT)/i', $stmt);
        });
    }
    
    /**
     * Output colored text
     */
    private function output(string $message, string $color = 'white'): void
    {
        if (!$this->verbose) return;
        
        $colors = [
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'magenta' => "\033[35m",
            'cyan' => "\033[36m",
            'white' => "\033[37m",
            'reset' => "\033[0m"
        ];
        
        $colorCode = $colors[$color] ?? $colors['white'];
        echo $colorCode . $message . $colors['reset'] . "\n";
    }
}

// Run migration
if (php_sapi_name() === 'cli') {
    $verbose = in_array('--verbose', $argv) || in_array('-v', $argv);
    $migrator = new DatabaseMigrator($verbose);
    $success = $migrator->migrate();
    exit($success ? 0 : 1);
} else {
    header('Content-Type: text/plain');
    $migrator = new DatabaseMigrator(true);
    $migrator->migrate();
}
