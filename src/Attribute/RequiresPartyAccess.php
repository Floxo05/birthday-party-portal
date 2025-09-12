<?php

declare(strict_types=1);

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class RequiresPartyAccess
{
    public function __construct(
        public readonly bool $redirectIfForeshadowing = true
    ) {
    }
}


