<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Role;
use App\Service\User\UserPasswordManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted(Role::ADMIN->value)]
class UserCrudController extends AbstractCrudController
{


    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly UserPasswordManager $userPasswordManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions)
            ->disable(Action::NEW);

        $resetPasswordAction = Action::new('resetPassword', 'Passwort zurücksetzen')
            ->linkToCrudAction('resetPassword')
            ->addCssClass('btn btn-danger');

        $actions->add(Crud::PAGE_DETAIL, $resetPasswordAction);

        return $actions;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username')
            ->setDisabled();
        yield TextField::new('name')
            ->setDisabled();
        yield TextField::new('phoneNumber')
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

    /**
     * @throws RandomException
     */
    public function resetPassword(): RedirectResponse
    {
        $id = $this->getContext()?->getRequest()->query->get('entityId');

        if (!$id)
        {
            $this->addFlash('danger', 'Keine Benutzer-ID angegeben.');
            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(UserCrudController::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        $redirectUrl = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($id)
            ->generateUrl();

        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user)
        {
            $this->addFlash('danger', 'Benutzer nicht gefunden.');
            return $this->redirect($redirectUrl);
        }

        $newPassword = $this->userPasswordManager->resetPassword($user);

        $this->addFlash('success', sprintf('Das neue Passwort lautet: <code>%s</code>', $newPassword));

        return $this->redirect($redirectUrl);
    }
}
