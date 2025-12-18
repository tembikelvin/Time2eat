<?php

declare(strict_types=1);

/**
 * JWT Helper Class
 * Simple JWT implementation for authentication
 */
class JWTHelper
{
    private static string $secretKey = '';
    private static string $algorithm = 'HS256';
    
    /**
     * Initialize with secret key
     */
    public static function init(): void
    {
        if (empty(self::$secretKey)) {
            $config = require __DIR__ . '/../../config/app.php';
            self::$secretKey = $config['jwt_secret'] ?? self::generateSecret();
        }
    }
    
    /**
     * Encode payload to JWT token
     */
    public static function encode(array $payload): string
    {
        self::init();
        
        $header = [
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ];
        
        // Add issued at and expiration if not set
        if (!isset($payload['iat'])) {
            $payload['iat'] = time();
        }
        
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + 3600; // 1 hour default
        }
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = self::sign($headerEncoded . '.' . $payloadEncoded);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }
    
    /**
     * Decode JWT token to payload
     */
    public static function decode(string $token): ?array
    {
        self::init();
        
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        [$headerEncoded, $payloadEncoded, $signature] = $parts;
        
        // Verify signature
        $expectedSignature = self::sign($headerEncoded . '.' . $payloadEncoded);
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }
        
        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        if (!$header || !$payload) {
            return null;
        }
        
        // Check algorithm
        if ($header['alg'] !== self::$algorithm) {
            return null;
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        // Check not before
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Verify JWT token
     */
    public static function verify(string $token): bool
    {
        return self::decode($token) !== null;
    }
    
    /**
     * Get payload from token without verification (for debugging)
     */
    public static function getPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        return $payload ?: null;
    }
    
    /**
     * Refresh token (create new token with extended expiration)
     */
    public static function refresh(string $token, int $extendBy = 3600): ?string
    {
        $payload = self::decode($token);
        
        if (!$payload) {
            return null;
        }
        
        // Extend expiration
        $payload['exp'] = time() + $extendBy;
        $payload['iat'] = time();
        
        return self::encode($payload);
    }
    
    /**
     * Create access token
     */
    public static function createAccessToken(array $userData, int $expiresIn = 3600): string
    {
        $payload = [
            'user_id' => $userData['id'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? 'customer',
            'type' => 'access',
            'exp' => time() + $expiresIn
        ];
        
        return self::encode($payload);
    }
    
    /**
     * Create refresh token
     */
    public static function createRefreshToken(array $userData, int $expiresIn = 2592000): string // 30 days
    {
        $payload = [
            'user_id' => $userData['id'],
            'type' => 'refresh',
            'exp' => time() + $expiresIn
        ];
        
        return self::encode($payload);
    }
    
    /**
     * Create token pair (access + refresh)
     */
    public static function createTokenPair(array $userData): array
    {
        return [
            'access_token' => self::createAccessToken($userData),
            'refresh_token' => self::createRefreshToken($userData),
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];
    }
    
    /**
     * Blacklist token (simple file-based implementation)
     */
    public static function blacklistToken(string $token): void
    {
        $payload = self::getPayload($token);
        if (!$payload) {
            return;
        }
        
        $blacklistFile = __DIR__ . '/../../storage/jwt_blacklist.json';
        $blacklist = [];
        
        if (file_exists($blacklistFile)) {
            $blacklist = json_decode(file_get_contents($blacklistFile), true) ?: [];
        }
        
        $tokenHash = hash('sha256', $token);
        $blacklist[$tokenHash] = [
            'exp' => $payload['exp'] ?? time() + 3600,
            'blacklisted_at' => time()
        ];
        
        // Clean expired tokens
        $blacklist = array_filter($blacklist, fn($item) => $item['exp'] > time());
        
        file_put_contents($blacklistFile, json_encode($blacklist));
    }
    
    /**
     * Check if token is blacklisted
     */
    public static function isBlacklisted(string $token): bool
    {
        $blacklistFile = __DIR__ . '/../../storage/jwt_blacklist.json';
        
        if (!file_exists($blacklistFile)) {
            return false;
        }
        
        $blacklist = json_decode(file_get_contents($blacklistFile), true) ?: [];
        $tokenHash = hash('sha256', $token);
        
        return isset($blacklist[$tokenHash]) && $blacklist[$tokenHash]['exp'] > time();
    }
    
    /**
     * Sign data with secret key
     */
    private static function sign(string $data): string
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::$secretKey, true));
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Generate random secret key
     */
    private static function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Get token from authorization header
     */
    public static function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Validate token format
     */
    public static function isValidFormat(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $token) === 1;
    }
    
    /**
     * Get token expiration time
     */
    public static function getExpiration(string $token): ?int
    {
        $payload = self::getPayload($token);
        return $payload['exp'] ?? null;
    }
    
    /**
     * Check if token is expired
     */
    public static function isExpired(string $token): bool
    {
        $exp = self::getExpiration($token);
        return $exp !== null && $exp < time();
    }
    
    /**
     * Get time until token expires
     */
    public static function getTimeToExpiry(string $token): ?int
    {
        $exp = self::getExpiration($token);
        return $exp !== null ? max(0, $exp - time()) : null;
    }
    
    /**
     * Create password reset token
     */
    public static function createPasswordResetToken(int $userId, string $email): string
    {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'type' => 'password_reset',
            'exp' => time() + 3600 // 1 hour
        ];
        
        return self::encode($payload);
    }
    
    /**
     * Create email verification token
     */
    public static function createEmailVerificationToken(int $userId, string $email): string
    {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'type' => 'email_verification',
            'exp' => time() + 86400 // 24 hours
        ];
        
        return self::encode($payload);
    }
    
    /**
     * Validate specific token type
     */
    public static function validateTokenType(string $token, string $expectedType): bool
    {
        $payload = self::decode($token);
        return $payload && ($payload['type'] ?? null) === $expectedType;
    }
}
