<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PartyGroup;
use App\Entity\PartyGroupAssignment;
use App\Entity\PartyMember;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PartyGroupCrudController extends AbstractHostCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartyGroup::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $batchAssignAction = Action::new('batchAssign', 'Mitglieder zuweisen')
            ->linkToCrudAction('batchAssign')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-plus')
            ->displayIf(function ($entity) {
                return $entity !== null;
            });

        $actions->add(Crud::PAGE_DETAIL, $batchAssignAction);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield AssociationField::new('party', 'Party');
        yield AssociationField::new('assignments', 'Zuweisungen')
            ->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Gruppe')
            ->setEntityLabelInPlural('Gruppen');
    }

    public function batchAssign(AdminContext $context): Response
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        // Get entity ID from request
        $entityId = $context->getRequest()->query->get('entityId');
        if (!$entityId) {
            $this->addFlash('error', 'Keine Gruppe ausgewählt.');
            return $this->redirectToIndex($adminUrlGenerator);
        }
        
        $group = $entityManager->find(PartyGroup::class, $entityId);
        if (!$group) {
            $this->addFlash('error', 'Gruppe nicht gefunden.');
            return $this->redirectToIndex($adminUrlGenerator);
        }
        
        $party = $group->getParty();
        
        if (!$party) {
            $this->addFlash('error', 'Gruppe hat keine zugehörige Party.');
            return $this->redirectToIndex($adminUrlGenerator);
        }

        // Get all party members for this party
        $partyMembers = $party->getPartyMembers()->toArray();
        
        // Get already assigned members
        $assignedMemberIds = [];
        foreach ($group->getAssignments() as $assignment) {
            if ($assignment->getPartyMember()) {
                $assignedMemberIds[] = $assignment->getPartyMember()->getId()->toString();
            }
        }

        if ($context->getRequest()->isMethod('POST')) {
            $selectedMemberIds = $context->getRequest()->request->all('selected_members') ?? [];
            
            // Remove existing assignments for this group
            foreach ($group->getAssignments() as $assignment) {
                if (in_array($assignment->getPartyMember()->getId()->toString(), $assignedMemberIds)) {
                    continue;
                }
                $entityManager->remove($assignment);
            }
            
            // Add new assignments
            foreach ($selectedMemberIds as $memberId) {
                if (in_array($memberId, $assignedMemberIds))
                {
                    continue;
                }
                $member = $entityManager->find(PartyMember::class, $memberId);
                if ($member && $member->getParty() === $party) {
                    $assignment = new PartyGroupAssignment();
                    $assignment->setGroup($group);
                    $assignment->setPartyMember($member);
                    $entityManager->persist($assignment);
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Mitglieder erfolgreich zugewiesen.');
            
            return $this->redirectToDetail($group, $adminUrlGenerator);
        }

        return $this->render('admin/party_group/batch_assign.html.twig', [
            'group' => $group,
            'party' => $party,
            'partyMembers' => $partyMembers,
            'assignedMemberIds' => $assignedMemberIds,
        ]);
    }

    private function redirectToIndex(AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        return $this->redirect($adminUrlGenerator->setController(static::class)->setAction(Action::INDEX)->generateUrl());
    }

    private function redirectToDetail(PartyGroup $group, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        return $this->redirect($adminUrlGenerator->setController(static::class)->setAction(Action::DETAIL)->setEntityId($group->getId())->generateUrl());
    }
}


