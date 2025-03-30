<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberFactory;

use App\Entity\PartyMember;
use InvalidArgumentException;

class PartyMemberFactory implements PartyMemberFactoryInterface
{

    public function createPartyMemberByRole(string $role): PartyMember
    {
        if (!isset(self::ROLE_CLASS_MAP[$role]))
        {
            throw new InvalidArgumentException("Unbekannte Rolle: $role");
        }

        return new (self::ROLE_CLASS_MAP[$role])();
    }
}