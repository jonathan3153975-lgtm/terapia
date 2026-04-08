<?php

namespace App\Controllers;

use App\Models\User;
use Classes\Controller;
use Helpers\Auth;
use Helpers\Utils;

class TherapistController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        Auth::requireSuperAdmin();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $therapists = $this->userModel->listTherapists();
        $this->view('admin/super/therapists/index', ['therapists' => $therapists]);
    }

    public function create(): void
    {
        $this->view('admin/super/therapists/create');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Metodo nao permitido', 405);
        }

        $name = Utils::sanitize($_POST['name'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $this->error('Nome, email e senha sao obrigatorios');
        }

        if (!Utils::isValidEmail($email)) {
            $this->error('Email invalido');
        }

        if ($this->userModel->findByEmail($email)) {
            $this->error('Email ja cadastrado');
        }

        $id = $this->userModel->createUser([
            'name' => $name,
            'email' => $email,
            'password' => Utils::hashPassword($password),
            'role' => 'therapist',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$id) {
            $this->error('Nao foi possivel cadastrar o terapeuta');
        }

        $this->success('Terapeuta cadastrado com sucesso', [
            'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=therapists',
        ]);
    }
}
