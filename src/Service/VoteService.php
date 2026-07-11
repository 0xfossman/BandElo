<?php

declare(strict_types=1);

namespace BandElo\Service;

use BandElo\Repository\ArtistRepository;
use PDO;

final class VoteService
{
    public function __construct(private PDO $pdo, private ArtistRepository $artists, private EloService $elo) {}

    public function vote(int $userId, int $artistA, int $artistB, int $winner): array
    {
        if ($artistA === $artistB || !in_array($winner, [$artistA, $artistB], true) || !$this->artists->userOwnsArtists($userId, $artistA, $artistB)) {
            throw new \RuntimeException('Invalid vote.');
        }
        $loser = $winner === $artistA ? $artistB : $artistA;
        $this->pdo->beginTransaction();
        try {
            $winnerRow = $this->artists->findForUpdate($winner);
            $loserRow = $this->artists->findForUpdate($loser);
            $result = $this->elo->calculate((float) $winnerRow['global_elo'], (float) $loserRow['global_elo']);
            $this->artists->updateStats($winner, $result['winner'], true);
            $this->artists->updateStats($loser, $result['loser'], false);
            $stmt = $this->pdo->prepare('INSERT INTO votes (user_id, artist_a_id, artist_b_id, winner_artist_id, loser_artist_id, elo_before_winner, elo_after_winner, elo_before_loser, elo_after_loser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $artistA, $artistB, $winner, $loser, $winnerRow['global_elo'], $result['winner'], $loserRow['global_elo'], $result['loser']]);
            $this->pdo->commit();
            return ['winner_elo' => $result['winner'], 'loser_elo' => $result['loser']];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
