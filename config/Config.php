<?php

namespace Config;

class Config
{
    private static bool $loaded = false;

    public static function loadEnv(): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = dirname(__DIR__) . '/.env';
        $examplePath = dirname(__DIR__) . '/.env.example';
        $target = file_exists($envPath) ? $envPath : $examplePath;

        if (!file_exists($target)) {
            self::$loaded = true;
            return;
        }

        $lines = file($target, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::loadEnv();
        return $_ENV[$key] ?? $default;
    }
}
