<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Party;
use App\Entity\User;
use App\Service\Invitation\InvitationTranslator\InvitationToStringTranslatorInterface;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PartyCrudController extends AbstractCrudController
{


    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly InvitationToStringTranslatorInterface $invitationTranslator,
        private readonly PartyMemberRoleTranslatorInterface $partyMemberRoleTranslator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Party::class;
    }


    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $adminUrlGenerator = $this->adminUrlGenerator;

        yield TextField::new('title', 'party.crud.field.title');
        yield DateField::new('partyDate', 'party.crud.field.date');
        yield CollectionField::new('invitations', 'party.crud.field.invitations')
            ->onlyOnDetail()
            ->formatValue(function ($value, Party $entity)
            {
                if ($entity->getInvitations()->isEmpty())
                {
                    return 'Keine Einladungen vorhanden';
                }

                $links = [];
                foreach ($entity->getInvitations() as $invitation)
                {
                    $url = $this->adminUrlGenerator->
                    setController(InvitationCrudController::class)
                        ->setAction(Action::DETAIL)
                        ->setEntityId($invitation->getId())
                        ->generateUrl();

                    $links[] = sprintf(
                        '<a href="%s" target="_self">%s</a>',
                        $url,
                        $this->invitationTranslator->translate($invitation)
                    );
                }

                return implode('<br>', $links);
            });
        yield CollectionField::new('partyMembers', 'Teilnehmer')
            ->hideOnForm()
            ->formatValue(function ($value, Party $entity) use ($pageName)
            {
                if ($pageName === Crud::PAGE_INDEX)
                {
                    return $entity->getPartyMembers()->count();
                }

                if ($entity->getPartyMembers()->isEmpty())
                {
                    return 'Keine Gäste vorhanden';
                }

                $members = $entity->getPartyMembers()->toArray();

                usort($members, function ($a, $b)
                {
                    return strcmp($b->getRole(), $a->getRole()); // sort, that host is first
                });

                $formattedMembers = [];
                foreach ($members as $partyMember)
                {
                    $formattedMembers[] = sprintf(
                        '%s - %s',
                        $partyMember->getUser(),
                        $this->partyMemberRoleTranslator->translate($partyMember->getRole())
                    );
                }

                return implode('<br>', $formattedMembers);
            });
    }

    #[Override]
    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        return $this->redirect(
            $this->adminUrlGenerator->setAction(Action::DETAIL)
                ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                ->generateUrl()
        );
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $createInvitation = Action::new('createInvitation', 'Einladung erstellen')
            ->linkToUrl('#') // Kein direkter Link, da wir das Flyout über JS öffnen
            ->setHtmlAttributes(['id' => 'open-flyout']) // Statischer Button-ID
            ->setCssClass('btn btn-primary');

        return parent::configureActions($actions)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $createInvitation)//            ->update(Crud::PAGE_EDIT,
            ;
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        /** @var ?User $user */
        $user = $this->getUser();

        if ($user === null)
        {
            throw new AccessDeniedException('Nutzer nicht angemeldet');
        }

        $queryBuilder
            ->innerJoin('entity.partyMembers', 'partyMembers')
            ->innerJoin('partyMembers.user', 'user')
            ->andWhere('user = :user')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->andWhere('partyMembers INSTANCE OF App\Entity\Host');

        return $queryBuilder;
    }
}
