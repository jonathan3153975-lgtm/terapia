<?php

namespace App\Controllers;

use App\Models\FileStorage;
use App\Models\Patient;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;

class AdminController extends Controller
{
    private User $userModel;
    private Patient $patientModel;
    private FileStorage $fileModel;

    public function __construct()
    {
        Auth::requireRoles(['super_admin']);
        $this->userModel = new User();
        $this->patientModel = new Patient();
        $this->fileModel = new FileStorage();
    }

    public function dashboard(): void
    {
        $this->view('admin/dashboard', [
            'appUrl' => Config::get('APP_URL', ''),
            'totalTherapists' => $this->userModel->countByRole('therapist'),
            'totalPatients' => $this->patientModel->count('1=1'),
            'activePatients' => $this->patientModel->count("status = 'active'"),
            'totalFiles' => $this->fileModel->count('1=1'),
            'usedBytes' => $this->fileModel->totalBytes(),
        ]);
    }

    public function therapists(): void
    {
        $this->view('admin/therapists/index', [
            'appUrl' => Config::get('APP_URL', ''),
            'therapists' => $this->userModel->listTherapists(),
        ]);
    }

    public function createTherapist(): void
    {
        $this->view('admin/therapists/create', ['appUrl' => Config::get('APP_URL', '')]);
    }

    public function storeTherapist(): void
    {
        $name = Utils::sanitize($_POST['name'] ?? '');
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $phone = Validator::onlyDigits($_POST['phone'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $cpf === '' || $phone === '' || $email === '' || $password === '') {
            $this->error('Preencha todos os campos obrigatorios');
        }

        if (!Validator::validateCPF($cpf)) {
            $this->error('CPF invalido');
        }

        if (!Utils::isValidEmail($email)) {
            $this->error('Email invalido');
        }

        if ($this->userModel->findByEmail($email)) {
            $this->error('Email ja cadastrado');
        }

        $inserted = $this->userModel->insert([
            'name' => $name,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'password' => Utils::hashPassword($password),
            'role' => 'therapist',
            'status' => 'active',
            'plan_type' => 'mensal',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            $this->error('Falha ao cadastrar terapeuta');
        }

        $this->success('Terapeuta cadastrado', ['redirect' => Config::get('APP_URL', '') . '/dashboard.php?action=therapists']);
    }
}
