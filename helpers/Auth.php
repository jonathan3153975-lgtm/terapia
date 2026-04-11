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
        if (self::isPatientPreviewActive()) {
            return isset($_SESSION['preview_patient_id']) ? (int) $_SESSION['preview_patient_id'] : null;
        }
        return isset($_SESSION['patient_id']) ? (int) $_SESSION['patient_id'] : null;
    }

    public static function startPatientPreview(int $therapistId, int $patientId, string $patientName = ''): void
    {
        Session::start();
        $_SESSION['preview_therapist_id'] = $therapistId;
        $_SESSION['preview_patient_id'] = $patientId;
        $_SESSION['preview_patient_name'] = $patientName;
        $_SESSION['preview_started_at'] = time();
    }

    public static function stopPatientPreview(): void
    {
        Session::start();
        unset($_SESSION['preview_therapist_id'], $_SESSION['preview_patient_id'], $_SESSION['preview_patient_name'], $_SESSION['preview_started_at']);
    }

    public static function isPatientPreviewActive(): bool
    {
        Session::start();
        return (string) ($_SESSION['user_role'] ?? '') === 'therapist'
            && !empty($_SESSION['preview_patient_id'])
            && !empty($_SESSION['preview_therapist_id'])
            && (int) ($_SESSION['preview_therapist_id'] ?? 0) === (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function patientPreviewName(): ?string
    {
        Session::start();
        if (!self::isPatientPreviewActive()) {
            return null;
        }
        return (string) ($_SESSION['preview_patient_name'] ?? 'Paciente');
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
