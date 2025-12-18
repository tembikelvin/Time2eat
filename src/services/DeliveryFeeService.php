<?php

declare(strict_types=1);

namespace services;

/**
 * Delivery Fee Calculation Service
 * 
 * Calculates delivery fees based on distance from restaurant to customer
 * Admin controls:
 * - Base delivery fee (within free delivery zone)
 * - Free delivery zone radius (in km)
 * - Extra fee per km beyond free zone
 */
class DeliveryFeeService
{
    /**
     * Calculate delivery fee based on distance and restaurant settings
     *
     * @param float $distance Distance in kilometers
     * @param array $restaurant Restaurant data with delivery settings
     * @param float $orderSubtotal Order subtotal for free delivery threshold
     * @return array Delivery fee breakdown
     */
    public function calculateDeliveryFee(float $distance, array $restaurant, float $orderSubtotal = 0): array
    {
        // Get restaurant delivery settings
        $baseFee = (float)($restaurant['delivery_fee'] ?? 500);
        $freeZoneRadius = (float)($restaurant['delivery_radius'] ?? 10);
        $extraFeePerKm = (float)($restaurant['delivery_fee_per_extra_km'] ?? 100);

        // Get free delivery threshold from admin settings (0 = disabled)
        $freeDeliveryThreshold = $this->getFreeDeliveryThreshold();
        
        // Check if order qualifies for free delivery (only if threshold > 0)
        if ($freeDeliveryThreshold > 0 && $orderSubtotal >= $freeDeliveryThreshold) {
            return [
                'base_fee' => $baseFee,
                'extra_fee' => 0,
                'total_fee' => 0,
                'distance' => round($distance, 2),
                'free_zone_radius' => $freeZoneRadius,
                'is_free_delivery' => true,
                'free_delivery_reason' => 'Order above ' . number_format($freeDeliveryThreshold) . ' XAF',
                'savings' => $this->calculateRawFee($distance, $baseFee, $freeZoneRadius, $extraFeePerKm),
                'within_free_zone' => $distance <= $freeZoneRadius
            ];
        }
        
        // Calculate delivery fee
        $rawFee = $this->calculateRawFee($distance, $baseFee, $freeZoneRadius, $extraFeePerKm);
        
        return [
            'base_fee' => $baseFee,
            'extra_fee' => $rawFee - $baseFee,
            'total_fee' => $rawFee,
            'distance' => round($distance, 2),
            'free_zone_radius' => $freeZoneRadius,
            'is_free_delivery' => false,
            'free_delivery_reason' => null,
            'savings' => 0,
            'within_free_zone' => $distance <= $freeZoneRadius,
            'extra_distance' => max(0, $distance - $freeZoneRadius),
            'extra_fee_per_km' => $extraFeePerKm
        ];
    }
    
    /**
     * Calculate raw delivery fee without free delivery check
     */
    private function calculateRawFee(float $distance, float $baseFee, float $freeZoneRadius, float $extraFeePerKm): float
    {
        // Within free zone: base fee only
        if ($distance <= $freeZoneRadius) {
            return $baseFee;
        }
        
        // Beyond free zone: base fee + extra per km
        $extraDistance = $distance - $freeZoneRadius;
        $extraFee = $extraDistance * $extraFeePerKm;
        
        return $baseFee + $extraFee;
    }
    
    /**
     * Calculate distance between two coordinates using Haversine formula
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return round($distance, 2);
    }
    
    /**
     * Check if delivery address is within restaurant's delivery zone
     * 
     * @param array $restaurant Restaurant data
     * @param float $customerLat Customer latitude
     * @param float $customerLon Customer longitude
     * @return array Result with distance and availability
     */
    public function checkDeliveryAvailability(array $restaurant, float $customerLat, float $customerLon): array
    {
        $restaurantLat = (float)($restaurant['latitude'] ?? 0);
        $restaurantLon = (float)($restaurant['longitude'] ?? 0);
        
        if ($restaurantLat == 0 || $restaurantLon == 0) {
            return [
                'available' => false,
                'reason' => 'Restaurant location not set',
                'distance' => null
            ];
        }
        
        $distance = $this->calculateDistance($restaurantLat, $restaurantLon, $customerLat, $customerLon);
        $maxDeliveryDistance = (float)($restaurant['delivery_radius'] ?? 10) * 2; // Allow up to 2x the free zone
        
        if ($distance > $maxDeliveryDistance) {
            return [
                'available' => false,
                'reason' => 'Outside delivery zone',
                'distance' => $distance,
                'max_distance' => $maxDeliveryDistance
            ];
        }
        
        return [
            'available' => true,
            'distance' => $distance,
            'max_distance' => $maxDeliveryDistance
        ];
    }
    
    /**
     * Calculate rider earnings from delivery fee
     * Rider gets 70% of the delivery fee
     * 
     * @param float $deliveryFee Total delivery fee
     * @return array Rider earnings breakdown
     */
    public function calculateRiderEarnings(float $deliveryFee): array
    {
        $riderSharePercentage = 0.70; // 70% goes to rider
        $platformSharePercentage = 0.30; // 30% goes to platform
        
        $riderEarnings = $deliveryFee * $riderSharePercentage;
        $platformShare = $deliveryFee * $platformSharePercentage;
        
        return [
            'total_delivery_fee' => $deliveryFee,
            'rider_earnings' => round($riderEarnings, 2),
            'platform_share' => round($platformShare, 2),
            'rider_percentage' => $riderSharePercentage * 100,
            'platform_percentage' => $platformSharePercentage * 100
        ];
    }
    
    /**
     * Get delivery fee estimate for display (before exact address is known)
     * Shows range based on free zone
     * 
     * @param array $restaurant Restaurant data
     * @return array Delivery fee estimate
     */
    public function getDeliveryFeeEstimate(array $restaurant): array
    {
        $baseFee = (float)($restaurant['delivery_fee'] ?? 500);
        $freeZoneRadius = (float)($restaurant['delivery_radius'] ?? 10);
        $extraFeePerKm = (float)($restaurant['delivery_fee_per_extra_km'] ?? 100);
        
        // Calculate fee at edge of free zone
        $minFee = $baseFee;
        
        // Calculate fee at 2x the free zone (max delivery distance)
        $maxDistance = $freeZoneRadius * 2;
        $maxFee = $this->calculateRawFee($maxDistance, $baseFee, $freeZoneRadius, $extraFeePerKm);
        
        return [
            'min_fee' => $minFee,
            'max_fee' => $maxFee,
            'free_zone_radius' => $freeZoneRadius,
            'base_fee' => $baseFee,
            'extra_fee_per_km' => $extraFeePerKm,
            'display_text' => $minFee == $maxFee
                ? number_format($minFee) . ' XAF'
                : number_format($minFee) . ' - ' . number_format($maxFee) . ' XAF',
            'free_delivery_threshold' => $this->getFreeDeliveryThreshold()
        ];
    }

    /**
     * Get free delivery threshold from admin settings
     * Returns 0 if free delivery is disabled (admin sets 0 or empty)
     *
     * @return float Free delivery threshold in XAF (0 = no free delivery)
     */
    private function getFreeDeliveryThreshold(): float
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            $db = dbConnection();

            $stmt = $db->prepare("SELECT `value` FROM site_settings WHERE `key` = 'free_delivery_threshold'");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && isset($result['value'])) {
                $value = (float)$result['value'];
                // Return 0 if admin set 0 or empty (no free delivery)
                return $value;
            }

            // Default to 0 (no free delivery) if setting not found
            return 0;
        } catch (\Exception $e) {
            // If there's an error, return 0 (no free delivery)
            return 0;
        }
    }
}

