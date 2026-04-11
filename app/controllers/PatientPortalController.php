<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\DailyMessage;
use App\Models\FaithWord;
use App\Models\FileStorage;
use App\Models\GuidedMeditation;
use App\Models\HealingLetter;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\Patient;
use App\Models\PatientFaithEntry;
use App\Models\PatientGuidedMeditationEntry;
use App\Models\PatientMessageEntry;
use App\Models\Task;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\AlertDispatcher;
use Helpers\Auth;
use Helpers\EmailTemplate;
use Helpers\MailService;

class PatientPortalController extends Controller
{
    private Task $taskModel;
    private Appointment $appointmentModel;
    private Material $materialModel;
    private MaterialDelivery $materialDeliveryModel;
    private FileStorage $fileModel;
    private Patient $patientModel;
    private User $userModel;
    private DailyMessage $dailyMessageModel;
    private PatientMessageEntry $patientMessageEntryModel;
    private FaithWord $faithWordModel;
    private PatientFaithEntry $patientFaithEntryModel;
    private GuidedMeditation $guidedMeditationModel;
    private HealingLetter $healingLetterModel;
    private PatientGuidedMeditationEntry $patientGuidedMeditationEntryModel;

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
        $this->dailyMessageModel = new DailyMessage();
        $this->patientMessageEntryModel = new PatientMessageEntry();
        $this->faithWordModel = new FaithWord();
        $this->patientFaithEntryModel = new PatientFaithEntry();
        $this->guidedMeditationModel = new GuidedMeditation();
        $this->healingLetterModel = new HealingLetter();
        $this->patientGuidedMeditationEntryModel = new PatientGuidedMeditationEntry();
    }

    private function normalizeDailyMessageCategory(string $value): string
    {
        $value = strtolower(trim($value));
        return in_array($value, ['dores', 'reflexivas', 'cura', 'motivacionais', 'conflitos'], true) ? $value : 'dores';
    }

    private function messengerCycleSessionKey(int $patientId): string
    {
        return 'messenger_drawn_ids_patient_' . $patientId;
    }

    private function getMessengerCycleDrawnIds(int $patientId): array
    {
        $key = $this->messengerCycleSessionKey($patientId);
        $ids = $_SESSION[$key] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }

    private function setMessengerCycleDrawnIds(int $patientId, array $ids): void
    {
        $_SESSION[$this->messengerCycleSessionKey($patientId)] = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function appendMessengerCycleDrawnId(int $patientId, int $messageId): void
    {
        $ids = $this->getMessengerCycleDrawnIds($patientId);
        $ids[] = $messageId;
        $this->setMessengerCycleDrawnIds($patientId, $ids);
    }

    private function fatherWordCycleSessionKey(int $patientId): string
    {
        return 'father_word_drawn_ids_patient_' . $patientId;
    }

    private function getFatherWordCycleDrawnIds(int $patientId): array
    {
        $key = $this->fatherWordCycleSessionKey($patientId);
        $ids = $_SESSION[$key] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }

    private function setFatherWordCycleDrawnIds(int $patientId, array $ids): void
    {
        $_SESSION[$this->fatherWordCycleSessionKey($patientId)] = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function appendFatherWordCycleDrawnId(int $patientId, int $wordId): void
    {
        $ids = $this->getFatherWordCycleDrawnIds($patientId);
        $ids[] = $wordId;
        $this->setFatherWordCycleDrawnIds($patientId, $ids);
    }

    private function healingLettersCycleSessionKey(int $patientId): string
    {
        return 'healing_letters_drawn_ids_patient_' . $patientId;
    }

    private function getHealingLettersCycleDrawnIds(int $patientId): array
    {
        $key = $this->healingLettersCycleSessionKey($patientId);
        $ids = $_SESSION[$key] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }

    private function setHealingLettersCycleDrawnIds(int $patientId, array $ids): void
    {
        $_SESSION[$this->healingLettersCycleSessionKey($patientId)] = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function appendHealingLettersCycleDrawnId(int $patientId, int $letterId): void
    {
        $ids = $this->getHealingLettersCycleDrawnIds($patientId);
        $ids[] = $letterId;
        $this->setHealingLettersCycleDrawnIds($patientId, $ids);
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
            $therapistEmail = (string) ($therapist['email'] ?? '');
            $therapistPhone = (string) ($therapist['phone'] ?? '');
            $therapistName = (string) ($therapist['name'] ?? 'Terapeuta');
            $patientName = Auth::user()['name'] ?? 'Paciente';

            $report = [];

            // Sending email with HTML template
            if (in_array('email', $channels, true) && filter_var($therapistEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    $mailService = new MailService();
                    $html = EmailTemplate::taskResponseReceived($therapistName, $patientName, $taskTitle);
                    $sent = $mailService->send($therapistEmail, $therapistName, 'Devolutiva de Tarefa Recebida', $html);
                    $report['email'] = ['status' => $sent ? 'sent' : 'failed'];
                } catch (\Throwable $e) {
                    error_log('[task-response-email] ' . $e->getMessage());
                    $report['email'] = ['status' => 'failed'];
                }
            }

            // Sending WhatsApp notification
            if (in_array('whatsapp', $channels, true)) {
                $message = 'Nova devolutiva de tarefa recebida: "' . $taskTitle . '". Acesse o painel para revisar.';
                $whatsappReport = AlertDispatcher::dispatch(['whatsapp'], '', $therapistPhone, '', $message);
                $report['whatsapp'] = $whatsappReport['whatsapp'] ?? ['status' => 'skipped'];
            }

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

    public function messenger(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $entries = $this->patientMessageEntryModel->listByPatient($patientId);
        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $totalMessages = $therapistId > 0 ? $this->dailyMessageModel->countByTherapist($therapistId) : 0;
        $cycleDrawnIds = $this->getMessengerCycleDrawnIds($patientId);

        $this->view('patient/messenger', [
            'appUrl' => Config::get('APP_URL', ''),
            'entries' => $entries,
            'totalMessages' => $totalMessages,
            'cycleDrawCount' => min(count($cycleDrawnIds), $totalMessages),
        ]);
    }

    public function drawMessengerMessage(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->error('Terapeuta não encontrado', 404);
        }

        $allMessageIds = $this->dailyMessageModel->listIdsByTherapist($therapistId);
        if ($allMessageIds === []) {
            $this->error('Nenhuma mensagem disponível para sorteio.', 404);
        }

        $cycleDrawnIds = $this->getMessengerCycleDrawnIds($patientId);
        $cycleDrawnIds = array_values(array_intersect($allMessageIds, $cycleDrawnIds));
        $cycleRestarted = false;

        if (count($cycleDrawnIds) >= count($allMessageIds)) {
            $cycleDrawnIds = [];
            $this->setMessengerCycleDrawnIds($patientId, []);
            $cycleRestarted = true;
        }

        $message = $this->dailyMessageModel->randomByTherapistExcludingIds($therapistId, $cycleDrawnIds);
        if (!$message) {
            $this->error('Nenhuma mensagem disponível para sorteio.', 404);
        }

        $messageId = (int) ($message['id'] ?? 0);
        if ($messageId > 0) {
            $this->appendMessengerCycleDrawnId($patientId, $messageId);
            $cycleDrawnIds[] = $messageId;
        }

        $this->json([
            'success' => true,
            'message' => 'Mensagem sorteada',
            'data' => [
                'message' => [
                    'id' => $messageId,
                    'category' => (string) ($message['category'] ?? ''),
                    'text' => (string) ($message['message_text'] ?? ''),
                ],
                'cycle' => [
                    'drawnCount' => min(count(array_unique($cycleDrawnIds)), count($allMessageIds)),
                    'totalCount' => count($allMessageIds),
                    'remainingCount' => max(count($allMessageIds) - count(array_unique($cycleDrawnIds)), 0),
                    'restarted' => $cycleRestarted,
                ],
            ],
        ]);
    }

    public function saveMessengerEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=error&msg=' . urlencode('Terapeuta não vinculado.'));
        }

        $messageId = (int) ($_POST['message_id'] ?? 0);
        $messageCategory = $this->normalizeDailyMessageCategory((string) ($_POST['message_category'] ?? 'dores'));
        $messageText = trim((string) ($_POST['message_text'] ?? ''));
        $patientNote = trim((string) ($_POST['patient_note'] ?? ''));
        $shareWithTherapist = isset($_POST['share_with_therapist']) ? 1 : 0;

        if ($messageText === '' || $patientNote === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=error&msg=' . urlencode('A mensagem sorteada e sua reflexão são obrigatórias.'));
        }

        $saved = $this->patientMessageEntryModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'message_id' => $messageId > 0 ? $messageId : null,
            'message_category' => $messageCategory,
            'message_text' => $messageText,
            'patient_note' => $patientNote,
            'share_with_therapist' => $shareWithTherapist,
            'drawn_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=error&msg=' . urlencode('Falha ao salvar reflexão.'));
        }

        if ($shareWithTherapist === 1) {
            $this->taskModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'due_date' => date('Y-m-d'),
                'title' => 'Leitura Mensageiro',
                'description' => $messageText,
                'patient_response_html' => $patientNote,
                'responded_at' => date('Y-m-d H:i:s'),
                'send_to_patient' => 0,
                'delivery_kind' => 'task',
                'status' => 'done',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $therapist = $this->userModel->findById($therapistId);
            if ($therapist) {
                $subject = 'Nova reflexão no Mensageiro';
                $message = 'Seu paciente compartilhou uma nova reflexão no Mensageiro. Acesse o painel para visualizar.';
                AlertDispatcher::dispatch(['email'], (string) ($therapist['email'] ?? ''), null, $subject, $message);
            }
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=messenger&status=success&msg=' . urlencode('Mensagem salva com sucesso.'));
    }

    public function fatherWord(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $entries = $this->patientFaithEntryModel->listByPatient($patientId);

        $this->view('patient/father-word', [
            'appUrl' => Config::get('APP_URL', ''),
            'entries' => $entries,
        ]);
    }

    public function guidedMeditations(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $meditations = $therapistId > 0 ? $this->guidedMeditationModel->listByTherapist($therapistId) : [];

        $this->view('patient/guided-meditations', [
            'appUrl' => Config::get('APP_URL', ''),
            'meditations' => $meditations,
        ]);
    }

    public function guidedMeditationShow(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $meditationId = (int) ($_GET['id'] ?? 0);
        if ($therapistId <= 0 || $meditationId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Meditação inválida.'));
        }

        $meditation = $this->guidedMeditationModel->findByTherapistAndId($therapistId, $meditationId);
        if (!$meditation) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Meditação não encontrada.'));
        }

        $entries = $this->patientGuidedMeditationEntryModel->listByPatient($patientId, $meditationId);

        $this->view('patient/guided-meditation-show', [
            'appUrl' => Config::get('APP_URL', ''),
            'meditation' => $meditation,
            'entries' => $entries,
        ]);
    }

    public function drawGuidedMeditationLetter(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->error('Terapeuta não encontrado', 404);
        }

        $meditationId = (int) ($_GET['meditation_id'] ?? 0);
        if ($meditationId <= 0) {
            $this->error('Meditação inválida', 422);
        }

        $meditation = $this->guidedMeditationModel->findByTherapistAndId($therapistId, $meditationId);
        if (!$meditation) {
            $this->error('Meditação não encontrada', 404);
        }

        $allLetterIds = $this->healingLetterModel->listIdsByTherapist($therapistId);
        if ($allLetterIds === []) {
            $this->error('Nenhuma carta de cura disponível para sorteio.', 404);
        }

        $cycleDrawnIds = $this->getHealingLettersCycleDrawnIds($patientId);
        $cycleDrawnIds = array_values(array_intersect($allLetterIds, $cycleDrawnIds));
        $cycleRestarted = false;

        if (count($cycleDrawnIds) >= count($allLetterIds)) {
            $cycleDrawnIds = [];
            $this->setHealingLettersCycleDrawnIds($patientId, []);
            $cycleRestarted = true;
        }

        $letter = $this->healingLetterModel->randomByTherapistExcludingIds($therapistId, $cycleDrawnIds);
        if (!$letter) {
            $this->error('Nenhuma carta de cura disponível para sorteio.', 404);
        }

        $letterId = (int) ($letter['id'] ?? 0);
        if ($letterId > 0) {
            $this->appendHealingLettersCycleDrawnId($patientId, $letterId);
            $cycleDrawnIds[] = $letterId;
        }

        $this->json([
            'success' => true,
            'message' => 'Carta sorteada',
            'data' => [
                'letter' => [
                    'id' => $letterId,
                    'category' => (string) ($letter['category'] ?? ''),
                    'text' => (string) ($letter['message_text'] ?? ''),
                ],
                'cycle' => [
                    'restarted' => $cycleRestarted,
                ],
            ],
        ]);
    }

    public function saveGuidedMeditationEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Terapeuta não vinculado.'));
        }

        $meditationId = (int) ($_POST['meditation_id'] ?? 0);
        $meditation = $this->guidedMeditationModel->findByTherapistAndId($therapistId, $meditationId);
        if (!$meditation) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditations&status=error&msg=' . urlencode('Meditação não encontrada.'));
        }

        $letterId = (int) ($_POST['letter_id'] ?? 0);
        $letterCategory = $this->normalizeDailyMessageCategory((string) ($_POST['letter_category'] ?? 'dores'));
        $letterText = trim((string) ($_POST['letter_text'] ?? ''));
        $patientNote = trim((string) ($_POST['patient_note'] ?? ''));
        $shareWithTherapist = isset($_POST['share_with_therapist']) ? 1 : 0;

        if ($letterText === '' || $patientNote === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditation-show&id=' . $meditationId . '&status=error&msg=' . urlencode('Carta e reflexão são obrigatórias.'));
        }

        $saved = $this->patientGuidedMeditationEntryModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'meditation_id' => $meditationId,
            'letter_id' => $letterId > 0 ? $letterId : null,
            'letter_category' => $letterCategory,
            'letter_text' => $letterText,
            'patient_note' => $patientNote,
            'share_with_therapist' => $shareWithTherapist,
            'listened_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditation-show&id=' . $meditationId . '&status=error&msg=' . urlencode('Falha ao salvar reflexão.'));
        }

        if ($shareWithTherapist === 1) {
            $this->taskModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'due_date' => date('Y-m-d'),
                'title' => 'Reflexão Meditação Guiada',
                'description' => (string) ($meditation['title'] ?? 'Meditação') . "\n\n" . $letterText,
                'patient_response_html' => $patientNote,
                'responded_at' => date('Y-m-d H:i:s'),
                'send_to_patient' => 0,
                'delivery_kind' => 'task',
                'status' => 'done',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $therapist = $this->userModel->findById($therapistId);
            if ($therapist) {
                $subject = 'Nova reflexão em Meditação Guiada';
                $message = 'Seu paciente compartilhou uma nova reflexão no módulo de Meditação Guiada. Acesse o painel para visualizar.';
                AlertDispatcher::dispatch(['email'], (string) ($therapist['email'] ?? ''), null, $subject, $message);
            }
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=guided-meditation-show&id=' . $meditationId . '&status=success&msg=' . urlencode('Reflexão salva com sucesso.'));
    }

    public function drawFatherWord(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->error('Terapeuta não encontrado', 404);
        }

        $allWordIds = $this->faithWordModel->listIdsByTherapist($therapistId);
        if ($allWordIds === []) {
            $this->error('Nenhuma palavra disponível para sorteio.', 404);
        }

        $cycleDrawnIds = $this->getFatherWordCycleDrawnIds($patientId);
        $cycleDrawnIds = array_values(array_intersect($allWordIds, $cycleDrawnIds));
        $cycleRestarted = false;

        if (count($cycleDrawnIds) >= count($allWordIds)) {
            $cycleDrawnIds = [];
            $this->setFatherWordCycleDrawnIds($patientId, []);
            $cycleRestarted = true;
        }

        $word = $this->faithWordModel->randomByTherapistExcludingIds($therapistId, $cycleDrawnIds);
        if (!$word) {
            $this->error('Nenhuma palavra disponível para sorteio.', 404);
        }

        $wordId = (int) ($word['id'] ?? 0);
        if ($wordId > 0) {
            $this->appendFatherWordCycleDrawnId($patientId, $wordId);
            $cycleDrawnIds[] = $wordId;
        }

        $this->json([
            'success' => true,
            'message' => 'Palavra sorteada',
            'data' => [
                'word' => [
                    'id' => $wordId,
                    'reference' => (string) ($word['reference_text'] ?? ''),
                    'text' => (string) ($word['verse_text'] ?? ''),
                ],
                'cycle' => [
                    'restarted' => $cycleRestarted,
                ],
            ],
        ]);
    }

    public function saveFatherWordEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=error&msg=' . urlencode('Terapeuta não vinculado.'));
        }

        $wordId = (int) ($_POST['word_id'] ?? 0);
        $wordReference = trim((string) ($_POST['word_reference'] ?? ''));
        $wordText = trim((string) ($_POST['word_text'] ?? ''));
        $patientNote = trim((string) ($_POST['patient_note'] ?? ''));
        $shareWithTherapist = isset($_POST['share_with_therapist']) ? 1 : 0;

        if ($wordReference === '' || $wordText === '' || $patientNote === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=error&msg=' . urlencode('Palavra e reflexão são obrigatórias.'));
        }

        $saved = $this->patientFaithEntryModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'word_id' => $wordId > 0 ? $wordId : null,
            'word_reference' => $wordReference,
            'word_text' => $wordText,
            'patient_note' => $patientNote,
            'share_with_therapist' => $shareWithTherapist,
            'drawn_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=error&msg=' . urlencode('Falha ao salvar reflexão.'));
        }

        if ($shareWithTherapist === 1) {
            $this->taskModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'due_date' => date('Y-m-d'),
                'title' => 'Leitura Pai, fala comigo',
                'description' => $wordReference . "\n\n" . $wordText,
                'patient_response_html' => $patientNote,
                'responded_at' => date('Y-m-d H:i:s'),
                'send_to_patient' => 0,
                'delivery_kind' => 'task',
                'status' => 'done',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $therapist = $this->userModel->findById($therapistId);
            if ($therapist) {
                $subject = 'Nova reflexão em Pai, fala comigo';
                $message = 'Seu paciente compartilhou uma nova reflexão em Pai, fala comigo. Acesse o painel para visualizar.';
                AlertDispatcher::dispatch(['email'], (string) ($therapist['email'] ?? ''), null, $subject, $message);
            }
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=father-word&status=success&msg=' . urlencode('Palavra e reflexão salvas com sucesso.'));
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
            $channels = $_POST['notify_channels'] ?? ['email'];
            if (!is_array($channels)) {
                $channels = ['email'];
            }

            $summary = $this->dispatchTaskAlertSafely($therapist, (string) ($task['title'] ?? 'Tarefa'), $channels);
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=success&msg=' . urlencode('Resposta enviada com sucesso. Alertas: ' . $summary . '.'));
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=success&msg=' . urlencode('Resposta enviada com sucesso.'));
    }
}
