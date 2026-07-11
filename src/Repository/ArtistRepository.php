<?php

declare(strict_types=1);

namespace BandElo\Repository;

use PDO;

final class ArtistRepository
{
    public function __construct(private PDO $pdo) {}

    public function upsertFromSpotify(array $artist): int
    {
        $image = $artist['images'][0]['url'] ?? null;
        $genres = json_encode($artist['genres'] ?? [], JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare('INSERT INTO artists (spotify_id, name, image_url, genres, popularity) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), image_url = VALUES(image_url), genres = VALUES(genres), popularity = VALUES(popularity), updated_at = CURRENT_TIMESTAMP');
        $stmt->execute([$artist['id'], $artist['name'], $image, $genres, (int) ($artist['popularity'] ?? 0)]);
        $id = (int) $this->pdo->lastInsertId();
        if ($id > 0) {
            return $id;
        }
        $select = $this->pdo->prepare('SELECT id FROM artists WHERE spotify_id = ?');
        $select->execute([$artist['id']]);
        return (int) $select->fetchColumn();
    }

    public function linkToUser(int $userId, int $artistId): void
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO user_artists (user_id, artist_id) VALUES (?, ?)');
        $stmt->execute([$userId, $artistId]);
    }

    public function nextPair(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.* FROM artists a INNER JOIN user_artists ua ON ua.artist_id = a.id WHERE ua.user_id = ? ORDER BY RAND() LIMIT 2');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function nextPairWithChampion(int $userId, int $championId, array $excludedIds = []): array
    {
        $champion = $this->findForUser($userId, $championId);
        if ($champion === null) {
            return $this->nextPair($userId);
        }

        $excluded = array_values(array_unique(array_map('intval', array_merge($excludedIds, [$championId]))));
        $placeholders = implode(',', array_fill(0, count($excluded), '?'));
        $sql = "SELECT a.* FROM artists a INNER JOIN user_artists ua ON ua.artist_id = a.id WHERE ua.user_id = ? AND a.id NOT IN ({$placeholders}) ORDER BY RAND() LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge([$userId], $excluded));
        $challenger = $stmt->fetch();

        if (!$challenger) {
            $stmt = $this->pdo->prepare('SELECT a.* FROM artists a INNER JOIN user_artists ua ON ua.artist_id = a.id WHERE ua.user_id = ? AND a.id <> ? ORDER BY RAND() LIMIT 1');
            $stmt->execute([$userId, $championId]);
            $challenger = $stmt->fetch();
        }

        return $challenger ? [$champion, $challenger] : [$champion];
    }

    public function findForUser(int $userId, int $artistId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT a.* FROM artists a INNER JOIN user_artists ua ON ua.artist_id = a.id WHERE ua.user_id = ? AND a.id = ?');
        $stmt->execute([$userId, $artistId]);
        $artist = $stmt->fetch();
        return $artist ?: null;
    }

    public function userOwnsArtists(int $userId, int $a, int $b): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM user_artists WHERE user_id = ? AND artist_id IN (?, ?)');
        $stmt->execute([$userId, $a, $b]);
        return (int) $stmt->fetchColumn() === 2;
    }

    public function findForUpdate(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM artists WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $artist = $stmt->fetch();
        if (!$artist) {
            throw new \RuntimeException('Artist not found.');
        }
        return $artist;
    }

    public function updateStats(int $id, float $elo, bool $won): void
    {
        $sql = $won
            ? 'UPDATE artists SET global_elo = ?, wins = wins + 1, matches = matches + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
            : 'UPDATE artists SET global_elo = ?, losses = losses + 1, matches = matches + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $this->pdo->prepare($sql)->execute([$elo, $id]);
    }

    public function leaderboard(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare('SELECT *, CASE WHEN matches = 0 THEN 0 ELSE ROUND(wins / matches * 100, 2) END AS win_rate FROM artists ORDER BY global_elo DESC, wins DESC, name ASC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
