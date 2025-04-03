<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Invitation;
use App\Entity\Party;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Override;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    #[Override]
    public function index(): Response
    {
        return $this->render('admin/welcome.html.twig');
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Birthday Party Portal')
            ->setTranslationDomain('admin');
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Party', 'fa fa-music', Party::class);
        yield MenuItem::linkToCrud('Einladung', 'fa fa-envelope-open-text', Invitation::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    #[Override]
    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    #[Override]
    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->showEntityActionsInlined();
    }


}
