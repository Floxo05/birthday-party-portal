<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberRoleProvider;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)] // todo remove if used
interface PartyMemberRoleProviderInterface
{
    /**
     * @return array<string>
     */
    public function getAvailableRoles(): array;
}