<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use App\Entity\PartyNews;
use App\Form\FormHandling\NestedInputBag\NestedInputBagFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Uid\Uuid;

class PartyNewsCrudController extends AbstractHostCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly NestedInputBagFactory $nestedInputBagFactory
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return PartyNews::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }


    public function createEntity(string $entityFqcn): PartyNews
    {
        /** @var string|null $partyId */
        $partyId = $this->getContext()?->getRequest()->query->get('party_id');

        if ($partyId === null)
        {
            throw new \RuntimeException('Keine Party-ID gesetzt.');
        }

        if (!Uuid::isValid($partyId))
        {
            throw new \RuntimeException('Ungültige party_id.');
        }

        /** @var Party|null $party */
        $party = $this->entityManager
            ->getRepository(Party::class)
            ->find($partyId);

        if ($party === null)
        {
            throw $this->createNotFoundException('Party nicht gefunden.');
        }

        $news = new PartyNews();
        $news->setParty($party);
        $news->setCreatedAt(new \DateTimeImmutable());

        return $news;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var PartyNews|null $partyNewsInstance */
        $partyNewsInstance = $this->getContext()?->getEntity()->getInstance();
        $partyId = $partyNewsInstance?->getParty()?->getId();

        if (!$partyId && $pageName === Crud::PAGE_NEW)
        {
            throw new \RuntimeException('Fehlende party_id in der URL.');
        }

        yield AssociationField::new('party')
            ->setDisabled(true);

        yield TextareaField::new('text')
            ->setRequired(true);


        yield AssociationField::new('media')
            ->setRequired(false)
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($partyId)
            {
                return $queryBuilder
                    ->where('entity.party = :party')
                    ->andWhere('entity.mimeType in (:mimeType)')
                    ->setParameter('party', $partyId, 'uuid')
                    ->setParameter('mimeType', ['image/jpeg', 'image/png'])
                    ->orderBy('entity.id', 'DESC');
            });

        yield DateTimeField::new('createdAt')
            ->hideWhenCreating()
            ->setFormat('dd.MM.yyyy HH:mm');
    }

    #[Override]
    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $request = $this->nestedInputBagFactory->create($context->getRequest()->getPayload());
        $btnAction = $request->get('ea.newForm.btn', Action::SAVE_AND_RETURN);

        if ($btnAction === Action::SAVE_AND_RETURN)
        {
            return $this->redirect(
                $this->adminUrlGenerator->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }
}
