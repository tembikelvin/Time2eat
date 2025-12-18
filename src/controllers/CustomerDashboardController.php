<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Message.php';

use core\BaseController;
use models\Message;

class CustomerDashboardController extends BaseController
{
    private $messageModel;

    public function __construct()
    {
        parent::__construct();
        // Use dashboard layout instead of public app layout
        $this->layout = 'dashboard';
        $this->messageModel = new Message();
    }

    public function index(): void
    {
        // CRITICAL: Prevent caching of dashboard (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Get customer statistics
        $stats = $this->getCustomerStats($customerId);

        // Get recent orders (last 5)
        $recentOrders = $this->getRecentOrders($customerId, 5);

        // Get favorite restaurants (based on order frequency)
        $favoriteRestaurants = $this->getFavoriteRestaurants($customerId, 3);

        // Get live orders (orders in progress)
        $liveOrders = $this->getLiveOrders($customerId);

        $this->render('dashboard/customer', [
            'title' => 'Customer Dashboard - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'liveOrders' => $liveOrders,
            'favoriteRestaurants' => $favoriteRestaurants
        ]);
    }

    public function orders(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Pagination
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get orders with pagination
        $orders = $this->getCustomerOrders($customerId, $limit, $offset);

        // Get total count for pagination
        $totalOrders = $this->getCustomerOrderCount($customerId);
        $totalPages = ceil($totalOrders / $limit);

        $this->render('customer/orders', [
            'title' => 'My Orders - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'orders',
            'orders' => $orders,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrders
        ]);
    }

    public function favorites(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        try {
            // Get favorite restaurants from wishlist
            $favoriteRestaurants = $this->getFavoriteRestaurantsFromWishlist($customerId);

            // Get favorite menu items from wishlist
            $favoriteMenuItems = $this->getFavoriteMenuItemsFromWishlist($customerId);

            $this->render('customer/favorites', [
                'title' => 'My Favorites - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'favorites',
                'favoriteRestaurants' => $favoriteRestaurants,
                'favoriteMenuItems' => $favoriteMenuItems
            ]);
        } catch (\Exception $e) {
            error_log("Favorites page error: " . $e->getMessage());
            $this->render('customer/favorites', [
                'title' => 'My Favorites - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'favorites',
                'favoriteRestaurants' => [],
                'favoriteMenuItems' => [],
                'error' => 'Unable to load favorites. Please try again later.'
            ]);
        }
    }

    public function profile(): void
    {
        // CRITICAL: Prevent caching of profile page (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Get user profile data
        $profile = $this->getUserProfile($customerId);

        // Check for success message
        $success = $_SESSION['success'] ?? '';
        if ($success) {
            unset($_SESSION['success']);
        }

        $this->render('customer/profile', [
            'title' => 'My Profile - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'profile',
            'profile' => $profile,
            'success' => $success
        ]);
    }

    public function addresses(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        try {
            // Get user addresses
            $addresses = $this->getUserAddresses($customerId);

            // Check for success message
            $success = $_SESSION['success'] ?? '';
            if ($success) {
                unset($_SESSION['success']);
            }

            $this->render('customer/addresses', [
                'title' => 'My Addresses - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'addresses',
                'addresses' => $addresses,
                'success' => $success
            ]);
        } catch (\Exception $e) {
            error_log("Addresses page error: " . $e->getMessage());
            $this->render('customer/addresses', [
                'title' => 'My Addresses - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'addresses',
                'addresses' => [],
                'error' => 'Unable to load addresses. Please try again later.'
            ]);
        }
    }

    public function payments(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Get user payment methods
        $paymentMethods = $this->getUserPaymentMethods($customerId);

        // Check for success message
        $success = $_SESSION['success'] ?? '';
        if ($success) {
            unset($_SESSION['success']);
        }

        $this->render('customer/payment-methods', [
            'title' => 'Payment Methods - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'payments',
            'paymentMethods' => $paymentMethods,
            'success' => $success
        ]);
    }

    public function affiliates(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        try {
            // Get fresh user data to ensure we have the latest affiliate_code
            $freshUser = $this->getFreshUserData($customerId);
            if ($freshUser) {
                $user = $freshUser;
            }

            // Get affiliate data from affiliates table
            $affiliate = $this->getUserAffiliate($customerId);
            $referrals = [];

            // If no affiliate record but user has affiliate_code, create missing record
            if (!$affiliate && !empty($user->affiliate_code)) {
                error_log("User {$customerId} has affiliate_code but no affiliates record. Creating missing record.");
                $affiliate = $this->createMissingAffiliateRecord($customerId, $user->affiliate_code);
            }

            if ($affiliate) {
                $referrals = $this->getAffiliateReferrals($customerId);
            }

            // Get withdrawal history
            $withdrawals = [];
            if ($affiliate) {
                $withdrawals = $this->getAffiliateWithdrawals($affiliate['id']);
            }

            $this->render('customer/affiliates', [
                'title' => 'Affiliate Program - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'affiliates',
                'affiliate' => $affiliate,
                'referrals' => $referrals,
                'withdrawals' => $withdrawals
            ]);
        } catch (\Exception $e) {
            error_log("Affiliate page error: " . $e->getMessage());
            $this->render('customer/affiliates', [
                'title' => 'Affiliate Program - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'affiliates',
                'affiliate' => null,
                'referrals' => [],
                'error' => 'Unable to load affiliate data. Please try again later.'
            ]);
        }
    }

    /**
     * Get customer statistics
     */
    private function getCustomerStats(int $customerId): array
    {
        $sql = "
            SELECT
                COUNT(*) as totalOrders,
                COALESCE(SUM(total_amount), 0) as totalSpent,
                COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as monthlyOrders,
                COUNT(DISTINCT restaurant_id) as favoriteRestaurants
            FROM orders
            WHERE customer_id = ? AND status != 'cancelled'
        ";

        $result = $this->fetchOne($sql, [$customerId]);

        return [
            'totalOrders' => (int)($result['totalOrders'] ?? 0),
            'totalSpent' => (float)($result['totalSpent'] ?? 0),
            'monthlyOrders' => (int)($result['monthlyOrders'] ?? 0),
            'favoriteRestaurants' => (int)($result['favoriteRestaurants'] ?? 0)
        ];
    }

    /**
     * Get favorite restaurants based on order frequency
     */
    private function getFavoriteRestaurants(int $customerId, int $limit = 3): array
    {
        $sql = "
            SELECT
                r.id, r.name, r.logo as image, r.rating, r.delivery_fee,
                COUNT(o.id) as order_count,
                AVG(o.total_amount) as avg_order_value
            FROM restaurants r
            INNER JOIN orders o ON r.id = o.restaurant_id
            WHERE o.customer_id = ? AND o.status = 'delivered'
            GROUP BY r.id
            ORDER BY order_count DESC, r.rating DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit]);
    }

    /**
     * Get live orders (orders in progress)
     */
    private function getLiveOrders(int $customerId): array
    {
        try {
            $sql = "
                SELECT 
                    o.id, o.order_number, o.status, o.total_amount, o.created_at,
                    o.estimated_delivery_time, o.delivery_address,
                    r.name as restaurant_name, r.phone as restaurant_phone,
                    u.first_name as rider_first_name, u.last_name as rider_last_name,
                    u.phone as rider_phone
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users u ON o.rider_id = u.id
                WHERE o.customer_id = ? 
                AND o.status IN ('pending', 'confirmed', 'preparing', 'ready', 'on_the_way')
                ORDER BY o.created_at DESC
                LIMIT 5
            ";

            return $this->fetchAll($sql, [$customerId]);
        } catch (\Exception $e) {
            error_log("Error fetching live orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer order count for pagination
     */
    private function getCustomerOrderCount(int $customerId): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE customer_id = ?";
        $result = $this->fetchOne($sql, [$customerId]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get favorite menu items based on order frequency
     */
    private function getFavoriteMenuItems(int $customerId, int $limit = 10): array
    {
        $sql = "
            SELECT
                mi.id, mi.name, mi.description, mi.price, mi.image,
                r.name as restaurant_name, r.id as restaurant_id,
                COUNT(oi.id) as order_count,
                AVG(oi.quantity) as avg_quantity
            FROM menu_items mi
            INNER JOIN order_items oi ON mi.id = oi.menu_item_id
            INNER JOIN orders o ON oi.order_id = o.id
            INNER JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE o.customer_id = ? AND o.status = 'delivered'
            GROUP BY mi.id
            ORDER BY order_count DESC, avg_quantity DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit]);
    }

    /**
     * Get recent orders for customer
     */
    private function getRecentOrders(int $customerId, int $limit = 5): array
    {
        $sql = "
            SELECT
                o.*,
                r.name as restaurant_name,
                r.logo as restaurant_image,
                r.phone as restaurant_phone,
                COUNT(oi.id) as item_count
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.customer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit]);
    }

    /**
     * Get customer orders with pagination
     */
    private function getCustomerOrders(int $customerId, int $limit = 10, int $offset = 0): array
    {
        $sql = "
            SELECT
                o.*,
                r.name as restaurant_name,
                r.logo as restaurant_image,
                r.phone as restaurant_phone,
                COUNT(oi.id) as item_count
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.customer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ";

        return $this->fetchAll($sql, [$customerId, $limit, $offset]);
    }

    /**
     * Get order items API endpoint
     */
    public function getOrderItems(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $orderId = (int)($_GET['order_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$orderId) {
            $this->jsonError('Order ID is required');
            return;
        }

        // Verify order belongs to customer
        $sql = "SELECT id FROM orders WHERE id = ? AND customer_id = ?";
        $order = $this->fetchOne($sql, [$orderId, $customerId]);

        if (!$order) {
            $this->jsonError('Order not found');
            return;
        }

        // Get order items with image fallback
        $sql = "
            SELECT
                oi.*,
                mi.name,
                COALESCE(mi.image_url, mi.image, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop&q=80') as image
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ";

        $items = $this->fetchAll($sql, [$orderId]);

        $this->jsonSuccess('Order items retrieved', ['items' => $items]);
    }

    /**
     * Cancel order API endpoint
     */
    public function cancelOrder(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $orderId = (int)($_GET['order_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$orderId) {
            $this->jsonError('Order ID is required');
            return;
        }

        // Verify order belongs to customer and can be cancelled
        $sql = "SELECT id, status FROM orders WHERE id = ? AND customer_id = ?";
        $order = $this->fetchOne($sql, [$orderId, $customerId]);

        if (!$order) {
            $this->jsonError('Order not found');
            return;
        }

        if (!in_array(strtolower($order['status']), ['pending', 'confirmed'])) {
            $this->jsonError('Order cannot be cancelled at this stage');
            return;
        }

        // Update order status using Order model
        require_once __DIR__ . '/../models/Order.php';
        $orderModel = new \models\Order();
        $cancelled = $orderModel->cancelOrder($orderId, $customerId, 'Cancelled by customer');

        if ($cancelled) {
            $this->jsonSuccess('Order cancelled successfully');
        } else {
            $this->jsonError('Failed to cancel order');
        }
    }

    /**
     * Rate order API endpoint
     */
    public function rateOrder(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $orderId = (int)($_GET['order_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$orderId) {
            $this->jsonError('Order ID is required');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $rating = (int)($input['rating'] ?? 0);
        $review = trim($input['review'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $this->jsonError('Rating must be between 1 and 5');
            return;
        }

        // Verify order belongs to customer and is delivered
        $sql = "SELECT id, status, restaurant_id FROM orders WHERE id = ? AND customer_id = ?";
        $order = $this->fetchOne($sql, [$orderId, $customerId]);

        if (!$order) {
            $this->jsonError('Order not found');
            return;
        }

        if (strtolower($order['status']) !== 'delivered') {
            $this->jsonError('Only delivered orders can be rated');
            return;
        }

        // Update order rating
        $sql = "UPDATE orders SET rating = ?, review = ?, updated_at = NOW() WHERE id = ?";
        $this->query($sql, [$rating, $review, $orderId]);

        $this->jsonSuccess('Rating submitted successfully');
    }

    /**
     * Get favorite restaurants from wishlist (based on menu items)
     */
    private function getFavoriteRestaurantsFromWishlist(int $customerId): array
    {
        $sql = "
            SELECT 
                r.id, r.name, r.logo, r.rating, r.delivery_fee, r.delivery_time, r.cuisine_type
            FROM wishlists w
            JOIN menu_items mi ON w.menu_item_id = mi.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE w.user_id = ?
            GROUP BY r.id, r.name, r.logo, r.rating, r.delivery_fee, r.delivery_time, r.cuisine_type
            ORDER BY MAX(w.created_at) DESC
        ";

        return $this->fetchAll($sql, [$customerId]);
    }

    /**
     * Get favorite menu items from wishlist
     */
    private function getFavoriteMenuItemsFromWishlist(int $customerId): array
    {
        $sql = "
            SELECT
                mi.id, mi.name, mi.description, mi.price, mi.image, mi.restaurant_id,
                r.name as restaurant_name
            FROM wishlists w
            JOIN menu_items mi ON w.menu_item_id = mi.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ";

        return $this->fetchAll($sql, [$customerId]);
    }

    /**
     * Remove favorite restaurant API endpoint
     */
    public function removeFavoriteRestaurant(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $restaurantId = (int)($_GET['id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$restaurantId) {
            $this->jsonError('Restaurant ID is required');
            return;
        }

        // Remove all menu items from this restaurant from wishlist
        $sql = "DELETE w FROM wishlists w 
                JOIN menu_items mi ON w.menu_item_id = mi.id 
                WHERE w.user_id = ? AND mi.restaurant_id = ?";
        $this->query($sql, [$customerId, $restaurantId]);

        $this->jsonSuccess('Restaurant removed from favorites');
    }

    /**
     * Remove favorite menu item API endpoint
     */
    public function removeFavoriteMenuItem(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $itemId = (int)($_GET['id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$itemId) {
            $this->jsonError('Menu item ID is required');
            return;
        }

        // Remove from wishlist
        $sql = "DELETE FROM wishlists WHERE user_id = ? AND menu_item_id = ?";
        $this->query($sql, [$customerId, $itemId]);

        $this->jsonSuccess('Menu item removed from favorites');
    }

    /**
     * Get user profile data
     */
    private function getUserProfile(int $userId): array
    {
        $sql = "
            SELECT
                up.*,
                u.first_name,
                u.last_name,
                u.email,
                u.avatar
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.id = ?
        ";

        $profile = $this->fetchOne($sql, [$userId]);
        return $profile ?: [];
    }

    /**
     * Update profile
     */
    public function updateProfile(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('/customer/profile'));
            return;
        }

        $user = $this->getCurrentUser();
        $customerId = $user->id;
        $errors = [];

        // Validate input
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $dateOfBirth = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $postalCode = trim($_POST['postal_code'] ?? '');

        // Validation
        if (empty($firstName)) $errors[] = 'First name is required';
        if (empty($lastName)) $errors[] = 'Last name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';

        // Check if email is already taken by another user
        if ($email !== $user->email) {
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $existingUser = $this->fetchOne($sql, [$email, $customerId]);
            if ($existingUser) {
                $errors[] = 'Email is already taken';
            }
        }

        if (empty($errors)) {
            try {
                // Update users table
                $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, updated_at = NOW() WHERE id = ?";
                $this->query($sql, [$firstName, $lastName, $email, $customerId]);

                // Handle dietary restrictions
                $dietaryRestrictions = $_POST['dietary_restrictions'] ?? [];
                $dietaryJson = json_encode($dietaryRestrictions);

                // Handle notification preferences
                $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
                $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
                $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;

                // Update or insert user profile
                $sql = "
                    INSERT INTO user_profiles (user_id, date_of_birth, gender, address, city, state, postal_code, dietary_restrictions, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                    date_of_birth = VALUES(date_of_birth),
                    gender = VALUES(gender),
                    address = VALUES(address),
                    city = VALUES(city),
                    state = VALUES(state),
                    postal_code = VALUES(postal_code),
                    dietary_restrictions = VALUES(dietary_restrictions),
                    updated_at = NOW()
                ";

                $this->query($sql, [
                    $customerId,
                    $dateOfBirth ?: null,
                    $gender ?: null,
                    $address,
                    $city,
                    $state,
                    $postalCode,
                    $dietaryJson
                ]);

                $_SESSION['success'] = 'Profile updated successfully';
                $this->redirect(url('/customer/profile'));
                return;

            } catch (\Exception $e) {
                $errors[] = 'Failed to update profile: ' . $e->getMessage();
            }
        }

        // If there are errors, show them
        $profile = $this->getUserProfile($customerId);
        $this->render('customer/profile', [
            'title' => 'My Profile - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'profile',
            'profile' => $profile,
            'errors' => $errors
        ]);
    }

    /**
     * Change password API endpoint
     */
    public function changePassword(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $input = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->jsonError('All password fields are required');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->jsonError('New passwords do not match');
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->jsonError('New password must be at least 6 characters long');
            return;
        }

        $user = $this->getCurrentUser();

        // Verify current password
        if (!password_verify($currentPassword, $user->password)) {
            $this->jsonError('Current password is incorrect');
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $this->query($sql, [$hashedPassword, $user->id]);

        $this->jsonSuccess('Password updated successfully');
    }

    /**
     * Get user addresses
     */
    private function getUserAddresses(int $userId): array
    {
        // Since there's no delivery_addresses table in the actual schema,
        // we'll extract unique addresses from completed orders and user profile
        $addresses = [];
        
        // Get address from user profile
        $sql = "
            SELECT up.address, up.city, up.state, up.postal_code, up.country,
                   up.latitude, up.longitude
            FROM user_profiles up
            WHERE up.user_id = ? AND up.address IS NOT NULL AND up.address != ''
        ";
        
        $profileAddress = $this->fetchOne($sql, [$userId]);
        
        if ($profileAddress && $profileAddress['address']) {
            $addresses[] = [
                'id' => 'profile',
                'type' => 'profile',
                'label' => 'Profile Address',
                'address_line_1' => $profileAddress['address'],
                'city' => $profileAddress['city'] ?? '',
                'state' => $profileAddress['state'] ?? '',
                'postal_code' => $profileAddress['postal_code'] ?? '',
                'country' => $profileAddress['country'] ?? 'Cameroon',
                'latitude' => $profileAddress['latitude'],
                'longitude' => $profileAddress['longitude'],
                'is_default' => true,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Get unique addresses from recent orders
        $sql = "
            SELECT DISTINCT delivery_address, created_at
            FROM orders
            WHERE customer_id = ? AND delivery_address IS NOT NULL
            ORDER BY created_at DESC
            LIMIT 5
        ";
        
        $orderAddresses = $this->fetchAll($sql, [$userId]);
        
        foreach ($orderAddresses as $index => $orderAddr) {
            if ($orderAddr['delivery_address']) {
                $addrData = json_decode($orderAddr['delivery_address'], true);
                if ($addrData && is_array($addrData)) {
                    $addresses[] = [
                        'id' => 'order_' . $index,
                        'type' => 'order',
                        'label' => 'Previous Order Address',
                        'address_line_1' => $addrData['address'] ?? $addrData['address_line_1'] ?? '',
                        'city' => $addrData['city'] ?? '',
                        'state' => $addrData['state'] ?? '',
                        'postal_code' => $addrData['postal_code'] ?? '',
                        'country' => $addrData['country'] ?? 'Cameroon',
                        'latitude' => $addrData['latitude'] ?? null,
                        'longitude' => $addrData['longitude'] ?? null,
                        'is_default' => false,
                        'created_at' => $orderAddr['created_at']
                    ];
                }
            }
        }
        
        return $addresses;
    }

    /**
     * Get single address API endpoint
     */
    public function getAddress(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $addressId = (int)($_GET['address_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$addressId) {
            $this->jsonError('Address ID is required');
            return;
        }

        // Get address and verify ownership
        $sql = "SELECT * FROM delivery_addresses WHERE id = ? AND user_id = ?";
        $address = $this->fetchOne($sql, [$addressId, $customerId]);

        if (!$address) {
            $this->jsonError('Address not found');
            return;
        }

        $this->jsonSuccess('Address retrieved', ['address' => $address]);
    }

    /**
     * Create address API endpoint
     */
    public function createAddress(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $input = json_decode(file_get_contents('php://input'), true);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Validate input
        $label = trim($input['label'] ?? '');
        $address = trim($input['address'] ?? '');
        $city = trim($input['city'] ?? '');
        $state = trim($input['state'] ?? '');
        $postalCode = trim($input['postal_code'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $deliveryInstructions = trim($input['delivery_instructions'] ?? '');
        $isDefault = (bool)($input['is_default'] ?? false);

        if (empty($label) || empty($address) || empty($city) || empty($state)) {
            $this->jsonError('Label, address, city, and state are required');
            return;
        }

        try {
            // Check if user profile exists
            $sql = "SELECT id FROM user_profiles WHERE user_id = ?";
            $profile = $this->fetchOne($sql, [$customerId]);

            if ($profile) {
                // Update existing profile
                $sql = "
                    UPDATE user_profiles 
                    SET address = ?, city = ?, state = ?, postal_code = ?, updated_at = NOW()
                    WHERE user_id = ?
                ";
                
                $this->query($sql, [$address, $city, $state, $postalCode, $customerId]);
            } else {
                // Create new profile
                $sql = "
                    INSERT INTO user_profiles (user_id, address, city, state, postal_code, country, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, 'Cameroon', NOW(), NOW())
                ";
                
                $this->query($sql, [$customerId, $address, $city, $state, $postalCode]);
            }

            $this->jsonSuccess('Address saved successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to create address: ' . $e->getMessage());
        }
    }

    /**
     * Update address API endpoint
     */
    public function updateAddress(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $addressId = (int)($_GET['address_id'] ?? 0);
        $input = json_decode(file_get_contents('php://input'), true);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$addressId) {
            $this->jsonError('Address ID is required');
            return;
        }

        // Verify address ownership
        $sql = "SELECT id FROM delivery_addresses WHERE id = ? AND user_id = ?";
        $address = $this->fetchOne($sql, [$addressId, $customerId]);

        if (!$address) {
            $this->jsonError('Address not found');
            return;
        }

        // Validate input
        $label = trim($input['label'] ?? '');
        $addressText = trim($input['address'] ?? '');
        $city = trim($input['city'] ?? '');
        $state = trim($input['state'] ?? '');
        $postalCode = trim($input['postal_code'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $deliveryInstructions = trim($input['delivery_instructions'] ?? '');
        $isDefault = (bool)($input['is_default'] ?? false);

        if (empty($label) || empty($addressText) || empty($city) || empty($state)) {
            $this->jsonError('Label, address, city, and state are required');
            return;
        }

        try {
            // If setting as default, unset other defaults
            if ($isDefault) {
                $sql = "UPDATE delivery_addresses SET is_default = 0 WHERE user_id = ? AND id != ?";
                $this->query($sql, [$customerId, $addressId]);
            }

            // Update address
            $sql = "
                UPDATE delivery_addresses
                SET label = ?, address_line_1 = ?, city = ?, state = ?, postal_code = ?, phone = ?, delivery_instructions = ?, is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ";

            $this->query($sql, [
                $label,
                $addressText,
                $city,
                $state,
                $postalCode,
                $phone,
                $deliveryInstructions,
                $isDefault ? 1 : 0,
                $addressId,
                $customerId
            ]);

            $this->jsonSuccess('Address updated successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to update address: ' . $e->getMessage());
        }
    }

    /**
     * Delete address API endpoint
     */
    public function deleteAddress(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $addressId = (int)($_GET['address_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$addressId) {
            $this->jsonError('Address ID is required');
            return;
        }

        // Verify address ownership
        $sql = "SELECT id, is_default FROM delivery_addresses WHERE id = ? AND user_id = ?";
        $address = $this->fetchOne($sql, [$addressId, $customerId]);

        if (!$address) {
            $this->jsonError('Address not found');
            return;
        }

        // Don't allow deleting the default address if it's the only one
        if ($address['is_default']) {
            $sql = "SELECT COUNT(*) as count FROM delivery_addresses WHERE user_id = ? AND is_active = 1";
            $result = $this->fetchOne($sql, [$customerId]);

            if ($result['count'] <= 1) {
                $this->jsonError('Cannot delete your only address');
                return;
            }
        }

        try {
            // Delete address (soft delete by setting is_active = 0)
            $sql = "UPDATE delivery_addresses SET is_active = 0 WHERE id = ? AND user_id = ?";
            $this->query($sql, [$addressId, $customerId]);

            // If deleted address was default, set another as default
            if ($address['is_default']) {
                $sql = "UPDATE delivery_addresses SET is_default = 1 WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 1";
                $this->query($sql, [$customerId]);
            }

            $this->jsonSuccess('Address deleted successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to delete address: ' . $e->getMessage());
        }
    }

    /**
     * Set default address API endpoint
     */
    public function setDefaultAddress(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $addressId = (int)($_GET['address_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$addressId) {
            $this->jsonError('Address ID is required');
            return;
        }

        // Verify address ownership
        $sql = "SELECT id FROM delivery_addresses WHERE id = ? AND user_id = ?";
        $address = $this->fetchOne($sql, [$addressId, $customerId]);

        if (!$address) {
            $this->jsonError('Address not found');
            return;
        }

        try {
            // Unset all defaults for this user
            $sql = "UPDATE delivery_addresses SET is_default = 0 WHERE user_id = ?";
            $this->query($sql, [$customerId]);

            // Set this address as default
            $sql = "UPDATE delivery_addresses SET is_default = 1 WHERE id = ? AND user_id = ?";
            $this->query($sql, [$addressId, $customerId]);

            $this->jsonSuccess('Default address updated successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to set default address: ' . $e->getMessage());
        }
    }

    /**
     * Get user payment methods
     */
    private function getUserPaymentMethods(int $userId): array
    {
        $sql = "
            SELECT * FROM payment_methods
            WHERE user_id = ?
            ORDER BY is_default DESC, created_at DESC
        ";

        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get single payment method API endpoint
     */
    public function getPaymentMethod(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$paymentId) {
            $this->jsonError('Payment method ID is required');
            return;
        }

        // Get payment method and verify ownership
        $sql = "SELECT * FROM payment_methods WHERE id = ? AND user_id = ?";
        $paymentMethod = $this->fetchOne($sql, [$paymentId, $customerId]);

        if (!$paymentMethod) {
            $this->jsonError('Payment method not found');
            return;
        }

        // Mask sensitive data
        if ($paymentMethod['type'] === 'card' && !empty($paymentMethod['card_number'])) {
            $paymentMethod['card_number'] = '•••• •••• •••• ' . substr($paymentMethod['card_number'], -4);
        }

        $this->jsonSuccess('Payment method retrieved', ['payment_method' => $paymentMethod]);
    }

    /**
     * Create payment method API endpoint
     */
    public function createPaymentMethod(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $input = json_decode(file_get_contents('php://input'), true);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Validate input
        $type = trim($input['type'] ?? '');
        $provider = trim($input['provider'] ?? '');
        $name = trim($input['name'] ?? '');
        $phoneNumber = trim($input['phone_number'] ?? '');
        $isDefault = (bool)($input['is_default'] ?? false);

        // Validate required fields
        if (empty($type)) {
            $this->jsonError('Payment type is required');
            return;
        }

        // Only allow mobile_money type (customers can only save mobile money numbers)
        if ($type !== 'mobile_money') {
            $this->jsonError('Only mobile money numbers can be saved');
            return;
        }

        // Validate provider
        $allowedProviders = ['mtn_momo', 'orange_money'];
        if (!in_array($provider, $allowedProviders)) {
            $this->jsonError('Invalid mobile money provider');
            return;
        }

        // Validate phone number
        if (empty($phoneNumber)) {
            $this->jsonError('Phone number is required');
            return;
        }

        // If name is empty, use phone number as name
        if (empty($name)) {
            $name = $phoneNumber;
        }

        try {
            // If setting as default, unset other defaults
            if ($isDefault) {
                $sql = "UPDATE payment_methods SET is_default = 0 WHERE user_id = ?";
                $this->query($sql, [$customerId]);
            }

            // Prepare mobile money data
            $details = [
                'phone_number' => $phoneNumber
            ];

            // Insert new payment method
            $sql = "
                INSERT INTO payment_methods (user_id, type, provider, name, details, is_default, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";

            $this->query($sql, [
                $customerId,
                $type,
                $provider,
                $name,
                json_encode($details),
                $isDefault ? 1 : 0
            ]);

            $this->jsonSuccess('Mobile money number saved successfully');

        } catch (\Exception $e) {
            error_log("Error creating payment method: " . $e->getMessage());
            $this->jsonError('Failed to save mobile money number: ' . $e->getMessage());
        }
    }

    /**
     * Update payment method API endpoint
     */
    public function updatePaymentMethod(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $input = json_decode(file_get_contents('php://input'), true);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$paymentId) {
            $this->jsonError('Payment method ID is required');
            return;
        }

        // Verify payment method ownership
        $sql = "SELECT id FROM payment_methods WHERE id = ? AND user_id = ?";
        $paymentMethod = $this->fetchOne($sql, [$paymentId, $customerId]);

        if (!$paymentMethod) {
            $this->jsonError('Payment method not found');
            return;
        }

        // Validate input
        $name = trim($input['name'] ?? '');
        $isDefault = (bool)($input['is_default'] ?? false);

        if (empty($name)) {
            $this->jsonError('Payment method name is required');
            return;
        }

        try {
            // If setting as default, unset other defaults
            if ($isDefault) {
                $sql = "UPDATE payment_methods SET is_default = 0 WHERE user_id = ? AND id != ?";
                $this->query($sql, [$customerId, $paymentId]);
            }

            // Update payment method
            $sql = "
                UPDATE payment_methods
                SET name = ?, is_default = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ";

            $this->query($sql, [
                $name,
                $isDefault ? 1 : 0,
                $paymentId,
                $customerId
            ]);

            $this->jsonSuccess('Payment method updated successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to update payment method: ' . $e->getMessage());
        }
    }

    /**
     * Delete payment method API endpoint
     */
    public function deletePaymentMethod(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$paymentId) {
            $this->jsonError('Payment method ID is required');
            return;
        }

        // Verify payment method ownership
        $sql = "SELECT id, is_default FROM payment_methods WHERE id = ? AND user_id = ?";
        $paymentMethod = $this->fetchOne($sql, [$paymentId, $customerId]);

        if (!$paymentMethod) {
            $this->jsonError('Payment method not found');
            return;
        }

        try {
            // Delete payment method
            $sql = "DELETE FROM payment_methods WHERE id = ? AND user_id = ?";
            $this->query($sql, [$paymentId, $customerId]);

            // If deleted payment method was default, set another as default
            if ($paymentMethod['is_default']) {
                $sql = "UPDATE payment_methods SET is_default = 1 WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
                $this->query($sql, [$customerId]);
            }

            $this->jsonSuccess('Payment method deleted successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to delete payment method: ' . $e->getMessage());
        }
    }

    /**
     * Set default payment method API endpoint
     */
    public function setDefaultPaymentMethod(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $user = $this->getCurrentUser();
        $customerId = $user->id;

        if (!$paymentId) {
            $this->jsonError('Payment method ID is required');
            return;
        }

        // Verify payment method ownership
        $sql = "SELECT id FROM payment_methods WHERE id = ? AND user_id = ?";
        $paymentMethod = $this->fetchOne($sql, [$paymentId, $customerId]);

        if (!$paymentMethod) {
            $this->jsonError('Payment method not found');
            return;
        }

        try {
            // Unset all defaults for this user
            $sql = "UPDATE payment_methods SET is_default = 0 WHERE user_id = ?";
            $this->query($sql, [$customerId]);

            // Set this payment method as default
            $sql = "UPDATE payment_methods SET is_default = 1 WHERE id = ? AND user_id = ?";
            $this->query($sql, [$paymentId, $customerId]);

            $this->jsonSuccess('Default payment method updated successfully');

        } catch (\Exception $e) {
            $this->jsonError('Failed to set default payment method: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to mask card number
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $cardNumber);
        if (strlen($cleaned) >= 4) {
            return '•••• •••• •••• ' . substr($cleaned, -4);
        }
        return $cleaned;
    }

    /**
     * Helper method to parse expiry date
     */
    private function parseExpiryDate(string $expiry): ?string
    {
        if (preg_match('/^(\d{2})\/(\d{2})$/', $expiry, $matches)) {
            $month = $matches[1];
            $year = '20' . $matches[2];
            return $year . '-' . $month . '-01';
        }
        return null;
    }

    /**
     * Get user affiliate data
     */
    private function getUserAffiliate(int $userId): ?array
    {
        $sql = "SELECT * FROM affiliates WHERE user_id = ?";
        return $this->fetchOne($sql, [$userId]);
    }

    /**
     * Create missing affiliate record for users who have affiliate_code but no affiliates record
     */
    private function createMissingAffiliateRecord(int $userId, string $affiliateCode): ?array
    {
        try {
            // Insert missing affiliate record
            $sql = "
                INSERT INTO affiliates (user_id, affiliate_code, commission_rate, total_earnings, pending_earnings, paid_earnings, status, created_at, updated_at)
                VALUES (?, ?, 0.0500, 0.00, 0.00, 0.00, 'active', NOW(), NOW())
            ";

            $result = $this->query($sql, [$userId, $affiliateCode]);
            
            if ($result) {
                error_log("Created missing affiliate record for user {$userId} with code {$affiliateCode}");
                // Return the newly created affiliate record
                return $this->getUserAffiliate($userId);
            } else {
                error_log("Failed to create missing affiliate record for user {$userId}");
                return null;
            }
        } catch (\Exception $e) {
            error_log("Error creating missing affiliate record: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get affiliate referrals
     */
    private function getAffiliateReferrals(int $userId): array
    {
        // Get affiliate ID first
        $affiliate = $this->getUserAffiliate($userId);
        if (!$affiliate) {
            return [];
        }

        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.created_at,
                   COALESCE(SUM(o.total_amount), 0) as total_spent,
                   COUNT(o.id) as order_count,
                   COALESCE(SUM(ar.commission_amount), 0) as commission_earned
            FROM affiliate_referrals ar
            JOIN users u ON ar.referred_user_id = u.id
            LEFT JOIN orders o ON u.id = o.customer_id AND o.status = 'delivered'
            WHERE ar.affiliate_id = ?
            GROUP BY u.id, u.first_name, u.last_name, u.created_at
            ORDER BY u.created_at DESC
        ";

        return $this->fetchAll($sql, [$affiliate['id']]);
    }

    /**
     * Get affiliate withdrawal history
     */
    private function getAffiliateWithdrawals(int $affiliateId): array
    {
        $sql = "
            SELECT id, amount, method, reference, status,
                   created_at, processed_at, notes
            FROM affiliate_payouts
            WHERE affiliate_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ";

        return $this->fetchAll($sql, [$affiliateId]);
    }

    /**
     * Join affiliate program API endpoint
     */
    public function joinAffiliateProgram(): void
    {
        // Set JSON header immediately
        header('Content-Type: application/json');
        
        try {
            $this->requireAuth();
            $this->requireRole('customer');

            $user = $this->getCurrentUser();
            $customerId = $user->id;
            
            error_log("Join affiliate: User authenticated, ID: $customerId");

            // Check if already an affiliate
            $existing = $this->getUserAffiliate($customerId);
            if ($existing) {
                $this->jsonError('You are already part of the affiliate program');
                return;
            }
            
            // Generate unique referral code
            $referralCode = $this->generateReferralCode();
            
            // Log for debugging
            error_log("Attempting to create affiliate for user ID: $customerId with code: $referralCode");

            // Insert affiliate record
            $sql = "
                INSERT INTO affiliates (user_id, affiliate_code, commission_rate, total_earnings, pending_earnings, paid_earnings, status, created_at, updated_at)
                VALUES (?, ?, 0.0500, 0.00, 0.00, 0.00, 'active', NOW(), NOW())
            ";

            $result = $this->query($sql, [$customerId, $referralCode]);
            error_log("Affiliate insert result: " . ($result ? 'success' : 'failed'));

            if ($result) {
                // Update user table with affiliate_code
                $updateUserSql = "UPDATE users SET affiliate_code = ?, updated_at = NOW() WHERE id = ?";
                $userUpdateResult = $this->query($updateUserSql, [$referralCode, $customerId]);
                error_log("User affiliate_code update result: " . ($userUpdateResult ? 'success' : 'failed'));
                
                if ($userUpdateResult) {
                    // Update user session data immediately
                    $this->updateUserSession($customerId);
                    
                    // Clear any cached user data
                    $this->clearUserCache($customerId);
                    
                    $this->jsonSuccess('Successfully joined the affiliate program!', [
                        'referral_code' => $referralCode,
                        'affiliate_id' => $this->getLastInsertId()
                    ]);
                } else {
                    $this->jsonError('Failed to update user affiliate status');
                }
            } else {
                $this->jsonError('Failed to join affiliate program');
            }
        } catch (\Exception $e) {
            error_log("Affiliate join auth error: " . $e->getMessage());
            $this->jsonError('Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Show order confirmation page
     */
    public function orderConfirmation(int $id = 0): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;
        $orderId = $id;

        if (!$orderId) {
            $this->redirect('/customer/orders');
            return;
        }

        try {
            // Get order details
            $order = $this->getOrderDetails($orderId, $customerId);
            if (!$order) {
                $this->redirect('/customer/orders');
                return;
            }

            // Get order items
            $orderItems = $this->getOrderItems($orderId);

            // Get restaurant details
            $restaurant = $this->getRestaurantById($order['restaurant_id']);

            // Parse delivery address
            $deliveryAddress = json_decode($order['delivery_address'], true) ?? [];

            // Render standalone confirmation page (not in dashboard layout)
            include BASE_PATH . '/src/views/orders/confirmation.php';

        } catch (\Exception $e) {
            error_log("Order confirmation error: " . $e->getMessage());
            $this->redirect('/customer/orders');
        }
    }

    /**
     * Track specific order
     */
    public function trackOrder(int $id = 0): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;
        $orderId = $id;

        if (!$orderId) {
            $this->render('customer/order-tracking', [
                'title' => 'Track Order - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'orders',
                'order' => null,
                'error' => 'Order ID is required'
            ]);
            return;
        }

        try {
            // Get order details
            $order = $this->getOrderDetails($orderId, $customerId);
            if (!$order) {
                $this->render('customer/order-tracking', [
                    'title' => 'Track Order - Time2Eat',
                    'user' => $user,
                    'userRole' => 'customer',
                    'currentPage' => 'orders',
                    'order' => null,
                    'error' => 'Order not found'
                ]);
                return;
            }

            // Get rider information if order is being delivered
            $rider = null;
            if (in_array(strtolower($order['status']), ['picked_up', 'on_the_way'])) {
                $rider = $this->getOrderRider($orderId);
            }

            // Get tracking history
            $tracking = $this->getOrderTracking($orderId);

            $this->render('customer/order-tracking', [
                'title' => 'Track Order #' . $order['order_number'] . ' - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'orders',
                'order' => $order,
                'rider' => $rider,
                'tracking' => $tracking
            ]);

        } catch (\Exception $e) {
            error_log("Order tracking error: " . $e->getMessage());
            $this->render('customer/order-tracking', [
                'title' => 'Track Order - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'currentPage' => 'orders',
                'order' => null,
                'error' => 'Unable to load order tracking. Please try again later.'
            ]);
        }
    }

    /**
     * Get order details for tracking
     */
    private function getOrderDetails(int $orderId, int $customerId): ?array
    {
        $sql = "
            SELECT o.*, r.name as restaurant_name, r.logo as restaurant_image,
                   da.address_line_1, da.address_line_2, da.city, da.delivery_instructions,
                   CONCAT(da.address_line_1, ', ', da.city) as delivery_address
            FROM orders o
            JOIN restaurants r ON o.restaurant_id = r.id
            LEFT JOIN delivery_addresses da ON o.delivery_address_id = da.id
            WHERE o.id = ? AND o.customer_id = ?
        ";

        return $this->fetchOne($sql, [$orderId, $customerId]);
    }

    /**
     * Get rider information for order
     */
    private function getOrderRider(int $orderId): ?array
    {
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.phone, u.profile_image,
                   CONCAT(u.first_name, ' ', u.last_name) as name,
                   rl.latitude, rl.longitude, rl.updated_at as location_updated_at
            FROM rider_assignments ra
            JOIN users u ON ra.rider_id = u.id
            LEFT JOIN rider_locations rl ON u.id = rl.rider_id
            WHERE ra.order_id = ? AND ra.status IN ('accepted', 'picked_up')
            ORDER BY ra.assigned_at DESC
            LIMIT 1
        ";

        $rider = $this->fetchOne($sql, [$orderId]);
        
        if ($rider && $rider['latitude'] && $rider['longitude']) {
            $rider['location'] = [
                'latitude' => $rider['latitude'],
                'longitude' => $rider['longitude'],
                'updated_at' => $rider['location_updated_at']
            ];
        }

        return $rider;
    }

    /**
     * Get order tracking history
     */
    private function getOrderTracking(int $orderId): array
    {
        $sql = "
            SELECT status, created_at as timestamp, notes
            FROM order_status_history
            WHERE order_id = ?
            ORDER BY created_at ASC
        ";

        $history = $this->fetchAll($sql, [$orderId]);
        
        // Convert to associative array for easier access
        $tracking = [];
        foreach ($history as $entry) {
            $tracking[$entry['status']] = [
                'timestamp' => $entry['timestamp'],
                'notes' => $entry['notes']
            ];
        }

        return $tracking;
    }

    /**
     * Request withdrawal API endpoint
     */
    public function requestWithdrawal(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        $customerId = $user->id;

        // Get affiliate data
        $affiliate = $this->getUserAffiliate($customerId);
        if (!$affiliate) {
            $this->jsonError('You are not part of the affiliate program');
            return;
        }

        $pendingEarnings = $affiliate['pending_earnings'];
        $withdrawalThreshold = 10000; // 10,000 XAF minimum

        if ($pendingEarnings < $withdrawalThreshold) {
            $this->jsonError('Insufficient balance for withdrawal. Minimum: ' . number_format($withdrawalThreshold, 0) . ' XAF');
            return;
        }

        try {
            // Create payout request
            $sql = "
                INSERT INTO affiliate_payouts (affiliate_id, amount, method, status, created_at, updated_at)
                VALUES (?, ?, 'bank_transfer', 'pending', NOW(), NOW())
            ";

            $this->query($sql, [$affiliate['id'], $pendingEarnings]);

            // Move pending earnings to paid earnings
            $sql = "UPDATE affiliates SET pending_earnings = 0.00, paid_earnings = paid_earnings + ?, updated_at = NOW() WHERE id = ?";
            $this->query($sql, [$pendingEarnings, $affiliate['id']]);

            $this->jsonSuccess('Withdrawal request submitted successfully!');

        } catch (\Exception $e) {
            $this->jsonError('Failed to request withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique referral code
     */
    private function generateReferralCode(): string
    {
        do {
            // Use secure random bytes to generate an 8-character uppercase hex code
            $code = strtoupper(bin2hex(random_bytes(4)));
            $sql = "SELECT id FROM affiliates WHERE affiliate_code = ?";
            $existing = $this->fetchOne($sql, [$code]);
        } while ($existing);

        return $code;
    }

    /**
     * Update user session with fresh data
     */
    private function updateUserSession(int $userId): void
    {
        try {
            // Get fresh user data from database
            $sql = "SELECT * FROM users WHERE id = ?";
            $user = $this->fetchOne($sql, [$userId]);
            
            if ($user) {
                // Update session with fresh user data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['affiliate_code'] = $user['affiliate_code'];
                $_SESSION['last_updated'] = time();
                
                // Update the current user object
                $this->currentUser = (object) $user;
                
                error_log("User session updated for user ID: $userId");
            }
        } catch (\Exception $e) {
            error_log("Error updating user session: " . $e->getMessage());
        }
    }

    /**
     * Clear user-related cache
     */
    private function clearUserCache(int $userId): void
    {
        try {
            // Clear any cached user data
            $cacheKeys = [
                "user_data_{$userId}",
                "user_affiliate_{$userId}",
                "user_profile_{$userId}",
                "affiliate_data_{$userId}",
                "customer_stats_{$userId}"
            ];
            
            foreach ($cacheKeys as $key) {
                $this->clearCacheKey($key);
            }
            
            // Clear session cache
            unset($_SESSION['user_cache']);
            unset($_SESSION['affiliate_cache']);
            unset($_SESSION['profile_cache']);
            
            error_log("User cache cleared for user ID: $userId");
        } catch (\Exception $e) {
            error_log("Error clearing user cache: " . $e->getMessage());
        }
    }

    /**
     * Clear specific cache key
     */
    private function clearCacheKey(string $key): void
    {
        try {
            // Clear file-based cache
            $cacheFile = __DIR__ . '/../../cache/' . md5($key) . '.cache';
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            
            // Clear any other cache mechanisms
            if (class_exists('CacheManager')) {
                $cacheManager = new CacheManager();
                $cacheManager->delete($key);
            }
        } catch (\Exception $e) {
            error_log("Error clearing cache key $key: " . $e->getMessage());
        }
    }

    /**
     * Get fresh user data from database
     */
    private function getFreshUserData(int $userId): ?object
    {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $user = $this->fetchOne($sql, [$userId]);
            
            if ($user) {
                return (object) $user;
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error getting fresh user data: " . $e->getMessage());
            return null;
        }
    }

    // ============================================================================
    // MESSAGING METHODS
    // ============================================================================

    /**
     * Display customer messages dashboard
     */
    public function messages(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        
        // Get conversations for this customer
        $conversations = $this->messageModel->getConversationsForUser($user->id, 'customer');
        
        // Get message statistics
        $stats = $this->messageModel->getMessageStats($user->id);

        $this->render('customer/messages', [
            'title' => 'Messages - Time2Eat',
            'user' => $user,
            'userRole' => 'customer',
            'currentPage' => 'messages',
            'conversations' => $conversations,
            'stats' => $stats
        ]);
    }

    /**
     * Get conversation details and messages
     */
    public function getConversation(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $conversationId = $_GET['id'] ?? '';
        if (!$conversationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid conversation ID'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        
        if (!$conversation) {
            $this->jsonResponse(['success' => false, 'message' => 'Conversation not found'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'conversation' => $conversation]);
    }

    /**
     * Send a message in an existing conversation
     */
    public function sendMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $conversationId = $_POST['conversation_id'] ?? '';
        $message = trim($_POST['message'] ?? '');

        if (!$conversationId || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Get conversation to find recipient
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        if (!$conversation) {
            $this->jsonResponse(['success' => false, 'message' => 'Conversation not found'], 404);
            return;
        }

        // Send the message
        $messageData = [
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'recipient_id' => $conversation['other_party_id'],
            'order_id' => $conversation['order_id'],
            'message' => $message,
            'message_type' => 'text'
        ];

        $success = $this->messageModel->sendMessage($messageData);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get riders for customer's current orders
     */
    public function getRiders(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        
        try {
            $riders = $this->messageModel->getCustomerRiders($user->id);
            $this->jsonResponse(['success' => true, 'riders' => $riders]);
        } catch (\Exception $e) {
            error_log("Error getting customer riders: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to load riders'], 500);
        }
    }

    /**
     * Get restaurants customer has ordered from
     */
    public function getRestaurants(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        
        try {
            $restaurants = $this->messageModel->getCustomerRestaurants($user->id);
            $this->jsonResponse(['success' => true, 'restaurants' => $restaurants]);
        } catch (\Exception $e) {
            error_log("Error getting customer restaurants: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to load restaurants'], 500);
        }
    }

    /**
     * Get customer's orders for messaging
     */
    public function getOrders(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();
        
        try {
            $orders = $this->messageModel->getCustomerOrders($user->id);
            $this->jsonResponse(['success' => true, 'orders' => $orders]);
        } catch (\Exception $e) {
            error_log("Error getting customer orders: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to load orders'], 500);
        }
    }

    /**
     * Compose message to rider
     */
    public function composeMessageToRider(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $riderId = (int)($_POST['rider_id'] ?? 0);
        $orderId = (int)($_POST['order_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$riderId || !$subject || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Verify rider is assigned to customer's order
        if ($orderId) {
            $orderCheck = $this->fetchOne(
                "SELECT ra.rider_id, o.customer_id 
                 FROM rider_assignments ra 
                 JOIN orders o ON ra.order_id = o.id 
                 WHERE ra.order_id = ? AND ra.rider_id = ? AND o.customer_id = ?",
                [$orderId, $riderId, $user->id]
            );

            if (!$orderCheck) {
                $this->jsonResponse(['success' => false, 'message' => 'Rider not assigned to this order or order does not belong to you'], 400);
                return;
            }
        } else {
            // Verify rider has delivered for this customer
            $riderCheck = $this->fetchOne(
                "SELECT COUNT(*) as count 
                 FROM rider_assignments ra 
                 JOIN orders o ON ra.order_id = o.id 
                 WHERE ra.rider_id = ? AND o.customer_id = ?",
                [$riderId, $user->id]
            );

            if (!($riderCheck && $riderCheck['count'] > 0)) {
                $this->jsonResponse(['success' => false, 'message' => 'This rider has not delivered for you'], 400);
                return;
            }
        }

        // Create new conversation with the rider
        $conversationId = $this->messageModel->createConversation(
            $user->id,           // sender (customer)
            $riderId,            // recipient (rider)
            $message,
            $orderId,            // link to order if provided
            $subject
        );

        if ($conversationId) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Compose message to restaurant
     */
    public function composeMessageToRestaurant(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
        $orderId = (int)($_POST['order_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$restaurantId || !$subject || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Verify customer has ordered from this restaurant
        $orderCheck = $this->fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE customer_id = ? AND restaurant_id = ?",
            [$user->id, $restaurantId]
        );

        if (!($orderCheck && $orderCheck['count'] > 0)) {
            $this->jsonResponse(['success' => false, 'message' => 'You have not ordered from this restaurant'], 400);
            return;
        }

        // Get vendor ID from restaurant
        $vendorId = $this->messageModel->getVendorIdFromRestaurant($restaurantId);
        if (!$vendorId) {
            $this->jsonResponse(['success' => false, 'message' => 'Restaurant not found'], 404);
            return;
        }

        // Create new conversation with the restaurant
        $conversationId = $this->messageModel->createConversation(
            $user->id,           // sender (customer)
            $vendorId,           // recipient (vendor)
            $message,
            $orderId,            // link to order if provided
            $subject
        );

        if ($conversationId) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Compose message to admin support
     */
    public function composeMessageToSupport(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $orderId = (int)($_POST['order_id'] ?? 0);

        if (!$subject || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Get admin support user ID
        $adminId = $this->messageModel->getSupportUserId();
        if (!$adminId) {
            $this->jsonResponse(['success' => false, 'message' => 'Support not available'], 500);
            return;
        }

        // Verify order belongs to customer if provided
        if ($orderId) {
            $orderCheck = $this->fetchOne(
                "SELECT COUNT(*) as count FROM orders WHERE id = ? AND customer_id = ?",
                [$orderId, $user->id]
            );

            if (!($orderCheck && $orderCheck['count'] > 0)) {
                $this->jsonResponse(['success' => false, 'message' => 'Order not found or does not belong to you'], 400);
                return;
            }
        }

        // Create new conversation with admin support
        $conversationId = $this->messageModel->createConversation(
            $user->id,           // sender (customer)
            $adminId,            // recipient (admin)
            $message,
            $orderId,            // link to order if provided
            $subject
        );

        if ($conversationId) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Compose a new message/conversation
     */
    public function composeMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $recipientType = $_POST['recipient_type'] ?? '';
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$recipientType || !$subject || !$message) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $recipientId = null;
        $orderId = null;

        // Determine recipient based on type
        switch ($recipientType) {
            case 'restaurant':
                $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
                if (!$restaurantId) {
                    $this->jsonResponse(['success' => false, 'message' => 'Restaurant is required'], 400);
                    return;
                }
                $recipientId = $this->messageModel->getVendorIdFromRestaurant($restaurantId);
                break;

            case 'support':
                $recipientId = $this->messageModel->getSupportUserId();
                break;

            case 'order_inquiry':
                $orderId = (int)($_POST['order_id'] ?? 0);
                if (!$orderId) {
                    $this->jsonResponse(['success' => false, 'message' => 'Order is required'], 400);
                    return;
                }
                
                // Get restaurant from order and find vendor
                $orderResult = $this->fetchOne(
                    "SELECT restaurant_id FROM orders WHERE id = ? AND customer_id = ?", 
                    [$orderId, $user->id]
                );
                
                if (!$orderResult) {
                    $this->jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
                    return;
                }
                
                $recipientId = $this->messageModel->getVendorIdFromRestaurant($orderResult['restaurant_id']);
                break;

            default:
                $this->jsonResponse(['success' => false, 'message' => 'Invalid recipient type'], 400);
                return;
        }

        if (!$recipientId) {
            $this->jsonResponse(['success' => false, 'message' => 'Recipient not found'], 404);
            return;
        }

        // Create new conversation
        $conversationId = $this->messageModel->createConversation(
            $user->id, 
            $recipientId, 
            $message, 
            $orderId, 
            $subject
        );

        if ($conversationId) {
            $this->jsonResponse(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Customer notifications page
     */
    public function notifications(): void
    {
        $this->requireAuth();
        $this->requireRole('customer');

        $user = $this->getCurrentUser();

        try {
            // Get notifications for customer
            $notifications = $this->fetchAll("
                SELECT
                    id,
                    title,
                    message,
                    type,
                    priority,
                    created_at,
                    read_at,
                    action_url,
                    action_text
                FROM popup_notifications
                WHERE (target_user_id = ? OR target_user_id IS NULL)
                AND is_dismissed = 0
                AND (expires_at IS NULL OR expires_at > NOW())
                AND deleted_at IS NULL
                ORDER BY priority DESC, created_at DESC
                LIMIT 50
            ", [$user->id]);

            // Get notification statistics
            $stats = [
                'total' => count($notifications),
                'unread' => 0,
                'urgent_unread' => 0,
                'order_updates' => 0,
                'system_alerts' => 0
            ];

            foreach ($notifications as $notification) {
                if (!$notification['read_at']) {
                    $stats['unread']++;
                    if ($notification['priority'] === 'urgent') {
                        $stats['urgent_unread']++;
                    }
                }

                if ($notification['type'] === 'order_update') {
                    $stats['order_updates']++;
                } elseif ($notification['type'] === 'system_alert') {
                    $stats['system_alerts']++;
                }
            }

            $this->render('customer/notifications', [
                'title' => 'Notifications - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'notifications' => $notifications,
                'stats' => $stats,
                'currentPage' => 'notifications'
            ]);

        } catch (\Exception $e) {
            error_log("Error loading customer notifications: " . $e->getMessage());
            $this->render('customer/notifications', [
                'title' => 'Notifications - Time2Eat',
                'user' => $user,
                'userRole' => 'customer',
                'notifications' => [],
                'stats' => ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'order_updates' => 0, 'system_alerts' => 0],
                'error' => 'Failed to load notifications',
                'currentPage' => 'notifications'
            ]);
        }
    }
}