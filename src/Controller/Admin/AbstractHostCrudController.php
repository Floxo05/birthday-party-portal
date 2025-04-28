<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Override;
use Symfony\Bridge\Doctrine\Types\UuidType;

abstract class AbstractHostCrudController extends AbstractCrudController
{
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
            throw $this->createAccessDeniedException('Nutzer nicht angemeldet');
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