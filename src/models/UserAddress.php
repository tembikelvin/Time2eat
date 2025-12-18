<?php

namespace Time2Eat\Models;

use core\Model;

/**
 * UserAddress Model
 * Handles user saved addresses functionality
 */
class UserAddress extends Model
{
    protected $table = 'user_addresses';
    protected $fillable = [
        'user_id', 'label', 'address_line_1', 'address_line_2', 'city', 
        'state', 'postal_code', 'country', 'latitude', 'longitude', 
        'is_default', 'delivery_instructions'
    ];

    /**
     * Get addresses for a user
     */
    public function getByUser(int $userId): array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE user_id = ? AND deleted_at IS NULL
            ORDER BY is_default DESC, created_at DESC
        ";

        return $this->fetchAll($sql, [$userId]);
    }

    /**
     * Get address by ID
     */
    public function getById(int $addressId, int $userId): ?array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE id = ? AND user_id = ? AND deleted_at IS NULL
        ";

        return $this->fetchOne($sql, [$addressId, $userId]);
    }

    /**
     * Create a new address
     */
    public function createAddress(array $data): array
    {
        try {
            // If this is set as default, unset other defaults
            if ($data['is_default'] ?? false) {
                $this->unsetDefaultForUser($data['user_id']);
            }

            $addressId = $this->insert($this->table, $data);
            
            return [
                'success' => true,
                'address_id' => $addressId,
                'message' => 'Address saved successfully'
            ];

        } catch (\Exception $e) {
            error_log("Error creating address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an address
     */
    public function updateAddress(int $addressId, int $userId, array $data): array
    {
        try {
            // If this is set as default, unset other defaults
            if ($data['is_default'] ?? false) {
                $this->unsetDefaultForUser($userId);
            }

            $this->update($this->table, $data, "id = ? AND user_id = ?", [$addressId, $userId]);
            
            return [
                'success' => true,
                'message' => 'Address updated successfully'
            ];

        } catch (\Exception $e) {
            error_log("Error updating address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete an address (soft delete)
     */
    public function deleteAddress(int $addressId, int $userId): array
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ? LIMIT 1";
        $result = $this->db->execute($sql, [$addressId, $userId]);
        return ["success" => $result > 0];
    }

    /**
     * Set address as default
     */
    public function setAsDefault(int $addressId, int $userId): array
    {
        try {
            // Unset all other defaults for this user
            $this->unsetDefaultForUser($userId);

            // Set this address as default
            $this->update($this->table, ['is_default' => 1], "id = ? AND user_id = ?", [$addressId, $userId]);
            
            return [
                'success' => true,
                'message' => 'Address set as default successfully'
            ];

        } catch (\Exception $e) {
            error_log("Error setting default address: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to set default address: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get default address for user
     */
    public function getDefaultAddress(int $userId): ?array
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE user_id = ? AND is_default = 1 AND deleted_at IS NULL
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$userId]);
    }

    /**
     * Unset default address for user
     */
    private function unsetDefaultForUser(int $userId): void
    {
        $this->update($this->table, ['is_default' => 0], "user_id = ?", [$userId]);
    }

    /**
     * Search addresses by location (for nearby addresses)
     */
    public function searchByLocation(int $userId, float $latitude, float $longitude, float $radiusKm = 5): array
    {
        $sql = "
            SELECT *, 
                   (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude)))) AS distance
            FROM {$this->table}
            WHERE user_id = ? AND deleted_at IS NULL
            HAVING distance < ?
            ORDER BY distance ASC
        ";

        return $this->fetchAll($sql, [$latitude, $longitude, $latitude, $userId, $radiusKm]);
    }

    /**
     * Validate address data
     */
    public function validateAddressData(array $data): array
    {
        $errors = [];

        if (empty($data['label'])) {
            $errors[] = 'Address label is required';
        }

        if (empty($data['address_line_1'])) {
            $errors[] = 'Street address is required';
        }

        if (empty($data['city'])) {
            $errors[] = 'City is required';
        }

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $errors[] = 'GPS coordinates are required';
        }

        // Validate latitude
        if (!empty($data['latitude']) && ($data['latitude'] < -90 || $data['latitude'] > 90)) {
            $errors[] = 'Invalid latitude value';
        }

        // Validate longitude
        if (!empty($data['longitude']) && ($data['longitude'] < -180 || $data['longitude'] > 180)) {
            $errors[] = 'Invalid longitude value';
        }

        return $errors;
    }
}
