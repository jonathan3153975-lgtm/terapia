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
use App\Models\PatientGratitudeEntry;
use App\Models\PatientGuidedMeditationEntry;
use App\Models\PatientMessageEntry;
use App\Models\PatientPrayerEntry;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Prayer;
use App\Models\Task;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\AlertDispatcher;
use Helpers\Auth;
use Helpers\EmailTemplate;
use Helpers\MailService;
use Helpers\MercadoPagoGateway;
use Helpers\PatientSubscriptionPaymentSync;
use App\Models\VirtualTask;

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
    private PatientGratitudeEntry $patientGratitudeEntryModel;
    private GuidedMeditation $guidedMeditationModel;
    private Prayer $prayerModel;
    private HealingLetter $healingLetterModel;
    private PatientGuidedMeditationEntry $patientGuidedMeditationEntryModel;
    private PatientPrayerEntry $patientPrayerEntryModel;
    private Plan $planModel;
    private Payment $paymentModel;
    private PatientSubscription $patientSubscriptionModel;
    private MercadoPagoGateway $mercadoPagoGateway;
    private VirtualTask $virtualTaskModel;

    public function __construct()
    {
        $this->authorizePortalAccess();
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
        $this->patientGratitudeEntryModel = new PatientGratitudeEntry();
        $this->guidedMeditationModel = new GuidedMeditation();
        $this->prayerModel = new Prayer();
        $this->healingLetterModel = new HealingLetter();
        $this->patientGuidedMeditationEntryModel = new PatientGuidedMeditationEntry();
        $this->patientPrayerEntryModel = new PatientPrayerEntry();
        $this->planModel = new Plan();
        $this->paymentModel = new Payment();
        $this->patientSubscriptionModel = new PatientSubscription();
        $this->mercadoPagoGateway = new MercadoPagoGateway();
        $this->virtualTaskModel = new VirtualTask();

        $this->enforceActiveSubscription();
    }

    private function authorizePortalAccess(): void
    {
        if (Auth::role() === 'patient') {
            return;
        }

        if (Auth::role() === 'therapist' && Auth::isPatientPreviewActive()) {
            return;
        }

        header('Location: ' . Config::get('APP_URL', '') . '/index.php?action=login');
        exit;
    }

    private function enforceActiveSubscription(): void
    {
        // Ações que não exigem assinatura ativa (plano gratuito + gerenciamento de assinatura)
        $freeActions = [
            'dashboard',
            'tasks',
            'task-material',
            'task-respond',
            'task-respond-submit',
        ];

        $subscriptionActions = [
            'subscription-plans',
            'subscription-checkout',
            'subscription-return',
        ];

        $alwaysAllowed = array_merge($freeActions, $subscriptionActions);

        $action = (string) ($_GET['action'] ?? 'dashboard');
        $patientId = (int) Auth::patientId();
        if ($patientId <= 0) {
            return;
        }

        // Terapeuta em modo preview tem acesso irrestrito
        if (Auth::isPatientPreviewActive()) {
            $_SESSION['patient_has_active_plan'] = true;
            return;
        }

        $this->patientSubscriptionModel->markExpiredSubscriptions();
        $activeSubscription = $this->patientSubscriptionModel->findActiveByPatient($patientId);

        if ($activeSubscription) {
            $_SESSION['patient_has_active_plan'] = true;
            if (in_array($action, $subscriptionActions, true)) {
                $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=success&msg=' . urlencode('Sua assinatura está ativa.'));
            }
            return;
        }

        // Sem plano ativo: marcar sessão como free-tier
        $_SESSION['patient_has_active_plan'] = false;

        // Redirecionar para tarefas se tentar acessar conteúdo restrito
        if (!in_array($action, $alwaysAllowed, true)) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Este recurso requer um plano ativo. No plano gratuito você pode responder suas tarefas.'));
        }
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

    private function resolveGratitudeCycleContext(int $patientId): array
    {
        $latest = $this->patientGratitudeEntryModel->getLatestCycleStatsByPatient($patientId);
        if (!$latest) {
            return [
                'current_cycle' => 1,
                'next_cycle' => 1,
                'next_day' => 1,
                'is_locked' => false,
            ];
        }

        $currentCycle = (int) ($latest['cycle_number'] ?? 1);
        $maxDay = (int) ($latest['max_day'] ?? 0);
        $isLocked = $maxDay >= 30;

        if ($isLocked) {
            return [
                'current_cycle' => $currentCycle,
                'next_cycle' => $currentCycle + 1,
                'next_day' => 1,
                'is_locked' => true,
            ];
        }

        return [
            'current_cycle' => $currentCycle,
            'next_cycle' => $currentCycle,
            'next_day' => $maxDay + 1,
            'is_locked' => false,
        ];
    }

    private function buildGratitudeCycleDocument(array $entries, int $cycleNumber, string $patientName): string
    {
        $rows = '';
        foreach ($entries as $entry) {
            $day = (int) ($entry['day_number'] ?? 0);
            $content = (string) ($entry['content_html'] ?? '');
            $rows .= '<h4>Dia ' . $day . '</h4>' . $content . '<hr>';
        }

        return '<h3>Diário de gratidão (30 dias)</h3>'
            . '<p><strong>Paciente:</strong> ' . htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Ciclo:</strong> ' . $cycleNumber . '</p>'
            . $rows;
    }

    public function gratitude(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $context = $this->resolveGratitudeCycleContext($patientId);
        $entries = $this->patientGratitudeEntryModel->listByPatientAndCycle($patientId, (int) $context['current_cycle']);

        $this->view('patient/gratitude', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'entries' => $entries,
            'currentCycle' => (int) $context['current_cycle'],
            'nextCycle' => (int) $context['next_cycle'],
            'nextDay' => (int) $context['next_day'],
            'isCurrentCycleLocked' => (bool) $context['is_locked'],
        ]);
    }

    public function storeGratitudeEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $contentHtml = $this->sanitizeRichText((string) ($_POST['content_html'] ?? ''));
        if (trim(strip_tags($contentHtml)) === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Escreva sua gratidão antes de salvar.'));
        }

        $context = $this->resolveGratitudeCycleContext($patientId);
        $targetCycle = (int) $context['next_cycle'];
        $targetDay = (int) $context['next_day'];

        $saved = $this->patientGratitudeEntryModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'cycle_number' => $targetCycle,
            'day_number' => $targetDay,
            'content_html' => $contentHtml,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Falha ao salvar o registro de gratidão.'));
        }

        if ($targetDay >= 30) {
            $cycleEntries = $this->patientGratitudeEntryModel->listByPatientAndCycle($patientId, $targetCycle);
            $compiledHtml = $this->buildGratitudeCycleDocument($cycleEntries, $targetCycle, (string) ($patient['name'] ?? 'Paciente'));
            $this->taskModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'due_date' => date('Y-m-d'),
                'title' => 'Diário de gratidão (30 dias)',
                'description' => 'Resumo do diário de gratidão do paciente (ciclo ' . $targetCycle . ').',
                'patient_response_html' => $compiledHtml,
                'responded_at' => date('Y-m-d H:i:s'),
                'send_to_patient' => 0,
                'delivery_kind' => 'task',
                'status' => 'done',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=success&msg=' . urlencode('Registro salvo com sucesso.'));
    }

    public function showGratitudeEntry(): void
    {
        $patientId = (int) Auth::patientId();
        $entryId = (int) ($_GET['id'] ?? 0);
        $entry = $this->patientGratitudeEntryModel->findByPatientAndId($patientId, $entryId);
        if (!$entry) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Registro não encontrado.'));
        }

        $this->view('patient/gratitude-show', [
            'appUrl' => Config::get('APP_URL', ''),
            'entry' => $entry,
        ]);
    }

    public function editGratitudeEntry(): void
    {
        $patientId = (int) Auth::patientId();
        $entryId = (int) ($_GET['id'] ?? 0);
        $entry = $this->patientGratitudeEntryModel->findByPatientAndId($patientId, $entryId);
        if (!$entry) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Registro não encontrado.'));
        }

        $stats = $this->patientGratitudeEntryModel->getCycleStatsByPatient($patientId, (int) ($entry['cycle_number'] ?? 0));
        if ((int) ($stats['max_day'] ?? 0) >= 30) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Após 30 dias, o diário é bloqueado para edição.'));
        }

        $this->view('patient/gratitude-edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'entry' => $entry,
        ]);
    }

    public function updateGratitudeEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $entryId = (int) ($_POST['id'] ?? 0);
        $entry = $this->patientGratitudeEntryModel->findByPatientAndId($patientId, $entryId);
        if (!$entry) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Registro não encontrado.'));
        }

        $stats = $this->patientGratitudeEntryModel->getCycleStatsByPatient($patientId, (int) ($entry['cycle_number'] ?? 0));
        if ((int) ($stats['max_day'] ?? 0) >= 30) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Após 30 dias, o diário é bloqueado para edição.'));
        }

        $contentHtml = $this->sanitizeRichText((string) ($_POST['content_html'] ?? ''));
        if (trim(strip_tags($contentHtml)) === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude-edit&id=' . $entryId . '&status=error&msg=' . urlencode('Escreva sua gratidão antes de salvar.'));
        }

        $updated = $this->patientGratitudeEntryModel->updateById($entryId, [
            'content_html' => $contentHtml,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude-edit&id=' . $entryId . '&status=error&msg=' . urlencode('Falha ao atualizar o registro.'));
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=success&msg=' . urlencode('Registro atualizado com sucesso.'));
    }

    public function deleteGratitudeEntry(): void
    {
        $patientId = (int) Auth::patientId();
        $entryId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $entry = $this->patientGratitudeEntryModel->findByPatientAndId($patientId, $entryId);
        if (!$entry) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Registro não encontrado.'));
        }

        $stats = $this->patientGratitudeEntryModel->getCycleStatsByPatient($patientId, (int) ($entry['cycle_number'] ?? 0));
        if ((int) ($stats['max_day'] ?? 0) >= 30) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Após 30 dias, o diário é bloqueado para exclusão.'));
        }

        $deleted = $this->patientGratitudeEntryModel->deleteByPatientAndId($patientId, $entryId);
        if (!$deleted) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=error&msg=' . urlencode('Falha ao excluir registro.'));
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=gratitude&status=success&msg=' . urlencode('Registro excluído com sucesso.'));
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

    public function subscriptionPlans(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=logout');
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $plans = $therapistId > 0 ? $this->planModel->listPatientPlansByTherapist($therapistId) : [];
        $latestSubscription = $this->patientSubscriptionModel->findLatestByPatient($patientId);

        $this->view('patient/subscription-plans', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'plans' => $plans,
            'latestSubscription' => $latestSubscription,
            'mercadoPagoConfigured' => $this->mercadoPagoGateway->isConfigured(),
        ]);
    }

    public function startSubscriptionCheckout(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/index.php?action=logout');
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $plan = $this->planModel->findPatientPlanById($planId);

        if (!$plan || (int) ($plan['is_active'] ?? 0) !== 1 || (int) ($plan['therapist_id'] ?? 0) !== $therapistId) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode('Plano inválido para este paciente.'));
        }

        if (!$this->mercadoPagoGateway->isConfigured()) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode('Pagamento indisponível no momento. Contate o suporte.'));
        }

        $providerReference = 'PATSUB-' . $patientId . '-' . time() . '-' . strtoupper(bin2hex(random_bytes(3)));
        $amount = (float) ($plan['price'] ?? 0);

        $paymentId = $this->paymentModel->createPatientPlanPayment(
            $therapistId,
            $patientId,
            (int) $plan['id'],
            $amount,
            $providerReference
        );

        if (!$paymentId) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode('Não foi possível iniciar o pagamento.'));
        }

        $subscriptionId = $this->patientSubscriptionModel->insert([
            'patient_id' => $patientId,
            'therapist_id' => $therapistId,
            'plan_id' => (int) $plan['id'],
            'payment_id' => (int) $paymentId,
            'status' => 'pending',
            'billing_cycle' => (string) $plan['billing_cycle'],
            'amount' => number_format($amount, 2, '.', ''),
            'provider' => 'mercado_pago',
            'provider_reference' => $providerReference,
            'checkout_url' => null,
            'starts_at' => null,
            'ends_at' => null,
            'paid_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$subscriptionId) {
            $this->paymentModel->markStatusById((int) $paymentId, 'failed');
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode('Falha ao criar assinatura pendente.'));
        }

        $appUrl = Config::get('APP_URL', '');
        $notificationUrl = (string) Config::get('MP_WEBHOOK_URL', $appUrl . '/webhook.php?action=mercado-pago');
        $webhookSecret = trim((string) Config::get('MP_WEBHOOK_SECRET', ''));
        if ($webhookSecret !== '' && !str_contains($notificationUrl, 'token=')) {
            $notificationUrl .= (str_contains($notificationUrl, '?') ? '&' : '?') . 'token=' . urlencode($webhookSecret);
        }

        $payerEmail = (string) ($patient['email'] ?? '');
        if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userModel->findById((int) Auth::id());
            $payerEmail = (string) ($user['email'] ?? '');
        }

        $payload = [
            'items' => [[
                'id' => (string) $plan['id'],
                'title' => (string) $plan['name'],
                'description' => (string) ($plan['description_text'] ?? 'Assinatura de acesso ao conteúdo terapêutico'),
                'quantity' => 1,
                'currency_id' => (string) Config::get('MP_CURRENCY_ID', 'BRL'),
                'unit_price' => (float) $amount,
            ]],
            'external_reference' => $providerReference,
            'notification_url' => $notificationUrl,
            'back_urls' => [
                'success' => $appUrl . '/patient.php?action=subscription-return',
                'failure' => $appUrl . '/patient.php?action=subscription-return',
                'pending' => $appUrl . '/patient.php?action=subscription-return',
            ],
            'auto_return' => 'approved',
            'statement_descriptor' => substr((string) Config::get('MP_STATEMENT_DESCRIPTOR', 'TERAPIA'), 0, 13),
            'metadata' => [
                'patient_id' => $patientId,
                'therapist_id' => $therapistId,
                'plan_id' => (int) $plan['id'],
                'subscription_id' => (int) $subscriptionId,
            ],
        ];

        if (filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            $payload['payer'] = ['email' => $payerEmail];
        }

        $preferenceResult = $this->mercadoPagoGateway->createPreference($payload);
        if (($preferenceResult['ok'] ?? false) !== true) {
            $this->paymentModel->markStatusById((int) $paymentId, 'failed');
            $this->patientSubscriptionModel->markStatusById((int) $subscriptionId, 'failed');
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode((string) ($preferenceResult['message'] ?? 'Erro ao criar checkout no Mercado Pago.')));
        }

        $responseData = (array) ($preferenceResult['data'] ?? []);
        $checkoutUrl = (string) ($responseData['init_point'] ?? '');
        $preferSandbox = filter_var((string) Config::get('MP_USE_SANDBOX', 'true'), FILTER_VALIDATE_BOOLEAN);
        if ($preferSandbox && !empty($responseData['sandbox_init_point'])) {
            $checkoutUrl = (string) $responseData['sandbox_init_point'];
        }

        if ($checkoutUrl === '') {
            $this->paymentModel->markStatusById((int) $paymentId, 'failed');
            $this->patientSubscriptionModel->markStatusById((int) $subscriptionId, 'failed');
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=error&msg=' . urlencode('Checkout do Mercado Pago indisponível.'));
        }

        $this->patientSubscriptionModel->updateById((int) $subscriptionId, [
            'checkout_url' => $checkoutUrl,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect($checkoutUrl);
    }

    public function subscriptionReturn(): void
    {
        $paymentId = (int) ($_GET['payment_id'] ?? 0);
        if ($paymentId > 0) {
            (new PatientSubscriptionPaymentSync())->syncByPaymentId($paymentId);
        }

        $patientId = (int) Auth::patientId();
        $active = $this->patientSubscriptionModel->findActiveByPatient($patientId);

        if ($active) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=success&msg=' . urlencode('Assinatura ativada com sucesso.'));
        }

        $status = strtolower((string) ($_GET['status'] ?? 'pending'));
        $message = match ($status) {
            'approved' => 'Pagamento recebido. Estamos finalizando a ativação da assinatura.',
            'failure' => 'Pagamento não concluído. Tente novamente.',
            default => 'Pagamento pendente. Assim que confirmado, sua assinatura será ativada.',
        };

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=subscription-plans&status=' . ($status === 'failure' ? 'error' : 'success') . '&msg=' . urlencode($message));
    }

    public function dashboard(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        $nextAppointment = $this->appointmentModel->findNextByPatient($patientId);
        $activeSubscription = $this->patientSubscriptionModel->findActiveByPatient($patientId);

        $chartLabels = [];
        $chartSessions = [];
        $chartTasksDone = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = new \DateTimeImmutable("-{$i} months");
            $ym = $dt->format('Y-m');
            $chartLabels[] = $dt->format('m/Y');
            $chartSessions[] = $this->appointmentModel->countCompletedInMonthByPatient($patientId, $ym);
            $chartTasksDone[] = $this->taskModel->countDoneInMonthByPatient($patientId, $ym);
        }

        $pendingTasks = $this->taskModel->countPendingInboxTasksByPatient($patientId);
        $doneTasks = $this->taskModel->countDoneByPatient($patientId);
        $completionRate = ($pendingTasks + $doneTasks) > 0 ? (int) round(($doneTasks / ($pendingTasks + $doneTasks)) * 100) : 0;

        $this->view('patient/dashboard', [
            'appUrl' => Config::get('APP_URL', ''),
            'daysSinceRegister' => $this->daysSince($patient['created_at'] ?? null),
            'sessionsDone' => $this->appointmentModel->countCompletedByPatient($patientId),
            'nextAppointment' => $nextAppointment,
            'receivedTasks' => $pendingTasks,
            'receivedMaterials' => $this->taskModel->countInboxByPatientAndKind($patientId, 'material') + $this->materialDeliveryModel->countByPatient($patientId),
            'doneTasks' => $doneTasks,
            'completionRate' => $completionRate,
            'activeSubscription' => $activeSubscription,
            'chartLabels' => $chartLabels,
            'chartSessions' => $chartSessions,
            'chartTasksDone' => $chartTasksDone,
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

        $therapist = $this->userModel->findById($therapistId);
        $entries = $this->patientGuidedMeditationEntryModel->listByPatient($patientId, $meditationId);

        $this->view('patient/guided-meditation-show', [
            'appUrl' => Config::get('APP_URL', ''),
            'meditation' => $meditation,
            'entries' => $entries,
            'therapist' => $therapist,
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

    public function prayers(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=dashboard&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $prayers = $therapistId > 0 ? $this->prayerModel->listByTherapist($therapistId) : [];

        $this->view('patient/prayers', [
            'appUrl' => Config::get('APP_URL', ''),
            'prayers' => $prayers,
        ]);
    }

    public function prayerShow(): void
    {
        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $prayerId = (int) ($_GET['id'] ?? 0);
        if ($therapistId <= 0 || $prayerId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Oração inválida.'));
        }

        $prayer = $this->prayerModel->findByTherapistAndId($therapistId, $prayerId);
        if (!$prayer) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Oração não encontrada.'));
        }

        $therapist = $this->userModel->findById($therapistId);
        $entries = $this->patientPrayerEntryModel->listByPatient($patientId, $prayerId);

        $this->view('patient/prayer-show', [
            'appUrl' => Config::get('APP_URL', ''),
            'prayer' => $prayer,
            'entries' => $entries,
            'therapist' => $therapist,
        ]);
    }

    public function savePrayerEntry(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $patientId = (int) Auth::patientId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Terapeuta não vinculado.'));
        }

        $prayerId = (int) ($_POST['prayer_id'] ?? 0);
        $prayer = $this->prayerModel->findByTherapistAndId($therapistId, $prayerId);
        if (!$prayer) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayers&status=error&msg=' . urlencode('Oração não encontrada.'));
        }

        $patientNote = trim((string) ($_POST['patient_note'] ?? ''));
        $shareWithTherapist = isset($_POST['share_with_therapist']) ? 1 : 0;

        if ($patientNote === '') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayer-show&id=' . $prayerId . '&status=error&msg=' . urlencode('A reflexão é obrigatória.'));
        }

        $saved = $this->patientPrayerEntryModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'prayer_id' => $prayerId,
            'patient_note' => $patientNote,
            'share_with_therapist' => $shareWithTherapist,
            'listened_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayer-show&id=' . $prayerId . '&status=error&msg=' . urlencode('Falha ao salvar reflexão.'));
        }

        if ($shareWithTherapist === 1) {
            $this->taskModel->insert([
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'due_date' => date('Y-m-d'),
                'title' => 'Reflexão Oração',
                'description' => (string) ($prayer['title'] ?? 'Oração'),
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
                $subject = 'Nova reflexão em Orações';
                $message = 'Seu paciente compartilhou uma nova reflexão no módulo de Orações. Acesse o painel para visualizar.';
                AlertDispatcher::dispatch(['email'], (string) ($therapist['email'] ?? ''), null, $subject, $message);
            }
        }

        $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=prayer-show&id=' . $prayerId . '&status=success&msg=' . urlencode('Reflexão salva com sucesso.'));
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

        // Tarefas dinâmicas possuem fluxo de resposta dedicado.
        if ((string) ($task['task_type'] ?? 'regular') === 'virtual_tree_of_life') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=virtual-tree-of-life&id=' . (int) $task['id']);
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

    public function showVirtualTask(string $type = 'tree_of_life'): void
    {
        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_GET['id'] ?? 0);

        if ($taskId <= 0) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Tarefa inválida.'));
        }

        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Tarefa não encontrada.'));
        }

        $taskType = (string) ($task['task_type'] ?? 'regular');
        if ($taskType !== 'virtual_tree_of_life') {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks&status=error&msg=' . urlencode('Tipo de tarefa inválido.'));
        }

        $contentJson = (string) ($task['content_json'] ?? '');
        $structure = $contentJson !== '' ? json_decode($contentJson, true) : [];
        if (!is_array($structure) || $structure === []) {
            $structure = VirtualTask::getTreeOfLifeStructure();
        }

        $this->view('patient/virtual-tree-of-life', [
            'appUrl' => Config::get('APP_URL', ''),
            'task' => $task,
            'structure' => $structure,
        ]);
    }

    public function completeVirtualTask(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!is_array($input)) {
            $this->error('Entrada inválida', 400);
        }

        $patientId = (int) Auth::patientId();
        $taskId = (int) ($input['task_id'] ?? 0);
        $reflectionHtml = (string) ($input['reflection'] ?? '');
        $answers = $input['answers'] ?? [];

        if ($taskId <= 0) {
            $this->error('Tarefa inválida', 422);
        }

        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->error('Tarefa não encontrada', 404);
        }

        if ((string) ($task['task_type'] ?? 'regular') !== 'virtual_tree_of_life') {
            $this->error('Tipo de tarefa inválido', 422);
        }

        $reflectionHtml = $this->sanitizeRichText($reflectionHtml);

        // Salva respostas por seção na tabela virtual_task_responses
        if (is_array($answers) && $answers !== []) {
            $therapistId = (int) ($task['therapist_id'] ?? 0);
            foreach ($answers as $sectionName => $sectionAnswers) {
                if (!is_string($sectionName) || trim($sectionName) === '') {
                    continue;
                }

                $normalizedAnswers = [];
                if (is_array($sectionAnswers)) {
                    foreach ($sectionAnswers as $answer) {
                        if (is_scalar($answer)) {
                            $normalizedAnswers[] = trim((string) $answer);
                        }
                    }
                }

                $this->virtualTaskModel->saveResponse(
                    $therapistId,
                    $patientId,
                    $taskId,
                    trim($sectionName),
                    json_encode($normalizedAnswers, JSON_UNESCAPED_UNICODE)
                );
            }
        }

        $updated = $this->taskModel->updateById($taskId, [
            'status' => 'done',
            'is_active' => 0,
            'patient_response_html' => $reflectionHtml,
            'responded_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->error('Falha ao completar tarefa', 500);
        }

        $this->json([
            'success' => true,
            'message' => 'Tarefa virtual concluída com sucesso',
            'redirect' => Config::get('APP_URL', '') . '/patient.php?action=tasks&status=success&msg=' . urlencode('Tarefa concluída!'),
        ]);
    }
}
