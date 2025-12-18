<?php

namespace Time2Eat\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Time2Eat\Models\Delivery;
use Time2Eat\Models\User;

class TrackingServer implements MessageComponentInterface
{
    protected $clients;
    protected $deliverySubscriptions;
    private Delivery $deliveryModel;
    private User $userModel;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->deliverySubscriptions = [];
        $this->deliveryModel = new Delivery();
        $this->userModel = new User();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $this->sendError($from, 'Invalid message format');
                return;
            }

            switch ($data['type']) {
                case 'authenticate':
                    $this->handleAuthentication($from, $data);
                    break;
                    
                case 'subscribe_delivery':
                    $this->handleDeliverySubscription($from, $data);
                    break;
                    
                case 'unsubscribe_delivery':
                    $this->handleDeliveryUnsubscription($from, $data);
                    break;
                    
                case 'location_update':
                    $this->handleLocationUpdate($from, $data);
                    break;
                    
                case 'status_update':
                    $this->handleStatusUpdate($from, $data);
                    break;
                    
                case 'ping':
                    $this->sendMessage($from, ['type' => 'pong', 'timestamp' => time()]);
                    break;
                    
                default:
                    $this->sendError($from, 'Unknown message type');
            }
        } catch (\Exception $e) {
            $this->sendError($from, 'Error processing message: ' . $e->getMessage());
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // Remove from delivery subscriptions
        foreach ($this->deliverySubscriptions as $deliveryId => $subscribers) {
            if (isset($subscribers[$conn->resourceId])) {
                unset($this->deliverySubscriptions[$deliveryId][$conn->resourceId]);
                
                if (empty($this->deliverySubscriptions[$deliveryId])) {
                    unset($this->deliverySubscriptions[$deliveryId]);
                }
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function handleAuthentication(ConnectionInterface $conn, array $data)
    {
        if (!isset($data['token'])) {
            $this->sendError($conn, 'Authentication token required');
            return;
        }

        // Verify JWT token or session
        $user = $this->verifyToken($data['token']);
        
        if (!$user) {
            $this->sendError($conn, 'Invalid authentication token');
            return;
        }

        // Store user info with connection
        $conn->user = $user;
        
        $this->sendMessage($conn, [
            'type' => 'authenticated',
            'user_id' => $user['id'],
            'role' => $user['role']
        ]);
    }

    private function handleDeliverySubscription(ConnectionInterface $conn, array $data)
    {
        if (!isset($conn->user)) {
            $this->sendError($conn, 'Authentication required');
            return;
        }

        if (!isset($data['delivery_id'])) {
            $this->sendError($conn, 'Delivery ID required');
            return;
        }

        $deliveryId = $data['delivery_id'];
        $user = $conn->user;

        // Verify user has access to this delivery
        $delivery = $this->deliveryModel->getDeliveryByOrder($deliveryId);
        
        if (!$delivery) {
            $this->sendError($conn, 'Delivery not found');
            return;
        }

        $hasAccess = false;
        switch ($user['role']) {
            case 'customer':
                $hasAccess = $delivery['customer_id'] === $user['id'];
                break;
            case 'rider':
                $hasAccess = $delivery['rider_id'] === $user['id'];
                break;
            case 'vendor':
                $hasAccess = $delivery['restaurant_owner_id'] === $user['id'];
                break;
            case 'admin':
                $hasAccess = true;
                break;
        }

        if (!$hasAccess) {
            $this->sendError($conn, 'Access denied');
            return;
        }

        // Add to subscription
        if (!isset($this->deliverySubscriptions[$deliveryId])) {
            $this->deliverySubscriptions[$deliveryId] = [];
        }

        $this->deliverySubscriptions[$deliveryId][$conn->resourceId] = [
            'connection' => $conn,
            'user' => $user,
            'subscribed_at' => time()
        ];

        $this->sendMessage($conn, [
            'type' => 'subscribed',
            'delivery_id' => $deliveryId
        ]);

        // Send current delivery status
        $this->sendDeliveryUpdate($conn, $delivery);
    }

    private function handleDeliveryUnsubscription(ConnectionInterface $conn, array $data)
    {
        if (!isset($data['delivery_id'])) {
            $this->sendError($conn, 'Delivery ID required');
            return;
        }

        $deliveryId = $data['delivery_id'];

        if (isset($this->deliverySubscriptions[$deliveryId][$conn->resourceId])) {
            unset($this->deliverySubscriptions[$deliveryId][$conn->resourceId]);
            
            if (empty($this->deliverySubscriptions[$deliveryId])) {
                unset($this->deliverySubscriptions[$deliveryId]);
            }
        }

        $this->sendMessage($conn, [
            'type' => 'unsubscribed',
            'delivery_id' => $deliveryId
        ]);
    }

    private function handleLocationUpdate(ConnectionInterface $from, array $data)
    {
        if (!isset($from->user) || $from->user['role'] !== 'rider') {
            $this->sendError($from, 'Only riders can send location updates');
            return;
        }

        if (!isset($data['delivery_id'], $data['latitude'], $data['longitude'])) {
            $this->sendError($from, 'Delivery ID, latitude, and longitude required');
            return;
        }

        $deliveryId = $data['delivery_id'];
        
        // Verify rider owns this delivery
        $delivery = $this->deliveryModel->getById($deliveryId);
        
        if (!$delivery || $delivery['rider_id'] !== $from->user['id']) {
            $this->sendError($from, 'Access denied');
            return;
        }

        // Update location in database
        $this->deliveryModel->updateRiderLocation(
            $deliveryId,
            $data['latitude'],
            $data['longitude']
        );

        // Broadcast to subscribers
        $this->broadcastToDeliverySubscribers($deliveryId, [
            'type' => 'location_update',
            'delivery_id' => $deliveryId,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timestamp' => time()
        ]);
    }

    private function handleStatusUpdate(ConnectionInterface $from, array $data)
    {
        if (!isset($from->user) || !in_array($from->user['role'], ['rider', 'admin'])) {
            $this->sendError($from, 'Insufficient permissions');
            return;
        }

        if (!isset($data['delivery_id'], $data['status'])) {
            $this->sendError($from, 'Delivery ID and status required');
            return;
        }

        $deliveryId = $data['delivery_id'];
        $status = $data['status'];

        // Verify access
        if ($from->user['role'] === 'rider') {
            $delivery = $this->deliveryModel->getById($deliveryId);
            
            if (!$delivery || $delivery['rider_id'] !== $from->user['id']) {
                $this->sendError($from, 'Access denied');
                return;
            }
        }

        // Update status in database
        $this->deliveryModel->updateDeliveryStatus($deliveryId, $status);

        // Broadcast to subscribers
        $this->broadcastToDeliverySubscribers($deliveryId, [
            'type' => 'status_update',
            'delivery_id' => $deliveryId,
            'status' => $status,
            'timestamp' => time()
        ]);
    }

    private function broadcastToDeliverySubscribers(int $deliveryId, array $message)
    {
        if (!isset($this->deliverySubscriptions[$deliveryId])) {
            return;
        }

        foreach ($this->deliverySubscriptions[$deliveryId] as $subscriber) {
            $this->sendMessage($subscriber['connection'], $message);
        }
    }

    private function sendMessage(ConnectionInterface $conn, array $data)
    {
        $conn->send(json_encode($data));
    }

    private function sendError(ConnectionInterface $conn, string $message)
    {
        $this->sendMessage($conn, [
            'type' => 'error',
            'message' => $message,
            'timestamp' => time()
        ]);
    }

    private function sendDeliveryUpdate(ConnectionInterface $conn, array $delivery)
    {
        $riderLocation = null;
        if ($delivery['status'] === 'out_for_delivery') {
            $riderLocation = $this->deliveryModel->getRiderLocation($delivery['id']);
        }

        $this->sendMessage($conn, [
            'type' => 'delivery_update',
            'delivery' => [
                'id' => $delivery['id'],
                'status' => $delivery['status'],
                'pickup_location' => json_decode($delivery['pickup_location'], true),
                'delivery_location' => json_decode($delivery['delivery_location'], true),
                'rider_location' => $riderLocation,
                'estimated_delivery_time' => $delivery['estimated_delivery_time']
            ]
        ]);
    }

    private function verifyToken(string $token): ?array
    {
        // Implement JWT token verification or session validation
        // This is a simplified version - in production, use proper JWT validation
        
        try {
            // For session-based auth, you might decode a session token
            // For JWT, use a proper JWT library
            
            // Simplified example - replace with actual implementation
            $decoded = base64_decode($token);
            $data = json_decode($decoded, true);
            
            if (!$data || !isset($data['user_id'])) {
                return null;
            }

            return $this->userModel->getById($data['user_id']);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function start(int $port = 8080)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new TrackingServer()
                )
            ),
            $port
        );

        echo "WebSocket server started on port {$port}\n";
        $server->run();
    }
}
