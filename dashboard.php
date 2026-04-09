<?php

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\AdminController;
use App\Controllers\TherapistController;
use Config\Config;
use Helpers\Session;

Config::loadEnv();
Session::start();

$action = $_GET['action'] ?? 'therapist-dashboard';

match ($action) {
    'admin-dashboard' => (new AdminController())->dashboard(),
    'therapists' => (new AdminController())->therapists(),
    'therapists-create' => (new AdminController())->createTherapist(),
    'therapists-store' => (new AdminController())->storeTherapist(),
    'therapists-show' => (new AdminController())->showTherapist(),
    'therapists-edit' => (new AdminController())->editTherapist(),
    'therapists-update' => (new AdminController())->updateTherapist(),
    'therapists-password' => (new AdminController())->passwordTherapist(),
    'therapists-password-update' => (new AdminController())->updatePasswordTherapist(),
    'therapists-delete' => (new AdminController())->deleteTherapist(),

    'therapist-dashboard' => (new TherapistController())->dashboard(),
    'therapist-schedule' => (new TherapistController())->schedule(),
    'therapist-schedule-store' => (new TherapistController())->storeScheduleAppointment(),
    'therapist-schedule-show' => (new TherapistController())->showScheduleAppointment(),
    'therapist-schedule-edit' => (new TherapistController())->editScheduleAppointment(),
    'therapist-schedule-update' => (new TherapistController())->updateScheduleAppointment(),
    'therapist-schedule-delete' => (new TherapistController())->deleteScheduleAppointment(),
    'therapist-financial' => (new TherapistController())->financial(),
    'therapist-financial-update' => (new TherapistController())->financialUpdate(),
    'therapist-financial-confirm' => (new TherapistController())->financialConfirmPayment(),
    'therapist-materials' => (new TherapistController())->materials(),
    'therapist-materials-create' => (new TherapistController())->createMaterial(),
    'therapist-materials-store' => (new TherapistController())->storeMaterial(),
    'therapist-materials-show' => (new TherapistController())->showMaterial(),
    'therapist-materials-edit' => (new TherapistController())->editMaterial(),
    'therapist-materials-update' => (new TherapistController())->updateMaterial(),
    'therapist-materials-delete' => (new TherapistController())->deleteMaterial(),
    'therapist-materials-send' => (new TherapistController())->sendMaterial(),
    'patients' => (new TherapistController())->patients(),
    'patients-create' => (new TherapistController())->createPatient(),
    'patients-store' => (new TherapistController())->storePatient(),
    'patients-show' => (new TherapistController())->showPatient(),
    'patients-edit' => (new TherapistController())->editPatient(),
    'patients-update' => (new TherapistController())->updatePatient(),
    'patients-delete' => (new TherapistController())->deletePatient(),
    'patients-history' => (new TherapistController())->historyPatient(),
    'patients-appointments-store' => (new TherapistController())->storePatientAppointment(),
    'patients-appointments-show' => (new TherapistController())->showPatientAppointment(),
    'patients-appointments-edit' => (new TherapistController())->editPatientAppointment(),
    'patients-appointments-update' => (new TherapistController())->updatePatientAppointment(),
    'patients-appointments-delete' => (new TherapistController())->deletePatientAppointment(),
    'patients-tasks-store' => (new TherapistController())->storePatientTask(),
    'patients-tasks-show' => (new TherapistController())->showPatientTask(),
    'patients-tasks-edit' => (new TherapistController())->editPatientTask(),
    'patients-tasks-update' => (new TherapistController())->updatePatientTask(),
    'patients-tasks-delete' => (new TherapistController())->deletePatientTask(),

    default => (new TherapistController())->dashboard(),
};
