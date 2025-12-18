<?php

namespace Time2Eat\Models;

use core\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $fillable = [
        'user_id', 'type', 'provider', 'name', 'details', 'is_default',
        'is_verified', 'last_used_at', 'expires_at'
    ];

    /**
     * Get payment methods by user ID
     */
    public function getByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY is_default DESC, created_at DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get default payment method for user
     */
    public function getDefaultByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND is_default = 1 
                LIMIT 1";
        return $this->fetchOne($sql, [$userId]);
    }

    /**
     * Add payment method for user
     */
    public function addPaymentMethod(int $userId, array $data): ?int
    {
        // Validate required fields
        $required = ['type', 'provider', 'name', 'details'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Encrypt sensitive details
        $details = is_array($data['details']) ? $data['details'] : json_decode($data['details'], true);
        $encryptedDetails = $this->encryptPaymentDetails($details);

        $paymentMethodData = [
            'user_id' => $userId,
            'type' => $data['type'],
            'provider' => $data['provider'],
            'name' => $data['name'],
            'details' => json_encode($encryptedDetails),
            'is_default' => $data['is_default'] ?? false,
            'is_verified' => $data['is_verified'] ?? false,
            'expires_at' => $data['expires_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // If this is set as default, unset other defaults
        if ($paymentMethodData['is_default']) {
            $this->unsetDefaultForUser($userId);
        }

        return $this->create($paymentMethodData);
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(int $id, int $userId, array $data): bool
    {
        // Verify ownership
        $paymentMethod = $this->getById($id);
        if (!$paymentMethod || $paymentMethod['user_id'] != $userId) {
            return false;
        }

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['is_default'])) {
            $updateData['is_default'] = $data['is_default'];
            if ($data['is_default']) {
                $this->unsetDefaultForUser($userId);
            }
        }

        if (isset($data['details'])) {
            $details = is_array($data['details']) ? $data['details'] : json_decode($data['details'], true);
            $encryptedDetails = $this->encryptPaymentDetails($details);
            $updateData['details'] = json_encode($encryptedDetails);
        }

        if (isset($data['expires_at'])) {
            $updateData['expires_at'] = $data['expires_at'];
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($id, $updateData);
        }

        return true;
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(int $id, int $userId): bool
    {
        // Verify ownership
        $paymentMethod = $this->getById($id);
        if (!$paymentMethod || $paymentMethod['user_id'] != $userId) {
            return false;
        }

        return $this->delete($id);
    }

    /**
     * Set payment method as default
     */
    public function setAsDefault(int $id, int $userId): bool
    {
        // Verify ownership
        $paymentMethod = $this->getById($id);
        if (!$paymentMethod || $paymentMethod['user_id'] != $userId) {
            return false;
        }

        // Unset other defaults
        $this->unsetDefaultForUser($userId);

        // Set this as default
        return $this->update($id, [
            'is_default' => true,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(int $id): bool
    {
        return $this->update($id, [
            'last_used_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verify payment method
     */
    public function verifyPaymentMethod(int $id, bool $verified = true): bool
    {
        return $this->update($id, [
            'is_verified' => $verified,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get payment method with decrypted details
     */
    public function getWithDecryptedDetails(int $id, int $userId): ?array
    {
        $paymentMethod = $this->getById($id);
        if (!$paymentMethod || $paymentMethod['user_id'] != $userId) {
            return null;
        }

        // Decrypt details
        $details = json_decode($paymentMethod['details'], true);
        $paymentMethod['details'] = $this->decryptPaymentDetails($details);

        return $paymentMethod;
    }

    /**
     * Get expired payment methods
     */
    public function getExpiredMethods(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE expires_at IS NOT NULL 
                AND expires_at < NOW()
                ORDER BY expires_at ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Get payment methods expiring soon
     */
    public function getExpiringSoon(int $days = 30): array
    {
        $sql = "SELECT pm.*, u.email, u.first_name, u.last_name
                FROM {$this->table} pm
                JOIN users u ON pm.user_id = u.id
                WHERE pm.expires_at IS NOT NULL 
                AND pm.expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY pm.expires_at ASC";
        return $this->fetchAll($sql, [$days]);
    }

    /**
     * Get payment method statistics
     */
    public function getPaymentMethodStats(): array
    {
        $sql = "SELECT 
                    type,
                    provider,
                    COUNT(*) as count,
                    COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_count,
                    COUNT(CASE WHEN is_default = 1 THEN 1 END) as default_count
                FROM {$this->table}
                GROUP BY type, provider
                ORDER BY count DESC";
        
        return $this->fetchAll($sql);
    }

    /**
     * Clean up expired payment methods
     */
    public function cleanupExpired(): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE expires_at IS NOT NULL 
                AND expires_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        return $this->execute($sql);
    }

    /**
     * Unset default for user
     */
    private function unsetDefaultForUser(int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_default = 0, updated_at = NOW() 
                WHERE user_id = ? AND is_default = 1";
        
        return $this->execute($sql, [$userId]) !== false;
    }

    /**
     * Encrypt payment details
     */
    private function encryptPaymentDetails(array $details): array
    {
        $encryptedDetails = [];
        
        foreach ($details as $key => $value) {
            if (in_array($key, ['card_number', 'cvv', 'account_number', 'pin'])) {
                // Encrypt sensitive fields
                $encryptedDetails[$key] = $this->encrypt($value);
            } else {
                $encryptedDetails[$key] = $value;
            }
        }
        
        return $encryptedDetails;
    }

    /**
     * Decrypt payment details
     */
    private function decryptPaymentDetails(array $details): array
    {
        $decryptedDetails = [];
        
        foreach ($details as $key => $value) {
            if (in_array($key, ['card_number', 'cvv', 'account_number', 'pin'])) {
                // Decrypt sensitive fields
                $decryptedDetails[$key] = $this->decrypt($value);
            } else {
                $decryptedDetails[$key] = $value;
            }
        }
        
        return $decryptedDetails;
    }

    /**
     * Simple encryption (use proper encryption in production)
     */
    private function encrypt(string $data): string
    {
        $key = $_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_in_production';
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16)));
    }

    /**
     * Simple decryption (use proper encryption in production)
     */
    private function decrypt(string $data): string
    {
        $key = $_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_in_production';
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }

    /**
     * Validate payment method data
     */
    public function validatePaymentMethodData(array $data): array
    {
        $errors = [];

        if (empty($data['type'])) {
            $errors[] = 'Payment method type is required';
        } elseif (!in_array($data['type'], ['card', 'mobile_money', 'bank_account', 'paypal'])) {
            $errors[] = 'Invalid payment method type';
        }

        if (empty($data['provider'])) {
            $errors[] = 'Payment provider is required';
        }

        if (empty($data['name'])) {
            $errors[] = 'Payment method name is required';
        }

        if (empty($data['details'])) {
            $errors[] = 'Payment method details are required';
        } else {
            $details = is_array($data['details']) ? $data['details'] : json_decode($data['details'], true);
            
            // Validate based on type
            switch ($data['type']) {
                case 'card':
                    if (empty($details['card_number'])) {
                        $errors[] = 'Card number is required';
                    }
                    if (empty($details['expiry_month']) || empty($details['expiry_year'])) {
                        $errors[] = 'Card expiry date is required';
                    }
                    break;
                    
                case 'mobile_money':
                    if (empty($details['phone_number'])) {
                        $errors[] = 'Phone number is required for mobile money';
                    }
                    break;
                    
                case 'bank_account':
                    if (empty($details['account_number'])) {
                        $errors[] = 'Account number is required';
                    }
                    if (empty($details['bank_name'])) {
                        $errors[] = 'Bank name is required';
                    }
                    break;
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }
}
