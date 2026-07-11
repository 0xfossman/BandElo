<?php

require __DIR__ . '/../../config/bootstrap.php';

use BandElo\Http\Response;
use BandElo\Http\Security;
use BandElo\Repository\ArtistRepository;

try {
    $userId = Security::requireUser();
    $repository = new ArtistRepository($db->pdo());
    $tournament = $_SESSION['tournament'] ?? [];
    $championId = isset($tournament['champion_id']) ? (int) $tournament['champion_id'] : null;
    $excluded = isset($tournament['eliminated']) && is_array($tournament['eliminated']) ? $tournament['eliminated'] : [];
    $artists = $championId ? $repository->nextPairWithChampion($userId, $championId, $excluded) : $repository->nextPair($userId);

    Response::json([
        'artists' => $artists,
        'champion_id' => $championId,
        'votes' => (int) ($tournament['votes'] ?? 0),
        'complete' => $championId !== null && count($artists) === 1,
    ]);
} catch (Throwable $e) {
    Response::json(['error' => $e->getMessage()], 401);
}
