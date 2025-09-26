<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartyGroupAssignmentRepository;
use App\Validator\SameParty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PartyGroupAssignmentRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_group_member', columns: ['group_id','party_member_id'])]
class PartyGroupAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    private ?PartyGroup $group = null;

    #[ORM\ManyToOne]
    #[SameParty]
    private ?PartyMember $partyMember = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getGroup(): ?PartyGroup
    {
        return $this->group;
    }

    public function setGroup(?PartyGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getPartyMember(): ?PartyMember
    {
        return $this->partyMember;
    }

    public function setPartyMember(?PartyMember $partyMember): static
    {
        $this->partyMember = $partyMember;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s gehört zu %s', $this->getPartyMember()?->getUser()?->getName(), $this->getGroup()?->getName());
    }
}


