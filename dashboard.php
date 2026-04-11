<?php

$bootstrapLogFile = __DIR__ . '/bootstrap-error.log';

$bootstrapLog = static function (string $message) use ($bootstrapLogFile): void {
    @file_put_contents($bootstrapLogFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
};

set_exception_handler(static function (\Throwable $e) use ($bootstrapLog): void {
    $bootstrapLog('Uncaught exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo 'Erro interno. Consulte bootstrap-error.log.';
});

register_shutdown_function(static function () use ($bootstrapLog): void {
    $error = error_get_last();
    if (!is_array($error)) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (in_array((int) ($error['type'] ?? 0), $fatalTypes, true)) {
        $bootstrapLog('Fatal error: ' . (string) ($error['message'] ?? '') . ' @ ' . (string) ($error['file'] ?? '') . ':' . (string) ($error['line'] ?? ''));
    }
});

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    $autoload = __DIR__ . '/autoload.php';
}

if (!file_exists($autoload)) {
    http_response_code(500);
    echo 'Erro de inicializacao: arquivo de autoload nao encontrado.';
    exit;
}

require $autoload;

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
    'patient-packages' => (new AdminController())->patientPackages(),
    'patient-packages-store' => (new AdminController())->storePatientPackage(),
    'patient-packages-toggle-status' => (new AdminController())->togglePatientPackageStatus(),
    'patient-packages-delete' => (new AdminController())->deletePatientPackage(),
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
    'therapist-messages' => (new TherapistController())->dailyMessages(),
    'therapist-messages-store' => (new TherapistController())->storeDailyMessage(),
    'therapist-messages-bulk' => (new TherapistController())->bulkDailyMessages(),
    'therapist-messages-update' => (new TherapistController())->updateDailyMessage(),
    'therapist-messages-delete' => (new TherapistController())->deleteDailyMessage(),
    'therapist-faith-words' => (new TherapistController())->faithWords(),
    'therapist-faith-words-store' => (new TherapistController())->storeFaithWord(),
    'therapist-faith-words-bulk' => (new TherapistController())->bulkFaithWords(),
    'therapist-faith-words-update' => (new TherapistController())->updateFaithWord(),
    'therapist-faith-words-delete' => (new TherapistController())->deleteFaithWord(),
    'therapist-guided-meditations' => (new TherapistController())->guidedMeditations(),
    'therapist-guided-meditations-store' => (new TherapistController())->storeGuidedMeditation(),
    'therapist-guided-meditations-update' => (new TherapistController())->updateGuidedMeditation(),
    'therapist-guided-meditations-delete' => (new TherapistController())->deleteGuidedMeditation(),
    'therapist-healing-letters' => (new TherapistController())->healingLetters(),
    'therapist-healing-letters-store' => (new TherapistController())->storeHealingLetter(),
    'therapist-healing-letters-bulk' => (new TherapistController())->bulkHealingLetters(),
    'therapist-healing-letters-update' => (new TherapistController())->updateHealingLetter(),
    'therapist-healing-letters-delete' => (new TherapistController())->deleteHealingLetter(),
    'patients' => (new TherapistController())->patients(),
    'patients-signup-link-create' => (new TherapistController())->createPatientSignupLink(),
    'patients-approve-review' => (new TherapistController())->approvePatientReview(),
    'patients-preview-start' => (new TherapistController())->startPatientPreview(),
    'patients-preview-stop' => (new TherapistController())->stopPatientPreview(),
    'patients-create' => (new TherapistController())->createPatient(),
    'patients-store' => (new TherapistController())->storePatient(),
    'patients-show' => (new TherapistController())->showPatient(),
    'patients-edit' => (new TherapistController())->editPatient(),
    'patients-password' => (new TherapistController())->passwordPatient(),
    'patients-password-update' => (new TherapistController())->updatePasswordPatient(),
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
