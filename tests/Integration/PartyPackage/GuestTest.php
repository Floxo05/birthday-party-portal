<?php
declare(strict_types=1);

namespace App\Tests\Integration\PartyPackage;

use App\Entity\PartyPackage\Guest;
use App\Entity\PartyPackage\Host;
use App\Entity\PartyPackage\PartyMember;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Trait\PartyTrait;
use App\Tests\Integration\Trait\UserTrait;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GuestTest extends DatabaseTestCase
{
    use UserTrait;
    use PartyTrait;

    public function testCreatePartyMember(): void
    {
        $hasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->getNewUser($hasher);
        $party = $this->getNewParty();

        $guest = new Guest();
        $guest
            ->setParty($party)
            ->setUser($user)
        ;

        $this->entityManager->persist($guest);
        
        $this->entityManager->flush();

        $this->assertNotNull($guest->getId());
    }
}