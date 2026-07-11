<?php

declare(strict_types=1);

namespace BandElo;

final class Config
{
    public function __construct(private array $env) {}

    public function get(string $key, ?string $default = null): string
    {
        if (array_key_exists($key, $this->env)) {
            $value = $this->env[$key];
        } else {
            $environmentValue = getenv($key);
            $value = $environmentValue === false ? $default : $environmentValue;
        }

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
