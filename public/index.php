<?php

require __DIR__ . '/../config/bootstrap.php';

use BandElo\Http\Security;

$loggedIn = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BandElo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<main class="container py-5">
    <nav class="navbar navbar-expand mb-4">
        <a class="navbar-brand fw-bold" href="/">BandElo</a>
        <div class="navbar-nav">
            <a class="nav-link" href="/leaderboard.php">Community Top 10</a>
            <?php if ($loggedIn): ?>
                <a class="nav-link" href="/vote.php">Voting</a>
                <a class="nav-link" href="/auth/logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="p-4 p-md-5 bg-white rounded-3 shadow-sm">
        <h1 class="display-5 fw-bold">BandElo</h1>
        <p class="lead">Importiere deine Spotify Top-20-Interpreten und finde deinen Favoriten im Gewinner-bleibt-Modus.</p>
        <?php if ($loggedIn): ?>
            <p>Angemeldet als <strong><?= Security::e($_SESSION['display_name'] ?? 'Spotify User') ?></strong>.</p>
            <a class="btn btn-primary btn-lg" href="/vote.php">Jetzt abstimmen</a>
        <?php else: ?>
            <a class="btn btn-success btn-lg" href="/auth/login.php">Mit Spotify anmelden</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
