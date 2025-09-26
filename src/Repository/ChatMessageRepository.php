<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\Party;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<ChatMessage>
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    /**
     * @return ChatMessage[]
     */
    public function findLatestForRoom(Party $party, User $roomOwner, ?string $sinceId = null, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.party = :party')
            ->andWhere('m.roomOwner = :owner')
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->setParameter('owner', $roomOwner->getId(), UuidType::NAME)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($sinceId !== null) {
            $qb->andWhere('m.id > :sinceId')
               ->setParameter('sinceId', $sinceId, UuidType::NAME);
        }

        /** @var ChatMessage[] $result */
        $result = $qb->getQuery()->getResult();

        return array_reverse($result);
    }

    /**
     * Returns PHP-aggregated rooms to avoid DB UUID/binary pitfalls.
     *
     * @return array<int, array{ownerId: string, lastAt: \DateTimeImmutable, messageCount: int}>
     */
    public function findRoomsWithLastActivity(Party $party): array
    {
        /** @var ChatMessage[] $messages */
        $messages = $this->createQueryBuilder('m')
            ->where('m.party = :party')
            ->setParameter('party', $party->getId(), UuidType::NAME)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $rooms = [];
        foreach ($messages as $m) {
            $owner = $m->getRoomOwner();
            if ($owner === null) {
                continue;
            }
            $ownerId = (string)$owner->getId();
            if (!isset($rooms[$ownerId])) {
                $rooms[$ownerId] = [
                    'ownerId' => $ownerId,
                    'lastAt' => $m->getCreatedAt() ?? new \DateTimeImmutable(),
                    'messageCount' => 0,
                ];
            }
            $rooms[$ownerId]['messageCount']++;
        }

        // Sort by lastAt DESC
        usort($rooms, static function (array $a, array $b) {
            return ($b['lastAt'] <=> $a['lastAt']);
        });

        return array_values($rooms);
    }
}


