<?php
require __DIR__ . '/../../config/bootstrap.php';
use BandElo\Http\Response;
use BandElo\Service\SpotifyService;
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));
Response::redirect((new SpotifyService($config))->authUrl($_SESSION['oauth_state']));
