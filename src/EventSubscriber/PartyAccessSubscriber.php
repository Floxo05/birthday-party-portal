<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\RequiresPartyAccess;
use App\Entity\Party;
use App\Entity\PartyNews;
use App\Security\Voter\PartyVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PartyAccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments',
        ];
    }

    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = $reflection->getAttributes(RequiresPartyAccess::class);
        if ($attributes === []) {
            return;
        }

        $config = $attributes[0]->newInstance(); /** @var RequiresPartyAccess $config */

        $request = $event->getRequest();

        // Prefer resolved controller arguments (more reliable than attributes)
        $party = null;
        foreach ($event->getArguments() as $arg) {
            if ($arg instanceof Party) {
                $party = $arg;
                break;
            }
        }

        // Fallback to request attributes if not in arguments
        if (!$party instanceof Party) {
            $party = $request->attributes->get('party');
        }

        // Derive from PartyNews argument if still missing
        if (!$party instanceof Party) {
            foreach ($event->getArguments() as $arg) {
                if ($arg instanceof PartyNews) {
                    $partyCandidate = $arg->getParty();
                    if ($partyCandidate instanceof Party) {
                        $party = $partyCandidate;
                        // store for downstream consumers
                        $request->attributes->set('party', $party);
                    }
                    break;
                }
            }
        }
        if (!$party instanceof Party) {
            return; // Party muss per MapEntity/Resolver bereitgestellt sein
        }

        if (!$this->authorizationChecker->isGranted(PartyVoter::ACCESS, $party)) {
            throw new AccessDeniedHttpException('Du hast keinen Zugriff auf diese Party.');
        }

        if ($config->redirectIfForeshadowing && $party->isForeshadowing()) {
            $response = new RedirectResponse(
                $this->urlGenerator->generate('party_foreshadowing', ['id' => $party->getId()])
            );
            $event->setController(static fn () => $response);
        }
    }
}


