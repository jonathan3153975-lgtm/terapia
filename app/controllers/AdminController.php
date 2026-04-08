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
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $name = Utils::sanitize($_POST['name'] ?? '');
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $phone = Validator::onlyDigits($_POST['phone'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists';
        $redirectCreateBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists-create';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($name === '' || $cpf === '' || $phone === '' || $email === '' || $password === '') {
            if ($isAjax) {
                $this->error('Preencha todos os campos obrigatorios');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Preencha todos os campos obrigatorios.'));
        }

        if (!Validator::validateCPF($cpf)) {
            if ($isAjax) {
                $this->error('CPF invalido');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'CPF invalido.'));
        }

        if (!Utils::isValidEmail($email)) {
            if ($isAjax) {
                $this->error('Email invalido');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Email invalido.'));
        }

        if ($this->userModel->findByEmail($email)) {
            if ($isAjax) {
                $this->error('Email ja cadastrado');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Email ja cadastrado.'));
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
            if ($isAjax) {
                $this->error('Falha ao cadastrar terapeuta');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Falha ao cadastrar terapeuta.'));
        }

        if ($isAjax) {
            $this->success('Terapeuta cadastrado', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Terapeuta cadastrado com sucesso.'));
    }
}
