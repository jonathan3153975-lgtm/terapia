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
        $comorbidities = $_POST['comorbidities'] ?? [];
        if (!is_array($comorbidities)) {
            $comorbidities = [];
        }
        $comorbidities = array_values(array_map(static fn ($item) => Utils::sanitize((string) $item), $comorbidities));
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectCreateBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-create';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($name === '' || $cpf === '' || $phone === '') {
            if ($isAjax) {
                $this->error('Nome, CPF e telefone são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'Nome, CPF e telefone são obrigatórios.'));
        }

        if (!Validator::validateCPF($cpf)) {
            if ($isAjax) {
                $this->error('CPF inválido');
            }
            $this->redirect($redirectWithStatus($redirectCreateBase, 'error', 'CPF inválido.'));
        }

        $inserted = $this->patientModel->insert([
            'therapist_id' => $therapistId,
            'name' => $name,
            'cpf' => $cpf,
            'birth_date' => trim((string) ($_POST['birth_date'] ?? '')) !== '' ? $_POST['birth_date'] : null,
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
            'comorbidities_json' => empty($comorbidities) ? null : json_encode($comorbidities, JSON_UNESCAPED_UNICODE),
            'addictions_json' => empty($addictions) ? null : json_encode($addictions, JSON_UNESCAPED_UNICODE),
            'had_therapy' => $this->boolPost('had_therapy'),
            'therapy_description' => Utils::sanitize($_POST['therapy_description'] ?? ''),
            'treatment_start_date' => trim((string) ($_POST['treatment_start_date'] ?? '')) !== '' ? $_POST['treatment_start_date'] : null,
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

        $comorbidities = [];
        if (!empty($patient['comorbidities_json'])) {
            $decoded = json_decode((string) $patient['comorbidities_json'], true);
            if (is_array($decoded)) {
                $comorbidities = $decoded;
            }
        }

        $medicalTreatment = (string) ($patient['medical_treatment'] ?? '');
        $patient['depression_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicação depressão:');
        $patient['anxiety_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicação ansiedade:');
        $patient['medical_treatment_description'] = $this->extractMedicalLine($medicalTreatment, 'Tratamento médico:');
        $patient['medical_treatment_medication'] = $this->extractMedicalLine($medicalTreatment, 'Medicação tratamento:');
        $patient['has_medical_treatment'] = ($patient['medical_treatment_description'] !== '' || $patient['medical_treatment_medication'] !== '' || $patient['depression_medication'] !== '' || $patient['anxiety_medication'] !== '');

        $this->view('therapist/patients/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'addictions' => $addictions,
            'comorbidities' => $comorbidities,
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
        $comorbidities = $_POST['comorbidities'] ?? [];
        if (!is_array($comorbidities)) {
            $comorbidities = [];
        }
        $comorbidities = array_values(array_map(static fn ($item) => Utils::sanitize((string) $item), $comorbidities));
        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectEditBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-edit&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Paciente não encontrado.'));
        }

        $updated = $this->patientModel->updateById($patientId, [
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'birth_date' => trim((string) ($_POST['birth_date'] ?? '')) !== '' ? $_POST['birth_date'] : null,
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
            'comorbidities_json' => empty($comorbidities) ? null : json_encode($comorbidities, JSON_UNESCAPED_UNICODE),
            'addictions_json' => empty($addictions) ? null : json_encode($addictions, JSON_UNESCAPED_UNICODE),
            'had_therapy' => $this->boolPost('had_therapy'),
            'therapy_description' => Utils::sanitize($_POST['therapy_description'] ?? ''),
            'treatment_start_date' => trim((string) ($_POST['treatment_start_date'] ?? '')) !== '' ? $_POST['treatment_start_date'] : null,
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
                $this->error('Paciente não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Paciente não encontrado.'));
        }

        $deleted = (bool) $this->patientModel->query('DELETE FROM patients WHERE id = ? AND therapist_id = ?', [$patientId, $therapistId]);
        if (!$deleted) {
            if ($isAjax) {
                $this->error('Falha ao excluir paciente');
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Falha ao excluir paciente.'));
        }

        if ($isAjax) {
            $this->success('Paciente excluído', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Paciente excluído com sucesso.'));
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

    private function sanitizeRichText(string $html): string
    {
        $value = trim($html);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/<\s*(script|style)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $value) ?? '';
        $value = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $value) ?? '';
        $value = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $value) ?? '';
        $value = preg_replace('/\s(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '', $value) ?? '';
        $value = preg_replace('/\s(href|src)\s*=\s*\'\s*javascript:[^\']*\'/i', '', $value) ?? '';

        return strip_tags($value, '<p><br><strong><b><em><i><u><s><ol><ul><li><blockquote><pre><code><h1><h2><h3><a><span>');
    }

    public function storePatientAppointment(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);

        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente não encontrado', 404);
            }
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $sessionDate = trim((string) ($_POST['session_date'] ?? ''));
        $description = Utils::sanitize($_POST['description'] ?? '');
        $history = $this->sanitizeRichText((string) ($_POST['history'] ?? ''));

        if ($sessionDate === '' || $history === '') {
            if ($isAjax) {
                $this->error('Data e histórico são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Data e histórico são obrigatórios.'));
        }

        if ($description === '') {
            $description = Utils::sanitize(substr(trim(strip_tags($history)), 0, 120));
        }

        $inserted = $this->appointmentModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'session_date' => $sessionDate,
            'description' => $description,
            'history' => $history,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            if ($isAjax) {
                $this->error('Falha ao cadastrar atendimento');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Falha ao cadastrar atendimento.'));
        }

        if ($isAjax) {
            $this->success('Atendimento cadastrado', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Atendimento cadastrado com sucesso.'));
    }

    public function showPatientAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        $appointmentId = (int) ($_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $appointment = $this->appointmentModel->findByTherapistPatientAndId($therapistId, $patientId, $appointmentId);
        if (!$appointment) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId . '&status=error&msg=' . urlencode('Atendimento não encontrado.'));
        }

        $this->view('therapist/patients/appointments/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'appointment' => $appointment,
        ]);
    }

    public function editPatientAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        $appointmentId = (int) ($_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $appointment = $this->appointmentModel->findByTherapistPatientAndId($therapistId, $patientId, $appointmentId);
        if (!$appointment) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId . '&status=error&msg=' . urlencode('Atendimento não encontrado.'));
        }

        $this->view('therapist/patients/appointments/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'appointment' => $appointment,
        ]);
    }

    public function updatePatientAppointment(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $appointmentId = (int) ($_POST['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $appointment = $this->appointmentModel->findByTherapistPatientAndId($therapistId, $patientId, $appointmentId);

        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectEditBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-appointments-edit&patient_id=' . $patientId . '&id=' . $appointmentId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient || !$appointment) {
            if ($isAjax) {
                $this->error('Atendimento não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Atendimento não encontrado.'));
        }

        $sessionDate = trim((string) ($_POST['session_date'] ?? ''));
        $description = Utils::sanitize($_POST['description'] ?? '');
        $history = $this->sanitizeRichText((string) ($_POST['history'] ?? ''));

        if ($sessionDate === '' || $history === '') {
            if ($isAjax) {
                $this->error('Data e histórico são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Data e histórico são obrigatórios.'));
        }

        if ($description === '') {
            $description = Utils::sanitize(substr(trim(strip_tags($history)), 0, 120));
        }

        $updated = $this->appointmentModel->updateById($appointmentId, [
            'session_date' => $sessionDate,
            'description' => $description,
            'history' => $history,
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao atualizar atendimento');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Falha ao atualizar atendimento.'));
        }

        if ($isAjax) {
            $this->success('Atendimento atualizado', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Atendimento atualizado com sucesso.'));
    }

    public function deletePatientAppointment(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? $_GET['patient_id'] ?? 0);
        $appointmentId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $appointment = $this->appointmentModel->findByTherapistPatientAndId($therapistId, $patientId, $appointmentId);
        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient || !$appointment) {
            if ($isAjax) {
                $this->error('Atendimento não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Atendimento não encontrado.'));
        }

        $deleted = $this->appointmentModel->deleteByTherapistPatientAndId($therapistId, $patientId, $appointmentId);
        if (!$deleted) {
            if ($isAjax) {
                $this->error('Falha ao excluir atendimento');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Falha ao excluir atendimento.'));
        }

        if ($isAjax) {
            $this->success('Atendimento excluído', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Atendimento excluído com sucesso.'));
    }

    private function storeTaskAttachments(int $therapistId, int $patientId, int $taskId): void
    {
        $uploadBase = dirname(__DIR__, 2) . '/uploads/tasks';
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0775, true);
        }

        $link = filter_var(trim((string) ($_POST['attachment_link'] ?? '')), FILTER_SANITIZE_URL);
        if ($link !== '' && filter_var($link, FILTER_VALIDATE_URL)) {
            $this->fileModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'task_id' => $taskId,
                'file_name' => $link,
                'file_path' => $link,
                'file_type' => 'link',
                'file_size' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!isset($_FILES['task_attachments'])) {
            return;
        }

        $names = (array) ($_FILES['task_attachments']['name'] ?? []);
        $tmpNames = (array) ($_FILES['task_attachments']['tmp_name'] ?? []);
        $sizes = (array) ($_FILES['task_attachments']['size'] ?? []);
        $errors = (array) ($_FILES['task_attachments']['error'] ?? []);

        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'];

        foreach ($names as $idx => $originalName) {
            $error = (int) ($errors[$idx] ?? UPLOAD_ERR_NO_FILE);
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpName = (string) ($tmpNames[$idx] ?? '');
            $size = (int) ($sizes[$idx] ?? 0);
            $ext = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }

            $safeFile = uniqid('task_', true) . '.' . $ext;
            $target = $uploadBase . '/' . $safeFile;
            if (!@move_uploaded_file($tmpName, $target)) {
                continue;
            }

            $relativePath = 'uploads/tasks/' . $safeFile;
            $fileType = $ext === 'pdf' ? 'pdf' : 'image';

            $this->fileModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'task_id' => $taskId,
                'file_name' => (string) $originalName,
                'file_path' => $relativePath,
                'file_type' => $fileType,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function storePatientTask(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);

        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente não encontrado', 404);
            }
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        $title = Utils::sanitize($_POST['title'] ?? '');
        $description = $this->sanitizeRichText((string) ($_POST['description'] ?? ''));
        $sendToPatient = isset($_POST['send_to_patient']) ? 1 : 0;

        if ($dueDate === '' || $title === '' || $description === '') {
            if ($isAjax) {
                $this->error('Data, título e descrição são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Data, título e descrição são obrigatórios.'));
        }

        $taskId = $this->taskModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'due_date' => $dueDate,
            'title' => $title,
            'description' => $description,
            'send_to_patient' => $sendToPatient,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$taskId) {
            if ($isAjax) {
                $this->error('Falha ao cadastrar tarefa');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Falha ao cadastrar tarefa.'));
        }

        $this->storeTaskAttachments($therapistId, $patientId, (int) $taskId);

        if ($isAjax) {
            $this->success('Tarefa cadastrada', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Tarefa cadastrada com sucesso.'));
    }
}
