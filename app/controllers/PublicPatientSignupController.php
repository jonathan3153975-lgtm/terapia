<?php

namespace App\Controllers;

use App\Models\Patient;
use App\Models\PatientSignupLink;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\EmailTemplate;
use Helpers\MailService;
use Helpers\Utils;
use Helpers\Validator;

class PublicPatientSignupController extends Controller
{
    private PatientSignupLink $signupLinkModel;
    private Patient $patientModel;
    private User $userModel;

    public function __construct()
    {
        $this->signupLinkModel = new PatientSignupLink();
        $this->patientModel = new Patient();
        $this->userModel = new User();
    }

    public function showForm(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $link = $this->signupLinkModel->findActiveByToken($token);
        $appUrl = Config::get('APP_URL', '');
        $faviconUrl = '';

        if ($link) {
            $therapistId = (int) ($link['therapist_id'] ?? 0);
            if ($therapistId > 0) {
                $therapist = $this->userModel->findTherapistById($therapistId);
                $logoPath = trim((string) ($therapist['company_logo_path'] ?? ''));
                if ($logoPath !== '') {
                    $faviconUrl = rtrim($appUrl, '/') . '/' . ltrim($logoPath, '/');
                }
            }
        }

        $this->view('public/patient-signup', [
            'appUrl' => $appUrl,
            'token' => $token,
            'linkData' => $link,
            'faviconUrl' => $faviconUrl,
        ]);
    }

    private function boolPost(string $key): int
    {
        return isset($_POST[$key]) ? 1 : 0;
    }

    private function buildMedicalTreatmentText(): string
    {
        if (!$this->boolPost('has_medical_treatment')) {
            return '';
        }

        $description = Utils::sanitize($_POST['medical_treatment_description'] ?? '');
        $treatmentMedication = Utils::sanitize($_POST['medical_treatment_medication'] ?? '');
        $depressionMedication = Utils::sanitize($_POST['depression_medication'] ?? '');
        $anxietyMedication = Utils::sanitize($_POST['anxiety_medication'] ?? '');

        $parts = [];
        if ($description !== '') {
            $parts[] = 'Tratamento médico: ' . $description;
        }
        if ($treatmentMedication !== '') {
            $parts[] = 'Medicação tratamento: ' . $treatmentMedication;
        }
        if ($depressionMedication !== '') {
            $parts[] = 'Medicação depressão: ' . $depressionMedication;
        }
        if ($anxietyMedication !== '') {
            $parts[] = 'Medicação ansiedade: ' . $anxietyMedication;
        }

        return implode("\n", $parts);
    }

    public function submitForm(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        $link = $this->signupLinkModel->findActiveByToken($token);
        if (!$link) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Link inválido ou expirado.'));
        }

        $therapistId = (int) ($link['therapist_id'] ?? 0);
        $name = Utils::sanitize($_POST['name'] ?? '');
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $phone = Validator::onlyDigits($_POST['phone'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');
        if ($name === '' || $cpf === '' || $phone === '' || $email === '') {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Preencha todos os campos obrigatórios.'));
        }

        if (!Validator::validateCPF($cpf) || !Utils::isValidEmail($email)) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Dados inválidos. Verifique CPF e e-mail.'));
        }

        if ($this->userModel->findByEmail($email)) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Este e-mail já possui cadastro no sistema.'));
        }

        $patientId = $this->patientModel->insert([
            'therapist_id' => $therapistId,
            'name' => $name,
            'cpf' => $cpf,
            'birth_date' => null,
            'phone' => $phone,
            'email' => $email,
            'marital_status' => '',
            'children' => '',
            'father' => '',
            'mother' => '',
            'first_word' => '',
            'cep' => '',
            'address' => '',
            'neighborhood' => '',
            'city' => '',
            'state' => '',
            'depression' => 0,
            'anxiety' => 0,
            'medical_treatment' => '',
            'comorbidities_json' => null,
            'addictions_json' => null,
            'had_therapy' => 0,
            'therapy_description' => '',
            'treatment_start_date' => null,
            'menstruation' => '',
            'bowel' => '',
            'main_complaint' => '',
            'review_status' => 'pending_review',
            'approved_at' => null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$patientId) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Falha ao salvar cadastro.'));
        }

        $rawPassword = substr(bin2hex(random_bytes(8)), 0, 10);
        $userId = $this->userModel->insert([
            'name' => $name,
            'cpf' => $cpf,
            'phone' => $phone,
            'email' => $email,
            'password' => Utils::hashPassword($rawPassword),
            'role' => 'patient',
            'therapist_id' => $therapistId,
            'patient_id' => (int) $patientId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$userId) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Falha ao criar acesso do paciente.'));
        }

        $this->signupLinkModel->incrementUsage((int) ($link['id'] ?? 0));

        $sent = false;
        try {
            $mail = new MailService();
            $sent = $mail->send(
                $email,
                $name,
                'Acesso ao sistema de terapia',
                EmailTemplate::patientAccessCredentials(
                    $name,
                    $email,
                    $rawPassword,
                    Config::get('APP_URL', '') . '/index.php?action=login'
                )
            );
        } catch (\Throwable $e) {
            error_log('[public-signup-email] ' . $e->getMessage());
        }

        $status = $sent ? 'success' : 'error';
        $message = $sent
            ? 'Cadastro concluído com sucesso. Enviamos sua senha por e-mail.'
            : 'Cadastro concluído, mas não foi possível enviar o e-mail com a senha. Solicite o reenvio ao terapeuta.';

        $this->redirect(Config::get('APP_URL', '') . '/index.php?action=login&status=' . $status . '&msg=' . urlencode($message));
    }

    public function successPage(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $link = $this->signupLinkModel->findActiveByToken($token);
        $appUrl = Config::get('APP_URL', '');
        $faviconUrl = '';

        if ($link) {
            $therapistId = (int) ($link['therapist_id'] ?? 0);
            if ($therapistId > 0) {
                $therapist = $this->userModel->findTherapistById($therapistId);
                $logoPath = trim((string) ($therapist['company_logo_path'] ?? ''));
                if ($logoPath !== '') {
                    $faviconUrl = rtrim($appUrl, '/') . '/' . ltrim($logoPath, '/');
                }
            }
        }

        $this->view('public/patient-signup-success', [
            'appUrl' => $appUrl,
            'faviconUrl' => $faviconUrl,
        ]);
    }
}
