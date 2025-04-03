<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Repository\InvitationRepository;
use App\Service\Invitation\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;

readonly class InvitationManager implements InvitationManagerInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvitationRepository $invitationRepository,
        private TokenGeneratorInterface $tokenGenerator,
        private ClockInterface $clock,
    ) {
    }

    public function createInvitation(
        Party $party,
        string $role,
        \DateTimeImmutable $expiresAt,
        int $maxUses = 1
    ): Invitation {
        $invitation = (new Invitation())
            ->setParty($party)
            ->setRole($role)
            ->setToken($this->tokenGenerator->generate())
            ->setExpiresAt($expiresAt)
            ->setUses(0)
            ->setMaxUses($maxUses);

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        return $invitation;
    }

    public function getValidInvitation(string $token): ?Invitation
    {
        $invitation = $this->invitationRepository->findOneBy(['token' => $token]);

        if (!$invitation)
        {
            return null;
        }

        if ($invitation->getExpiresAt() <= $this->clock->now())
        {
            return null;
        }

        return $invitation;
    }
}