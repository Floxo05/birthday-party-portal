<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationTranslator;

use App\Entity\Invitation;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class InvitationTranslator implements InvitationToStringTranslatorInterface
{


    public function __construct(
        private PartyMemberRoleTranslatorInterface $roleTranslator,
        private TranslatorInterface $translator
    ) {
    }

    public function translate(Invitation $invitation): string
    {
        if ($invitation->getParty() === null
            || $invitation->getParty()->getPartyDate() === null
            || $invitation->getRole() === null
        )
        {
            throw new \InvalidArgumentException('Die Einladung kann nicht übersetzt werden.');
        }

        return $this->translator->trans('invitation.message', [
            '%party%' => $invitation->getParty()->getTitle(),
            '%date%' => $invitation->getParty()->getPartyDate()->format('d.m.Y'),
            '%role%' => $this->roleTranslator->translate($invitation->getRole()),
        ], 'admin');
    }
}