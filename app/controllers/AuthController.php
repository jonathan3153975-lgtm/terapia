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
        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->error('Email e senha sao obrigatorios');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !Utils::verifyPassword($password, $user['password'])) {
            $this->error('Credenciais invalidas');
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

        $this->success('Login realizado', ['redirect' => $redirect]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login');
    }
}
