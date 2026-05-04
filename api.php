<?php

$bootstrapLogFile = __DIR__ . '/bootstrap-error.log';

$bootstrapLog = static function (string $message) use ($bootstrapLogFile): void {
    @file_put_contents($bootstrapLogFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
};

set_exception_handler(static function (\Throwable $e) use ($bootstrapLog): void {
    $bootstrapLog('Uncaught exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Erro interno. Consulte bootstrap-error.log.'], JSON_UNESCAPED_UNICODE);
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Erro de inicialização: autoload não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require $autoload;

use App\Controllers\Api\PatientAppController;
use Config\Config;

Config::loadEnv();

header('Content-Type: application/json; charset=utf-8');

$controller = new PatientAppController();
$action = $_GET['action'] ?? 'patient-me';

match ($action) {
    'patient-login' => $controller->login(),
    'patient-logout' => $controller->logout(),
    'patient-me' => $controller->me(),
    'patient-dashboard' => $controller->dashboard(),
    'patient-tasks' => $controller->tasks(),
    'patient-task-show' => $controller->taskShow(),
    'patient-task-respond' => $controller->respondTask(),
    'patient-materials' => $controller->materials(),
    'patient-books' => $controller->books(),
    'patient-book-favorite-toggle' => $controller->toggleBookFavorite(),
    'patient-book-file' => $controller->streamBookPdf(),
    'patient-videos' => $controller->videos(),
    'patient-video-show' => $controller->videoShow(),
    'patient-video-favorite-toggle' => $controller->toggleVideoFavorite(),
    'patient-video-rate' => $controller->rateVideo(),
    'patient-video-comment' => $controller->commentVideo(),
    'patient-video-file' => $controller->streamTeraTubeVideo(),
    'patient-messenger-entries' => $controller->messengerEntries(),
    'patient-messenger-draw' => $controller->drawMessenger(),
    'patient-messenger-save' => $controller->saveMessenger(),
    'patient-guided-meditations' => $controller->guidedMeditations(),
    'patient-guided-meditation-show' => $controller->guidedMeditationShow(),
    'patient-guided-meditation-draw-letter' => $controller->drawGuidedMeditationLetter(),
    'patient-guided-meditation-save' => $controller->saveGuidedMeditationEntry(),
    'patient-guided-meditation-audio' => $controller->streamGuidedMeditationAudio(),
    'patient-prayers' => $controller->prayers(),
    'patient-prayer-show' => $controller->prayerShow(),
    'patient-prayer-save' => $controller->savePrayerEntry(),
    'patient-prayer-audio' => $controller->streamPrayerAudio(),
    'patient-gratitude' => $controller->gratitude(),
    'patient-gratitude-store' => $controller->storeGratitudeEntry(),
    'patient-devotionals' => $controller->devotionals(),
    'patient-devotional-today' => $controller->devotionalToday(),
    'patient-devotional-save' => $controller->saveDevotionalReflection(),
    'patient-my-account' => $controller->myAccount(),
    'patient-my-account-update' => $controller->updateMyAccount(),
    'patient-device-register' => $controller->registerDevice(),
    'patient-analytics-track' => $controller->trackEvent(),
    'patient-material-asset' => $controller->streamMaterialAsset(),
    default => $controller->me(),
};