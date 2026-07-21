<?php

namespace App\Controllers;

use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;
use Helpers\EmailTemplate;
use Helpers\MailService;
use Helpers\Utils;

class AuthController extends Controller
{
    private User $userModel;
    public const DEFAULT_PUBLIC_SIGNUP_TOKEN = '3bc0590207b22f6bfa4f4e24cdaaca4b2687055be8303c09';

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): void
    {
        $this->view('auth/login', [
            'appUrl' => Config::get('APP_URL', ''),
            'signupUrl' => self::buildPublicSignupUrl(),
        ]);
    }

    public static function buildPublicSignupUrl(): string
    {
        $appUrl = rtrim((string) Config::get('APP_URL', ''), '/');
        if ($appUrl === '') {
            return 'https://jw-adminix.com.br/terapia/index.php?action=patient-signup&token=' . self::DEFAULT_PUBLIC_SIGNUP_TOKEN;
        }

        return $appUrl . '/index.php?action=patient-signup&token=' . urlencode(self::DEFAULT_PUBLIC_SIGNUP_TOKEN);
    }

    public function processLogin(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            if ($isAjax) {
                $this->error('Email e senha sao obrigatorios');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&error=1');
        }

        if (!Utils::isValidEmail($email)) {
            if ($isAjax) {
                $this->error('Informe um e-mail válido.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&error=1');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !Utils::verifyPassword($password, $user['password'])) {
            if ($isAjax) {
                $this->error('Credenciais invalidas');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&error=1');
        }

        $rawRole = strtolower(trim((string) ($user['role'] ?? '')));
        $role = match ($rawRole) {
            'super_admin', 'therapist', 'patient' => $rawRole,
            'admin', 'administrator' => 'super_admin',
            'terapeuta' => 'therapist',
            'paciente' => 'patient',
            default => '',
        };

        if ($role === '') {
            if ($isAjax) {
                $this->error('Seu usuário está com perfil inválido. Contate o suporte.');
            }
            $this->redirect(
                Config::get('APP_URL', '')
                . '/index.php?action=login&status=error&msg='
                . urlencode('Seu usuário está com perfil inválido. Contate o suporte.')
            );
        }

        if ($role !== 'patient' && (string) ($user['status'] ?? 'active') !== 'active') {
            if ($isAjax) {
                $this->error('Seu acesso está pendente de liberação pelo terapeuta.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&error=1');
        }

        // Preserve compatibility for legacy role values stored in the database.
        $user['role'] = $role;

        Auth::login($user);

        $appUrl = Config::get('APP_URL', '');

        $redirect = $appUrl . '/index.php?action=login';
        if ($role === 'super_admin') {
            $redirect = $appUrl . '/dashboard.php?action=admin-dashboard';
        } elseif ($role === 'therapist') {
            $redirect = $appUrl . '/dashboard.php?action=therapist-dashboard';
        } elseif ($role === 'patient') {
            $redirect = $appUrl . '/patient.php?action=dashboard';
        }

        if ($isAjax) {
            $this->success('Login realizado', ['redirect' => $redirect]);
        }

        $this->redirect($redirect);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login');
    }

    public function forgotPassword(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if ($email === '' || !Utils::isValidEmail($email)) {
            if ($isAjax) {
                $this->error('Informe um e-mail válido.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=error&msg=' . urlencode('Informe um e-mail válido.'));
        }

        $genericSuccess = 'Se o e-mail existir, você receberá uma nova senha em instantes.';
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            if ($isAjax) {
                $this->success($genericSuccess);
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=success&msg=' . urlencode($genericSuccess));
        }

        $newPassword = substr(bin2hex(random_bytes(8)), 0, 10);
        $updated = $this->userModel->updateById((int) $user['id'], [
            'password' => Utils::hashPassword($newPassword),
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Não foi possível redefinir a senha agora. Tente novamente.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=error&msg=' . urlencode('Não foi possível redefinir a senha agora.'));
        }

        $sent = false;
        try {
            $mail = new MailService();
            $sent = $mail->send(
                (string) ($user['email'] ?? ''),
                (string) ($user['name'] ?? 'Usuário'),
                'Redefinição de senha - Tera-Tech',
                EmailTemplate::passwordResetCredentials(
                    (string) ($user['name'] ?? 'Usuário'),
                    (string) ($user['email'] ?? $email),
                    $newPassword,
                    Config::get('APP_URL', '') . '/index.php?action=login'
                )
            );
        } catch (\Throwable $e) {
            error_log('[forgot-password] ' . $e->getMessage());
        }

        if (!$sent) {
            if ($isAjax) {
                $this->error('Senha redefinida, mas falhou o envio do e-mail. Contate o suporte.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=error&msg=' . urlencode('Senha redefinida, mas falhou o envio do e-mail.'));
        }

        if ($isAjax) {
            $this->success($genericSuccess);
        }
        $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=success&msg=' . urlencode($genericSuccess));
    }
}
