<?php

declare(strict_types=1);

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Delivery.php';
require_once __DIR__ . '/../models/Message.php';

use core\BaseController;

class RiderDashboardController extends BaseController
{
    private $userModel;
    private $deliveryModel;
    private $orderModel;
    private $messageModel;

    public function __construct()
    {
        parent::__construct();
        
        // Set dashboard layout
        $this->layout = 'dashboard';
        
        $this->userModel = new \models\User();
        $this->orderModel = new \models\Order();
        $this->deliveryModel = new \models\Delivery();
        $this->messageModel = new \models\Message();
    }

    public function index(): void
    {
        // CRITICAL: Prevent caching of dashboard (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        
        // Get rider statistics
        $stats = $this->getRiderStats($user->id);
        
        // Get active deliveries
        $activeDeliveries = $this->deliveryModel->getActiveDeliveriesByRider($user->id);
        
        // Get available orders
        $availableOrders = $this->orderModel->getAvailableOrdersForRider($user->id, 10);
        
        // Get recent activity
        $recentActivity = $this->deliveryModel->getRecentActivityByRider($user->id, 10);

        $this->render('dashboard/rider', [
            'title' => 'Rider Dashboard - Time2Eat',
            'user' => $user,
            'stats' => $stats,
            'activeDeliveries' => $activeDeliveries,
            'availableOrders' => $availableOrders,
            'recentActivity' => $recentActivity
        ]);
    }

    public function deliveries(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        $status = $_GET['status'] ?? 'active';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get deliveries with pagination and filtering
        $deliveries = $this->deliveryModel->getDeliveriesByRider($user->id, $status, $limit, $offset);
        $totalDeliveries = $this->deliveryModel->countDeliveriesByRider($user->id, $status);
        $totalPages = ceil($totalDeliveries / $limit);

        // Get delivery status counts
        $statusCounts = $this->deliveryModel->getDeliveryStatusCounts($user->id);
        
        // Get today's performance stats
        $todayStats = $this->getTodayStats($user->id);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($user->id);

        $this->render('rider/deliveries', [
            'title' => 'Active Deliveries - Time2Eat',
            'user' => $user,
            'deliveries' => $deliveries,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'currentPage' => 'deliveries',
            'paginationPage' => $page,
            'totalPages' => $totalPages,
            'totalDeliveries' => $totalDeliveries,
            'todayStats' => $todayStats,
            'recentActivity' => $recentActivity
        ]);
    }

    public function available(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        
        // Check if rider is available
        if (!$user->is_available) {
            $this->render('rider/unavailable', [
                'title' => 'Go Online - Time2Eat',
                'user' => $user
            ]);
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get available orders
        $availableOrders = $this->orderModel->getAvailableOrdersForRider($user->id, $limit, $offset);
        $totalOrders = $this->orderModel->countAvailableOrdersForRider($user->id);
        $totalPages = ceil($totalOrders / $limit);

        $this->render('rider/available', [
            'title' => 'Available Orders - Time2Eat',
            'user' => $user,
            'availableOrders' => $availableOrders,
            'currentPage' => 'available',
            'paginationPage' => $page,
            'totalPages' => $totalPages,
            'totalOrders' => $totalOrders
        ]);
    }

    public function earnings(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        $period = $_GET['period'] ?? '7days';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get earnings data
        $earnings = $this->deliveryModel->getEarningsByRider($user->id, $period, $limit, $offset);
        $totalEarnings = $this->deliveryModel->getTotalEarnings($user->id);
        $weeklyEarnings = $this->deliveryModel->getWeeklyEarnings($user->id);
        $monthlyEarnings = $this->deliveryModel->getMonthlyEarnings($user->id);
        
        // Get available balance for withdrawal
        $availableBalance = $this->deliveryModel->getAvailableBalance($user->id);
        $pendingWithdrawals = $this->deliveryModel->getPendingWithdrawals($user->id);

        $totalPages = ceil(count($earnings) / $limit);

        $this->render('rider/earnings', [
            'title' => 'My Earnings - Time2Eat',
            'user' => $user,
            'earnings' => $earnings,
            'totalEarnings' => $totalEarnings,
            'availableBalance' => $availableBalance,
            'pendingWithdrawals' => $pendingWithdrawals,
            'weeklyEarnings' => $weeklyEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'currentPeriod' => $period,
            'currentPage' => 'earnings',
            'paginationPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function requestWithdrawal(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $user = $this->getCurrentUser();

        // Validate input
        if (empty($input['amount']) || empty($input['payment_method'])) {
            $this->json(['success' => false, 'message' => 'Amount and payment method are required'], 422);
            return;
        }

        $amount = (float)$input['amount'];
        $paymentMethod = $input['payment_method'];
        $accountDetails = $input['account_details'] ?? null;

        // Check minimum withdrawal amount
        if ($amount < 2000) {
            $this->json(['success' => false, 'message' => 'Minimum withdrawal amount is 2,000 XAF'], 422);
            return;
        }

        // Check available balance
        $availableBalance = $this->deliveryModel->getAvailableBalance($user->id);
        if ($amount > $availableBalance) {
            $this->json(['success' => false, 'message' => 'Insufficient balance'], 422);
            return;
        }

        try {
            // Create withdrawal request
            require_once __DIR__ . '/../services/WithdrawalService.php';
            $withdrawalService = new \Time2Eat\Services\WithdrawalService();

            $withdrawalData = [
                'user_id' => $user->id,
                'withdrawal_type' => 'rider',
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'account_details' => $accountDetails,
                'status' => 'pending'
            ];

            $result = $withdrawalService->createWithdrawal($withdrawalData);

            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Withdrawal request submitted successfully',
                    'withdrawal_id' => $result['withdrawal_id']
                ]);
            } else {
                $this->json(['success' => false, 'message' => $result['message']], 500);
            }
        } catch (\Exception $e) {
            error_log("Withdrawal request error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to process withdrawal request'], 500);
        }
    }

    public function schedule(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSchedule();
            return;
        }

        // Get rider's schedule
        $schedule = $this->userModel->getRiderSchedule($user->id);
        
        // Get today's schedule
        $todaySchedule = $this->getTodaySchedule($schedule);
        
        // Get upcoming deliveries
        $upcomingDeliveries = $this->getUpcomingDeliveries($user->id);
        
        // Get weekly performance stats
        $weeklyStats = $this->getWeeklyStats($user->id);

        $this->render('rider/schedule', [
            'title' => 'My Schedule - Time2Eat',
            'user' => $user,
            'schedule' => $schedule,
            'todaySchedule' => $todaySchedule,
            'upcomingDeliveries' => $upcomingDeliveries,
            'weeklyStats' => $weeklyStats,
            'currentPage' => 'schedule'
        ]);
    }

    public function performance(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        $period = $_GET['period'] ?? '30days';

        // Get performance metrics
        $performance = $this->deliveryModel->getPerformanceMetrics($user->id, $period);
        $ratings = $this->deliveryModel->getRatingsByRider($user->id, $period);
        $deliveryTimes = $this->deliveryModel->getDeliveryTimeAnalytics($user->id, $period);
        $dailyPerformance = $this->deliveryModel->getDailyPerformanceData($user->id, '7days');

        $this->render('rider/performance', [
            'title' => 'Performance Metrics - Time2Eat',
            'user' => $user,
            'performance' => $performance,
            'ratings' => $ratings,
            'deliveryTimes' => $deliveryTimes,
            'dailyPerformance' => $dailyPerformance,
            'currentPeriod' => $period,
            'currentPage' => 'performance'
        ]);
    }

    public function toggleAvailability(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        // Get JSON input
        $input = $this->getJsonInput();
        error_log("Toggle availability request - User ID: " . ($_SESSION['user_id'] ?? 'not set'));
        error_log("Toggle availability request - JSON input: " . json_encode($input));

        $user = $this->getCurrentUser();
        if (!$user) {
            error_log("Toggle availability error - User not found");
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Load RiderStatusService
        require_once __DIR__ . '/../services/RiderStatusService.php';
        $riderStatusService = new \Time2Eat\Services\RiderStatusService();

        // Get location data if provided
        $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
        $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

        // Toggle rider status using the service
        $result = $riderStatusService->toggleRiderStatus($user->id, $latitude, $longitude);
        
        error_log("Toggle availability - Service result: " . json_encode($result));

        // CRITICAL: Prevent caching of this response
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => $result['message'],
                'status' => $result['status'],
                'is_available' => $result['status'] === 'online',
                'timestamp' => $result['timestamp']
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    public function getStatus(): void
    {
        // CRITICAL: Prevent caching of this response
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Load RiderStatusService
        require_once __DIR__ . '/../services/RiderStatusService.php';
        $riderStatusService = new \Time2Eat\Services\RiderStatusService();

        // Get comprehensive status
        $status = $riderStatusService->getRiderStatus($user->id);
        
        if ($status['success']) {
            $this->json([
                'success' => true,
                'is_available' => $status['is_available'],
                'is_online' => $status['is_online'],
                'account_status' => $status['account_status'],
                'overall_status' => $status['overall_status'],
                'last_location' => $status['last_location'],
                'schedule_status' => $status['schedule_status'],
                'role' => $user->role ?? 'rider'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $status['message']
            ], 500);
        }
    }

    public function acceptOrder(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        // Get JSON input
        $input = $this->getJsonInput();
        if (empty($input)) {
            $input = $_POST;
        }

        error_log("Accept order request - Input: " . json_encode($input));

        if (empty($input['order_id'])) {
            $this->json(['success' => false, 'message' => 'Order ID is required'], 422);
            return;
        }

        $user = $this->getCurrentUser();
        $orderId = (int)$input['order_id'];

        // Check if rider is available
        if (!$user->is_available) {
            $this->json(['success' => false, 'message' => 'You must be online to accept orders'], 400);
            return;
        }

        // Check if order is still available
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order || $order['status'] !== 'ready' || $order['rider_id']) {
            $this->json(['success' => false, 'message' => 'Order is no longer available'], 400);
            return;
        }

        // Use transaction to ensure atomicity
        // If delivery creation fails, rollback the order assignment
        try {
            $this->orderModel->beginTransaction();

            // Assign order to rider
            $assigned = $this->orderModel->assignRider($orderId, $user->id);

            if (!$assigned) {
                $this->orderModel->rollback();
                $this->json(['success' => false, 'message' => 'Failed to accept order'], 500);
                return;
            }

            // Create delivery record
            $deliveryData = [
                'order_id' => $orderId,
                'rider_id' => $user->id,
                'status' => 'assigned',
                'assigned_at' => date('Y-m-d H:i:s')
            ];
            
            $deliveryId = $this->deliveryModel->create($deliveryData);

            if (!$deliveryId) {
                // Rollback order assignment if delivery creation fails
                $this->orderModel->rollback();
                error_log("Failed to create delivery record for order {$orderId}, rolling back assignment");
                $this->json(['success' => false, 'message' => 'Failed to create delivery record. Please try again.'], 500);
                return;
            }

            // Commit transaction
            $this->orderModel->commit();
            
            error_log("Order {$orderId} accepted successfully by rider {$user->id}, delivery ID: {$deliveryId}");
            $this->json(['success' => true, 'message' => 'Order accepted successfully', 'delivery_id' => $deliveryId]);
            
        } catch (\Exception $e) {
            // Rollback on any exception
            try {
                $this->orderModel->rollback();
            } catch (\Exception $rollbackException) {
                // Ignore rollback errors if transaction already ended
                error_log("Rollback error (may be expected): " . $rollbackException->getMessage());
            }
            error_log("Exception accepting order {$orderId}: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while accepting the order. Please try again.'], 500);
        }
    }

    public function updateDeliveryStatus(): void
    {
        // CRITICAL: Clear any output buffer and ensure we return JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON headers immediately to prevent any HTML output
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        
        // Wrap everything in try-catch to ensure JSON response
        try {
            // Check authentication manually to ensure JSON response
            if (!$this->isAuthenticated()) {
                $this->json(['success' => false, 'message' => 'Authentication required'], 401);
                return;
            }
            
            // Check role manually to ensure JSON response
            if (!$this->hasRole('rider')) {
                $this->json(['success' => false, 'message' => 'Insufficient permissions. Rider role required.'], 403);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            // Get JSON input
            $input = $this->getJsonInput();
            if (empty($input)) {
                $input = $_POST;
            }

            error_log("Update delivery status request - Input: " . json_encode($input));

            // Validate required fields - accept either delivery_id or order_id
            if (empty($input['delivery_id']) && empty($input['order_id'])) {
                $this->json(['success' => false, 'message' => 'Delivery ID or Order ID is required'], 422);
                return;
            }

            if (empty($input['status'])) {
                $this->json(['success' => false, 'message' => 'Status is required'], 422);
                return;
            }

            $validStatuses = ['picked_up', 'on_the_way', 'delivered'];
            if (!in_array($input['status'], $validStatuses)) {
                $this->json(['success' => false, 'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)], 422);
                return;
            }

            $user = $this->getCurrentUser();
            $data = $input;

            // Get delivery by delivery_id or order_id
            if (!empty($input['delivery_id'])) {
                $deliveryId = (int)$input['delivery_id'];
                $delivery = $this->deliveryModel->getById($deliveryId);
            } else {
                $orderId = (int)$input['order_id'];
                // Find delivery by order_id and rider_id
                $delivery = $this->deliveryModel->getByOrderAndRider($orderId, $user->id);
                $deliveryId = $delivery ? $delivery['id'] : null;
                
                // If no delivery record exists, create one for this order
                if (!$delivery) {
                    // Verify the order exists and is assigned to this rider
                    $order = $this->orderModel->getOrderById($orderId);
                    if (!$order || $order['rider_id'] !== $user->id) {
                        $this->json(['success' => false, 'message' => 'Order not found or does not belong to you (Order #' . ($order['order_number'] ?? 'Unknown') . ')'], 404);
                        return;
                    }
                    
                    // Create delivery record with required fields from order
                    // Get restaurant address for pickup
                    try {
                        $db = $this->getDb();
                        $stmt = $db->prepare("SELECT address, city, latitude, longitude FROM restaurants WHERE id = ?");
                        $stmt->execute([$order['restaurant_id']]);
                        $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);
                    } catch (\Exception $e) {
                        error_log("Error fetching restaurant: " . $e->getMessage());
                        $restaurant = null;
                    }
                    
                    $pickupAddress = [
                        'restaurant_id' => $order['restaurant_id'],
                        'address' => $restaurant['address'] ?? 'Restaurant Address',
                        'city' => $restaurant['city'] ?? '',
                        'latitude' => $restaurant['latitude'] ?? null,
                        'longitude' => $restaurant['longitude'] ?? null
                    ];
                    
                    // Parse delivery address if it's JSON, otherwise create structure
                    $deliveryAddress = $order['delivery_address'];
                    if (is_string($deliveryAddress)) {
                        $decoded = json_decode($deliveryAddress, true);
                        $deliveryAddress = $decoded ?: ['address' => $deliveryAddress];
                    }
                    
                    $deliveryData = [
                        'order_id' => $orderId,
                        'rider_id' => $user->id,
                        'pickup_address' => json_encode($pickupAddress),
                        'delivery_address' => is_string($deliveryAddress) ? $deliveryAddress : json_encode($deliveryAddress),
                        'delivery_fee' => $order['delivery_fee'] ?? 0,
                        'rider_earnings' => ($order['delivery_fee'] ?? 0) * 0.8, // 80% to rider
                        'platform_commission' => ($order['delivery_fee'] ?? 0) * 0.2, // 20% platform fee
                        'status' => 'assigned'
                    ];
                    
                    $deliveryId = $this->deliveryModel->createDelivery($deliveryData);
                    if ($deliveryId) {
                        $delivery = $this->deliveryModel->getById($deliveryId);
                        error_log("Created missing delivery record for order {$orderId}, delivery ID: {$deliveryId}");
                    } else {
                        $this->json(['success' => false, 'message' => 'Failed to create delivery record'], 500);
                        return;
                    }
                }
            }

            // Verify delivery exists and belongs to this rider
            if (!$delivery || $delivery['rider_id'] !== $user->id) {
                $this->json(['success' => false, 'message' => 'Delivery not found or does not belong to you'], 404);
                return;
            }

            // Update delivery status
            $updateData = [
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Store location in tracking_data if provided
            if (isset($data['latitude']) && isset($data['longitude'])) {
                $updateData['tracking_data'] = json_encode([
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }

            if (isset($data['notes'])) {
                $updateData['rider_notes'] = $data['notes'];
            }

            // Set timestamp based on status
            switch ($data['status']) {
                case 'picked_up':
                    $updateData['pickup_time'] = date('Y-m-d H:i:s');
                    // Also update order status
                    $this->orderModel->updateOrderStatus($delivery['order_id'], 'picked_up');
                    break;
                case 'on_the_way':
                    // Delivery record doesn't have on_the_way timestamp, just update status
                    // Also update order status
                    $this->orderModel->updateOrderStatus($delivery['order_id'], 'on_the_way');
                    break;
                case 'delivered':
                    $updateData['delivery_time'] = date('Y-m-d H:i:s');
                    // Also update order status
                    try {
                        $orderUpdated = $this->orderModel->updateOrderStatus($delivery['order_id'], 'delivered');
                        if (!$orderUpdated) {
                            error_log("Failed to update order status to delivered for order ID: {$delivery['order_id']}");
                            // Continue with delivery update even if order update fails
                        }
                    } catch (\Exception $e) {
                        error_log("Exception updating order status to delivered: " . $e->getMessage());
                        // Continue with delivery update even if order update throws exception
                    }
                    break;
            }

            // Log the update attempt
            error_log("Attempting to update delivery ID: {$deliveryId} with status: {$data['status']}, data: " . json_encode($updateData));
            
            $updated = $this->deliveryModel->updateDelivery($deliveryId, $updateData);

            if ($updated) {
                // Send notification to customer
                try {
                    $this->sendDeliveryStatusNotification($delivery, $data['status']);
                } catch (\Exception $e) {
                    error_log("Failed to send delivery status notification: " . $e->getMessage());
                    // Don't fail the update if notification fails
                }
                
                error_log("Successfully updated delivery ID: {$deliveryId} to status: {$data['status']}");
                $this->json(['success' => true, 'message' => 'Delivery status updated successfully']);
            } else {
                // Get more detailed error information
                $errorMsg = 'Failed to update delivery status. Please check the logs for details.';
                error_log("Delivery update failed for delivery ID: {$deliveryId}, status: {$data['status']}, updateData: " . json_encode($updateData));
                
                // Verify delivery still exists
                $verifyDelivery = $this->deliveryModel->getById($deliveryId);
                if (!$verifyDelivery) {
                    $errorMsg = 'Delivery record not found. It may have been deleted.';
                    error_log("Delivery ID {$deliveryId} does not exist in database");
                }
                
                $this->json(['success' => false, 'message' => $errorMsg], 500);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            $errorTrace = $e->getTraceAsString();
            
            error_log("Exception updating delivery status: {$errorMessage}\nFile: {$errorFile}:{$errorLine}\nStack trace: {$errorTrace}");
            
            // Ensure we return JSON even on exceptions
            if (ob_get_level()) {
                ob_clean();
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            $this->json([
                'success' => false, 
                'message' => 'An error occurred: ' . $errorMessage,
                'error_type' => 'Exception',
                'file' => basename($errorFile),
                'line' => $errorLine
            ], 500);
        } catch (\Error $e) {
            // Catch fatal errors and PHP 7+ errors
            $errorMessage = $e->getMessage();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            $errorTrace = $e->getTraceAsString();
            
            error_log("Fatal error updating delivery status: {$errorMessage}\nFile: {$errorFile}:{$errorLine}\nStack trace: {$errorTrace}");
            
            // Ensure we return JSON even on fatal errors
            if (ob_get_level()) {
                ob_clean();
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            $this->json([
                'success' => false, 
                'message' => 'A system error occurred: ' . $errorMessage,
                'error_type' => 'Fatal Error',
                'file' => basename($errorFile),
                'line' => $errorLine
            ], 500);
        } catch (\Throwable $e) {
            // Catch any other throwable
            $errorMessage = $e->getMessage();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            $errorTrace = $e->getTraceAsString();
            
            error_log("Throwable error updating delivery status: {$errorMessage}\nFile: {$errorFile}:{$errorLine}\nStack trace: {$errorTrace}");
            
            // Ensure we return JSON even on throwable errors
            if (ob_get_level()) {
                ob_clean();
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            $this->json([
                'success' => false, 
                'message' => 'An unexpected error occurred: ' . $errorMessage,
                'error_type' => 'Throwable',
                'file' => basename($errorFile),
                'line' => $errorLine
            ], 500);
        }
    }

    private function getRiderStats(int $riderId): array
    {
        return [
            'activeDeliveries' => $this->deliveryModel->countActiveDeliveriesByRider($riderId),
            'todayEarnings' => $this->deliveryModel->getTodayEarnings($riderId),
            'todayDeliveries' => $this->deliveryModel->getTodayDeliveryCount($riderId),
            'averageRating' => $this->deliveryModel->getAverageRating($riderId),
            'totalDeliveries' => $this->deliveryModel->getTotalDeliveryCount($riderId),
            'weeklyEarnings' => $this->deliveryModel->getWeeklyEarnings($riderId),
            'monthlyEarnings' => $this->deliveryModel->getMonthlyEarnings($riderId),
            'completionRate' => $this->deliveryModel->getCompletionRate($riderId)
        ];
    }

    private function updateSchedule(): void
    {
        $validation = $this->validateRequest([
            'schedule' => 'required|json'
        ]);

        if (!$validation['isValid']) {
            $this->json(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getCurrentUser();
        $schedule = json_decode($validation['data']['schedule'], true);

        // Validate schedule format
        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($schedule as $day => $times) {
            if (!in_array($day, $validDays)) {
                $this->json(['success' => false, 'message' => 'Invalid day: ' . $day], 400);
                return;
            }

            if (!isset($times['start']) || !isset($times['end']) || !isset($times['available'])) {
                $this->json(['success' => false, 'message' => 'Invalid schedule format'], 400);
                return;
            }
        }

        $updated = $this->userModel->updateRiderSchedule($user->id, $schedule);

        if ($updated) {
            $this->json(['success' => true, 'message' => 'Schedule updated successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update schedule'], 500);
        }
    }

    /**
     * Get today's schedule from weekly schedule
     */
    private function getTodaySchedule(array $schedule): ?array
    {
        $today = strtolower(date('l')); // monday, tuesday, etc.
        return $schedule[$today] ?? null;
    }

    /**
     * Get upcoming deliveries for the rider
     */
    private function getUpcomingDeliveries(int $riderId): array
    {
        try {
            $sql = "SELECT d.*, o.id as order_id, r.name as restaurant_name, 
                           CONCAT(u.first_name, ' ', u.last_name) as customer_name
                    FROM deliveries d
                    JOIN orders o ON d.order_id = o.id
                    JOIN restaurants r ON o.restaurant_id = r.id
                    JOIN users u ON o.user_id = u.id
                    WHERE d.rider_id = ? 
                    AND d.status IN ('assigned', 'picked_up', 'on_the_way')
                    AND d.scheduled_delivery_time >= NOW()
                    ORDER BY d.scheduled_delivery_time ASC
                    LIMIT 5";
            
            $deliveries = $this->fetchAll($sql, [$riderId]);
            
            // Format the data for display
            $formatted = [];
            foreach ($deliveries as $delivery) {
                $formatted[] = [
                    'order_id' => $delivery['order_id'],
                    'restaurant_name' => $delivery['restaurant_name'],
                    'customer_name' => $delivery['customer_name'],
                    'scheduled_time' => date('H:i', strtotime($delivery['scheduled_delivery_time'])),
                    'status' => ucfirst(str_replace('_', ' ', $delivery['status'])),
                    'address' => $delivery['delivery_address'] ?? 'Address not provided'
                ];
            }
            
            return $formatted;
        } catch (\Exception $e) {
            error_log("Error getting upcoming deliveries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get weekly performance statistics
     */
    private function getWeeklyStats(int $riderId): array
    {
        try {
            // Get deliveries count for this week
            $sql = "SELECT COUNT(*) as deliveries
                    FROM deliveries 
                    WHERE rider_id = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND status = 'delivered'";
            $deliveriesResult = $this->fetchOne($sql, [$riderId]);
            
            // Get earnings for this week
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings
                    FROM deliveries 
                    WHERE rider_id = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND status = 'delivered'";
            $earningsResult = $this->fetchOne($sql, [$riderId]);
            
            // Get average rating for this week
            $sql = "SELECT COALESCE(AVG(rating), 0) as rating
                    FROM reviews r
                    JOIN deliveries d ON r.order_id = d.order_id
                    WHERE d.rider_id = ? 
                    AND r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $ratingResult = $this->fetchOne($sql, [$riderId]);
            
            // Calculate hours worked (simplified - based on schedule)
            $schedule = $this->userModel->getRiderSchedule($riderId);
            $hoursWorked = 0;
            foreach ($schedule as $daySchedule) {
                if ($daySchedule['available']) {
                    $start = strtotime($daySchedule['start']);
                    $end = strtotime($daySchedule['end']);
                    $hoursWorked += ($end - $start) / 3600;
                }
            }
            
            return [
                'deliveries' => (int)($deliveriesResult['deliveries'] ?? 0),
                'earnings' => number_format((float)($earningsResult['earnings'] ?? 0)),
                'rating' => number_format((float)($ratingResult['rating'] ?? 0), 1),
                'hours_worked' => number_format($hoursWorked, 1)
            ];
        } catch (\Exception $e) {
            error_log("Error getting weekly stats: " . $e->getMessage());
            return [
                'deliveries' => 0,
                'earnings' => '0',
                'rating' => '0.0',
                'hours_worked' => '0.0'
            ];
        }
    }

    private function sendDeliveryStatusNotification(array $delivery, string $status): void
    {
        $statusMessages = [
            'picked_up' => 'Your order has been picked up and is on the way.',
            'in_transit' => 'Your order is in transit.',
            'delivered' => 'Your order has been delivered. Enjoy your meal!'
        ];

        $message = $statusMessages[$status] ?? 'Your delivery status has been updated.';
        
        // Send notification logic here
        // Example: $this->notificationService->send($delivery['customer_id'], $message);
    }

    /**
     * Get today's performance statistics
     */
    private function getTodayStats(int $riderId): array
    {
        try {
            // Get today's deliveries count
            $sql = "SELECT COUNT(*) as deliveries
                    FROM deliveries 
                    WHERE rider_id = ? 
                    AND DATE(created_at) = CURDATE()
                    AND status = 'delivered'";
            $deliveriesResult = $this->fetchOne($sql, [$riderId]);
            
            // Get today's earnings
            $sql = "SELECT COALESCE(SUM(rider_earnings), 0) as earnings
                    FROM deliveries 
                    WHERE rider_id = ? 
                    AND DATE(created_at) = CURDATE()
                    AND status = 'delivered'";
            $earningsResult = $this->fetchOne($sql, [$riderId]);
            
            // Get today's average rating
            $sql = "SELECT COALESCE(AVG(r.rating), 0) as rating
                    FROM reviews r
                    JOIN deliveries d ON r.order_id = d.order_id
                    WHERE d.rider_id = ? 
                    AND DATE(r.created_at) = CURDATE()";
            $ratingResult = $this->fetchOne($sql, [$riderId]);
            
            // Calculate hours worked today (simplified)
            $sql = "SELECT 
                        COALESCE(SUM(TIMESTAMPDIFF(MINUTE, 
                            CASE WHEN picked_up_at IS NOT NULL THEN picked_up_at ELSE created_at END,
                            CASE WHEN delivered_at IS NOT NULL THEN delivered_at ELSE NOW() END
                        )), 0) / 60 as hours
                    FROM deliveries 
                    WHERE rider_id = ? 
                    AND DATE(created_at) = CURDATE()
                    AND status IN ('picked_up', 'on_the_way', 'delivered')";
            $hoursResult = $this->fetchOne($sql, [$riderId]);
            
            return [
                'deliveries' => (int)($deliveriesResult['deliveries'] ?? 0),
                'earnings' => (float)($earningsResult['earnings'] ?? 0),
                'rating' => number_format((float)($ratingResult['rating'] ?? 0), 1),
                'hours' => number_format((float)($hoursResult['hours'] ?? 0), 1)
            ];
        } catch (\Exception $e) {
            error_log("Error getting today's stats: " . $e->getMessage());
            return [
                'deliveries' => 0,
                'earnings' => 0,
                'rating' => '0.0',
                'hours' => '0.0'
            ];
        }
    }

    /**
     * Get recent activity for the rider
     */
    private function getRecentActivity(int $riderId): array
    {
        try {
            $sql = "SELECT 
                        d.id,
                        d.status,
                        d.updated_at,
                        r.name as restaurant_name,
                        CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                        o.order_number
                    FROM deliveries d
                    JOIN orders o ON d.order_id = o.id
                    JOIN restaurants r ON o.restaurant_id = r.id
                    JOIN users u ON o.user_id = u.id
                    WHERE d.rider_id = ? 
                    AND d.updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ORDER BY d.updated_at DESC
                    LIMIT 5";
            
            $activities = $this->fetchAll($sql, [$riderId]);
            
            $formatted = [];
            foreach ($activities as $activity) {
                $statusMessages = [
                    'assigned' => 'New delivery assigned',
                    'picked_up' => 'Order picked up from ' . $activity['restaurant_name'],
                    'in_transit' => 'Delivery started for ' . $activity['customer_name'],
                    'delivered' => 'Order delivered to ' . $activity['customer_name']
                ];
                
                $icons = [
                    'assigned' => 'clock',
                    'picked_up' => 'package',
                    'in_transit' => 'truck',
                    'delivered' => 'check-circle'
                ];
                
                $formatted[] = [
                    'message' => $statusMessages[$activity['status']] ?? 'Delivery updated',
                    'time' => $this->timeAgo($activity['updated_at']),
                    'icon' => $icons[$activity['status']] ?? 'circle'
                ];
            }
            
            return $formatted;
        } catch (\Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get time ago string
     */
    private function timeAgo(string $datetime): string
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time / 60) . 'm ago';
        if ($time < 86400) return floor($time / 3600) . 'h ago';
        return floor($time / 86400) . 'd ago';
    }

    /**
     * Display and handle rider profile
     */
    public function profile(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfileUpdate($user);
            return;
        }

        // Get rider's vehicle information if exists
        $vehicleInfo = $this->getRiderVehicleInfo($user->id);

        $this->render('rider/profile', [
            'title' => 'My Profile - Time2Eat',
            'user' => $user,
            'vehicleInfo' => $vehicleInfo,
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Handle profile update
     */
    private function handleProfileUpdate($user): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $this->json(['success' => false, 'message' => 'Invalid data'], 400);
                return;
            }

            $updateData = [];
            
            // Update personal information
            if (isset($data['first_name'])) {
                $updateData['first_name'] = trim($data['first_name']);
            }
            if (isset($data['last_name'])) {
                $updateData['last_name'] = trim($data['last_name']);
            }
            if (isset($data['phone'])) {
                $updateData['phone'] = trim($data['phone']);
            }

            // Update user information
            if (!empty($updateData)) {
                $updated = $this->userModel->update($user->id, $updateData);
                if (!$updated) {
                    $this->json(['success' => false, 'message' => 'Failed to update profile'], 500);
                    return;
                }
            }

            // Update vehicle information if provided
            if (isset($data['vehicle'])) {
                $this->updateVehicleInfo($user->id, $data['vehicle']);
            }

            $this->json(['success' => true, 'message' => 'Profile updated successfully']);
            
        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating profile'], 500);
        }
    }

    /**
     * Get rider's vehicle information
     */
    private function getRiderVehicleInfo(int $riderId): array
    {
        try {
            $sql = "SELECT * FROM rider_vehicles WHERE rider_id = ?";
            $result = $this->fetchOne($sql, [$riderId]);
            
            return $result ?: [
                'vehicle_type' => '',
                'vehicle_make' => '',
                'vehicle_model' => '',
                'vehicle_year' => '',
                'license_plate' => '',
                'insurance_number' => ''
            ];
        } catch (\Exception $e) {
            error_log("Error getting vehicle info: " . $e->getMessage());
            return [
                'vehicle_type' => '',
                'vehicle_make' => '',
                'vehicle_model' => '',
                'vehicle_year' => '',
                'license_plate' => '',
                'insurance_number' => ''
            ];
        }
    }

    /**
     * Update rider's vehicle information
     */
    private function updateVehicleInfo(int $riderId, array $vehicleData): bool
    {
        try {
            $allowedFields = ['vehicle_type', 'vehicle_make', 'vehicle_model', 'vehicle_year', 'license_plate', 'insurance_number'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($vehicleData[$field])) {
                    $updateData[$field] = trim($vehicleData[$field]);
                }
            }
            
            if (empty($updateData)) {
                return true;
            }
            
            // Try to update existing record first
            $sql = "UPDATE rider_vehicles SET " . 
                   implode(' = ?, ', array_keys($updateData)) . 
                   " = ?, updated_at = NOW() WHERE rider_id = ?";
            
            $params = array_values($updateData);
            $params[] = $riderId;
            
            $updated = $this->execute($sql, $params);
            
            // If no rows were updated, insert new record
            if ($updated === 0) {
                $updateData['rider_id'] = $riderId;
                $fields = array_keys($updateData);
                $placeholders = str_repeat('?,', count($fields) - 1) . '?';
                
                $sql = "INSERT INTO rider_vehicles (" . implode(', ', $fields) . ", created_at, updated_at) 
                        VALUES ({$placeholders}, NOW(), NOW())";
                
                $this->execute($sql, array_values($updateData));
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error updating vehicle info: " . $e->getMessage());
            return false;
        }
    }

    // ============================================================================
    // MESSAGING METHODS
    // ============================================================================

    /**
     * Display rider messages dashboard
     */
    public function messages(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        
        // Get conversations for this rider
        $conversations = $this->messageModel->getConversationsForUser($user->id, 'rider');
        
        // Get message statistics
        $stats = $this->messageModel->getMessageStats($user->id);

        $this->render('rider/messages', [
            'title' => 'Messages - Time2Eat',
            'user' => $user,
            'userRole' => 'rider',
            'conversations' => $conversations,
            'stats' => $stats,
            'currentPage' => 'messages'
        ]);
    }

    /**
     * Get conversation details and messages
     */
    public function getConversation(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $conversationId = $_GET['id'] ?? '';
        if (!$conversationId) {
            $this->json(['success' => false, 'message' => 'Invalid conversation ID'], 400);
            return;
        }

        $user = $this->getCurrentUser();
        
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        
        if (!$conversation) {
            $this->json(['success' => false, 'message' => 'Conversation not found'], 404);
            return;
        }

        $this->json(['success' => true, 'conversation' => $conversation]);
    }

    /**
     * Send a message in an existing conversation
     */
    public function sendMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $conversationId = $_POST['conversation_id'] ?? '';
        $message = trim($_POST['message'] ?? '');

        if (!$conversationId || !$message) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Get conversation to find recipient
        $conversation = $this->messageModel->getConversationMessages($conversationId, $user->id);
        if (!$conversation) {
            $this->json(['success' => false, 'message' => 'Conversation not found'], 404);
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
            $this->json(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get rider's active deliveries for message composition
     */
    public function getRiderDeliveries(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();
        
        // Get active deliveries for this rider
        $deliveries = $this->getRiderActiveDeliveries($user->id);

        $this->json(['success' => true, 'deliveries' => $deliveries]);
    }

    /**
     * Compose a new message to a customer
     */
    public function composeMessage(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $deliveryId = (int)($_POST['delivery_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$deliveryId || !$subject || !$message) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }

        $user = $this->getCurrentUser();

        // Get delivery details and verify rider ownership
        $delivery = $this->getDeliveryDetails($deliveryId, $user->id);
        if (!$delivery) {
            $this->json(['success' => false, 'message' => 'Delivery not found or not assigned to you'], 404);
            return;
        }

        // Create new conversation with the customer
        $conversationId = $this->messageModel->createConversation(
            $user->id,           // sender (rider)
            $delivery['customer_id'], // recipient (customer)
            $message,
            $delivery['order_id'], // link to order
            $subject
        );

        if ($conversationId) {
            $this->json(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversationId]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get active deliveries for the rider
     */
    private function getRiderActiveDeliveries(int $riderId): array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.customer_id,
                    o.restaurant_id,
                    o.status,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.phone as customer_phone,
                    r.name as restaurant_name,
                    o.created_at,
                    o.delivery_address
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.rider_id = ? 
                AND o.status IN ('confirmed', 'preparing', 'ready', 'picked_up', 'on_the_way')
                ORDER BY o.created_at DESC
                LIMIT 20
            ";

            return $this->query($sql, [$riderId]);

        } catch (\Exception $e) {
            error_log("Error getting rider deliveries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get delivery details and verify rider ownership
     */
    private function getDeliveryDetails(int $deliveryId, int $riderId): ?array
    {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.customer_id,
                    o.restaurant_id,
                    o.id as order_id,
                    o.status,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.phone as customer_phone
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE o.id = ? AND o.rider_id = ?
                LIMIT 1
            ";

            $result = $this->query($sql, [$deliveryId, $riderId]);
            return $result[0] ?? null;

        } catch (\Exception $e) {
            error_log("Error getting delivery details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Rider notifications page
     */
    public function notifications(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();

        try {
            // Get notifications for rider
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
                'delivery_updates' => 0,
                'system_alerts' => 0
            ];

            foreach ($notifications as $notification) {
                if (!$notification['read_at']) {
                    $stats['unread']++;
                    if ($notification['priority'] === 'urgent') {
                        $stats['urgent_unread']++;
                    }
                }

                if ($notification['type'] === 'delivery_update') {
                    $stats['delivery_updates']++;
                } elseif ($notification['type'] === 'system_alert') {
                    $stats['system_alerts']++;
                }
            }

            $this->render('rider/notifications', [
                'title' => 'Notifications - Time2Eat',
                'user' => $user,
                'userRole' => 'rider',
                'notifications' => $notifications,
                'stats' => $stats,
                'currentPage' => 'notifications'
            ]);

        } catch (\Exception $e) {
            error_log("Error loading rider notifications: " . $e->getMessage());
            $this->render('rider/notifications', [
                'title' => 'Notifications - Time2Eat',
                'user' => $user,
                'userRole' => 'rider',
                'notifications' => [],
                'stats' => ['total' => 0, 'unread' => 0, 'urgent_unread' => 0, 'delivery_updates' => 0, 'system_alerts' => 0],
                'error' => 'Failed to load notifications',
                'currentPage' => 'notifications'
            ]);
        }
    }

    /**
     * Report issue page
     */
    public function reportIssue(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        $user = $this->getCurrentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleIssueReport($user);
            return;
        }

        $this->render('rider/report-issue', [
            'title' => 'Report Issue - Time2Eat',
            'user' => $user,
            'currentPage' => 'reports'
        ]);
    }

    /**
     * Handle issue report submission
     */
    private function handleIssueReport($user): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $this->json(['success' => false, 'message' => 'Invalid data received'], 400);
                return;
            }

            // Validate required fields
            if (empty($data['issue_type']) || empty($data['description'])) {
                $this->json(['success' => false, 'message' => 'Issue type and description are required'], 400);
                return;
            }

            // Prepare issue data
            $issueData = [
                'user_id' => $user->id,
                'user_type' => 'rider',
                'issue_type' => trim(strip_tags($data['issue_type'])),
                'description' => trim(strip_tags($data['description'])),
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Add optional fields
            if (!empty($data['order_id'])) {
                $issueData['order_id'] = (int)$data['order_id'];
            }
            if (!empty($data['delivery_id'])) {
                $issueData['delivery_id'] = (int)$data['delivery_id'];
            }

            // Insert issue report
            $issueId = $this->insertRecord('issue_reports', $issueData);
            
            if ($issueId) {
                // Log the issue report
                error_log("Issue report submitted by rider {$user->id}: {$issueData['issue_type']}");
                
                $this->json([
                    'success' => true, 
                    'message' => 'Issue report submitted successfully. We will review it shortly.',
                    'issue_id' => $issueId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to submit issue report'], 500);
            }

        } catch (Exception $e) {
            error_log("Error submitting issue report: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while submitting the report'], 500);
        }
    }

}
