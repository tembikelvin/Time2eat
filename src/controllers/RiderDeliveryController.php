<?php

declare(strict_types=1);

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\Delivery;
use Time2Eat\Models\Order;
use Time2Eat\Models\User;
use Time2Eat\Models\RiderSchedule;
use Time2Eat\Models\RiderLocation;
use Time2Eat\Models\PushSubscription;

/**
 * Rider Delivery Controller
 * Handles delivery acceptance, navigation, and real-time updates
 */
class RiderDeliveryController extends BaseController
{
    private Delivery $deliveryModel;
    private Order $orderModel;
    private User $userModel;
    private RiderSchedule $scheduleModel;
    private RiderLocation $locationModel;
    private PushSubscription $pushModel;

    public function __construct()
    {
        parent::__construct();
        $this->deliveryModel = new Delivery();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->scheduleModel = new RiderSchedule();
        $this->locationModel = new RiderLocation();
        $this->pushModel = new PushSubscription();
    }

    /**
     * Get available deliveries for rider
     */
    public function getAvailableDeliveries(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        
        // Check if rider is available and online
        if (!$this->isRiderAvailable($user['id'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'You are not available for deliveries',
                'available_deliveries' => []
            ]);
            return;
        }

        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $radius = (float)($_GET['radius'] ?? 10); // km
            
            // Get rider's current location
            $riderLocation = $this->locationModel->getLatestLocation($user['id']);
            
            if (!$riderLocation) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Location not available. Please enable location services.',
                    'available_deliveries' => []
                ]);
                return;
            }

            // Get available deliveries within radius
            $deliveries = $this->deliveryModel->getAvailableDeliveriesNearby(
                $riderLocation['latitude'],
                $riderLocation['longitude'],
                $radius,
                $limit,
                ($page - 1) * $limit
            );

            // Calculate distances and estimated earnings
            foreach ($deliveries as &$delivery) {
                $delivery['distance_to_pickup'] = $this->calculateDistance(
                    $riderLocation['latitude'],
                    $riderLocation['longitude'],
                    $delivery['pickup_latitude'],
                    $delivery['pickup_longitude']
                );
                
                $delivery['estimated_earnings'] = $this->calculateRiderEarnings(
                    $delivery['delivery_fee'],
                    $delivery['distance_km']
                );
                
                $delivery['estimated_time'] = $this->estimateDeliveryTime(
                    $delivery['distance_km'],
                    $delivery['preparation_time'] ?? 15
                );
            }

            $this->jsonResponse([
                'success' => true,
                'available_deliveries' => $deliveries,
                'rider_location' => $riderLocation,
                'page' => $page,
                'has_more' => count($deliveries) === $limit
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to get available deliveries', [
                'rider_id' => $user['id'],
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load available deliveries'
            ], 500);
        }
    }

    /**
     * Accept a delivery
     */
    public function acceptDelivery(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $input = $this->getJsonInput();
        $deliveryId = (int)($input['delivery_id'] ?? 0);

        if (!$deliveryId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Delivery ID is required'
            ], 400);
            return;
        }

        try {
            // Check if delivery is still available
            $delivery = $this->deliveryModel->getById($deliveryId);
            
            if (!$delivery || $delivery['status'] !== 'assigned') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Delivery is no longer available'
                ], 404);
                return;
            }

            // Check if rider can accept more deliveries
            $activeDeliveries = $this->deliveryModel->getActiveDeliveriesByRider($user['id']);
            $maxConcurrentDeliveries = $this->getRiderMaxDeliveries($user['id']);
            
            if (count($activeDeliveries) >= $maxConcurrentDeliveries) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'You have reached the maximum number of concurrent deliveries'
                ], 400);
                return;
            }

            // Accept the delivery
            $success = $this->deliveryModel->acceptDelivery($deliveryId, $user['id']);
            
            if (!$success) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to accept delivery'
                ], 500);
                return;
            }

            // Get updated delivery details
            $updatedDelivery = $this->deliveryModel->getDeliveryWithDetails($deliveryId);

            // Send push notification to customer
            $this->sendDeliveryNotification(
                $updatedDelivery['customer_id'],
                'Rider Assigned',
                'Your delivery has been assigned to ' . $user['first_name'] . ' ' . $user['last_name'],
                [
                    'type' => 'delivery_accepted',
                    'delivery_id' => $deliveryId,
                    'rider_name' => $user['first_name'] . ' ' . $user['last_name'],
                    'rider_phone' => $user['phone']
                ]
            );

            // Send notification to restaurant
            $this->sendDeliveryNotification(
                $updatedDelivery['restaurant_vendor_id'],
                'Rider Assigned',
                'Rider ' . $user['first_name'] . ' will pick up order #' . $updatedDelivery['order_number'],
                [
                    'type' => 'rider_assigned',
                    'delivery_id' => $deliveryId,
                    'order_number' => $updatedDelivery['order_number']
                ]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Delivery accepted successfully',
                'delivery' => $updatedDelivery
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to accept delivery', [
                'rider_id' => $user['id'],
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to accept delivery'
            ], 500);
        }
    }

    /**
     * Reject a delivery
     */
    public function rejectDelivery(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $input = $this->getJsonInput();
        $deliveryId = (int)($input['delivery_id'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$deliveryId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Delivery ID is required'
            ], 400);
            return;
        }

        try {
            // Log the rejection
            $this->deliveryModel->logRiderRejection($deliveryId, $user['id'], $reason);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Delivery rejected'
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to reject delivery', [
                'rider_id' => $user['id'],
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reject delivery'
            ], 500);
        }
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $input = $this->getJsonInput();
        $deliveryId = (int)($input['delivery_id'] ?? 0);
        $status = trim($input['status'] ?? '');
        $notes = trim($input['notes'] ?? '');
        $proofImage = $input['proof_image'] ?? null;

        if (!$deliveryId || !$status) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Delivery ID and status are required'
            ], 400);
            return;
        }

        $validStatuses = ['accepted', 'picked_up', 'on_the_way', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid status'
            ], 400);
            return;
        }

        try {
            // Verify rider owns this delivery
            $delivery = $this->deliveryModel->getById($deliveryId);
            if (!$delivery || $delivery['rider_id'] !== $user['id']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Delivery not found or not assigned to you'
                ], 404);
                return;
            }

            // Update delivery status
            $updateData = [
                'status' => $status,
                'rider_notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Set timestamps based on status
            switch ($status) {
                case 'picked_up':
                    $updateData['pickup_time'] = date('Y-m-d H:i:s');
                    break;
                case 'delivered':
                    $updateData['delivery_time'] = date('Y-m-d H:i:s');
                    if ($proofImage) {
                        $updateData['delivery_proof'] = $proofImage;
                    }
                    break;
            }

            $success = $this->deliveryModel->updateDelivery($deliveryId, $updateData);

            if (!$success) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update delivery status'
                ], 500);
                return;
            }
            
            // Process affiliate commission if delivery is completed
            if ($status === 'delivered') {
                $order = $this->query("SELECT id FROM orders WHERE id = ?", [$delivery['order_id']]);
                if ($order) {
                    require_once __DIR__ . '/../../models/Order.php';
                    $orderModel = new \models\Order();
                    $orderModel->processAffiliateCommission($delivery['order_id']);
                }
            }

            // Get updated delivery for notifications
            $updatedDelivery = $this->deliveryModel->getDeliveryWithDetails($deliveryId);

            // Send appropriate notifications
            $this->sendStatusUpdateNotifications($updatedDelivery, $status);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Delivery status updated successfully',
                'delivery' => $updatedDelivery
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to update delivery status', [
                'rider_id' => $user['id'],
                'delivery_id' => $deliveryId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update delivery status'
            ], 500);
        }
    }

    /**
     * Update rider location
     */
    public function updateLocation(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $input = $this->getJsonInput();

        $latitude = (float)($input['latitude'] ?? 0);
        $longitude = (float)($input['longitude'] ?? 0);
        $accuracy = (float)($input['accuracy'] ?? null);
        $speed = (float)($input['speed'] ?? null);
        $heading = (float)($input['heading'] ?? null);
        $batteryLevel = (int)($input['battery_level'] ?? null);

        if (!$latitude || !$longitude) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Latitude and longitude are required'
            ], 400);
            return;
        }

        try {
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
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update location'
                ], 500);
                return;
            }

            // Broadcast location update to active deliveries
            $this->broadcastLocationUpdate($user['id'], $latitude, $longitude);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Location updated successfully',
                'location_id' => $locationId
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to update rider location', [
                'rider_id' => $user['id'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }

    /**
     * Get navigation route
     */
    public function getNavigationRoute(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $deliveryId = (int)($_GET['delivery_id'] ?? 0);
        $destination = $_GET['destination'] ?? 'pickup'; // pickup or delivery

        if (!$deliveryId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Delivery ID is required'
            ], 400);
            return;
        }

        try {
            // Verify rider owns this delivery
            $delivery = $this->deliveryModel->getDeliveryWithDetails($deliveryId);
            if (!$delivery || $delivery['rider_id'] !== $user['id']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Delivery not found or not assigned to you'
                ], 404);
                return;
            }

            // Get rider's current location
            $riderLocation = $this->locationModel->getLatestLocation($user['id']);
            if (!$riderLocation) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Current location not available'
                ], 400);
                return;
            }

            // Determine destination coordinates
            if ($destination === 'pickup') {
                $destLat = $delivery['pickup_latitude'];
                $destLng = $delivery['pickup_longitude'];
                $destAddress = $delivery['pickup_address'];
            } else {
                $destLat = $delivery['delivery_latitude'];
                $destLng = $delivery['delivery_longitude'];
                $destAddress = $delivery['delivery_address'];
            }

            // Get route from mapping service
            $route = $this->getRoute(
                $riderLocation['latitude'],
                $riderLocation['longitude'],
                $destLat,
                $destLng
            );

            if (!$route) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Unable to calculate route'
                ], 500);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'route' => $route,
                'origin' => [
                    'latitude' => $riderLocation['latitude'],
                    'longitude' => $riderLocation['longitude']
                ],
                'destination' => [
                    'latitude' => $destLat,
                    'longitude' => $destLng,
                    'address' => $destAddress
                ],
                'delivery' => $delivery
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to get navigation route', [
                'rider_id' => $user['id'],
                'delivery_id' => $deliveryId,
                'destination' => $destination,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get navigation route'
            ], 500);
        }
    }

    /**
     * Get rider earnings summary
     */
    public function getEarnings(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['rider'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $period = $_GET['period'] ?? 'today'; // today, week, month, all

        try {
            $earnings = $this->deliveryModel->getRiderEarnings($user['id'], $period);
            $stats = $this->deliveryModel->getRiderStats($user['id'], $period);

            $this->jsonResponse([
                'success' => true,
                'earnings' => $earnings,
                'stats' => $stats,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to get rider earnings', [
                'rider_id' => $user['id'],
                'period' => $period,
                'error' => $e->getMessage()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get earnings data'
            ], 500);
        }
    }

    /**
     * Check if rider is available for deliveries
     */
    private function isRiderAvailable(int $riderId): bool
    {
        // Check if rider is online and within working hours
        $schedule = $this->scheduleModel->getCurrentSchedule($riderId);
        if (!$schedule || !$schedule['is_available']) {
            return false;
        }

        // Check if rider has reached maximum concurrent deliveries
        $activeDeliveries = $this->deliveryModel->getActiveDeliveriesByRider($riderId);
        $maxDeliveries = $this->getRiderMaxDeliveries($riderId);

        return count($activeDeliveries) < $maxDeliveries;
    }

    /**
     * Get rider's maximum concurrent deliveries
     */
    private function getRiderMaxDeliveries(int $riderId): int
    {
        $schedule = $this->scheduleModel->getCurrentSchedule($riderId);
        return $schedule['max_orders'] ?? 3; // Default to 3 concurrent deliveries
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Calculate rider earnings for a delivery (Standardized Formula)
     */
    private function calculateRiderEarnings(float $deliveryFee, float $distance): float
    {
        // Standardized rider earnings calculation
        // Base earnings: 350 XAF (for 0-3km)
        $baseEarnings = 350;

        // Distance bonus: 70 XAF per km (only for distance > 3km)
        $distanceBonus = 0;
        if ($distance > 3) {
            $additionalDistance = $distance - 3;
            $distanceBonus = $additionalDistance * 70;
        }

        $totalEarnings = $baseEarnings + $distanceBonus;

        // Ensure minimum earnings
        return round(max($totalEarnings, 350), 0);
    }

    /**
     * Estimate delivery time based on distance and preparation time
     */
    private function estimateDeliveryTime(float $distance, int $preparationTime): int
    {
        // Assume average speed of 25 km/h in city traffic
        $travelTime = ($distance / 25) * 60; // Convert to minutes

        return (int)($preparationTime + $travelTime + 5); // Add 5 minutes buffer
    }

    /**
     * Send delivery notification via push notification
     */
    private function sendDeliveryNotification(int $userId, string $title, string $body, array $data = []): void
    {
        try {
            $subscriptions = $this->pushModel->getActiveSubscriptions($userId);

            if (empty($subscriptions)) {
                return; // No active subscriptions
            }

            $notificationData = [
                'title' => $title,
                'body' => $body,
                'icon' => '/images/icons/icon-192x192.png',
                'badge' => '/images/icons/badge-72x72.png',
                'data' => array_merge($data, [
                    'timestamp' => time(),
                    'url' => '/dashboard'
                ])
            ];

            $this->pushModel->sendToUsers([$userId], $notificationData);

        } catch (\Exception $e) {
            $this->logError('Failed to send push notification', [
                'user_id' => $userId,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send status update notifications to relevant parties
     */
    private function sendStatusUpdateNotifications(array $delivery, string $status): void
    {
        $statusMessages = [
            'accepted' => 'Your delivery has been accepted by the rider',
            'picked_up' => 'Your order has been picked up and is on the way',
            'on_the_way' => 'Your order is on the way to you',
            'delivered' => 'Your order has been delivered successfully',
            'cancelled' => 'Your delivery has been cancelled'
        ];

        $message = $statusMessages[$status] ?? 'Delivery status updated';

        // Notify customer
        $this->sendDeliveryNotification(
            $delivery['customer_id'],
            'Delivery Update',
            $message,
            [
                'type' => 'status_update',
                'delivery_id' => $delivery['id'],
                'status' => $status,
                'tracking_code' => $delivery['tracking_code']
            ]
        );

        // Notify restaurant for certain statuses
        if (in_array($status, ['picked_up', 'delivered'])) {
            $restaurantMessage = $status === 'picked_up'
                ? 'Order #' . $delivery['order_number'] . ' has been picked up'
                : 'Order #' . $delivery['order_number'] . ' has been delivered';

            $this->sendDeliveryNotification(
                $delivery['restaurant_vendor_id'],
                'Order Update',
                $restaurantMessage,
                [
                    'type' => 'order_update',
                    'order_id' => $delivery['order_id'],
                    'status' => $status
                ]
            );
        }
    }

    /**
     * Broadcast location update to WebSocket clients
     */
    private function broadcastLocationUpdate(int $riderId, float $latitude, float $longitude): void
    {
        try {
            // Get active deliveries for this rider
            $activeDeliveries = $this->deliveryModel->getActiveDeliveriesByRider($riderId);

            foreach ($activeDeliveries as $delivery) {
                // Broadcast to WebSocket server if available
                $this->broadcastToWebSocket([
                    'type' => 'location_update',
                    'delivery_id' => $delivery['id'],
                    'rider_id' => $riderId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'timestamp' => time()
                ]);
            }
        } catch (\Exception $e) {
            $this->logError('Failed to broadcast location update', [
                'rider_id' => $riderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get route from mapping service
     */
    private function getRoute(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        $mapProvider = $_ENV['MAP_PROVIDER'] ?? 'openstreetmap';

        if ($mapProvider === 'google') {
            return $this->getGoogleRoute($originLat, $originLng, $destLat, $destLng);
        } else {
            return $this->getOpenStreetMapRoute($originLat, $originLng, $destLat, $destLng);
        }
    }

    /**
     * Get route from Google Maps
     */
    private function getGoogleRoute(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
        if (!$apiKey) {
            return null;
        }

        $url = "https://maps.googleapis.com/maps/api/directions/json?" . http_build_query([
            'origin' => "$originLat,$originLng",
            'destination' => "$destLat,$destLng",
            'mode' => 'driving',
            'key' => $apiKey
        ]);

        $response = file_get_contents($url);
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if ($data['status'] !== 'OK' || empty($data['routes'])) {
            return null;
        }

        $route = $data['routes'][0];
        return [
            'distance' => $route['legs'][0]['distance']['value'] / 1000, // Convert to km
            'duration' => $route['legs'][0]['duration']['value'] / 60, // Convert to minutes
            'polyline' => $route['overview_polyline']['points'],
            'steps' => $route['legs'][0]['steps']
        ];
    }

    /**
     * Get route from OpenStreetMap (OSRM)
     */
    private function getOpenStreetMapRoute(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        $url = "http://router.project-osrm.org/route/v1/driving/$originLng,$originLat;$destLng,$destLat?overview=full&geometries=polyline";

        $response = file_get_contents($url);
        if (!$response) {
            return null;
        }

        $data = json_decode($response, true);
        if ($data['code'] !== 'Ok' || empty($data['routes'])) {
            return null;
        }

        $route = $data['routes'][0];
        return [
            'distance' => $route['distance'] / 1000, // Convert to km
            'duration' => $route['duration'] / 60, // Convert to minutes
            'polyline' => $route['geometry'],
            'steps' => $route['legs'][0]['steps'] ?? []
        ];
    }

    /**
     * Broadcast message to WebSocket server
     */
    private function broadcastToWebSocket(array $message): void
    {
        try {
            $websocketHost = $_ENV['WEBSOCKET_HOST'] ?? 'localhost';
            $websocketPort = $_ENV['WEBSOCKET_PORT'] ?? 8080;

            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket && socket_connect($socket, $websocketHost, (int)$websocketPort)) {
                socket_write($socket, json_encode($message));
                socket_close($socket);
            }
        } catch (\Exception $e) {
            // Silently fail - WebSocket is optional
        }
    }
}
