<?php

declare(strict_types=1);

namespace App\Service\Invitation\TokenGenerator;

use App\Repository\InvitationRepository;
use Symfony\Component\Uid\Uuid;

class TokenGenerator implements TokenGeneratorInterface
{

    public function __construct(
        private readonly InvitationRepository $invitationRepository
    ) {
    }

    public function generate(): string
    {
        do
        {
            $token = Uuid::v4()->toRfc4122();
            $exists = $this->invitationRepository->findOneBy(['token' => $token]);
        } while ($exists !== null);

        return $token;
    }
}