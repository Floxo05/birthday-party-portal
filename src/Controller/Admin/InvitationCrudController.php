<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Invitation;
use App\Entity\User;
use App\Service\Invitation\InvitationLinkGenerator\InvitationLinkGeneratorInterface;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Override;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InvitationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly InvitationLinkGeneratorInterface $linkGenerator,
        private readonly PartyMemberRoleTranslatorInterface $roleTranslator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Invitation::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Einladungen')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Einladungsdetails');
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('party');
        yield TextField::new('role', 'Rolle')
            ->formatValue(fn($value, $entity) => $this->roleTranslator->translate($value));

        yield DateField::new('expiresAt', 'Gültig bis');

        yield IntegerField::new('maxUses', 'Maximale Nutzungen');
        yield IntegerField::new('uses', 'Bereits genutzt');

        if ($pageName === Crud::PAGE_DETAIL)
        {
            yield UrlField::new('token', 'Link')
                ->setValue($this->linkGenerator->generate($this->getContext()?->getEntity()->getInstance()));
        }
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
            ->innerJoin('entity.party', 'party')
            ->innerJoin('party.partyMembers', 'partyMembers')
            ->innerJoin('partyMembers.user', 'user')
            ->andWhere('user = :user')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->andWhere('partyMembers INSTANCE OF App\Entity\Host');

        return $queryBuilder;
    }


}
