<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\Invitation\InvitationHandler\InvitationHandlerInterface;
use App\Service\Invitation\InvitationSessionManager\InvitationSessionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AddUserToPartySubscriber implements EventSubscriberInterface
{


    public function __construct(
        private readonly InvitationSessionManagerInterface $sessionManager,
        private readonly InvitationHandlerInterface $invitationHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User)
        {
            return;
        }

        if (!$invitationToken = $this->sessionManager->getInvitationToken())
        {
            return;
        }

        // Einladung verarbeiten und User der Party hinzufügen
        try
        {
            $invitationResult = $this->invitationHandler->handleInvitation($invitationToken, $user);

            if ($invitationResult->userJoinedSuccessfully())
            {
                /** @var FlashBagAwareSessionInterface $session */
                $session = $this->requestStack->getSession();
                $session->getFlashBag()->add(
                    'success',
                    sprintf('Du bist der Party "%s" beigetreten!', $invitationResult->getInvitation()?->getParty())
                );

                $response = new RedirectResponse($this->urlGenerator->generate('app_home'));
                $event->setResponse($response);
            }
        } catch (\Exception)
        {
        } finally
        {
            $this->sessionManager->clearInvitationToken();
        }
    }
}