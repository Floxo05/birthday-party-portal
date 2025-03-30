<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartyMember>
 */
class PartyMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartyMember::class);
    }

    public function isUserInParty(User $user, Party $party): bool
    {
        return (bool)$this->createQueryBuilder('pm')
            ->select('COUNT(pm.id)')
            ->where('pm.user = :user')
            ->andWhere('pm.party = :party')
            ->setParameter('user', $user)
            ->setParameter('party', $party)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
