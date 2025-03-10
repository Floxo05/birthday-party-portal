<?php
declare(strict_types=1);

namespace App\EventSubscriber\Admin\PartyPackage;

use App\Entity\PartyPackage\Host;
use App\Entity\PartyPackage\Party;
use App\Entity\UserPackage\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddHostToPartySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onAfterEntityPersisted'
        ];
    }

    public function onAfterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $partyEntity = $event->getEntityInstance();

        if (!$partyEntity instanceof Party) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $newHost = new Host();
        $newHost
            ->setParty($partyEntity)
            ->setUser($user);

        $this->em->persist($newHost);
        $this->em->flush();
    }
}