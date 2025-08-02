<?php

declare(strict_types=1);

namespace App\Controller\Admin\Custom;

use App\Controller\Admin\InvitationCrudController;
use App\Entity\Party;
use App\Form\Admin\InvitationFormType;
use App\Form\Model\InvitationFormModel;
use App\Service\Invitation\InvitationHandler\InvitationHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use UnexpectedValueException;

class InvitationController extends AbstractController
{

    public function __construct(
        private readonly InvitationHandlerInterface $invitationHandler,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/invitation/flyout/{partyId}', name: 'admin_invitation_flyout')]
    public function flyout(string $partyId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $party = $entityManager->getRepository(Party::class)->find($partyId);
        if (!$party)
        {
            return new JsonResponse(['error' => 'Party nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(InvitationFormType::class, null, ['party' => $party]);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if (!$form->isValid())
            {
                $errors = [];
                foreach ($form->getErrors(true) as /** @var FormErrorIterator $error */ $errorIterator)
                {
                    /** @var FormError $error */
                    $error = $errorIterator;
                    $formField = $error->getOrigin(); // Das Feld, an dem der Fehler auftrat
                    $fieldName = $formField?->getPropertyPath(); // Pfadname wie "product[price]"
                    $message = $error->getMessage();
                    $errors[] = sprintf('%s: %s', $fieldName, $message);
                }

                $errorAsString = implode("\n", $errors);
                return $this->json(
                    [
                        'success' => false,
                        'error' => $errorAsString
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            /** @var InvitationFormModel $data */
            $data = $form->getData();

            if ($data->role === null)
            {
                throw new UnexpectedValueException('Role must not be null.');
            }

            if ($data->expiresAt === null)
            {
                throw new UnexpectedValueException('ExpiresAt must not be null.');
            }

            if ($data->maxUses === null)
            {
                throw new UnexpectedValueException('MaxUses must not be null.');
            }

            $invitation = $this->invitationHandler->createInvitation(
                $party,
                $data->role,
                \DateTimeImmutable::createFromInterface($data->expiresAt),
                $data->maxUses
            );

            $redirectUrl = $this->adminUrlGenerator->setController(InvitationCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($invitation->getId())
                ->generateUrl();

            return $this->json(
                [
                    'success' => true,
                    'inviteUrl' => $this->invitationHandler->getInvitationLink($invitation),
                    'redirectUrl' => $redirectUrl
                ]
            );
        }

        return $this->render('admin/invitation/flyout.html.twig', [
            'form' => $form->createView(),
            'party' => $party
        ]);
    }
}