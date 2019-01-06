<?php

namespace App\Services;


class StringFormatterService
{
    public function formatDuration($iso8601, $format = '%d д., %h ч.')
    {
        $interval = new \DateInterval($iso8601);
        return $interval->format($format);
    }
}
