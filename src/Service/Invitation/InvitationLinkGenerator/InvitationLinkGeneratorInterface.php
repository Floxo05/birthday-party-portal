<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationLinkGenerator;

use App\Entity\Invitation;

interface InvitationLinkGeneratorInterface
{
    /**
     * @param Invitation $invitation
     * @return string
     * @throws \LogicException
     */
    public function generate(Invitation $invitation): string;
}