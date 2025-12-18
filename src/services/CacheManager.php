<?php
/**
 * Time2Eat Cache Manager
 * Advanced caching system with multiple storage backends
 */

declare(strict_types=1);

class CacheManager
{
    private string $cacheDir;
    private array $config;
    private Database $db;
    private array $memoryCache = [];
    private int $hitCount = 0;
    private int $missCount = 0;
    
    public function __construct()
    {
        $this->cacheDir = ROOT_PATH . '/storage/cache';
        $this->db = Database::getInstance();
        $this->config = [
            'default_ttl' => 3600, // 1 hour
            'max_memory_items' => 1000,
            'compression' => true,
            'serialization' => 'serialize', // serialize, json, igbinary
            'storage_backends' => ['file', 'database', 'memory']
        ];
        
        $this->ensureDirectories();
        $this->createCacheTable();
    }
    
    /**
     * Get cached value
     */
    public function get(string $key, $default = null)
    {
        // Try memory cache first
        if (isset($this->memoryCache[$key])) {
            $item = $this->memoryCache[$key];
            if ($item['expires'] === 0 || $item['expires'] > time()) {
                $this->hitCount++;
                return $item['data'];
            } else {
                unset($this->memoryCache[$key]);
            }
        }
        
        // Try file cache
        $value = $this->getFromFile($key);
        if ($value !== null) {
            $this->hitCount++;
            $this->setInMemory($key, $value, 0); // Store in memory without expiration
            return $value;
        }
        
        // Try database cache
        $value = $this->getFromDatabase($key);
        if ($value !== null) {
            $this->hitCount++;
            $this->setInMemory($key, $value, 0);
            return $value;
        }
        
        $this->missCount++;
        return $default;
    }
    
    /**
     * Set cached value
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $expires = $ttl > 0 ? time() + $ttl : 0;
        
        $success = true;
        
        // Store in memory
        $this->setInMemory($key, $value, $expires);
        
        // Store in file
        if (!$this->setInFile($key, $value, $expires)) {
            $success = false;
        }
        
        // Store in database
        if (!$this->setInDatabase($key, $value, $expires)) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * Delete cached value
     */
    public function delete(string $key): bool
    {
        $success = true;
        
        // Remove from memory
        unset($this->memoryCache[$key]);
        
        // Remove from file
        if (!$this->deleteFromFile($key)) {
            $success = false;
        }
        
        // Remove from database
        if (!$this->deleteFromDatabase($key)) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $success = true;
        
        // Clear memory
        $this->memoryCache = [];
        
        // Clear file cache
        if (!$this->clearFileCache()) {
            $success = false;
        }
        
        // Clear database cache
        if (!$this->clearDatabaseCache()) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Get multiple cached values
     */
    public function getMultiple(array $keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * Set multiple cached values
     */
    public function setMultiple(array $values, int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Delete multiple cached values
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Cache with callback
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Cache forever (until manually deleted)
     */
    public function forever(string $key, $value): bool
    {
        return $this->set($key, $value, 0);
    }
    
    /**
     * Increment cached numeric value
     */
    public function increment(string $key, int $value = 1): int
    {
        $current = (int)$this->get($key, 0);
        $new = $current + $value;
        $this->set($key, $new);
        return $new;
    }
    
    /**
     * Decrement cached numeric value
     */
    public function decrement(string $key, int $value = 1): int
    {
        $current = (int)$this->get($key, 0);
        $new = $current - $value;
        $this->set($key, $new);
        return $new;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'hits' => $this->hitCount,
            'misses' => $this->missCount,
            'hit_ratio' => $this->hitCount + $this->missCount > 0 
                ? round(($this->hitCount / ($this->hitCount + $this->missCount)) * 100, 2) 
                : 0,
            'memory_items' => count($this->memoryCache),
            'file_cache_size' => $this->getFileCacheSize(),
            'database_cache_count' => $this->getDatabaseCacheCount()
        ];
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanExpired(): array
    {
        $cleaned = [
            'memory' => $this->cleanExpiredMemory(),
            'file' => $this->cleanExpiredFiles(),
            'database' => $this->cleanExpiredDatabase()
        ];
        
        return $cleaned;
    }
    
    /**
     * Get from file cache
     */
    private function getFromFile(string $key): mixed
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }
        
        $data = $this->unserializeData($content);
        
        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            return null;
        }
        
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            unlink($filePath);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Set in file cache
     */
    private function setInFile(string $key, $value, int $expires): bool
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];
        
        $content = $this->serializeData($data);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filePath, $content, LOCK_EX) !== false;
    }
    
    /**
     * Delete from file cache
     */
    private function deleteFromFile(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Get from database cache
     */
    private function getFromDatabase(string $key): mixed
    {
        try {
            $stmt = $this->db->prepare("
                SELECT cache_value, expires_at 
                FROM cache_entries 
                WHERE cache_key = ? AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }
            
            return $this->unserializeData($row['cache_value']);
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Set in database cache
     */
    private function setInDatabase(string $key, $value, int $expires): bool
    {
        try {
            $expiresAt = $expires > 0 ? date('Y-m-d H:i:s', $expires) : null;
            $serializedValue = $this->serializeData($value);
            
            $stmt = $this->db->prepare("
                INSERT INTO cache_entries (cache_key, cache_value, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                cache_value = VALUES(cache_value), 
                expires_at = VALUES(expires_at),
                updated_at = NOW()
            ");
            
            return $stmt->execute([$key, $serializedValue, $expiresAt]);
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete from database cache
     */
    private function deleteFromDatabase(string $key): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cache_entries WHERE cache_key = ?");
            return $stmt->execute([$key]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Set in memory cache
     */
    private function setInMemory(string $key, $value, int $expires): void
    {
        // Limit memory cache size
        if (count($this->memoryCache) >= $this->config['max_memory_items']) {
            // Remove oldest item
            $oldestKey = array_key_first($this->memoryCache);
            unset($this->memoryCache[$oldestKey]);
        }
        
        $this->memoryCache[$key] = [
            'data' => $value,
            'expires' => $expires,
            'created' => time()
        ];
    }
    
    /**
     * Get file path for cache key
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $dir1 = substr($hash, 0, 2);
        $dir2 = substr($hash, 2, 2);
        
        return $this->cacheDir . "/files/{$dir1}/{$dir2}/{$hash}.cache";
    }
    
    /**
     * Serialize data for storage
     */
    private function serializeData($data): string
    {
        $serialized = serialize($data);
        
        if ($this->config['compression'] && function_exists('gzcompress')) {
            $serialized = gzcompress($serialized, 6);
        }
        
        return $serialized;
    }
    
    /**
     * Unserialize data from storage
     */
    private function unserializeData(string $data): mixed
    {
        if ($this->config['compression'] && function_exists('gzuncompress')) {
            $decompressed = @gzuncompress($data);
            if ($decompressed !== false) {
                $data = $decompressed;
            }
        }
        
        return unserialize($data);
    }
    
    /**
     * Clear file cache
     */
    private function clearFileCache(): bool
    {
        $filesCacheDir = $this->cacheDir . '/files';
        
        if (!is_dir($filesCacheDir)) {
            return true;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filesCacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            } elseif ($file->isDir()) {
                rmdir($file->getPathname());
            }
        }
        
        return true;
    }
    
    /**
     * Clear database cache
     */
    private function clearDatabaseCache(): bool
    {
        try {
            $this->db->exec("DELETE FROM cache_entries");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Clean expired memory cache
     */
    private function cleanExpiredMemory(): int
    {
        $cleaned = 0;
        $now = time();
        
        foreach ($this->memoryCache as $key => $item) {
            if ($item['expires'] > 0 && $item['expires'] < $now) {
                unset($this->memoryCache[$key]);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Clean expired file cache
     */
    private function cleanExpiredFiles(): int
    {
        $cleaned = 0;
        $filesCacheDir = $this->cacheDir . '/files';
        
        if (!is_dir($filesCacheDir)) {
            return 0;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filesCacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                $data = $this->unserializeData($content);
                
                if (is_array($data) && isset($data['expires']) && 
                    $data['expires'] > 0 && $data['expires'] < time()) {
                    unlink($file->getPathname());
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Clean expired database cache
     */
    private function cleanExpiredDatabase(): int
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cache_entries WHERE expires_at IS NOT NULL AND expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get file cache size
     */
    private function getFileCacheSize(): int
    {
        $size = 0;
        $filesCacheDir = $this->cacheDir . '/files';
        
        if (!is_dir($filesCacheDir)) {
            return 0;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filesCacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Get database cache count
     */
    private function getDatabaseCacheCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM cache_entries");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Create cache table
     */
    private function createCacheTable(): void
    {
        try {
            $sql = "
            CREATE TABLE IF NOT EXISTS cache_entries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cache_key VARCHAR(255) NOT NULL UNIQUE,
                cache_value LONGTEXT NOT NULL,
                expires_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_cache_key (cache_key),
                INDEX idx_expires_at (expires_at)
            )";
            
            $this->db->exec($sql);
        } catch (Exception $e) {
            // Table creation failed, continue without database caching
        }
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $directories = [
            $this->cacheDir,
            $this->cacheDir . '/files',
            $this->cacheDir . '/queries',
            $this->cacheDir . '/images'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
