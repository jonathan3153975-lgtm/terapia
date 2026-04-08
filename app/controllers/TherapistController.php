<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\FileStorage;
use App\Models\Patient;
use App\Models\Task;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;

class TherapistController extends Controller
{
    private Patient $patientModel;
    private Appointment $appointmentModel;
    private Task $taskModel;
    private FileStorage $fileModel;

    public function __construct()
    {
        Auth::requireRoles(['therapist']);
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->taskModel = new Task();
        $this->fileModel = new FileStorage();
    }

    public function dashboard(): void
    {
        $therapistId = (int) Auth::id();

        $this->view('therapist/dashboard', [
            'appUrl' => Config::get('APP_URL', ''),
            'totalPatients' => $this->patientModel->countByTherapist($therapistId),
            'totalAppointments' => $this->appointmentModel->countByTherapist($therapistId),
            'totalTasks' => $this->taskModel->countByTherapist($therapistId),
            'totalMessages' => 0,
            'totalFiles' => $this->fileModel->countByTherapist($therapistId),
        ]);
    }

    public function patients(): void
    {
        $term = Utils::sanitize($_GET['search'] ?? '');
        $therapistId = (int) Auth::id();
        $patients = $this->patientModel->searchByTherapist($therapistId, $term);

        $this->view('therapist/patients/index', [
            'appUrl' => Config::get('APP_URL', ''),
            'patients' => $patients,
            'search' => $term,
        ]);
    }

    public function createPatient(): void
    {
        $this->view('therapist/patients/create', ['appUrl' => Config::get('APP_URL', '')]);
    }

    private function boolPost(string $key): int
    {
        return isset($_POST[$key]) ? 1 : 0;
    }

    private function buildMedicalTreatmentText(): string
    {
        $description = Utils::sanitize($_POST['medical_treatment_description'] ?? '');
        $treatmentMedication = Utils::sanitize($_POST['medical_treatment_medication'] ?? '');
        $depressionMedication = Utils::sanitize($_POST['depression_medication'] ?? '');
        $anxietyMedication = Utils::sanitize($_POST['anxiety_medication'] ?? '');

        $parts = [];
        if ($description !== '') {
            $parts[] = 'Tratamento medico: ' . $description;
        }
        if ($treatmentMedication !== '') {
            $parts[] = 'Medicacao tratamento: ' . $treatmentMedication;
        }
        if ($depressionMedication !== '') {
            $parts[] = 'Medicacao depressao: ' . $depressionMedication;
        }
        if ($anxietyMedication !== '') {
            $parts[] = 'Medicacao ansiedade: ' . $anxietyMedication;
        }

        return implode("\n", $parts);
    }

    private function extractMedicalLine(string $medicalTreatment, string $prefix): string
    {
        foreach (preg_split('/\r\n|\r|\n/', $medicalTreatment) as $line) {
            if (str_starts_with($line, $prefix)) {
                return trim((string) substr($line, strlen($prefix)));
            }
        }
        return '';
    }

    public function storePatient(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
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
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectCreateBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-create';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($name === '' || $cpf === '' || $phone === '') {
            if ($isAjax) {
                $this->error('Nome, CPF e telefone sao obrigatorios');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Nome, CPF e telefone sao obrigatorios.'));
        }

        if (!Validator::validateCPF($cpf)) {
            if ($isAjax) {
                $this->error('CPF invalido');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'CPF invalido.'));
        }

        $inserted = $this->patientModel->insert([
            'therapist_id' => $therapistId,
            'name' => $name,
            'cpf' => $cpf,
            'birth_date' => $_POST['birth_date'] ?? null,
            'phone' => $phone,
            'email' => $email,
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'cep' => Validator::onlyDigits($_POST['cep'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'depression' => $this->boolPost('depression'),
            'anxiety' => $this->boolPost('anxiety'),
            'medical_treatment' => $medicalTreatment,
            'alcoholism' => $this->boolPost('alcoholism'),
            'drugs' => $this->boolPost('drugs'),
            'convulsions' => $this->boolPost('convulsions'),
            'smoker' => $this->boolPost('smoker'),
            'hepatitis' => $this->boolPost('hepatitis'),
            'hypertension' => $this->boolPost('hypertension'),
            'diabetes' => $this->boolPost('diabetes'),
            'addictions_json' => empty($addictions) ? null : json_encode($addictions, JSON_UNESCAPED_UNICODE),
            'had_therapy' => $this->boolPost('had_therapy'),
            'therapy_description' => Utils::sanitize($_POST['therapy_description'] ?? ''),
            'treatment_start_date' => $_POST['treatment_start_date'] ?? null,
            'menstruation' => Utils::sanitize($_POST['menstruation'] ?? ''),
            'bowel' => Utils::sanitize($_POST['bowel'] ?? ''),
            'main_complaint' => Utils::sanitize($_POST['main_complaint'] ?? ''),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            if ($isAjax) {
                $this->error('Falha ao cadastrar paciente');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Falha ao cadastrar paciente.'));
        }

        if ($isAjax) {
            $this->success('Paciente cadastrado', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Paciente cadastrado com sucesso.'));
    }

    public function showPatient(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $this->view('therapist/patients/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
        ]);
    }

    public function editPatient(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $addictions = [];
        if (!empty($patient['addictions_json'])) {
            $decoded = json_decode((string) $patient['addictions_json'], true);
            if (is_array($decoded)) {
                $addictions = $decoded;
            }
        }

        $medicalTreatment = (string) ($patient['medical_treatment'] ?? '');
        $patient['depression_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicacao depressao:');
        $patient['anxiety_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicacao ansiedade:');
        $patient['medical_treatment_description'] = $this->extractMedicalLine($medicalTreatment, 'Tratamento medico:');
        $patient['medical_treatment_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicacao tratamento:');

        $this->view('therapist/patients/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'addictions' => $addictions,
        ]);
    }

    public function updatePatient(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $medicalTreatment = $this->buildMedicalTreatmentText();
        $addictions = $_POST['addictions'] ?? [];
        if (!is_array($addictions)) {
            $addictions = [];
        }
        $addictions = array_values(array_map(static fn ($item) => Utils::sanitize((string) $item), $addictions));
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectEditBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-edit&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente nao encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Paciente nao encontrado.'));
        }

        $updated = $this->patientModel->updateById($patientId, [
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? null,
            'phone' => Validator::onlyDigits($_POST['phone'] ?? ''),
            'email' => Utils::sanitize($_POST['email'] ?? ''),
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'cep' => Validator::onlyDigits($_POST['cep'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'depression' => $this->boolPost('depression'),
            'anxiety' => $this->boolPost('anxiety'),
            'medical_treatment' => $medicalTreatment,
            'alcoholism' => $this->boolPost('alcoholism'),
            'drugs' => $this->boolPost('drugs'),
            'convulsions' => $this->boolPost('convulsions'),
            'smoker' => $this->boolPost('smoker'),
            'hepatitis' => $this->boolPost('hepatitis'),
            'hypertension' => $this->boolPost('hypertension'),
            'diabetes' => $this->boolPost('diabetes'),
            'addictions_json' => empty($addictions) ? null : json_encode($addictions, JSON_UNESCAPED_UNICODE),
            'had_therapy' => $this->boolPost('had_therapy'),
            'therapy_description' => Utils::sanitize($_POST['therapy_description'] ?? ''),
            'treatment_start_date' => $_POST['treatment_start_date'] ?? null,
            'menstruation' => Utils::sanitize($_POST['menstruation'] ?? ''),
            'bowel' => Utils::sanitize($_POST['bowel'] ?? ''),
            'main_complaint' => Utils::sanitize($_POST['main_complaint'] ?? ''),
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao atualizar paciente');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Falha ao atualizar paciente.'));
        }

        if ($isAjax) {
            $this->success('Paciente atualizado', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Paciente atualizado com sucesso.'));
    }

    public function deletePatient(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente nao encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Paciente nao encontrado.'));
        }

        $deleted = (bool) $this->patientModel->query('DELETE FROM patients WHERE id = ? AND therapist_id = ?', [$patientId, $therapistId]);
        if (!$deleted) {
            if ($isAjax) {
                $this->error('Falha ao excluir paciente');
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Falha ao excluir paciente.'));
        }

        if ($isAjax) {
            $this->success('Paciente excluido', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Paciente excluido com sucesso.'));
    }

    public function historyPatient(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $appointments = $this->appointmentModel->listByPatient($patientId);
        $tasks = $this->taskModel->listByPatient($patientId);

        $this->view('therapist/patients/history', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'appointments' => $appointments,
            'tasks' => $tasks,
        ]);
    }
}
