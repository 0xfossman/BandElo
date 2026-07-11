<?php
require __DIR__ . '/../../config/bootstrap.php';
use BandElo\Http\Response;
use BandElo\Repository\ArtistRepository;
use BandElo\Repository\UserRepository;
use BandElo\Service\SpotifyService;
if (!hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'] ?? '') || empty($_GET['code'])) { throw new RuntimeException('Invalid OAuth callback.'); }
$spotify = new SpotifyService($config);
$tokens = $spotify->token((string) $_GET['code']);
$_SESSION['access_token'] = $tokens['access_token'];
$_SESSION['refresh_token'] = $tokens['refresh_token'] ?? null;
$_SESSION['token_expires_at'] = time() + (int) ($tokens['expires_in'] ?? 3600);
$me = $spotify->me($tokens['access_token']);
$userId = (new UserRepository($db->pdo()))->upsert($me['id'], $me['display_name'] ?? $me['id']);
$_SESSION['user_id'] = $userId;
$_SESSION['display_name'] = $me['display_name'] ?? $me['id'];
$artists = new ArtistRepository($db->pdo());
foreach ($spotify->topArtists($tokens['access_token']) as $artist) { $artists->linkToUser($userId, $artists->upsertFromSpotify($artist)); }
Response::redirect('/vote.php');
