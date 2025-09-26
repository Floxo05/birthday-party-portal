<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GuestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuestRepository::class)]
class Guest extends PartyMember
{
    public function getRole(): string
    {
        return self::ROLE_GUEST;
    }
}
