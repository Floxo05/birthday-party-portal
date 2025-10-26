<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Repository\GameScoreRepository;
use App\Repository\PartyMemberRepository;
use App\Service\GameFinalizer;
use App\Service\GameRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/party/{id}/clash/games', requirements: ['id' => '[0-9a-f\-]{36}'])]
class GamesController extends AbstractController
{
    public function __construct(
        private readonly PartyMemberRepository $partyMembers,
        private readonly GameScoreRepository $scores,
        private readonly GameRegistry $registry,
        private readonly EntityManagerInterface $em,
        private readonly GameFinalizer $finalizer,
    ) {
    }

    private function requireMembership(Party $party): PartyMember
    {
        $user = $this->getUser();
        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException();
        }
        $pm = $this->partyMembers->findOneByUserAndParty($user, $party);
        if (!$pm instanceof PartyMember)
        {
            throw $this->createAccessDeniedException();
        }
        if (!$pm->getClashTeam())
        {
            // must pick team first
            throw $this->createAccessDeniedException('Du musst zuerst ein Team wählen.');
        }
        return $pm;
    }

    #[Route('', name: 'party_clash_games', methods: ['GET'])]
    public function list(Party $party): Response
    {
        $pm = $this->requireMembership($party);
        $now = new \DateTimeImmutable();
        $games = array_map(function (array $g) use ($now)
        {
            $closed = (bool)($g['closed'] ?? false);
            $active = !$closed
                && ($g['startAt'] instanceof \DateTimeImmutable)
                && $g['startAt'] <= $now
                && (!($g['endAt'] instanceof \DateTimeImmutable) || $g['endAt'] >= $now);
            $status = $active ? 'active' : (($closed || ($g['endAt'] instanceof \DateTimeImmutable && $g['endAt'] < $now)) ? 'ended' : 'upcoming');
            $g['status'] = $status;
            return $g;
        }, $this->registry->listGames());

        // Show only active games (require started, not ended/closed)
        $games = array_values(array_filter($games, fn($g) => ($g['status'] ?? 'upcoming') === 'active'));
        usort($games, fn($a, $b) => strcmp($a['title'] ?? '', $b['title'] ?? ''));

        // Build cross-game scoreboard (sum of rank points per member across started games)
        $rankPoints = $this->registry->getRankPoints();
        $now = new \DateTimeImmutable();
        $allGames = $this->registry->listGames();
        // consider games that have started
        $startedGames = array_values(array_filter($allGames, function (array $g) use ($now)
        {
            return ($g['startAt'] instanceof \DateTimeImmutable) && $g['startAt'] <= $now;
        }));
        $aggregate = []; // [string memberId => ['member'=>PartyMember,'points'=>int]]
        foreach ($startedGames as $g)
        {
            $lb = $this->scores->getLeaderboard($party, $g['slug'], 1000);
            if (!$lb)
            {
                continue;
            }
            // assign ranks with ties based on score desc; getLeaderboard already sorted by bestScore desc then lastSubmittedAt asc
            $rank = 0;
            $prevScore = null;
            $actual = 0;
            foreach ($lb as $row)
            {
                $actual++;
                $score = $row['score'] ?? 0;
                if ($prevScore === null || $score !== $prevScore)
                {
                    $rank = $actual;
                    $prevScore = $score;
                }
                $pts = $rankPoints[$rank] ?? 0;
                if ($pts <= 0)
                {
                    continue;
                }
                /** @var PartyMember $m */
                $m = $row['member'];
                $mid = (string)$m->getId();
                if (!isset($aggregate[$mid]))
                {
                    $aggregate[$mid] = ['member' => $m, 'points' => 0];
                }
                $aggregate[$mid]['points'] += $pts;
            }
        }
        // sort by points desc, then name asc
        uasort($aggregate, function (array $a, array $b)
        {
            $d = ($b['points'] <=> $a['points']);
            if ($d !== 0)
            {
                return $d;
            }
            $an = $a['member']->getUser()?->getUsername() ?? '';
            $bn = $b['member']->getUser()?->getUsername() ?? '';
            return strcmp($an, $bn);
        });
        $scoreboard = array_slice(array_values($aggregate), 0, 10);

        return $this->render('clash/games/list.html.twig', [
            'party' => $party,
            'me' => $pm,
            'games' => $games,
            'scoreboard' => $scoreboard,
        ]);
    }

    #[Route('/{slug}', name: 'party_clash_game_detail', methods: ['GET'])]
    public function detail(Party $party, string $slug): Response
    {
        $pm = $this->requireMembership($party);
        $game = $this->registry->getGame($slug);
        if (!$game)
        {
            throw $this->createNotFoundException();
        }

        $now = new \DateTimeImmutable();
        $closed = (bool)($game['closed'] ?? false);
        $active = !$closed
            && ($game['startAt'] instanceof \DateTimeImmutable)
            && $game['startAt'] <= $now
            && (!($game['endAt'] instanceof \DateTimeImmutable) || $game['endAt'] >= $now);

        // If game has ended (time) or is closed, finalize points (idempotent)
        if ($closed || ($game['endAt'] instanceof \DateTimeImmutable && $game['endAt'] < $now))
        {
            $this->finalizer->finalizePartyGame($party, $game['slug'], $game['rankPoints']);
        }

        $leaderboard = $this->scores->getLeaderboard($party, $game['slug'], 100);

        // Build play URL with context (new tab)
        $callbackUrl = $this->generateUrl(
            'party_clash_game_submit',
            ['id' => (string)$party->getId(), 'slug' => $game['slug']],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $query = http_build_query([
            'partyId' => (string)$party->getId(),
            'playerId' => (string)$pm->getId(),
            'gameId' => $game['slug'],
            'callbackUrl' => $callbackUrl,
        ]);
        $playUrl = $game['path'] . '?' . $query;

        return $this->render('clash/games/detail.html.twig', [
            'party' => $party,
            'me' => $pm,
            'game' => $game,
            'active' => $active,
            'leaderboard' => $leaderboard,
            'playUrl' => $playUrl,
        ]);
    }

}
