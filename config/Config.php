<?php

namespace Config;

/*
 * Lê o APP_URL do arquivo .env antes de definir a classe,
 * permitindo que a constante seja dinâmica sem alterar as views.
 */
(function () {
    $envFile = __DIR__ . '/../.env';
    $appUrl  = '';

    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            if (trim($key) === 'APP_URL') {
                $appUrl = rtrim(trim($val), '/');
                break;
            }
        }
    }

    // Fallback: auto-detecta protocolo + host atual
    if ($appUrl === '') {
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $https . '://' . $host;
    }

    if (!defined('Config\__APP_URL')) {
        define('Config\__APP_URL', $appUrl);
    }
})();

/**
 * Configurações globais da aplicação
 */
class Config
{
    // ── Identidade ────────────────────────────────────────
    public const APP_NAME = 'Terapia - Sistema de Consultório';

    /** URL base lida do .env (ou auto-detectada). Sem barra no final. */
    public const APP_URL  = __APP_URL;

    // ── Ambiente ──────────────────────────────────────────
    public const APP_ENV  = 'production';   // development | production

    // ── Sessão ────────────────────────────────────────────
    public const SESSION_TIMEOUT = 3600;    // segundos
    public const SESSION_NAME    = 'terapia_session';

    // ── Upload ────────────────────────────────────────────
    public const UPLOAD_PATH        = __DIR__ . '/../uploads/';
    public const MAX_UPLOAD_SIZE    = 5242880;   // 5 MB
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

    // ── Segurança ─────────────────────────────────────────
    public const HASH_ALGORITHM = 'sha256';

    // ── Paginação ─────────────────────────────────────────
    public const ITEMS_PER_PAGE = 15;

    public static function isProduction(): bool  { return self::APP_ENV === 'production';  }
    public static function isDevelopment(): bool { return self::APP_ENV === 'development'; }
}
