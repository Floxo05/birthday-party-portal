<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GameConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameConfig>
 */
class GameConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameConfig::class);
    }

    public function findOneBySlug(string $slug): ?GameConfig
    {
        return $this->createQueryBuilder('gc')
            ->where('gc.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, GameConfig>
     */
    public function mapBySlug(): array
    {
        $all = $this->createQueryBuilder('gc')->getQuery()->getResult();
        $map = [];
        foreach ($all as $gc)
        {
            $map[$gc->getSlug()] = $gc;
        }
        return $map;
    }
}
