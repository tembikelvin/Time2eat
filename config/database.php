<?php
/**
 * Database Configuration and Connection Handler
 * Provides PDO connection with error handling and connection pooling
 */

class Database {
    private static $instance = null;
    private $connection = null;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    
    private function __construct() {
        // Ensure config.php is loaded first to set DB_* constants
        if (!defined('DB_HOST')) {
            $configPath = __DIR__ . '/config.php';
            if (file_exists($configPath)) {
                require_once $configPath;
            }
        }
        
        // Load configuration from environment or defaults
        $this->host = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
        $this->dbname = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'time2eat');
        $this->username = defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root');
        $this->password = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : ($_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4');
        
        // Log if using defaults (helps debug)
        if ($this->host === 'localhost' && $this->username === 'root' && empty($this->password)) {
            error_log("Database: Using default credentials - environment variables may not be loaded correctly");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                    PDO::ATTR_PERSISTENT => false, // Disabled to prevent connection pool exhaustion in production
                ];
                
                $this->connection = new PDO($dsn, $this->username, $this->password, $options);
                
                // Set timezone to match PHP
                $this->connection->exec("SET time_zone = '+01:00'"); // WAT (West Africa Time)
                
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->connection;
    }
    
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
    
    public function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }
    
    public function query($sql) {
        return $this->getConnection()->query($sql);
    }
    
    public function exec($sql) {
        return $this->getConnection()->exec($sql);
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Database helper functions
if (!function_exists('db')) {
    function db() {
        return Database::getInstance();
    }
}

if (!function_exists('dbConnection')) {
    function dbConnection() {
        return Database::getInstance()->getConnection();
    }
}

// Test database connection on first load
try {
    $testConnection = Database::getInstance()->getConnection();
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log("Database connection successful");
    }
} catch (Exception $e) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log("Database connection failed: " . $e->getMessage());
    }
    // Don't throw exception here to allow installer to run
}
