<?php
/**
 * Order Completion Service
 * Handles order completion and commission calculation
 */

class OrderCompletionService
{
    private $db;
    
    public function __construct($database)
    {
        $this->db = $database;
    }
    
    /**
     * Complete an order and calculate commissions
     */
    public function completeOrder(int $orderId): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Update order status to delivered
            $this->db->query("UPDATE orders SET status = 'delivered', updated_at = NOW() WHERE id = ?", [$orderId]);
            
            // Calculate affiliate commission
            $this->calculateAffiliateCommission($orderId);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Order completion error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate affiliate commission for an order
     */
    private function calculateAffiliateCommission(int $orderId): void
    {
        // Get order details
        $order = $this->db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            return;
        }
        
        // Get customer's referrer
        $customer = $this->db->fetchOne("SELECT referred_by FROM users WHERE id = ?", [$order['customer_id']]);
        if (!$customer || !$customer['referred_by']) {
            return; // Customer was not referred
        }
        
        // Get affiliate details
        $affiliate = $this->db->fetchOne("
            SELECT id, commission_rate, affiliate_code 
            FROM affiliates 
            WHERE user_id = ? AND status = 'active'
        ", [$customer['referred_by']]);
        
        if (!$affiliate) {
            return; // Referrer is not an active affiliate
        }
        
        // Calculate commission
        $commissionAmount = round($order['subtotal'] * $affiliate['commission_rate'], 2);
        
        // Update order with commission
        $this->db->query("
            UPDATE orders 
            SET affiliate_commission = ?, affiliate_code = ?
            WHERE id = ?
        ", [$commissionAmount, $affiliate['affiliate_code'], $orderId]);
        
        // Record earning
        $this->db->query("
            INSERT INTO affiliate_earnings (
                affiliate_id, order_id, customer_id, amount, type, status, earned_at
            ) VALUES (?, ?, ?, ?, 'referral', 'confirmed', NOW())
            ON DUPLICATE KEY UPDATE
                amount = VALUES(amount),
                status = 'confirmed',
                updated_at = NOW()
        ", [$affiliate['id'], $orderId, $order['customer_id'], $commissionAmount]);
        
        // Update affiliate balance
        $this->db->query("
            UPDATE affiliates 
            SET total_earnings = total_earnings + ?,
                pending_earnings = pending_earnings + ?,
                updated_at = NOW()
            WHERE id = ?
        ", [$commissionAmount, $commissionAmount, $affiliate['id']]);
        
        // Update referral record
        $this->db->query("
            UPDATE affiliate_referrals 
            SET order_id = ?,
                commission_amount = commission_amount + ?,
                status = 'confirmed',
                updated_at = NOW()
            WHERE affiliate_id = ? AND referred_user_id = ?
        ", [$orderId, $commissionAmount, $affiliate['id'], $order['customer_id']]);
    }
}