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
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;
use App\Enum\ResponseStatus;
use Symfony\Component\Form\ClickableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Form\Party\PartyResponseFormModel;
use App\Form\Party\PartyResponseFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;
use App\Attribute\RequiresPartyAccess;

final class PartyController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/party/{id}', name: 'party_show')]
    #[RequiresPartyAccess]
    public function show(
        Party $party,
        PartyNewsRepository $partyNewsRepository,
        UserMessageStatusRepository $messageStatusRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
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
        try {
            $popupNews = array_filter(
                $news,
                fn(PartyNews $news) => $news->getAsPopup()  // marked as popup
                    && !($statusMap[$news->getId()?->toRfc4122()]->isRead()) // news not read
            );
        } catch (\Exception $exception) {
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
    #[RequiresPartyAccess(redirectIfForeshadowing: false)]
    public function foreshadowing(
        Party $party,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
        }
        if (!$party->isForeshadowing()) {
            return $this->redirectToRoute('party_show', ['id' => $party->getId()]);
        }

        return $this->render('party/foreshadowing.html.twig', [
            'party' => $party,
            'currentUser' => $user,
            'imagePath' => '486b7063-47a6-4b32-a234-0396406db70a.png'
        ]);
    }

    #[Route('/party/{id}/news', name: 'party_news_list')]
    #[RequiresPartyAccess]
    public function newsList(
        Party $party,
        PartyNewsRepository $partyNewsRepository,
        UserMessageStatusRepository $messageStatusRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
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
    #[RequiresPartyAccess]
    public function newsDetail(
        PartyNews $news,
        UserMessageManager $userMessageManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Nachricht zu sehen.');
        }

        $userMessageManager->markAsRead($user, $news);

        return $this->render('party/news_detail.html.twig', [
            'news' => $news,
            'party' => $news->getParty(),
        ]);
    }

    #[Route('/party/{id}/action/response', name: 'party_action_response')]
    #[RequiresPartyAccess]
    public function manageResponse(
        Party $party,
        Request $request,
        PartyMembershipManagerInterface $partyMembershipManager,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Party zu sehen.');
        }

        $now = new \DateTimeImmutable();
        $deadline = $party->getRsvpDeadline();
        $isRsvpOpen = $deadline === null || $deadline >= $now;

        $form = null;
        if ($isRsvpOpen) {
            $formModel = new PartyResponseFormModel();
            $form = $this->createForm(PartyResponseFormType::class, $formModel);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $accept = $form->get('accept');
                $acceptWithGuests = $form->get('accept_with_guests');
                $decline = $form->get('decline');

                if ($accept instanceof ClickableInterface && $accept->isClicked()) {
                    $formModel->responseStatus = ResponseStatus::ACCEPTED;
                    $formModel->plusGuests = null;
                } elseif ($acceptWithGuests instanceof ClickableInterface && $acceptWithGuests->isClicked()) {
                    $formModel->responseStatus = ResponseStatus::ACCEPTED;
                    $formModel->plusGuests = 1;
                } elseif ($decline instanceof ClickableInterface && $decline->isClicked()) {
                    $formModel->responseStatus = ResponseStatus::DECLINED;
                    $formModel->plusGuests = null;
                } else {
                    return $this->redirectToRoute('party_action_response', ['id' => $party->getId()]);
                }

                $partyMembershipManager->setResponseForUser($user, $party, $formModel);

                if ($formModel->responseStatus === ResponseStatus::ACCEPTED) {
                    $this->addFlash('success', $formModel->plusGuests ? sprintf('Deine Zusage mit %d Begleitperson(en) wurde erfasst.', $formModel->plusGuests) : 'Deine Zusage wurde erfasst.');
                } else {
                    $this->addFlash('info', 'Deine Absage wurde erfasst.');
                }

                return $this->redirectToRoute('party_action_response', ['id' => $party->getId()]);
            }
        }

        $currentDecision = null;

        // Resolve current decision for display
        $membership = null;
        try {
            $membership = $partyMembershipManager->getMembershipForUser($user, $party);
        } catch (\Throwable) {
            $membership = null;
        }

        if ($membership !== null) {
            $status = $membership->getResponseStatus();
            $extras = $membership->getExtraGuests();
            if ($status !== null) {
                $label = $status->getLabel();
                $emoji = $status === ResponseStatus::ACCEPTED ? ' 🙂' : ' 🙁';
                if ($status === ResponseStatus::ACCEPTED && $extras) {
                    $currentDecision = sprintf('%s + %d%s', $label, $extras, $emoji);
                } else {
                    $currentDecision = $label . $emoji;
                }
            }
        }


        return $this->render('party/action_response.html.twig', [
            'party' => $party,
            'currentUser' => $user,
            'isRsvpOpen' => $isRsvpOpen,
            'form' => $form?->createView(),
            'currentDecision' => $currentDecision,
        ]);
    }

    /**
     * @param UserMessageStatus[] $userMessageStatus
     * @return array<string,UserMessageStatus> mapping partyNewsId to UserMessageStatus
     */
    private function getUserMessageStatusMap(array $userMessageStatus): array
    {
        $statusMap = [];
        foreach ($userMessageStatus as $status) {
            $partyNewsId = $status->getPartyNews()?->getId();

            if ($partyNewsId === null) {
                throw new \UnexpectedValueException('id must be set.');
            }

            $statusMap[$partyNewsId->toRfc4122()] = $status;
        }

        return $statusMap;
    }
}
