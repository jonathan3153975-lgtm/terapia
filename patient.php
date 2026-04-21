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
    'subscription-plans' => $portal->subscriptionPlans(),
    'subscription-checkout' => $portal->startSubscriptionCheckout(),
    'subscription-return' => $portal->subscriptionReturn(),
    'dashboard' => $portal->dashboard(),
    'tasks' => $portal->tasks(),
    'materials' => $portal->materials(),
    'material-asset-view' => $portal->streamMaterialAsset(),
    'books' => $portal->books(),
    'book-view' => $portal->streamBookPdf(),
    'book-toggle-favorite' => $portal->toggleBookFavorite(),
    'teratube' => $portal->teraTube(),
    'teratube-watch' => $portal->teraTubeWatch(),
    'teratube-file' => $portal->streamTeraTubeVideo(),
    'teratube-toggle-favorite' => $portal->toggleTeraTubeFavorite(),
    'teratube-rate' => $portal->rateTeraTubeVideo(),
    'teratube-comment' => $portal->commentTeraTubeVideo(),
    'teratube-comment-rate' => $portal->rateTeraTubeComment(),
    'my-contents' => $portal->myContents(),
    'messenger' => $portal->messenger(),
    'messenger-draw' => $portal->drawMessengerMessage(),
    'messenger-save' => $portal->saveMessengerEntry(),
    'gratitude' => $portal->gratitude(),
    'gratitude-store' => $portal->storeGratitudeEntry(),
    'gratitude-show' => $portal->showGratitudeEntry(),
    'gratitude-edit' => $portal->editGratitudeEntry(),
    'gratitude-update' => $portal->updateGratitudeEntry(),
    'gratitude-delete' => $portal->deleteGratitudeEntry(),
    'my-account' => $portal->myAccount(),
    'my-account-save' => $portal->saveMyAccount(),
    'devotionals' => $portal->devotionals(),
    'devotional-today' => $portal->devotionalToday(),
    'devotional-save' => $portal->saveDevotionalReflection(),
    'devotional-record-show' => $portal->showPatientDevotionalRecord(),
    'devotional-record-download' => $portal->downloadPatientDevotionalRecord(),
    'father-word' => $portal->fatherWord(),
    'father-word-draw' => $portal->drawFatherWord(),
    'father-word-save' => $portal->saveFatherWordEntry(),
    'guided-meditations' => $portal->guidedMeditations(),
    'guided-meditation-show' => $portal->guidedMeditationShow(),
    'guided-meditation-audio' => $portal->streamGuidedMeditationAudio(),
    'guided-meditation-draw-letter' => $portal->drawGuidedMeditationLetter(),
    'guided-meditation-save' => $portal->saveGuidedMeditationEntry(),
    'breathing-game' => $portal->breathingGame(),
    'prayers' => $portal->prayers(),
    'prayer-show' => $portal->prayerShow(),
    'prayer-audio' => $portal->streamPrayerAudio(),
    'prayer-save' => $portal->savePrayerEntry(),
    'task-material' => $portal->showTaskMaterial(),
    'task-respond' => $portal->respondTask(),
    'task-respond-submit' => $portal->submitTaskResponse(),
    'virtual-tree-of-life' => $portal->showVirtualTask('tree_of_life'),
    'virtual-task-complete' => $portal->completeVirtualTask(),
    default => $portal->dashboard(),
};

