<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\BeforeLoadDataForPartyEvent;
use App\Service\UserMessage\UserMessageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserMessageStatusSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private UserMessageManager $manager
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLoadDataForPartyEvent::class => 'onBeforeLoadDataForParty',
        ];
    }

    public function onBeforeLoadDataForParty(BeforeLoadDataForPartyEvent $event): void
    {
        $this->manager->ensureAllMessagesHaveStatus($event->user, $event->party);
    }
}