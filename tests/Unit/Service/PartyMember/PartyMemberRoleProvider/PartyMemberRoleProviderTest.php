<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\PartyMember\PartyMemberRoleProvider;

use App\Entity\Guest;
use App\Entity\Host;
use App\Entity\Party;
use App\Service\PartyMember\PartyMemberRoleProvider\PartyMemberRoleProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class PartyMemberRoleProviderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testGetAvailableRolesReturnsShortNamesOfPartyMemberSubclasses(): void
    {
        $guestMetadata = $this->createMock(ClassMetadata::class);
        $guestMetadata->method('getName')->willReturn(Guest::class);

        $hostMetadata = $this->createMock(ClassMetadata::class);
        $hostMetadata->method('getName')->willReturn(Host::class);

        $nonMemberMetadata = $this->createMock(ClassMetadata::class);
        $nonMemberMetadata->method('getName')->willReturn(Party::class);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory
            ->method('getAllMetadata')
            ->willReturn([$guestMetadata, $hostMetadata, $nonMemberMetadata]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $provider = new PartyMemberRoleProvider($entityManager);

        $roles = $provider->getAvailableRoles();

        $this->assertContains('Guest', $roles);
        $this->assertContains('Host', $roles);
        $this->assertNotContains('Party', $roles);
        $this->assertCount(2, $roles);
    }
}
