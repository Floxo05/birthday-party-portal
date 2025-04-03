<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InvitationRepository;
use App\Validator\ValidPartyMemberRole\ValidPartyMemberRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Party $party = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[ValidPartyMemberRole]
    private ?string $role = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $token = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    #[Assert\Expression('this.getMaxUses() > this.getUses()')]
    private ?int $maxUses = null;

    #[ORM\Column]
    private ?int $uses = null;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getMaxUses(): ?int
    {
        return $this->maxUses;
    }

    public function setMaxUses(?int $maxUses): static
    {
        $this->maxUses = $maxUses;

        return $this;
    }

    public function getUses(): ?int
    {
        return $this->uses;
    }

    public function setUses(?int $uses): static
    {
        $this->uses = $uses;

        return $this;
    }

    public function incrementUses(): static
    {
        $this->uses++;

        return $this;
    }

    public function __toString(): string
    {
        return $this->party . ' - ' . $this->role;
    }
}
