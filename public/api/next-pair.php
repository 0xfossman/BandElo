<?php
require __DIR__ . '/../../config/bootstrap.php';
use BandElo\Http\Response; use BandElo\Http\Security; use BandElo\Repository\ArtistRepository;
try { $userId = Security::requireUser(); Response::json(['artists' => (new ArtistRepository($db->pdo()))->nextPair($userId)]); } catch (Throwable $e) { Response::json(['error' => $e->getMessage()], 401); }
