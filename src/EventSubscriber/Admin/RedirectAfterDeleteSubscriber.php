<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectAfterDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AfterCrudActionEvent::class => 'redirectToDashboard'
        ];
    }

    public function redirectToDashboard(AfterCrudActionEvent $event): void
    {
        $action = $event->getAdminContext()?->getCrud()?->getCurrentAction();

        if ($action !== Action::DELETE)
        {
            return;
        }

        $url = $this->adminUrlGenerator
            ->setController($event->getAdminContext()?->getCrud()?->getControllerFqcn() ?? '')
            ->setAction(Action::INDEX)
            ->generateUrl();

        $event->setResponse(new RedirectResponse($url));
    }
}