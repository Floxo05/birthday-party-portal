<?php
declare(strict_types=1);

namespace App\Tests\Integration\PartyPackage;

use App\Entity\PartyPackage\Host;
use App\Entity\PartyPackage\PartyMember;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Trait\PartyTrait;
use App\Tests\Integration\Trait\UserTrait;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HostTest extends DatabaseTestCase
{
    use UserTrait;
    use PartyTrait;

    public function testCreatePartyMember(): void
    {
        $hasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->getNewUser($hasher);
        $party = $this->getNewParty();

        $host = new Host();
        $host
            ->setParty($party)
            ->setUser($user)
        ;

        $this->entityManager->persist($host);

        $this->entityManager->flush();

        $this->assertNotNull($host->getId());
    }
}