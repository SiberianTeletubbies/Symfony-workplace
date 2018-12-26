<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DurationExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('duration', array($this, 'formatDuration')),
        );
    }

    public function formatDuration($iso8601, $format = '%d д., %h ч.')
    {
        $interval = new \DateInterval($iso8601);
        return $interval->format($format);
    }
}
