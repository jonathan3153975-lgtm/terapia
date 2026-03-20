<?php

require_once __DIR__ . '/vendor/autoload.php';

use Helpers\Auth;
use Helpers\Session;

Session::start();
Auth::requireAdmin();

try {
    $action = $_GET['action'] ?? 'index';
    $subaction = $_GET['subaction'] ?? 'index';

    match ($action) {
        'dashboard' => (new \App\Controllers\DashboardController())->index(),
        'patients' => match ($subaction) {
            'index' => (new \App\Controllers\PatientController())->index(),
            'create' => (new \App\Controllers\PatientController())->create(),
            'store' => (new \App\Controllers\PatientController())->store(),
            'show' => (new \App\Controllers\PatientController())->show(),
            'edit' => (new \App\Controllers\PatientController())->edit(),
            'update' => (new \App\Controllers\PatientController())->update(),
            'delete' => (new \App\Controllers\PatientController())->delete(),
            'search-cep' => (new \App\Controllers\PatientController())->searchCEP(),
            default => (new \App\Controllers\PatientController())->index()
        },
        'records' => match ($subaction) {
            'index' => (new \App\Controllers\PatientRecordController())->index(),
            'create' => (new \App\Controllers\PatientRecordController())->create(),
            'store' => (new \App\Controllers\PatientRecordController())->store(),
            'show' => (new \App\Controllers\PatientRecordController())->show(),
            'edit' => (new \App\Controllers\PatientRecordController())->edit(),
            'update' => (new \App\Controllers\PatientRecordController())->update(),
            'delete' => (new \App\Controllers\PatientRecordController())->delete(),
            default => (new \App\Controllers\PatientRecordController())->index()
        },
        'payments' => match ($subaction) {
            'index' => (new \App\Controllers\PaymentController())->index(),
            'create' => (new \App\Controllers\PaymentController())->create(),
            'store' => (new \App\Controllers\PaymentController())->store(),
            'show' => (new \App\Controllers\PaymentController())->show(),
            'edit' => (new \App\Controllers\PaymentController())->edit(),
            'update' => (new \App\Controllers\PaymentController())->update(),
            'delete' => (new \App\Controllers\PaymentController())->delete(),
            default => (new \App\Controllers\PaymentController())->index()
        },
        'appointments' => match ($subaction) {
            'calendar' => (new \App\Controllers\AppointmentController())->calendar(),
            'list' => (new \App\Controllers\AppointmentController())->list(),
            'create' => (new \App\Controllers\AppointmentController())->create(),
            'store' => (new \App\Controllers\AppointmentController())->store(),
            'show' => (new \App\Controllers\AppointmentController())->show(),
            'edit' => (new \App\Controllers\AppointmentController())->edit(),
            'update' => (new \App\Controllers\AppointmentController())->update(),
            'delete' => (new \App\Controllers\AppointmentController())->delete(),
            'get-by-date' => (new \App\Controllers\AppointmentController())->getByDate(),
            'get-by-range' => (new \App\Controllers\AppointmentController())->getByRange(),
            'approve' => (new \App\Controllers\AppointmentController())->approve(),
            default => (new \App\Controllers\AppointmentController())->list()
        },
        'reports' => match ($subaction) {
            'index' => (new \App\Controllers\ReportController())->index(),
            'records' => (new \App\Controllers\ReportController())->recordsReport(),
            'patient-records' => (new \App\Controllers\ReportController())->patientRecordsReport(),
            'appointments' => (new \App\Controllers\ReportController())->appointmentsReport(),
            'payments' => (new \App\Controllers\ReportController())->paymentsReport(),
            'annual' => (new \App\Controllers\ReportController())->annualReport(),
            'export-pdf' => (new \App\Controllers\ReportController())->exportPDF(),
            default => (new \App\Controllers\ReportController())->index()
        },
        default => (new \App\Controllers\DashboardController())->index()
    };
} catch (\Exception $e) {
    die('Erro: ' . $e->getMessage());
}
