<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartyMemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;


#[ORM\Entity(repositoryClass: PartyMemberRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['host' => PartyMember::ROLE_HOST, 'guest' => PartyMember::ROLE_GUEST])]
abstract class PartyMember
{
    const string ROLE_HOST = 'Host';
    const string ROLE_GUEST = 'Guest';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'partyMembers')]
    private ?Party $party = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'partyMembers')]
    private ?User $user = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): static
    {
        $this->party = $party;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    abstract public function getRole(): string;
}
