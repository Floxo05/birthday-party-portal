<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberFactory;

use App\Entity\Guest;
use App\Entity\Host;
use App\Entity\PartyMember;

interface PartyMemberFactoryInterface
{
    public const ROLE_CLASS_MAP = [
        PartyMember::ROLE_GUEST => Guest::class,
        PartyMember::ROLE_HOST => Host::class,
    ];

    public function createPartyMemberByRole(string $role): PartyMember;
}