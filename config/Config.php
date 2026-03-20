<?php

namespace Config;

/**
 * Configurações globais da aplicação
 */
class Config
{
    // URLs da aplicação
    public const APP_NAME = 'Terapia - Sistema de Consultório';
    public const APP_URL = 'http://localhost:8000';
    public const APP_ENV = 'development'; // development ou production

    // Configurações de sessão
    public const SESSION_TIMEOUT = 3600; // 1 hora
    public const SESSION_NAME = 'terapia_session';

    // Configurações de upload
    public const UPLOAD_PATH = __DIR__ . '/../uploads/';
    public const MAX_UPLOAD_SIZE = 5242880; // 5MB
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

    // Configurações de segurança
    public const HASH_ALGORITHM = 'sha256';

    // Paginação
    public const ITEMS_PER_PAGE = 15;

    /**
     * Verifica se está em ambiente de produção
     */
    public static function isProduction(): bool
    {
        return self::APP_ENV === 'production';
    }

    /**
     * Verifica se está em ambiente de desenvolvimento
     */
    public static function isDevelopment(): bool
    {
        return self::APP_ENV === 'development';
    }
}
