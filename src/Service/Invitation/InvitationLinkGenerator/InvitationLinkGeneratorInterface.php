<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationLinkGenerator;

use App\Entity\Invitation;

interface InvitationLinkGeneratorInterface
{
    public function generate(Invitation $invitation): string;
}