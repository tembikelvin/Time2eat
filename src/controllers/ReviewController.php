<?php

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\Review;
use Time2Eat\Models\Order;
use Time2Eat\Models\Restaurant;
use Time2Eat\Models\MenuItem;
use Time2Eat\Models\User;

class ReviewController extends BaseController
{
    private Review $reviewModel;
    private Order $orderModel;
    private Restaurant $restaurantModel;
    private MenuItem $menuItemModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new Review();
        $this->orderModel = new Order();
        $this->restaurantModel = new Restaurant();
        $this->menuItemModel = new MenuItem();
        $this->userModel = new User();
    }

    /**
     * Display reviews for a restaurant or menu item
     */
    public function index(): void
    {
        $type = $_GET['type'] ?? 'restaurant';
        $id = (int)($_GET['id'] ?? 0);
        
        if (!in_array($type, ['restaurant', 'menu_item', 'rider']) || !$id) {
            $this->redirect(url('/browse'));
            return;
        }

        $reviews = $this->reviewModel->getReviewsByEntity($type, $id);
        $stats = $this->reviewModel->getReviewStats($type, $id);
        
        // Get entity details
        $entity = null;
        switch ($type) {
            case 'restaurant':
                $entity = $this->restaurantModel->findById($id);
                break;
            case 'menu_item':
                $entity = $this->menuItemModel->findById($id);
                break;
        }

        $this->render('reviews/index', [
            'reviews' => $reviews,
            'stats' => $stats,
            'entity' => $entity,
            'type' => $type,
            'title' => 'Reviews - Time2Eat'
        ]);
    }

    /**
     * Show review form (for completed orders)
     */
    public function create(): void
    {
        $this->requireAuth();
        
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order = $this->orderModel->findById($orderId);
        
        if (!$order || $order['customer_id'] !== $this->getCurrentUser()['id']) {
            $this->redirect(url('/customer/orders'));
            return;
        }

        // Check if order is completed
        if ($order['status'] !== 'delivered') {
            $this->setFlashMessage('error', 'You can only review completed orders.');
            $this->redirect(url('/customer/orders'));
            return;
        }

        // Check if already reviewed
        $existingReview = $this->reviewModel->getOrderReview($orderId);
        if ($existingReview) {
            $this->redirect(url("/reviews/edit/{$existingReview['id']}"));
            return;
        }

        // Get order items for individual reviews
        $orderItems = $this->orderModel->getOrderItems($orderId);
        $restaurant = $this->restaurantModel->findById($order['restaurant_id']);

        $this->render('reviews/create', [
            'order' => $order,
            'orderItems' => $orderItems,
            'restaurant' => $restaurant,
            'title' => 'Write Review - Time2Eat'
        ]);
    }

    /**
     * Store new review
     */
    public function store(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('/customer/orders'));
            return;
        }

        $data = $this->validateReviewData($_POST);
        if (!$data) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? url('/customer/orders'));
            return;
        }

        $userId = $this->getCurrentUser()['id'];
        
        try {
            $this->getDb()->beginTransaction();

            // Create restaurant review
            if (!empty($data['restaurant_rating'])) {
                $restaurantReviewId = $this->reviewModel->create([
                    'user_id' => $userId,
                    'order_id' => $data['order_id'],
                    'reviewable_type' => 'restaurant',
                    'reviewable_id' => $data['restaurant_id'],
                    'rating' => $data['restaurant_rating'],
                    'comment' => $data['restaurant_comment'] ?? '',
                    'status' => 'pending',
                    'is_verified' => 1 // Verified purchase
                ]);
            }

            // Create menu item reviews
            if (!empty($data['item_reviews'])) {
                foreach ($data['item_reviews'] as $itemId => $itemReview) {
                    if (!empty($itemReview['rating'])) {
                        $this->reviewModel->create([
                            'user_id' => $userId,
                            'order_id' => $data['order_id'],
                            'reviewable_type' => 'menu_item',
                            'reviewable_id' => $itemId,
                            'rating' => $itemReview['rating'],
                            'comment' => $itemReview['comment'] ?? '',
                            'status' => 'pending',
                            'is_verified' => 1
                        ]);
                    }
                }
            }

            // Create rider review if applicable
            if (!empty($data['rider_rating']) && !empty($data['rider_id'])) {
                $this->reviewModel->create([
                    'user_id' => $userId,
                    'order_id' => $data['order_id'],
                    'reviewable_type' => 'rider',
                    'reviewable_id' => $data['rider_id'],
                    'rating' => $data['rider_rating'],
                    'comment' => $data['rider_comment'] ?? '',
                    'status' => 'pending',
                    'is_verified' => 1
                ]);
            }

            // Update order review status
            $this->orderModel->update($data['order_id'], ['is_reviewed' => 1]);

            $this->getDb()->commit();
            
            $this->setFlashMessage('success', 'Thank you for your review!');
            $this->redirect(url('/customer/orders'));

        } catch (\Exception $e) {
            $this->getDb()->rollback();
            $this->logError('Review creation error', [
                'user_id' => $userId,
                'order_id' => $data['order_id'],
                'error' => $e->getMessage()
            ]);
            
            $this->setFlashMessage('error', 'Failed to submit review. Please try again.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? url('/customer/orders'));
        }
    }

    /**
     * Show edit review form
     */
    public function edit(int $id): void
    {
        $this->requireAuth();
        
        $review = $this->reviewModel->findById($id);
        if (!$review || $review['user_id'] !== $this->getCurrentUser()['id']) {
            $this->redirect(url('/customer/orders'));
            return;
        }

        // Get related data
        $order = $this->orderModel->findById($review['order_id']);
        $entity = null;
        
        switch ($review['reviewable_type']) {
            case 'restaurant':
                $entity = $this->restaurantModel->findById($review['reviewable_id']);
                break;
            case 'menu_item':
                $entity = $this->menuItemModel->findById($review['reviewable_id']);
                break;
        }

        $this->render('reviews/edit', [
            'review' => $review,
            'order' => $order,
            'entity' => $entity,
            'title' => 'Edit Review - Time2Eat'
        ]);
    }

    /**
     * Update review
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('/customer/orders'));
            return;
        }

        $review = $this->reviewModel->findById($id);
        if (!$review || $review['user_id'] !== $this->getCurrentUser()['id']) {
            $this->redirect(url('/customer/orders'));
            return;
        }

        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->setFlashMessage('error', 'Please provide a valid rating (1-5 stars).');
            $this->redirect(url("/reviews/edit/{$id}"));
            return;
        }

        try {
            $this->reviewModel->update($id, [
                'rating' => $rating,
                'comment' => $comment,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->setFlashMessage('success', 'Review updated successfully!');
            $this->redirect(url('/customer/orders'));

        } catch (\Exception $e) {
            $this->logError('Review update error', [
                'review_id' => $id,
                'user_id' => $this->getCurrentUser()['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->setFlashMessage('error', 'Failed to update review. Please try again.');
            $this->redirect(url("/reviews/edit/{$id}"));
        }
    }

    /**
     * Delete review
     */
    public function delete(int $id): void
    {
        $this->requireAuth();
        
        $review = $this->reviewModel->findById($id);
        if (!$review || $review['user_id'] !== $this->getCurrentUser()['id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Review not found']);
            return;
        }

        try {
            $this->reviewModel->delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'Review deleted successfully']);

        } catch (\Exception $e) {
            $this->logError('Review deletion error', [
                'review_id' => $id,
                'user_id' => $this->getCurrentUser()['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete review']);
        }
    }

    /**
     * API endpoint to get reviews
     */
    public function apiGetReviews(): void
    {
        $type = $_GET['type'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(5, (int)($_GET['limit'] ?? 10)));
        
        if (!in_array($type, ['restaurant', 'menu_item', 'rider']) || !$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $reviews = $this->reviewModel->getReviewsByEntity($type, $id, $page, $limit);
        $stats = $this->reviewModel->getReviewStats($type, $id);
        
        $this->jsonResponse([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $stats['total_reviews']
            ]
        ]);
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(int $id): void
    {
        $this->requireAuth();
        
        $userId = $this->getCurrentUser()['id'];
        
        try {
            $this->reviewModel->toggleHelpful($id, $userId);
            $helpfulCount = $this->reviewModel->getHelpfulCount($id);
            
            $this->jsonResponse([
                'success' => true,
                'helpful_count' => $helpfulCount
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update helpful status']);
        }
    }

    /**
     * Report review
     */
    public function report(int $id): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please provide a reason']);
            return;
        }

        try {
            $this->reviewModel->reportReview($id, $this->getCurrentUser()['id'], $reason);
            $this->jsonResponse(['success' => true, 'message' => 'Review reported successfully']);

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to report review']);
        }
    }

    /**
     * Validate review data
     */
    private function validateReviewData(array $data): ?array
    {
        $orderId = (int)($data['order_id'] ?? 0);
        $order = $this->orderModel->findById($orderId);
        
        if (!$order || $order['customer_id'] !== $this->getCurrentUser()['id']) {
            $this->setFlashMessage('error', 'Invalid order.');
            return null;
        }

        if ($order['status'] !== 'delivered') {
            $this->setFlashMessage('error', 'You can only review completed orders.');
            return null;
        }

        $restaurantRating = (int)($data['restaurant_rating'] ?? 0);
        if ($restaurantRating && ($restaurantRating < 1 || $restaurantRating > 5)) {
            $this->setFlashMessage('error', 'Please provide a valid restaurant rating (1-5 stars).');
            return null;
        }

        return [
            'order_id' => $orderId,
            'restaurant_id' => $order['restaurant_id'],
            'rider_id' => $order['rider_id'],
            'restaurant_rating' => $restaurantRating,
            'restaurant_comment' => trim($data['restaurant_comment'] ?? ''),
            'rider_rating' => (int)($data['rider_rating'] ?? 0),
            'rider_comment' => trim($data['rider_comment'] ?? ''),
            'item_reviews' => $data['item_reviews'] ?? []
        ];
    }
}
