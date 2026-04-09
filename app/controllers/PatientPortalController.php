<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Material;
use App\Models\Task;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;

class PatientPortalController extends Controller
{
    private Task $taskModel;
    private Appointment $appointmentModel;
    private Material $materialModel;

    public function __construct()
    {
        Auth::requireRoles(['patient']);
        $this->taskModel = new Task();
        $this->appointmentModel = new Appointment();
        $this->materialModel = new Material();
    }

    public function dashboard(): void
    {
        $patientId = (int) Auth::patientId();
        $this->view('patient/dashboard', [
            'appUrl' => Config::get('APP_URL', ''),
            'sessions' => count($this->appointmentModel->listByPatient($patientId)),
            'tasks' => count($this->taskModel->listByPatient($patientId)),
            'pending' => $this->taskModel->countPendingByPatient($patientId),
            'done' => $this->taskModel->countDoneByPatient($patientId),
        ]);
    }

    public function tasks(): void
    {
        $patientId = (int) Auth::patientId();
        $tasks = $this->taskModel->listByPatient($patientId);
        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $tasks);
        $this->view('patient/tasks', [
            'appUrl' => Config::get('APP_URL', ''),
            'tasks' => $tasks,
            'taskLinkedMaterials' => $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds),
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
}
