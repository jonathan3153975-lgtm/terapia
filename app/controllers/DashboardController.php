<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\Patient;
use App\Models\PatientRecord;
use App\Models\Appointment;
use App\Models\Payment;
use Helpers\Auth;

class DashboardController extends Controller
{
    private Patient $patientModel;
    private PatientRecord $patientRecordModel;
    private Appointment $appointmentModel;
    private Payment $paymentModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->patientModel = new Patient();
        $this->patientRecordModel = new PatientRecord();
        $this->appointmentModel = new Appointment();
        $this->paymentModel = new Payment();
    }

    /**
     * Exibe dashboard principal
     */
    public function index(): void
    {
        // KPIs gerais
        $totalPatients        = $this->patientModel->count();
        $totalRecords         = $this->patientRecordModel->count();
        $totalAppointments    = $this->appointmentModel->count();
        $pendingAppointments  = $this->appointmentModel->count("status = ?", ['pending']);
        $thisMonth            = date('Y-m');
        $monthlyRevenue       = $this->paymentModel->getTotalAmount('paid', $thisMonth);
        $pendingRevenue       = $this->paymentModel->getTotalAmount('pending', '');

        // Agendamentos de hoje e próximos
        $todayAppointments    = $this->appointmentModel->findToday();
        $upcomingAppointments = $this->appointmentModel->findUpcoming(6);

        // Últimos atendimentos com nome do paciente
        $recentRecords = $this->patientRecordModel->findRecentWithPatients(5);

        // Últimos pagamentos
        $recentPayments = $this->paymentModel->search('', '', 0, 5);

        // Dados para gráfico de linha: atendimentos + receita por mês (últimos 6 meses)
        $chartLabels   = [];
        $chartRecords  = [];
        $chartRevenue  = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt       = new \DateTime("-{$i} months");
            $key      = $dt->format('Y-m');
            $chartLabels[]  = $dt->format('M/Y');
            $chartRecords[] = $this->patientRecordModel->count(
                "DATE_FORMAT(record_date, '%Y-%m') = ?", [$key]
            );
            $chartRevenue[] = $this->paymentModel->getTotalAmount('paid', $key);
        }

        // Dados para gráfico de rosca: agendamentos por status
        $apptByStatus = $this->appointmentModel->countByStatus();

        $this->view('admin/dashboard', [
            'totalPatients'        => $totalPatients,
            'totalRecords'         => $totalRecords,
            'totalAppointments'    => $totalAppointments,
            'pendingAppointments'  => $pendingAppointments,
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
        ]);
    }
}
