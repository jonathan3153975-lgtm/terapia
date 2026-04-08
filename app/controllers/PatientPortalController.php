<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Task;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;

class PatientPortalController extends Controller
{
    private Task $taskModel;
    private Appointment $appointmentModel;

    public function __construct()
    {
        Auth::requireRoles(['patient']);
        $this->taskModel = new Task();
        $this->appointmentModel = new Appointment();
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
}
