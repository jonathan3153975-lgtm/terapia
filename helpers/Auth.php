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
    public static function login(int $userId, string $userName, string $userRole, ?int $therapistId = null, ?int $patientId = null): void
    {
        Session::start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['therapist_id'] = $therapistId;
        $_SESSION['patient_id'] = $patientId;
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
     * Obtém patient_id vinculado ao login de paciente
     */
    public static function patientId(): ?int
    {
        Session::start();
        return isset($_SESSION['patient_id']) ? (int) $_SESSION['patient_id'] : null;
    }

    /**
     * Verifica se é admin
     */
    public static function isAdmin(): bool
    {
        return in_array(self::userRole(), ['admin', 'super_admin'], true);
    }

    /**
     * Verifica se é terapeuta
     */
    public static function isTherapist(): bool
    {
        return self::userRole() === 'therapist';
    }

    /**
     * Verifica se é paciente
     */
    public static function isPatient(): bool
    {
        return self::userRole() === 'patient';
    }

    /**
     * Retorna o therapist_id vinculado na sessão
     */
    public static function sessionTherapistId(): ?int
    {
        Session::start();
        if (!empty($_SESSION['therapist_id'])) {
            return (int) $_SESSION['therapist_id'];
        }
        return null;
    }

    /**
     * Retorna o ID do terapeuta da sessão (o próprio usuário quando role=therapist)
     */
    public static function therapistId(): ?int
    {
        if (self::isTherapist()) {
            return self::userId();
        }

        return self::sessionTherapistId();
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
            header('Location: ' . \Config\Config::APP_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Redireciona para login se não é admin
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin() && !self::isTherapist()) {
            header('Location: ' . \Config\Config::APP_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Exige usuário terapeuta
     */
    public static function requireTherapist(): void
    {
        self::requireLogin();
        if (!self::isTherapist()) {
            header('Location: ' . \Config\Config::APP_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Exige usuário super admin
     */
    public static function requireSuperAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . \Config\Config::APP_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Exige usuário paciente
     */
    public static function requirePatient(): void
    {
        self::requireLogin();
        if (!self::isPatient()) {
            header('Location: ' . \Config\Config::APP_URL . '/index.php?action=login');
            exit;
        }
    }
}
