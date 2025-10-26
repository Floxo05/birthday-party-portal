<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameScore;
use App\Entity\Party;
use App\Entity\PartyMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<GameScore>
 */
class GameScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameScore::class);
    }

    public function findOneByPartyMemberAndGame(PartyMember $pm, string $gameSlug): ?GameScore
    {
        return $this->createQueryBuilder('gs')
            ->where('gs.partyMember = :pm')
            ->andWhere('gs.gameSlug = :slug')
            ->setParameter('pm', $pm->getId(), UuidType::NAME)
            ->setParameter('slug', $gameSlug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, array{member: PartyMember, score: int}>
     */
    public function getLeaderboard(Party $party, string $gameSlug, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('gs')
            ->leftJoin('gs.partyMember', 'pm')
            ->addSelect('pm')
            ->leftJoin('pm.user', 'u')
            ->addSelect('u')
            ->where('gs.party = :party')
            ->andWhere('gs.gameSlug = :slug')
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->setParameter('slug', $gameSlug)
            ->orderBy('gs.bestScore', 'DESC')
            ->addOrderBy('gs.lastSubmittedAt', 'ASC')
            ->setMaxResults($limit);
        $rows = $qb->getQuery()->getResult();
        $result = [];
        foreach ($rows as $gs)
        {
            $result[] = [
                'member' => $gs->getPartyMember(),
                'score' => $gs->getBestScore(),
            ];
        }
        return $result;
    }
}
