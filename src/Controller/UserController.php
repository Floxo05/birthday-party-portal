<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPackage\ChangePasswordFormType;
use App\Service\User\UserPasswordManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/change-password', name: 'user_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordManager $passwordManager
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordManager->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'Das aktuelle Passwort ist nicht korrekt.');
            } else {
                $passwordManager->changePassword($user, $newPassword);
                $this->addFlash('success', 'Passwort wurde erfolgreich geändert.');
                return $this->redirectToRoute('user_change_password');
            }
        } else if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Beim Speichern ist ein Fehler aufgetreten.');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}