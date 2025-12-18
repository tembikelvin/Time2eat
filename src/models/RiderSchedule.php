<?php

declare(strict_types=1);

namespace Time2Eat\Models;

use core\Model;

/**
 * Rider Schedule Model
 * Manages rider availability schedules and working hours
 */
class RiderSchedule extends Model
{
    protected $table = 'rider_schedules';

    /**
     * Get current schedule for rider
     */
    public function getCurrentSchedule(int $riderId): ?array
    {
        $currentDay = (int)date('w'); // 0 = Sunday, 1 = Monday, etc.
        $currentTime = date('H:i:s');

        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? AND day_of_week = ? 
                AND is_available = 1
                AND start_time <= ? AND end_time >= ?
                LIMIT 1";

        return $this->fetchOne($sql, [$riderId, $currentDay, $currentTime, $currentTime]);
    }

    /**
     * Get rider's weekly schedule
     */
    public function getWeeklySchedule(int $riderId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                ORDER BY day_of_week ASC";

        return $this->fetchAll($sql, [$riderId]);
    }

    /**
     * Create or update schedule for a day
     */
    public function setDaySchedule(int $riderId, int $dayOfWeek, string $startTime, string $endTime, bool $isAvailable = true, int $maxOrders = 10): bool
    {
        // Check if schedule exists for this day
        $existing = $this->fetchOne(
            "SELECT id FROM {$this->table} WHERE rider_id = ? AND day_of_week = ?",
            [$riderId, $dayOfWeek]
        );

        $data = [
            'rider_id' => $riderId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_available' => $isAvailable ? 1 : 0,
            'max_orders' => $maxOrders,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->create($data) !== null;
        }
    }

    /**
     * Update rider availability for current day
     */
    public function updateAvailability(int $riderId, bool $isAvailable): bool
    {
        $currentDay = (int)date('w');
        
        $sql = "UPDATE {$this->table} 
                SET is_available = ?, updated_at = ? 
                WHERE rider_id = ? AND day_of_week = ?";

        return $this->execute($sql, [$isAvailable ? 1 : 0, date('Y-m-d H:i:s'), $riderId, $currentDay]) > 0;
    }

    /**
     * Check if rider is available now
     */
    public function isRiderAvailable(int $riderId): bool
    {
        $schedule = $this->getCurrentSchedule($riderId);
        return $schedule !== null;
    }

    /**
     * Get available riders for a time slot
     */
    public function getAvailableRiders(\DateTime $dateTime): array
    {
        $dayOfWeek = (int)$dateTime->format('w');
        $time = $dateTime->format('H:i:s');

        $sql = "SELECT rs.*, u.first_name, u.last_name, u.phone, u.profile_image,
                       rl.latitude, rl.longitude, rl.is_online
                FROM {$this->table} rs
                JOIN users u ON rs.rider_id = u.id
                LEFT JOIN rider_locations rl ON u.id = rl.rider_id 
                    AND rl.id = (
                        SELECT id FROM rider_locations 
                        WHERE rider_id = u.id 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    )
                WHERE rs.day_of_week = ? 
                AND rs.is_available = 1
                AND rs.start_time <= ? 
                AND rs.end_time >= ?
                AND u.status = 'active'
                AND u.role = 'rider'
                AND (rl.is_online = 1 OR rl.is_online IS NULL)";

        return $this->fetchAll($sql, [$dayOfWeek, $time, $time]);
    }

    /**
     * Get rider's maximum concurrent orders for current time
     */
    public function getRiderMaxOrders(int $riderId): int
    {
        $schedule = $this->getCurrentSchedule($riderId);
        return $schedule ? (int)$schedule['max_orders'] : 3; // Default to 3
    }

    /**
     * Set rider's maximum concurrent orders
     */
    public function setMaxOrders(int $riderId, int $maxOrders): bool
    {
        $currentDay = (int)date('w');
        
        $sql = "UPDATE {$this->table} 
                SET max_orders = ?, updated_at = ? 
                WHERE rider_id = ? AND day_of_week = ?";

        return $this->execute($sql, [$maxOrders, date('Y-m-d H:i:s'), $riderId, $currentDay]) > 0;
    }

    /**
     * Get schedule statistics for rider
     */
    public function getScheduleStats(int $riderId, string $period = '30days'): array
    {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    COUNT(DISTINCT DATE(created_at)) as days_scheduled,
                    AVG(max_orders) as avg_max_orders,
                    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_days,
                    COUNT(*) as total_schedule_entries
                FROM {$this->table} 
                WHERE rider_id = ? $dateCondition";

        return $this->fetchOne($sql, [$riderId]) ?: [];
    }

    /**
     * Create default schedule for new rider
     */
    public function createDefaultSchedule(int $riderId): bool
    {
        $defaultSchedule = [
            // Monday to Friday: 8 AM to 8 PM
            1 => ['08:00:00', '20:00:00'],
            2 => ['08:00:00', '20:00:00'],
            3 => ['08:00:00', '20:00:00'],
            4 => ['08:00:00', '20:00:00'],
            5 => ['08:00:00', '20:00:00'],
            // Saturday: 9 AM to 9 PM
            6 => ['09:00:00', '21:00:00'],
            // Sunday: 10 AM to 6 PM
            0 => ['10:00:00', '18:00:00']
        ];

        try {
            $this->beginTransaction();

            foreach ($defaultSchedule as $dayOfWeek => $times) {
                $this->setDaySchedule($riderId, $dayOfWeek, $times[0], $times[1], true, 5);
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Update entire weekly schedule
     */
    public function updateWeeklySchedule(int $riderId, array $scheduleData): bool
    {
        try {
            $this->beginTransaction();

            foreach ($scheduleData as $dayOfWeek => $dayData) {
                $this->setDaySchedule(
                    $riderId,
                    (int)$dayOfWeek,
                    $dayData['start_time'],
                    $dayData['end_time'],
                    (bool)$dayData['is_available'],
                    (int)($dayData['max_orders'] ?? 5)
                );
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Get riders working in a specific time range
     */
    public function getRidersInTimeRange(string $startTime, string $endTime, int $dayOfWeek): array
    {
        $sql = "SELECT rs.*, u.first_name, u.last_name, u.phone
                FROM {$this->table} rs
                JOIN users u ON rs.rider_id = u.id
                WHERE rs.day_of_week = ? 
                AND rs.is_available = 1
                AND rs.start_time <= ? 
                AND rs.end_time >= ?
                AND u.status = 'active'
                AND u.role = 'rider'
                ORDER BY rs.start_time ASC";

        return $this->fetchAll($sql, [$dayOfWeek, $endTime, $startTime]);
    }

    /**
     * Check for schedule conflicts
     */
    public function hasScheduleConflict(int $riderId, int $dayOfWeek, string $startTime, string $endTime): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE rider_id = ? AND day_of_week = ?
                AND is_available = 1
                AND (
                    (start_time <= ? AND end_time >= ?) OR
                    (start_time <= ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                )";

        $result = $this->fetchOne($sql, [
            $riderId, $dayOfWeek,
            $startTime, $startTime,
            $endTime, $endTime,
            $startTime, $endTime
        ]);

        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Get schedule for specific date range
     */
    public function getScheduleForDateRange(int $riderId, string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rider_id = ? 
                AND created_at BETWEEN ? AND ?
                ORDER BY day_of_week ASC, start_time ASC";

        return $this->fetchAll($sql, [$riderId, $startDate, $endDate]);
    }

    /**
     * Delete schedule for a specific day
     */
    public function deleteScheduleForDay(int $riderId, int $dayOfWeek): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE rider_id = ? AND day_of_week = ?";

        return $this->execute($sql, [$riderId, $dayOfWeek]) > 0;
    }

    /**
     * Get working hours summary for rider
     */
    public function getWorkingHoursSummary(int $riderId): array
    {
        $sql = "SELECT 
                    day_of_week,
                    start_time,
                    end_time,
                    is_available,
                    max_orders,
                    CASE day_of_week
                        WHEN 0 THEN 'Sunday'
                        WHEN 1 THEN 'Monday'
                        WHEN 2 THEN 'Tuesday'
                        WHEN 3 THEN 'Wednesday'
                        WHEN 4 THEN 'Thursday'
                        WHEN 5 THEN 'Friday'
                        WHEN 6 THEN 'Saturday'
                    END as day_name,
                    TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600 as hours_per_day
                FROM {$this->table} 
                WHERE rider_id = ?
                ORDER BY day_of_week ASC";

        return $this->fetchAll($sql, [$riderId]);
    }

    /**
     * Get date condition for queries
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'today':
                return "AND DATE(created_at) = CURDATE()";
            case 'week':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '30days':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "";
        }
    }
}
