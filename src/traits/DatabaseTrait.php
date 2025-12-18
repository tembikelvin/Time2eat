<?php

declare(strict_types=1);

namespace traits;

/**
 * Database Trait
 * Provides database connection and query methods
 */
trait DatabaseTrait
{
    /**
     * Get database connection
     *
     * Note: This trait expects the class using it to have a $db property.
     * If the class extends Model, $db will be a Database instance.
     * If used standalone, $db should be a PDO instance or null.
     */
    protected function getDb(): \PDO
    {
        // If $this->db is already set by parent class (e.g., Model)
        if (isset($this->db) && $this->db !== null) {
            // Check if it's a Database instance (from Model class)
            if ($this->db instanceof \Database) {
                return $this->db->getConnection();
            }
            // If it's already a PDO instance
            if ($this->db instanceof \PDO) {
                return $this->db;
            }
        }

        // Create new connection if not set
        if (!isset($this->db) || $this->db === null) {
            $this->db = $this->createConnection();
        }

        return $this->db;
    }
    
    /**
     * Create database connection
     */
    private function createConnection(): \PDO
    {
        // Load config first (defines DB constants), then database
        require_once __DIR__ . '/../../config/config.php';
        require_once __DIR__ . '/../../config/database.php';

        try {
            $database = \Database::getInstance();
            return $database->getConnection();
        } catch (\Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("DB Config - Host: " . (defined('DB_HOST') ? DB_HOST : 'undefined') .
                     ", Name: " . (defined('DB_NAME') ? DB_NAME : 'undefined') .
                     ", User: " . (defined('DB_USER') ? DB_USER : 'undefined'));
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute query with parameters
     * Note: Signature must match core\Model::query() for compatibility
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log("Query failed: {$sql} - " . $e->getMessage());
            error_log("Query params: " . json_encode($params));
            throw new \Exception("Database query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch single row
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Fetch all rows
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert record and return ID
     */
    protected function insertRecord(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);

        return (int) $this->getDb()->lastInsertId();
    }

    /**
     * Update records
     */
    protected function updateRecord(string $table, array $data, array $where): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);

        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :where_{$column}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";

        // Prefix where parameters to avoid conflicts
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_{$key}"] = $value;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete records
     */
    protected function deleteRecord(string $table, array $where): int
    {
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :{$column}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        $stmt = $this->query($sql, $where);

        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     * Note: Must be public to match core\Model::beginTransaction()
     */
    public function beginTransaction(): void
    {
        $this->getDb()->beginTransaction();
    }

    /**
     * Commit transaction
     * Note: Must be public to match core\Model::commit()
     */
    public function commit(): void
    {
        $this->getDb()->commit();
    }

    /**
     * Rollback transaction
     * Note: Must be public to match core\Model::rollback()
     */
    public function rollback(): void
    {
        $this->getDb()->rollBack();
    }
    
    /**
     * Execute in transaction
     */
    protected function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Build WHERE clause from array
     */
    protected function buildWhereClause(array $conditions): array
    {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle IN clause
                $placeholders = [];
                foreach ($value as $i => $v) {
                    $placeholder = ":{$column}_{$i}";
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $v;
                }
                $whereParts[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
            } elseif (is_null($value)) {
                $whereParts[] = "{$column} IS NULL";
            } else {
                $whereParts[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
        }
        
        return [
            'clause' => implode(' AND ', $whereParts),
            'params' => $params
        ];
    }
    
    /**
     * Paginate results with custom SQL
     */
    protected function paginateQuery(string $sql, array $params, int $page = 1, int $perPage = 20): array
    {
        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $totalResult = $this->fetchOne($countSql, $params);
        $total = (int) $totalResult['total'];

        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $totalPages = (int) ceil($total / $perPage);

        // Get paginated results
        $paginatedSql = "{$sql} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->fetchAll($paginatedSql, $params);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Search with full-text search
     */
    protected function fullTextSearch(string $table, array $columns, string $query, array $where = []): array
    {
        $searchColumns = implode(', ', $columns);
        
        $sql = "SELECT *, MATCH({$searchColumns}) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance 
                FROM {$table} 
                WHERE MATCH({$searchColumns}) AGAINST(:query IN NATURAL LANGUAGE MODE)";
        
        $params = ['query' => $query];
        
        if (!empty($where)) {
            $whereClause = $this->buildWhereClause($where);
            $sql .= " AND " . $whereClause['clause'];
            $params = array_merge($params, $whereClause['params']);
        }
        
        $sql .= " ORDER BY relevance DESC";
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Get table schema
     */
    protected function getTableSchema(string $table): array
    {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }
    
    /**
     * Check if table exists
     */
    protected function tableExists(string $table): bool
    {
        $sql = "SHOW TABLES LIKE '" . $table . "'";
        $result = $this->fetchOne($sql);
        return $result !== null;
    }
    
    /**
     * Escape identifier (table/column names)
     */
    protected function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Get last insert ID
     */
    protected function getLastInsertId(): int
    {
        return (int) $this->getDb()->lastInsertId();
    }
    
    /**
     * Close database connection
     */
    protected function closeConnection(): void
    {
        $this->db = null;
    }
}
