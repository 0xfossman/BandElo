<?php
require __DIR__ . '/../../config/bootstrap.php';
use BandElo\Http\Response; use BandElo\Repository\ArtistRepository;
$limit = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
Response::json(['artists' => (new ArtistRepository($db->pdo()))->leaderboard($limit)]);
