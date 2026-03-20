<?php

namespace Helpers;

use Classes\Model;

/**
 * Classe para autenticação do usuário
 */
class Auth
{
    /**
     * Faz login do usuário
     */
    public static function login(int $userId, string $userName, string $userRole): void
    {
        Session::start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['login_time'] = time();
    }

    /**
     * Faz logout do usuário
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Verifica se o usuário está autenticado
     */
    public static function isAuthenticated(): bool
    {
        Session::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtém ID do usuário autenticado
     */
    public static function userId(): ?int
    {
        Session::start();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtém nome do usuário autenticado
     */
    public static function userName(): ?string
    {
        Session::start();
        return $_SESSION['user_name'] ?? null;
    }

    /**
     * Obtém role do usuário
     */
    public static function userRole(): ?string
    {
        Session::start();
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Verifica se é admin
     */
    public static function isAdmin(): bool
    {
        return self::userRole() === 'admin';
    }

    /**
     * Verifica se é paciente
     */
    public static function isPatient(): bool
    {
        return self::userRole() === 'patient';
    }

    /**
     * Verifica se a sessão expirou
     */
    public static function isSessionExpired(): bool
    {
        Session::start();
        $loginTime = $_SESSION['login_time'] ?? 0;
        $timeout = 3600; // 1 hora

        if (time() - $loginTime > $timeout) {
            self::logout();
            return true;
        }

        $_SESSION['login_time'] = time();
        return false;
    }

    /**
     * Redireciona para login se não autenticado
     */
    public static function requireLogin(): void
    {
        if (!self::isAuthenticated() || self::isSessionExpired()) {
            header('Location: /terapia/index.php?action=login');
            exit;
        }
    }

    /**
     * Redireciona para login se não é admin
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: /terapia/dashboard.php');
            exit;
        }
    }
}
