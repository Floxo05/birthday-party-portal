<?php

declare(strict_types=1);

namespace App\DTO\Admin;

use App\Validator\ValidPartyMemberRole\ValidPartyMemberRole;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class InvitationDataDTO
{
    public function __construct(
        #[ValidPartyMemberRole]
        public string $role,

        #[Assert\GreaterThan(0)]
        public int $maxUses,

        #[Assert\GreaterThan('today')]
        public DateTimeInterface $expiresAt
    ) {
    }
}