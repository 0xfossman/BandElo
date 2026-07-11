<?php require __DIR__ . '/../config/bootstrap.php';
use BandElo\Http\Security;
$loggedIn = isset($_SESSION['user_id']); ?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><title>BandElo</title></head><body>
<h1>BandElo</h1>
<nav><a href="/leaderboard.php">Community Top 10</a><?php if ($loggedIn): ?> | <a href="/vote.php">Voting</a> | <a href="/auth/logout.php">Logout</a><?php endif; ?></nav>
<?php if ($loggedIn): ?><p>Angemeldet als <?= Security::e($_SESSION['display_name'] ?? 'Spotify User') ?>.</p><p><a href="/vote.php">Jetzt abstimmen</a></p><?php else: ?><p><a href="/auth/login.php">Mit Spotify anmelden</a></p><?php endif; ?>
</body></html>
