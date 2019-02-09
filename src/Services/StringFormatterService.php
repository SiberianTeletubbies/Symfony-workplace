<?php

namespace App\Services;


class StringFormatterService
{
    public const PRIORITY_LOW = 0;
    public const PRIORITY_MEDIUM = 1;
    public const PRIORITY_HIGH = 2;

    public function formatDuration($iso8601, $format = '%d д., %h ч.')
    {
        $interval = new \DateInterval($iso8601);
        return $interval->format($format);
    }

    public function formatPriorityTask($priority)
    {
        $labels = [
            self::PRIORITY_LOW => 'Низкий',
            self::PRIORITY_MEDIUM => 'Средний',
            self::PRIORITY_HIGH => 'Высокий',
        ];
        return isset($labels[$priority]) ? $labels[$priority] : null;
    }
}
