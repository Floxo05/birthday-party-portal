<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Party;
use App\Entity\PartyNews;
use App\Entity\User;
use App\Entity\UserMessageStatus;
use App\Event\BeforeLoadDataForPartyEvent;
use App\Repository\PartyMemberRepository;
use App\Repository\PartyNewsRepository;
use App\Repository\UserMessageStatusRepository;
use App\Service\UserMessage\UserMessageManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

final class PartyController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    )
    {
    }

   #[Route('/party/{id}', name: 'party_show')]
   public function show(
       Party $party,
       PartyNewsRepository $partyNewsRepository,
       PartyMemberRepository $partyMemberRepository,
       UserMessageStatusRepository $messageStatusRepository,
   ): Response {
       $user = $this->getUser();

       if (!$user instanceof User)
       {
           throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
       }

       if (!$partyMemberRepository->isUserInParty($user, $party))
       {
           throw $this->createAccessDeniedException('Du hast keinen Zugriff auf diese Party.');
       }

       if ($party->isForeshadowing())
       {
           return $this->redirectToRoute('party_foreshadowing', ['id' => $party->getId()]);
       }

       $this->eventDispatcher->dispatch(new BeforeLoadDataForPartyEvent($user, $party));

       /** @var PartyNews[] $news */
       $news = $partyNewsRepository->findBy(
           ['party' => $party],
           ['createdAt' => 'DESC'],
           3
       );

       $userMessageStatus = $messageStatusRepository->findAllByUserAndPartyNews($user, $news);
       $statusMap = $this->getUserMessageStatusMap($userMessageStatus);

       $popupNews = [];
       try
       {
           $popupNews = array_filter(
               $news,
               fn(PartyNews $news) => $news->getAsPopup()  // marked as popup
                   && !($statusMap[$news->getId()?->toRfc4122()]->isRead()) // news not read
           );
       } catch (\Exception $exception)
       {
           $this->logger->error(
               $exception->getMessage(),
               ['id' => $user->getId(), 'party' => $party->getId(), 'exception' => $exception]
           );
       }


       return $this->render('party/show.html.twig', [
           'party' => $party,
           'news' => $news,
           'currentUser' => $user,
           'statusMap' => $statusMap,
           'popupNews' => $popupNews,
       ]);
   }


    #[Route('/party/{id}/foreshadowing', name: 'party_foreshadowing')]
    public function foreshadowing(
        Party $party,
        PartyMemberRepository $partyMemberRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
        }

        if (!$partyMemberRepository->isUserInParty($user, $party))
        {
            throw $this->createAccessDeniedException('Du hast keinen Zugriff auf diese Party.');
        }

        if (!$party->isForeshadowing())
        {
            return $this->redirectToRoute('party_show', ['id' => $party->getId()]);
        }

        return $this->render('party/foreshadowing.html.twig', [
            'party' => $party,
            'currentUser' => $user,
            'imagePath' => '486b7063-47a6-4b32-a234-0396406db70a.png'
        ]);
    }

    #[Route('/party/{id}/news', name: 'party_news_list')]
    public function newsList(
        Party $party,
        PartyNewsRepository $partyNewsRepository,
        PartyMemberRepository $partyMemberRepository,
        UserMessageStatusRepository $messageStatusRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
        }

        if (!$partyMemberRepository->isUserInParty($user, $party))
        {
            throw $this->createAccessDeniedException('Du hast keinen Zugriff auf diese Party.');
        }

        if ($party->isForeshadowing())
        {
            return $this->redirectToRoute('party_foreshadowing', ['id' => $party->getId()]);
        }

        $this->eventDispatcher->dispatch(new BeforeLoadDataForPartyEvent($user, $party));

        $news = $partyNewsRepository->findBy(
            ['party' => $party],
            ['createdAt' => 'DESC']
        );

        $userMessageStatus = $messageStatusRepository->findAllByUserAndPartyNews($user, $news);
        $statusMap = $this->getUserMessageStatusMap($userMessageStatus);

        return $this->render('party/news_list.html.twig', [
            'party' => $party,
            'news' => $news,
            'statusMap' => $statusMap,
        ]);
    }

    #[Route('/party/news/{id}', name: 'party_news_detail')]
    public function newsDetail(
        PartyNews $news,
        PartyMemberRepository $partyMemberRepository,
        UserMessageManager $userMessageManager
    ): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Nachricht zu sehen.');
        }

        $party = $news->getParty();
        if ($party === null)
        {
            throw new UnexpectedValueException('Die Nachricht ist zu keiner Party zugeordnet.');
        }

        if (!$partyMemberRepository->isUserInParty($user, $party))
        {
            throw $this->createAccessDeniedException('Du hast keinen Zugriff auf diese Nachricht.');
        }

        $userMessageManager->markAsRead($user, $news);

        return $this->render('party/news_detail.html.twig', [
            'news' => $news,
            'party' => $party,
        ]);
    }

    /**
     * @param UserMessageStatus[] $userMessageStatus
     * @return array<string,UserMessageStatus> mapping partyNewsId to UserMessageStatus
     */
    private function getUserMessageStatusMap(array $userMessageStatus): array
    {
        $statusMap = [];
        foreach ($userMessageStatus as $status)
        {
            $partyNewsId = $status->getPartyNews()?->getId();

            if ($partyNewsId === null)
            {
                throw new \UnexpectedValueException('id must be set.');
            }

            $statusMap[$partyNewsId->toRfc4122()] = $status;
        }

        return $statusMap;
    }
}
