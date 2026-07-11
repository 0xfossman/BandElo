<?php

require __DIR__ . '/../../config/bootstrap.php';

use BandElo\Http\Response;
use BandElo\Http\Security;
use BandElo\Repository\ArtistRepository;
use BandElo\Service\EloService;
use BandElo\Service\VoteService;

try {
    $userId = Security::requireUser();
    Security::requireCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    $tournament = $_SESSION['tournament'] ?? ['eliminated' => [], 'votes' => 0];
    if ((int) ($tournament['votes'] ?? 0) >= 20) {
        throw new RuntimeException('Diese Voting-Runde ist bereits abgeschlossen.');
    }

    $input = json_decode(file_get_contents('php://input') ?: '{}', true, 512, JSON_THROW_ON_ERROR);
    $artistA = (int) $input['artist_a_id'];
    $artistB = (int) $input['artist_b_id'];
    $winner = (int) $input['winner_artist_id'];
    $loser = $winner === $artistA ? $artistB : $artistA;

    $service = new VoteService($db->pdo(), new ArtistRepository($db->pdo()), new EloService($config->int('ELO_K_FACTOR', 32)));
    $result = $service->vote($userId, $artistA, $artistB, $winner);

    $tournament['champion_id'] = $winner;
    $tournament['eliminated'] = array_values(array_unique(array_map('intval', array_merge($tournament['eliminated'] ?? [], [$loser]))));
    $tournament['votes'] = (int) ($tournament['votes'] ?? 0) + 1;
    $_SESSION['tournament'] = $tournament;

    Response::json(['success' => true, 'result' => $result, 'champion_id' => $winner, 'votes' => $tournament['votes'], 'votes_remaining' => max(0, 20 - $tournament['votes']), 'complete' => $tournament['votes'] >= 20]);
} catch (Throwable $e) {
    Response::json(['success' => false, 'error' => $e->getMessage()], 400);
}
