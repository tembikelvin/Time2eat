<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../services/DeliveryFeeService.php';
require_once __DIR__ . '/../services/TranzakPaymentService.php';

use core\BaseController;
use Time2Eat\Models\Cart;
use models\Order;
use models\User;
use models\Restaurant;
use services\DeliveryFeeService;
use Time2Eat\Services\TranzakPaymentService;

class CheckoutController extends BaseController
{
    private Cart $cartModel;
    private Order $orderModel;
    private User $userModel;
    private Restaurant $restaurantModel;
    private DeliveryFeeService $deliveryFeeService;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->deliveryFeeService = new DeliveryFeeService();
    }

    public function index(): void
    {
        // CRITICAL: Prevent caching of checkout page (user-specific, must be real-time)
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireAuth();

        // Only customers can access checkout
        if (!$this->isCustomer()) {
            $user = $this->getCurrentUser();
            $userRole = $user ? $user->role : 'not logged in';
            
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'authorization_error',
                    'message' => 'Only customers can place orders',
                    'errors' => ['role' => "Your account role is '{$userRole}'. Only customers can place orders."]
                ], 403);
                return;
            } else {
                $this->flash('error', 'Only customers can access checkout');
                $this->redirect(url('/'));
                return;
            }
        }

        $user = $this->user;
        $userId = $user->id;

        // Validate cart before checkout
        $cartValidation = $this->cartModel->validateCartForCheckout($userId);

        if (!$cartValidation['valid']) {
            // Log the validation errors for debugging
            error_log("Checkout validation failed for user {$userId}: " . json_encode($cartValidation['errors']));
            
            // Store errors in session for display
            $_SESSION['checkout_errors'] = $cartValidation['errors'];
            
            // In production, show specific error message instead of silent redirect
            if (defined('APP_ENV') && APP_ENV === 'production') {
                $errorMessage = "Unable to proceed to checkout: " . implode(', ', $cartValidation['errors']);
                $this->flash('error', $errorMessage);
            } else {
                $this->flash('error', 'Cart validation failed. Please check your cart items.');
            }
            
            $this->redirect(url('/browse'));
            return;
        }

        $cartItems = $this->cartModel->getCartByUser($userId);
        $cartTotals = $this->cartModel->getCartTotals($userId);

        if (empty($cartItems)) {
            $this->flash('error', 'Your cart is empty');
            $this->redirect(url('/browse'));
            return;
        }

        // Check if user has cash on delivery enabled
        $cashOnDeliveryEnabled = (bool)($user->cash_on_delivery_enabled ?? true);

        $this->render('checkout/index', [
            'title' => 'Checkout - Time2Eat',
            'user' => $user,
            'cartItems' => $cartItems,
            'cartTotals' => $cartTotals,
            'cashOnDeliveryEnabled' => $cashOnDeliveryEnabled
        ]);
    }

    public function placeOrder(): void
    {
        // CRITICAL: Suppress all output and ensure JSON response
        // Turn off all error display to prevent HTML output
        $oldDisplayErrors = ini_get('display_errors');
        $oldErrorReporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        ini_set('display_errors', '0');
        
        // Wrap everything in try-catch to prevent 500 errors
        try {
            // CRITICAL: Clear output buffer FIRST before any headers or output
            // This must happen before requireAuth() which may trigger output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Start fresh output buffering for JSON response
            ob_start();

            // CRITICAL: Check if headers already sent before setting new ones
            if (!headers_sent()) {
                // CRITICAL: Prevent caching of order placement (user-specific, must be real-time)
                header('Cache-Control: no-cache, no-store, must-revalidate, private');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('Content-Type: application/json; charset=utf-8');
            }

            $this->requireAuth();

            // Only customers can place orders
            if (!$this->isCustomer()) {
                $user = $this->getCurrentUser();
                $userRole = $user ? $user->role : 'not logged in';

                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'authorization_error',
                    'message' => 'Only customers can place orders',
                    'errors' => ['role' => "Your account role is '{$userRole}'. Only customers can place orders."]
                ], 403);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
                return;
            }

            $user = $this->user;
            $userId = $user->id;

            // DEBUG: Log incoming request data
            error_log('=== PLACE ORDER REQUEST ===');
            error_log('User ID: ' . $userId);
            error_log('POST data: ' . json_encode($_POST));
            error_log('Location type: ' . ($_POST['location_type'] ?? 'NOT SET'));
            error_log('Payment method: ' . ($_POST['payment_method'] ?? 'NOT SET'));

            // Get form data
            $locationType = $_POST['location_type'] ?? 'gps';
            $paymentMethod = $_POST['payment_method'] ?? 'cash_on_delivery';
            $deliveryInstructions = $_POST['delivery_instructions'] ?? '';

            // Validate payment method
            if ($paymentMethod === 'cash_on_delivery') {
                $cashOnDeliveryEnabled = (bool)($user->cash_on_delivery_enabled ?? true);
                if (!$cashOnDeliveryEnabled) {
                    $this->jsonResponse([
                        'success' => false,
                        'error_type' => 'payment_error',
                        'message' => 'Cash on delivery is not available for your account',
                        'errors' => ['payment_method' => 'Cash on delivery is not enabled for your account. Please choose a different payment method.']
                    ], 400);
                    return;
                }
            } elseif ($paymentMethod === 'tranzak') {
                // Validate Tranzak payment requirements - use user's phone from database
                if (empty($user->phone)) {
                    $this->jsonResponse([
                        'success' => false,
                        'error_type' => 'validation_error',
                        'message' => 'Phone number is required for Tranzak payment',
                        'errors' => ['phone' => 'Please add a phone number to your profile to use Tranzak payment.']
                    ], 400);
                    return;
                }
            }

            // Build delivery address based on location type
            $deliveryAddress = [];

            if ($locationType === 'gps') {
                $latitude = $_POST['latitude'] ?? null;
                $longitude = $_POST['longitude'] ?? null;

                error_log('GPS Location received - Latitude: ' . var_export($latitude, true) . ', Longitude: ' . var_export($longitude, true));

                if (!$latitude || !$longitude || $latitude === '' || $longitude === '') {
                    error_log('GPS Location validation FAILED - Missing or empty coordinates');
                    $this->jsonResponse([
                        'success' => false,
                        'error_type' => 'location_error',
                        'message' => 'Please select your delivery location',
                        'errors' => [
                            'location' => 'Please click on the map or use the GPS button to select your delivery location.',
                            'debug' => 'Latitude: ' . var_export($latitude, true) . ', Longitude: ' . var_export($longitude, true)
                        ]
                    ], 400);
                    return;
                }

                $deliveryAddress = [
                    'type' => 'gps',
                    'latitude' => (float)$latitude,
                    'longitude' => (float)$longitude,
                    'instructions' => $deliveryInstructions
                ];

                error_log('GPS delivery address created: ' . json_encode($deliveryAddress));
            } else {
                $streetAddress = $_POST['street_address'] ?? '';
                $neighborhood = $_POST['neighborhood'] ?? '';
                $landmark = $_POST['landmark'] ?? '';
                $textLatitude = $_POST['text_latitude'] ?? null;
                $textLongitude = $_POST['text_longitude'] ?? null;

                error_log('Text Address received - Street: ' . $streetAddress . ', Neighborhood: ' . $neighborhood . ', Landmark: ' . $landmark);
                error_log('Text Address coordinates - Lat: ' . var_export($textLatitude, true) . ', Lon: ' . var_export($textLongitude, true));

                if (empty($streetAddress) || trim($streetAddress) === '') {
                    error_log('Text Address validation FAILED - Missing street address');
                    $this->jsonResponse([
                        'success' => false,
                        'error_type' => 'validation_error',
                        'message' => 'Please provide your street address',
                        'errors' => ['street_address' => 'Street address is required for delivery.']
                    ], 400);
                    return;
                }

                // Validate coordinates are provided (required for delivery fee calculation)
                if (!$textLatitude || !$textLongitude || $textLatitude === '' || $textLongitude === '') {
                    error_log('Text Address validation FAILED - Missing coordinates');
                    $this->jsonResponse([
                        'success' => false,
                        'error_type' => 'location_error',
                        'message' => 'Please select your location on the map',
                        'errors' => [
                            'location' => 'Please click on the map or drag the marker to set your exact delivery location. This is required for accurate delivery fee calculation.',
                            'debug' => 'Latitude: ' . var_export($textLatitude, true) . ', Longitude: ' . var_export($textLongitude, true)
                        ]
                    ], 400);
                    return;
                }

                $deliveryAddress = [
                    'type' => 'text',
                    'street_address' => $streetAddress,
                    'neighborhood' => $neighborhood,
                    'landmark' => $landmark,
                    'latitude' => (float)$textLatitude,
                    'longitude' => (float)$textLongitude,
                    'instructions' => $deliveryInstructions
                ];

                error_log('Text delivery address created: ' . json_encode($deliveryAddress));
            }

            // Validate cart
            error_log('Validating cart for user: ' . $userId);
            $cartValidation = $this->cartModel->validateCartForCheckout($userId);
            error_log('Cart validation result: ' . json_encode($cartValidation));

            if (!$cartValidation['valid']) {
                error_log('Cart validation FAILED: ' . json_encode($cartValidation['errors']));
                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'message' => 'There are issues with your cart that need to be resolved',
                    'errors' => $cartValidation['errors']
                ], 400);
                return;
            }

            // Get cart items and totals
            $cartItems = $this->cartModel->getCartByUser($userId);
            $cartTotals = $this->cartModel->getCartTotals($userId);

            if (empty($cartItems)) {
                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'cart_error',
                    'message' => 'Your cart is empty. Please add items before placing an order.'
                ], 400);
                return;
            }

            // For Tranzak payment: Initiate payment first, create order only after payment confirmation
            if ($paymentMethod === 'tranzak') {
                $this->initiateTranzakPaymentBeforeOrder($cartItems, $cartValidation, $deliveryAddress, $deliveryInstructions, $user);
                return; // Exit early - initiateTranzakPaymentBeforeOrder handles the response
            }

            // For Cash on Delivery: Create order immediately
            // Start database transaction for order creation
            try {
            $this->getDb()->beginTransaction();

            // Process each restaurant's order separately
            $orderIds = [];
            $restaurantGroups = $cartValidation['restaurant_groups'];

            foreach ($restaurantGroups as $restaurantId => $restaurantData) {
                $restaurantItems = array_filter($cartItems, function($item) use ($restaurantId) {
                    return $item['restaurant_id'] == $restaurantId;
                });

                if (empty($restaurantItems)) {
                    continue;
                }

                // Get restaurant details with delivery settings
                // Smart delivery fee system: Fixed base fee within radius + distance-based beyond radius
                // delivery_fee_per_extra_km: Extra XAF per km beyond delivery_radius
                // If set to 0: Fixed fee only (simplified model)
                // If > 0: Mixed model (fixed within radius, distance-based beyond)
                try {
                    $restaurantStmt = $this->getDb()->prepare("
                        SELECT id, name, latitude, longitude, delivery_radius, delivery_fee, delivery_fee_per_extra_km
                        FROM restaurants WHERE id = ?
                    ");
                    $restaurantStmt->execute([$restaurantId]);
                    $restaurant = $restaurantStmt->fetch(\PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                    // If column doesn't exist, query without it and calculate smart default
                    if (strpos($e->getMessage(), 'delivery_fee_per_extra_km') !== false) {
                        $restaurantStmt = $this->getDb()->prepare("
                            SELECT id, name, latitude, longitude, delivery_radius, delivery_fee
                            FROM restaurants WHERE id = ?
                        ");
                        $restaurantStmt->execute([$restaurantId]);
                        $restaurant = $restaurantStmt->fetch(\PDO::FETCH_ASSOC);
                        
                        if ($restaurant) {
                            // Smart default: Based on existing delivery settings
                            // Large radius (>=15km) or high base fee (>=1000) = Fixed fee (0)
                            // Otherwise = Distance-based (100 XAF/km)
                            $radius = (float)($restaurant['delivery_radius'] ?? 10);
                            $baseFee = (float)($restaurant['delivery_fee'] ?? 500);
                            
                            if ($radius >= 15.0 || $baseFee >= 1000.0) {
                                $restaurant['delivery_fee_per_extra_km'] = 0.00; // Fixed fee system
                            } else {
                                $restaurant['delivery_fee_per_extra_km'] = 100.00; // Distance-based system
                            }
                        }
                    } else {
                        // Re-throw if it's a different error
                        throw $e;
                    }
                }

                if (!$restaurant || !$restaurant['latitude'] || !$restaurant['longitude']) {
                    throw new \Exception("Restaurant location not available for delivery calculation");
                }

                // Calculate totals for this restaurant
                $subtotal = array_sum(array_column($restaurantItems, 'total_price'));
                $serviceFee = $subtotal * 0.025; // 2.5% service fee

                // Calculate delivery fee based on distance using restaurant-specific settings
                $deliveryFee = (float)($restaurant['delivery_fee'] ?? 500); // Default base fee
                $distance = 0;

                // Use coordinates from either GPS or text address type
                if (isset($deliveryAddress['latitude']) && isset($deliveryAddress['longitude'])) {
                    $customerLat = (float)$deliveryAddress['latitude'];
                    $customerLon = (float)$deliveryAddress['longitude'];

                    // Check delivery availability
                    $availability = $this->deliveryFeeService->checkDeliveryAvailability(
                        $restaurant,
                        $customerLat,
                        $customerLon
                    );

                    if (!$availability['available']) {
                        throw new \Exception("Delivery not available: " . $availability['reason'] . " for " . $restaurant['name']);
                    }

                    $distance = $availability['distance'];

                    // Calculate delivery fee using restaurant-specific settings
                    $feeCalculation = $this->deliveryFeeService->calculateDeliveryFee(
                        $distance,
                        $restaurant,
                        $subtotal
                    );

                    $deliveryFee = $feeCalculation['total_fee'];

                    // Round to nearest 50 XAF for cleaner pricing
                    $deliveryFee = ceil($deliveryFee / 50) * 50;
                }

                $totalAmount = $subtotal + $serviceFee + $deliveryFee;

                // Generate unique order number
                $orderNumber = 'ORD-' . strtoupper(uniqid());

                // Create order
                $orderData = [
                    'order_number' => $orderNumber,
                    'customer_id' => $userId,
                    'restaurant_id' => $restaurantId,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'tax_amount' => 0, // Tax removed - set to 0 for database compatibility
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'delivery_address' => json_encode($deliveryAddress),
                    'delivery_instructions' => $deliveryInstructions,
                    'currency' => 'XAF'
                ];

                $orderId = $this->orderModel->createOrder($orderData);

                if (!$orderId) {
                    throw new \Exception('Failed to create order');
                }

                $orderIds[] = $orderId;

                // Process affiliate commission if customer was referred
                $this->processAffiliateCommissionForOrder($orderId, $userId);

                // Move cart items to order items for this restaurant
                foreach ($restaurantItems as $item) {
                    $sql = "
                        INSERT INTO order_items
                        (order_id, menu_item_id, quantity, unit_price, total_price, variants, special_instructions, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ";

                    $stmt = $this->getDb()->prepare($sql);
                    $stmt->execute([
                        $orderId,
                        $item['menu_item_id'],
                        $item['quantity'],
                        $item['unit_price'],
                        $item['total_price'],
                        $item['customizations'] ?? null,
                        $item['special_instructions'] ?? ''
                    ]);
                }
            }

            // Clear cart
            $this->cartModel->clearCart($userId);

            $this->getDb()->commit();

            // Send order confirmation notifications (optional - can be implemented later)
            // foreach ($orderIds as $orderId) {
            //     $this->sendOrderNotifications($orderId);
            // }

            $response = [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_ids' => $orderIds
            ];

            // Add payment URL if Tranzak payment was initiated
            if ($paymentMethod === 'tranzak' && isset($_SESSION['tranzak_payment_url'])) {
                $response['payment_url'] = $_SESSION['tranzak_payment_url'];
                unset($_SESSION['tranzak_payment_url']);
            } elseif ($paymentMethod === 'tranzak') {
                // Tranzak payment failed, but order was created
                // Customer can complete payment later
                $response['warning'] = 'Order created successfully. Payment processing encountered an issue. Please try again or contact support.';
                $response['payment_status'] = 'pending';
            }

            $this->jsonResponse($response);
        } catch (\Exception $e) { // End try block for database transaction
            // Restore error reporting settings
            if (isset($oldErrorReporting)) {
                error_reporting($oldErrorReporting);
            }
            if (isset($oldDisplayErrors)) {
                ini_set('display_errors', $oldDisplayErrors);
            }
            
            // Only rollback if transaction was started
            try {
                if ($this->getDb()->inTransaction()) {
                    $this->getDb()->rollback();
                }
            } catch (\Exception $rollbackError) {
                // Ignore rollback errors
                error_log('Rollback error: ' . $rollbackError->getMessage());
            }
            
            error_log('Order placement failed: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());

            // CRITICAL: Clean output buffer completely before JSON response
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Start fresh output buffer for JSON response
            ob_start();
            
            // Ensure headers are set
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }

            // Determine error type based on exception
            $errorType = 'server_error';
            $userMessage = 'An unexpected error occurred while placing your order.';
            $statusCode = 500;

            if (strpos($e->getMessage(), 'Delivery not available') !== false) {
                $errorType = 'delivery_error';
                $userMessage = $e->getMessage(); // Use the actual error message
                $statusCode = 400;
            } elseif (strpos($e->getMessage(), 'payment') !== false) {
                $errorType = 'payment_error';
                $userMessage = 'There was an issue processing your payment. Please try again or use a different payment method.';
                $statusCode = 400;
            } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'SQL') !== false) {
                $errorType = 'server_error';
                $userMessage = 'A database error occurred. Please try again in a few minutes.';
                $statusCode = 500;
            } elseif (strpos($e->getMessage(), 'network') !== false || strpos($e->getMessage(), 'timeout') !== false) {
                $errorType = 'network_error';
                $userMessage = 'A network error occurred. Please check your connection and try again.';
                $statusCode = 500;
            }

            $errorResponse = [
                'success' => false,
                'error_type' => $errorType,
                'message' => $userMessage
            ];
            
            // Only include debug details in development
            if (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) {
                $errorResponse['details'] = [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            
            $this->jsonResponse($errorResponse, $statusCode);
        } // End catch (\Exception $e) for database transaction

        } catch (\Throwable $e) { // Catch for main placeOrder try
            // Restore error reporting settings
            if (isset($oldErrorReporting)) {
                error_reporting($oldErrorReporting);
            }
            if (isset($oldDisplayErrors)) {
                ini_set('display_errors', $oldDisplayErrors);
            }
            
            // Catch any other errors (like fatal errors)
            error_log('Fatal error in placeOrder: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            ob_start();
            
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            $errorResponse = [
                'success' => false,
                'error_type' => 'server_error',
                'message' => 'An unexpected error occurred while placing your order. Please try again.',
                'errors' => []
            ];
            
            // Only include debug details in development
            if (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) {
                $errorResponse['debug'] = [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit; // Use exit instead of ob_end_flush to ensure clean termination
        }
    } // End placeOrder()

    public function validateAffiliateCode(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $validation = $this->validateRequest([
            'affiliate_code' => 'required|string|max:20'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->user;
        $userId = $user->id;
        $affiliateCode = strtoupper(trim($validation['data']['affiliate_code']));

        // Check if user is trying to use their own affiliate code
        if (isset($user->affiliate_code) && $user->affiliate_code === $affiliateCode) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'You cannot use your own affiliate code'
            ], 400);
            return;
        }

        $affiliate = $this->userModel->findByAffiliateCode($affiliateCode);

        if ($affiliate) {
            $cartTotals = $this->cartModel->getCartTotals($userId);
            $commission = $this->orderModel->calculateAffiliateCommission(
                $cartTotals['subtotal'], $affiliate['affiliate_rate']
            );

        $this->jsonResponse([
            'success' => true,
                'message' => 'Valid affiliate code',
                'affiliate' => [
                    'code' => $affiliateCode,
                    'name' => $affiliate['first_name'] . ' ' . $affiliate['last_name'],
                    'commission_rate' => $affiliate['affiliate_rate'],
                    'estimated_commission' => $commission
                ]
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid affiliate code'
            ], 400);
        }
    }

    private function sendOrderNotifications(int $orderId): void
    {
        $order = $this->orderModel->getOrderDetails($orderId);
        
        if (!$order) {
            return;
        }

        // Send notification to customer
        $this->sendCustomerOrderNotification($order);
        
        // Send notification to restaurant
        $this->sendRestaurantOrderNotification($order);
        
        // Process affiliate commission if applicable
        if ($order['affiliate_code']) {
            $this->orderModel->processAffiliateCommission($orderId);
        }
    }

    private function sendCustomerOrderNotification(array $order): void
    {
        // Implementation for customer notification
        // This could be email, SMS, push notification, etc.
        
        $message = "Your order #{$order['order_number']} has been placed successfully. " .
                  "Total: {$order['total_amount']} XAF. " .
                  "We'll notify you when it's confirmed.";
        
        // Send notification logic here
        // Example: $this->notificationService->send($order['customer_id'], $message);
    }

    private function sendRestaurantOrderNotification(array $order): void
    {
        // Implementation for restaurant notification
        // This could be email, SMS, dashboard notification, etc.
        
        $message = "New order #{$order['order_number']} received. " .
                  "Total: {$order['total_amount']} XAF. " .
                  "Please confirm the order.";
        
        // Send notification logic here
        // Example: $this->notificationService->sendToRestaurant($order['restaurant_id'], $message);
    }

    /**
     * Initiate Tranzak payment before creating order
     * Order will only be created after payment is confirmed via webhook
     */
    private function initiateTranzakPaymentBeforeOrder(array $cartItems, array $cartValidation, array $deliveryAddress, string $deliveryInstructions, $user): void
    {
        // Suppress any warnings/notices that might output before JSON
        $oldErrorReporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        
        try {
            $userId = $user->id;
            
            // Initialize payment service with error handling
            try {
                $paymentService = new TranzakPaymentService();
            } catch (\Exception $e) {
                error_reporting($oldErrorReporting);
                error_log('Failed to initialize TranzakPaymentService: ' . $e->getMessage());
                
                // Clean output buffer
                while (ob_get_level()) {
                    ob_end_clean();
                }
                ob_start();
                
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'payment_error',
                    'message' => 'Payment service initialization failed. Please contact support.',
                    'errors' => ['payment' => 'Service unavailable']
                ], 500);
                return;
            }
            
            // Calculate order totals for all restaurants
            $restaurantGroups = $cartValidation['restaurant_groups'];
            $totalAmount = 0;
            $draftOrders = [];
            
            foreach ($restaurantGroups as $restaurantId => $restaurantData) {
                $restaurantItems = array_filter($cartItems, function($item) use ($restaurantId) {
                    return $item['restaurant_id'] == $restaurantId;
                });

                if (empty($restaurantItems)) {
                    continue;
                }

                // Get restaurant details
                try {
                    $restaurantStmt = $this->getDb()->prepare("
                        SELECT id, name, latitude, longitude, delivery_radius, delivery_fee, delivery_fee_per_extra_km
                        FROM restaurants WHERE id = ?
                    ");
                    $restaurantStmt->execute([$restaurantId]);
                    $restaurant = $restaurantStmt->fetch(\PDO::FETCH_ASSOC);
                } catch (\PDOException $e) {
                    if (strpos($e->getMessage(), 'delivery_fee_per_extra_km') !== false) {
                        $restaurantStmt = $this->getDb()->prepare("
                            SELECT id, name, latitude, longitude, delivery_radius, delivery_fee
                            FROM restaurants WHERE id = ?
                        ");
                        $restaurantStmt->execute([$restaurantId]);
                        $restaurant = $restaurantStmt->fetch(\PDO::FETCH_ASSOC);
                        
                        if ($restaurant) {
                            $radius = (float)($restaurant['delivery_radius'] ?? 10);
                            $baseFee = (float)($restaurant['delivery_fee'] ?? 500);
                            $restaurant['delivery_fee_per_extra_km'] = ($radius >= 15.0 || $baseFee >= 1000.0) ? 0.00 : 100.00;
                        }
                    } else {
                        throw $e;
                    }
                }

                if (!$restaurant || !$restaurant['latitude'] || !$restaurant['longitude']) {
                    throw new \Exception("Restaurant location not available for delivery calculation");
                }

                // Calculate totals
                $subtotal = array_sum(array_column($restaurantItems, 'total_price'));
                $serviceFee = $subtotal * 0.025;
                $deliveryFee = (float)($restaurant['delivery_fee'] ?? 500);

                if (isset($deliveryAddress['latitude']) && isset($deliveryAddress['longitude'])) {
                    $customerLat = (float)$deliveryAddress['latitude'];
                    $customerLon = (float)$deliveryAddress['longitude'];

                    $availability = $this->deliveryFeeService->checkDeliveryAvailability(
                        $restaurant,
                        $customerLat,
                        $customerLon
                    );

                    if (!$availability['available']) {
                        throw new \Exception("Delivery not available: " . $availability['reason'] . " for " . $restaurant['name']);
                    }

                    $feeCalculation = $this->deliveryFeeService->calculateDeliveryFee(
                        $availability['distance'],
                        $restaurant,
                        $subtotal
                    );

                    $deliveryFee = $feeCalculation['total_fee'];
                    $deliveryFee = ceil($deliveryFee / 50) * 50;
                }

                $orderTotal = $subtotal + $serviceFee + $deliveryFee;
                $totalAmount += $orderTotal;

                // Store draft order data
                $orderNumber = 'ORD-' . strtoupper(uniqid());
                $draftOrders[] = [
                    'order_number' => $orderNumber,
                    'restaurant_id' => $restaurantId,
                    'restaurant_name' => $restaurant['name'],
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'delivery_fee' => $deliveryFee,
                    'total_amount' => $orderTotal,
                    'items' => $restaurantItems
                ];
            }

            if ($totalAmount <= 0) {
                throw new \Exception('Invalid order amount');
            }

            // Generate temporary reference for payment
            $tempOrderRef = 'TEMP-' . strtoupper(uniqid()) . '-' . time();

            // Store draft order data in session (will be used to create order after payment confirmation)
            $draftOrderData = [
                'user_id' => $userId,
                'temp_reference' => $tempOrderRef,
                'delivery_address' => $deliveryAddress,
                'delivery_instructions' => $deliveryInstructions,
                'total_amount' => $totalAmount,
                'orders' => $draftOrders,
                'cart_items' => $cartItems,
                'created_at' => time()
            ];
            
            $_SESSION['tranzak_draft_orders'] = $draftOrderData;

            // Also store in database for webhook access (create table if doesn't exist)
            try {
                // Check if table exists, create if not
                $tableCheck = $this->getDb()->query("SHOW TABLES LIKE 'draft_orders'");
                if (!$tableCheck->fetch()) {
                    // Create draft_orders table
                    $createTableSql = "CREATE TABLE IF NOT EXISTS draft_orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        temp_reference VARCHAR(100) UNIQUE NOT NULL,
                        user_id INT NOT NULL,
                        order_data TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        expires_at TIMESTAMP DEFAULT (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)),
                        INDEX idx_temp_ref (temp_reference),
                        INDEX idx_user_id (user_id),
                        INDEX idx_expires_at (expires_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                    $this->getDb()->exec($createTableSql);
                    error_log("Created draft_orders table");
                }

                // Store draft order in database
                $stmt = $this->getDb()->prepare("
                    INSERT INTO draft_orders (temp_reference, user_id, order_data, created_at, expires_at)
                    VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
                    ON DUPLICATE KEY UPDATE order_data = VALUES(order_data), expires_at = VALUES(expires_at)
                ");
                $stmt->execute([
                    $tempOrderRef,
                    $userId,
                    json_encode($draftOrderData)
                ]);
                error_log("Stored draft order in database for temp ref: {$tempOrderRef}");
            } catch (\Exception $e) {
                error_log("Warning: Could not store draft order in database: " . $e->getMessage() . ". Using session only.");
                // Continue with session storage only
            }

            // Prepare payment data
            $paymentData = [
                'amount' => (int)$totalAmount,
                'currency' => 'XAF',
                'order_id' => $tempOrderRef, // Use temp reference instead of order ID
                'customer_phone' => $user->phone,
                'customer_email' => $user->email ?? '',
                'customer_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'description' => 'Time2Eat Order - ' . count($draftOrders) . ' restaurant(s)',
                'return_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/payment/tranzak-return?ref=' . urlencode($tempOrderRef),
                'notify_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/api/payment/tranzak/notify'
            ];

            error_log('Initiating Tranzak payment before order creation. Temp Ref: ' . $tempOrderRef . ', Amount: ' . $paymentData['amount']);

            // Initiate payment
            $result = $paymentService->initiatePayment($paymentData);

            if (!$result['success']) {
                // Clear draft orders on payment failure
                unset($_SESSION['tranzak_draft_orders']);
                
                // Get detailed error message
                $errorMsg = $result['message'] ?? 'Unknown error';
                $errorCode = $result['error_code'] ?? null;
                $httpCode = $result['http_code'] ?? null;
                
                error_log("Tranzak payment initiation failed: {$errorMsg} (Error Code: {$errorCode}, HTTP: {$httpCode})");
                
                // Return error as JSON instead of throwing exception
                // CRITICAL: Clean output buffer
                while (ob_get_level()) {
                    ob_end_clean();
                }
                ob_start();
                
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                $this->jsonResponse([
                    'success' => false,
                    'error_type' => 'payment_error',
                    'message' => $errorMsg,
                    'error_code' => $errorCode,
                    'http_code' => $httpCode,
                    'errors' => ['payment' => $errorMsg]
                ], 400);
                return; // Exit early
            }

            // Store payment info in session
            $_SESSION['tranzak_payment_info'] = [
                'temp_reference' => $tempOrderRef,
                'payment_id' => $result['payment_id'] ?? null,
                'transaction_id' => $result['payment_id'] ?? null
            ];

            // Restore error reporting
            error_reporting($oldErrorReporting);
            
            // Return payment URL to frontend
            $this->jsonResponse([
                'success' => true,
                'message' => 'Redirecting to payment...',
                'payment_url' => $result['payment_url'],
                'requires_payment' => true,
                'temp_reference' => $tempOrderRef
            ]);

        } catch (\Exception $e) {
            // Restore error reporting
            error_reporting($oldErrorReporting);
            error_log('Error initiating Tranzak payment before order: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clear any stored draft data
            unset($_SESSION['tranzak_draft_orders']);
            unset($_SESSION['tranzak_payment_info']);
            
            // CRITICAL: Clean output buffer completely before JSON response
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Start fresh output buffer for JSON response
            ob_start();
            
            // Ensure headers are set
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            $this->jsonResponse([
                'success' => false,
                'error_type' => 'payment_error',
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
                'errors' => ['payment' => $e->getMessage()]
            ], 400);
        } catch (\Throwable $e) {
            // Restore error reporting
            error_reporting($oldErrorReporting);
            
            // Catch any fatal errors
            error_log('Fatal error initiating Tranzak payment: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clear any stored draft data
            unset($_SESSION['tranzak_draft_orders']);
            unset($_SESSION['tranzak_payment_info']);
            
            // CRITICAL: Clean output buffer completely
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            ob_start();
            
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            // Use json_encode directly to ensure valid JSON
            $errorResponse = [
                'success' => false,
                'error_type' => 'server_error',
                'message' => 'An unexpected error occurred while initiating payment. Please try again.',
                'errors' => ['payment' => 'Payment initiation failed']
            ];
            
            echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } finally {
            // Always restore error reporting
            error_reporting($oldErrorReporting);
        }
    }

    /**
     * Process Tranzak payment for an order (legacy method - kept for compatibility)
     */
    private function processTranzakPayment(int $orderId, array $orderData, $user): void
    {
        try {
            // Validate required data
            if (empty($orderData['total_amount']) || $orderData['total_amount'] <= 0) {
                throw new \Exception('Invalid order amount for payment');
            }

            if (empty($user->phone)) {
                throw new \Exception('Phone number is required for Tranzak payment');
            }

            $paymentService = new TranzakPaymentService();

            // Prepare payment data
            $paymentData = [
                'amount' => (int)$orderData['total_amount'],
                'currency' => $orderData['currency'] ?? 'XAF',
                'order_id' => $orderId,
                'customer_phone' => $user->phone,
                'customer_email' => $user->email ?? '',
                'customer_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'description' => 'Order #' . $orderData['order_number'] . ' - Time2Eat',
                'return_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/payment/success?order_id=' . $orderId,
                'notify_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/api/payment/tranzak/notify'
            ];

            error_log('Initiating Tranzak payment for order: ' . $orderId . ' with amount: ' . $paymentData['amount']);

            // Initiate payment
            $result = $paymentService->initiatePayment($paymentData);

            if ($result['success']) {
                // Store payment URL in session for redirect
                $_SESSION['tranzak_payment_url'] = $result['payment_url'];
                $_SESSION['tranzak_order_id'] = $orderId;

                // Update order status to pending payment
                $this->getDb()->prepare("UPDATE orders SET payment_status = 'pending' WHERE id = ?")
                    ->execute([$orderId]);

                error_log('Tranzak payment initiated successfully for order: ' . $orderId);
            } else {
                error_log('Tranzak payment initiation failed: ' . $result['message']);
                throw new \Exception('Tranzak payment initiation failed: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            error_log('Tranzak payment processing failed for order ' . $orderId . ': ' . $e->getMessage());
            
            // Update order status to failed
            try {
                $this->getDb()->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")
                    ->execute([$orderId]);
            } catch (\Exception $updateError) {
                error_log('Failed to update order status: ' . $updateError->getMessage());
            }
            
            throw $e;
        }
    }

    /**
     * Process affiliate commission when order is placed
     * Adds commission to affiliate's pending earnings
     */
    private function processAffiliateCommissionForOrder(int $orderId, int $customerId): void
    {
        try {
            // Get customer's referrer
            $customerStmt = $this->getDb()->prepare("SELECT referred_by FROM users WHERE id = ?");
            $customerStmt->execute([$customerId]);
            $customer = $customerStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer || !$customer['referred_by']) {
                return; // Customer was not referred
            }

            // Get affiliate details by affiliate code
            $affiliateStmt = $this->getDb()->prepare("
                SELECT id, commission_rate, affiliate_code
                FROM affiliates
                WHERE affiliate_code = ? AND status = 'active'
            ");
            $affiliateStmt->execute([$customer['referred_by']]);
            $affiliate = $affiliateStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$affiliate) {
                return; // Referrer is not an active affiliate
            }

            // Get order details
            $orderStmt = $this->getDb()->prepare("SELECT subtotal FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order) {
                return;
            }

            // Calculate commission
            $commissionAmount = round($order['subtotal'] * ($affiliate['commission_rate'] / 100), 2);

            // Update order with commission and affiliate code
            $updateStmt = $this->getDb()->prepare("
                UPDATE orders
                SET affiliate_commission = ?, affiliate_code = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$commissionAmount, $affiliate['affiliate_code'], $orderId]);

            // Record earning in affiliate_earnings table
            $earningStmt = $this->getDb()->prepare("
                INSERT INTO affiliate_earnings (
                    affiliate_id, order_id, customer_id, amount, type, status, earned_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 'referral', 'confirmed', NOW(), NOW(), NOW())
            ");
            $earningStmt->execute([$affiliate['id'], $orderId, $customerId, $commissionAmount]);

            // Update affiliate balance
            $balanceStmt = $this->getDb()->prepare("
                UPDATE affiliates
                SET total_earnings = total_earnings + ?,
                    pending_earnings = pending_earnings + ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $balanceStmt->execute([$commissionAmount, $commissionAmount, $affiliate['id']]);

            // Update affiliate_referrals table with commission
            $referralStmt = $this->getDb()->prepare("
                UPDATE affiliate_referrals
                SET order_id = ?,
                    commission_amount = commission_amount + ?,
                    status = 'confirmed',
                    updated_at = NOW()
                WHERE affiliate_id = ? AND referred_user_id = ?
            ");
            $referralStmt->execute([$orderId, $commissionAmount, $affiliate['id'], $customerId]);

            error_log("Affiliate commission processed: Order #$orderId, Affiliate ID: {$affiliate['id']}, Commission: $commissionAmount XAF");

        } catch (\Exception $e) {
            error_log("Error processing affiliate commission for order $orderId: " . $e->getMessage());
            // Don't throw - this shouldn't fail the order placement
        }
    }
}
