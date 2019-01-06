<?php

namespace App\Twig;

use App\Services\StringFormatterService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DurationExtension extends AbstractExtension
{
    private $formatter;

    public function __construct(StringFormatterService $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('duration', array($this, 'formatDuration')),
        );
    }

    public function formatDuration($iso8601, $format = '%d д., %h ч.')
    {
        return $this->formatter->formatDuration($iso8601, $format);
    }
}
