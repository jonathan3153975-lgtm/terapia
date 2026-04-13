<?php

namespace App\Controllers;

use App\Models\Patient;
use App\Models\VirtualTask;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;

class VirtualTaskController extends Controller
{
    private VirtualTask $virtualTaskModel;
    private Patient $patientModel;

    public function __construct()
    {
        $this->virtualTaskModel = new VirtualTask();
        $this->patientModel = new Patient();
    }

    /**
     * Lista tarefas dinâmicas do terapeuta
     */
    public function index()
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        $tasks = $this->virtualTaskModel->getVirtualTasksByTherapist($therapistId);

        $this->view('therapist/virtual-tasks/index', [
            'appUrl' => Config::get('APP_URL', ''),
            'tasks' => $tasks ?? [],
            'therapistId' => $therapistId
        ]);
    }

    /**
     * Exibe formulário para criar nova tarefa dinâmica
     */
    public function create(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        // Lista de templates disponíveis
        $templates = [
            'virtual_tree_of_life' => [
                'name' => 'Árvore da Vida',
                'description' => 'Explore sua árvore da vida respondendo perguntas sobre desafios, pessoas importantes, habilidades e sonhos.',
                'icon' => '🌳'
            ]
        ];

        $this->view('therapist/virtual-tasks/create', [
            'appUrl' => Config::get('APP_URL', ''),
            'templates' => $templates,
            'therapistId' => $therapistId
        ]);
    }

    /**
     * Editor da tarefa dinâmica (Árvore da Vida)
     */
    public function editor(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        // Carrega estrutura padrão
        $structure = VirtualTask::getTreeOfLifeStructure();

        // Lista de pacientes para enviar
        $patients = $this->patientModel->searchByTherapist($therapistId);

        $this->view('therapist/virtual-tasks/editor-tree', [
            'appUrl' => Config::get('APP_URL', ''),
            'structure' => $structure,
            'patients' => $patients ?? [],
            'therapistId' => $therapistId
        ]);
    }

    /**
     * Salva tarefa dinâmica (AJAX)
     */
    public function store(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        $postData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $title = $postData['title'] ?? 'Árvore da Vida';
        $patientId = (int) ($postData['patient_id'] ?? 0);
        $taskType = $postData['task_type'] ?? 'virtual_tree_of_life';

        if (!$patientId) {
            $this->json(['success' => false, 'message' => 'Paciente inválido'], 400);
        }

        // Verifica se paciente pertence ao terapeuta
        $patient = $this->patientModel->findByTherapistAndId($therapistId, $patientId);
        if (!$patient) {
            $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        try {
            $structure = VirtualTask::getTreeOfLifeStructure();
            $description = trim((string) ($postData['description'] ?? ''));
            $sections = $postData['sections'] ?? [];
            if (is_array($sections) && $sections !== []) {
                $allowed = array_flip(array_map(static fn (array $section): string => (string) ($section['key'] ?? ''), $structure['sections']));
                $structure['sections'] = array_values(array_filter(
                    $structure['sections'],
                    static fn (array $section): bool => isset($allowed[(string) ($section['key'] ?? '')]) && in_array((string) ($section['key'] ?? ''), $sections, true)
                ));
            }

            $data = [
                'therapist_id' => $therapistId,
                'patient_id' => $patientId,
                'title' => $title,
                'description' => $description !== '' ? $description : 'Explore sua árvore da vida respondendo perguntas sobre seus desafios, pessoas importantes, habilidades e sonhos.',
                'due_date' => date('Y-m-d'),
                'send_to_patient' => 1,
                'delivery_kind' => 'task',
                'task_type' => $taskType,
                'content_json' => json_encode($structure),
                'is_active' => 1,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $taskId = $this->virtualTaskModel->insert($data);
            if ($taskId === false) {
                throw new \RuntimeException('Não foi possível salvar a tarefa dinâmica.');
            }

            $this->json([
                'success' => true,
                'message' => 'Tarefa dinâmica enviada com sucesso!',
                'task_id' => $taskId
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Modo teste/preview (terapeuta testa antes de enviar)
     */
    public function preview(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        $structure = VirtualTask::getTreeOfLifeStructure();

        $this->view('therapist/virtual-tasks/preview-tree', [
            'appUrl' => Config::get('APP_URL', ''),
            'structure' => $structure,
            'isPreview' => true,
            'therapistId' => $therapistId
        ]);
    }

    /**
     * Visualiza respostas de um paciente
     */
    public function show(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        $taskId = (int) ($_GET['id'] ?? 0);

        if (!$taskId) {
            $this->json(['success' => false, 'message' => 'Tarefa não encontrada'], 404);
        }

        // Busca tarefa
        $task = $this->virtualTaskModel->findByTherapistAndId($therapistId, $taskId);
        if (!$task || (int) $task['therapist_id'] !== $therapistId) {
            $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        // Busca respostas
        $responses = $this->virtualTaskModel->getTaskResponses($taskId);
        $structure = json_decode($task['content_json'] ?? '{}', true);

        $this->view('therapist/virtual-tasks/show', [
            'appUrl' => Config::get('APP_URL', ''),
            'task' => $task,
            'responses' => $responses ?? [],
            'structure' => $structure,
            'therapistId' => $therapistId
        ]);
    }

    /**
     * Deleta tarefa dinâmica
     */
    public function delete(): void
    {
        Auth::requireRoles(['therapist']);
        $therapistId = (int) Auth::therapistId();

        $taskId = (int) ($_POST['id'] ?? 0);

        if (!$taskId) {
            $this->json(['success' => false, 'message' => 'Tarefa não encontrada'], 404);
        }

        // Busca tarefa
        $task = $this->virtualTaskModel->findByTherapistAndId($therapistId, $taskId);
        if (!$task || (int) $task['therapist_id'] !== $therapistId) {
            $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        try {
            $this->virtualTaskModel->deleteByTherapistAndId($therapistId, $taskId);
            $this->json(['success' => true, 'message' => 'Tarefa deletada com sucesso']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Salva resposta parcial da tarefa (paciente respondendo)
     */
    public function saveResponse(): void
    {
        Auth::requireRoles(['patient']);
        $patientId = (int) Auth::patientId();
        $therapistId = (int) Auth::therapistId();

        $postData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $taskId = (int) ($postData['task_id'] ?? 0);
        $sectionName = $postData['section_name'] ?? '';
        $answers = $postData['answers'] ?? [];

        if (!$taskId || !$sectionName) {
            $this->json(['success' => false, 'message' => 'Dados inválidos'], 400);
        }

        try {
            $this->virtualTaskModel->saveResponse(
                $therapistId,
                $patientId,
                $taskId,
                $sectionName,
                json_encode($answers)
            );

            $this->json(['success' => true, 'message' => 'Resposta salva']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Finaliza tarefa dinâmica com reflexão final
     */
    public function complete(): void
    {
        Auth::requireRoles(['patient']);
        $patientId = (int) Auth::patientId();

        $postData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $taskId = (int) ($postData['task_id'] ?? 0);
        $reflectionHtml = $postData['reflection'] ?? '';

        if (!$taskId) {
            $this->json(['success' => false, 'message' => 'Tarefa não encontrada'], 400);
        }

        try {
            $task = $this->virtualTaskModel->findById($taskId);
            if (!$task || (int) ($task['patient_id'] ?? 0) !== $patientId) {
                $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
            }

            $this->virtualTaskModel->completeVirtualTask($taskId, $reflectionHtml);
            $this->json(['success' => true, 'message' => 'Tarefa concluída com sucesso']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
