<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\GameConfig;
use App\Repository\GameScoreRepository;
use App\Service\GameFinalizer;
use App\Service\GameRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GameConfigCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GameScoreRepository $scores,
        private readonly GameRegistry $registry,
        private readonly GameFinalizer $finalizer,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return GameConfig::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Spiel-Fenster')
            ->setEntityLabelInPlural('Spiel-Fenster')
            ->setDefaultSort(['slug' => 'ASC'])
            ->showEntityActionsInlined();
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('slug', 'Game-Slug')
            ->setHelp('z.B. snake, tetris, flappy');
        yield DateTimeField::new('startAt', 'Startet am')
            ->setHelp('Leer lassen = sofort verfügbar');
        yield DateTimeField::new('endAt', 'Endet am')
            ->setHelp('Leer lassen = kein Ende');
        yield BooleanField::new('closed', 'Abgeschlossen')
            ->setDisabled();
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $closeAction = Action::new('closeWindow', 'Fenster abschließen')
            ->setCssClass('btn btn-danger')
            ->displayIf(static function ($entity)
            {
                return $entity instanceof GameConfig && !$entity->isClosed();
            })
            ->linkToUrl(fn(GameConfig $config) => $this->adminUrlGenerator
                ->setEntityId($config->getId())
                ->setAction('closeWindow')
                ->setController(self::class)
                ->generateUrl());

        return parent::configureActions($actions)
            ->add(Crud::PAGE_DETAIL, $closeAction);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function closeWindow(AdminContext $context): RedirectResponse
    {
//        $entity = $context->getEntity()?->getInstance();

        $entity = $this->em->find(GameConfig::class, $context->getRequest()->query->get('entityId'));

        if (!$entity instanceof GameConfig)
        {
            $this->addFlash('danger', 'Ungültige Entität.');
            return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl());
        }

        if ($entity->isClosed())
        {
            $this->addFlash('info', 'Dieses Spielfenster ist bereits abgeschlossen.');
        } else
        {
            $entity->setClosed(true);
            $this->em->persist($entity);
            $this->em->flush();

            $rankPoints = $this->registry->getRankPoints();
            $finalized = $this->finalizer->finalizeAllPartiesForSlug($entity->getSlug(), $rankPoints);
            $this->addFlash(
                'success',
                sprintf(
                    'Spielfenster "%s" abgeschlossen. Finalisiert für %d Party/Parties.',
                    $entity->getSlug(),
                    $finalized
                )
            );
        }

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entity->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    // Entfernt: Automatische Finalisierung über persist/update Hooks – Abschluss erfolgt nun explizit über die Aktion.
}
