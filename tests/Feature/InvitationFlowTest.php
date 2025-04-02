<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Service\Invitation\InvitationSessionManager\InvitationSessionManagerInterface;
use App\Service\Invitation\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvitationFlowTest extends WebTestCase
{
    public function testUserCanAccessInvitation(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var TokenGeneratorInterface $tokenGenerator */
        $tokenGenerator = $container->get(TokenGeneratorInterface::class);
        $token = $tokenGenerator->generate();

        // Setup Party + Invitation
        $party = new Party();
        $party->setTitle('Testparty');
        $party->setPartyDate(new \DateTimeImmutable('+1 day'));

        $invitation = (new Invitation())
            ->setParty($party)
            ->setToken($token)
            ->setRole(PartyMember::ROLE_HOST)
            ->setExpiresAt(new \DateTimeImmutable('+1 day'))
            ->setUses(0)
            ->setMaxUses(1);

        $em->persist($party);
        $em->persist($invitation);
        $em->flush();

        // Zugriff auf Einladung
        $client->request('GET', '/invite/' . $token);

        // Erwarte Redirect zu Login
        $this->assertResponseRedirects('/login');

        // Optional: Session enthält Token
        $session = $client->getRequest()->getSession();
        $this->assertSame(
            $token,
            $session->get(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY)
        );
    }
}
