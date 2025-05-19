<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Party;
use App\Entity\User;

final readonly class BeforeLoadDataForPartyEvent
{
    public function __construct(
        public User $user,
        public Party $party,
    ) {
    }
}