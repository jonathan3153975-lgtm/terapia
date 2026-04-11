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

use App\Controllers\PaymentWebhookController;
use Config\Config;

Config::loadEnv();

$action = $_GET['action'] ?? '';
if ($action === 'mercado-pago') {
    (new PaymentWebhookController())->mercadoPago();
    exit;
}

http_response_code(404);
echo 'Webhook nao encontrado.';
