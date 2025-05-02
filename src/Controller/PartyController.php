<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Party;
use App\Entity\PartyNews;
use App\Entity\User;
use App\Repository\PartyMemberRepository;
use App\Repository\PartyNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PartyController extends AbstractController
{
    #[Route('/party/{id}', name: 'party_show')]
    public function show(
        Party $party,
        PartyNewsRepository $partyNewsRepository,
        PartyMemberRepository $partyMemberRepository
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

        $news = $partyNewsRepository->findBy(
            ['party' => $party],
            ['createdAt' => 'DESC'],
            3
        );

        return $this->render('party/show.html.twig', [
            'party' => $party,
            'news' => $news,
            'currentUser' => $user,
        ]);
    }

    #[Route('/party/{id}/news', name: 'party_news_list')]
    public function newsList(
        Party $party,
        PartyNewsRepository $partyNewsRepository,
        PartyMemberRepository $partyMemberRepository
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

        $news = $partyNewsRepository->findBy(
            ['party' => $party],
            ['createdAt' => 'DESC']
        );

        return $this->render('party/news_list.html.twig', [
            'party' => $party,
            'news' => $news,
        ]);
    }

    #[Route('/party/news/{id}', name: 'party_news_detail')]
    public function newsDetail(PartyNews $news, PartyMemberRepository $partyMemberRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException('Du musst eingeloggt sein, um diese Nachricht zu sehen.');
        }

        $party = $news->getParty();

        if (!$partyMemberRepository->isUserInParty($user, $party))
        {
            throw $this->createAccessDeniedException('Du hast keinen Zugriff auf diese Nachricht.');
        }

        return $this->render('party/news_detail.html.twig', [
            'news' => $news,
            'party' => $party,
        ]);
    }
}
