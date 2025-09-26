<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PartyGroupAssignment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PartyGroupAssignmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PartyGroupAssignment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('group', 'Gruppe');
        yield AssociationField::new('partyMember', 'Mitglied');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Gruppenzuweisung')
            ->setEntityLabelInPlural('Gruppenzuweisungen');
    }
}


