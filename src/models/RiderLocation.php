<?php

declare(strict_types=1);

namespace Time2Eat\Models;

use core\Model;

/**
 * Rider Location Model
 * Manages real-time rider location tracking
 */
class RiderLocation extends Model
{
    protected $table = 'rider_locations';

    /**
     * Create new location record
     */
    public function createLocation(array $locationData): ?int
    {
        $locationData['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($locationData);
    }

    /**
     * Get latest location for rider
     */
    public function getLatestLocation(int $riderId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";

        return $this->fetchOne($sql, [$riderId]);
    }

    /**
     * Get rider's location history
     */
    public function getLocationHistory(int $riderId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";

        return $this->fetchAll($sql, [$riderId, $limit]);
    }

    /**
     * Get locations within time range
     */
    public function getLocationsInTimeRange(int $riderId, string $startTime, string $endTime): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                AND created_at BETWEEN ? AND ?
                ORDER BY created_at ASC";

        return $this->fetchAll($sql, [$riderId, $startTime, $endTime]);
    }

    /**
     * Get nearby riders
     */
    public function getNearbyRiders(float $latitude, float $longitude, float $radiusKm = 10, int $limit = 20): array
    {
        $sql = "SELECT rl.*, u.first_name, u.last_name, u.phone, u.profile_image,
                       (6371 * acos(cos(radians(?)) * cos(radians(rl.latitude)) * 
                        cos(radians(rl.longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(rl.latitude)))) AS distance
                FROM {$this->table} rl
                JOIN users u ON rl.rider_id = u.id
                WHERE rl.is_online = 1
                AND u.status = 'active'
                AND u.role = 'rider'
                AND rl.id = (
                    SELECT id FROM {$this->table} 
                    WHERE rider_id = rl.rider_id 
                    ORDER BY created_at DESC 
                    LIMIT 1
                )
                HAVING distance <= ?
                ORDER BY distance ASC
                LIMIT ?";

        return $this->fetchAll($sql, [$latitude, $longitude, $latitude, $radiusKm, $limit]);
    }

    /**
     * Update rider online status
     */
    public function updateOnlineStatus(int $riderId, bool $isOnline): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_online = ? 
                WHERE rider_id = ? 
                AND id = (
                    SELECT id FROM (
                        SELECT id FROM {$this->table} 
                        WHERE rider_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) as latest
                )";

        return $this->execute($sql, [$isOnline ? 1 : 0, $riderId, $riderId]) > 0;
    }

    /**
     * Get online riders count
     */
    public function getOnlineRidersCount(): int
    {
        $sql = "SELECT COUNT(DISTINCT rider_id) as count
                FROM {$this->table} rl1
                WHERE rl1.is_online = 1
                AND rl1.id = (
                    SELECT id FROM {$this->table} rl2
                    WHERE rl2.rider_id = rl1.rider_id
                    ORDER BY created_at DESC
                    LIMIT 1
                )
                AND rl1.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";

        $result = $this->fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get rider's current speed and heading
     */
    public function getRiderMovementData(int $riderId): ?array
    {
        $sql = "SELECT speed, heading, accuracy, battery_level, created_at
                FROM {$this->table} 
                WHERE rider_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";

        return $this->fetchOne($sql, [$riderId]);
    }

    /**
     * Calculate distance traveled by rider
     */
    public function getDistanceTraveled(int $riderId, string $startTime, string $endTime): float
    {
        $locations = $this->getLocationsInTimeRange($riderId, $startTime, $endTime);
        
        if (count($locations) < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;
        
        for ($i = 1; $i < count($locations); $i++) {
            $prev = $locations[$i - 1];
            $curr = $locations[$i];
            
            $distance = $this->calculateDistance(
                (float)$prev['latitude'],
                (float)$prev['longitude'],
                (float)$curr['latitude'],
                (float)$curr['longitude']
            );
            
            $totalDistance += $distance;
        }

        return round($totalDistance, 2);
    }

    /**
     * Get rider's average speed
     */
    public function getAverageSpeed(int $riderId, string $period = '1hour'): float
    {
        $timeCondition = $this->getTimeCondition($period);
        
        $sql = "SELECT AVG(speed) as avg_speed
                FROM {$this->table} 
                WHERE rider_id = ? 
                AND speed IS NOT NULL 
                AND speed > 0 
                $timeCondition";

        $result = $this->fetchOne($sql, [$riderId]);
        return round((float)($result['avg_speed'] ?? 0), 2);
    }

    /**
     * Get location accuracy statistics
     */
    public function getLocationAccuracyStats(int $riderId): array
    {
        $sql = "SELECT 
                    AVG(accuracy) as avg_accuracy,
                    MIN(accuracy) as min_accuracy,
                    MAX(accuracy) as max_accuracy,
                    COUNT(*) as total_locations,
                    COUNT(CASE WHEN accuracy <= 10 THEN 1 END) as high_accuracy_count
                FROM {$this->table} 
                WHERE rider_id = ? 
                AND accuracy IS NOT NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

        return $this->fetchOne($sql, [$riderId]) ?: [];
    }

    /**
     * Clean old location data
     */
    public function cleanOldLocations(int $daysToKeep = 7): int
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

        return $this->execute($sql, [$daysToKeep]);
    }

    /**
     * Get rider's location at specific time
     */
    public function getLocationAtTime(int $riderId, string $timestamp): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                AND created_at <= ?
                ORDER BY created_at DESC 
                LIMIT 1";

        return $this->fetchOne($sql, [$riderId, $timestamp]);
    }

    /**
     * Check if rider has moved significantly
     */
    public function hasRiderMoved(int $riderId, float $thresholdMeters = 50): bool
    {
        $sql = "SELECT latitude, longitude FROM {$this->table} 
                WHERE rider_id = ? 
                ORDER BY created_at DESC 
                LIMIT 2";

        $locations = $this->fetchAll($sql, [$riderId]);
        
        if (count($locations) < 2) {
            return false;
        }

        $distance = $this->calculateDistance(
            (float)$locations[1]['latitude'],
            (float)$locations[1]['longitude'],
            (float)$locations[0]['latitude'],
            (float)$locations[0]['longitude']
        );

        return ($distance * 1000) > $thresholdMeters; // Convert km to meters
    }

    /**
     * Get riders in delivery zone
     */
    public function getRidersInDeliveryZone(array $zone): array
    {
        $centerLat = $zone['center'][0];
        $centerLng = $zone['center'][1];
        $radius = $zone['radius'];

        return $this->getNearbyRiders($centerLat, $centerLng, $radius);
    }

    /**
     * Update battery level
     */
    public function updateBatteryLevel(int $riderId, int $batteryLevel): bool
    {
        $sql = "UPDATE {$this->table} 
                SET battery_level = ? 
                WHERE rider_id = ? 
                AND id = (
                    SELECT id FROM (
                        SELECT id FROM {$this->table} 
                        WHERE rider_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) as latest
                )";

        return $this->execute($sql, [$batteryLevel, $riderId, $riderId]) > 0;
    }

    /**
     * Get riders with low battery
     */
    public function getRidersWithLowBattery(int $threshold = 20): array
    {
        $sql = "SELECT rl.*, u.first_name, u.last_name, u.phone
                FROM {$this->table} rl
                JOIN users u ON rl.rider_id = u.id
                WHERE rl.battery_level IS NOT NULL 
                AND rl.battery_level <= ?
                AND rl.is_online = 1
                AND rl.id = (
                    SELECT id FROM {$this->table} 
                    WHERE rider_id = rl.rider_id 
                    ORDER BY created_at DESC 
                    LIMIT 1
                )
                ORDER BY rl.battery_level ASC";

        return $this->fetchAll($sql, [$threshold]);
    }

    /**
     * Get location tracking statistics
     */
    public function getTrackingStats(int $riderId, string $period = '24hours'): array
    {
        $timeCondition = $this->getTimeCondition($period);
        
        $sql = "SELECT 
                    COUNT(*) as total_updates,
                    MIN(created_at) as first_update,
                    MAX(created_at) as last_update,
                    AVG(accuracy) as avg_accuracy,
                    AVG(speed) as avg_speed,
                    MIN(battery_level) as min_battery,
                    MAX(battery_level) as max_battery
                FROM {$this->table} 
                WHERE rider_id = ? 
                $timeCondition";

        return $this->fetchOne($sql, [$riderId]) ?: [];
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Get time condition for queries
     */
    private function getTimeCondition(string $period): string
    {
        switch ($period) {
            case '1hour':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            case '24hours':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            case '7days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "";
        }
    }
}
