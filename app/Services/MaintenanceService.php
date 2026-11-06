<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;

class MaintenanceService
{
    /**
     * Generate pending maintenance tasks idempotently
     */
    public static function generateUpcomingTasks(int $daysAhead = 30): array
    {
        $db = Database::getConnection();
        $targetDate = date('Y-m-d', strtotime("+{$daysAhead} days"));
        $today = date('Y-m-d');

        // Fetch active plans due up to targetDate
        $stmt = $db->prepare("
            SELECT * FROM maintenance_plans 
            WHERE status = 'active' AND next_execution_date <= :target_date
        ");
        $stmt->execute([':target_date' => $targetDate]);
        $plans = $stmt->fetchAll();

        $generatedTasks = [];

        foreach ($plans as $plan) {
            $scheduledDate = $plan['next_execution_date'];

            // Check idempotency: does a task already exist for this plan and scheduled date?
            $checkStmt = $db->prepare("
                SELECT id FROM maintenance_tasks 
                WHERE plan_id = :plan_id AND scheduled_date = :scheduled_date
            ");
            $checkStmt->execute([
                ':plan_id' => $plan['id'],
                ':scheduled_date' => $scheduledDate
            ]);
            $existingTask = $checkStmt->fetch();

            if (!$existingTask) {
                Database::beginTransaction();
                try {
                    // Create maintenance task
                    $taskStmt = $db->prepare("
                        INSERT INTO maintenance_tasks (plan_id, property_id, asset_id, scheduled_date, status, created_at)
                        VALUES (:plan_id, :property_id, :asset_id, :scheduled_date, 'scheduled', datetime('now'))
                    ");
                    $taskStmt->execute([
                        ':plan_id' => $plan['id'],
                        ':property_id' => $plan['property_id'],
                        ':asset_id' => $plan['asset_id'],
                        ':scheduled_date' => $scheduledDate
                    ]);
                    $taskId = (int)$db->lastInsertId();

                    // Calculate next execution date based on frequency_days
                    $freqDays = (int)($plan['frequency_days'] ?: 30);
                    $nextDate = date('Y-m-d', strtotime("{$scheduledDate} +{$freqDays} days"));

                    $updatePlanStmt = $db->prepare("
                        UPDATE maintenance_plans 
                        SET next_execution_date = :next_date, updated_at = datetime('now') 
                        WHERE id = :id
                    ");
                    $updatePlanStmt->execute([
                        ':next_date' => $nextDate,
                        ':id' => $plan['id']
                    ]);

                    Database::commit();
                    $generatedTasks[] = [
                        'task_id' => $taskId,
                        'plan_id' => $plan['id'],
                        'title' => $plan['title'],
                        'scheduled_date' => $scheduledDate
                    ];
                } catch (\Throwable $e) {
                    Database::rollBack();
                    Logger::error("Failed to generate task for plan {$plan['id']}: " . $e->getMessage());
                }
            }
        }

        return $generatedTasks;
    }

    /**
     * Mark overdue scheduled tasks
     */
    public static function checkOverdueTasks(): int
    {
        $db = Database::getConnection();
        $today = date('Y-m-d');
        
        $stmt = $db->prepare("
            UPDATE maintenance_tasks 
            SET status = 'overdue' 
            WHERE status = 'scheduled' AND scheduled_date < :today
        ");
        $stmt->execute([':today' => $today]);
        return $stmt->rowCount();
    }
}
