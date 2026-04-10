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

use App\Controllers\AuthController;
use Config\Config;
use Helpers\Session;

Config::loadEnv();
Session::start();

$action = $_GET['action'] ?? 'login';
$auth = new AuthController();

match ($action) {
    'login' => $auth->login(),
    'process-login' => $auth->processLogin(),
    'logout' => $auth->logout(),
    default => $auth->login(),
};
