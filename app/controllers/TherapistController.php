<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\FileStorage;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Task;
use Classes\Controller;
use Config\Config;
use Helpers\AlertDispatcher;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;

class TherapistController extends Controller
{
    private Patient $patientModel;
    private Appointment $appointmentModel;
    private Material $materialModel;
    private MaterialDelivery $materialDeliveryModel;
    private Payment $paymentModel;
    private Task $taskModel;
    private FileStorage $fileModel;

    public function __construct()
    {
        Auth::requireRoles(['therapist']);
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
        $this->materialModel = new Material();
        $this->materialDeliveryModel = new MaterialDelivery();
        $this->paymentModel = new Payment();
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

    private function financialRedirectBase(int $month, int $year): string
    {
        return Config::get('APP_URL', '') . '/dashboard.php?action=therapist-financial&month=' . $month . '&year=' . $year;
    }

    private function parseMoneyInput(string $value): float
    {
        $raw = trim($value);
        if ($raw === '') {
            return 0.0;
        }

        $normalized = preg_replace('/[^0-9,\.]/', '', $raw) ?? '';
        if ($normalized === '') {
            return 0.0;
        }

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return (float) $normalized;
    }

    public function financial(): void
    {
        $therapistId = (int) Auth::id();
        $month = $this->normalizeMonth((int) ($_GET['month'] ?? date('n')));
        $year = $this->normalizeYear((int) ($_GET['year'] ?? date('Y')));

        $rows = $this->paymentModel->listAppointmentFinancialMonthly($therapistId, $month, $year);
        foreach ($rows as &$row) {
            if (empty($row['payment_id'])) {
                $newId = $this->paymentModel->ensurePendingForAppointment(
                    $therapistId,
                    (int) $row['appointment_id'],
                    isset($row['patient_id']) ? (int) $row['patient_id'] : null
                );
                $row['payment_id'] = $newId ?: null;
                $row['payment_status'] = 'pending';
                $row['amount'] = 0.00;
            }
        }
        unset($row);

        $received = 0.0;
        $pending = 0.0;
        $appliedAmounts = [];

        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            if ($amount > 0) {
                $appliedAmounts[] = $amount;
            }

            $status = (string) ($row['payment_status'] ?? 'pending');
            if ($status === 'paid') {
                $received += $amount;
            }
            if ($status === 'pending') {
                $pending += $amount;
            }
        }

        $appointmentsCount = count($rows);
        $averageTicket = count($appliedAmounts) > 0 ? (array_sum($appliedAmounts) / count($appliedAmounts)) : 0.0;
        $estimatedRevenue = $averageTicket * $appointmentsCount;

        $monthNames = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Marco',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        $this->view('therapist/financial', [
            'appUrl' => Config::get('APP_URL', ''),
            'month' => $month,
            'year' => $year,
            'monthLabel' => ($monthNames[$month] ?? '') . ' de ' . $year,
            'financialRows' => $rows,
            'receivedTotal' => $received,
            'pendingTotal' => $pending,
            'appointmentsCount' => $appointmentsCount,
            'averageTicket' => $averageTicket,
            'estimatedRevenue' => $estimatedRevenue,
        ]);
    }

    public function financialUpdate(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
        $patientIdRaw = $_POST['patient_id'] ?? null;
        $patientId = ($patientIdRaw === '' || $patientIdRaw === null) ? null : (int) $patientIdRaw;
        $status = (string) ($_POST['status'] ?? 'pending');
        $amount = $this->parseMoneyInput((string) ($_POST['amount'] ?? '0'));

        $month = $this->normalizeMonth((int) ($_POST['month'] ?? date('n')));
        $year = $this->normalizeYear((int) ($_POST['year'] ?? date('Y')));
        $redirectBase = $this->financialRedirectBase($month, $year);
        $redirectWithStatus = static function (string $baseUrl, string $type, string $message): string {
            return $baseUrl . '&status=' . urlencode($type) . '&msg=' . urlencode($message);
        };

        if ($appointmentId <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Agendamento inválido.'));
        }

        if (!in_array($status, ['pending', 'paid'], true)) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Status de pagamento inválido.'));
        }

        if ($amount < 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Valor da consulta inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Agendamento não encontrado.'));
        }

        $ok = $this->paymentModel->upsertAppointmentPayment($therapistId, $appointmentId, $patientId, $amount, $status);
        if (!$ok) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao atualizar pagamento.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Pagamento atualizado com sucesso.'));
    }

    public function financialConfirmPayment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
        $month = $this->normalizeMonth((int) ($_POST['month'] ?? date('n')));
        $year = $this->normalizeYear((int) ($_POST['year'] ?? date('Y')));

        $redirectBase = $this->financialRedirectBase($month, $year);
        $redirectWithStatus = static function (string $baseUrl, string $type, string $message): string {
            return $baseUrl . '&status=' . urlencode($type) . '&msg=' . urlencode($message);
        };

        if ($appointmentId <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Agendamento inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Agendamento não encontrado.'));
        }

        $payment = $this->paymentModel->findByAppointmentId($appointmentId);
        if (!$payment || (float) ($payment['amount'] ?? 0) <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Defina o valor da consulta antes de confirmar pagamento.'));
        }

        $ok = $this->paymentModel->confirmPaymentByAppointment($therapistId, $appointmentId);
        if (!$ok) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao confirmar pagamento.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Pagamento confirmado como pago.'));
    }

    private function normalizeMaterialType(string $type): string
    {
        return in_array($type, ['support', 'exercise'], true) ? $type : 'support';
    }

    private function materialUploadBasePath(): string
    {
        $uploadBase = dirname(__DIR__, 2) . '/uploads/materials';
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0775, true);
        }
        return $uploadBase;
    }

    private function detectMaterialAssetType(string $extension, string $mimeType): string
    {
        $extension = strtolower($extension);
        $mimeType = strtolower($mimeType);

        if ($extension === 'pdf' || str_contains($mimeType, 'pdf')) {
            return 'pdf';
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'], true) || str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (in_array($extension, ['mp4', 'webm', 'mov', 'avi', 'mkv'], true) || str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return '';
    }

    private function saveMaterialAssetsFromRequest(int $materialId): void
    {
        $uploadBase = $this->materialUploadBasePath();

        if (isset($_FILES['material_files'])) {
            $rawNames = $_FILES['material_files']['name'] ?? [];
            $rawTmpNames = $_FILES['material_files']['tmp_name'] ?? [];
            $rawSizes = $_FILES['material_files']['size'] ?? [];
            $rawErrors = $_FILES['material_files']['error'] ?? [];
            $rawTypes = $_FILES['material_files']['type'] ?? [];

            $names = is_array($rawNames) ? array_values($rawNames) : [$rawNames];
            $tmpNames = is_array($rawTmpNames) ? array_values($rawTmpNames) : [$rawTmpNames];
            $sizes = is_array($rawSizes) ? array_values($rawSizes) : [$rawSizes];
            $errors = is_array($rawErrors) ? array_values($rawErrors) : [$rawErrors];
            $types = is_array($rawTypes) ? array_values($rawTypes) : [$rawTypes];

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
                $mimeType = (string) ($types[$idx] ?? '');
                $ext = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
                $assetType = $this->detectMaterialAssetType($ext, $mimeType);
                if ($assetType === '') {
                    continue;
                }

                $safeFile = uniqid('material_', true) . ($ext !== '' ? ('.' . $ext) : '');
                $target = $uploadBase . '/' . $safeFile;
                if (!@move_uploaded_file($tmpName, $target)) {
                    continue;
                }

                $this->materialModel->insertAsset([
                    'material_id' => $materialId,
                    'asset_type' => $assetType,
                    'file_name' => (string) $originalName,
                    'file_path' => 'uploads/materials/' . $safeFile,
                    'file_url' => null,
                    'mime_type' => $mimeType,
                    'file_size' => $size,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $linksRaw = trim((string) ($_POST['material_links'] ?? ''));
        if ($linksRaw !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $linksRaw) ?: [];
            foreach ($lines as $line) {
                $url = trim((string) $line);
                if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $this->materialModel->insertAsset([
                    'material_id' => $materialId,
                    'asset_type' => 'url',
                    'file_name' => $url,
                    'file_path' => null,
                    'file_url' => $url,
                    'mime_type' => null,
                    'file_size' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function deleteMaterialAssetFileIfExists(array $asset): void
    {
        $relative = trim((string) ($asset['file_path'] ?? ''));
        if ($relative === '') {
            return;
        }

        $absolute = dirname(__DIR__, 2) . '/' . ltrim($relative, '/');
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    public function materials(): void
    {
        $therapistId = (int) Auth::id();
        $term = Utils::sanitize($_GET['search'] ?? '');
        $materials = $this->materialModel->listByTherapist($therapistId, $term);
        $patients = $this->patientModel->searchByTherapist($therapistId);

        $this->view('therapist/materials/index', [
            'appUrl' => Config::get('APP_URL', ''),
            'materials' => $materials,
            'patients' => $patients,
            'search' => $term,
        ]);
    }

    public function createMaterial(): void
    {
        $this->view('therapist/materials/create', ['appUrl' => Config::get('APP_URL', '')]);
    }

    public function storeMaterial(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $therapistId = (int) Auth::id();
        $title = Utils::sanitize($_POST['title'] ?? '');
        $type = $this->normalizeMaterialType((string) ($_POST['type'] ?? 'support'));
        $descriptionHtml = $this->sanitizeRichText((string) ($_POST['description_html'] ?? ''));
        $customHtml = trim((string) ($_POST['custom_html'] ?? ''));

        $redirectBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($title === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Título é obrigatório.'));
        }

        $materialId = $this->materialModel->insert([
            'therapist_id' => $therapistId,
            'title' => $title,
            'type' => $type,
            'description_html' => $descriptionHtml,
            'custom_html' => $customHtml !== '' ? $customHtml : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$materialId) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao cadastrar material.'));
        }

        $this->saveMaterialAssetsFromRequest((int) $materialId);

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Material cadastrado com sucesso.'));
    }

    public function showMaterial(): void
    {
        $therapistId = (int) Auth::id();
        $materialId = (int) ($_GET['id'] ?? 0);
        $material = $this->materialModel->findByTherapistAndId($therapistId, $materialId);

        if (!$material) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials&status=error&msg=' . urlencode('Material não encontrado.'));
        }

        $assets = $this->materialModel->listAssets($materialId);
        $deliveries = $this->materialDeliveryModel->listByMaterial($materialId);

        $this->view('therapist/materials/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'material' => $material,
            'assets' => $assets,
            'deliveries' => $deliveries,
        ]);
    }

    public function editMaterial(): void
    {
        $therapistId = (int) Auth::id();
        $materialId = (int) ($_GET['id'] ?? 0);
        $material = $this->materialModel->findByTherapistAndId($therapistId, $materialId);

        if (!$material) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials&status=error&msg=' . urlencode('Material não encontrado.'));
        }

        $assets = $this->materialModel->listAssets($materialId);
        $this->view('therapist/materials/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'material' => $material,
            'assets' => $assets,
        ]);
    }

    public function updateMaterial(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $therapistId = (int) Auth::id();
        $materialId = (int) ($_POST['id'] ?? 0);
        $material = $this->materialModel->findByTherapistAndId($therapistId, $materialId);
        $redirectBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$material) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Material não encontrado.'));
        }

        $title = Utils::sanitize($_POST['title'] ?? '');
        $type = $this->normalizeMaterialType((string) ($_POST['type'] ?? 'support'));
        $descriptionHtml = $this->sanitizeRichText((string) ($_POST['description_html'] ?? ''));
        $customHtml = trim((string) ($_POST['custom_html'] ?? ''));

        if ($title === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Título é obrigatório.'));
        }

        $updated = $this->materialModel->updateById($materialId, [
            'title' => $title,
            'type' => $type,
            'description_html' => $descriptionHtml,
            'custom_html' => $customHtml !== '' ? $customHtml : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao atualizar material.'));
        }

        $removeAssetIds = $_POST['remove_asset_ids'] ?? [];
        if (!is_array($removeAssetIds)) {
            $removeAssetIds = [];
        }

        foreach ($removeAssetIds as $assetIdRaw) {
            $assetId = (int) $assetIdRaw;
            if ($assetId <= 0) {
                continue;
            }

            $asset = $this->materialModel->findAssetByTherapistAndId($therapistId, $assetId);
            if (!$asset) {
                continue;
            }

            $this->deleteMaterialAssetFileIfExists($asset);
            $this->materialModel->deleteAssetById($assetId);
        }

        $this->saveMaterialAssetsFromRequest($materialId);

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Material atualizado com sucesso.'));
    }

    public function deleteMaterial(): void
    {
        $therapistId = (int) Auth::id();
        $materialId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $material = $this->materialModel->findByTherapistAndId($therapistId, $materialId);
        $redirectBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$material) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Material não encontrado.'));
        }

        $assets = $this->materialModel->listAssets($materialId);
        foreach ($assets as $asset) {
            $this->deleteMaterialAssetFileIfExists($asset);
        }

        $deleted = $this->materialModel->deleteByTherapistAndId($therapistId, $materialId);
        if (!$deleted) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao excluir material.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Material excluído com sucesso.'));
    }

    public function sendMaterial(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials&status=error&msg=' . urlencode('Método não permitido.'));
        }

        $therapistId = (int) Auth::id();
        $materialId = (int) ($_POST['material_id'] ?? 0);
        $message = Utils::sanitize($_POST['message'] ?? '');
        $patientIds = $_POST['patient_ids'] ?? [];
        if (!is_array($patientIds)) {
            $patientIds = [];
        }

        $redirectBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-materials';
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        $material = $this->materialModel->findByTherapistAndId($therapistId, $materialId);
        if (!$material) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Material não encontrado.'));
        }

        if (empty($patientIds)) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Selecione ao menos um paciente para encaminhar.'));
        }

        $allowedPatients = $this->patientModel->searchByTherapist($therapistId);
        $allowedMap = [];
        foreach ($allowedPatients as $patient) {
            $allowedMap[(int) $patient['id']] = true;
        }

        $safePatientIds = [];
        foreach ($patientIds as $patientIdRaw) {
            $patientId = (int) $patientIdRaw;
            if ($patientId > 0 && isset($allowedMap[$patientId])) {
                $safePatientIds[] = $patientId;
            }
        }
        $safePatientIds = array_values(array_unique($safePatientIds));

        if (empty($safePatientIds)) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Nenhum paciente válido selecionado.'));
        }

        $sent = $this->materialDeliveryModel->sendToPatients($therapistId, $materialId, $safePatientIds, $message);
        if ($sent <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao encaminhar material.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Material encaminhado para ' . $sent . ' paciente(s).'));
    }

    private function normalizeMonth(int $month): int
    {
        return max(1, min(12, $month));
    }

    private function normalizeYear(int $year): int
    {
        return max(2000, min(2100, $year));
    }

    private function normalizeViewMode(string $view): string
    {
        $allowed = ['week', 'day'];
        return in_array($view, $allowed, true) ? $view : 'week';
    }

    private function sanitizeDateParam(string $date): string
    {
        $value = trim($date);
        if ($value === '') {
            return date('Y-m-d');
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return date('Y-m-d');
        }

        return date('Y-m-d', $timestamp);
    }

    private function buildAppointmentsByDate(array $appointments): array
    {
        $appointmentsByDate = [];
        foreach ($appointments as $appointment) {
            $dateKey = date('Y-m-d', strtotime((string) $appointment['session_date']));
            if (!isset($appointmentsByDate[$dateKey])) {
                $appointmentsByDate[$dateKey] = [];
            }
            $appointmentsByDate[$dateKey][] = $appointment;
        }

        return $appointmentsByDate;
    }

    private function buildMonthlyCalendarGrid(int $year, int $month, array $appointments): array
    {
        $firstOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $firstWeekday = (int) date('N', strtotime($firstOfMonth));
        $daysInMonth = (int) date('t', strtotime($firstOfMonth));
        $weeks = [];
        $week = [];
        $appointmentsByDate = $this->buildAppointmentsByDate($appointments);

        for ($i = 1; $i < $firstWeekday; $i++) {
            $week[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $week[] = [
                'day' => $day,
                'date' => $date,
                'appointments' => $appointmentsByDate[$date] ?? [],
                'isToday' => $date === date('Y-m-d'),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        if (!empty($week)) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    private function buildWeeklyCalendarGrid(string $weekStartDate, array $appointments): array
    {
        $appointmentsByDate = $this->buildAppointmentsByDate($appointments);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime($weekStartDate . ' +' . $i . ' day'));
            $days[] = [
                'dayLabel' => ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'][$i],
                'date' => $currentDate,
                'day' => (int) date('d', strtotime($currentDate)),
                'appointments' => $appointmentsByDate[$currentDate] ?? [],
                'isToday' => $currentDate === date('Y-m-d'),
            ];
        }

        return $days;
    }

    private function buildDailyHoursGrid(string $date, array $appointments): array
    {
        $appointmentsByHour = [];
        foreach ($appointments as $appointment) {
            $hour = (int) date('G', strtotime((string) $appointment['session_date']));
            if (!isset($appointmentsByHour[$hour])) {
                $appointmentsByHour[$hour] = [];
            }
            $appointmentsByHour[$hour][] = $appointment;
        }

        $hours = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hours[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'appointments' => $appointmentsByHour[$hour] ?? [],
                'isCurrentHour' => ($date === date('Y-m-d') && (int) date('G') === $hour),
            ];
        }

        return $hours;
    }

    private function scheduleRedirectBaseFromParams(string $viewMode, string $date): string
    {
        return Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=' . $viewMode . '&date=' . $date;
    }

    private function appointmentDisplayName(array $appointment): string
    {
        $patientName = trim((string) ($appointment['patient_name'] ?? ''));
        if ($patientName !== '') {
            return $patientName;
        }

        $guestName = trim((string) ($appointment['guest_patient_name'] ?? ''));
        if ($guestName !== '') {
            return $guestName;
        }

        return 'Paciente sem cadastro';
    }

    public function schedule(): void
    {
        $therapistId = (int) Auth::id();
        $viewMode = $this->normalizeViewMode((string) ($_GET['view'] ?? 'week'));
        $month = $this->normalizeMonth((int) ($_GET['month'] ?? date('n')));
        $year = $this->normalizeYear((int) ($_GET['year'] ?? date('Y')));
        $selectedDate = $this->sanitizeDateParam((string) ($_GET['date'] ?? date('Y-m-d')));

        $startDate = '';
        $endDate = '';
        $monthLabel = '';
        $calendarWeeks = [];
        $calendarWeekDays = [];
        $calendarDayHours = [];

        if ($viewMode === 'week') {
            $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
            $startDate = $weekStart;
            $endDate = date('Y-m-d', strtotime($weekStart . ' +6 day'));
            $appointments = $this->appointmentModel->calendarByTherapist($therapistId, $startDate, $endDate);
            $calendarWeekDays = $this->buildWeeklyCalendarGrid($weekStart, $appointments);
        } else {
            $startDate = $selectedDate;
            $endDate = $selectedDate;
            $appointments = $this->appointmentModel->calendarByTherapist($therapistId, $startDate, $endDate);
            $calendarDayHours = $this->buildDailyHoursGrid($selectedDate, $appointments);
        }

        $patients = $this->patientModel->searchByTherapist($therapistId);

        if ($viewMode === 'week') {
            $monthLabel = 'Semana de ' . date('d/m', strtotime($startDate)) . ' a ' . date('d/m/Y', strtotime($endDate));
        } else {
            $weekNames = [
                'Sunday' => 'Domingo',
                'Monday' => 'Segunda-feira',
                'Tuesday' => 'Terca-feira',
                'Wednesday' => 'Quarta-feira',
                'Thursday' => 'Quinta-feira',
                'Friday' => 'Sexta-feira',
                'Saturday' => 'Sabado',
            ];
            $dayName = $weekNames[date('l', strtotime($startDate))] ?? '';
            $monthLabel = $dayName . ', ' . date('d/m/Y', strtotime($startDate));
        }

        $month = (int) date('n', strtotime($startDate));
        $year = (int) date('Y', strtotime($startDate));

        if ($viewMode === 'week') {
            $previousAnchor = date('Y-m-d', strtotime($startDate . ' -7 day'));
            $nextAnchor = date('Y-m-d', strtotime($startDate . ' +7 day'));
        } else {
            $previousAnchor = date('Y-m-d', strtotime($startDate . ' -1 day'));
            $nextAnchor = date('Y-m-d', strtotime($startDate . ' +1 day'));
        }

        $previousUrl = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=' . $viewMode . '&date=' . $previousAnchor;
        $nextUrl = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=' . $viewMode . '&date=' . $nextAnchor;

        $weekViewUrl = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=week&date=' . $startDate;
        $dayViewUrl = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=day&date=' . $startDate;

        $this->view('therapist/schedule', [
            'appUrl' => Config::get('APP_URL', ''),
            'month' => $month,
            'year' => $year,
            'viewMode' => $viewMode,
            'selectedDate' => $startDate,
            'monthLabel' => $monthLabel,
            'calendarWeeks' => $calendarWeeks,
            'calendarWeekDays' => $calendarWeekDays,
            'calendarDayHours' => $calendarDayHours,
            'patients' => $patients,
            'previousUrl' => $previousUrl,
            'nextUrl' => $nextUrl,
            'weekViewUrl' => $weekViewUrl,
            'dayViewUrl' => $dayViewUrl,
        ]);
    }

    public function storeScheduleAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentAtRaw = trim((string) ($_POST['appointment_at'] ?? ''));
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $newPatientName = Utils::sanitize($_POST['new_patient_name'] ?? '');
        $description = Utils::sanitize($_POST['description'] ?? '');

        $selectedMonth = $this->normalizeMonth((int) ($_POST['month'] ?? date('n')));
        $selectedYear = $this->normalizeYear((int) ($_POST['year'] ?? date('Y')));
        $selectedView = $this->normalizeViewMode((string) ($_POST['view_mode'] ?? 'week'));
        $selectedDate = $this->sanitizeDateParam((string) ($_POST['date'] ?? date('Y-m-d')));

        $redirectBase = Config::get('APP_URL', '') . '/dashboard.php?action=therapist-schedule&view=' . $selectedView . '&month=' . $selectedMonth . '&year=' . $selectedYear . '&date=' . $selectedDate;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($appointmentAtRaw === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Data e hora são obrigatórias.'));
        }

        $appointmentTimestamp = strtotime($appointmentAtRaw);
        if ($appointmentTimestamp === false) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Data e hora inválidas.'));
        }

        if ($patientId <= 0 && $newPatientName === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Selecione um paciente ou informe um novo paciente.'));
        }

        if ($patientId > 0) {
            $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
            if (!$patient) {
                $this->redirect($redirectWithStatus($redirectBase, 'error', 'Paciente selecionado inválido.'));
            }
            $newPatientName = '';
        }

        $sessionDate = date('Y-m-d H:i:s', $appointmentTimestamp);

        if ($this->appointmentModel->hasConflictForTherapist($therapistId, $sessionDate)) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Já existe um compromisso nesse horário.'));
        }

        if ($description === '') {
            $description = $newPatientName !== '' ? 'Compromisso com ' . $newPatientName : 'Compromisso agendado';
        }

        $created = $this->appointmentModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId > 0 ? $patientId : null,
            'guest_patient_name' => $newPatientName !== '' ? $newPatientName : null,
            'session_date' => $sessionDate,
            'description' => $description,
            'history' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$created) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao cadastrar compromisso.'));
        }

        $this->paymentModel->ensurePendingForAppointment($therapistId, (int) $created, $patientId > 0 ? $patientId : null);

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Compromisso cadastrado com sucesso.'));
    }

    public function showScheduleAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_GET['id'] ?? 0);
        $viewMode = $this->normalizeViewMode((string) ($_GET['view'] ?? 'week'));
        $date = $this->sanitizeDateParam((string) ($_GET['date'] ?? date('Y-m-d')));
        $backUrl = $this->scheduleRedirectBaseFromParams($viewMode, $date);

        if ($appointmentId <= 0) {
            $this->redirect($backUrl . '&status=error&msg=' . urlencode('Compromisso inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($backUrl . '&status=error&msg=' . urlencode('Compromisso não encontrado.'));
        }

        $appointment['display_name'] = $this->appointmentDisplayName($appointment);

        $this->view('therapist/schedule-show', [
            'appUrl' => Config::get('APP_URL', ''),
            'appointment' => $appointment,
            'viewMode' => $viewMode,
            'date' => $date,
            'backUrl' => $backUrl,
        ]);
    }

    public function editScheduleAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_GET['id'] ?? 0);
        $viewMode = $this->normalizeViewMode((string) ($_GET['view'] ?? 'week'));
        $date = $this->sanitizeDateParam((string) ($_GET['date'] ?? date('Y-m-d')));
        $backUrl = $this->scheduleRedirectBaseFromParams($viewMode, $date);

        if ($appointmentId <= 0) {
            $this->redirect($backUrl . '&status=error&msg=' . urlencode('Compromisso inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($backUrl . '&status=error&msg=' . urlencode('Compromisso não encontrado.'));
        }

        $patients = $this->patientModel->searchByTherapist($therapistId);

        $this->view('therapist/schedule-edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'appointment' => $appointment,
            'patients' => $patients,
            'viewMode' => $viewMode,
            'date' => $date,
            'backUrl' => $backUrl,
        ]);
    }

    public function updateScheduleAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_POST['id'] ?? 0);
        $viewMode = $this->normalizeViewMode((string) ($_POST['view_mode'] ?? 'week'));
        $date = $this->sanitizeDateParam((string) ($_POST['date'] ?? date('Y-m-d')));
        $redirectBase = $this->scheduleRedirectBaseFromParams($viewMode, $date);
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($appointmentId <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Compromisso inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Compromisso não encontrado.'));
        }

        $appointmentAtRaw = trim((string) ($_POST['appointment_at'] ?? ''));
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $newPatientName = Utils::sanitize($_POST['new_patient_name'] ?? '');
        $description = Utils::sanitize($_POST['description'] ?? '');
        $history = $this->sanitizeRichText((string) ($_POST['history'] ?? ''));

        if ($appointmentAtRaw === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Data e hora são obrigatórias.'));
        }

        $appointmentTimestamp = strtotime($appointmentAtRaw);
        if ($appointmentTimestamp === false) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Data e hora inválidas.'));
        }

        if ($patientId <= 0 && $newPatientName === '') {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Selecione um paciente ou informe um novo paciente.'));
        }

        if ($patientId > 0) {
            $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
            if (!$patient) {
                $this->redirect($redirectWithStatus($redirectBase, 'error', 'Paciente selecionado inválido.'));
            }
            $newPatientName = '';
        }

        $sessionDate = date('Y-m-d H:i:s', $appointmentTimestamp);
        if ($this->appointmentModel->hasConflictForTherapist($therapistId, $sessionDate, $appointmentId)) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Já existe um compromisso nesse horário.'));
        }

        if ($description === '') {
            $description = $newPatientName !== '' ? 'Compromisso com ' . $newPatientName : 'Compromisso agendado';
        }

        $updated = $this->appointmentModel->updateById($appointmentId, [
            'patient_id' => $patientId > 0 ? $patientId : null,
            'guest_patient_name' => $newPatientName !== '' ? $newPatientName : null,
            'session_date' => $sessionDate,
            'description' => $description,
            'history' => $history !== '' ? $history : null,
        ]);

        if (!$updated) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao atualizar compromisso.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Compromisso atualizado com sucesso.'));
    }

    public function deleteScheduleAppointment(): void
    {
        $therapistId = (int) Auth::id();
        $appointmentId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $viewMode = $this->normalizeViewMode((string) ($_POST['view_mode'] ?? $_GET['view'] ?? 'week'));
        $date = $this->sanitizeDateParam((string) ($_POST['date'] ?? $_GET['date'] ?? date('Y-m-d')));
        $redirectBase = $this->scheduleRedirectBaseFromParams($viewMode, $date);
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if ($appointmentId <= 0) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Compromisso inválido.'));
        }

        $appointment = $this->appointmentModel->findByTherapistAndId($therapistId, $appointmentId);
        if (!$appointment) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Compromisso não encontrado.'));
        }

        $deleted = $this->appointmentModel->deleteByTherapistAndId($therapistId, $appointmentId);
        if (!$deleted) {
            $this->redirect($redirectWithStatus($redirectBase, 'error', 'Falha ao excluir compromisso.'));
        }

        $this->redirect($redirectWithStatus($redirectBase, 'success', 'Compromisso excluído com sucesso.'));
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

    public function passwordPatient(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['id'] ?? 0);
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);

        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients&status=error&msg=' . urlencode('Paciente não encontrado.'));
        }

        $patientAccess = $this->userModel->findPatientAccessByTherapistAndPatient($therapistId, $patientId);

        $this->view('therapist/patients/password', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'patientAccess' => $patientAccess,
        ]);
    }

    public function updatePasswordPatient(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $patientAccess = $this->userModel->findPatientAccessByTherapistAndPatient($therapistId, $patientId);

        $redirectListBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients';
        $redirectPasswordBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-password&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient) {
            if ($isAjax) {
                $this->error('Paciente não encontrado', 404);
            }
            $this->redirect($redirectWithStatus($redirectListBase, 'error', 'Paciente não encontrado.'));
        }

        if (trim($password) === '') {
            if ($isAjax) {
                $this->error('Informe uma senha válida');
            }
            $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Informe uma senha válida.'));
        }

        $patientEmail = trim((string) ($patient['email'] ?? ''));
        if ($patientAccess === null) {
            if ($patientEmail === '' || !Utils::isValidEmail($patientEmail)) {
                if ($isAjax) {
                    $this->error('Cadastre um e-mail válido no paciente antes de criar o acesso');
                }
                $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Cadastre um e-mail válido no paciente antes de criar o acesso.'));
            }

            $existingUserByEmail = $this->userModel->findByEmail($patientEmail);
            if ($existingUserByEmail && ((int) ($existingUserByEmail['patient_id'] ?? 0) !== $patientId || (string) ($existingUserByEmail['role'] ?? '') !== 'patient')) {
                if ($isAjax) {
                    $this->error('O e-mail do paciente já está em uso por outro usuário');
                }
                $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'O e-mail do paciente já está em uso por outro usuário.'));
            }

            $created = $this->userModel->insert([
                'name' => (string) ($patient['name'] ?? ''),
                'cpf' => (string) ($patient['cpf'] ?? ''),
                'phone' => (string) ($patient['phone'] ?? ''),
                'email' => $patientEmail,
                'password' => Utils::hashPassword($password),
                'role' => 'patient',
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$created) {
                if ($isAjax) {
                    $this->error('Falha ao criar acesso do paciente');
                }
                $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Falha ao criar acesso do paciente.'));
            }

            if ($isAjax) {
                $this->success('Acesso do paciente criado', ['redirect' => $redirectListBase]);
            }

            $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Acesso do paciente criado com sucesso.'));
        }

        $updated = $this->userModel->updateById((int) $patientAccess['id'], [
            'password' => Utils::hashPassword($password),
            'name' => (string) ($patient['name'] ?? ''),
            'cpf' => (string) ($patient['cpf'] ?? ''),
            'phone' => (string) ($patient['phone'] ?? ''),
            'email' => $patientEmail !== '' ? $patientEmail : (string) ($patientAccess['email'] ?? ''),
            'status' => 'active',
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao redefinir senha do paciente');
            }
            $this->redirect($redirectWithStatus($redirectPasswordBase, 'error', 'Falha ao redefinir senha do paciente.'));
        }

        if ($isAjax) {
            $this->success('Senha do paciente alterada', ['redirect' => $redirectListBase]);
        }

        $this->redirect($redirectWithStatus($redirectListBase, 'success', 'Senha do paciente alterada com sucesso.'));
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
        $taskFiles = $this->fileModel->listByPatientGroupedByTask($patientId);
        $materials = $this->materialModel->listByTherapist($therapistId);
        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $tasks);
        $taskLinkedMaterials = $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds);

        $this->view('therapist/patients/history', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'appointments' => $appointments,
            'tasks' => $tasks,
            'taskFiles' => $taskFiles,
            'materials' => $materials,
            'taskLinkedMaterials' => $taskLinkedMaterials,
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

        $this->paymentModel->ensurePendingForAppointment($therapistId, (int) $inserted, $patientId);

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

    private function storeTaskAttachments(int $therapistId, int $patientId, int $taskId, string $sourceRole = 'therapist'): void
    {
        $sourceRole = $sourceRole === 'patient' ? 'patient' : 'therapist';
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
                'source_role' => $sourceRole,
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
                'source_role' => $sourceRole,
                'file_name' => (string) $originalName,
                'file_path' => $relativePath,
                'file_type' => $fileType,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function normalizeDeliveryKind(string $kind): string
    {
        return $kind === 'material' ? 'material' : 'task';
    }

    private function dispatchTaskDeliveryAlert(array $patient, array $task, string $deliveryKind, array $channels): string
    {
        if (empty($task['send_to_patient'])) {
            return 'envio interno';
        }

        $label = $deliveryKind === 'material' ? 'material' : 'tarefa';
        $message = 'Você recebeu um novo ' . $label . ': "' . (string) ($task['title'] ?? 'Sem título') . '".';

        $report = AlertDispatcher::dispatch(
            $channels,
            (string) ($patient['email'] ?? ''),
            (string) ($patient['phone'] ?? ''),
            'Novo conteúdo disponível no portal do paciente',
            $message
        );

        return AlertDispatcher::summarize($report);
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
        $deliveryKind = $this->normalizeDeliveryKind((string) ($_POST['delivery_kind'] ?? 'task'));
        $notifyChannels = $_POST['notify_channels'] ?? ['email', 'whatsapp'];
        if (!is_array($notifyChannels)) {
            $notifyChannels = ['email', 'whatsapp'];
        }
        $materialIds = $_POST['material_ids'] ?? [];
        if (!is_array($materialIds)) {
            $materialIds = [];
        }
        $status = Utils::sanitize($_POST['status'] ?? 'pending');
        if (!in_array($status, ['pending', 'done'], true)) {
            $status = 'pending';
        }

        $validatedMaterialIds = [];
        foreach ($materialIds as $materialIdRaw) {
            $materialId = (int) $materialIdRaw;
            if ($materialId <= 0) {
                continue;
            }
            $selectedMaterial = $this->materialModel->findByTherapistAndId($therapistId, $materialId);
            if ($selectedMaterial) {
                $validatedMaterialIds[] = $materialId;
            }
        }

        if ($dueDate === '' || $title === '' || $description === '') {
            if ($isAjax) {
                $this->error('Data, título e descrição são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Data, título e descrição são obrigatórios.'));
        }

        if ($deliveryKind === 'material' && empty($validatedMaterialIds)) {
            if ($isAjax) {
                $this->error('Selecione ao menos um material para envio de material');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Selecione ao menos um material para envio de material.'));
        }

        $taskId = $this->taskModel->insert([
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'material_id' => !empty($validatedMaterialIds) ? (int) reset($validatedMaterialIds) : null,
            'due_date' => $dueDate,
            'title' => $title,
            'description' => $description,
            'send_to_patient' => $sendToPatient,
            'delivery_kind' => $deliveryKind,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$taskId) {
            if ($isAjax) {
                $this->error('Falha ao cadastrar tarefa');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Falha ao cadastrar tarefa.'));
        }

        $this->taskModel->syncLinkedMaterials((int) $taskId, $validatedMaterialIds);
        $this->storeTaskAttachments($therapistId, $patientId, (int) $taskId);

        $taskPayload = [
            'send_to_patient' => $sendToPatient,
            'title' => $title,
        ];
        $alertSummary = $this->dispatchTaskDeliveryAlert($patient, $taskPayload, $deliveryKind, $notifyChannels);

        if ($isAjax) {
            $this->success('Tarefa cadastrada', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Tarefa cadastrada com sucesso. Alertas: ' . $alertSummary . '.'));
    }

    public function showPatientTask(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        $taskId = (int) ($_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $task = $this->taskModel->findByTherapistPatientAndId($therapistId, $patientId, $taskId);
        if (!$task) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId . '&status=error&msg=' . urlencode('Tarefa não encontrada.'));
        }

        $files = $this->fileModel->listByTaskAndSourceRole($taskId, 'therapist');
        $patientResponseFiles = $this->fileModel->listByTaskAndSourceRole($taskId, 'patient');
        $linkedMaterials = $this->taskModel->listLinkedMaterials($taskId);
        $linkedMaterialAssets = [];
        foreach ($linkedMaterials as $linkedMaterial) {
            $linkedMaterialAssets[(int) $linkedMaterial['id']] = $this->materialModel->listAssets((int) $linkedMaterial['id']);
        }

        $this->view('therapist/patients/tasks/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'task' => $task,
            'files' => $files,
            'patientResponseFiles' => $patientResponseFiles,
            'linkedMaterials' => $linkedMaterials,
            'linkedMaterialAssets' => $linkedMaterialAssets,
        ]);
    }

    public function editPatientTask(): void
    {
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_GET['patient_id'] ?? 0);
        $taskId = (int) ($_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients');
        }

        $task = $this->taskModel->findByTherapistPatientAndId($therapistId, $patientId, $taskId);
        if (!$task) {
            $this->redirect(Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId . '&status=error&msg=' . urlencode('Tarefa não encontrada.'));
        }

        $materials = $this->materialModel->listByTherapist($therapistId);
        $linkedMaterials = $this->taskModel->listLinkedMaterials($taskId);

        $this->view('therapist/patients/tasks/edit', [
            'appUrl' => Config::get('APP_URL', ''),
            'patient' => $patient,
            'task' => $task,
            'materials' => $materials,
            'linkedMaterials' => $linkedMaterials,
        ]);
    }

    public function updatePatientTask(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $taskId = (int) ($_POST['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $task = $this->taskModel->findByTherapistPatientAndId($therapistId, $patientId, $taskId);

        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectEditBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-tasks-edit&patient_id=' . $patientId . '&id=' . $taskId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient || !$task) {
            if ($isAjax) {
                $this->error('Tarefa não encontrada', 404);
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Tarefa não encontrada.'));
        }

        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        $title = Utils::sanitize($_POST['title'] ?? '');
        $description = $this->sanitizeRichText((string) ($_POST['description'] ?? ''));
        $sendToPatient = isset($_POST['send_to_patient']) ? 1 : 0;
        $deliveryKind = $this->normalizeDeliveryKind((string) ($_POST['delivery_kind'] ?? ($task['delivery_kind'] ?? 'task')));
        $notifyChannels = $_POST['notify_channels'] ?? ['email', 'whatsapp'];
        if (!is_array($notifyChannels)) {
            $notifyChannels = ['email', 'whatsapp'];
        }
        $materialIds = $_POST['material_ids'] ?? [];
        if (!is_array($materialIds)) {
            $materialIds = [];
        }
        $status = Utils::sanitize($_POST['status'] ?? 'pending');
        if (!in_array($status, ['pending', 'done'], true)) {
            $status = 'pending';
        }

        $validatedMaterialIds = [];
        foreach ($materialIds as $materialIdRaw) {
            $materialId = (int) $materialIdRaw;
            if ($materialId <= 0) {
                continue;
            }
            $selectedMaterial = $this->materialModel->findByTherapistAndId($therapistId, $materialId);
            if ($selectedMaterial) {
                $validatedMaterialIds[] = $materialId;
            }
        }

        if ($dueDate === '' || $title === '' || $description === '') {
            if ($isAjax) {
                $this->error('Data, título e descrição são obrigatórios');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Data, título e descrição são obrigatórios.'));
        }

        if ($deliveryKind === 'material' && empty($validatedMaterialIds)) {
            if ($isAjax) {
                $this->error('Selecione ao menos um material para envio de material');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Selecione ao menos um material para envio de material.'));
        }

        $updated = $this->taskModel->updateById($taskId, [
            'due_date' => $dueDate,
            'title' => $title,
            'description' => $description,
            'material_id' => !empty($validatedMaterialIds) ? (int) reset($validatedMaterialIds) : null,
            'send_to_patient' => $sendToPatient,
            'delivery_kind' => $deliveryKind,
            'status' => $status,
        ]);

        if (!$updated) {
            if ($isAjax) {
                $this->error('Falha ao atualizar tarefa');
            }
            $this->redirect($redirectWithStatus($redirectEditBase, 'error', 'Falha ao atualizar tarefa.'));
        }

        $this->taskModel->syncLinkedMaterials($taskId, $validatedMaterialIds);
        $this->storeTaskAttachments($therapistId, $patientId, $taskId);

        $taskPayload = [
            'send_to_patient' => $sendToPatient,
            'title' => $title,
        ];
        $alertSummary = $this->dispatchTaskDeliveryAlert($patient, $taskPayload, $deliveryKind, $notifyChannels);

        if ($isAjax) {
            $this->success('Tarefa atualizada', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Tarefa atualizada com sucesso. Alertas: ' . $alertSummary . '.'));
    }

    public function deletePatientTask(): void
    {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $therapistId = (int) Auth::id();
        $patientId = (int) ($_POST['patient_id'] ?? $_GET['patient_id'] ?? 0);
        $taskId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        $task = $this->taskModel->findByTherapistPatientAndId($therapistId, $patientId, $taskId);
        $redirectHistoryBase = Config::get('APP_URL', '') . '/dashboard.php?action=patients-history&id=' . $patientId;
        $redirectWithStatus = static function (string $baseUrl, string $status, string $message): string {
            return $baseUrl . '&status=' . urlencode($status) . '&msg=' . urlencode($message);
        };

        if (!$patient || !$task) {
            if ($isAjax) {
                $this->error('Tarefa não encontrada', 404);
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Tarefa não encontrada.'));
        }

        $deleted = $this->taskModel->deleteByTherapistPatientAndId($therapistId, $patientId, $taskId);
        if (!$deleted) {
            if ($isAjax) {
                $this->error('Falha ao excluir tarefa');
            }
            $this->redirect($redirectWithStatus($redirectHistoryBase, 'error', 'Falha ao excluir tarefa.'));
        }

        if ($isAjax) {
            $this->success('Tarefa excluída', ['redirect' => $redirectHistoryBase]);
        }

        $this->redirect($redirectWithStatus($redirectHistoryBase, 'success', 'Tarefa excluída com sucesso.'));
    }
}
