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
        $this->view('patient/tasks', [
            'appUrl' => Config::get('APP_URL', ''),
            'tasks' => $this->taskModel->listByPatient($patientId),
        ]);
    }

    public function showTaskMaterial(): void
    {
        $patientId = (int) Auth::patientId();
        $taskId = (int) ($_GET['id'] ?? 0);

        $task = $this->taskModel->findByPatientAndId($patientId, $taskId);
        if (!$task || empty($task['material_id']) || empty($task['send_to_patient'])) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks');
        }

        $material = $this->materialModel->findById((int) $task['material_id']);
        if (!$material) {
            $this->redirect(Config::get('APP_URL', '') . '/patient.php?action=tasks');
        }

        $assets = $this->materialModel->listAssets((int) $material['id']);

        $this->view('patient/task-material', [
            'appUrl' => Config::get('APP_URL', ''),
            'task' => $task,
            'material' => $material,
            'assets' => $assets,
        ]);
    }
}
