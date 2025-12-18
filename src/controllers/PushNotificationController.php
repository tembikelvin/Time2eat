<?php

declare(strict_types=1);

namespace Time2Eat\Controllers;

use core\BaseController;
use Time2Eat\Models\PushSubscription;
use Time2Eat\Models\User;

/**
 * Push Notification Controller
 * Handles PWA push notification subscriptions and sending
 */
class PushNotificationController extends BaseController
{
    private PushSubscription $subscriptionModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->subscriptionModel = new PushSubscription();
        $this->userModel = new User();
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        $input = $this->getJsonInput();
        
        if (!$this->validateSubscriptionData($input)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid subscription data'
            ], 400);
            return;
        }

        $user = $this->getAuthenticatedUser();
        
        try {
            $subscriptionId = $this->subscriptionModel->createSubscription(
                $user['id'],
                $input['endpoint'],
                $input['keys']['p256dh'] ?? null,
                $input['keys']['auth'] ?? null,
                $this->getUserAgent(),
                $this->getClientIP()
            );

            if ($subscriptionId) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Push notification subscription created',
                    'subscription_id' => $subscriptionId
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create subscription'
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logError('Push subscription failed', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Subscription failed'
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        $input = $this->getJsonInput();
        $user = $this->getAuthenticatedUser();

        if (empty($input['endpoint'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Endpoint required'
            ], 400);
            return;
        }

        try {
            $success = $this->subscriptionModel->removeSubscription(
                $user['id'],
                $input['endpoint']
            );

            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Push notification subscription removed'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->logError('Push unsubscription failed', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unsubscription failed'
            ], 500);
        }
    }

    /**
     * Send push notification to user
     */
    public function sendNotification(): void
    {
        if (!$this->isAuthenticated() || !$this->hasRole(['admin', 'vendor'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
            return;
        }

        $input = $this->getJsonInput();
        
        if (!$this->validateNotificationData($input)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid notification data'
            ], 400);
            return;
        }

        try {
            $results = $this->sendPushNotification(
                $input['user_id'] ?? null,
                $input['title'],
                $input['body'],
                $input['icon'] ?? '/images/icons/icon-192x192.png',
                $input['badge'] ?? '/images/icons/badge-72x72.png',
                $input['url'] ?? '/',
                $input['data'] ?? []
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notifications sent',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            $this->logError('Push notification sending failed', [
                'error' => $e->getMessage(),
                'input' => $input
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to send notifications'
            ], 500);
        }
    }

    /**
     * Get user's push subscriptions
     */
    public function getSubscriptions(): void
    {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        $user = $this->getAuthenticatedUser();
        
        try {
            $subscriptions = $this->subscriptionModel->getUserSubscriptions($user['id']);
            
            $this->jsonResponse([
                'success' => true,
                'subscriptions' => $subscriptions
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to get subscriptions', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get subscriptions'
            ], 500);
        }
    }

    /**
     * Send push notification to users
     */
    private function sendPushNotification(
        ?int $userId,
        string $title,
        string $body,
        string $icon,
        string $badge,
        string $url,
        array $data
    ): array {
        $subscriptions = $userId 
            ? $this->subscriptionModel->getUserSubscriptions($userId)
            : $this->subscriptionModel->getAllActiveSubscriptions();

        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($subscriptions as $subscription) {
            try {
                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'icon' => $icon,
                    'badge' => $badge,
                    'url' => $url,
                    'data' => array_merge($data, [
                        'timestamp' => time(),
                        'subscription_id' => $subscription['id']
                    ])
                ]);

                $success = $this->sendWebPush(
                    $subscription['endpoint'],
                    $payload,
                    $subscription['p256dh_key'],
                    $subscription['auth_key']
                );

                if ($success) {
                    $results['sent']++;
                    $this->subscriptionModel->updateLastUsed($subscription['id']);
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
                
                // Mark subscription as failed if it's a permanent error
                if (strpos($e->getMessage(), '410') !== false) {
                    $this->subscriptionModel->markAsInactive($subscription['id']);
                }
            }
        }

        return $results;
    }

    /**
     * Send web push using VAPID
     */
    private function sendWebPush(
        string $endpoint,
        string $payload,
        ?string $p256dhKey,
        ?string $authKey
    ): bool {
        // This is a simplified implementation
        // In production, use a library like web-push-php
        
        $vapidKeys = $this->getVapidKeys();
        
        $headers = [
            'Content-Type: application/json',
            'TTL: 86400',
            'Authorization: vapid t=' . $this->generateVapidJWT($endpoint, $vapidKeys['private']),
            'Crypto-Key: p256ecdsa=' . $vapidKeys['public']
        ];

        if ($p256dhKey && $authKey) {
            // Encrypt payload (simplified - use proper encryption in production)
            $encryptedPayload = $this->encryptPayload($payload, $p256dhKey, $authKey);
            $headers[] = 'Content-Encoding: aes128gcm';
            $headers[] = 'Encryption: salt=' . base64_encode($encryptedPayload['salt']);
            $headers[] = 'Crypto-Key: dh=' . base64_encode($encryptedPayload['key']) . ';p256ecdsa=' . $vapidKeys['public'];
            $body = $encryptedPayload['ciphertext'];
        } else {
            $body = $payload;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Validate subscription data
     */
    private function validateSubscriptionData(array $data): bool
    {
        return !empty($data['endpoint']) && 
               filter_var($data['endpoint'], FILTER_VALIDATE_URL) &&
               !empty($data['keys']) &&
               !empty($data['keys']['p256dh']) &&
               !empty($data['keys']['auth']);
    }

    /**
     * Validate notification data
     */
    private function validateNotificationData(array $data): bool
    {
        return !empty($data['title']) && !empty($data['body']);
    }

    /**
     * Get VAPID keys (should be stored securely)
     */
    private function getVapidKeys(): array
    {
        return [
            'public' => $_ENV['VAPID_PUBLIC_KEY'] ?? 'BEl62iUYgUivxIkv69yViEuiBIa40HI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqIUHI80NqI',
            'private' => $_ENV['VAPID_PRIVATE_KEY'] ?? 'your-private-key-here'
        ];
    }

    /**
     * Generate VAPID JWT (simplified)
     */
    private function generateVapidJWT(string $audience, string $privateKey): string
    {
        // Simplified JWT generation - use proper JWT library in production
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = base64_encode(json_encode([
            'aud' => parse_url($audience, PHP_URL_SCHEME) . '://' . parse_url($audience, PHP_URL_HOST),
            'exp' => time() + 3600,
            'sub' => 'mailto:admin@time2eat.com'
        ]));
        
        return $header . '.' . $payload . '.signature';
    }

    /**
     * Encrypt payload (simplified)
     */
    private function encryptPayload(string $payload, string $p256dhKey, string $authKey): array
    {
        // Simplified encryption - use proper Web Push encryption in production
        $salt = random_bytes(16);
        $key = random_bytes(32);
        
        return [
            'salt' => $salt,
            'key' => $key,
            'ciphertext' => $payload // Should be properly encrypted
        ];
    }
}
