<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Exception\Invitation\InvitationExpiredException;
use App\Exception\Invitation\InvitationLimitReachedException;
use App\Exception\Invitation\InvitationNotFoundException;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Service\Invitation\InvitationHandler\InvitationHandlerInterface;
use App\Service\Invitation\InvitationSessionManager\InvitationSessionManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    #[Route('/invite/{token}', name: 'app_invite')]
    public function invite(
        string $token,
        Security $security,
        InvitationHandlerInterface $invitationHandler,
        InvitationSessionManagerInterface $sessionManager
    ): Response {
        try
        {
            /** @var User|null $user */
            $user = $security->getUser();
            $result = $invitationHandler->handleInvitation($token, $user);

            if ($result->userJoinedSuccessfully())
            {
                $this->addFlash('success', 'Du bist der Party beigetreten!');
                return $this->redirectToRoute('app_home'); // TODO: Zielseite anpassen
            }

            if ($result->needsRegistration())
            {
                $sessionManager->storeInvitationToken($token);
                return $this->redirectToRoute('app_login');
            }
        } catch (InvitationNotFoundException|InvitationExpiredException|InvitationLimitReachedException)
        {
            $this->addFlash('danger', 'Diese Einladung ist ungültig.');
        } catch (UserAlreadyInPartyException)
        {
            $this->addFlash('info', 'Du bist bereits Mitglied dieser Party.');
        }

        return $this->redirectToRoute('app_home');
    }
}