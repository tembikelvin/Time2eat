<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class Dispute
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected string $table = 'disputes';
    
    protected array $fillable = [
        'order_id', 'initiator_id', 'type', 'subject', 'description', 'evidence',
        'status', 'priority', 'resolution', 'resolved_by', 'resolved_at',
        'compensation_amount'
    ];

    /**
     * Get all disputes with related data
     */
    public function getAllWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $whereConditions = [];
        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $whereConditions[] = 'd.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereConditions[] = 'd.type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['priority'])) {
            $whereConditions[] = 'd.priority = ?';
            $params[] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $whereConditions[] = '(d.subject LIKE ? OR d.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.total_amount as order_amount,
                CONCAT(u.first_name, ' ', u.last_name) as initiator_name,
                u.email as initiator_email,
                u.phone as initiator_phone,
                u.role as initiator_role,
                r.name as restaurant_name,
                CONCAT(resolver.first_name, ' ', resolver.last_name) as resolved_by_name
            FROM {$this->table} d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON d.initiator_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users resolver ON d.resolved_by = resolver.id
            {$whereClause}
            ORDER BY d.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get dispute by ID with full details
     */
    public function getByIdWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.total_amount as order_amount,
                o.status as order_status,
                o.created_at as order_date,
                CONCAT(u.first_name, ' ', u.last_name) as initiator_name,
                u.email as initiator_email,
                u.phone as initiator_phone,
                u.role as initiator_role,
                r.name as restaurant_name,
                r.id as restaurant_id,
                CONCAT(vendor.first_name, ' ', vendor.last_name) as vendor_name,
                vendor.email as vendor_email,
                CONCAT(rider.first_name, ' ', rider.last_name) as rider_name,
                rider.email as rider_email,
                CONCAT(resolver.first_name, ' ', resolver.last_name) as resolved_by_name
            FROM {$this->table} d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON d.initiator_id = u.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN users vendor ON r.user_id = vendor.id
            LEFT JOIN users rider ON o.rider_id = rider.id
            LEFT JOIN users resolver ON d.resolved_by = resolver.id
            WHERE d.id = ?
        ";

        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Create a new dispute
     */
    public function createDispute(array $data): int
    {
        $sql = "
            INSERT INTO {$this->table} (
                order_id, initiator_id, type, subject, description, evidence,
                status, priority, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $params = [
            $data['order_id'],
            $data['initiator_id'],
            $data['type'],
            $data['subject'],
            $data['description'],
            $data['evidence'] ?? null,
            $data['status'] ?? 'open',
            $data['priority'] ?? 'medium'
        ];

        return $this->insertRecord($sql, $params);
    }

    /**
     * Update dispute status
     */
    public function updateStatus(int $id, string $status, ?int $resolvedBy = null, ?string $resolution = null): bool
    {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW()";
        $params = [$status];

        if ($status === 'resolved' && $resolvedBy) {
            $sql .= ", resolved_by = ?, resolved_at = NOW()";
            $params[] = $resolvedBy;
        }

        if ($resolution) {
            $sql .= ", resolution = ?";
            $params[] = $resolution;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->update($sql, $params);
    }

    /**
     * Add compensation to dispute
     */
    public function addCompensation(int $id, float $amount): bool
    {
        $sql = "UPDATE {$this->table} SET compensation_amount = ?, updated_at = NOW() WHERE id = ?";
        return $this->update($sql, [$amount, $id]);
    }

    /**
     * Get dispute statistics
     */
    public function getStatistics(): array
    {
        $stats = [];

        // Total disputes
        $stats['total'] = $this->fetchOne("SELECT COUNT(*) as count FROM {$this->table}")['count'] ?? 0;

        // Open disputes
        $stats['open'] = $this->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE status IN ('open', 'investigating')")['count'] ?? 0;

        // Resolved today
        $stats['resolved_today'] = $this->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'resolved' AND DATE(resolved_at) = CURDATE()")['count'] ?? 0;

        // Urgent disputes
        $stats['urgent'] = $this->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE priority = 'urgent' AND status IN ('open', 'investigating')")['count'] ?? 0;

        // Average resolution time
        $avgHours = $this->fetchOne("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours 
            FROM {$this->table} 
            WHERE status = 'resolved' AND resolved_at IS NOT NULL
        ")['avg_hours'] ?? 0;
        
        $stats['avg_resolution_days'] = $avgHours ? round($avgHours / 24, 1) : 0;

        // Disputes by type
        $typeStats = $this->fetchAll("
            SELECT type, COUNT(*) as count 
            FROM {$this->table} 
            WHERE 1=1 
            GROUP BY type
        ");
        
        $stats['by_type'] = [];
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['type']] = $stat['count'];
        }

        return $stats;
    }

    /**
     * Get disputes by user ID
     */
    public function getByUserId(int $userId, int $limit = 20): array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.total_amount as order_amount,
                r.name as restaurant_name
            FROM {$this->table} d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            WHERE d.initiator_id = ?
            ORDER BY d.created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Get disputes for a restaurant
     */
    public function getByRestaurantId(int $restaurantId, int $limit = 20): array
    {
        $sql = "
            SELECT 
                d.*,
                o.order_number,
                o.total_amount as order_amount,
                CONCAT(u.first_name, ' ', u.last_name) as initiator_name,
                u.email as initiator_email
            FROM {$this->table} d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON d.initiator_id = u.id
            WHERE o.restaurant_id = ?
            ORDER BY d.created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$restaurantId, $limit]);
    }

    /**
     * Count total disputes with filters
     */
    public function countWithFilters(array $filters = []): int
    {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $whereConditions[] = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereConditions[] = 'type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['priority'])) {
            $whereConditions[] = 'priority = ?';
            $params[] = $filters['priority'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";

        return $this->fetchOne($sql, $params)['count'] ?? 0;
    }
}
