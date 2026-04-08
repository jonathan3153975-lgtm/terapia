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

    'therapist-dashboard' => (new TherapistController())->dashboard(),
    'patients' => (new TherapistController())->patients(),
    'patients-create' => (new TherapistController())->createPatient(),
    'patients-store' => (new TherapistController())->storePatient(),

    default => (new TherapistController())->dashboard(),
};
