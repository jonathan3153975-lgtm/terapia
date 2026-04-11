<?php

namespace App\Controllers;

use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;
use Helpers\Utils;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): void
    {
        $this->view('auth/login', ['appUrl' => Config::get('APP_URL', '')]);
    }

    public function processLogin(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            if ($isAjax) {
                $this->error('Email e senha sao obrigatorios');
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

        if ((string) ($user['status'] ?? 'active') !== 'active') {
            if ($isAjax) {
                $this->error('Seu acesso está pendente de liberação pelo terapeuta.');
            }
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&error=1');
        }

        Auth::login($user);

        $appUrl = Config::get('APP_URL', '');
        $role = $user['role'];

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
}
