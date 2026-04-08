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

    public function showTherapist(): void
    {
        $therapistId = (int) ($_GET['id'] ?? 0);
        $therapist = $this->userModel->findTherapistById($therapistId);

        if (!$therapist) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapists&status=error&msg=' . urlencode('Terapeuta não encontrado.'));
        }

        $this->view('admin/therapists/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'therapist' => $therapist,
        ]);
    }

    public function editTherapist(): void
    {
        $therapistId = (int) ($_GET['id'] ?? 0);
        $therapist = $this->userModel->findTherapistById($therapistId);

        if (!$therapist) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapists&status=error&msg=' . urlencode('Terapeuta não encontrado.'));
        }

        $this->view('admin/therapists/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'therapist' => $therapist,
        ]);
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

    public function updateTherapist(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) ($_POST['id'] ?? 0);
        $therapist = $this->userModel->findTherapistById($therapistId);

        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists';
        $redirectEditBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists-edit&id=' . $therapistId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$therapist) {
            if ($isAjax) {
                $this->error('Terapeuta não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Terapeuta não encontrado.'));
        }

        $name = Utils::sanitize($_POST['name'] ?? '');
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $phone = Validator::onlyDigits($_POST['phone'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');
        $planType = Utils::sanitize($_POST['plan_type'] ?? 'mensal');
        $status = Utils::sanitize($_POST['status'] ?? 'active');

        if ($name === '' || $cpf === '' || $phone === '' || $email === '') {
            if ($isAjax) {
                $this->error('Preencha todos os campos obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Preencha todos os campos obrigatórios.'));
        }

        if (!Validator::validateCPF($cpf)) {
            if ($isAjax) {
                $this->error('CPF inválido');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'CPF inválido.'));
        }

        if (!Utils::isValidEmail($email)) {
            if ($isAjax) {
                $this->error('E-mail inválido');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'E-mail inválido.'));
        }

        $existing = $this->userModel->findByEmail($email);
        if ($existing && (int) $existing['id'] !== $therapistId) {
            if ($isAjax) {
                $this->error('E-mail já cadastrado');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'E-mail já cadastrado.'));
        }

        if (!in_array($planType, ['mensal', 'anual'], true)) {
            $planType = 'mensal';
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $updated = $this->userModel->updateById($therapistId, [
            'name' => $name,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'plan_type' => $planType,
            'status' => $status,
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao atualizar terapeuta');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Falha ao atualizar terapeuta.'));
        }

        if ($isAjax) {
            $this->success('Terapeuta atualizado', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Terapeuta atualizado com sucesso.'));
    }

    public function passwordTherapist(): void
    {
        $therapistId = (int) ($_GET['id'] ?? 0);
        $therapist = $this->userModel->findTherapistById($therapistId);

        if (!$therapist) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapists&status=error&msg=' . urlencode('Terapeuta não encontrado.'));
        }

        $this->view('admin/therapists/password', [
            'appUrl' => Config::get('APP_URL', ''),
            'therapist' => $therapist,
        ]);
    }

    public function updatePasswordTherapist(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) ($_POST['id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        $therapist = $this->userModel->findTherapistById($therapistId);

        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists';
        $redirectPasswordBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists-password&id=' . $therapistId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$therapist) {
            if ($isAjax) {
                $this->error('Terapeuta não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Terapeuta não encontrado.'));
        }

        if (trim($password) === '') {
            if ($isAjax) {
                $this->error('Informe uma senha válida');
            }
            $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Informe uma senha válida.'));
        }

        $updated = $this->userModel->updateById($therapistId, [
            'password' => Utils::hashPassword($password),
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao alterar senha');
            }
            $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Falha ao alterar senha.'));
        }

        if ($isAjax) {
            $this->success('Senha alterada', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Senha alterada com sucesso.'));
    }

    public function deleteTherapist(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $therapist = $this->userModel->findTherapistById($therapistId);
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapists';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$therapist) {
            if ($isAjax) {
                $this->error('Terapeuta não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Terapeuta não encontrado.'));
        }

        $deleted = $this->userModel->deleteTherapistById($therapistId);

        if (!$deleted) {
            if ($isAjax) {
                $this->error('Falha ao excluir terapeuta');
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Falha ao excluir terapeuta.'));
        }

        if ($isAjax) {
            $this->success('Terapeuta excluído', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Terapeuta excluído com sucesso.'));
    }
}
