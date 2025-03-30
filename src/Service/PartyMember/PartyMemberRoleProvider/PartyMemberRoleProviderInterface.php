<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberRoleProvider;

interface PartyMemberRoleProviderInterface
{
    /**
     * @return array<string>
     */
    public function getAvailableRoles(): array;
}