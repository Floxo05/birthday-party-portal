<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HostRepository::class)]
class Host extends PartyMember
{
    public function getRole(): string
    {
        return self::ROLE_HOST;
    }
}
