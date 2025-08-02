<?php
declare(strict_types=1);

namespace App\Service\UserMessage;

use App\Entity\Party;
use App\Entity\PartyNews;
use App\Entity\User;
use App\Entity\UserMessageStatus;
use App\Repository\PartyNewsRepository;
use App\Repository\UserMessageStatusRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserMessageManager
{
    public function __construct(
        private PartyNewsRepository $partyNewsRepository,
        private UserMessageStatusRepository $messageStatusRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function markAsRead(User $user, PartyNews $news): void
    {
        /** @var UserMessageStatus|null $userMessageStatus */
        $userMessageStatus = $this->messageStatusRepository->findOneByUserAndPartyNews($user, $news);
        if (null === $userMessageStatus)
        {
            throw new \LogicException('user message should already exist at this point');
        }

        $userMessageStatus->setReadAt(new \DateTimeImmutable('now'));
        $this->entityManager->flush();
    }

    public function ensureAllMessagesHaveStatus(User $user, Party $party): void
    {
        $newsList = $this->partyNewsRepository->findBy(['party' => $party]);

        foreach ($newsList as $news)
        {
            /** @var UserMessageStatus|null $status */
            $status = $this->messageStatusRepository->findOneByUserAndPartyNews($user, $news);

            if ($status === null)
            {
                $status = new UserMessageStatus();
                $status->setUser($user);
                $status->setPartyNews($news);
                $this->entityManager->persist($status);
            }
        }

        $this->entityManager->flush();
    }
}