<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\User;
use Helpers\Auth;
use Helpers\Session;
use Helpers\Utils;
use Helpers\Validator;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Exibe tela de login
     */
    public function login(): void
    {
        if (Auth::isAuthenticated()) {
            $this->redirect('/terapia/dashboard.php');
        }

        $flash = Session::getFlash();
        $this->view('auth/login', ['flash' => $flash]);
    }

    /**
     * Processa login
     */
    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->error('Email e senha são obrigatórios');
        }

        if (!Utils::isValidEmail($email)) {
            $this->error('Email inválido');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !Utils::verifyPassword($password, $user['password'])) {
            $this->error('Email ou senha incorretos');
        }

        if ($user['status'] === 'inactive') {
            $this->error('Usuário inativo');
        }

        Auth::login($user['id'], $user['name'], $user['role']);

        $this->success('Login realizado com sucesso', [
            'redirect' => $user['role'] === 'admin' ? '/terapia/dashboard.php' : '/terapia/patient-dashboard.php'
        ]);
    }

    /**
     * Realiza logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('success', 'Logout realizado com sucesso');
        $this->redirect('/terapia/index.php?action=login');
    }

    /**
     * Exibe tela de recuperação de senha
     */
    public function forgotPassword(): void
    {
        $this->view('auth/forgot-password');
    }

    /**
     * Processa recuperação de senha
     */
    public function processForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $email = Utils::sanitize($_POST['email'] ?? '');

        if (empty($email) || !Utils::isValidEmail($email)) {
            $this->error('Email inválido');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // Não revelamos se o email existe por segurança
            $this->success('Se o email existe, você receberá instruções de recuperação');
        }

        $token = Utils::generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires_at' => $expiresAt
        ]);

        // TODO: Enviar email com link de recuperação
        // $this->sendResetEmail($user['email'], $token);

        Session::setFlash('success', 'Email de recuperação enviado');
        $this->success('Email de recuperação enviado');
    }

    /**
     * Exibe formulário de redefinição de senha
     */
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Session::setFlash('error', 'Token inválido');
            $this->redirect('/terapia/index.php?action=login');
        }

        $user = $this->userModel->find("reset_token = ?", [$token]);

        if (empty($user)) {
            Session::setFlash('error', 'Token inválido ou expirado');
            $this->redirect('/terapia/index.php?action=login');
        }

        $user = $user[0] ?? null;

        if (!$user || strtotime($user['reset_token_expires_at']) < time()) {
            Session::setFlash('error', 'Token expirado');
            $this->redirect('/terapia/index.php?action=login');
        }

        $this->view('auth/reset-password', ['token' => $token]);
    }

    /**
     * Processa redefinição de senha
     */
    public function processResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirmPassword)) {
            $this->error('Todos os campos são obrigatórios');
        }

        if ($password !== $confirmPassword) {
            $this->error('As senhas não conferem');
        }

        $passwordErrors = Validator::validatePassword($password);
        if (!empty($passwordErrors)) {
            $this->error(implode('. ', $passwordErrors));
        }

        $user = $this->userModel->find("reset_token = ?", [$token]);

        if (empty($user)) {
            $this->error('Token inválido');
        }

        $user = $user[0] ?? null;

        if (strtotime($user['reset_token_expires_at']) < time()) {
            $this->error('Token expirado');
        }

        $hashedPassword = Utils::hashPassword($password);

        $this->userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires_at' => null
        ]);

        Session::setFlash('success', 'Senha redefinida com sucesso');
        $this->success('Senha redefinida com sucesso', ['redirect' => '/terapia/index.php?action=login']);
    }
}
