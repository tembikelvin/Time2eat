<?php

namespace Time2Eat\Controllers\Admin;

use core\BaseController;
use models\Review;
use models\User;
use models\Restaurant;
use models\Order;

class ReviewController extends BaseController
{
    private Review $reviewModel;
    private User $userModel;
    private Restaurant $restaurantModel;
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->reviewModel = new Review();
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->orderModel = new Order();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filters = [
            'status' => $_GET['status'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'restaurant_id' => $_GET['restaurant_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? '',
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'limit' => 20
        ];

        $reviews = $this->getFilteredReviews($filters);
        $stats = $this->getReviewStats();
        $restaurants = $this->getRestaurantsList();

        $this->render('admin/reviews/index', [
            'title' => 'Review Management',
            'currentPage' => 'reviews',
            'reviews' => $reviews['data'],
            'pagination' => $reviews['pagination'],
            'stats' => $stats,
            'restaurants' => $restaurants,
            'filters' => $filters
        ]);
    }

    public function pending(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filters = [
            'status' => 'pending',
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'limit' => 20
        ];

        $reviews = $this->getFilteredReviews($filters);
        $stats = $this->getReviewStats();

        $this->render('admin/reviews/pending', [
            'title' => 'Pending Reviews',
            'currentPage' => 'reviews',
            'reviews' => $reviews['data'],
            'pagination' => $reviews['pagination'],
            'stats' => $stats
        ]);
    }

    public function approve(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $adminNotes = $input['admin_notes'] ?? '';

        try {
            $review = $this->reviewModel->findById($id);
            if (!$review) {
                $this->json(['success' => false, 'message' => 'Review not found'], 404);
                return;
            }

            $success = $this->reviewModel->update($id, [
                'status' => 'approved',
                'is_approved' => true,
                'admin_notes' => $adminNotes,
                'approved_by' => $this->getCurrentUser()['id'],
                'approved_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($success) {
                $this->updateRestaurantRating($review['reviewable_id']);
                $this->logReviewAction($id, 'approved', $adminNotes);
                
                $this->json([
                    'success' => true,
                    'message' => 'Review approved successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to approve review'
                ], 500);
            }

        } catch (\Exception $e) {
            error_log("Error approving review: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while approving the review'
            ], 500);
        }
    }

    public function reject(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $adminNotes = $input['admin_notes'] ?? '';
        $reason = $input['reason'] ?? '';

        try {
            $review = $this->reviewModel->findById($id);
            if (!$review) {
                $this->json(['success' => false, 'message' => 'Review not found'], 404);
                return;
            }

            $success = $this->reviewModel->update($id, [
                'status' => 'rejected',
                'is_approved' => false,
                'admin_notes' => $adminNotes,
                'rejection_reason' => $reason,
                'rejected_by' => $this->getCurrentUser()['id'],
                'rejected_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($success) {
                $this->logReviewAction($id, 'rejected', $adminNotes);
                
                $this->json([
                    'success' => true,
                    'message' => 'Review rejected successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to reject review'
                ], 500);
            }

        } catch (\Exception $e) {
            error_log("Error rejecting review: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the review'
            ], 500);
        }
    }

    public function hide(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $adminNotes = $input['admin_notes'] ?? '';

        try {
            $success = $this->reviewModel->update($id, [
                'status' => 'hidden',
                'admin_notes' => $adminNotes,
                'hidden_by' => $this->getCurrentUser()['id'],
                'hidden_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($success) {
                $this->logReviewAction($id, 'hidden', $adminNotes);
                
                $this->json([
                    'success' => true,
                    'message' => 'Review hidden successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to hide review'
                ], 500);
            }

        } catch (\Exception $e) {
            error_log("Error hiding review: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while hiding the review'
            ], 500);
        }
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $review = $this->reviewModel->findById($id);
            if (!$review) {
                $this->json(['success' => false, 'message' => 'Review not found'], 404);
                return;
            }

            $success = $this->reviewModel->delete($id);

            if ($success) {
                $this->logReviewAction($id, 'deleted', 'Review permanently deleted');
                $this->updateRestaurantRating($review['reviewable_id']);
                
                $this->json([
                    'success' => true,
                    'message' => 'Review deleted successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete review'
                ], 500);
            }

        } catch (\Exception $e) {
            error_log("Error deleting review: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while deleting the review'
            ], 500);
        }
    }

    public function bulkAction(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $action = $input['action'] ?? '';
        $reviewIds = $input['review_ids'] ?? [];
        $adminNotes = $input['admin_notes'] ?? '';

        if (empty($reviewIds) || !in_array($action, ['approve', 'reject', 'hide', 'delete'])) {
            $this->json(['success' => false, 'message' => 'Invalid action or review IDs'], 400);
            return;
        }

        try {
            $successCount = 0;
            $failedCount = 0;
            $restaurantIds = [];

            foreach ($reviewIds as $reviewId) {
                $review = $this->reviewModel->findById($reviewId);
                if (!$review) {
                    $failedCount++;
                    continue;
                }

                $restaurantIds[] = $review['reviewable_id'];

                $updateData = [
                    'admin_notes' => $adminNotes,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                switch ($action) {
                    case 'approve':
                        $updateData['status'] = 'approved';
                        $updateData['is_approved'] = true;
                        $updateData['approved_by'] = $this->getCurrentUser()['id'];
                        $updateData['approved_at'] = date('Y-m-d H:i:s');
                        break;
                    case 'reject':
                        $updateData['status'] = 'rejected';
                        $updateData['is_approved'] = false;
                        $updateData['rejected_by'] = $this->getCurrentUser()['id'];
                        $updateData['rejected_at'] = date('Y-m-d H:i:s');
                        break;
                    case 'hide':
                        $updateData['status'] = 'hidden';
                        $updateData['hidden_by'] = $this->getCurrentUser()['id'];
                        $updateData['hidden_at'] = date('Y-m-d H:i:s');
                        break;
                    case 'delete':
                        if ($this->reviewModel->delete($reviewId)) {
                            $successCount++;
                        } else {
                            $failedCount++;
                        }
                        continue 2;
                }

                if ($this->reviewModel->update($reviewId, $updateData)) {
                    $successCount++;
                    $this->logReviewAction($reviewId, $action, $adminNotes);
                } else {
                    $failedCount++;
                }
            }

            foreach (array_unique($restaurantIds) as $restaurantId) {
                $this->updateRestaurantRating($restaurantId);
            }

            $this->json([
                'success' => true,
                'message' => "Bulk action completed: {$successCount} successful, {$failedCount} failed",
                'success_count' => $successCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            error_log("Error in bulk review action: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred during bulk action'
            ], 500);
        }
    }

    public function details(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $review = $this->getReviewDetails($id);
        if (!$review) {
            $this->json(['success' => false, 'message' => 'Review not found'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'review' => $review
        ]);
    }

    public function stats(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $stats = $this->getReviewStats();
        $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function export(): void
    {
        $this->requireAuth();
        $this->requireRole('admin');

        $filters = [
            'status' => $_GET['status'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'restaurant_id' => $_GET['restaurant_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'limit' => 10000
        ];

        $reviews = $this->getFilteredReviews($filters);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reviews_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'ID',
            'Customer Name',
            'Customer Email',
            'Restaurant',
            'Rating',
            'Comment',
            'Status',
            'Is Verified',
            'Order Number',
            'Created At',
            'Admin Notes'
        ]);

        foreach ($reviews['data'] as $review) {
            fputcsv($output, [
                $review['id'],
                $review['customer_name'],
                $review['customer_email'],
                $review['restaurant_name'],
                $review['rating'],
                $review['comment'],
                $review['status'],
                $review['is_verified'] ? 'Yes' : 'No',
                $review['order_number'],
                $review['created_at'],
                $review['admin_notes'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    private function getFilteredReviews(array $filters): array
    {
        $sql = "
            SELECT 
                r.*,
                u.first_name,
                u.last_name,
                u.email as customer_email,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                res.name as restaurant_name,
                o.order_number,
                o.created_at as order_date
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN restaurants res ON r.reviewable_id = res.id AND r.reviewable_type = 'restaurant'
            LEFT JOIN orders o ON r.order_id = o.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = (int)$filters['rating'];
        }

        if (!empty($filters['restaurant_id'])) {
            $sql .= " AND r.reviewable_id = ? AND r.reviewable_type = 'restaurant'";
            $params[] = (int)$filters['restaurant_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND r.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND r.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (r.comment LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR res.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY r.created_at DESC";

        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_query";
        $totalResult = $this->fetchOne($countSql, $params);
        $total = (int)($totalResult['total'] ?? 0);

        $offset = ($filters['page'] - 1) * $filters['limit'];
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $filters['limit'];
        $params[] = $offset;

        $reviews = $this->fetchAll($sql, $params);

        return [
            'data' => $reviews,
            'pagination' => [
                'current_page' => $filters['page'],
                'per_page' => $filters['limit'],
                'total' => $total,
                'total_pages' => ceil($total / $filters['limit'])
            ]
        ];
    }

    private function getReviewStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reviews,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reviews,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reviews,
                COUNT(CASE WHEN status = 'hidden' THEN 1 END) as hidden_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_stars,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_stars,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_stars,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_stars,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as reviews_this_week,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as reviews_this_month
            FROM reviews
        ";

        $result = $this->fetchOne($sql);

        return [
            'total_reviews' => (int)($result['total_reviews'] ?? 0),
            'pending_reviews' => (int)($result['pending_reviews'] ?? 0),
            'approved_reviews' => (int)($result['approved_reviews'] ?? 0),
            'rejected_reviews' => (int)($result['rejected_reviews'] ?? 0),
            'hidden_reviews' => (int)($result['hidden_reviews'] ?? 0),
            'average_rating' => round((float)($result['average_rating'] ?? 0), 1),
            'rating_distribution' => [
                5 => (int)($result['five_stars'] ?? 0),
                4 => (int)($result['four_stars'] ?? 0),
                3 => (int)($result['three_stars'] ?? 0),
                2 => (int)($result['two_stars'] ?? 0),
                1 => (int)($result['one_star'] ?? 0)
            ],
            'reviews_this_week' => (int)($result['reviews_this_week'] ?? 0),
            'reviews_this_month' => (int)($result['reviews_this_month'] ?? 0)
        ];
    }

    private function getRestaurantsList(): array
    {
        $sql = "SELECT id, name FROM restaurants WHERE status = 'active' ORDER BY name";
        return $this->fetchAll($sql);
    }

    private function getReviewDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                r.*,
                u.first_name,
                u.last_name,
                u.email as customer_email,
                u.phone as customer_phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                res.name as restaurant_name,
                res.id as restaurant_id,
                o.order_number,
                o.total_amount,
                o.created_at as order_date,
                admin.first_name as admin_first_name,
                admin.last_name as admin_last_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN restaurants res ON r.reviewable_id = res.id AND r.reviewable_type = 'restaurant'
            LEFT JOIN orders o ON r.order_id = o.id
            LEFT JOIN users admin ON r.approved_by = admin.id
            WHERE r.id = ?
        ";

        return $this->fetchOne($sql, [$id]);
    }

    private function updateRestaurantRating(int $restaurantId): void
    {
        $stats = $this->reviewModel->getRestaurantStats($restaurantId);
        
        $this->restaurantModel->update($restaurantId, [
            'rating' => $stats['average_rating'],
            'total_reviews' => $stats['total_reviews'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function logReviewAction(int $reviewId, string $action, string $notes = ''): void
    {
        $sql = "
            INSERT INTO admin_activity_logs (user_id, action_type, entity_type, entity_id, description, created_at)
            VALUES (?, ?, 'review', ?, ?, NOW())
        ";

        $this->execute($sql, [
            $this->getCurrentUser()['id'],
            'review_' . $action,
            $reviewId,
            "Review {$action}" . ($notes ? ": {$notes}" : "")
        ]);
    }
}
