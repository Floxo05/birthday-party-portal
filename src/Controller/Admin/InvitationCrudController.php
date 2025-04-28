<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Invitation;
use App\Service\Invitation\InvitationLinkGenerator\InvitationLinkGeneratorInterface;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Override;

class InvitationCrudController extends AbstractHostCrudController
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
}
