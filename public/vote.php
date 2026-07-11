<?php

require __DIR__ . '/../config/bootstrap.php';

use BandElo\Http\Security;

Security::requireUser();
$csrf = Security::csrfToken();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voting - BandElo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<main class="container py-4">
    <nav class="navbar navbar-expand mb-4">
        <a class="navbar-brand fw-bold" href="/">BandElo</a>
        <div class="navbar-nav">
            <a class="nav-link" href="/leaderboard.php">Community Top 10</a>
            <a class="nav-link" href="/auth/logout.php">Logout</a>
        </div>
    </nav>

    <section class="p-4 p-md-5 mb-4 bg-white rounded-3 shadow-sm">
        <h1 class="display-6">Wer bleibt im Rennen?</h1>
        <p class="lead mb-0">Klicke auf deinen bevorzugten Interpreten. Der Gewinner bleibt stehen und tritt gegen den nächsten Herausforderer an.</p>
    </section>

    <div id="message" class="alert alert-info d-none" role="status"></div>
    <section id="pair" class="row g-4 align-items-stretch" aria-live="polite"></section>
</main>
<script>
const csrf = <?= json_encode($csrf) ?>;

async function loadPair() {
    const response = await fetch('/api/next-pair.php');
    const data = await response.json();
    const box = document.getElementById('pair');

    if (data.complete && data.artists && data.artists.length === 1) {
        const champion = data.artists[0];
        box.innerHTML = championCard(champion, data.votes || 0);
        document.getElementById('reset-tournament').addEventListener('click', resetTournament);
        return;
    }

    if (!data.artists || data.artists.length < 2) {
        box.innerHTML = '<div class="col-12"><div class="alert alert-warning">Keine Paarung verfügbar. Importiere zuerst Spotify Top Artists.</div></div>';
        return;
    }

    box.innerHTML = data.artists.map((artist) => artistCard(artist, Number(data.champion_id) === Number(artist.id))).join('');
    box.querySelectorAll('button[data-id]').forEach((button) => button.addEventListener('click', () => vote(data.artists[0].id, data.artists[1].id, button.dataset.id)));
}

async function vote(artistA, artistB, winner) {
    const response = await fetch('/api/vote.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
        body: JSON.stringify({artist_a_id: artistA, artist_b_id: artistB, winner_artist_id: Number(winner)})
    });
    const data = await response.json();
    showMessage(data.success ? `Vote gespeichert. Aktueller Favorit bleibt in Runde ${data.votes}.` : (data.error || 'Fehler'), data.success);
    await loadPair();
}

async function resetTournament() {
    await fetch('/api/reset-tournament.php', {method: 'POST', headers: {'X-CSRF-Token': csrf}});
    showMessage('Neue Favoritenrunde gestartet.', true);
    await loadPair();
}

function artistCard(artist, isChampion) {
    return `<article class="col-12 col-md-6">
        <button type="button" data-id="${artist.id}" class="card h-100 w-100 text-start shadow-sm border-0">
            ${artist.image_url ? `<img src="${escapeHtml(artist.image_url)}" class="card-img-top object-fit-cover" alt="" style="max-height: 320px;">` : ''}
            <span class="card-body">
                ${isChampion ? '<span class="badge text-bg-success mb-2">Aktueller Favorit</span>' : '<span class="badge text-bg-secondary mb-2">Herausforderer</span>'}
                <span class="h3 d-block">${escapeHtml(artist.name)}</span>
                <span class="text-muted">Elo: ${artist.global_elo}</span>
            </span>
        </button>
    </article>`;
}

function championCard(champion, votes) {
    return `<article class="col-12">
        <div class="card border-success shadow-sm">
            ${champion.image_url ? `<img src="${escapeHtml(champion.image_url)}" class="card-img-top object-fit-cover" alt="" style="max-height: 420px;">` : ''}
            <div class="card-body text-center">
                <span class="badge text-bg-success mb-3">Gewinner nach ${votes} Votes</span>
                <h2>${escapeHtml(champion.name)}</h2>
                <p class="lead">Das ist dein aktueller Lieblingsinterpret aus dieser Runde.</p>
                <button id="reset-tournament" type="button" class="btn btn-primary">Neue Runde starten</button>
            </div>
        </div>
    </article>`;
}

function showMessage(message, success) {
    const element = document.getElementById('message');
    element.className = `alert ${success ? 'alert-success' : 'alert-danger'}`;
    element.textContent = message;
}

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, (char) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[char]));
}

loadPair();
</script>
</body>
</html>
