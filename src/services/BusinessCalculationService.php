<?php

declare(strict_types=1);

namespace services;

/**
 * Business Calculation Service
 * Centralized service for all financial calculations in Time2Eat platform
 * Ensures consistency and accuracy across all business logic
 */
class BusinessCalculationService
{
    // Business Constants (Cameroon Market)
    private const PLATFORM_COMMISSION_RATE = 0.15; // 15% platform commission
    private const SERVICE_FEE_RATE = 0.025; // 2.5% service fee
    private const RIDER_SHARE_RATE = 0.70; // 70% of delivery fee goes to rider
    private const AFFILIATE_COMMISSION_RATE = 0.05; // 5% affiliate commission
    private const PAYMENT_PROCESSING_FEE = 0.025; // 2.5% payment processing fee
    
    // Delivery Fee Structure
    private const BASE_DELIVERY_FEE = 500; // XAF for 0-3km
    private const FREE_DELIVERY_DISTANCE = 3; // km
    private const ADDITIONAL_FEE_PER_KM = 100; // XAF per km beyond 3km
    private const MAX_DELIVERY_DISTANCE = 15; // km
    private const FREE_DELIVERY_THRESHOLD = 5000; // XAF order value for free delivery
    
    // Rider Earnings Structure
    private const RIDER_BASE_EARNINGS = 350; // XAF (70% of base delivery fee)
    private const RIDER_DISTANCE_BONUS = 70; // XAF per km (70% of distance fee)
    private const MINIMUM_RIDER_EARNINGS = 350; // XAF
    
    /**
     * Calculate delivery fee based on distance
     * 
     * @deprecated This method uses hardcoded values and doesn't respect restaurant-specific settings.
     * Use DeliveryFeeService::calculateDeliveryFee() instead for restaurant-specific calculations.
     * This method is kept for backward compatibility and general calculations only.
     * 
     * For order placement and restaurant-specific fees, always use:
     * \services\DeliveryFeeService::calculateDeliveryFee($distance, $restaurantData, $orderSubtotal)
     */
    public function calculateDeliveryFee(float $distance, float $orderSubtotal = 0): array
    {
        // Validate distance
        if ($distance < 0) {
            throw new \InvalidArgumentException('Distance cannot be negative');
        }
        
        if ($distance > self::MAX_DELIVERY_DISTANCE) {
            throw new \InvalidArgumentException('Distance exceeds maximum delivery range');
        }
        
        // Check for free delivery
        $isFreeDelivery = $orderSubtotal >= self::FREE_DELIVERY_THRESHOLD;
        
        // Calculate base fee
        $deliveryFee = self::BASE_DELIVERY_FEE;
        
        // Add distance-based fee
        if ($distance > self::FREE_DELIVERY_DISTANCE) {
            $additionalDistance = $distance - self::FREE_DELIVERY_DISTANCE;
            $deliveryFee += $additionalDistance * self::ADDITIONAL_FEE_PER_KM;
        }
        
        return [
            'base_fee' => self::BASE_DELIVERY_FEE,
            'distance_fee' => $deliveryFee - self::BASE_DELIVERY_FEE,
            'total_fee' => $isFreeDelivery ? 0 : $deliveryFee,
            'original_fee' => $deliveryFee,
            'is_free_delivery' => $isFreeDelivery,
            'distance' => $distance,
            'savings' => $isFreeDelivery ? $deliveryFee : 0
        ];
    }
    
    /**
     * Calculate rider earnings for a delivery
     */
    public function calculateRiderEarnings(float $distance, float $deliveryFee): array
    {
        // Base earnings (70% of base delivery fee)
        $baseEarnings = self::RIDER_BASE_EARNINGS;
        
        // Distance bonus (70% of distance fee, only for distance > 3km)
        $distanceBonus = 0;
        if ($distance > self::FREE_DELIVERY_DISTANCE) {
            $additionalDistance = $distance - self::FREE_DELIVERY_DISTANCE;
            $distanceBonus = $additionalDistance * self::RIDER_DISTANCE_BONUS;
        }
        
        $totalEarnings = $baseEarnings + $distanceBonus;
        
        // Ensure minimum earnings
        $totalEarnings = max($totalEarnings, self::MINIMUM_RIDER_EARNINGS);
        
        return [
            'base_earnings' => $baseEarnings,
            'distance_bonus' => $distanceBonus,
            'total_earnings' => $totalEarnings,
            'distance' => $distance,
            'delivery_fee' => $deliveryFee,
            'platform_share' => $deliveryFee - $totalEarnings
        ];
    }
    
    /**
     * Calculate order totals with all fees (tax removed)
     */
    public function calculateOrderTotals(float $subtotal, float $distance, array $options = []): array
    {
        if ($subtotal < 0) {
            throw new \InvalidArgumentException('Subtotal cannot be negative');
        }

        // Calculate delivery fee
        $deliveryCalculation = $this->calculateDeliveryFee($distance, $subtotal);
        $deliveryFee = $deliveryCalculation['total_fee'];

        // Calculate service fee (on subtotal only)
        $serviceFee = $subtotal * self::SERVICE_FEE_RATE;

        // Apply discount if provided
        $discount = $options['discount'] ?? 0;
        $discountedSubtotal = max(0, $subtotal - $discount);

        // Recalculate fees based on discounted subtotal
        if ($discount > 0) {
            $serviceFee = $discountedSubtotal * self::SERVICE_FEE_RATE;

            // Recalculate delivery fee for free delivery threshold
            $deliveryCalculation = $this->calculateDeliveryFee($distance, $discountedSubtotal);
            $deliveryFee = $deliveryCalculation['total_fee'];
        }

        $totalAmount = $discountedSubtotal + $serviceFee + $deliveryFee;

        return [
            'subtotal' => $subtotal,
            'discounted_subtotal' => $discountedSubtotal,
            'discount' => $discount,
            'service_fee' => round($serviceFee, 0),
            'tax' => 0, // Tax removed - kept for backward compatibility
            'tax_rate' => 0,
            'delivery_fee' => $deliveryFee,
            'delivery_details' => $deliveryCalculation,
            'total_amount' => round($totalAmount, 0),
            'breakdown' => [
                'food_cost' => $discountedSubtotal,
                'service_fee' => round($serviceFee, 0),
                'tax' => 0,
                'delivery' => $deliveryFee
            ]
        ];
    }
    
    /**
     * Calculate platform commission and restaurant earnings
     */
    public function calculateCommissions(float $subtotal, ?float $customCommissionRate = null): array
    {
        $commissionRate = $customCommissionRate ?? self::PLATFORM_COMMISSION_RATE;
        
        if ($commissionRate < 0 || $commissionRate > 1) {
            throw new \InvalidArgumentException('Commission rate must be between 0 and 1');
        }
        
        $platformCommission = $subtotal * $commissionRate;
        $restaurantEarnings = $subtotal - $platformCommission;
        
        return [
            'subtotal' => $subtotal,
            'commission_rate' => $commissionRate,
            'platform_commission' => round($platformCommission, 2),
            'restaurant_earnings' => round($restaurantEarnings, 2),
            'commission_percentage' => $commissionRate * 100
        ];
    }
    
    /**
     * Calculate affiliate commission
     */
    public function calculateAffiliateCommission(float $subtotal, ?float $customRate = null): array
    {
        $commissionRate = $customRate ?? self::AFFILIATE_COMMISSION_RATE;
        $commission = $subtotal * $commissionRate;
        
        return [
            'subtotal' => $subtotal,
            'commission_rate' => $commissionRate,
            'commission_amount' => round($commission, 2),
            'commission_percentage' => $commissionRate * 100
        ];
    }
    
    /**
     * Calculate platform profit for an order
     */
    public function calculatePlatformProfit(array $orderData): array
    {
        $subtotal = $orderData['subtotal'] ?? 0;
        $deliveryFee = $orderData['delivery_fee'] ?? 0;
        $distance = $orderData['distance'] ?? 0;
        $affiliateCommission = $orderData['affiliate_commission'] ?? 0;
        $customCommissionRate = $orderData['commission_rate'] ?? null;
        
        // Calculate revenue sources
        $commissionData = $this->calculateCommissions($subtotal, $customCommissionRate);
        $platformCommission = $commissionData['platform_commission'];
        
        $serviceFee = $subtotal * self::SERVICE_FEE_RATE;
        $deliveryProfit = $deliveryFee * (1 - self::RIDER_SHARE_RATE); // 30% of delivery fee
        
        $totalRevenue = $platformCommission + $serviceFee + $deliveryProfit;
        
        // Calculate costs
        $riderEarnings = $this->calculateRiderEarnings($distance, $deliveryFee);
        $riderCost = $riderEarnings['total_earnings'];
        
        $paymentProcessingFee = ($subtotal + $deliveryFee) * self::PAYMENT_PROCESSING_FEE;
        
        $totalCosts = $riderCost + $affiliateCommission + $paymentProcessingFee;
        
        // Calculate profit
        $grossProfit = $totalRevenue - $totalCosts;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        return [
            'revenue' => [
                'platform_commission' => round($platformCommission, 2),
                'service_fee' => round($serviceFee, 2),
                'delivery_profit' => round($deliveryProfit, 2),
                'total_revenue' => round($totalRevenue, 2)
            ],
            'costs' => [
                'rider_earnings' => round($riderCost, 2),
                'affiliate_commission' => round($affiliateCommission, 2),
                'payment_processing' => round($paymentProcessingFee, 2),
                'total_costs' => round($totalCosts, 2)
            ],
            'profit' => [
                'gross_profit' => round($grossProfit, 2),
                'profit_margin' => round($profitMargin, 2),
                'profit_per_order' => round($grossProfit, 2)
            ],
            'breakdown' => [
                'order_value' => $subtotal + $deliveryFee,
                'platform_share' => round($grossProfit, 2),
                'restaurant_share' => $commissionData['restaurant_earnings'],
                'rider_share' => round($riderCost, 2)
            ]
        ];
    }
    
    /**
     * Validate business rules for an order
     */
    public function validateOrderBusinessRules(array $orderData): array
    {
        $errors = [];
        $warnings = [];
        
        $subtotal = $orderData['subtotal'] ?? 0;
        $distance = $orderData['distance'] ?? 0;
        
        // Minimum order validation
        if ($subtotal < 1000) { // 1000 XAF minimum
            $errors[] = 'Order subtotal must be at least 1,000 XAF';
        }
        
        // Maximum distance validation
        if ($distance > self::MAX_DELIVERY_DISTANCE) {
            $errors[] = "Delivery distance cannot exceed " . self::MAX_DELIVERY_DISTANCE . " km";
        }
        
        // Profit margin warning
        $profitData = $this->calculatePlatformProfit($orderData);
        if ($profitData['profit']['profit_margin'] < 5) {
            $warnings[] = 'Low profit margin on this order';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'profit_analysis' => $profitData
        ];
    }
    
    /**
     * Get business configuration constants
     */
    public function getBusinessConfig(): array
    {
        return [
            'vat_rate' => 0, // Tax removed
            'platform_commission_rate' => self::PLATFORM_COMMISSION_RATE,
            'service_fee_rate' => self::SERVICE_FEE_RATE,
            'rider_share_rate' => self::RIDER_SHARE_RATE,
            'affiliate_commission_rate' => self::AFFILIATE_COMMISSION_RATE,
            'base_delivery_fee' => self::BASE_DELIVERY_FEE,
            'additional_fee_per_km' => self::ADDITIONAL_FEE_PER_KM,
            'max_delivery_distance' => self::MAX_DELIVERY_DISTANCE,
            'free_delivery_threshold' => self::FREE_DELIVERY_THRESHOLD,
            'minimum_rider_earnings' => self::MINIMUM_RIDER_EARNINGS
        ];
    }
}
