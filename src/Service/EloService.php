<?php

declare(strict_types=1);

namespace BandElo\Service;

final class EloService
{
    public function __construct(private int $kFactor) {}

    public function calculate(float $winnerElo, float $loserElo): array
    {
        $expectedWinner = 1 / (1 + (10 ** (($loserElo - $winnerElo) / 400)));
        $expectedLoser = 1 / (1 + (10 ** (($winnerElo - $loserElo) / 400)));
        return [
            'winner' => round($winnerElo + $this->kFactor * (1 - $expectedWinner), 2),
            'loser' => round($loserElo + $this->kFactor * (0 - $expectedLoser), 2),
        ];
    }
}
