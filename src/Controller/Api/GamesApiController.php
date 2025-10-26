<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\GameScore;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Repository\GameScoreRepository;
use App\Repository\PartyMemberRepository;
use App\Service\GameFinalizer;
use App\Service\GameRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GamesApiController extends AbstractController
{
    public function __construct(
        private readonly PartyMemberRepository $partyMembers,
        private readonly GameScoreRepository $scores,
        private readonly GameRegistry $registry,
        private readonly EntityManagerInterface $em,
        private readonly GameFinalizer $finalizer,
    ) {
    }

    #[Route('/api/party/{id}/games/{slug}/score', name: 'party_clash_game_submit', methods: ['POST'], requirements: ['id' => '[0-9a-f\-]{36}'])]
    public function submit(Party $party, string $slug, Request $request): Response
    {
        // Authentication and membership
        $user = $this->getUser();
        if (!$user instanceof User)
        {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $pm = $this->partyMembers->findOneByUserAndParty($user, $party);
        if (!$pm instanceof PartyMember)
        {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        if (!$pm->getClashTeam())
        {
            return new JsonResponse(['error' => 'Kein Team gewählt'], Response::HTTP_FORBIDDEN);
        }

        // Game validation
        $game = $this->registry->getGame($slug);
        if (!$game)
        {
            return new JsonResponse(['error' => 'Game not found'], Response::HTTP_NOT_FOUND);
        }
        $now = new \DateTimeImmutable();

        // If closed or ended: finalize and reject further submissions
        $closed = (bool)($game['closed'] ?? false);
        if ($closed || ($game['endAt'] instanceof \DateTimeImmutable && $game['endAt'] < $now))
        {
            $this->finalizer->finalizePartyGame($party, $game['slug'], $game['rankPoints']);
            return new JsonResponse([
                'error' => $closed ? 'Game closed' : 'Game window ended',
                'scoreboardUrl' => $this->generateUrl('party_clash_game_detail', [
                    'id' => (string)$party->getId(),
                    'slug' => $game['slug'],
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ], Response::HTTP_GONE);
        }

        // Not yet started
        if ($game['startAt'] instanceof \DateTimeImmutable && $game['startAt'] > $now)
        {
            return new JsonResponse(['error' => 'Game not active yet'], Response::HTTP_FORBIDDEN);
        }

        // Parse payload
        $data = json_decode($request->getContent() ?: 'null', true);
        if (!is_array($data))
        {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        // Optional: verify playerId and gameId from payload for sanity
        if (!empty($data['playerId']) && (string)$pm->getId() !== (string)$data['playerId'])
        {
            return new JsonResponse(['error' => 'Player mismatch'], Response::HTTP_FORBIDDEN);
        }
        if (!empty($data['gameId']) && $data['gameId'] !== $game['slug'])
        {
            return new JsonResponse(['error' => 'Game mismatch'], Response::HTTP_BAD_REQUEST);
        }

        $score = isset($data['score']) ? (int)$data['score'] : 0;
        $score = max(0, $score);

        // Upsert score (best per member)
        $gs = $this->scores->findOneBy([
            'party' => $party,
            'partyMember' => $pm,
            'gameSlug' => $game['slug'],
        ]);
        $nowDt = new \DateTimeImmutable();
        if (!$gs instanceof GameScore)
        {
            $gs = new GameScore();
            $gs->setParty($party)
                ->setPartyMember($pm)
                ->setGameSlug($game['slug'])
                ->setBestScore($score)
                ->setAttempts(1)
                ->setLastSubmittedAt($nowDt);
        } else
        {
            $gs->incAttempts();
            if ($score > $gs->getBestScore())
            {
                $gs->setBestScore($score);
            }
            $gs->setLastSubmittedAt($nowDt);
        }
        $this->em->persist($gs);
        $this->em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'bestScore' => $gs->getBestScore(),
            'attempts' => $gs->getAttempts(),
            'scoreboardUrl' => $this->generateUrl('party_clash_game_detail', [
                'id' => (string)$party->getId(),
                'slug' => $game['slug'],
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

}
