<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberRoleTranslator;

use App\Entity\PartyMember;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class PartyMemberRoleTranslator implements PartyMemberRoleTranslatorInterface
{

    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function translate(string $role): string
    {
        $roleKey = match ($role)
        {
            PartyMember::ROLE_GUEST => 'party.roles.guest',
            PartyMember::ROLE_HOST => 'party.roles.host',
            default => $role,
        };

        return $this->translator->trans($roleKey, [], 'admin');
    }
}