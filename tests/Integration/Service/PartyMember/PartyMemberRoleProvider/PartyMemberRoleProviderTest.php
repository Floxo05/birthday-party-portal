<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\PartyMember\PartyMemberRoleProvider;

use App\Entity\PartyMember;
use App\Service\PartyMember\PartyMemberRoleProvider\PartyMemberRoleProviderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PartyMemberRoleProviderTest extends KernelTestCase
{

    public function testGetAvailableRoles(): void
    {
        /** @var PartyMemberRoleProviderInterface $roleProvider */
        $roleProvider = $this->getContainer()->get(PartyMemberRoleProviderInterface::class);

        $roles = $roleProvider->getAvailableRoles();

        $this->assertContains(PartyMember::ROLE_GUEST, $roles);
        $this->assertContains(PartyMember::ROLE_HOST, $roles);

        $this->assertCount(2, $roles);
    }
}