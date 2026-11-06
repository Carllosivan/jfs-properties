<?php

namespace App\Services;

class SlaService
{
    /**
     * Calculate deadlines in hours based on priority
     */
    public static function getHoursForPriority(string $priority): array
    {
        return match ($priority) {
            'critical' => ['response' => 1, 'resolution' => 4],
            'urgent' => ['response' => 2, 'resolution' => 8],
            'high' => ['response' => 4, 'resolution' => 16],
            'low' => ['response' => 12, 'resolution' => 72],
            default => ['response' => 6, 'resolution' => 24], // normal
        };
    }

    public static function calculateDeadlines(string $priority): array
    {
        $hours = self::getHoursForPriority($priority);
        $now = time();
        
        return [
            'response_deadline' => date('Y-m-d H:i:s', $now + ($hours['response'] * 3600)),
            'resolution_deadline' => date('Y-m-d H:i:s', $now + ($hours['resolution'] * 3600)),
        ];
    }

    public static function isBreached(?string $deadline, ?string $completedAt = null): bool
    {
        if (!$deadline) return false;
        $deadlineTs = strtotime($deadline);
        $checkTs = $completedAt ? strtotime($completedAt) : time();
        return $checkTs > $deadlineTs;
    }
}
