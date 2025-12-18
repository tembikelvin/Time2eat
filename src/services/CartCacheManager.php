<?php
/**
 * Cart Cache Manager
 * Ensures cart data is never cached in production
 */

class CartCacheManager
{
    /**
     * Set no-cache headers for cart-related responses
     */
    public static function setNoCacheHeaders(): void
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            // Production: Strict no-cache
            header('Cache-Control: no-cache, no-store, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('ETag: "' . uniqid() . '"');
        } else {
            // Development: Allow short caching for debugging
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }
    
    /**
     * Clear any existing cache for cart data
     */
    public static function clearCartCache(int $userId): void
    {
        // Clear browser cache headers
        self::setNoCacheHeaders();
        
        // Clear any server-side cache
        if (class_exists('CacheManager')) {
            $cacheManager = new CacheManager();
            $cacheManager->delete("cart_user_{$userId}");
            $cacheManager->delete("cart_count_{$userId}");
            $cacheManager->delete("cart_totals_{$userId}");
        }
        
        // Clear session cache
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['cart_cache']);
            unset($_SESSION['cart_count']);
            unset($_SESSION['cart_totals']);
        }
    }
    
    /**
     * Check if request is for cart-related endpoint
     */
    public static function isCartEndpoint(string $path): bool
    {
        $cartPatterns = [
            '/api/cart/',
            '/api/checkout/',
            '/api/orders/',
            '/api/payments/',
            '/api/profile/',
            '/api/dashboard/',
            '/api/settings/',
            '/api/affiliate/',
            '/api/notifications/'
        ];
        
        foreach ($cartPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}