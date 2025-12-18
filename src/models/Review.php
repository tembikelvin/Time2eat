<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Review Model
 * Manages restaurant reviews and ratings
 */
class Review
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    protected $table = 'reviews';

    /**
     * Get reviews for a restaurant
     */
    public function getByRestaurant(int $restaurantId, int $limit = 20, int $offset = 0): array
    {
        try {
        $sql = "
                SELECT 
                    r.*,
                    r.comment as review_text,
                    u.first_name,
                    u.last_name,
                    u.avatar as profile_image,
                   o.order_number,
                    o.created_at as order_date
            FROM {$this->table} r
                JOIN users u ON r.user_id = u.id
            LEFT JOIN orders o ON r.order_id = o.id
                WHERE r.reviewable_type = 'restaurant' 
                AND r.reviewable_id = ? 
                AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            return $this->fetchAll($sql, [$restaurantId, $limit, $offset]);
        } catch (\Exception $e) {
            error_log("Error in getByRestaurant: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get review statistics for a restaurant
     */
    public function getRestaurantStats(int $restaurantId): array
    {
        $sql = "
            SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_stars,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_stars,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_stars,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_stars,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            FROM {$this->table}
            WHERE reviewable_type = 'restaurant' 
            AND reviewable_id = ? 
            AND status = 'approved'
        ";
        
        $result = $this->fetchOne($sql, [$restaurantId]);
        
        if (!$result) {
            return [
                'average_rating' => 0,
                'total_reviews' => 0,
                'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
            ];
        }

        return [
            'average_rating' => round((float)$result['average_rating'], 1),
            'total_reviews' => (int)$result['total_reviews'],
            'distribution' => [
                5 => (int)$result['five_stars'],
                4 => (int)$result['four_stars'], 
                3 => (int)$result['three_stars'],
                2 => (int)$result['two_stars'],
                1 => (int)$result['one_star']
            ]
        ];
    }

    /**
     * Get recent review trends
     */
    public function getRecentTrends(int $restaurantId): array
    {
        try {
            // This week's average
            $thisWeekSql = "
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM {$this->table}
                WHERE reviewable_type = 'restaurant' AND reviewable_id = ? AND status = 'approved'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ";
            $thisWeek = $this->fetchOne($thisWeekSql, [$restaurantId]);
            
            // Last week's average  
            $lastWeekSql = "
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM {$this->table}
                WHERE reviewable_type = 'restaurant' AND reviewable_id = ? AND status = 'approved'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ";
            $lastWeek = $this->fetchOne($lastWeekSql, [$restaurantId]);
            
            // This month's average
            $thisMonthSql = "
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM {$this->table}
                WHERE reviewable_type = 'restaurant' AND reviewable_id = ? AND status = 'approved'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $thisMonth = $this->fetchOne($thisMonthSql, [$restaurantId]);
            
            // Response rate (reviews with restaurant response)
            $responseRateSql = "
                SELECT 
                    COUNT(*) as total_reviews,
                    COUNT(CASE WHEN response IS NOT NULL THEN 1 END) as responded_reviews
                FROM {$this->table}
                WHERE reviewable_type = 'restaurant' AND reviewable_id = ? AND status = 'approved'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $responseData = $this->fetchOne($responseRateSql, [$restaurantId]);
            
            $thisWeekRating = round((float)($thisWeek['avg_rating'] ?? 0), 1);
            $lastWeekRating = round((float)($lastWeek['avg_rating'] ?? 0), 1);
            $thisMonthRating = round((float)($thisMonth['avg_rating'] ?? 0), 1);
            
            $totalReviews = (int)($responseData['total_reviews'] ?? 0);
            $respondedReviews = (int)($responseData['responded_reviews'] ?? 0);
            $responseRate = $totalReviews > 0 ? round(($respondedReviews / $totalReviews) * 100) : 0;
            
            return [
                'this_week' => [
                    'rating' => $thisWeekRating,
                    'change' => $lastWeekRating > 0 ? round($thisWeekRating - $lastWeekRating, 1) : 0,
                    'count' => (int)($thisWeek['review_count'] ?? 0)
                ],
                'this_month' => [
                    'rating' => $thisMonthRating,
                    'count' => (int)($thisMonth['review_count'] ?? 0)
                ],
                'response_rate' => $responseRate
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting review trends: " . $e->getMessage());
            return [
                'this_week' => ['rating' => 0, 'change' => 0, 'count' => 0],
                'this_month' => ['rating' => 0, 'count' => 0],
                'response_rate' => 0
            ];
        }
    }

    /**
     * Count reviews by restaurant
     */
    public function countByRestaurant(int $restaurantId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE reviewable_type = 'restaurant' AND reviewable_id = ? AND status = 'approved'";
        $result = $this->fetchOne($sql, [$restaurantId]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Add restaurant response to review
     */
    public function addResponse(int $reviewId, string $response, int $restaurantId): bool
    {
        $sql = "
            UPDATE {$this->table} 
            SET response = ?, responded_at = NOW(), updated_at = NOW()
            WHERE id = ? AND reviewable_type = 'restaurant' AND reviewable_id = ?
        ";
        
        return $this->execute($sql, [$response, $reviewId, $restaurantId]) > 0;
    }

    /**
     * Create review from order data (fallback method)
     */
    public function createFromOrder(array $orderData): ?int
    {
        $data = [
            'user_id' => $orderData['customer_id'],
            'order_id' => $orderData['order_id'],
            'reviewable_type' => 'restaurant',
            'reviewable_id' => $orderData['restaurant_id'],
            'rating' => $orderData['rating'] ?? 4,
            'comment' => $orderData['review_text'] ?? '',
            'status' => 'pending',
            'is_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Get reviews by entity type and ID
     */
    public function getReviewsByEntity(string $type, int $id, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT 
                r.*,
                r.comment as review_text,
                u.first_name,
                u.last_name,
                u.avatar as profile_image,
                o.order_number,
                o.created_at as order_date
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN orders o ON r.order_id = o.id
            WHERE r.reviewable_type = ? 
            AND r.reviewable_id = ? 
            AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->fetchAll($sql, [$type, $id, $limit, $offset]);
    }

    /**
     * Get review statistics by entity
     */
    public function getReviewStats(string $type, int $id): array
    {
        $sql = "
            SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_stars,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_stars,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_stars,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_stars,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            FROM {$this->table}
            WHERE reviewable_type = ? 
            AND reviewable_id = ? 
            AND status = 'approved'
        ";
        
        $result = $this->fetchOne($sql, [$type, $id]);
        
        if (!$result) {
            return [
                'average_rating' => 0,
                'total_reviews' => 0,
                'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
            ];
        }

        return [
            'average_rating' => round((float)$result['average_rating'], 1),
            'total_reviews' => (int)$result['total_reviews'],
            'distribution' => [
                5 => (int)$result['five_stars'],
                4 => (int)$result['four_stars'], 
                3 => (int)$result['three_stars'],
                2 => (int)$result['two_stars'],
                1 => (int)$result['one_star']
            ]
        ];
    }

    /**
     * Get order review
     */
    public function getOrderReview(int $orderId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? AND reviewable_type = 'restaurant' LIMIT 1";
        return $this->fetchOne($sql, [$orderId]);
    }

    /**
     * Toggle helpful status
     */
    public function toggleHelpful(int $reviewId, int $userId): bool
    {
        try {
            $this->beginTransaction();
            
            $existing = $this->fetchOne(
                "SELECT * FROM review_helpful WHERE review_id = ? AND user_id = ?",
                [$reviewId, $userId]
            );
            
            if ($existing) {
                $this->execute(
                    "DELETE FROM review_helpful WHERE review_id = ? AND user_id = ?",
                    [$reviewId, $userId]
                );
                $this->execute(
                    "UPDATE {$this->table} SET helpful_count = helpful_count - 1 WHERE id = ?",
                    [$reviewId]
                );
            } else {
                $this->execute(
                    "INSERT INTO review_helpful (review_id, user_id, created_at) VALUES (?, ?, NOW())",
                    [$reviewId, $userId]
                );
                $this->execute(
                    "UPDATE {$this->table} SET helpful_count = helpful_count + 1 WHERE id = ?",
                    [$reviewId]
                );
            }
            
            $this->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Error toggling helpful: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get helpful count
     */
    public function getHelpfulCount(int $reviewId): int
    {
        $result = $this->fetchOne(
            "SELECT helpful_count FROM {$this->table} WHERE id = ?",
            [$reviewId]
        );
        return (int)($result['helpful_count'] ?? 0);
    }

    /**
     * Report review
     */
    public function reportReview(int $reviewId, int $userId, string $reason): bool
    {
        try {
            $this->execute(
                "INSERT INTO review_reports (review_id, user_id, reason, created_at) VALUES (?, ?, ?, NOW())",
                [$reviewId, $userId, $reason]
            );
            return true;
        } catch (\Exception $e) {
            error_log("Error reporting review: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reviews with filters
     */
    public function getFilteredReviews(int $restaurantId, array $filters = []): array
    {
        $sql = "
            SELECT 
                r.*,
                r.comment as review_text,
                u.first_name,
                u.last_name,
                u.avatar as profile_image,
                o.order_number,
                o.created_at as order_date
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN orders o ON r.order_id = o.id
            WHERE r.reviewable_type = 'restaurant' 
            AND r.reviewable_id = ? 
            AND r.status = 'approved'
        ";
        
        $params = [$restaurantId];
        
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = (int)$filters['rating'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND r.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND r.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        return $this->fetchAll($sql, $params);
    }
}