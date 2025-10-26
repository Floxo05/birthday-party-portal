<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GameScoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GameScoreRepository::class)]
#[ORM\Table(name: 'game_score')]
#[ORM\UniqueConstraint(name: 'uniq_game_party_member', columns: ['game_slug', 'party_id', 'party_member_id'])]
class GameScore
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(name: 'game_slug', type: 'string', length: 64)]
    private string $gameSlug;

    #[ORM\ManyToOne(targetEntity: Party::class)]
    #[ORM\JoinColumn(name: 'party_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Party $party;

    #[ORM\ManyToOne(targetEntity: PartyMember::class)]
    #[ORM\JoinColumn(name: 'party_member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private PartyMember $partyMember;

    #[ORM\Column(name: 'best_score', type: 'integer', options: ['default' => 0])]
    private int $bestScore = 0;

    #[ORM\Column(name: 'attempts', type: 'integer', options: ['default' => 0])]
    private int $attempts = 0;

    #[ORM\Column(name: 'last_submitted_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $lastSubmittedAt;

    #[ORM\Column(name: 'applied_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $appliedAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getGameSlug(): string
    {
        return $this->gameSlug;
    }

    public function setGameSlug(string $slug): self
    {
        $this->gameSlug = $slug;
        return $this;
    }

    public function getParty(): Party
    {
        return $this->party;
    }

    public function setParty(Party $party): self
    {
        $this->party = $party;
        return $this;
    }

    public function getPartyMember(): PartyMember
    {
        return $this->partyMember;
    }

    public function setPartyMember(PartyMember $pm): self
    {
        $this->partyMember = $pm;
        return $this;
    }

    public function getBestScore(): int
    {
        return $this->bestScore;
    }

    public function setBestScore(int $s): self
    {
        $this->bestScore = max(0, $s);
        return $this;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $n): self
    {
        $this->attempts = max(0, $n);
        return $this;
    }

    public function incAttempts(): self
    {
        $this->attempts++;
        return $this;
    }

    public function getLastSubmittedAt(): \DateTimeImmutable
    {
        return $this->lastSubmittedAt;
    }

    public function setLastSubmittedAt(\DateTimeImmutable $dt): self
    {
        $this->lastSubmittedAt = $dt;
        return $this;
    }

    public function getAppliedAt(): ?\DateTimeImmutable
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(?\DateTimeImmutable $dt): self
    {
        $this->appliedAt = $dt;
        return $this;
    }
}
