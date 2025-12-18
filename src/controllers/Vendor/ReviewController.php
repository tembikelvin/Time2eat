<?php

namespace Time2Eat\Controllers\Vendor;

use core\BaseController;
use models\Restaurant;
use models\Review;

class ReviewController extends BaseController
{
    private Restaurant $restaurantModel;
    private Review $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->setLayout('dashboard');
        $this->restaurantModel = new Restaurant();
        $this->reviewModel = new Review();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);

        if (!$restaurant) {
            $this->redirect('/vendor/setup');
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $rating = $_GET['rating'] ?? null;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $reviews = $this->reviewModel->getByRestaurant($restaurant['id'], $rating, $limit, $offset);
        $totalReviews = $this->reviewModel->countByRestaurant($restaurant['id'], $rating);
        $totalPages = ceil($totalReviews / $limit);
        
        $reviewStats = $this->reviewModel->getRestaurantStats($restaurant['id']);

        $this->render('vendor/reviews/index', [
            'title' => 'Reviews - Time2Eat',
            'user' => $user,
            'restaurant' => $restaurant,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'currentRating' => $rating,
            'currentPage' => 'reviews',
            'paginationPage' => $page,
            'totalPages' => $totalPages,
            'totalReviews' => $totalReviews
        ]);
    }

    public function reply(int $id): void
    {
        $this->requireAuth();
        $this->requireRole('vendor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/vendor/reviews');
            return;
        }

        $user = $this->getAuthenticatedUser();
        $restaurant = $this->restaurantModel->getByVendorId($user['id']);
        $review = $this->reviewModel->find($id);

        if (!$restaurant || !$review) {
            $this->session->setFlash('error', 'Review not found');
            $this->redirect('/vendor/reviews');
            return;
        }

        // Verify the review belongs to this vendor's restaurant
        $order = $this->reviewModel->getOrderDetails($review['order_id']);
        if ($order['restaurant_id'] !== $restaurant['id']) {
            $this->session->setFlash('error', 'Unauthorized access');
            $this->redirect('/vendor/reviews');
            return;
        }

        try {
            $reply = $this->request->post('reply');
            
            $this->reviewModel->addReply($id, $reply, $user['id']);
            $this->session->setFlash('success', 'Reply added successfully');

        } catch (Exception $e) {
            $this->session->setFlash('error', 'Failed to add reply: ' . $e->getMessage());
        }

        $this->redirect('/vendor/reviews');
    }
}