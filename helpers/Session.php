<?php

namespace Helpers;

use Config\Config;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        Config::loadEnv();
        session_name((string) Config::get('SESSION_NAME', 'terapia_session'));
        session_start();
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
