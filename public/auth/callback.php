<?php

require __DIR__ . '/../../config/bootstrap.php';

use BandElo\Http\Response;
use BandElo\Http\Security;
use BandElo\Repository\ArtistRepository;
use BandElo\Repository\UserRepository;
use BandElo\Service\SpotifyService;

$error = null;

try {
    if (!hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'] ?? '') || empty($_GET['code'])) {
        throw new RuntimeException('Der Spotify-Login konnte nicht validiert werden. Bitte versuche es erneut.');
    }

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
    foreach ($spotify->topArtists($tokens['access_token']) as $artist) {
        $artists->linkToUser($userId, $artists->upsertFromSpotify($artist));
    }

    Response::redirect('/vote.php');
} catch (Throwable $exception) {
    unset($_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['token_expires_at'], $_SESSION['oauth_state']);
    error_log('Spotify callback failed: ' . $exception->getMessage());
    $error = $exception->getMessage();
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spotify Login fehlgeschlagen - BandElo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/app.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<main class="container py-5">
    <section class="content-card p-4 bg-white">
        <h1>Spotify Login fehlgeschlagen</h1>
        <p>Die Anmeldung bei Spotify konnte nicht abgeschlossen werden.</p>
        <p class="alert alert-danger"><?= Security::e($error) ?></p>
        <p>Prüfe insbesondere, ob <code>SPOTIFY_REDIRECT_URI</code> exakt mit der Redirect URI in der Spotify Developer Console übereinstimmt.</p>
        <a class="btn btn-primary" href="/auth/login.php">Erneut mit Spotify anmelden</a>
        <a class="btn btn-link" href="/">Zur Startseite</a>
    </section>
</main>
</body>
</html>
