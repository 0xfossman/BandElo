<?php

declare(strict_types=1);

require __DIR__ . '/../config/bootstrap.php';

$required = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','SPOTIFY_CLIENT_ID','SPOTIFY_CLIENT_SECRET','SPOTIFY_REDIRECT_URI'];
foreach ($required as $key) {
    if ($config->get($key, '') === '') {
        throw new RuntimeException("Missing {$key} in .env");
    }
}
$sql = file_get_contents(__DIR__ . '/../database/schema.sql');
if ($sql === false) {
    throw new RuntimeException('Unable to read schema.sql');
}
$db->pdo()->exec($sql);
echo "BandElo schema installed and configuration verified.\n";
