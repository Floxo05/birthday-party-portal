<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationLinkGenerator;

use App\Entity\Invitation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationLinkGenerator implements InvitationLinkGeneratorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function generate(Invitation $invitation): string
    {
        return $this->urlGenerator->generate(
            'app_invite',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}