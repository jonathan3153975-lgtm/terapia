<?php

require_once __DIR__ . '/vendor/autoload.php';

use Helpers\Auth;
use Helpers\Session;

Session::start();
Auth::requirePatient();

try {
    $action = $_GET['action'] ?? 'dashboard';

    match ($action) {
        'dashboard' => (new \App\Controllers\PatientPortalController())->dashboard(),
        'tasks' => (new \App\Controllers\PatientPortalController())->tasks(),
        'mark-task-done' => (new \App\Controllers\PatientPortalController())->markTaskDone(),
        default => (new \App\Controllers\PatientPortalController())->dashboard(),
    };
} catch (\Exception $e) {
    die('Erro: ' . $e->getMessage());
}
