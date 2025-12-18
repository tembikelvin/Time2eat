<?php

namespace Time2Eat\Services;

require_once __DIR__ . '/../../config/database.php';

/**
 * User Trust Service
 * Determines user trustworthiness for cash on delivery eligibility
 */
class UserTrustService
{
    private $db;

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    /**
     * Check if user is eligible for cash on delivery
     */
    public function isEligibleForCOD(int $userId): array
    {
        try {
            $trustScore = $this->calculateTrustScore($userId);
            $requirements = $this->getCODRequirements();
            
            $isEligible = $trustScore['total_score'] >= $requirements['minimum_score'];
            
            return [
                'eligible' => $isEligible,
                'trust_score' => $trustScore,
                'requirements' => $requirements,
                'reason' => $isEligible ? 'User meets trust requirements' : $this->getIneligibilityReason($trustScore, $requirements)
            ];

        } catch (\Exception $e) {
            error_log("Error checking COD eligibility: " . $e->getMessage());
            return [
                'eligible' => false,
                'trust_score' => ['total_score' => 0],
                'requirements' => $this->getCODRequirements(),
                'reason' => 'Unable to verify user trustworthiness'
            ];
        }
    }

    /**
     * Calculate comprehensive trust score for user
     */
    public function calculateTrustScore(int $userId): array
    {
        $scores = [
            'order_history' => $this->getOrderHistoryScore($userId),
            'payment_reliability' => $this->getPaymentReliabilityScore($userId),
            'account_age' => $this->getAccountAgeScore($userId),
            'delivery_success' => $this->getDeliverySuccessScore($userId),
            'cancellation_rate' => $this->getCancellationRateScore($userId),
            'rating_consistency' => $this->getRatingConsistencyScore($userId)
        ];

        $totalScore = array_sum($scores);
        $maxPossibleScore = count($scores) * 100; // Each category max 100 points

        return [
            'total_score' => $totalScore,
            'max_possible' => $maxPossibleScore,
            'percentage' => round(($totalScore / $maxPossibleScore) * 100, 2),
            'breakdown' => $scores
        ];
    }

    /**
     * Get order history score (0-100)
     */
    private function getOrderHistoryScore(int $userId): int
    {
        try {
            // Count completed orders
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_orders,
                       SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
                       AVG(total_amount) as avg_order_value
                FROM orders 
                WHERE customer_id = ? 
                AND status IN ('delivered', 'cancelled', 'refunded')
            ");
            $stmt->execute([$userId]);
            $data = $stmt->fetch();

            $totalOrders = (int)$data['total_orders'];
            $completedOrders = (int)$data['completed_orders'];
            $avgOrderValue = (float)$data['avg_order_value'];

            // Score based on number of orders
            $orderScore = min(100, ($totalOrders * 2)); // 2 points per order, max 100

            // Bonus for high average order value
            $valueBonus = 0;
            if ($avgOrderValue > 5000) $valueBonus = 10;
            elseif ($avgOrderValue > 3000) $valueBonus = 5;

            // Penalty for low completion rate
            $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) : 0;
            $completionPenalty = $completionRate < 0.8 ? -20 : 0;

            return max(0, min(100, $orderScore + $valueBonus + $completionPenalty));

        } catch (\Exception $e) {
            error_log("Error calculating order history score: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get payment reliability score (0-100)
     */
    private function getPaymentReliabilityScore(int $userId): int
    {
        try {
            // Check payment history
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_payments,
                       SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as successful_payments,
                       SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments
                FROM orders 
                WHERE customer_id = ? 
                AND payment_status IN ('paid', 'failed', 'refunded')
            ");
            $stmt->execute([$userId]);
            $data = $stmt->fetch();

            $totalPayments = (int)$data['total_payments'];
            $successfulPayments = (int)$data['successful_payments'];
            $failedPayments = (int)$data['failed_payments'];

            if ($totalPayments == 0) return 50; // Neutral score for new users

            $successRate = $successfulPayments / $totalPayments;
            $failureRate = $failedPayments / $totalPayments;

            // Base score on success rate
            $baseScore = $successRate * 100;

            // Penalty for high failure rate
            $failurePenalty = $failureRate > 0.1 ? -30 : 0;

            // Bonus for many successful payments
            $volumeBonus = min(20, $successfulPayments * 2);

            return max(0, min(100, $baseScore + $failurePenalty + $volumeBonus));

        } catch (\Exception $e) {
            error_log("Error calculating payment reliability score: " . $e->getMessage());
            return 50;
        }
    }

    /**
     * Get account age score (0-100)
     */
    private function getAccountAgeScore(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT created_at, last_login_at
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) return 0;

            $accountAge = time() - strtotime($user['created_at']);
            $daysOld = $accountAge / (24 * 60 * 60);

            // Score based on account age
            if ($daysOld >= 365) return 100; // 1+ year
            if ($daysOld >= 180) return 80;  // 6+ months
            if ($daysOld >= 90) return 60;   // 3+ months
            if ($daysOld >= 30) return 40;   // 1+ month
            if ($daysOld >= 7) return 20;    // 1+ week
            return 10; // New account

        } catch (\Exception $e) {
            error_log("Error calculating account age score: " . $e->getMessage());
            return 50;
        }
    }

    /**
     * Get delivery success score (0-100)
     */
    private function getDeliverySuccessScore(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_deliveries,
                       SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as successful_deliveries,
                       SUM(CASE WHEN status = 'cancelled' AND cancellation_reason LIKE '%customer%' THEN 1 ELSE 0 END) as customer_cancellations
                FROM orders 
                WHERE customer_id = ? 
                AND status IN ('delivered', 'cancelled')
            ");
            $stmt->execute([$userId]);
            $data = $stmt->fetch();

            $totalDeliveries = (int)$data['total_deliveries'];
            $successfulDeliveries = (int)$data['successful_deliveries'];
            $customerCancellations = (int)$data['customer_cancellations'];

            if ($totalDeliveries == 0) return 50;

            $successRate = $successfulDeliveries / $totalDeliveries;
            $cancellationRate = $customerCancellations / $totalDeliveries;

            $baseScore = $successRate * 100;
            $cancellationPenalty = $cancellationRate > 0.2 ? -25 : 0;

            return max(0, min(100, $baseScore + $cancellationPenalty));

        } catch (\Exception $e) {
            error_log("Error calculating delivery success score: " . $e->getMessage());
            return 50;
        }
    }

    /**
     * Get cancellation rate score (0-100)
     */
    private function getCancellationRateScore(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_orders,
                       SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
                FROM orders 
                WHERE customer_id = ? 
                AND status IN ('delivered', 'cancelled', 'refunded')
            ");
            $stmt->execute([$userId]);
            $data = $stmt->fetch();

            $totalOrders = (int)$data['total_orders'];
            $cancelledOrders = (int)$data['cancelled_orders'];

            if ($totalOrders == 0) return 50;

            $cancellationRate = $cancelledOrders / $totalOrders;

            // Score inversely related to cancellation rate
            if ($cancellationRate <= 0.05) return 100; // 5% or less
            if ($cancellationRate <= 0.10) return 80;  // 10% or less
            if ($cancellationRate <= 0.20) return 60;  // 20% or less
            if ($cancellationRate <= 0.30) return 40;  // 30% or less
            return 20; // More than 30%

        } catch (\Exception $e) {
            error_log("Error calculating cancellation rate score: " . $e->getMessage());
            return 50;
        }
    }

    /**
     * Get rating consistency score (0-100)
     */
    private function getRatingConsistencyScore(int $userId): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(rating) as avg_rating,
                       COUNT(rating) as rated_orders
                FROM orders 
                WHERE customer_id = ? 
                AND rating IS NOT NULL
            ");
            $stmt->execute([$userId]);
            $data = $stmt->fetch();

            $avgRating = (float)$data['avg_rating'];
            $ratedOrders = (int)$data['rated_orders'];

            if ($ratedOrders == 0) return 50; // No ratings yet

            // Score based on average rating
            $ratingScore = ($avgRating / 5) * 100;

            // Bonus for consistent rating behavior
            $consistencyBonus = min(20, $ratedOrders * 2);

            return max(0, min(100, $ratingScore + $consistencyBonus));

        } catch (\Exception $e) {
            error_log("Error calculating rating consistency score: " . $e->getMessage());
            return 50;
        }
    }

    /**
     * Get COD requirements from settings
     */
    private function getCODRequirements(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT value FROM site_settings 
                WHERE `key` = 'cod_minimum_score'
            ");
            $stmt->execute();
            $result = $stmt->fetch();

            return [
                'minimum_score' => (int)($result['value'] ?? 70),
                'minimum_orders' => 50,
                'minimum_account_age_days' => 30,
                'maximum_cancellation_rate' => 0.2
            ];

        } catch (\Exception $e) {
            error_log("Error getting COD requirements: " . $e->getMessage());
            return [
                'minimum_score' => 70,
                'minimum_orders' => 50,
                'minimum_account_age_days' => 30,
                'maximum_cancellation_rate' => 0.2
            ];
        }
    }

    /**
     * Get reason for ineligibility
     */
    private function getIneligibilityReason(array $trustScore, array $requirements): string
    {
        $reasons = [];

        if ($trustScore['breakdown']['order_history'] < 50) {
            $reasons[] = "Insufficient order history (need 50+ orders)";
        }

        if ($trustScore['breakdown']['payment_reliability'] < 60) {
            $reasons[] = "Poor payment reliability";
        }

        if ($trustScore['breakdown']['account_age'] < 40) {
            $reasons[] = "Account too new (need 30+ days)";
        }

        if ($trustScore['breakdown']['cancellation_rate'] < 60) {
            $reasons[] = "High cancellation rate";
        }

        if ($trustScore['total_score'] < $requirements['minimum_score']) {
            $reasons[] = "Overall trust score too low (need {$requirements['minimum_score']}+)";
        }

        return implode(', ', $reasons);
    }

    /**
     * Get user trust summary for admin
     */
    public function getUserTrustSummary(int $userId): array
    {
        $trustScore = $this->calculateTrustScore($userId);
        $codEligibility = $this->isEligibleForCOD($userId);

        // Get additional user stats
        $stmt = $this->db->prepare("
            SELECT 
                u.first_name,
                u.last_name,
                u.email,
                u.created_at,
                COUNT(o.id) as total_orders,
                SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                AVG(o.rating) as avg_rating,
                SUM(o.total_amount) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.customer_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$userId]);
        $userStats = $stmt->fetch();

        return [
            'user' => $userStats,
            'trust_score' => $trustScore,
            'cod_eligible' => $codEligibility['eligible'],
            'cod_reason' => $codEligibility['reason'],
            'recommendations' => $this->getTrustRecommendations($trustScore)
        ];
    }

    /**
     * Get recommendations to improve trust score
     */
    private function getTrustRecommendations(array $trustScore): array
    {
        $recommendations = [];

        if ($trustScore['breakdown']['order_history'] < 70) {
            $recommendations[] = "Place more orders to build order history";
        }

        if ($trustScore['breakdown']['payment_reliability'] < 70) {
            $recommendations[] = "Ensure timely payment completion";
        }

        if ($trustScore['breakdown']['account_age'] < 60) {
            $recommendations[] = "Account needs more time to establish trust";
        }

        if ($trustScore['breakdown']['cancellation_rate'] < 70) {
            $recommendations[] = "Reduce order cancellations";
        }

        if ($trustScore['breakdown']['rating_consistency'] < 70) {
            $recommendations[] = "Provide consistent ratings for orders";
        }

        return $recommendations;
    }
}
