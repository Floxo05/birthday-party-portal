<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted(Role::ADMIN->value)]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW);
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username')
            ->setDisabled();
        yield TextField::new('name')
            ->setDisabled();
        yield ChoiceField::new('roles')
            ->setFormTypeOptions([
                'multiple' => true,
                'choices' => array_combine(
                    array_map(fn(Role $role) => $role->label(), Role::assignable()),
                    array_map(fn(Role $role) => $role->value, Role::assignable()),
                ),
            ])
            ->formatValue(function (array $roles): string
            {
                return implode(', ', array_map(function (string $role)
                {
                    $enum = Role::tryFrom($role);
                    return $enum?->label() ?? $role;
                }, $roles));
            });
    }
}
