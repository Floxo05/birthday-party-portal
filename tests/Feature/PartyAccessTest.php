<?php

namespace App\Tests\Feature;

use App\Factory\PartyFactory;
use App\Factory\PartyMemberFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PartyAccessTest extends WebTestCase
{
    public function testUserCannotSeeOthersParty(): void
    {
        $client = static::createClient();

        // Benutzer A und B erstellen
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);
        $userA = $userFactory->create();
        $userB = $userFactory->create();

        /** @var PartyFactory $partyFactory */
        $partyFactory = $this->getContainer()->get(PartyFactory::class);
        $party = $partyFactory->create(['title' => 'Geheime Party']);

        /** @var PartyMemberFactory $partyMemberFactory */
        $partyMemberFactory = $this->getContainer()->get(PartyMemberFactory::class);
        $host = $partyMemberFactory->createHost($userA, $party);

        // Als Benutzer B einloggen
        $client->loginUser($userB);

        // 1️⃣ Übersichtsseite aufrufen – Party sollte nicht sichtbar sein
        $crawler = $client->request('GET', '/');
        $this->assertSelectorTextNotContains('body', 'Geheime Party');

        // 2️⃣ Direktzugriff auf Detailseite – sollte 403 Forbidden zurückgeben
        $client->request('GET', '/party/' . $party->getId());
        $this->assertResponseStatusCodeSame(403);
    }
}
