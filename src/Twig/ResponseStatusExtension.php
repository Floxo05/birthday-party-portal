<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\ResponseStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ResponseStatusExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('response_status_label', [$this, 'responseStatusLabel']),
        ];
    }

    public function responseStatusLabel(ResponseStatus $status): string
    {
        return $status->getLabel();
    }
}
