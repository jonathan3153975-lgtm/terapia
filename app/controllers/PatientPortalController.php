<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\FileStorage;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\Patient;
use App\Models\Task;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\AlertDispatcher;
use Helpers\Auth;

class PatientPortalController extends Controller
{
    private Task $taskModel;
    private Appointment $appointmentModel;
    private Material $materialModel;
    private MaterialDelivery $materialDeliveryModel;
    private FileStorage $fileModel;
    private Patient $patientModel;
    private User $userModel;

    public function __construct()
    {
        Auth::requireRoles(['patient']);
        $this->taskModel = new Task();
        $this->appointmentModel = new Appointment();
        $this->materialModel = new Material();
        $this->materialDeliveryModel = new MaterialDelivery();
        $this->fileModel = new FileStorage();
        $this->patientModel = new Patient();
        $this->userModel = new User();
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

    private function daysSince(?string $createdAt): int
    {
        if (empty($createdAt)) {
            return 0;
        }

        $created = strtotime((string) $createdAt);
        if ($created === false) {
            return 0;
        }

        $diff = time() - $created;
        if ($diff < 0) {
            return 0;
        }

        return (int) floor($diff / 86400);
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
                'source_role' => 'patient',
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
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                continue;
            }

            $size = (int) ($sizes[$idx] ?? 0);
            $ext = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                continue;
            }

            $safeFile = uniqid('task_response_', true) . '.' . $ext;
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
                'source_role' => 'patient',
                'file_name' => (string) $originalName,
                'file_path' => $relativePath,
                'file_type' => $fileType,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function updateTaskResponseSafely(int $taskId, string $responseHtml): bool
    {
        $data = [
            'patient_response_html' => $responseHtml,
            'responded_at' => date('Y-m-d H:i:s'),
            'status' => 'done',
        ];

        $updated = $this->taskModel->updateById($taskId, $data);
        if ($updated) {
            return true;
        }

        // Fallback: Try without the new columns in case migration not applied
        $fallback = [
            'status' => 'done',
        ];
        return $this->taskModel->updateById($taskId, $fallback);
    }

    private function storeTaskAttachmentsSafely(int $therapistId, int $patientId, int $taskId): void
    {
        try {
            $this->storeTaskAttachments($therapistId, $patientId, $taskId);
        } catch (\Throwable $e) {
            error_log('Error storing task attachments for task ' . $taskId . ': ' . $e->getMessage());
        }
    }

    private function dispatchTaskAlertSafely(array $therapist, string $taskTitle, array $channels): string
    {
        try {
            $message = 'Nova devolutiva de tarefa recebida: "' . $taskTitle . '".';
            $report = AlertDispatcher::dispatch(
                $channels,
                (string) ($therapist['email'] ?? ''),
                (string) ($therapist['phone'] ?? ''),
                'Devolutiva de tarefa recebida',
                $message
            );
            return AlertDispatcher::summarize($report);
        } catch (\Throwable $e) {
            error_log('Error dispatching task alert: ' . $e->getMessage());
            return 'alert-error';
        }
    }

    public function dashboard(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        $nextAppointment = $this->appointmentModel->findNextByPatient($patientId);

        $this->view('patient/dashboard', [
            'appUrl' => Config::get('APP_URL', ''),
            'daysSinceRegister' => $this->daysSince($patient['created_at'] ?? null),
            'sessionsDone' => $this->appointmentModel->countCompletedByPatient($patientId),
            'nextAppointment' => $nextAppointment,
            'receivedTasks' => $this->taskModel->countPendingInboxTasksByPatient($patientId),
            'receivedMaterials' => $this->taskModel->countInboxByPatientAndKind($patientId, 'material') + $this->materialDeliveryModel->countByPatient($patientId),
        ]);
    }

    public function tasks(): void
    {
        $patientId = (int) Auth::patientId();
        $tasks = $this->taskModel->listInboxByPatientAndKind($patientId, 'task');
        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $tasks);
        $this->view('patient/tasks', [
            'appUrl' => Config::get('APP_URL', ''),
            'tasks' => $tasks,
            'taskLinkedMaterials' => $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds),
        ]);
    }

    public function materials(): void
    {
        $patientId = (int) Auth::patientId();
        $materialTasks = $this->taskModel->listInboxByPatientAndKind($patientId, 'material');
        $deliveries = $this->materialDeliveryModel->listByPatient($patientId);

        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $materialTasks);
        $taskLinkedMaterials = $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds);

        $assetsByMaterial = [];
        foreach ($deliveries as $delivery) {
            $materialId = (int) ($delivery['material_id'] ?? 0);
            if ($materialId <= 0 || isset($assetsByMaterial[$materialId])) {
                continue;
            }
            $assetsByMaterial[$materialId] = $this->materialModel->listAssets($materialId);
        }

        $this->view('patient/materials', [
            'appUrl' => Config::get('APP_URL', ''),
            'materialTasks' => $materialTasks,
            'deliveries' => $deliveries,
            'taskLinkedMaterials' => $taskLinkedMaterials,
            'assetsByMaterial' => $assetsByMaterial,
        ]);
    }

    public function showTaskMaterial(): void
    {
        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_GET['id'] ?? 0);

        $task = $this->taskModel->findByPatientAndId($patientId, $taskId);
        if (!$task || empty($task['send_to_patient'])) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks');
        }

        $materials = $this->taskModel->listLinkedMaterials($taskId);
        if (empty($materials)) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks');
        }

        $assetsByMaterial = [];
        foreach ($materials as $material) {
            $assetsByMaterial[(int) $material['id']] = $this->materialModel->listAssets((int) $material['id']);
        }

        $this->view('patient/task-material', [
            'appUrl' => Config::get('APP_URL', ''),
            'task' => $task,
            'materials' => $materials,
            'assetsByMaterial' => $assetsByMaterial,
        ]);
    }

    public function respondTask(): void
    {
        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_GET['id'] ?? 0);

        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Tarefa não encontrada.'));
        }

        $therapistFiles = $this->fileModel->listByTaskAndSourceRole($taskId, 'therapist');
        $patientFiles = $this->fileModel->listByTaskAndSourceRole($taskId, 'patient');

        $this->view('patient/task-respond', [
            'appUrl' => Config::get('APP_URL', ''),
            'task' => $task,
            'therapistFiles' => $therapistFiles,
            'patientFiles' => $patientFiles,
        ]);
    }

    public function submitTaskResponse(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Tarefa inválida.'));
        }

        $responseHtml = $this->sanitizeRichText((string) ($_POST['response_html'] ?? ''));
        if ($responseHtml === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=task-respond&id=' . $taskId . '&status=error&msg=' . urlencode('A resposta é obrigatória.'));
        }

        $updated = $this->updateTaskResponseSafely($taskId, $responseHtml);
        if (!$updated) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=task-respond&id=' . $taskId . '&status=error&msg=' . urlencode('Falha ao enviar resposta.'));
        }

        $this->storeTaskAttachmentsSafely((int) ($task['therapist_id'] ?? 0), $patientId, $taskId);

        $therapist = $this->userModel->findById((int) ($task['therapist_id'] ?? 0));
        if ($therapist) {
            $channels = $_POST['notify_channels'] ?? ['email', 'whatsapp'];
            if (!is_array($channels)) {
                $channels = ['email', 'whatsapp'];
            }

            $summary = $this->dispatchTaskAlertSafely($therapist, (string) ($task['title'] ?? 'Tarefa'), $channels);
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=success&msg=' . urlencode('Resposta enviada com sucesso. Alertas: ' . $summary . '.'));
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=success&msg=' . urlencode('Resposta enviada com sucesso.'));
    }
}
