<?php

namespace Helpers;

use Config\Config;

class Auth
{
    public static function login(array $user): void
    {
        Session::start();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['therapist_id'] = isset($user['therapist_id']) ? (int) $user['therapist_id'] : null;
        $_SESSION['patient_id'] = isset($user['patient_id']) ? (int) $user['patient_id'] : null;
        $_SESSION['logged_at'] = time();
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function isAuthenticated(): bool
    {
        Session::start();
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        Session::start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        Session::start();
        return $_SESSION['user_role'] ?? null;
    }

    public static function name(): ?string
    {
        Session::start();
        return $_SESSION['user_name'] ?? null;
    }

    public static function therapistId(): ?int
    {
        Session::start();
        if (self::role() === 'therapist') {
            return self::id();
        }
        return isset($_SESSION['therapist_id']) ? (int) $_SESSION['therapist_id'] : null;
    }

    public static function patientId(): ?int
    {
        Session::start();
        return isset($_SESSION['patient_id']) ? (int) $_SESSION['patient_id'] : null;
    }

    public static function requireRoles(array $roles): void
    {
        if (!self::isAuthenticated() || !in_array((string) self::role(), $roles, true)) {
            Config::loadEnv();
            header('Location: ' . Config::get('APP_URL', '') . '/index.php?action=login');
            exit;
        }
    }
}
