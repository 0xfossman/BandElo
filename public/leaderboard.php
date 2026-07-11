<?php require __DIR__ . '/../config/bootstrap.php';
use BandElo\Http\Security;
use BandElo\Repository\ArtistRepository;
$rows = (new ArtistRepository($db->pdo()))->leaderboard(10); ?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><title>Community Top 10</title></head><body>
<h1>Community Top 10</h1><nav><a href="/">Start</a> | <a href="/vote.php">Voting</a></nav>
<table><thead><tr><th>Platz</th><th>Bild</th><th>Name</th><th>Elo</th><th>Siege</th><th>Niederlagen</th><th>Matches</th><th>Gewinnquote</th></tr></thead><tbody>
<?php foreach ($rows as $i => $row): ?><tr><td><?= $i + 1 ?></td><td><?php if ($row['image_url']): ?><img src="<?= Security::e($row['image_url']) ?>" alt="" width="64"><?php endif; ?></td><td><?= Security::e($row['name']) ?></td><td><?= Security::e((string) $row['global_elo']) ?></td><td><?= (int) $row['wins'] ?></td><td><?= (int) $row['losses'] ?></td><td><?= (int) $row['matches'] ?></td><td><?= Security::e((string) $row['win_rate']) ?>%</td></tr><?php endforeach; ?>
</tbody></table></body></html>
