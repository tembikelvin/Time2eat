<?php

namespace Time2Eat\Services;

require_once __DIR__ . '/../../config/database.php';

/**
 * Rider Status Service
 * Centralized service for managing rider online/offline status across all tables
 */
class RiderStatusService
{
    private $db;
    private $userModel;
    private $riderLocationModel;

    public function __construct()
    {
        $this->db = \Database::getInstance();
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/RiderLocation.php';
        $this->userModel = new \models\User();
        $this->riderLocationModel = new \Time2Eat\Models\RiderLocation();
    }

    /**
     * Set rider online status
     * Updates all relevant tables when rider goes online
     */
    public function setRiderOnline(int $riderId, ?float $latitude = null, ?float $longitude = null): array
    {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // 1. Update users table - set is_available = true
            $userUpdated = $this->updateUserAvailability($riderId, true);
            if (!$userUpdated) {
                throw new \Exception("Failed to update user availability");
            }

            // 2. Update or create rider location with is_online = true
            $locationUpdated = $this->updateRiderLocation($riderId, $latitude, $longitude, true);
            if (!$locationUpdated) {
                throw new \Exception("Failed to update rider location");
            }

            // 3. Log the status change
            $this->logStatusChange($riderId, 'online', 'Rider went online');

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Rider is now online',
                'status' => 'online',
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            // Rollback on error
            $this->db->rollback();
            error_log("Error setting rider online: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to set rider online: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Set rider offline status
     * Updates all relevant tables when rider goes offline
     */
    public function setRiderOffline(int $riderId): array
    {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // 1. Update users table - set is_available = false
            $userUpdated = $this->updateUserAvailability($riderId, false);
            if (!$userUpdated) {
                throw new \Exception("Failed to update user availability");
            }

            // 2. Update rider location with is_online = false
            $locationUpdated = $this->updateRiderLocation($riderId, null, null, false);
            if (!$locationUpdated) {
                throw new \Exception("Failed to update rider location");
            }

            // 3. Log the status change
            $this->logStatusChange($riderId, 'offline', 'Rider went offline');

            // Commit transaction
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Rider is now offline',
                'status' => 'offline',
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            // Rollback on error
            $this->db->rollback();
            error_log("Error setting rider offline: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to set rider offline: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle rider status (online/offline)
     */
    public function toggleRiderStatus(int $riderId, ?float $latitude = null, ?float $longitude = null): array
    {
        // Get current status
        $currentStatus = $this->getRiderStatus($riderId);
        
        if ($currentStatus['is_online']) {
            return $this->setRiderOffline($riderId);
        } else {
            return $this->setRiderOnline($riderId, $latitude, $longitude);
        }
    }

    /**
     * Get comprehensive rider status
     */
    public function getRiderStatus(int $riderId): array
    {
        try {
            // Get user availability status
            $user = $this->userModel->findById($riderId);
            if (!$user || $user['role'] !== 'rider') {
                return [
                    'success' => false,
                    'message' => 'Rider not found'
                ];
            }

            // Get latest location status
            $location = $this->getLatestRiderLocation($riderId);

            // Get schedule status for today
            $scheduleStatus = $this->getTodayScheduleStatus($riderId);

            return [
                'success' => true,
                'rider_id' => $riderId,
                'is_available' => (bool)$user['is_available'],
                'is_online' => $location ? (bool)$location['is_online'] : false,
                'account_status' => $user['status'],
                'last_location' => $location ? [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'updated_at' => $location['created_at']
                ] : null,
                'schedule_status' => $scheduleStatus,
                'overall_status' => $this->determineOverallStatus($user, $location, $scheduleStatus)
            ];

        } catch (\Exception $e) {
            error_log("Error getting rider status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get rider status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all online riders
     */
    public function getOnlineRiders(int $limit = 50): array
    {
        try {
            $sql = "SELECT u.id, u.first_name, u.last_name, u.phone, u.is_available,
                           rl.latitude, rl.longitude, rl.created_at as last_location_update
                    FROM users u
                    LEFT JOIN rider_locations rl ON u.id = rl.rider_id 
                        AND rl.id = (
                            SELECT id FROM rider_locations 
                            WHERE rider_id = u.id 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        )
                    WHERE u.role = 'rider' 
                    AND u.status = 'active' 
                    AND u.is_available = 1
                    AND (rl.is_online = 1 OR rl.is_online IS NULL)
                    ORDER BY rl.created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log("Error getting online riders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update rider location and online status
     */
    public function updateRiderLocation(int $riderId, ?float $latitude, ?float $longitude, bool $isOnline = true): bool
    {
        try {
            // If no coordinates provided, try to update existing or create with default location
            if ($latitude === null || $longitude === null) {
                // Try to update existing location
                $sql = "UPDATE rider_locations 
                        SET is_online = ?, created_at = NOW()
                        WHERE rider_id = ? 
                        AND id = (
                            SELECT id FROM (
                                SELECT id FROM rider_locations 
                                WHERE rider_id = ? 
                                ORDER BY created_at DESC 
                                LIMIT 1
                            ) as latest
                        )";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$isOnline ? 1 : 0, $riderId, $riderId]);
                
                // If no rows updated, create a new record with default location (0,0)
                if ($stmt->rowCount() === 0) {
                    $sql = "INSERT INTO rider_locations (rider_id, latitude, longitude, is_online, created_at) 
                            VALUES (?, 0, 0, ?, NOW())";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$riderId, $isOnline ? 1 : 0]);
                }
                
                return true;
            }

            // Insert new location record with provided coordinates
            $sql = "INSERT INTO rider_locations (rider_id, latitude, longitude, is_online, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$riderId, $latitude, $longitude, $isOnline ? 1 : 0]);
            return $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            error_log("Error updating rider location: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user availability in users table
     */
    private function updateUserAvailability(int $riderId, bool $isAvailable): bool
    {
        try {
            $sql = "UPDATE users SET is_available = ?, updated_at = NOW() WHERE id = ? AND role = 'rider'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$isAvailable ? 1 : 0, $riderId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating user availability: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get latest rider location
     */
    private function getLatestRiderLocation(int $riderId): ?array
    {
        try {
            $sql = "SELECT * FROM rider_locations 
                    WHERE rider_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$riderId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Error getting latest rider location: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get today's schedule status
     */
    private function getTodayScheduleStatus(int $riderId): array
    {
        try {
            $dayOfWeek = (int)date('w'); // 0=Sunday, 1=Monday, etc.
            $currentTime = date('H:i:s');

            $sql = "SELECT is_available, start_time, end_time 
                    FROM rider_schedules 
                    WHERE rider_id = ? AND day_of_week = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$riderId, $dayOfWeek]);
            $schedule = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$schedule) {
                return [
                    'scheduled' => false,
                    'available' => false,
                    'message' => 'No schedule set for today'
                ];
            }

            $isWithinHours = $currentTime >= $schedule['start_time'] && $currentTime <= $schedule['end_time'];
            $isAvailable = (bool)$schedule['is_available'] && $isWithinHours;

            return [
                'scheduled' => true,
                'available' => $isAvailable,
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'message' => $isAvailable ? 'Within working hours' : 'Outside working hours'
            ];

        } catch (\Exception $e) {
            error_log("Error getting today's schedule status: " . $e->getMessage());
            return [
                'scheduled' => false,
                'available' => false,
                'message' => 'Error checking schedule'
            ];
        }
    }

    /**
     * Determine overall rider status
     */
    private function determineOverallStatus(array $user, ?array $location, array $scheduleStatus): string
    {
        // Account is suspended or inactive
        if ($user['status'] !== 'active') {
            return 'inactive';
        }

        // Not available in users table
        if (!$user['is_available']) {
            return 'offline';
        }

        // No location data or not online
        if (!$location || !$location['is_online']) {
            return 'offline';
        }

        // Check if within scheduled hours
        if ($scheduleStatus['scheduled'] && !$scheduleStatus['available']) {
            return 'offline_schedule';
        }

        return 'online';
    }

    /**
     * Log status changes
     */
    private function logStatusChange(int $riderId, string $status, string $message): void
    {
        try {
            // You can implement logging to a separate table or file here
            error_log("Rider Status Change - ID: $riderId, Status: $status, Message: $message");
        } catch (\Exception $e) {
            error_log("Error logging status change: " . $e->getMessage());
        }
    }

    /**
     * Get rider statistics
     */
    public function getRiderStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_riders,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_riders,
                        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_riders,
                        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_riders
                    FROM users 
                    WHERE role = 'rider'";

            $stmt = $this->db->query($sql);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get online riders count
            $onlineCount = count($this->getOnlineRiders(1000));

            return [
                'total_riders' => (int)$stats['total_riders'],
                'active_riders' => (int)$stats['active_riders'],
                'available_riders' => (int)$stats['available_riders'],
                'online_riders' => $onlineCount,
                'suspended_riders' => (int)$stats['suspended_riders']
            ];

        } catch (\Exception $e) {
            error_log("Error getting rider statistics: " . $e->getMessage());
            return [
                'total_riders' => 0,
                'active_riders' => 0,
                'available_riders' => 0,
                'online_riders' => 0,
                'suspended_riders' => 0
            ];
        }
    }
}
