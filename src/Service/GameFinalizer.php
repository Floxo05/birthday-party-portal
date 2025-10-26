<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GameScore;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Repository\GameScoreRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final readonly class GameFinalizer
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameScoreRepository $scores,
    ) {
    }

    /**
     * Finalize a single party+game atomically and idempotently.
     * Returns number of GameScore rows newly applied.
     *
     * @param array<int,int> $rankPoints
     */
    public function finalizePartyGame(Party $party, string $slug, array $rankPoints): int
    {
        $applied = 0;
        $this->em->beginTransaction();
        try
        {
            // Lock all relevant scores for update to prevent concurrent double-credit
            $qb = $this->scores->createQueryBuilder('gs')
                ->leftJoin('gs.partyMember', 'pm')
                ->addSelect('pm')
                ->where('gs.party = :party')
                ->andWhere('gs.gameSlug = :slug')
                ->setParameter('party', $party->getId(), 'uuid')
                ->setParameter('slug', $slug);
            $query = $qb->getQuery();
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
            /** @var GameScore[] $all */
            $all = $query->getResult();

            if (!$all)
            {
                $this->em->commit();
                return 0;
            }

            // If there is nothing pending anymore, exit quickly
            $pending = array_filter($all, static fn(GameScore $gs) => $gs->getAppliedAt() === null);
            if (!$pending)
            {
                $this->em->commit();
                return 0;
            }

            // Sort by bestScore desc, then lastSubmittedAt asc
            usort($all, static function (GameScore $a, GameScore $b)
            {
                $d = $b->getBestScore() <=> $a->getBestScore();
                if ($d !== 0)
                {
                    return $d;
                }
                return $a->getLastSubmittedAt() <=> $b->getLastSubmittedAt();
            });

            // Assign ranks with ties
            $rank = 0;
            $prevScore = null;
            $actual = 0;
            $ranks = [];
            foreach ($all as $gs)
            {
                $actual++;
                if ($prevScore === null || $gs->getBestScore() !== $prevScore)
                {
                    $rank = $actual;
                    $prevScore = $gs->getBestScore();
                }
                $ranks[spl_object_id($gs)] = $rank;
            }

            $now = new \DateTimeImmutable();
            foreach ($all as $gs)
            {
                if ($gs->getAppliedAt() !== null)
                {
                    continue;
                }
                $r = $ranks[spl_object_id($gs)] ?? 0;
                $pts = $rankPoints[$r] ?? 0;
                if ($pts > 0)
                {
                    /** @var PartyMember $pm */
                    $pm = $gs->getPartyMember();
                    $pm->setClashPoints($pm->getClashPoints() + $pts);
                    $this->em->persist($pm);
                }
                $gs->setAppliedAt($now);
                $this->em->persist($gs);
                $applied++;
            }

            $this->em->flush();
            $this->em->commit();
            return $applied;
        } catch (\Throwable $e)
        {
            try
            {
                $this->em->rollback();
            } catch (Exception)
            {
            }
            throw $e;
        }
    }

    /**
     * Finalize all parties that have pending scores for a given game slug.
     * Returns number of parties finalized.
     *
     * @param array<int,int> $rankPoints
     */
    public function finalizeAllPartiesForSlug(string $slug, array $rankPoints): int
    {
        // Fetch all scores with this slug (no lock yet); we'll lock per-party inside finalizePartyGame
        $all = $this->scores->findBy(['gameSlug' => $slug]);
        if (!$all)
        {
            return 0;
        }
        // Group by party id
        $byParty = [];
        foreach ($all as $gs)
        {
            if ($gs->getAppliedAt() !== null)
            {
                continue;
            }
            $pid = (string)$gs->getParty()->getId();
            $byParty[$pid] = $gs->getParty();
        }
        $cnt = 0;
        foreach ($byParty as $party)
        {
            $n = $this->finalizePartyGame($party, $slug, $rankPoints);
            if ($n > 0)
            {
                $cnt++;
            }
        }
        return $cnt;
    }
}
