<?php

declare(strict_types=1);

/**
 * Time2Eat Database Import Script
 * Automated database schema setup and sample data import
 */

// Configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'time2eat',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4'
];

// Colors for console output
class Colors {
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const RESET = "\033[0m";
}

/**
 * Database Import Class
 */
class DatabaseImporter
{
    private PDO $pdo;
    private array $config;
    private bool $verbose;
    
    public function __construct(array $config, bool $verbose = true)
    {
        $this->config = $config;
        $this->verbose = $verbose;
    }
    
    /**
     * Run the complete import process
     */
    public function run(): void
    {
        $this->output("🚀 Starting Time2Eat Database Setup", Colors::CYAN);
        $this->output("=====================================", Colors::CYAN);
        
        try {
            $this->connectToDatabase();
            $this->createDatabaseIfNotExists();
            $this->importSchema();
            $this->importSampleData();
            $this->importCameroonianFoods();
            $this->verifyImport();
            $this->showSummary();
            
            $this->output("\n✅ Database setup completed successfully!", Colors::GREEN);
            
        } catch (Exception $e) {
            $this->output("\n❌ Error: " . $e->getMessage(), Colors::RED);
            exit(1);
        }
    }
    
    /**
     * Connect to database server
     */
    private function connectToDatabase(): void
    {
        $this->output("\n📡 Connecting to database server...", Colors::YELLOW);
        
        try {
            // First connect without database to create it if needed
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};charset={$this->config['charset']}";
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}"
            ]);
            
            $this->output("✅ Connected to MySQL server", Colors::GREEN);
            
        } catch (PDOException $e) {
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        }
    }
    
    /**
     * Create database if it doesn't exist
     */
    private function createDatabaseIfNotExists(): void
    {
        $this->output("\n🗄️  Checking database existence...", Colors::YELLOW);
        
        $dbName = $this->config['database'];
        
        // Check if database exists
        $stmt = $this->pdo->query("SHOW DATABASES LIKE '$dbName'");
        $exists = $stmt->rowCount() > 0;
        
        if (!$exists) {
            $this->output("📝 Creating database: $dbName", Colors::BLUE);
            $this->pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->output("✅ Database created successfully", Colors::GREEN);
        } else {
            $this->output("✅ Database already exists", Colors::GREEN);
        }
        
        // Connect to the specific database
        $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
        $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']}"
        ]);
    }
    
    /**
     * Import schema from SQL file
     */
    private function importSchema(): void
    {
        $this->output("\n📋 Importing database schema...", Colors::YELLOW);
        
        $schemaFile = __DIR__ . '/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $sql = file_get_contents($schemaFile);
        
        if ($sql === false) {
            throw new Exception("Failed to read schema file");
        }
        
        // Split SQL into individual statements
        $statements = $this->splitSqlStatements($sql);
        
        $this->output("📊 Executing " . count($statements) . " SQL statements...", Colors::BLUE);
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $i => $statement) {
            $statement = trim($statement);
            
            if (empty($statement) || $statement === ';') {
                continue;
            }
            
            try {
                $this->pdo->exec($statement);
                $successCount++;
                
                if ($this->verbose && $successCount % 10 === 0) {
                    $this->output("  ✓ Executed $successCount statements...", Colors::GREEN);
                }
                
            } catch (PDOException $e) {
                $errorCount++;
                
                // Some errors are acceptable (like table already exists)
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $this->output("  ⚠️  Warning on statement " . ($i + 1) . ": " . $e->getMessage(), Colors::YELLOW);
                }
            }
        }
        
        $this->output("✅ Schema import completed: $successCount successful, $errorCount warnings", Colors::GREEN);
    }
    
    /**
     * Import sample data from SQL file
     */
    private function importSampleData(): void
    {
        $this->output("\n📋 Importing sample data...", Colors::YELLOW);
        
        $dataFile = __DIR__ . '/sample_data.sql';
        
        if (!file_exists($dataFile)) {
            $this->output("⚠️  Sample data file not found: $dataFile", Colors::YELLOW);
            return;
        }
        
        $this->importSqlFile($dataFile, 'sample data');
    }
    
    /**
     * Import Cameroonian foods from SQL file
     */
    private function importCameroonianFoods(): void
    {
        $this->output("\n🍲 Importing Cameroonian foods...", Colors::YELLOW);
        
        $dataFile = __DIR__ . '/cameroonian_foods.sql';
        
        if (!file_exists($dataFile)) {
            $this->output("⚠️  Cameroonian foods file not found: $dataFile", Colors::YELLOW);
            return;
        }
        
        $this->importSqlFile($dataFile, 'Cameroonian foods');
    }
    
    /**
     * Import SQL file with error handling
     */
    private function importSqlFile(string $filePath, string $description): void
    {
        $sql = file_get_contents($filePath);
        
        if ($sql === false) {
            throw new Exception("Failed to read $description file: $filePath");
        }
        
        // Split SQL into individual statements
        $statements = $this->splitSqlStatements($sql);
        
        $this->output("📊 Executing " . count($statements) . " $description statements...", Colors::BLUE);
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $i => $statement) {
            $statement = trim($statement);
            
            if (empty($statement) || $statement === ';') {
                continue;
            }
            
            try {
                $this->pdo->exec($statement);
                $successCount++;
                
                if ($this->verbose && $successCount % 5 === 0) {
                    $this->output("  ✓ Executed $successCount statements...", Colors::GREEN);
                }
                
            } catch (PDOException $e) {
                $errorCount++;
                
                // Some errors are acceptable (like duplicate entries)
                if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    $this->output("  ⚠️  Warning on statement " . ($i + 1) . ": " . $e->getMessage(), Colors::YELLOW);
                }
            }
        }
        
        $this->output("✅ $description import completed: $successCount successful, $errorCount warnings", Colors::GREEN);
    }
    
    /**
     * Split SQL file into individual statements
     */
    private function splitSqlStatements(string $sql): array
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon, but handle DELIMITER statements
        $statements = [];
        $currentStatement = '';
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Handle DELIMITER statements
            if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
                $delimiter = trim($matches[1]);
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // Check if statement ends with current delimiter
            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $statements[] = substr($currentStatement, 0, -strlen($delimiter) - 1);
                $currentStatement = '';
            }
        }
        
        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = $currentStatement;
        }
        
        return array_filter($statements, function($stmt) {
            return !empty(trim($stmt));
        });
    }
    
    /**
     * Verify the import was successful
     */
    private function verifyImport(): void
    {
        $this->output("\n🔍 Verifying database structure...", Colors::YELLOW);
        
        // Get list of tables
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $expectedTables = [
            'users', 'user_profiles', 'categories', 'restaurants', 'menu_items', 'menu_item_variants',
            'orders', 'order_items', 'order_status_history', 'deliveries', 'rider_schedules', 'rider_locations',
            'affiliates', 'affiliate_referrals', 'affiliate_payouts', 'payment_methods', 'payments',
            'reviews', 'review_votes', 'popup_notifications', 'messages', 'wishlists', 'cart_items',
            'disputes', 'site_settings', 'logs', 'coupons', 'coupon_usages', 'analytics', 'daily_stats'
        ];
        
        $missingTables = array_diff($expectedTables, $tables);
        
        if (empty($missingTables)) {
            $this->output("✅ All " . count($expectedTables) . " tables created successfully", Colors::GREEN);
        } else {
            $this->output("⚠️  Missing tables: " . implode(', ', $missingTables), Colors::YELLOW);
        }
        
        // Check sample data
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories");
        $categoryCount = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM site_settings");
        $settingsCount = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM restaurants");
        $restaurantCount = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM menu_items");
        $menuItemCount = $stmt->fetchColumn();
        
        // Check for Cameroonian foods specifically
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM menu_items WHERE name LIKE '%Yellow Soup%' OR name LIKE '%Pepper Soup%' OR name LIKE '%Lentil%'");
        $cameroonianFoodCount = $stmt->fetchColumn();
        
        $this->output("📊 Sample data: $categoryCount categories, $restaurantCount restaurants, $menuItemCount menu items", Colors::BLUE);
        $this->output("🍲 Cameroonian foods: $cameroonianFoodCount traditional dishes added", Colors::GREEN);
    }
    
    /**
     * Show import summary
     */
    private function showSummary(): void
    {
        $this->output("\n📈 Database Summary", Colors::MAGENTA);
        $this->output("==================", Colors::MAGENTA);
        
        // Get database size
        $stmt = $this->pdo->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = '{$this->config['database']}'
        ");
        $sizeInfo = $stmt->fetch();
        
        // Get table count
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as table_count 
            FROM information_schema.tables 
            WHERE table_schema = '{$this->config['database']}'
        ");
        $tableCount = $stmt->fetchColumn();
        
        $this->output("Database: {$this->config['database']}", Colors::WHITE);
        $this->output("Tables: $tableCount", Colors::WHITE);
        $this->output("Size: {$sizeInfo['size_mb']} MB", Colors::WHITE);
        $this->output("Charset: {$this->config['charset']}", Colors::WHITE);
    }
    
    /**
     * Output colored text to console
     */
    private function output(string $message, string $color = Colors::WHITE): void
    {
        echo $color . $message . Colors::RESET . "\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    // Command line execution
    $verbose = in_array('--verbose', $argv) || in_array('-v', $argv);
    $importer = new DatabaseImporter($config, $verbose);
    $importer->run();
} else {
    // Web execution
    header('Content-Type: text/plain');
    echo "Time2Eat Database Import\n";
    echo "========================\n\n";
    
    try {
        $importer = new DatabaseImporter($config, true);
        $importer->run();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        http_response_code(500);
    }
}
