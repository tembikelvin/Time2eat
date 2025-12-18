<?php
/**
 * Simple Cache Control Helpers
 * Simplified caching to prevent production issues
 */

/**
 * Set no-cache headers (for user-specific data)
 * Use for: cart, checkout, orders, dashboards, auth, profile, settings, API endpoints
 */
function setNoCacheHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/**
 * Set cache headers for static assets
 * Use this for: CSS, JS, images, fonts
 * 
 * @param int $maxAge Cache duration in seconds (default: 1 week)
 */
function setCacheHeaders(int $maxAge = 604800): void
{
    if (headers_sent()) {
        return;
    }
    
    // Cache for specified duration
    header('Cache-Control: public, max-age=' . $maxAge);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
}

/**
 * Check if current request is for static assets
 *
 * @return bool True if static asset, false otherwise
 */
function isStaticAsset(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico'];

    foreach ($staticExtensions as $ext) {
        if (strpos($uri, $ext) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Clear OPcache (PHP code cache only)
 * Use after deploying code changes
 *
 * @return bool True if cleared, false otherwise
 */
function clearOPcache(): bool
{
    if (function_exists('opcache_reset')) {
        return opcache_reset();
    }
    return false;
}

