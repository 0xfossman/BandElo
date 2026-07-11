<?php

declare(strict_types=1);

namespace BandElo;

final class Config
{
    public function __construct(private array $env) {}

    public function get(string $key, ?string $default = null): string
    {
        $value = $this->env[$key] ?? getenv($key) ?: $default;
        if ($value === null) {
            throw new \RuntimeException("Missing configuration value: {$key}");
        }
        return (string) $value;
    }

    public function int(string $key, int $default): int
    {
        return (int) $this->get($key, (string) $default);
    }
}
