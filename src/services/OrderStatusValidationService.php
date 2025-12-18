<?php

declare(strict_types=1);

namespace services;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

/**
 * Order Status Validation Service
 * Handles order status transitions with proper role-based permissions and validation
 */
class OrderStatusValidationService
{
    use DatabaseTrait;
    
    protected ?\PDO $db = null;
    
    // Define valid status transitions based on user roles
    private const STATUS_TRANSITIONS = [
        'pending' => [
            'roles' => ['vendor', 'admin'],
            'next' => ['confirmed', 'preparing', 'cancelled'],
            'description' => 'Order is waiting for restaurant confirmation'
        ],
        'confirmed' => [
            'roles' => ['vendor', 'admin'],
            'next' => ['preparing', 'cancelled'],
            'description' => 'Restaurant has confirmed the order'
        ],
        'preparing' => [
            'roles' => ['vendor', 'admin'],
            'next' => ['ready', 'cancelled'],
            'description' => 'Restaurant is preparing the food'
        ],
        'ready' => [
            'roles' => ['rider', 'admin'],
            'next' => ['picked_up', 'cancelled'],
            'description' => 'Food is ready for pickup by rider'
        ],
        'picked_up' => [
            'roles' => ['rider', 'admin'],
            'next' => ['on_the_way'],
            'description' => 'Rider has picked up the order'
        ],
        'on_the_way' => [
            'roles' => ['rider', 'admin'],
            'next' => ['delivered'],
            'description' => 'Rider is on the way to customer'
        ],
        'delivered' => [
            'roles' => ['customer', 'admin'],
            'next' => [],
            'description' => 'Order has been delivered successfully'
        ],
        'cancelled' => [
            'roles' => ['vendor', 'admin', 'customer'],
            'next' => [],
            'description' => 'Order has been cancelled'
        ]
    ];
    
    /**
     * Validate if a user can update an order to a specific status
     */
    public function canUpdateStatus(string $currentStatus, string $newStatus, string $userRole, int $orderId = null): array
    {
        // Check if status transition is valid
        if (!isset(self::STATUS_TRANSITIONS[$currentStatus])) {
            return [
                'valid' => false,
                'message' => 'Invalid current status',
                'error_code' => 'INVALID_CURRENT_STATUS'
            ];
        }
        
        if (!isset(self::STATUS_TRANSITIONS[$newStatus])) {
            return [
                'valid' => false,
                'message' => 'Invalid target status',
                'error_code' => 'INVALID_TARGET_STATUS'
            ];
        }
        
        // Check if transition is allowed
        $currentStatusConfig = self::STATUS_TRANSITIONS[$currentStatus];
        if (!in_array($newStatus, $currentStatusConfig['next'])) {
            return [
                'valid' => false,
                'message' => "Cannot transition from {$currentStatus} to {$newStatus}",
                'error_code' => 'INVALID_TRANSITION'
            ];
        }
        
        // Check if user role can perform this transition
        $targetStatusConfig = self::STATUS_TRANSITIONS[$newStatus];
        if (!in_array($userRole, $targetStatusConfig['roles'])) {
            return [
                'valid' => false,
                'message' => "User role '{$userRole}' cannot update status to '{$newStatus}'",
                'error_code' => 'INSUFFICIENT_PERMISSIONS'
            ];
        }
        
        // Additional business logic validations
        $businessValidation = $this->validateBusinessRules($currentStatus, $newStatus, $userRole, $orderId);
        if (!$businessValidation['valid']) {
            return $businessValidation;
        }
        
        return [
            'valid' => true,
            'message' => 'Status update is valid',
            'error_code' => null
        ];
    }
    
    /**
     * Get available status transitions for a user role and current status
     */
    public function getAvailableTransitions(string $currentStatus, string $userRole): array
    {
        if (!isset(self::STATUS_TRANSITIONS[$currentStatus])) {
            return [];
        }
        
        $currentConfig = self::STATUS_TRANSITIONS[$currentStatus];
        $availableTransitions = [];
        
        foreach ($currentConfig['next'] as $nextStatus) {
            if (isset(self::STATUS_TRANSITIONS[$nextStatus])) {
                $nextConfig = self::STATUS_TRANSITIONS[$nextStatus];
                if (in_array($userRole, $nextConfig['roles'])) {
                    $availableTransitions[] = [
                        'status' => $nextStatus,
                        'description' => $nextConfig['description'],
                        'label' => $this->getStatusLabel($nextStatus)
                    ];
                }
            }
        }
        
        return $availableTransitions;
    }
    
    /**
     * Validate business rules for specific status transitions
     */
    private function validateBusinessRules(string $currentStatus, string $newStatus, string $userRole, int $orderId = null): array
    {
        // Special validation for delivery confirmation
        if ($newStatus === 'delivered' && $userRole === 'customer') {
            // Customer can only confirm delivery if order is on_the_way
            if ($currentStatus !== 'on_the_way') {
                return [
                    'valid' => false,
                    'message' => 'Customer can only confirm delivery when order is on the way',
                    'error_code' => 'INVALID_DELIVERY_CONFIRMATION'
                ];
            }
        }
        
        // Validation for rider actions
        if ($userRole === 'rider') {
            if ($newStatus === 'picked_up' && $currentStatus !== 'ready') {
                return [
                    'valid' => false,
                    'message' => 'Rider can only pick up orders that are ready',
                    'error_code' => 'INVALID_PICKUP_STATUS'
                ];
            }
            
            if ($newStatus === 'on_the_way' && $currentStatus !== 'picked_up') {
                return [
                    'valid' => false,
                    'message' => 'Rider can only start delivery after picking up the order',
                    'error_code' => 'INVALID_DELIVERY_START'
                ];
            }
        }
        
        // Validation for vendor actions
        if ($userRole === 'vendor') {
            // Allow preparing from both pending and confirmed statuses
            if ($newStatus === 'preparing' && !in_array($currentStatus, ['pending', 'confirmed'])) {
                return [
                    'valid' => false,
                    'message' => 'Vendor can only start preparing from pending or confirmed status',
                    'error_code' => 'INVALID_PREPARATION_START'
                ];
            }

            if ($newStatus === 'ready' && $currentStatus !== 'preparing') {
                return [
                    'valid' => false,
                    'message' => 'Vendor can only mark ready after starting preparation',
                    'error_code' => 'INVALID_READY_STATUS'
                ];
            }
        }
        
        // Check if order exists and belongs to the user (if orderId provided)
        if ($orderId) {
            $orderValidation = $this->validateOrderOwnership($orderId, $userRole);
            if (!$orderValidation['valid']) {
                return $orderValidation;
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate that the user has permission to modify the specific order
     */
    private function validateOrderOwnership(int $orderId, string $userRole): array
    {
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("
                SELECT o.*, r.vendor_id, o.customer_id, o.rider_id
                FROM orders o
                LEFT JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return [
                    'valid' => false,
                    'message' => 'Order not found',
                    'error_code' => 'ORDER_NOT_FOUND'
                ];
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return [
                    'valid' => false,
                    'message' => 'User not authenticated',
                    'error_code' => 'NOT_AUTHENTICATED'
                ];
            }
            
            // Check role-specific ownership
            switch ($userRole) {
                case 'vendor':
                    if ($order['vendor_id'] != $userId) {
                        return [
                            'valid' => false,
                            'message' => 'Order does not belong to this vendor',
                            'error_code' => 'ORDER_NOT_OWNED'
                        ];
                    }
                    break;
                    
                case 'rider':
                    if ($order['rider_id'] != $userId) {
                        return [
                            'valid' => false,
                            'message' => 'Order is not assigned to this rider',
                            'error_code' => 'ORDER_NOT_ASSIGNED'
                        ];
                    }
                    break;
                    
                case 'customer':
                    if ($order['customer_id'] != $userId) {
                        return [
                            'valid' => false,
                            'message' => 'Order does not belong to this customer',
                            'error_code' => 'ORDER_NOT_OWNED'
                        ];
                    }
                    break;
                    
                case 'admin':
                    // Admin can modify any order
                    break;
                    
                default:
                    return [
                        'valid' => false,
                        'message' => 'Invalid user role',
                        'error_code' => 'INVALID_ROLE'
                    ];
            }
            
            return ['valid' => true];
            
        } catch (\Exception $e) {
            error_log("Order ownership validation error: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Error validating order ownership',
                'error_code' => 'VALIDATION_ERROR'
            ];
        }
    }
    
    /**
     * Get human-readable status label
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready for Pickup',
            'picked_up' => 'Picked Up',
            'on_the_way' => 'On the Way',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
    
    /**
     * Get all possible statuses for a user role
     */
    public function getStatusesForRole(string $userRole): array
    {
        $statuses = [];
        
        foreach (self::STATUS_TRANSITIONS as $status => $config) {
            if (in_array($userRole, $config['roles'])) {
                $statuses[] = [
                    'status' => $status,
                    'label' => $this->getStatusLabel($status),
                    'description' => $config['description']
                ];
            }
        }
        
        return $statuses;
    }
    
    /**
     * Check if a status is terminal (no further transitions possible)
     */
    public function isTerminalStatus(string $status): bool
    {
        return isset(self::STATUS_TRANSITIONS[$status]) && 
               empty(self::STATUS_TRANSITIONS[$status]['next']);
    }
    
    /**
     * Get the complete status flow for display
     */
    public function getStatusFlow(): array
    {
        return self::STATUS_TRANSITIONS;
    }
}
