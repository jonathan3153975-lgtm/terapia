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
        $totalPatients = $this->patientModel->count();
        $totalRecords = $this->patientRecordModel->count();
        $totalAppointments = $this->appointmentModel->count();
        $pendingAppointments = $this->appointmentModel->count("status = ?", ['pending']);

        // Últimos registros
        $recentPatients = $this->patientModel->find('', [], 'created_at DESC', 5);
        $recentAppointments = $this->appointmentModel->find('', [], 'appointment_date DESC', 5);
        $recentRecords = $this->patientRecordModel->find('', [], 'record_date DESC', 5);

        $this->view('admin/dashboard', [
            'totalPatients' => $totalPatients,
            'totalRecords' => $totalRecords,
            'totalAppointments' => $totalAppointments,
            'pendingAppointments' => $pendingAppointments,
            'recentPatients' => $recentPatients,
            'recentAppointments' => $recentAppointments,
            'recentRecords' => $recentRecords
        ]);
    }
}
