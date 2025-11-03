<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use App\Entity\ShopItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Uid\Uuid;

class ShopItemCrudController extends AbstractHostCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return ShopItem::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function createEntity(string $entityFqcn): ShopItem
    {
        /** @var string|null $partyId */
        $partyId = $this->getContext()?->getRequest()->query->get('party_id');

        if ($partyId === null) {
            throw new \RuntimeException('Keine Party-ID gesetzt.');
        }
        if (!Uuid::isValid($partyId)) {
            throw new \RuntimeException('Ungültige party_id.');
        }

        /** @var Party|null $party */
        $party = $this->entityManager->getRepository(Party::class)->find($partyId);
        if ($party === null) {
            throw $this->createNotFoundException('Party nicht gefunden.');
        }

        $item = new ShopItem();
        $item->setParty($party);
        return $item;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var ShopItem|null $instance */
        $instance = $this->getContext()?->getEntity()->getInstance();
        $partyId = $instance?->getParty()?->getId();

        if (!$partyId && $pageName === Crud::PAGE_NEW) {
            throw new \RuntimeException('Fehlende party_id in der URL.');
        }

        yield AssociationField::new('party')->setDisabled(true);
        yield TextField::new('name', 'Name');
        yield TextareaField::new('description', 'Beschreibung')->onlyOnForms();
        yield IntegerField::new('pricePoints', 'Preis (Punkte)');
        yield IntegerField::new('quantity', 'Anzahl');

        yield AssociationField::new('media', 'Bild')
            ->setRequired(false)
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($partyId) {
                return $queryBuilder
                    ->where('entity.party = :party')
                    ->andWhere('entity.mimeType in (:mimeType)')
                    ->setParameter('party', $partyId, 'uuid')
                    ->setParameter('mimeType', ['image/jpeg', 'image/png'])
                    ->orderBy('entity.id', 'DESC');
            });
    }
}
