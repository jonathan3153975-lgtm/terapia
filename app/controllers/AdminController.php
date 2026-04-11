<?php

namespace App\Controllers;

use App\Models\FileStorage;
use App\Models\Patient;
use App\Models\Plan;
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
    private Plan $planModel;

    public function __construct()
    {
        Auth::requireRoles(['super_admin']);
        $this->userModel = new User();
        $this->patientModel = new Patient();
        $this->fileModel = new FileStorage();
        $this->planModel = new Plan();
    }

    private function therapistLogoUploadBasePath(): string
    {
        $uploadBase = dirname(__DIR__, 2) . '/uploads/therapist-logos';
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0775, true);
        }

        return $uploadBase;
    }

    private function storeTherapistLogoFromRequest(string $fieldName = 'company_logo'): array
    {
        if (!isset($_FILES[$fieldName]) || (int) ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'name' => null,
                'path' => null,
                'uploaded' => false,
                'invalid' => false,
            ];
        }

        $tmpName = (string) ($_FILES[$fieldName]['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return [
                'name' => null,
                'path' => null,
                'uploaded' => false,
                'invalid' => true,
            ];
        }

        $originalName = trim((string) ($_FILES[$fieldName]['name'] ?? ''));
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return [
                'name' => null,
                'path' => null,
                'uploaded' => false,
                'invalid' => true,
            ];
        }

        $safeFile = uniqid('therapist_logo_', true) . '.' . $ext;
        $target = $this->therapistLogoUploadBasePath() . '/' . $safeFile;
        if (!@move_uploaded_file($tmpName, $target)) {
            return [
                'name' => null,
                'path' => null,
                'uploaded' => false,
                'invalid' => true,
            ];
        }

        return [
            'name' => $originalName,
            'path' => 'uploads/therapist-logos/' . $safeFile,
            'uploaded' => true,
            'invalid' => false,
        ];
    }

    private function deleteTherapistLogoIfExists(string $relativePath): void
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            return;
        }

        $absolute = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (is_file($absolute)) {
            @unlink($absolute);
        }
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

    public function patientPackages(): void
    {
        $this->view('admin/packages/index', [
            'appUrl' => Config::get('APP_URL', ''),
            'therapists' => $this->userModel->listTherapists(),
            'packages' => $this->planModel->listPatientPlansForAdmin(),
        ]);
    }

    public function storePatientPackage(): void
    {
        $name = Utils::sanitize($_POST['name'] ?? '');
        $description = trim((string) ($_POST['description_text'] ?? ''));
        $billingCycle = Utils::sanitize($_POST['billing_cycle'] ?? 'mensal');
        $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
        $therapistId = (int) ($_POST['therapist_id'] ?? 0);

        if ($name === '' || $therapistId <= 0 || $price <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=error&msg=' . urlencode('Preencha nome, terapeuta e valor do pacote.'));
        }

        if (!in_array($billingCycle, ['mensal', 'semestral', 'anual'], true)) {
            $billingCycle = 'mensal';
        }

        $therapist = $this->userModel->findTherapistById($therapistId);
        if (!$therapist) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=error&msg=' . urlencode('Terapeuta inválido para vincular o pacote.'));
        }

        $created = $this->planModel->insert([
            'target' => 'patient',
            'therapist_id' => $therapistId,
            'name' => $name,
            'description_text' => $description !== '' ? $description : null,
            'billing_cycle' => $billingCycle,
            'price' => number_format($price, 2, '.', ''),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$created) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=error&msg=' . urlencode('Falha ao cadastrar pacote.'));
        }

        $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=success&msg=' . urlencode('Pacote cadastrado com sucesso.'));
    }

    public function togglePatientPackageStatus(): void
    {
        $packageId = (int) ($_POST['id'] ?? 0);
        $package = $this->planModel->findPatientPlanById($packageId);

        if (!$package) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=error&msg=' . urlencode('Pacote não encontrado.'));
        }

        $nextStatus = (int) ($package['is_active'] ?? 1) === 1 ? 0 : 1;
        $updated = $this->planModel->updateById($packageId, [
            'is_active' => $nextStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=error&msg=' . urlencode('Não foi possível atualizar o status do pacote.'));
        }

        $msg = $nextStatus === 1 ? 'Pacote ativado com sucesso.' : 'Pacote desativado com sucesso.';
        $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patient-packages&status=success&msg=' . urlencode($msg));
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

        $logo = $this->storeTherapistLogoFromRequest('company_logo');
        if ($logo['invalid'] === true) {
            if ($isAjax) {
                $this->error('Logo invalido. Use jpg, png, webp, gif ou svg.');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Logo invalido. Use jpg, png, webp, gif ou svg.'));
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
            'company_logo_name' => $logo['name'],
            'company_logo_path' => $logo['path'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            if ($logo['uploaded'] === true) {
                $this->deleteTherapistLogoIfExists((string) ($logo['path'] ?? ''));
            }
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

        $logo = $this->storeTherapistLogoFromRequest('company_logo');
        if ($logo['invalid'] === true) {
            if ($isAjax) {
                $this->error('Logo inválido. Use jpg, png, webp, gif ou svg.');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Logo inválido. Use jpg, png, webp, gif ou svg.'));
        }

        $newLogoName = (string) ($therapist['company_logo_name'] ?? '');
        $newLogoPath = (string) ($therapist['company_logo_path'] ?? '');
        if ($logo['uploaded'] === true) {
            $newLogoName = (string) ($logo['name'] ?? '');
            $newLogoPath = (string) ($logo['path'] ?? '');
        }

        $updated = $this->userModel->updateById($therapistId, [
            'name' => $name,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'plan_type' => $planType,
            'status' => $status,
            'company_logo_name' => $newLogoName !== '' ? $newLogoName : null,
            'company_logo_path' => $newLogoPath !== '' ? $newLogoPath : null,
        ]);

        if (!$updated) {
            if ($logo['uploaded'] === true) {
                $this->deleteTherapistLogoIfExists((string) ($logo['path'] ?? ''));
            }
            if ($isAjax) {
                $this->error('Falha ao atualizar terapeuta');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Falha ao atualizar terapeuta.'));
        }

        if ($logo['uploaded'] === true) {
            $this->deleteTherapistLogoIfExists((string) ($therapist['company_logo_path'] ?? ''));
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
