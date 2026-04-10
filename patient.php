<?php

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
    'materials' => $portal->materials(),
    'task-material' => $portal->showTaskMaterial(),
    'task-respond' => $portal->respondTask(),
    'task-respond-submit' => $portal->submitTaskResponse(),
    default => $portal->dashboard(),
};
