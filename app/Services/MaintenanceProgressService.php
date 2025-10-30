<?php

namespace App\Services;

class MaintenanceProgressService
{
    private const TEMPLATES = [
        'lavadora' => 9,
        'secadora' => 7,
        'centrifuga' => 5,
    ];

    public function getTotalByType(?string $type, ?int $fallback): ?int
    {
        if ($type && isset(self::TEMPLATES[$type])) {
            return self::TEMPLATES[$type];
        }
        return $fallback;
    }

    public function sanitizeCompleted(array $completed, int $total): array
    {
        if ($total <= 0) {
            return [];
        }

        $clean = array_values(array_unique(array_filter($completed, function ($i) use ($total) {
            return is_int($i) && $i >= 0 && $i < $total;
        })));
        sort($clean);
        return $clean;
    }

    public function computeStatus(int $count, int $total): string
    {
        if ($total <= 0 || $count === 0) {
            return 'pending';
        }
        if ($count < $total) {
            return 'in_progress';
        }
        return 'completed';
    }

    public function computePct(int $count, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }
        return round(($count / $total) * 100, 2);
    }
}



