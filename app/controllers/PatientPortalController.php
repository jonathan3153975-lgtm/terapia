<?php

namespace App\Controllers;

use Classes\Controller;
use Helpers\Auth;
use App\Models\PatientRecord;
use App\Models\PatientTask;
use App\Models\TherapistFile;

class PatientPortalController extends Controller
{
    private PatientRecord $patientRecordModel;
    private PatientTask $patientTaskModel;
    private TherapistFile $therapistFileModel;

    public function __construct()
    {
        Auth::requirePatient();
        $this->patientRecordModel = new PatientRecord();
        $this->patientTaskModel = new PatientTask();
        $this->therapistFileModel = new TherapistFile();
    }

    public function dashboard(): void
    {
        $patientId = (int) Auth::patientId();
        if ($patientId <= 0) {
            $this->error('Paciente sem vinculo de acesso', 403);
        }

        $totalSessions = $this->patientRecordModel->countByPatient($patientId);
        $totalTasks = $this->patientTaskModel->countByPatient($patientId);
        $pendingTasks = $this->patientTaskModel->countPendingByPatient($patientId);
        $doneTasks = $this->patientTaskModel->countDoneByPatient($patientId);

        // Placeholder para evolucao futura de mensagem diaria configuravel.
        $dailyMessage = 'Mensagem diaria sera configurada pelo terapeuta em breve.';

        $this->view('patient/dashboard', [
            'totalSessions' => $totalSessions,
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'doneTasks' => $doneTasks,
            'materialAccessed' => 0,
            'dailyMessage' => $dailyMessage,
        ]);
    }

    public function tasks(): void
    {
        $patientId = (int) Auth::patientId();
        if ($patientId <= 0) {
            $this->error('Paciente sem vinculo de acesso', 403);
        }

        $tasks = $this->patientTaskModel->findByPatient($patientId);

        $this->view('patient/tasks', [
            'tasks' => $tasks,
        ]);
    }

    public function markTaskDone(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Metodo nao permitido', 405);
        }

        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $task = $this->patientTaskModel->findById($taskId);

        if (!$task || (int) $task['patient_id'] !== $patientId) {
            $this->error('Tarefa nao encontrada', 404);
        }

        $this->patientTaskModel->update($taskId, ['status' => 'done', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->success('Tarefa marcada como realizada', [
            'redirect' => \Config\Config::APP_URL . '/patient.php?action=tasks',
        ]);
    }
}
