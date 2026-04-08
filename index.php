<?php

require __DIR__ . '/vendor/autoload.php';

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
