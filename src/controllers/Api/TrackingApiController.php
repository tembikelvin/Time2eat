<?php

namespace controllers\Api;

use core\Controller;
use models\Order;
use models\Delivery;
use models\RiderLocation;
use models\User;

class TrackingApiController extends Controller
{
    private Order $orderModel;
    private Delivery $deliveryModel;
    private RiderLocation $locationModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->deliveryModel = new Delivery();
        $this->locationModel = new RiderLocation();
        $this->userModel = new User();
    }

    /**
     * Get real-time tracking data for an order
     * GET /api/tracking/order/{orderId}
     */
    public function getOrderTracking(int $orderId): void
    {
        header('Content-Type: application/json');

        try {
            // Get order details
            $order = $this->orderModel->getById($orderId);

            if (!$order) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
                return;
            }

            // Get delivery information
            $delivery = $this->deliveryModel->getByOrderId($orderId);

            $response = [
                'success' => true,
                'order' => [
                    'id' => $order['id'],
                    'order_number' => $order['order_number'],
                    'status' => $order['status'],
                    'created_at' => $order['created_at']
                ],
                'rider_location' => null,
                'rider' => null,
                'estimated_delivery_time' => null
            ];

            // If delivery exists and has a rider, get rider location
            if ($delivery && !empty($delivery['rider_id'])) {
                // Get latest rider location
                $riderLocation = $this->locationModel->getLatestLocation($delivery['rider_id']);

                if ($riderLocation) {
                    $response['rider_location'] = [
                        'latitude' => (float)$riderLocation['latitude'],
                        'longitude' => (float)$riderLocation['longitude'],
                        'accuracy' => $riderLocation['accuracy'] ?? null,
                        'speed' => $riderLocation['speed'] ?? null,
                        'heading' => $riderLocation['heading'] ?? null,
                        'updated_at' => $riderLocation['created_at']
                    ];
                }

                // Get rider information
                $rider = $this->userModel->getById($delivery['rider_id']);
                if ($rider) {
                    $response['rider'] = [
                        'id' => $rider['id'],
                        'name' => $rider['full_name'] ?? $rider['name'],
                        'phone' => $rider['phone'] ?? null,
                        'avatar' => $rider['avatar'] ?? null
                    ];
                }

                // Calculate estimated delivery time
                if ($delivery['estimated_delivery_time']) {
                    $response['estimated_delivery_time'] = $delivery['estimated_delivery_time'];
                }
            }

            $this->jsonResponse($response);

        } catch (\Exception $e) {
            error_log("Tracking API Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch tracking data'
            ], 500);
        }
    }

    /**
     * Update rider location (called by rider app)
     * POST /api/tracking/rider/location
     */
    public function updateRiderLocation(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
            return;
        }

        // Check authentication
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $input = $this->getJsonInput();

        // Validate input
        $latitude = (float)($input['latitude'] ?? 0);
        $longitude = (float)($input['longitude'] ?? 0);
        $accuracy = isset($input['accuracy']) ? (float)$input['accuracy'] : null;
        $speed = isset($input['speed']) ? (float)$input['speed'] : null;
        $heading = isset($input['heading']) ? (float)$input['heading'] : null;
        $batteryLevel = isset($input['battery_level']) ? (int)$input['battery_level'] : null;

        if (!$latitude || !$longitude) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Latitude and longitude are required'
            ], 400);
            return;
        }

        try {
            // Save location
            $locationData = [
                'rider_id' => $user['id'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'speed' => $speed,
                'heading' => $heading,
                'battery_level' => $batteryLevel,
                'is_online' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $locationId = $this->locationModel->createLocation($locationData);

            if (!$locationId) {
                throw new \Exception('Failed to save location');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Location updated successfully',
                'location_id' => $locationId
            ]);

        } catch (\Exception $e) {
            error_log("Rider location update error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }

    /**
     * Get customer location for rider
     * GET /api/tracking/customer/{orderId}
     */
    public function getCustomerLocation(int $orderId): void
    {
        header('Content-Type: application/json');

        // Check authentication
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
            return;
        }

        try {
            $order = $this->orderModel->getById($orderId);

            if (!$order) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
                return;
            }

            // Verify rider is assigned to this order
            $delivery = $this->deliveryModel->getByOrderId($orderId);
            $user = $this->getAuthenticatedUser();

            if (!$delivery || $delivery['rider_id'] !== $user['id']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
                return;
            }

            // Parse delivery address
            $deliveryAddress = json_decode($order['delivery_address'], true);

            $response = [
                'success' => true,
                'customer_location' => null,
                'delivery_address' => $deliveryAddress
            ];

            // If GPS coordinates are available
            if (isset($deliveryAddress['latitude']) && isset($deliveryAddress['longitude'])) {
                $response['customer_location'] = [
                    'latitude' => (float)$deliveryAddress['latitude'],
                    'longitude' => (float)$deliveryAddress['longitude']
                ];
            }

            $this->jsonResponse($response);

        } catch (\Exception $e) {
            error_log("Customer location API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch customer location'
            ], 500);
        }
    }

    /**
     * Get delivery route information
     * GET /api/tracking/route/{deliveryId}
     */
    public function getDeliveryRoute(int $deliveryId): void
    {
        header('Content-Type: application/json');

        try {
            $delivery = $this->deliveryModel->getById($deliveryId);

            if (!$delivery) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Delivery not found'
                ], 404);
                return;
            }

            // Get order details
            $order = $this->orderModel->getById($delivery['order_id']);

            // Get restaurant location
            $restaurantLat = $order['restaurant_latitude'] ?? null;
            $restaurantLng = $order['restaurant_longitude'] ?? null;

            // Get customer location
            $deliveryAddress = json_decode($order['delivery_address'], true);
            $customerLat = $deliveryAddress['latitude'] ?? null;
            $customerLng = $deliveryAddress['longitude'] ?? null;

            // Get current rider location
            $riderLocation = null;
            if ($delivery['rider_id']) {
                $location = $this->locationModel->getLatestLocation($delivery['rider_id']);
                if ($location) {
                    $riderLocation = [
                        'latitude' => (float)$location['latitude'],
                        'longitude' => (float)$location['longitude']
                    ];
                }
            }

            $this->jsonResponse([
                'success' => true,
                'route' => [
                    'restaurant' => [
                        'latitude' => $restaurantLat,
                        'longitude' => $restaurantLng
                    ],
                    'customer' => [
                        'latitude' => $customerLat,
                        'longitude' => $customerLng
                    ],
                    'rider' => $riderLocation
                ],
                'status' => $delivery['status']
            ]);

        } catch (\Exception $e) {
            error_log("Route API error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch route data'
            ], 500);
        }
    }
}

