<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

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
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByUserAndParty(User $user, Party $party): ?PartyMember
    {
        return $this->createQueryBuilder('pm')
            ->where('pm.user = :user')
            ->andWhere('pm.party = :party')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByPartyAndTeam(Party $party, string $team): int
    {
        return (int)$this->createQueryBuilder('pm')
            ->select('COUNT(pm.id)')
            ->where('pm.party = :party')
            ->andWhere('pm.clashTeam = :team')
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->setParameter('team', $team)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTeamCounts(Party $party): array
    {
        $qb = $this->createQueryBuilder('pm')
            ->select('pm.clashTeam AS team, COUNT(pm.id) AS cnt')
            ->where('pm.party = :party')
            ->andWhere('pm.clashTeam IS NOT NULL')
            ->groupBy('pm.clashTeam')
            ->setParameter('party', $party->getId(), UuidType::NAME);
        $rows = $qb->getQuery()->getArrayResult();
        $result = ['A' => 0, 'B' => 0];
        foreach ($rows as $row) {
            $t = $row['team'];
            if (isset($result[$t])) {
                $result[$t] = (int)$row['cnt'];
            }
        }
        return $result;
    }

    public function getTeamPoints(Party $party): array
    {
        $qb = $this->createQueryBuilder('pm')
            ->select('pm.clashTeam AS team, SUM(pm.clashPoints) AS pts')
            ->where('pm.party = :party')
            ->andWhere('pm.clashTeam IS NOT NULL')
            ->groupBy('pm.clashTeam')
            ->setParameter('party', $party->getId(), UuidType::NAME);
        $rows = $qb->getQuery()->getArrayResult();
        $result = ['A' => 0, 'B' => 0];
        foreach ($rows as $row)
        {
            $t = $row['team'];
            if (isset($result[$t]))
            {
                $result[$t] = (int)($row['pts'] ?? 0);
            }
        }
        return $result;
    }

    public function findMembersByPartyAndTeam(Party $party, string $team): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.user', 'u')
            ->addSelect('u')
            ->where('pm.party = :party')
            ->andWhere('pm.clashTeam = :team')
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->setParameter('team', $team)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all party memberships for a user, ordered by party date ascending.
     *
     * @param User $user
     * @return PartyMember[]
     */
    public function findByUserOrderedByPartyDate(User $user): array
    {
        /** @var PartyMember[] $result */
        $result = $this->createQueryBuilder('pm')
            ->leftJoin('pm.party', 'p')
            ->addSelect('p')
            ->where('IDENTITY(pm.user) = :userId')
            ->setParameter('userId', (string)$user->getId(), 'uuid')
            ->orderBy('p.partyDate', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
