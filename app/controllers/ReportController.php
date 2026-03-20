<?php

namespace App\Controllers;

use Classes\Controller;
use App\Models\PatientRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use Helpers\Auth;

class ReportController extends Controller
{
    private PatientRecord $patientRecordModel;
    private Appointment $appointmentModel;
    private Patient $patientModel;
    private Payment $paymentModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->patientRecordModel = new PatientRecord();
        $this->appointmentModel = new Appointment();
        $this->patientModel = new Patient();
        $this->paymentModel = new Payment();
    }

    /**
     * Exibe painel de relatórios
     */
    public function index(): void
    {
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Estatísticas gerais
        $totalPatients = $this->patientModel->count();
        $totalRecords = $this->patientRecordModel->count();
        $totalAppointments = $this->appointmentModel->count();
        $totalPayments = $this->paymentModel->count();

        // Dados do mês
        $recordsThisMonth = $this->patientRecordModel->count(
            "DATE(record_date) >= ? AND DATE(record_date) <= ?",
            [$startDate, $endDate]
        );

        $appointmentsThisMonth = $this->appointmentModel->count(
            "DATE(appointment_date) >= ? AND DATE(appointment_date) <= ?",
            [$startDate, $endDate]
        );

        $monthlyRevenue = $this->paymentModel->getTotalAmount('paid', $year . '-' . $month);

        $this->view('admin/reports/index', [
            'year' => $year,
            'month' => $month,
            'totalPatients' => $totalPatients,
            'totalRecords' => $totalRecords,
            'totalAppointments' => $totalAppointments,
            'totalPayments' => $totalPayments,
            'recordsThisMonth' => $recordsThisMonth,
            'appointmentsThisMonth' => $appointmentsThisMonth,
            'monthlyRevenue' => $monthlyRevenue
        ]);
    }

    /**
     * Relatório de atendimentos
     */
    public function recordsReport(): void
    {
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $records = $this->patientRecordModel->getByDateRange($startDate, $endDate);

        $this->view('admin/reports/records', [
            'records' => $records,
            'year' => $year,
            'month' => $month
        ]);
    }

    /**
     * Relatório de atendimentos por paciente
     */
    public function patientRecordsReport(): void
    {
        $patients = $this->patientModel->findAll();

        // Conta atendimentos por paciente
        $patientsWithCounts = array_map(function ($patient) {
            $patient['total_records'] = $this->patientRecordModel->countByPatient($patient['id']);
            return $patient;
        }, $patients);

        usort($patientsWithCounts, function ($a, $b) {
            return $b['total_records'] <=> $a['total_records'];
        });

        $this->view('admin/reports/patient-records', [
            'patients' => $patientsWithCounts
        ]);
    }

    /**
     * Relatório de agendamentos
     */
    public function appointmentsReport(): void
    {
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m') ?? '';

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $appointments = $this->appointmentModel->findBetweenDates($startDate, $endDate);

        // Agrupa por status
        $appointmentsByStatus = [
            'confirmed' => array_filter($appointments, fn($a) => $a['status'] === 'confirmed'),
            'pending' => array_filter($appointments, fn($a) => $a['status'] === 'pending'),
            'cancelled' => array_filter($appointments, fn($a) => $a['status'] === 'cancelled'),
            'completed' => array_filter($appointments, fn($a) => $a['status'] === 'completed')
        ];

        $this->view('admin/reports/appointments', [
            'appointments' => $appointments,
            'appointmentsByStatus' => $appointmentsByStatus,
            'year' => $year,
            'month' => $month
        ]);
    }

    /**
     * Relatório de pagamentos
     */
    public function paymentsReport(): void
    {
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $monthKey = "$year-$month";
        $payments = $this->paymentModel->getByMonth($monthKey);

        // Agrupa por status
        $paidTotal = 0;
        $pendingTotal = 0;

        foreach ($payments as $payment) {
            if ($payment['status'] === 'paid') {
                $paidTotal += $payment['amount'];
            } else {
                $pendingTotal += $payment['amount'];
            }
        }

        $this->view('admin/reports/payments', [
            'payments' => $payments,
            'year' => $year,
            'month' => $month,
            'paidTotal' => $paidTotal,
            'pendingTotal' => $pendingTotal
        ]);
    }

    /**
     * Relatório anual
     */
    public function annualReport(): void
    {
        $year = $_GET['year'] ?? date('Y');

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthKey = sprintf('%04d-%02d', $year, $month);
            $monthlyData[$month] = [
                'records' => $this->patientRecordModel->count(
                    "DATE_FORMAT(record_date, '%Y-%m') = ?",
                    [$monthKey]
                ),
                'appointments' => $this->appointmentModel->count(
                    "DATE_FORMAT(appointment_date, '%Y-%m') = ?",
                    [$monthKey]
                ),
                'revenue' => $this->paymentModel->getTotalAmount('paid', $monthKey)
            ];
        }

        $this->view('admin/reports/annual', [
            'year' => $year,
            'monthlyData' => $monthlyData
        ]);
    }

    /**
     * Exporta relatório para PDF
     */
    public function exportPDF(): void
    {
        // Será implementado com mPDF
        $this->error('Funcionalidade em desenvolvimento');
    }
}
