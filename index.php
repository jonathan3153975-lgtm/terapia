<?php

require_once __DIR__ . '/vendor/autoload.php';

use Helpers\Auth;
use Helpers\Session;

Session::start();

// Verifica se há flash messages
$flash = Session::getFlash();

// Determina qual controller e action executar
$action = $_GET['action'] ?? 'login';

try {
    match ($action) {
        'login' => (new \App\Controllers\AuthController())->login(),
        'process-login' => (new \App\Controllers\AuthController())->processLogin(),
        'logout' => (new \App\Controllers\AuthController())->logout(),
        'forgot-password' => (new \App\Controllers\AuthController())->forgotPassword(),
        'process-forgot-password' => (new \App\Controllers\AuthController())->processForgotPassword(),
        'reset-password' => (new \App\Controllers\AuthController())->resetPassword(),
        'process-reset-password' => (new \App\Controllers\AuthController())->processResetPassword(),
        default => (new \App\Controllers\AuthController())->login()
    };
} catch (\Exception $e) {
    die('Erro: ' . $e->getMessage());
}
 
 