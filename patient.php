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
    'messenger' => $portal->messenger(),
    'messenger-draw' => $portal->drawMessengerMessage(),
    'messenger-save' => $portal->saveMessengerEntry(),
    'task-material' => $portal->showTaskMaterial(),
    'task-respond' => $portal->respondTask(),
    'task-respond-submit' => $portal->submitTaskResponse(),
    default => $portal->dashboard(),
};
