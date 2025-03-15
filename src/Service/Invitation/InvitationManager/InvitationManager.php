<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Repository\InvitationRepository;
use App\Service\Invitation\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;

class InvitationManager implements InvitationManagerInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InvitationRepository $invitationRepository,
        private readonly TokenGeneratorInterface $tokenGenerator,
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

        return ($invitation && $invitation->getExpiresAt() > new \DateTimeImmutable()) ? $invitation : null;
    }
}