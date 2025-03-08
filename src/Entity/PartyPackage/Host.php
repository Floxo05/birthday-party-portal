<?php

declare(strict_types=1);

namespace App\Entity\PartyPackage;

use App\Repository\PartyPackage\HostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HostRepository::class)]
class Host extends PartyMember
{
}
