<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\FileStorage;
use App\Models\Patient;
use App\Models\Task;
use Classes\Controller;
use Config\Config;
use Helpers\Auth;
use Helpers\Utils;
use Helpers\Validator;

class TherapistController extends Controller
{
    private Patient $patientModel;
    private Appointment $appointmentModel;
    private Task $taskModel;
    private FileStorage $fileModel;

    public function __construct()
    {
        Auth::requireRoles(['therapist']);
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
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

    public function storePatient(): void
    {
        $therapistId = (int) Auth::id();
        $name = Utils::sanitize($_POST['name'] ?? '');
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $phone = Validator::onlyDigits($_POST['phone'] ?? '');
        $email = Utils::sanitize($_POST['email'] ?? '');

        if ($name === '' || $cpf === '' || $phone === '') {
            $this->error('Nome, CPF e telefone sao obrigatorios');
        }

        if (!Validator::validateCPF($cpf)) {
            $this->error('CPF invalido');
        }

        $inserted = $this->patientModel->insert([
            'therapist_id' => $therapistId,
            'name' => $name,
            'cpf' => $cpf,
            'birth_date' => $_POST['birth_date'] ?? null,
            'phone' => $phone,
            'email' => $email,
            'marital_status' => Utils::sanitize($_POST['marital_status'] ?? ''),
            'children' => Utils::sanitize($_POST['children'] ?? ''),
            'cep' => Validator::onlyDigits($_POST['cep'] ?? ''),
            'address' => Utils::sanitize($_POST['address'] ?? ''),
            'neighborhood' => Utils::sanitize($_POST['neighborhood'] ?? ''),
            'city' => Utils::sanitize($_POST['city'] ?? ''),
            'state' => Utils::sanitize($_POST['state'] ?? ''),
            'main_complaint' => Utils::sanitize($_POST['main_complaint'] ?? ''),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            $this->error('Falha ao cadastrar paciente');
        }

        $this->success('Paciente cadastrado', ['redirect' => Config::get('APP_URL', '') . '/dashboard.php?action=patients']);
    }
}
