<?php
/**
 * WebSocket Server for Real-time Order Tracking
 * Handles real-time communication between customers, vendors, and riders
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Time2Eat WebSocket Application
 */
class Time2EatWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $rooms;
    protected $db;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->rooms = [];
        
        // Initialize database connection
        try {
            require_once __DIR__ . '/../config/database.php';
            $this->db = Database::getInstance();
            echo "✓ Database connection established\n";
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'welcome',
            'message' => 'Connected to Time2Eat real-time server',
            'connection_id' => $conn->resourceId
        ]));
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $this->sendError($from, 'Invalid message format');
                return;
            }
            
            switch ($data['type']) {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;
                    
                case 'join_order':
                    $this->handleJoinOrder($from, $data);
                    break;
                    
                case 'order_update':
                    $this->handleOrderUpdate($from, $data);
                    break;
                    
                case 'location_update':
                    $this->handleLocationUpdate($from, $data);
                    break;
                    
                case 'chat_message':
                    $this->handleChatMessage($from, $data);
                    break;
                    
                case 'heartbeat':
                    $this->handleHeartbeat($from);
                    break;
                    
                default:
                    $this->sendError($from, 'Unknown message type: ' . $data['type']);
            }
        } catch (Exception $e) {
            echo "Error handling message: " . $e->getMessage() . "\n";
            $this->sendError($from, 'Server error occurred');
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove user from tracking
        if (isset($this->users[$conn->resourceId])) {
            $user = $this->users[$conn->resourceId];
            unset($this->users[$conn->resourceId]);
            
            // Notify rooms about user disconnect
            $this->notifyRooms($user['user_id'], [
                'type' => 'user_disconnected',
                'user_id' => $user['user_id'],
                'role' => $user['role']
            ]);
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Handle user authentication
     */
    private function handleAuth(ConnectionInterface $conn, $data) {
        if (!isset($data['token']) || !isset($data['user_id'])) {
            $this->sendError($conn, 'Missing authentication data');
            return;
        }
        
        // Verify JWT token (simplified for demo)
        try {
            // In production, properly verify JWT token
            $userId = (int)$data['user_id'];
            
            // Get user from database
            $stmt = $this->db->prepare("SELECT id, username, role FROM users WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->sendError($conn, 'Invalid user');
                return;
            }
            
            // Store user connection
            $this->users[$conn->resourceId] = [
                'connection' => $conn,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'last_seen' => time()
            ];
            
            $conn->send(json_encode([
                'type' => 'auth_success',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]));
            
            echo "User {$user['username']} ({$user['role']}) authenticated\n";
            
        } catch (Exception $e) {
            $this->sendError($conn, 'Authentication failed');
        }
    }
    
    /**
     * Handle joining an order room for real-time updates
     */
    private function handleJoinOrder(ConnectionInterface $conn, $data) {
        if (!isset($this->users[$conn->resourceId])) {
            $this->sendError($conn, 'Not authenticated');
            return;
        }
        
        if (!isset($data['order_id'])) {
            $this->sendError($conn, 'Missing order_id');
            return;
        }
        
        $orderId = (int)$data['order_id'];
        $user = $this->users[$conn->resourceId];
        
        // Verify user has access to this order
        if (!$this->canAccessOrder($user['user_id'], $user['role'], $orderId)) {
            $this->sendError($conn, 'Access denied to order');
            return;
        }
        
        // Add to room
        $roomKey = "order_{$orderId}";
        if (!isset($this->rooms[$roomKey])) {
            $this->rooms[$roomKey] = [];
        }
        
        $this->rooms[$roomKey][$conn->resourceId] = $conn;
        
        // Send current order status
        $orderStatus = $this->getOrderStatus($orderId);
        $conn->send(json_encode([
            'type' => 'order_status',
            'order_id' => $orderId,
            'status' => $orderStatus
        ]));
        
        echo "User {$user['username']} joined order {$orderId}\n";
    }
    
    /**
     * Handle order status updates
     */
    private function handleOrderUpdate(ConnectionInterface $conn, $data) {
        if (!isset($this->users[$conn->resourceId])) {
            $this->sendError($conn, 'Not authenticated');
            return;
        }
        
        $user = $this->users[$conn->resourceId];
        
        // Only vendors, riders, and admins can update orders
        if (!in_array($user['role'], ['vendor', 'rider', 'admin'])) {
            $this->sendError($conn, 'Permission denied');
            return;
        }
        
        if (!isset($data['order_id']) || !isset($data['status'])) {
            $this->sendError($conn, 'Missing order data');
            return;
        }
        
        $orderId = (int)$data['order_id'];
        $status = $data['status'];
        
        // Update order in database
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            
            // Broadcast to all users in the order room
            $this->broadcastToRoom("order_{$orderId}", [
                'type' => 'order_status_updated',
                'order_id' => $orderId,
                'status' => $status,
                'updated_by' => $user['username'],
                'timestamp' => time()
            ]);
            
            echo "Order {$orderId} status updated to {$status} by {$user['username']}\n";
            
        } catch (Exception $e) {
            $this->sendError($conn, 'Failed to update order');
        }
    }
    
    /**
     * Handle rider location updates
     */
    private function handleLocationUpdate(ConnectionInterface $conn, $data) {
        if (!isset($this->users[$conn->resourceId])) {
            $this->sendError($conn, 'Not authenticated');
            return;
        }
        
        $user = $this->users[$conn->resourceId];
        
        if ($user['role'] !== 'rider') {
            $this->sendError($conn, 'Only riders can send location updates');
            return;
        }
        
        if (!isset($data['order_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
            $this->sendError($conn, 'Missing location data');
            return;
        }
        
        $orderId = (int)$data['order_id'];
        $latitude = (float)$data['latitude'];
        $longitude = (float)$data['longitude'];
        
        // Update delivery location in database
        try {
            $stmt = $this->db->prepare("
                UPDATE deliveries 
                SET current_lat = ?, current_long = ?, updated_at = NOW() 
                WHERE order_id = ? AND rider_id = ?
            ");
            $stmt->execute([$latitude, $longitude, $orderId, $user['user_id']]);
            
            // Broadcast location to order room
            $this->broadcastToRoom("order_{$orderId}", [
                'type' => 'rider_location',
                'order_id' => $orderId,
                'rider_id' => $user['user_id'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->sendError($conn, 'Failed to update location');
        }
    }
    
    /**
     * Handle chat messages
     */
    private function handleChatMessage(ConnectionInterface $conn, $data) {
        if (!isset($this->users[$conn->resourceId])) {
            $this->sendError($conn, 'Not authenticated');
            return;
        }
        
        $user = $this->users[$conn->resourceId];
        
        if (!isset($data['order_id']) || !isset($data['message'])) {
            $this->sendError($conn, 'Missing message data');
            return;
        }
        
        $orderId = (int)$data['order_id'];
        $message = trim($data['message']);
        
        if (empty($message)) {
            $this->sendError($conn, 'Empty message');
            return;
        }
        
        // Save message to database
        try {
            $stmt = $this->db->prepare("
                INSERT INTO messages (sender_id, order_id, message, timestamp) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['user_id'], $orderId, $message]);
            
            // Broadcast message to order room
            $this->broadcastToRoom("order_{$orderId}", [
                'type' => 'chat_message',
                'order_id' => $orderId,
                'sender_id' => $user['user_id'],
                'sender_name' => $user['username'],
                'sender_role' => $user['role'],
                'message' => $message,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->sendError($conn, 'Failed to send message');
        }
    }
    
    /**
     * Handle heartbeat to keep connection alive
     */
    private function handleHeartbeat(ConnectionInterface $conn) {
        if (isset($this->users[$conn->resourceId])) {
            $this->users[$conn->resourceId]['last_seen'] = time();
        }
        
        $conn->send(json_encode([
            'type' => 'heartbeat_ack',
            'timestamp' => time()
        ]));
    }
    
    /**
     * Send error message to connection
     */
    private function sendError(ConnectionInterface $conn, $message) {
        $conn->send(json_encode([
            'type' => 'error',
            'message' => $message
        ]));
    }
    
    /**
     * Broadcast message to all connections in a room
     */
    private function broadcastToRoom($roomKey, $data) {
        if (!isset($this->rooms[$roomKey])) {
            return;
        }
        
        $message = json_encode($data);
        foreach ($this->rooms[$roomKey] as $conn) {
            $conn->send($message);
        }
    }
    
    /**
     * Notify all rooms a user is in
     */
    private function notifyRooms($userId, $data) {
        foreach ($this->rooms as $roomKey => $connections) {
            foreach ($connections as $connId => $conn) {
                if (isset($this->users[$connId]) && $this->users[$connId]['user_id'] == $userId) {
                    $this->broadcastToRoom($roomKey, $data);
                    break;
                }
            }
        }
    }
    
    /**
     * Check if user can access order
     */
    private function canAccessOrder($userId, $role, $orderId) {
        try {
            switch ($role) {
                case 'admin':
                    return true;
                    
                case 'customer':
                    $stmt = $this->db->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
                    $stmt->execute([$orderId, $userId]);
                    return $stmt->fetch() !== false;
                    
                case 'vendor':
                    $stmt = $this->db->prepare("
                        SELECT o.id FROM orders o 
                        JOIN restaurants r ON o.restaurant_id = r.id 
                        WHERE o.id = ? AND r.user_id = ?
                    ");
                    $stmt->execute([$orderId, $userId]);
                    return $stmt->fetch() !== false;
                    
                case 'rider':
                    $stmt = $this->db->prepare("
                        SELECT id FROM deliveries WHERE order_id = ? AND rider_id = ?
                    ");
                    $stmt->execute([$orderId, $userId]);
                    return $stmt->fetch() !== false;
                    
                default:
                    return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get current order status
     */
    private function getOrderStatus($orderId) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, r.name as restaurant_name, d.rider_id, d.current_lat, d.current_long
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN deliveries d ON o.id = d.order_id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}

// Load WebSocket configuration
$config = include __DIR__ . '/../config/websocket.php';

echo "🚀 Starting Time2Eat WebSocket Server\n";
echo "=====================================\n";
echo "Host: {$config['host']}\n";
echo "Port: {$config['port']}\n";
echo "Max Connections: {$config['max_connections']}\n\n";

// Create and start server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Time2EatWebSocket()
        )
    ),
    $config['port'],
    $config['host']
);

echo "✓ WebSocket server started successfully!\n";
echo "Connect to: ws://{$config['host']}:{$config['port']}\n\n";
echo "Press Ctrl+C to stop the server\n";

$server->run();
