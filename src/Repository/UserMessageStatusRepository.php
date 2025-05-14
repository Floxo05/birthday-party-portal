<?php

namespace App\Repository;

use App\Entity\PartyNews;
use App\Entity\User;
use App\Entity\UserMessageStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
}
