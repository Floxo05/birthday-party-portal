<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberRoleTranslator;

interface PartyMemberRoleTranslatorInterface
{
    public function translate(string $role): string;
}