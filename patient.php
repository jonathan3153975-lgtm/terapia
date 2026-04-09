<?php

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\PatientPortalController;
use Config\Config;
use Helpers\Session;

Config::loadEnv();
Session::start();

$action = $_GET['action'] ?? 'dashboard';
$portal = new PatientPortalController();

match ($action) {
    'dashboard' => $portal->dashboard(),
    'tasks' => $portal->tasks(),
    'task-material' => $portal->showTaskMaterial(),
    default => $portal->dashboard(),
};
