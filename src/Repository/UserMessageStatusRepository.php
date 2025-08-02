<?php

namespace App\Repository;

use App\Entity\PartyNews;
use App\Entity\User;
use App\Entity\UserMessageStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @extends ServiceEntityRepository<UserMessageStatus>
 */
#[Autoconfigure(public: true)]
class UserMessageStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMessageStatus::class);
    }

    /**
     * @param User $user
     * @param PartyNews $news
     * @return UserMessageStatus|null
     */
    public function findOneByUserAndPartyNews(User $user, PartyNews $news): object|null
    {
        return $this->findOneBy([
            'user' => $user,
            'partyNews' => $news,
        ]);
    }

    /**
     * @param User $user
     * @param PartyNews[] $news
     * @return UserMessageStatus[]
     */
    public function findAllByUserAndPartyNews(User $user, array $news): array
    {
        $newsIds = array_map(fn(PartyNews $n) => $n->getId()?->toBinary(), $news);

        /** @var UserMessageStatus[]|null $result */
        $result = $this->createQueryBuilder('ums')
            ->where('ums.user = :user')
            ->andWhere('ums.partyNews in (:partyNews)')
            ->setParameter('user', $user->getId(), UuidType::NAME)
            ->setParameter('partyNews', $newsIds, ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();

        if (!is_array($result))
        {
            return [];
        }

        return $result;
    }
}
