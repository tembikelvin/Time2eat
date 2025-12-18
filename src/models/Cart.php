<?php

namespace Time2Eat\Models;

require_once __DIR__ . '/../traits/DatabaseTrait.php';

use traits\DatabaseTrait;

class Cart
{
    use DatabaseTrait;
    protected ?\PDO $db = null;
    protected $table = 'cart_items';
    protected $fillable = [
        'user_id', 'menu_item_id', 'quantity', 'customizations', 
        'special_instructions', 'unit_price', 'total_price'
    ];

    public function getCartByUser(int $userId): array
    {
        $sql = "
            SELECT 
                ci.*,
                mi.name as item_name,
                mi.description as item_description,
                mi.image as item_image,
                mi.category_id,
                mi.is_available,
                r.id as restaurant_id,
                r.name as restaurant_name,
                r.delivery_fee,
                r.minimum_order,
                r.delivery_time,
                COALESCE(c.name, 'Uncategorized') as category_name
            FROM {$this->table} ci
            JOIN menu_items mi ON ci.menu_item_id = mi.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            LEFT JOIN categories c ON mi.category_id = c.id
            WHERE ci.user_id = ? AND mi.is_available = 1 AND r.status = 'active'
            ORDER BY ci.created_at DESC
        ";

        return $this->fetchAll($sql, [$userId]);
    }

    public function addToCart(array $data): ?int
    {
        // Check if item already exists in cart
        $existingItem = $this->getCartItem($data['user_id'], $data['menu_item_id'], $data['customizations'] ?? '{}');
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $data['quantity'];
            $newTotalPrice = $newQuantity * $data['unit_price'];
            
            return $this->updateCartItem($existingItem['id'], [
                'quantity' => $newQuantity,
                'total_price' => $newTotalPrice,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Add new item
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insertRecord($this->table, $data);
    }

    public function updateCartItem(int $cartItemId, array $data): bool
    {
        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['total_price'] = $data['quantity'] * $data['unit_price'];
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->updateRecord($this->table, $data, ['id' => $cartItemId]);
    }

    public function removeFromCart(int $cartItemId, int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        return $this->query($sql, [$cartItemId, $userId])->rowCount();
    }

    public function clearCart(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        return $this->query($sql, [$userId])->rowCount();
    }

    public function getCartItem(int $userId, int $menuItemId, string $customizations = '{}'): ?array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE user_id = ? AND menu_item_id = ? AND customizations = ?
        ";
        
        return $this->fetchOne($sql, [$userId, $menuItemId, $customizations]);
    }

    public function getCartTotals(int $userId): array
    {
        $cartItems = $this->getCartByUser($userId);
        
        if (empty($cartItems)) {
            return [
                'subtotal' => 0,
                'delivery_fee' => 0,
                'total' => 0,
                'item_count' => 0,
                'restaurants' => []
            ];
        }

        $subtotal = 0;
        $itemCount = 0;
        $restaurants = [];

        foreach ($cartItems as $item) {
            $subtotal += $item['total_price'];
            $itemCount += $item['quantity'];
            
            if (!isset($restaurants[$item['restaurant_id']])) {
                $restaurants[$item['restaurant_id']] = [
                    'id' => $item['restaurant_id'],
                    'name' => $item['restaurant_name'],
                    'delivery_fee' => $item['delivery_fee'],
                    'minimum_order' => $item['minimum_order'],
                    'delivery_time' => $item['delivery_time'],
                    'subtotal' => 0,
                    'item_count' => 0
                ];
            }
            
            $restaurants[$item['restaurant_id']]['subtotal'] += $item['total_price'];
            $restaurants[$item['restaurant_id']]['item_count'] += $item['quantity'];
        }

        // Calculate delivery fees
        $totalDeliveryFee = 0;
        foreach ($restaurants as $restaurant) {
            if ($restaurant['subtotal'] >= $restaurant['minimum_order']) {
                $totalDeliveryFee += $restaurant['delivery_fee'];
            }
        }

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $totalDeliveryFee,
            'total' => $subtotal + $totalDeliveryFee,
            'item_count' => $itemCount,
            'restaurants' => array_values($restaurants)
        ];
    }

    public function validateCartForCheckout(int $userId): array
    {
        $cartItems = $this->getCartByUser($userId);
        $errors = [];

        if (empty($cartItems)) {
            $errors[] = 'Your cart is empty';
            return ['valid' => false, 'errors' => $errors];
        }

        $restaurantGroups = [];
        foreach ($cartItems as $item) {
            if (!$item['is_available']) {
                $errors[] = "Item '{$item['item_name']}' is no longer available";
                continue;
            }

            $restaurantId = $item['restaurant_id'];
            if (!isset($restaurantGroups[$restaurantId])) {
                $restaurantGroups[$restaurantId] = [
                    'name' => $item['restaurant_name'],
                    'subtotal' => 0,
                    'minimum_order' => $item['minimum_order']
                ];
            }
            
            $restaurantGroups[$restaurantId]['subtotal'] += $item['total_price'];
        }

        // Check minimum order requirements
        foreach ($restaurantGroups as $restaurant) {
            if ($restaurant['subtotal'] < $restaurant['minimum_order']) {
                $errors[] = "Minimum order for {$restaurant['name']} is " . 
                           number_format($restaurant['minimum_order']) . " XAF";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'restaurant_groups' => $restaurantGroups
        ];
    }

    public function getCartItemCount(int $userId): int
    {
        $sql = "SELECT COALESCE(SUM(quantity), 0) as count FROM {$this->table} WHERE user_id = ?";
        $result = $this->fetchOne($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }

    public function getRecentCartItems(int $userId, int $limit = 5): array
    {
        $sql = "
            SELECT DISTINCT
                mi.id,
                mi.name,
                mi.image,
                mi.price,
                r.name as restaurant_name
            FROM {$this->table} ci
            JOIN menu_items mi ON ci.menu_item_id = mi.id
            JOIN restaurants r ON mi.restaurant_id = r.id
            WHERE ci.user_id = ? AND mi.is_available = 1
            ORDER BY ci.updated_at DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$userId, $limit]);
    }

    public function moveCartToOrder(int $userId, int $orderId): bool
    {
        try {
            $this->beginTransaction();

            $cartItems = $this->getCartByUser($userId);
            
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'customizations' => $item['customizations'],
                    'special_instructions' => $item['special_instructions'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $sql = "
                    INSERT INTO order_items 
                    (order_id, menu_item_id, quantity, unit_price, total_price, customizations, special_instructions, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $this->query($sql, [
                    $orderItemData['order_id'],
                    $orderItemData['menu_item_id'],
                    $orderItemData['quantity'],
                    $orderItemData['unit_price'],
                    $orderItemData['total_price'],
                    $orderItemData['customizations'],
                    $orderItemData['special_instructions'],
                    $orderItemData['created_at'],
                    $orderItemData['updated_at']
                ]);
            }

            // Clear cart
            $this->clearCart($userId);

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    public function applyPromoCode(int $userId, string $promoCode): array
    {
        // Get promo code details
        $sql = "
            SELECT * FROM promo_codes 
            WHERE code = ? AND is_active = 1 
            AND (expires_at IS NULL OR expires_at > NOW())
            AND (usage_limit IS NULL OR usage_count < usage_limit)
        ";
        
        $promo = $this->fetchOne($sql, [$promoCode]);
        
        if (!$promo) {
            return ['valid' => false, 'message' => 'Invalid or expired promo code'];
        }

        // Check if user has already used this promo code
        if ($promo['usage_limit_per_user']) {
            $userUsageSQL = "
                SELECT COUNT(*) as usage_count 
                FROM orders 
                WHERE customer_id = ? AND promo_code = ?
            ";
            $userUsage = $this->fetchOne($userUsageSQL, [$userId, $promoCode]);
            
            if ($userUsage['usage_count'] >= $promo['usage_limit_per_user']) {
                return ['valid' => false, 'message' => 'You have already used this promo code'];
            }
        }

        $cartTotals = $this->getCartTotals($userId);
        
        // Check minimum order requirement
        if ($promo['minimum_order'] && $cartTotals['subtotal'] < $promo['minimum_order']) {
            return [
                'valid' => false, 
                'message' => 'Minimum order of ' . number_format($promo['minimum_order']) . ' XAF required'
            ];
        }

        // Calculate discount
        $discount = 0;
        if ($promo['discount_type'] === 'percentage') {
            $discount = ($cartTotals['subtotal'] * $promo['discount_value']) / 100;
            if ($promo['max_discount'] && $discount > $promo['max_discount']) {
                $discount = $promo['max_discount'];
            }
        } else {
            $discount = $promo['discount_value'];
        }

        return [
            'valid' => true,
            'promo' => $promo,
            'discount' => $discount,
            'message' => 'Promo code applied successfully'
        ];
    }
}
