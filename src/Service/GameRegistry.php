<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\GameConfigRepository;

final class GameRegistry
{
    /**
     * @param array<int, array{slug:string,title:string,description:string,icon?:string,image?:string,path:string,startAt?:mixed,endAt?:mixed}> $games
     * @param array<int,int> $rankPoints
     */
    public function __construct(
        private readonly array $games = [],
        private readonly array $rankPoints = [
            1 => 15,
            2 => 12,
            3 => 10,
            4 => 8,
            5 => 6,
            6 => 5,
            7 => 4,
            8 => 3,
            9 => 2,
            10 => 1
        ],
        private readonly GameConfigRepository $configs,
    ) {
    }

    /**
     * @param mixed $v
     */
    private function parseDate(mixed $v): ?\DateTimeImmutable
    {
        if ($v === null || $v === '')
        {
            return null;
        }
        if ($v instanceof \DateTimeImmutable)
        {
            return $v;
        }
        if ($v instanceof \DateTimeInterface)
        {
            return \DateTimeImmutable::createFromInterface($v);
        }
        if (is_string($v))
        {
            $now = new \DateTimeImmutable();
            if (strtolower($v) === 'now')
            {
                return $now;
            }
            if (preg_match('/^[+-]/', $v) === 1)
            {
                return $now->modify($v);
            }
            try
            {
                return new \DateTimeImmutable($v);
            } catch (\Exception)
            {
                return null;
            }
        }
        return null;
    }

    /**
     * @param array{slug:string,title:string,description:string,icon?:string,image?:string,path:string,startAt?:mixed,endAt?:mixed} $g
     * @return array{slug:string,title:string,description:string,icon?:string,image?:string,path:string,startAt:?\DateTimeImmutable,endAt:?\DateTimeImmutable,closed:bool,rankPoints:array<int,int>}
     */
    private function normalize(array $g): array
    {
        $g['startAt'] = $this->parseDate($g['startAt'] ?? null);
        $g['endAt'] = $this->parseDate($g['endAt'] ?? null);
        $g['closed'] = false;
        // Always use central rank points
        $g['rankPoints'] = $this->rankPoints;
        return $g;
    }

    /**
     * @return array<int, array{slug:string,title:string,description:string,icon?:string,image?:string,path:string,startAt:?\DateTimeImmutable,endAt:?\DateTimeImmutable,closed:bool,rankPoints:array<int,int> }>
     */
    public function listGames(): array
    {
        $games = array_map(fn(array $g) => $this->normalize($g), $this->games);
        // Override timings from DB configs if present
        $map = $this->configs->mapBySlug();
        foreach ($games as &$g)
        {
            $slug = $g['slug'] ?? null;
            if ($slug && isset($map[$slug]))
            {
                $cfg = $map[$slug];
                $g['startAt'] = $cfg->getStartAt();
                $g['endAt'] = $cfg->getEndAt();
                $g['closed'] = $cfg->isClosed();
            }
        }
        unset($g);
        return $games;
    }

    /**
     * @return array{slug:string,title:string,description:string,icon?:string,image?:string,path:string,startAt:?\DateTimeImmutable,endAt:?\DateTimeImmutable,closed:bool,rankPoints:array<int,int>}|null
     */
    public function getGame(string $slug): ?array
    {
        foreach ($this->listGames() as $g)
        {
            if (($g['slug'] ?? null) === $slug)
            {
                return $g;
            }
        }
        return null;
    }

    /**
     * @return array<int,int>
     */
    public function getRankPoints(): array
    {
        return $this->rankPoints;
    }
}
