<?php
declare(strict_types=1);

namespace App\Form\Model;

use App\Validator\ValidPartyMemberRole\ValidPartyMemberRole;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class InvitationFormModel
{
    #[ValidPartyMemberRole]
    public ?string $role = null;

    #[Assert\GreaterThan(0)]
    public ?int $maxUses = null;

    #[Assert\GreaterThan('today')]
    public ?DateTimeInterface $expiresAt = null;
}