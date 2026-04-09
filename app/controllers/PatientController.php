<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Patient;
use App\Models\PatientRecord;
use App\Models\PatientTask;
use App\Models\TaskAttachment;
use App\Models\TherapistFile;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;
use Config\Config;

class PatientController extends Controller
{
    private Patient $patientModel;
    private PatientRecord $patientRecordModel;
    private PatientTask $patientTaskModel;
    private TaskAttachment $taskAttachmentModel;
    private TherapistFile $therapistFileModel;

    public function __construct()
    {
        Auth::requireTherapist();
        $this->patientModel = new Patient();
        $this->patientRecordModel = new PatientRecord();
        $this->patientTaskModel = new PatientTask();
        $this->taskAttachmentModel = new TaskAttachment();
        $this->therapistFileModel = new TherapistFile();
    }

    /**
     * Lista todos os pacientes
     */
    public function index(): void
    {
        $therapistId = Auth::therapistId();
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $patients = $this->patientModel->search($search, $offset, $limit, $therapistId);
        $totalPatients = $this->patientModel->countSearch($search, $therapistId);
        $totalPages = ceil($totalPatients / $limit);

        $this->view('admin/patients/index', [
            'patients' => $patients,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'totalPatients' => $totalPatients
        ]);
    }

    /**
     * Exibe formulário de criação de paciente
     */
    public function create(): void
    {
        $this->view('admin/patients/create');
    }

    /**
     * Processa criação de paciente
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $data = [
            'therapist_id' => Auth::therapistId(),
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'cpf' => Validator::removeCPFMask($_POST['cpf'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? '',
            'phone' => Validator::removePhoneMask($_POST['phone'] ?? ''),
            'email' => Utils::sanitize($_POST['email'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'number' => Utils::sanitize($_POST['number'] ?? ''),
            'complement' => Utils::sanitize($_POST['complement'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'cep' => Validator::removeCEPMask($_POST['cep'] ?? ''),
            'observations' => Utils::sanitize($_POST['observations'] ?? ''),
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'father' => Utils::sanitize($_POST['father'] ?? ''),
            'mother' => Utils::sanitize($_POST['mother'] ?? ''),
            'first_word' => Utils::sanitize($_POST['first_word'] ?? ''),
            'depression' => isset($_POST['depression']) ? 1 : 0,
            'anxiety' => isset($_POST['anxiety']) ? 1 : 0,
            'medications' => Utils::sanitize($_POST['medications'] ?? ''),
            'bowel' => Utils::sanitize($_POST['bowel'] ?? ''),
            'menstruation' => Utils::sanitize($_POST['menstruation'] ?? ''),
            'had_therapy' => isset($_POST['had_therapy']) ? 1 : 0,
            'therapy_duration' => Utils::sanitize($_POST['therapy_duration'] ?? ''),
            'therapy_reason' => Utils::sanitize($_POST['therapy_reason'] ?? '')
        ];

        // Validações
        if (empty($data['name'])) {
            $this->error('Nome é obrigatório');
        }

        if (!Validator::validateCPF($data['cpf'])) {
            $this->error('CPF inválido');
        }

        if (!Validator::validatePhone($data['phone'])) {
            $this->error('Telefone inválido');
        }

        if (!empty($data['cep']) && !Validator::validateCEP($data['cep'])) {
            $this->error('CEP inválido');
        }

        if (!empty($data['email']) && !Utils::isValidEmail($data['email'])) {
            $this->error('Email inválido');
        }

        // Verifica se CPF já existe
        $existingPatient = $this->patientModel->findByCPF($data['cpf']);
        if ($existingPatient) {
            $this->error('Este CPF já está cadastrado');
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $patientId = $this->patientModel->createPatient($data);

        if ($patientId) {
            $this->success('Paciente cadastrado com sucesso', [
                'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=patients'
            ]);
        } else {
            $this->error('Erro ao cadastrar paciente');
        }
    }

    /**
     * Exibe detalhes do paciente
     */
    public function show(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        if ((int)($patient['therapist_id'] ?? 0) !== (int) Auth::therapistId()) {
            $this->error('Acesso negado', 403);
        }

        $records = $this->patientRecordModel->findByPatient($patientId, Auth::therapistId());

        $this->view('admin/patients/show', [
            'patient' => $patient,
            'records' => $records
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado', 404);
        }

        if ((int)($patient['therapist_id'] ?? 0) !== (int) Auth::therapistId()) {
            $this->error('Acesso negado', 403);
        }

        $this->view('admin/patients/edit', ['patient' => $patient]);
    }

    /**
     * Processa atualização
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido');
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado');
        }

        if ((int)($patient['therapist_id'] ?? 0) !== (int) Auth::therapistId()) {
            $this->error('Acesso negado', 403);
        }

        $data = [
            'name' => Utils::sanitize($_POST['name'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? '',
            'phone' => Validator::removePhoneMask($_POST['phone'] ?? ''),
            'email' => Utils::sanitize($_POST['email'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'number' => Utils::sanitize($_POST['number'] ?? ''),
            'complement' => Utils::sanitize($_POST['complement'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'cep' => Validator::removeCEPMask($_POST['cep'] ?? ''),
            'observations' => Utils::sanitize($_POST['observations'] ?? ''),
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'father' => Utils::sanitize($_POST['father'] ?? ''),
            'mother' => Utils::sanitize($_POST['mother'] ?? ''),
            'first_word' => Utils::sanitize($_POST['first_word'] ?? ''),
            'depression' => isset($_POST['depression']) ? 1 : 0,
            'anxiety' => isset($_POST['anxiety']) ? 1 : 0,
            'medications' => Utils::sanitize($_POST['medications'] ?? ''),
            'bowel' => Utils::sanitize($_POST['bowel'] ?? ''),
            'menstruation' => Utils::sanitize($_POST['menstruation'] ?? ''),
            'had_therapy' => isset($_POST['had_therapy']) ? 1 : 0,
            'therapy_duration' => Utils::sanitize($_POST['therapy_duration'] ?? ''),
            'therapy_reason' => Utils::sanitize($_POST['therapy_reason'] ?? '')
        ];

        // Validações
        if (empty($data['name'])) {
            $this->error('Nome é obrigatório');
        }

        if (!Validator::validatePhone($data['phone'])) {
            $this->error('Telefone inválido');
        }

        if (!empty($data['cep']) && !Validator::validateCEP($data['cep'])) {
            $this->error('CEP inválido');
        }

        if (!empty($data['email']) && !Utils::isValidEmail($data['email'])) {
            $this->error('Email inválido');
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->patientModel->updatePatient($patientId, $data)) {
            $this->success('Paciente atualizado com sucesso', [
                'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=patients&subaction=show&id=' . $patientId
            ]);
        } else {
            $this->error('Erro ao atualizar paciente');
        }
    }

    /**
     * Deleta paciente
     */
    public function delete(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);

        if ($patientId <= 0) {
            $this->error('ID inválido');
        }

        $patient = $this->patientModel->findById($patientId);
        if (!$patient || (int)($patient['therapist_id'] ?? 0) !== (int) Auth::therapistId()) {
            $this->error('Acesso negado', 403);
        }

        if ($this->patientModel->delete($patientId)) {
            $this->success('Paciente removido com sucesso', [
                'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=patients'
            ]);
        } else {
            $this->error('Erro ao remover paciente');
        }
    }

    /**
     * Busca CEP (API dos Correios)
     */
    public function searchCEP(): void
    {
        $cep = Validator::removeCEPMask($_GET['cep'] ?? '');

        if (!Validator::validateCEP($cep)) {
            $this->error('CEP inválido');
        }

        try {
            $url = "https://viacep.com.br/ws/{$cep}/json/";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data['erro'])) {
                $this->error('CEP não encontrado');
            }

            $this->success('CEP encontrado', [
                'address' => $data['logradouro'] ?? '',
                'neighborhood' => $data['bairro'] ?? '',
                'city' => $data['localidade'] ?? '',
                'state' => $data['uf'] ?? ''
            ]);
        } catch (\Exception $e) {
            $this->error('Erro ao buscar CEP');
        }
    }

    /**
     * Tela de historico do paciente com abas
     */
    public function history(): void
    {
        $patientId = (int)($_GET['id'] ?? 0);
        if ($patientId <= 0) {
            $this->error('ID inválido', 404);
        }

        $therapistId = Auth::therapistId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient || (int)($patient['therapist_id'] ?? 0) !== (int) $therapistId) {
            $this->error('Paciente não encontrado', 404);
        }

        $records = $this->patientRecordModel->findByPatient($patientId, $therapistId);
        $tasks = $this->patientTaskModel->findByPatient($patientId, $therapistId);

        $this->view('admin/patients/history', [
            'patient' => $patient,
            'records' => $records,
            'tasks' => $tasks,
        ]);
    }

    public function storeHistoryRecord(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['patient_id'] ?? 0);
        $therapistId = Auth::therapistId();

        $patient = $this->patientModel->findById($patientId);
        if (!$patient || (int)($patient['therapist_id'] ?? 0) !== (int) $therapistId) {
            $this->error('Acesso negado', 403);
        }

        $description = Utils::sanitize($_POST['description'] ?? '');
        $notes = $_POST['notes'] ?? '';
        if ($notes === '') {
            $this->error('Histórico é obrigatório');
        }

        $saved = $this->patientRecordModel->createRecord([
            'patient_id' => $patientId,
            'therapist_id' => $therapistId,
            'record_date' => $_POST['record_date'] ?? date('Y-m-d'),
            'description' => $description,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->error('Não foi possível salvar o atendimento');
        }

        $this->success('Atendimento registrado com sucesso', [
            'redirect' => Config::APP_URL . '/dashboard.php?action=patients&subaction=history&id=' . $patientId,
        ]);
    }

    public function storeTask(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['patient_id'] ?? 0);
        $therapistId = Auth::therapistId();
        $patient = $this->patientModel->findById($patientId);
        if (!$patient || (int)($patient['therapist_id'] ?? 0) !== (int) $therapistId) {
            $this->error('Acesso negado', 403);
        }

        $title = Utils::sanitize($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        if ($title === '' || $description === '') {
            $this->error('Título e descrição são obrigatórios');
        }

        $taskId = $this->patientTaskModel->createTask([
            'patient_id' => $patientId,
            'therapist_id' => $therapistId,
            'due_date' => $_POST['due_date'] ?? date('Y-m-d'),
            'title' => $title,
            'description' => $description,
            'sent_to_patient' => isset($_POST['send_to_patient']) ? 1 : 0,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$taskId) {
            $this->error('Não foi possível salvar a tarefa');
        }

        $this->saveTaskAttachments($taskId, $therapistId);

        $this->success('Tarefa criada com sucesso', [
            'redirect' => Config::APP_URL . '/dashboard.php?action=patients&subaction=history&id=' . $patientId . '#tab-tarefas',
        ]);
    }

    public function sendTask(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $taskId = (int)($_POST['task_id'] ?? 0);
        $task = $this->patientTaskModel->findById($taskId);
        if (!$task || (int)($task['therapist_id'] ?? 0) !== (int) Auth::therapistId()) {
            $this->error('Tarefa não encontrada', 404);
        }

        $this->patientTaskModel->update($taskId, [
            'sent_to_patient' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $this->success('Tarefa enviada ao paciente', [
            'redirect' => Config::APP_URL . '/dashboard.php?action=patients&subaction=history&id=' . (int) $task['patient_id'] . '#tab-tarefas',
        ]);
    }

    private function saveTaskAttachments(int $taskId, int $therapistId): void
    {
        $uploadBase = dirname(__DIR__, 2) . '/uploads/tasks';
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0775, true);
        }

        if (!empty($_POST['attachment_link'])) {
            $link = filter_var(trim((string) $_POST['attachment_link']), FILTER_SANITIZE_URL);
            if ($link !== '') {
                $this->taskAttachmentModel->insert([
                    'task_id' => $taskId,
                    'therapist_id' => $therapistId,
                    'type' => 'link',
                    'file_name' => $link,
                    'file_path' => $link,
                    'file_size' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (!isset($_FILES['attachment_file']) || ($_FILES['attachment_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return;
        }

        $originalName = (string) $_FILES['attachment_file']['name'];
        $tmpName = (string) $_FILES['attachment_file']['tmp_name'];
        $size = (int) $_FILES['attachment_file']['size'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            return;
        }

        $safeFile = uniqid('task_', true) . '.' . $ext;
        $target = $uploadBase . '/' . $safeFile;
        if (@move_uploaded_file($tmpName, $target)) {
            $relativePath = 'uploads/tasks/' . $safeFile;
            $this->taskAttachmentModel->insert([
                'task_id' => $taskId,
                'therapist_id' => $therapistId,
                'type' => 'file',
                'file_name' => $originalName,
                'file_path' => $relativePath,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->therapistFileModel->insert([
                'therapist_id' => $therapistId,
                'file_name' => $originalName,
                'file_path' => $relativePath,
                'file_size' => $size,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
