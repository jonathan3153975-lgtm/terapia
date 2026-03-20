<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Helpers\Auth;
use Helpers\Utils;

class AppointmentController extends Controller
{
    private Appointment $appointmentModel;
    private Patient $patientModel;

    public function __construct()
    {
        $this->appointmentModel = new Appointment();
        $this->patientModel = new Patient();
    }

    /**
     * Exibe calendário de agendamentos
     */
    public function calendar(): void
    {
        Auth::requireAdmin();

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $appointments = $this->appointmentModel->findBetweenDates($startDate, $endDate);

        $this->view('admin/appointments/calendar', [
            'appointments' => $appointments,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Exibe lista de agendamentos
     */
    public function list(): void
    {
        Auth::requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $month = $_GET['month'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $query = "1=1";
        $params = [];

        if (!empty($month)) {
            $query .= " AND DATE_FORMAT(appointment_date, '%Y-%m') = ?";
            $params[] = $month;
        }

        if (!empty($status)) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        $appointments = $this->appointmentModel->find($query, $params, 'appointment_date DESC', $limit);
        $totalAppointments = $this->appointmentModel->count($query, $params);
        $totalPages = ceil($totalAppointments / $limit);

        $this->view('admin/appointments/list', [
            'appointments' => $appointments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalAppointments' => $totalAppointments,
            'monthFilter' => $month,
            'statusFilter' => $status
        ]);
    }

    /**
     * Exibe formulário de criação
     */
    public function create(): void
    {
        Auth::requireAdmin();

        $patients = $this->patientModel->findAll();
        $this->view('admin/appointments/create', ['patients' => $patients]);
    }

    /**
     * Processa criação de agendamento
     */
    public function store(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $patientId = (int)($_POST['patient_id'] ?? 0);
        $appointmentDate = $_POST['appointment_date'] ?? '';
        $appointmentTime = $_POST['appointment_time'] ?? '';
        $notes = Utils::sanitize($_POST['notes'] ?? '');

        if ($patientId <= 0) {
            $this->error('Paciente não selecionado');
        }

        if (empty($appointmentDate) || empty($appointmentTime)) {
            $this->error('Data e hora são obrigatórias');
        }

        $patient = $this->patientModel->findById($patientId);

        if (!$patient) {
            $this->error('Paciente não encontrado');
        }

        $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;

        if ($this->appointmentModel->hasConflict($appointmentDateTime)) {
            $this->error('Já existe um agendamento para este horário');
        }

        $data = [
            'patient_id' => $patientId,
            'appointment_date' => $appointmentDateTime,
            'notes' => $notes,
            'status' => 'confirmed',
            'created_by' => Auth::userId(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $appointmentId = $this->appointmentModel->insert($data);

        if ($appointmentId) {
            $this->success('Agendamento criado com sucesso', [
                'redirect' => \Config\Config::APP_URL . '/dashboard.php?action=appointments&subaction=calendar'
            ]);
        } else {
            $this->error('Erro ao criar agendamento');
        }
    }

    /**
     * Exibe detalhes do agendamento
     */
    public function show(): void
    {
        Auth::requireAdmin();

        $appointmentId = (int)($_GET['id'] ?? 0);

        if ($appointmentId <= 0) {
            $this->error('ID inválido', 404);
        }

        $appointment = $this->appointmentModel->findById($appointmentId);

        if (!$appointment) {
            $this->error('Agendamento não encontrado', 404);
        }

        $patient = $this->patientModel->findById($appointment['patient_id']);

        $this->view('admin/appointments/show', [
            'appointment' => $appointment,
            'patient' => $patient
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(): void
    {
        Auth::requireAdmin();

        $appointmentId = (int)($_GET['id'] ?? 0);

        if ($appointmentId <= 0) {
            $this->error('ID inválido', 404);
        }

        $appointment = $this->appointmentModel->findById($appointmentId);

        if (!$appointment) {
            $this->error('Agendamento não encontrado', 404);
        }

        $patients = $this->patientModel->findAll();

        $this->view('admin/appointments/edit', [
            'appointment' => $appointment,
            'patients' => $patients
        ]);
    }

    /**
     * Processa atualização
     */
    public function update(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $appointmentId = (int)($_POST['id'] ?? 0);

        if ($appointmentId <= 0) {
            $this->error('ID inválido');
        }

        $appointment = $this->appointmentModel->findById($appointmentId);

        if (!$appointment) {
            $this->error('Agendamento não encontrado');
        }

        $patientId = (int)($_POST['patient_id'] ?? $appointment['patient_id']);
        $appointmentDate = $_POST['appointment_date'] ?? '';
        $appointmentTime = $_POST['appointment_time'] ?? '';
        $notes = Utils::sanitize($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? $appointment['status'];

        if (empty($appointmentDate) || empty($appointmentTime)) {
            $this->error('Data e hora são obrigatórias');
        }

        $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;

        if ($this->appointmentModel->hasConflict($appointmentDateTime, $appointmentId)) {
            $this->error('Já existe um agendamento para este horário');
        }

        $data = [
            'patient_id' => $patientId,
            'appointment_date' => $appointmentDateTime,
            'notes' => $notes,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->appointmentModel->update($appointmentId, $data)) {
            $this->success('Agendamento atualizado com sucesso');
        } else {
            $this->error('Erro ao atualizar agendamento');
        }
    }

    /**
     * Deleta agendamento
     */
    public function delete(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $appointmentId = (int)($_POST['id'] ?? 0);

        if ($appointmentId <= 0) {
            $this->error('ID inválido');
        }

        if ($this->appointmentModel->delete($appointmentId)) {
            $this->success('Agendamento removido com sucesso');
        } else {
            $this->error('Erro ao remover agendamento');
        }
    }

    /**
     * Obtem agendamentos em JSON (para AJAX)
     */
    public function getByDate(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        $appointments = $this->appointmentModel->findByDate($date);

        $this->json(['success' => true, 'appointments' => $appointments]);
    }

    /**
     * Retorna agendamentos em formato FullCalendar (AJAX)
     */
    public function getByRange(): void
    {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end   = $_GET['end']   ?? date('Y-m-t');

        // FullCalendar envia ISO8601 com T; extrai só a data
        $startDate = substr($start, 0, 10);
        $endDate   = substr($end,   0, 10);

        $appointments = $this->appointmentModel->findBetweenDates($startDate, $endDate);

        $statusColors = [
            'confirmed'  => '#2563eb',
            'pending'    => '#f59e0b',
            'cancelled'  => '#ef4444',
            'completed'  => '#10b981',
        ];

        $events = array_map(function ($a) use ($statusColors) {
            $color = $statusColors[$a['status']] ?? '#6b7280';
            return [
                'id'    => $a['id'],
                'title' => $a['patient_name'],
                'start' => str_replace(' ', 'T', $a['appointment_date']),
                'color' => $color,
                'extendedProps' => [
                    'status' => $a['status'],
                    'notes'  => $a['notes'] ?? '',
                ],
                'url' => \Config\Config::APP_URL . '/dashboard.php?action=appointments&subaction=show&id=' . $a['id'],
            ];
        }, $appointments);

        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

    /**
     * Aprova agendamento pendente
     */
    public function approve(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Método não permitido', 405);
        }

        $appointmentId = (int)($_POST['id'] ?? 0);

        if ($appointmentId <= 0) {
            $this->error('ID inválido');
        }

        if ($this->appointmentModel->update($appointmentId, ['status' => 'confirmed'])) {
            $this->success('Agendamento aprovado com sucesso');
        } else {
            $this->error('Erro ao aprovar agendamento');
        }
    }
}
