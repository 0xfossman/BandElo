<?php require __DIR__ . '/../config/bootstrap.php';
use BandElo\Http\Security;
Security::requireUser(); $csrf = Security::csrfToken(); ?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><title>Voting - BandElo</title></head><body>
<h1>Voting</h1><nav><a href="/">Start</a> | <a href="/leaderboard.php">Community Top 10</a></nav>
<p>Klicke auf den bevorzugten Künstler.</p><div id="message"></div><section id="pair" aria-live="polite"></section>
<script>
const csrf = <?= json_encode($csrf) ?>;
async function loadPair(){const r=await fetch('/api/next-pair.php'); const data=await r.json(); const box=document.getElementById('pair'); if(!data.artists||data.artists.length<2){box.innerHTML='<p>Keine Paarung verfügbar. Importiere zuerst Spotify Top Artists.</p>';return;} box.innerHTML=data.artists.map(a=>`<article><button type="button" data-id="${a.id}"><img src="${escapeHtml(a.image_url||'')}" alt="" width="160"><br><strong>${escapeHtml(a.name)}</strong><br>Elo: ${a.global_elo}</button></article>`).join('<p>gegen</p>'); box.querySelectorAll('button').forEach(b=>b.addEventListener('click',()=>vote(data.artists[0].id,data.artists[1].id,b.dataset.id)));}
async function vote(a,b,w){const r=await fetch('/api/vote.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({artist_a_id:a,artist_b_id:b,winner_artist_id:Number(w)})}); const data=await r.json(); document.getElementById('message').textContent=data.success?'Vote gespeichert.':(data.error||'Fehler'); await loadPair();}
function escapeHtml(s){return String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
loadPair();
</script></body></html>
