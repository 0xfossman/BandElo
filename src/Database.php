<?php

declare(strict_types=1);

namespace BandElo;

use PDO;

final class Database
{
    private PDO $pdo;

    public function __construct(Config $config)
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config->get('DB_HOST'), $config->get('DB_PORT', '3306'), $config->get('DB_NAME'));
        $this->pdo = new PDO($dsn, $config->get('DB_USER'), $config->get('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
