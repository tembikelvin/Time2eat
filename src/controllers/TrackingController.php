<?php

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\Delivery;
use Time2Eat\Models\Order;

class TrackingController extends BaseController
{
    private Delivery $deliveryModel;
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->deliveryModel = new Delivery();
        $this->orderModel = new Order();
    }

    public function trackOrder(): void
    {
        $this->requireAuth();

        $validation = $this->validateRequest([
            'order_id' => 'integer',
            'tracking_code' => 'string'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $data = $validation['data'];

        $delivery = null;

        if (isset($data['order_id'])) {
            // Verify order belongs to user (for customers) or user is rider/admin
            $order = $this->orderModel->getOrderDetails($data['order_id']);

            if (!$order) {
                $this->jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }

            if ($user['role'] === 'customer' && $order['customer_id'] !== $user['id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
                return;
            }

            $delivery = $this->deliveryModel->getDeliveryByOrder($data['order_id']);
        } elseif (isset($data['tracking_code'])) {
            $delivery = $this->deliveryModel->getDeliveryByTrackingCode($data['tracking_code']);

            if ($delivery && $user['role'] === 'customer' && $delivery['customer_id'] !== $user['id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
                return;
            }
        }

        if (!$delivery) {
            $this->jsonResponse(['success' => false, 'message' => 'Delivery not found'], 404);
            return;
        }

        $trackingData = $this->buildTrackingData($delivery);

        $this->jsonResponse([
            'success' => true,
            'tracking' => $trackingData
        ]);
    }

    public function trackByCode(): void
    {
        // Get tracking code from URL parameter
        $trackingCode = $_GET['tracking_code'] ?? '';

        if (empty($trackingCode)) {
            $this->jsonResponse(['success' => false, 'message' => 'Tracking code required'], 400);
            return;
        }

        $delivery = $this->deliveryModel->getDeliveryByTrackingCode($trackingCode);

        if (!$delivery) {
            $this->jsonResponse(['success' => false, 'message' => 'Delivery not found'], 404);
            return;
        }

        $trackingData = $this->buildTrackingData($delivery);

        $this->jsonResponse([
            'success' => true,
            'tracking' => $trackingData
        ]);
    }

    private function buildTrackingData(array $delivery): array
    {
        // Get current rider location if available
        $riderLocation = null;
        if ($delivery['status'] === 'out_for_delivery' && $delivery['rider_id']) {
            $riderLocation = $this->deliveryModel->getRiderLocation($delivery['id']);
        }

        // Parse locations
        $pickupLocation = json_decode($delivery['pickup_location'], true);
        $deliveryLocation = json_decode($delivery['delivery_location'], true);

        return [
            'delivery_id' => $delivery['id'],
            'tracking_code' => $delivery['tracking_code'],
            'order_number' => $delivery['order_number'],
            'status' => $delivery['status'],
            'estimated_delivery_time' => $delivery['estimated_delivery_time'],
            'pickup_time' => $delivery['pickup_time'],
            'delivery_time' => $delivery['delivery_time'],
            'pickup_location' => $pickupLocation,
            'delivery_location' => $deliveryLocation,
            'rider_location' => $riderLocation,
            'rider' => [
                'name' => ($delivery['rider_first_name'] ?? '') . ' ' . ($delivery['rider_last_name'] ?? ''),
                'phone' => $delivery['rider_phone'] ?? '',
                'image' => $delivery['rider_image'] ?? ''
            ],
            'restaurant' => [
                'name' => $delivery['restaurant_name'] ?? '',
                'address' => $delivery['restaurant_address'] ?? ''
            ]
        ];
    }

    public function updateRiderLocation(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $validation = $this->validateRequest([
            'delivery_id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $data = $validation['data'];

        // Verify delivery belongs to rider
        $delivery = $this->deliveryModel->getById($data['delivery_id']);
        
        if (!$delivery || $delivery['rider_id'] !== $user['id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Delivery not found or access denied'], 404);
            return;
        }

        // Update rider location
        $updated = $this->deliveryModel->updateRiderLocation(
            $data['delivery_id'],
            $data['latitude'],
            $data['longitude']
        );

        if ($updated) {
            // Broadcast location update to WebSocket clients
            $this->broadcastLocationUpdate($data['delivery_id'], $data['latitude'], $data['longitude']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Location updated successfully'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update location'], 500);
        }
    }

    public function updateDeliveryStatus(): void
    {
        $this->requireAuth();
        $this->requireRole('rider');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $validation = $this->validateRequest([
            'delivery_id' => 'required|integer',
            'status' => 'required|string|in:picked_up,out_for_delivery,delivered,failed',
            'notes' => 'string|max:500',
            'delivery_proof' => 'string'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $data = $validation['data'];

        // Verify delivery belongs to rider
        $delivery = $this->deliveryModel->getById($data['delivery_id']);
        
        if (!$delivery || $delivery['rider_id'] !== $user['id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Delivery not found or access denied'], 404);
            return;
        }

        // Prepare additional data
        $additionalData = [];
        if (!empty($data['notes'])) {
            $additionalData['rider_notes'] = $data['notes'];
        }
        if (!empty($data['delivery_proof'])) {
            $additionalData['delivery_proof'] = $data['delivery_proof'];
        }

        // Update delivery status
        $updated = $this->deliveryModel->updateDeliveryStatus(
            $data['delivery_id'],
            $data['status'],
            $additionalData
        );

        if ($updated) {
            // Update corresponding order status
            $this->updateOrderStatus($delivery['order_id'], $data['status']);

            // Broadcast status update to WebSocket clients
            $this->broadcastStatusUpdate($data['delivery_id'], $data['status']);

            // Send notifications
            $this->sendStatusNotification($delivery, $data['status']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }

    public function getTrackingPage(): void
    {
        $trackingCode = $_GET['code'] ?? '';
        
        if (empty($trackingCode)) {
            $this->render('tracking/search', [
                'title' => 'Track Your Order - Time2Eat'
            ]);
            return;
        }

        // Get delivery details
        $delivery = $this->deliveryModel->getDeliveryByTrackingCode($trackingCode);
        
        if (!$delivery) {
            $this->render('tracking/not-found', [
                'title' => 'Order Not Found - Time2Eat',
                'tracking_code' => $trackingCode
            ]);
            return;
        }

        // Parse locations
        $pickupLocation = json_decode($delivery['pickup_location'], true);
        $deliveryLocation = json_decode($delivery['delivery_location'], true);
        $riderLocation = null;

        if ($delivery['status'] === 'out_for_delivery' && $delivery['rider_id']) {
            $riderLocation = $this->deliveryModel->getRiderLocation($delivery['id']);
        }

        $this->render('tracking/live', [
            'title' => 'Track Order #' . $delivery['order_number'] . ' - Time2Eat',
            'delivery' => $delivery,
            'pickup_location' => $pickupLocation,
            'delivery_location' => $deliveryLocation,
            'rider_location' => $riderLocation
        ]);
    }

    public function getDeliveryUpdates(): void
    {
        $this->requireAuth();

        $validation = $this->validateRequest([
            'delivery_id' => 'required|integer'
        ]);

        if (!$validation['isValid']) {
            $this->jsonResponse(['success' => false, 'errors' => $validation['errors']], 400);
            return;
        }

        $user = $this->getAuthenticatedUser();
        $deliveryId = $validation['data']['delivery_id'];

        // Verify access
        $delivery = $this->deliveryModel->getById($deliveryId);
        
        if (!$delivery) {
            $this->jsonResponse(['success' => false, 'message' => 'Delivery not found'], 404);
            return;
        }

        if ($user['role'] === 'customer' && $delivery['customer_id'] !== $user['id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }

        // Set headers for Server-Sent Events
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Cache-Control');

        // Send initial data
        $this->sendSSEData('init', [
            'delivery_id' => $deliveryId,
            'status' => $delivery['status'],
            'timestamp' => time()
        ]);

        // Keep connection alive and send updates
        $lastUpdate = time();
        while (true) {
            // Check for updates every 5 seconds
            if (time() - $lastUpdate >= 5) {
                $currentDelivery = $this->deliveryModel->getById($deliveryId);
                
                if ($currentDelivery && $currentDelivery['updated_at'] !== $delivery['updated_at']) {
                    $this->sendSSEData('status_update', [
                        'status' => $currentDelivery['status'],
                        'timestamp' => time()
                    ]);
                    
                    $delivery = $currentDelivery;
                }

                // Send location update if rider is active
                if ($delivery['status'] === 'out_for_delivery') {
                    $riderLocation = $this->deliveryModel->getRiderLocation($deliveryId);
                    if ($riderLocation) {
                        $this->sendSSEData('location_update', $riderLocation);
                    }
                }

                $lastUpdate = time();
            }

            // Break if delivery is completed
            if (in_array($delivery['status'], ['delivered', 'failed', 'cancelled'])) {
                break;
            }

            // Check if client disconnected
            if (connection_aborted()) {
                break;
            }

            sleep(1);
        }
    }

    private function sendSSEData(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    private function updateOrderStatus(int $orderId, string $deliveryStatus): void
    {
        $orderStatus = match($deliveryStatus) {
            'picked_up' => 'picked_up',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'failed' => 'cancelled',
            default => null
        };

        if ($orderStatus) {
            $this->orderModel->updateOrderStatus($orderId, $orderStatus);
        }
    }

    private function broadcastLocationUpdate(int $deliveryId, float $latitude, float $longitude): void
    {
        // Implementation for WebSocket broadcasting
        // This would integrate with Ratchet WebSocket server
        $data = [
            'type' => 'location_update',
            'delivery_id' => $deliveryId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timestamp' => time()
        ];

        // Store in Redis or send to WebSocket server
        // $this->websocketBroadcast($data);
    }

    private function broadcastStatusUpdate(int $deliveryId, string $status): void
    {
        // Implementation for WebSocket broadcasting
        $data = [
            'type' => 'status_update',
            'delivery_id' => $deliveryId,
            'status' => $status,
            'timestamp' => time()
        ];

        // Store in Redis or send to WebSocket server
        // $this->websocketBroadcast($data);
    }

    private function sendStatusNotification(array $delivery, string $status): void
    {
        // Send notification to customer
        $message = match($status) {
            'picked_up' => "Your order #{$delivery['order_number']} has been picked up by the rider.",
            'out_for_delivery' => "Your order #{$delivery['order_number']} is out for delivery!",
            'delivered' => "Your order #{$delivery['order_number']} has been delivered. Enjoy your meal!",
            'failed' => "There was an issue with your order #{$delivery['order_number']}. Please contact support.",
            default => "Your order #{$delivery['order_number']} status has been updated."
        };

        // Implementation for sending notifications
        // $this->notificationService->send($delivery['customer_id'], $message);
    }
}
