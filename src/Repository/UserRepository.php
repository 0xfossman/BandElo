<?php

declare(strict_types=1);

namespace BandElo\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function upsert(string $spotifyId, string $displayName): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (spotify_user_id, display_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE display_name = VALUES(display_name)');
        $stmt->execute([$spotifyId, $displayName]);
        $id = (int) $this->pdo->lastInsertId();
        if ($id > 0) {
            return $id;
        }
        $select = $this->pdo->prepare('SELECT id FROM users WHERE spotify_user_id = ?');
        $select->execute([$spotifyId]);
        return (int) $select->fetchColumn();
    }
}
