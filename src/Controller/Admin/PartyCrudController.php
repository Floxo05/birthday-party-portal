<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PartyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Party::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'admin.party.crud.field.title');
        yield DateField::new('partyDate', 'admin.party.crud.field.date');
    }


}
