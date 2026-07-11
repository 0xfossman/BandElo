<?php

require __DIR__ . '/../config/bootstrap.php';

use BandElo\Http\Security;
use BandElo\Repository\ArtistRepository;

$rows = (new ArtistRepository($db->pdo()))->leaderboard(10);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Community Top 10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/app.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<main class="container py-4">
    <nav class="navbar navbar-expand mb-4">
        <a class="navbar-brand fw-bold" href="/">BandElo</a>
        <div class="navbar-nav">
            <a class="nav-link" href="/vote.php">Voting</a>
        </div>
    </nav>

    <section class="content-card p-4 bg-white">
        <h1 class="mb-4">Community Top 10</h1>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Platz</th><th>Bild</th><th>Name</th><th>Elo</th><th>Siege</th><th>Niederlagen</th><th>Matches</th><th>Gewinnquote</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?php if ($row['image_url']): ?><img class="rounded" src="<?= Security::e($row['image_url']) ?>" alt="" width="64" height="64"><?php endif; ?></td>
                        <td><?= Security::e($row['name']) ?></td>
                        <td><?= Security::e((string) $row['global_elo']) ?></td>
                        <td><?= (int) $row['wins'] ?></td>
                        <td><?= (int) $row['losses'] ?></td>
                        <td><?= (int) $row['matches'] ?></td>
                        <td><?= Security::e((string) $row['win_rate']) ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
