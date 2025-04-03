<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationLinkGenerator;

use App\Entity\Invitation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class InvitationLinkGenerator implements InvitationLinkGeneratorInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @param Invitation $invitation
     * @return string
     * @throws \LogicException
     */
    public function generate(Invitation $invitation): string
    {
        if ($invitation->getToken() === null)
        {
            throw new \LogicException('Invitation link cannot be generated: token is null');
        }

        return $this->urlGenerator->generate(
            'app_invite',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}