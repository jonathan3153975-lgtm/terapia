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

        $this->view('public/patient-signup', [
            'appUrl' => Config::get('APP_URL', ''),
            'token' => $token,
            'linkData' => $link,
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
        $medicalTreatment = $this->buildMedicalTreatmentText();
        $addictions = $_POST['addictions'] ?? [];
        if (!is_array($addictions)) {
            $addictions = [];
        }
        $addictions = array_values(array_map(static fn ($item) => Utils::sanitize((string) $item), $addictions));
        $comorbidities = $_POST['comorbidities'] ?? [];
        if (!is_array($comorbidities)) {
            $comorbidities = [];
        }
        $comorbidities = array_values(array_map(static fn ($item) => Utils::sanitize((string) $item), $comorbidities));

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
            'birth_date' => trim((string) ($_POST['birth_date'] ?? '')) !== '' ? $_POST['birth_date'] : null,
            'phone' => $phone,
            'email' => $email,
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'father' => Utils::sanitize($_POST['father'] ?? ''),
            'mother' => Utils::sanitize($_POST['mother'] ?? ''),
            'first_word' => Utils::sanitize($_POST['first_word'] ?? ''),
            'cep' => Validator::onlyDigits($_POST['cep'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'depression' => $this->boolPost('depression'),
            'anxiety' => $this->boolPost('anxiety'),
            'medical_treatment' => $medicalTreatment,
            'comorbidities_json' => empty($comorbidities) ? null : json_encode($comorbidities, JSON_UNESCAPED_UNICODE),
            'addictions_json' => empty($addictions) ? null : json_encode($addictions, JSON_UNESCAPED_UNICODE),
            'had_therapy' => $this->boolPost('had_therapy'),
            'therapy_description' => Utils::sanitize($_POST['therapy_description'] ?? ''),
            'treatment_start_date' => trim((string) ($_POST['treatment_start_date'] ?? '')) !== '' ? $_POST['treatment_start_date'] : null,
            'menstruation' => Utils::sanitize($_POST['menstruation'] ?? ''),
            'bowel' => Utils::sanitize($_POST['bowel'] ?? ''),
            'main_complaint' => Utils::sanitize($_POST['main_complaint'] ?? ''),
            'review_status' => 'pending_review',
            'approved_at' => null,
            'status' => 'inactive',
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
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$userId) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup&token=' . urlencode($token) . '&status=error&msg=' . urlencode('Falha ao criar acesso do paciente.'));
        }

        $this->signupLinkModel->incrementUsage((int) ($link['id'] ?? 0));

        try {
            $mail = new MailService();
            $mail->send(
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

        $this->redirect(Config::get('APP_URL', '') . '/index.php?action=patient-signup-success&status=success&msg=' . urlencode('Cadastro enviado com sucesso. Seu terapeuta irá revisar os dados para liberar o acesso.'));
    }

    public function successPage(): void
    {
        $this->view('public/patient-signup-success', [
            'appUrl' => Config::get('APP_URL', ''),
        ]);
    }
}
