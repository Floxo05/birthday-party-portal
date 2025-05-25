<?php
declare(strict_types=1);

namespace App\EventSubscriber\Admin\PartyPackage;

use App\Entity\Host;
use App\Entity\Party;
use App\Entity\User;
use App\Enum\ResponseStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class AddHostToPartySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security               $security,
        private EntityManagerInterface $em,
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
            ->setUser($user)
            ->setResponseStatus(ResponseStatus::ACCEPTED);

        $this->em->persist($newHost);
        $this->em->flush();
    }
}