<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Patient;
use App\Models\PatientRecord;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PatientTask;
use App\Models\TherapistFile;
use App\Models\PatientMessage;
use App\Models\User;
use Helpers\Auth;

class DashboardController extends Controller
{
    private Patient $patientModel;
    private PatientRecord $patientRecordModel;
    private Appointment $appointmentModel;
    private Payment $paymentModel;
    private PatientTask $patientTaskModel;
    private TherapistFile $therapistFileModel;
    private PatientMessage $patientMessageModel;
    private User $userModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->patientModel = new Patient();
        $this->patientRecordModel = new PatientRecord();
        $this->appointmentModel = new Appointment();
        $this->paymentModel = new Payment();
        $this->patientTaskModel = new PatientTask();
        $this->therapistFileModel = new TherapistFile();
        $this->patientMessageModel = new PatientMessage();
        $this->userModel = new User();
    }

    /**
     * Exibe dashboard principal
     */
    public function index(): void
    {
        if (Auth::isAdmin()) {
            $this->superAdminDashboard();
            return;
        }

        $therapistId = Auth::therapistId();
        if (!$therapistId) {
            $this->error('Terapeuta inválido', 403);
        }

        // KPIs gerais
        $totalPatients        = $this->patientModel->countByTherapist($therapistId);
        $totalRecords         = $this->patientRecordModel->countByTherapist($therapistId);
        $totalAppointments    = $this->appointmentModel->countByTherapist($therapistId);
        $pendingAppointments  = $this->appointmentModel->count("status = ? AND therapist_id = ?", ['pending', $therapistId]);
        $totalTasks           = $this->patientTaskModel->countByTherapist($therapistId);
        $totalMessagesSent    = $this->patientTaskModel->countSentToPatientByTherapist($therapistId);
        $totalStoredMaterials = $this->therapistFileModel->countByTherapist($therapistId);
        $totalMessagesStored  = $this->patientMessageModel->countByTherapist($therapistId);
        $thisMonth            = date('Y-m');
        $monthlyRevenue       = $this->paymentModel->getTotalAmount('paid', $thisMonth, $therapistId);
        $pendingRevenue       = $this->paymentModel->getTotalAmount('pending', '', $therapistId);

        // Agendamentos de hoje e próximos
        $todayAppointments    = $this->appointmentModel->findToday($therapistId);
        $upcomingAppointments = $this->appointmentModel->findUpcoming(6, $therapistId);

        // Últimos atendimentos com nome do paciente
        $recentRecords = $this->patientRecordModel->findRecentWithPatients(5, $therapistId);

        // Últimos pagamentos
        $recentPayments = $this->paymentModel->search('', '', 0, 5, $therapistId);

        // Dados para gráfico de linha: atendimentos + receita por mês (últimos 6 meses)
        $chartLabels   = [];
        $chartRecords  = [];
        $chartRevenue  = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt       = new \DateTime("-{$i} months");
            $key      = $dt->format('Y-m');
            $chartLabels[]  = $dt->format('M/Y');
            $chartRecords[] = $this->patientRecordModel->count(
                "DATE_FORMAT(record_date, '%Y-%m') = ? AND therapist_id = ?", [$key, $therapistId]
            );
            $chartRevenue[] = $this->paymentModel->getTotalAmount('paid', $key, $therapistId);
        }

        // Dados para gráfico de rosca: agendamentos por status
        $apptByStatus = $this->appointmentModel->countByStatus($therapistId);

        $this->view('admin/dashboard', [
            'totalPatients'        => $totalPatients,
            'totalRecords'         => $totalRecords,
            'totalAppointments'    => $totalAppointments,
            'pendingAppointments'  => $pendingAppointments,
            'totalTasks'           => $totalTasks,
            'totalMessagesSent'    => $totalMessagesSent,
            'totalStoredMaterials' => $totalStoredMaterials,
            'totalMessagesStored'  => $totalMessagesStored,
            'monthlyRevenue'       => $monthlyRevenue,
            'pendingRevenue'       => $pendingRevenue,
            'todayAppointments'    => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'recentRecords'        => $recentRecords,
            'recentPayments'       => $recentPayments,
            'chartLabels'          => $chartLabels,
            'chartRecords'         => $chartRecords,
            'chartRevenue'         => $chartRevenue,
            'apptByStatus'         => $apptByStatus,
            'isTherapistDashboard' => true,
        ]);
    }

    private function superAdminDashboard(): void
    {
        $totalTherapists = $this->userModel->countTherapists();
        $totalPatients = $this->patientModel->count();
        $totalActivePatients = $this->userModel->countActivePatientUsers();
        $totalFiles = $this->therapistFileModel->count();
        $usedBytes = $this->therapistFileModel->totalBytes();
        $therapists = $this->userModel->listTherapists();

        $this->view('admin/super/dashboard', [
            'totalTherapists' => $totalTherapists,
            'totalPatients' => $totalPatients,
            'totalActivePatients' => $totalActivePatients,
            'totalFiles' => $totalFiles,
            'usedBytes' => $usedBytes,
            'therapists' => $therapists,
        ]);
    }

}
