<?php

declare(strict_types=1);

namespace services;

require_once __DIR__ . '/BusinessCalculationService.php';
require_once __DIR__ . '/DeliveryFeeService.php';

/**
 * Financial Validation Service
 * Validates all financial calculations and ensures business rule compliance
 */
class FinancialValidationService
{
    private BusinessCalculationService $calculator;
    private DeliveryFeeService $deliveryFeeService;
    
    public function __construct()
    {
        $this->calculator = new BusinessCalculationService();
        $this->deliveryFeeService = new DeliveryFeeService();
    }
    
    /**
     * Validate order calculations before processing
     */
    public function validateOrderCalculations(array $orderData): array
    {
        $errors = [];
        $warnings = [];
        $calculations = [];
        
        try {
            // Extract order data
            $subtotal = (float)($orderData['subtotal'] ?? 0);
            $distance = (float)($orderData['distance'] ?? 0);
            $deliveryFee = (float)($orderData['delivery_fee'] ?? 0);
            $tax = (float)($orderData['tax'] ?? 0);
            $totalAmount = (float)($orderData['total_amount'] ?? 0);
            
            // Validate basic inputs
            if ($subtotal <= 0) {
                $errors[] = 'Order subtotal must be greater than 0';
            }
            
            if ($distance < 0) {
                $errors[] = 'Delivery distance cannot be negative';
            }
            
            if ($distance > 15) {
                $errors[] = 'Delivery distance exceeds maximum range (15km)';
            }
            
            // Calculate expected values using standardized formulas
            $expectedTotals = $this->calculator->calculateOrderTotals($subtotal, $distance);
            
            // Validate delivery fee calculation using restaurant-specific settings
            // If restaurant data is provided, use DeliveryFeeService (respects restaurant settings)
            // Otherwise, skip delivery fee validation (cannot validate without restaurant data)
            $restaurantData = $orderData['restaurant'] ?? null;
            if ($restaurantData && is_array($restaurantData)) {
                // Use DeliveryFeeService for restaurant-specific calculations
                $expectedDelivery = $this->deliveryFeeService->calculateDeliveryFee($distance, $restaurantData, $subtotal);
                
                $deliveryFeeTolerance = 50; // 50 XAF tolerance
                if (abs($deliveryFee - $expectedDelivery['total_fee']) > $deliveryFeeTolerance) {
                    $errors[] = sprintf(
                        'Delivery fee mismatch: Expected %.2f XAF, got %.2f XAF',
                        $expectedDelivery['total_fee'],
                        $deliveryFee
                    );
                }
            } else {
                // No restaurant data provided - skip delivery fee validation
                // This is acceptable for general validation, but restaurant-specific validation requires restaurant data
                $warnings[] = 'Delivery fee validation skipped: Restaurant data not provided';
            }

            // Tax validation removed - tax is no longer applied

            // Validate total amount
            $totalTolerance = 100; // 100 XAF tolerance
            if (abs($totalAmount - $expectedTotals['total_amount']) > $totalTolerance) {
                $errors[] = sprintf(
                    'Total amount mismatch: Expected %.2f XAF, got %.2f XAF',
                    $expectedTotals['total_amount'],
                    $totalAmount
                );
            }
            
            // Store calculations for reference
            $calculations = [
                'expected_totals' => $expectedTotals,
                'expected_delivery' => $expectedDelivery,
                'provided_values' => [
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'tax' => $tax,
                    'total_amount' => $totalAmount
                ]
            ];
            
        } catch (\Exception $e) {
            $errors[] = 'Calculation validation failed: ' . $e->getMessage();
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'calculations' => $calculations
        ];
    }
    
    /**
     * Validate rider earnings calculation
     */
    public function validateRiderEarnings(float $distance, float $deliveryFee, float $providedEarnings): array
    {
        $errors = [];
        $warnings = [];
        
        try {
            $expectedEarnings = $this->calculator->calculateRiderEarnings($distance, $deliveryFee);
            $tolerance = 25; // 25 XAF tolerance
            
            if (abs($providedEarnings - $expectedEarnings['total_earnings']) > $tolerance) {
                $errors[] = sprintf(
                    'Rider earnings mismatch: Expected %.2f XAF, got %.2f XAF',
                    $expectedEarnings['total_earnings'],
                    $providedEarnings
                );
            }
            
            // Check minimum earnings
            if ($providedEarnings < 350) {
                $errors[] = 'Rider earnings below minimum threshold (350 XAF)';
            }
            
            // Check if earnings exceed delivery fee (should not happen)
            if ($providedEarnings > $deliveryFee) {
                $warnings[] = 'Rider earnings exceed delivery fee - check calculation';
            }
            
            return [
                'valid' => empty($errors),
                'errors' => $errors,
                'warnings' => $warnings,
                'expected_earnings' => $expectedEarnings,
                'provided_earnings' => $providedEarnings
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Rider earnings validation failed: ' . $e->getMessage()],
                'warnings' => [],
                'expected_earnings' => null,
                'provided_earnings' => $providedEarnings
            ];
        }
    }
    
    /**
     * Validate commission calculations
     */
    public function validateCommissions(float $subtotal, float $providedCommission, ?float $commissionRate = null): array
    {
        $errors = [];
        $warnings = [];
        
        try {
            $expectedCommission = $this->calculator->calculateCommissions($subtotal, $commissionRate);
            $tolerance = 5; // 5 XAF tolerance
            
            if (abs($providedCommission - $expectedCommission['platform_commission']) > $tolerance) {
                $errors[] = sprintf(
                    'Commission mismatch: Expected %.2f XAF, got %.2f XAF',
                    $expectedCommission['platform_commission'],
                    $providedCommission
                );
            }
            
            // Check commission rate bounds
            $rate = $commissionRate ?? 0.15;
            if ($rate < 0.05 || $rate > 0.30) {
                $warnings[] = 'Commission rate outside normal range (5%-30%)';
            }
            
            return [
                'valid' => empty($errors),
                'errors' => $errors,
                'warnings' => $warnings,
                'expected_commission' => $expectedCommission,
                'provided_commission' => $providedCommission
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Commission validation failed: ' . $e->getMessage()],
                'warnings' => [],
                'expected_commission' => null,
                'provided_commission' => $providedCommission
            ];
        }
    }
    
    /**
     * Validate profit calculations
     */
    public function validateProfitCalculations(array $orderData): array
    {
        $errors = [];
        $warnings = [];
        
        try {
            $profitData = $this->calculator->calculatePlatformProfit($orderData);
            
            // Check profit margin
            $profitMargin = $profitData['profit']['profit_margin'];
            if ($profitMargin < 0) {
                $errors[] = 'Order results in negative profit margin';
            } elseif ($profitMargin < 5) {
                $warnings[] = 'Low profit margin (< 5%)';
            }
            
            // Check if costs exceed revenue
            $revenue = $profitData['revenue']['total_revenue'];
            $costs = $profitData['costs']['total_costs'];
            
            if ($costs > $revenue) {
                $errors[] = 'Order costs exceed revenue';
            }
            
            // Validate individual components
            if ($profitData['costs']['rider_earnings'] < 350) {
                $warnings[] = 'Rider earnings below minimum threshold';
            }
            
            return [
                'valid' => empty($errors),
                'errors' => $errors,
                'warnings' => $warnings,
                'profit_data' => $profitData
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Profit validation failed: ' . $e->getMessage()],
                'warnings' => [],
                'profit_data' => null
            ];
        }
    }
    
    /**
     * Comprehensive order validation
     */
    public function validateCompleteOrder(array $orderData): array
    {
        $allErrors = [];
        $allWarnings = [];
        $validationResults = [];
        
        // Validate order calculations
        $orderValidation = $this->validateOrderCalculations($orderData);
        $validationResults['order'] = $orderValidation;
        $allErrors = array_merge($allErrors, $orderValidation['errors']);
        $allWarnings = array_merge($allWarnings, $orderValidation['warnings']);
        
        // Validate rider earnings if rider is assigned
        if (!empty($orderData['rider_earnings']) && !empty($orderData['distance'])) {
            $riderValidation = $this->validateRiderEarnings(
                (float)$orderData['distance'],
                (float)($orderData['delivery_fee'] ?? 0),
                (float)$orderData['rider_earnings']
            );
            $validationResults['rider'] = $riderValidation;
            $allErrors = array_merge($allErrors, $riderValidation['errors']);
            $allWarnings = array_merge($allWarnings, $riderValidation['warnings']);
        }
        
        // Validate commission calculations
        if (!empty($orderData['platform_commission'])) {
            $commissionValidation = $this->validateCommissions(
                (float)($orderData['subtotal'] ?? 0),
                (float)$orderData['platform_commission'],
                (float)($orderData['commission_rate'] ?? null)
            );
            $validationResults['commission'] = $commissionValidation;
            $allErrors = array_merge($allErrors, $commissionValidation['errors']);
            $allWarnings = array_merge($allWarnings, $commissionValidation['warnings']);
        }
        
        // Validate profit calculations
        $profitValidation = $this->validateProfitCalculations($orderData);
        $validationResults['profit'] = $profitValidation;
        $allErrors = array_merge($allErrors, $profitValidation['errors']);
        $allWarnings = array_merge($allWarnings, $profitValidation['warnings']);
        
        return [
            'valid' => empty($allErrors),
            'errors' => $allErrors,
            'warnings' => $allWarnings,
            'validation_results' => $validationResults,
            'summary' => [
                'total_errors' => count($allErrors),
                'total_warnings' => count($allWarnings),
                'validation_passed' => empty($allErrors)
            ]
        ];
    }
    
    /**
     * Get financial health metrics for the platform
     */
    public function getFinancialHealthMetrics(array $orders): array
    {
        $totalRevenue = 0;
        $totalCosts = 0;
        $totalProfit = 0;
        $orderCount = count($orders);
        $profitableOrders = 0;
        
        foreach ($orders as $order) {
            $profitData = $this->calculator->calculatePlatformProfit($order);
            $totalRevenue += $profitData['revenue']['total_revenue'];
            $totalCosts += $profitData['costs']['total_costs'];
            $totalProfit += $profitData['profit']['gross_profit'];
            
            if ($profitData['profit']['gross_profit'] > 0) {
                $profitableOrders++;
            }
        }
        
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;
        $avgProfit = $orderCount > 0 ? $totalProfit / $orderCount : 0;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
        $profitableOrderRate = $orderCount > 0 ? ($profitableOrders / $orderCount) * 100 : 0;
        
        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_costs' => round($totalCosts, 2),
            'total_profit' => round($totalProfit, 2),
            'order_count' => $orderCount,
            'avg_order_value' => round($avgOrderValue, 2),
            'avg_profit_per_order' => round($avgProfit, 2),
            'profit_margin' => round($profitMargin, 2),
            'profitable_orders' => $profitableOrders,
            'profitable_order_rate' => round($profitableOrderRate, 2),
            'health_status' => $this->getHealthStatus($profitMargin, $profitableOrderRate)
        ];
    }
    
    /**
     * Determine financial health status
     */
    private function getHealthStatus(float $profitMargin, float $profitableOrderRate): string
    {
        if ($profitMargin >= 15 && $profitableOrderRate >= 90) {
            return 'Excellent';
        } elseif ($profitMargin >= 10 && $profitableOrderRate >= 80) {
            return 'Good';
        } elseif ($profitMargin >= 5 && $profitableOrderRate >= 70) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }
}
