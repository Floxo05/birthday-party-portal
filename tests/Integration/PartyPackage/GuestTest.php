<?php
declare(strict_types=1);

namespace App\Tests\Integration\PartyPackage;

use App\Entity\Guest;
use App\Enum\ResponseStatus;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Trait\PartyTrait;
use App\Tests\Integration\Trait\UserTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GuestTest extends DatabaseTestCase
{
    use UserTrait;
    use PartyTrait;

    /**
     * @throws \Exception
     */
    public function testCreatePartyMember(): void
    {
        $hasher = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->getNewUser($hasher);
        $party = $this->getNewParty();

        $guest = new Guest();
        $guest
            ->setParty($party)
            ->setUser($user)
            ->setResponseStatus(ResponseStatus::PENDING)
        ;

        $this->entityManager->persist($guest);
        
        $this->entityManager->flush();

        $this->assertNotNull($guest->getId());
    }
}