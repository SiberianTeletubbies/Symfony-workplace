<?php

namespace App\Twig;

use App\Services\StringFormatterService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TaskPriorityExtension extends AbstractExtension
{
    private $formatter;

    public function __construct(StringFormatterService $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('taskPriority', [$this, 'formatTaskPriority']),
        ];
    }

    public function formatTaskPriority($priority)
    {
        return $this->formatter->formatPriorityTask($priority);
    }
}
