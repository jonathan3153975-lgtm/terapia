<?php

namespace App\Controllers\Api;

use App\Models\ApiAccessToken;
use App\Models\AppAnalyticsEvent;
use App\Models\Appointment;
use App\Models\Book;
use App\Models\DailyMessage;
use App\Models\DevotionalEntry;
use App\Models\FileStorage;
use App\Models\GuidedMeditation;
use App\Models\HealingLetter;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\Patient;
use App\Models\PatientAppCycle;
use App\Models\PatientBookFavorite;
use App\Models\PatientDevotionalReflection;
use App\Models\PatientDevice;
use App\Models\PatientGratitudeEntry;
use App\Models\PatientGuidedMeditationEntry;
use App\Models\PatientMessageEntry;
use App\Models\PatientPrayerEntry;
use App\Models\PatientSubscription;
use App\Models\PatientVideoComment;
use App\Models\PatientVideoFavorite;
use App\Models\PatientVideoRating;
use App\Models\Prayer;
use App\Models\Task;
use App\Models\TeraTubeVideo;
use App\Models\User;
use Classes\Controller;
use Config\Config;
use Helpers\ApiPatientAuth;
use Helpers\Utils;

class PatientAppController extends Controller
{
    private User $userModel;
    private Patient $patientModel;
    private ApiAccessToken $tokenModel;
    private Appointment $appointmentModel;
    private Task $taskModel;
    private FileStorage $fileModel;
    private Material $materialModel;
    private MaterialDelivery $materialDeliveryModel;
    private Book $bookModel;
    private PatientBookFavorite $patientBookFavoriteModel;
    private TeraTubeVideo $teraTubeVideoModel;
    private PatientVideoFavorite $patientVideoFavoriteModel;
    private PatientVideoRating $patientVideoRatingModel;
    private PatientVideoComment $patientVideoCommentModel;
    private DailyMessage $dailyMessageModel;
    private PatientMessageEntry $patientMessageEntryModel;
    private GuidedMeditation $guidedMeditationModel;
    private HealingLetter $healingLetterModel;
    private PatientGuidedMeditationEntry $patientGuidedMeditationEntryModel;
    private Prayer $prayerModel;
    private PatientPrayerEntry $patientPrayerEntryModel;
    private PatientGratitudeEntry $patientGratitudeEntryModel;
    private DevotionalEntry $devotionalEntryModel;
    private PatientDevotionalReflection $patientDevotionalReflectionModel;
    private PatientSubscription $patientSubscriptionModel;
    private PatientDevice $patientDeviceModel;
    private AppAnalyticsEvent $analyticsEventModel;
    private PatientAppCycle $patientAppCycleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->patientModel = new Patient();
        $this->tokenModel = new ApiAccessToken();
        $this->appointmentModel = new Appointment();
        $this->taskModel = new Task();
        $this->fileModel = new FileStorage();
        $this->materialModel = new Material();
        $this->materialDeliveryModel = new MaterialDelivery();
        $this->bookModel = new Book();
        $this->patientBookFavoriteModel = new PatientBookFavorite();
        $this->teraTubeVideoModel = new TeraTubeVideo();
        $this->patientVideoFavoriteModel = new PatientVideoFavorite();
        $this->patientVideoRatingModel = new PatientVideoRating();
        $this->patientVideoCommentModel = new PatientVideoComment();
        $this->dailyMessageModel = new DailyMessage();
        $this->patientMessageEntryModel = new PatientMessageEntry();
        $this->guidedMeditationModel = new GuidedMeditation();
        $this->healingLetterModel = new HealingLetter();
        $this->patientGuidedMeditationEntryModel = new PatientGuidedMeditationEntry();
        $this->prayerModel = new Prayer();
        $this->patientPrayerEntryModel = new PatientPrayerEntry();
        $this->patientGratitudeEntryModel = new PatientGratitudeEntry();
        $this->devotionalEntryModel = new DevotionalEntry();
        $this->patientDevotionalReflectionModel = new PatientDevotionalReflection();
        $this->patientSubscriptionModel = new PatientSubscription();
        $this->patientDeviceModel = new PatientDevice();
        $this->analyticsEventModel = new AppAnalyticsEvent();
        $this->patientAppCycleModel = new PatientAppCycle();
    }

    public function login(): void
    {
        $this->requireMethod('POST');

        $data = $this->requestData();
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $deviceName = trim((string) ($data['device_name'] ?? 'flutter-app'));

        if ($email === '' || $password === '') {
            $this->fail('Email e senha são obrigatórios.', 422);
        }

        if (!Utils::isValidEmail($email)) {
            $this->fail('Informe um e-mail válido.', 422);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !Utils::verifyPassword($password, (string) ($user['password'] ?? ''))) {
            $this->fail('Credenciais inválidas.', 401);
        }

        if ((string) ($user['role'] ?? '') !== 'patient') {
            $this->fail('Este acesso é exclusivo para pacientes.', 403);
        }

        if ((string) ($user['status'] ?? 'inactive') !== 'active') {
            $this->fail('Seu acesso está pendente de liberação.', 403);
        }

        $patient = $this->patientModel->findById((int) ($user['patient_id'] ?? 0));
        if (!$patient) {
            $this->fail('Paciente não encontrado.', 404);
        }

        if ((string) ($patient['status'] ?? 'inactive') !== 'active') {
            $this->fail('Paciente inativo no sistema.', 403);
        }

        if ((string) ($patient['review_status'] ?? 'approved') !== 'approved') {
            $this->fail('Seu cadastro ainda está em revisão.', 403);
        }

        $issuedToken = ApiPatientAuth::issuePatientToken($user, $deviceName);
        $subscription = $this->getActiveSubscription((int) ($patient['id'] ?? 0));

        $this->ok([
            'access_token' => $issuedToken['token'],
            'token_type' => 'Bearer',
            'expires_at' => $issuedToken['expires_at'],
            'profile' => $this->buildProfile($user, $patient, $subscription),
        ], 'Login realizado com sucesso.');
    }

    public function logout(): void
    {
        $auth = $this->requirePatient(false);
        $plainToken = ApiPatientAuth::extractBearerToken();
        if ($plainToken !== null) {
            $this->tokenModel->revokeByHash(hash('sha256', $plainToken));
        }

        $this->ok([
            'user_id' => (int) ($auth['user_id'] ?? 0),
        ], 'Sessão encerrada com sucesso.');
    }

    public function me(): void
    {
        $auth = $this->requirePatient(false);
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $subscription = $this->getActiveSubscription((int) ($patient['id'] ?? 0));

        $this->ok([
            'profile' => $this->buildProfile($auth, $patient, $subscription),
        ]);
    }

    public function dashboard(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $subscription = $this->getActiveSubscription($patientId);
        $nextAppointment = $this->appointmentModel->findNextByPatient($patientId, date('Y-m-d H:i:s'));

        $chartLabels = [];
        $chartSessions = [];
        $chartTasksDone = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = new \DateTimeImmutable('-' . $i . ' months');
            $ym = $dt->format('Y-m');
            $chartLabels[] = $dt->format('m/Y');
            $chartSessions[] = $this->appointmentModel->countCompletedInMonthByPatient($patientId, $ym);
            $chartTasksDone[] = $this->taskModel->countDoneInMonthByPatient($patientId, $ym);
        }

        $pendingTasks = $this->taskModel->countPendingInboxTasksByPatient($patientId);
        $doneTasks = $this->taskModel->countDoneByPatient($patientId);
        $completionRate = ($pendingTasks + $doneTasks) > 0
            ? (int) round(($doneTasks / ($pendingTasks + $doneTasks)) * 100)
            : 0;

        $this->ok([
            'summary' => [
                'days_since_register' => $this->daysSince((string) ($patient['created_at'] ?? '')),
                'sessions_done' => $this->appointmentModel->countCompletedByPatient($patientId),
                'pending_tasks' => $pendingTasks,
                'done_tasks' => $doneTasks,
                'received_materials' => $this->taskModel->countInboxByPatientAndKind($patientId, 'material') + $this->materialDeliveryModel->countByPatient($patientId),
                'completion_rate' => $completionRate,
                'has_active_subscription' => $subscription !== null,
            ],
            'next_appointment' => $nextAppointment,
            'charts' => [
                'labels' => $chartLabels,
                'sessions' => $chartSessions,
                'tasks_done' => $chartTasksDone,
            ],
        ]);
    }

    public function tasks(): void
    {
        $auth = $this->requirePatient(false);
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $tasks = $this->taskModel->listInboxByPatientAndKind($patientId, 'task');
        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $tasks);
        $linkedMaterialsMap = $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds);
        $attachmentsByTask = $this->fileModel->listByPatientGroupedByTask($patientId);

        $payload = [];
        foreach ($tasks as $task) {
            $taskId = (int) ($task['id'] ?? 0);
            $payload[] = [
                'task' => $task,
                'linked_materials' => array_values($linkedMaterialsMap[$taskId] ?? []),
                'patient_attachments' => array_values($attachmentsByTask[$taskId] ?? []),
            ];
        }

        $this->ok([
            'items' => $payload,
        ]);
    }

    public function taskShow(): void
    {
        $auth = $this->requirePatient(false);
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $taskId = (int) ($_GET['id'] ?? 0);
        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->fail('Tarefa não encontrada.', 404);
        }

        $this->ok([
            'task' => $task,
            'linked_materials' => $this->taskModel->listLinkedMaterials($taskId),
            'therapist_files' => $this->fileModel->listByTaskAndSourceRole($taskId, 'therapist'),
            'patient_files' => $this->fileModel->listByTaskAndSourceRole($taskId, 'patient'),
        ]);
    }

    public function respondTask(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient(false);
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $data = $this->requestData();
        $taskId = (int) ($data['task_id'] ?? 0);
        $responseHtml = $this->sanitizeRichText((string) ($data['response_html'] ?? ''));

        if ($responseHtml === '') {
            $this->fail('A resposta é obrigatória.', 422);
        }

        $task = $this->taskModel->findInboxTaskByPatientAndId($patientId, $taskId, 'task');
        if (!$task) {
            $this->fail('Tarefa inválida.', 404);
        }

        if (!$this->updateTaskResponseSafely($taskId, $responseHtml)) {
            $this->fail('Falha ao enviar resposta.', 500);
        }

        $this->ok([
            'task_id' => $taskId,
            'status' => 'done',
        ], 'Resposta enviada com sucesso.');
    }

    public function materials(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $materialTasks = $this->taskModel->listInboxByPatientAndKind($patientId, 'material');
        $deliveries = $this->materialDeliveryModel->listByPatient($patientId);

        $taskIds = array_map(static fn (array $task): int => (int) ($task['id'] ?? 0), $materialTasks);
        $taskLinkedMaterials = $this->taskModel->listLinkedMaterialsGroupedByTask($taskIds);

        $assetsByMaterial = [];
        foreach ($deliveries as $delivery) {
            $materialId = (int) ($delivery['material_id'] ?? 0);
            if ($materialId <= 0 || isset($assetsByMaterial[$materialId])) {
                continue;
            }
            $assetsByMaterial[$materialId] = $this->decorateMaterialAssetsForPatient($this->materialModel->listAssets($materialId));
        }

        $this->ok([
            'tasks' => $materialTasks,
            'deliveries' => $deliveries,
            'task_linked_materials' => $taskLinkedMaterials,
            'assets_by_material' => $assetsByMaterial,
        ]);
    }

    public function books(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $search = Utils::sanitize((string) ($_GET['search'] ?? ''));

        $books = $therapistId > 0 ? $this->bookModel->listPublishedByTherapist($therapistId, $search) : [];
        $books = array_values(array_filter($books, fn (array $book): bool => $this->bookPdfExists($book)));
        $favorites = $this->patientBookFavoriteModel->listBookIdsByPatient($patientId);
        $favoriteMap = array_fill_keys($favorites, true);

        $payload = [];
        foreach ($books as $book) {
            $book['is_favorite'] = isset($favoriteMap[(int) ($book['id'] ?? 0)]);
            $book['pdf_url'] = $this->buildApiUrl('patient-book-file', ['id' => (int) ($book['id'] ?? 0)]);
            $payload[] = $book;
        }

        $this->ok([
            'items' => $payload,
        ]);
    }

    public function toggleBookFavorite(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $data = $this->requestData();
        $bookId = (int) ($data['book_id'] ?? 0);
        $book = $this->bookModel->findPublishedByPatientAndId($patientId, $bookId);
        if (!$book) {
            $this->fail('Livro não encontrado.', 404);
        }

        $wasFavorite = $this->patientBookFavoriteModel->exists($patientId, $bookId);
        if ($wasFavorite) {
            $this->patientBookFavoriteModel->deleteByPatientAndBook($patientId, $bookId);
        } else {
            $patient = $this->patientOrFail($patientId);
            $this->patientBookFavoriteModel->insertIgnore([
                'patient_id' => $patientId,
                'book_id' => $bookId,
                'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->ok([
            'book_id' => $bookId,
            'is_favorite' => !$wasFavorite,
        ], !$wasFavorite ? 'Livro salvo em Meus conteúdos.' : 'Livro removido de Meus conteúdos.');
    }

    public function videos(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $search = Utils::sanitize((string) ($_GET['search'] ?? ''));
        $therapistId = (int) ($patient['therapist_id'] ?? 0);

        $videos = $therapistId > 0 ? $this->teraTubeVideoModel->listPublishedByTherapist($therapistId, $search) : [];
        $favorites = $this->patientVideoFavoriteModel->listVideoIdsByPatient($patientId);
        $favoriteMap = array_fill_keys($favorites, true);

        $payload = [];
        foreach ($videos as $video) {
            $payload[] = $this->decorateVideo($video, isset($favoriteMap[(int) ($video['id'] ?? 0)]));
        }

        $this->ok([
            'items' => $payload,
        ]);
    }

    public function videoShow(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $videoId = (int) ($_GET['id'] ?? 0);
        $video = $this->teraTubeVideoModel->findPublishedByPatientAndId($patientId, $videoId);
        if (!$video) {
            $this->fail('Vídeo não encontrado.', 404);
        }

        $myRating = $this->patientVideoRatingModel->findByPatientAndVideo($patientId, $videoId);
        $comments = $this->patientVideoCommentModel->listByVideo($videoId);

        $this->ok([
            'video' => $this->decorateVideo($video, $this->patientVideoFavoriteModel->exists($patientId, $videoId)),
            'my_rating' => (int) ($myRating['rating'] ?? 0),
            'comments' => $comments,
        ]);
    }

    public function toggleVideoFavorite(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $data = $this->requestData();
        $videoId = (int) ($data['video_id'] ?? 0);
        $video = $this->teraTubeVideoModel->findPublishedByPatientAndId($patientId, $videoId);
        if (!$video) {
            $this->fail('Vídeo não encontrado.', 404);
        }

        $wasFavorite = $this->patientVideoFavoriteModel->exists($patientId, $videoId);
        if ($wasFavorite) {
            $this->patientVideoFavoriteModel->deleteByPatientAndVideo($patientId, $videoId);
        } else {
            $patient = $this->patientOrFail($patientId);
            $this->patientVideoFavoriteModel->insertIgnore([
                'patient_id' => $patientId,
                'video_id' => $videoId,
                'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->ok([
            'video_id' => $videoId,
            'is_favorite' => !$wasFavorite,
        ]);
    }

    public function rateVideo(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $data = $this->requestData();
        $videoId = (int) ($data['video_id'] ?? 0);
        $rating = max(1, min(5, (int) ($data['rating'] ?? 0)));
        $video = $this->teraTubeVideoModel->findPublishedByPatientAndId($patientId, $videoId);
        if (!$video) {
            $this->fail('Vídeo não encontrado.', 404);
        }

        if (!$this->patientVideoRatingModel->upsertRating($patientId, $videoId, (int) ($patient['therapist_id'] ?? 0), $rating)) {
            $this->fail('Falha ao registrar avaliação.', 500);
        }

        $this->ok([
            'video_id' => $videoId,
            'rating' => $rating,
        ]);
    }

    public function commentVideo(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $data = $this->requestData();
        $videoId = (int) ($data['video_id'] ?? 0);
        $comment = trim((string) ($data['comment'] ?? ''));

        if ($comment === '') {
            $this->fail('Escreva um comentário.', 422);
        }

        $video = $this->teraTubeVideoModel->findPublishedByPatientAndId($patientId, $videoId);
        if (!$video) {
            $this->fail('Vídeo não encontrado.', 404);
        }

        $commentId = $this->patientVideoCommentModel->insert([
            'patient_id' => $patientId,
            'video_id' => $videoId,
            'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
            'comment_text' => $comment,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($commentId === false) {
            $this->fail('Falha ao salvar comentário.', 500);
        }

        $this->ok([
            'comment_id' => $commentId,
        ], 'Comentário salvo com sucesso.');
    }

    public function messengerEntries(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $entries = $this->patientMessageEntryModel->listByPatient($patientId);
        $this->ok(['items' => $entries]);
    }

    public function drawMessenger(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        if ($therapistId <= 0) {
            $this->fail('Terapeuta não encontrado.', 404);
        }

        $allIds = $this->dailyMessageModel->listIdsByTherapist($therapistId);
        if ($allIds === []) {
            $this->fail('Nenhuma mensagem disponível para sorteio.', 404);
        }

        $cycle = $this->drawCycledItem($patientId, 'messenger', 'default', $allIds, function (array $excludedIds) use ($therapistId): ?array {
            return $this->dailyMessageModel->randomByTherapistExcludingIds($therapistId, $excludedIds);
        });

        $message = $cycle['item'];
        if (!$message) {
            $this->fail('Nenhuma mensagem disponível para sorteio.', 404);
        }

        $this->ok([
            'message' => [
                'id' => (int) ($message['id'] ?? 0),
                'category' => (string) ($message['category'] ?? ''),
                'text' => (string) ($message['message_text'] ?? ''),
            ],
            'cycle' => $cycle['meta'],
        ], 'Mensagem sorteada.');
    }

    public function saveMessenger(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $patient = $this->patientOrFail($patientId);
        $data = $this->requestData();
        $messageText = trim((string) ($data['message_text'] ?? ''));
        $patientNote = trim((string) ($data['patient_note'] ?? ''));
        if ($messageText === '' || $patientNote === '') {
            $this->fail('Mensagem e reflexão são obrigatórias.', 422);
        }

        $saved = $this->patientMessageEntryModel->insert([
            'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
            'patient_id' => $patientId,
            'message_id' => (int) ($data['message_id'] ?? 0) ?: null,
            'message_category' => $this->normalizeDailyMessageCategory((string) ($data['message_category'] ?? 'dores')),
            'message_text' => $messageText,
            'patient_note' => $patientNote,
            'share_with_therapist' => !empty($data['share_with_therapist']) ? 1 : 0,
            'drawn_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            $this->fail('Falha ao salvar reflexão.', 500);
        }

        $this->ok(['entry_id' => $saved], 'Reflexão salva com sucesso.');
    }

    public function guidedMeditations(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $meditations = (int) ($patient['therapist_id'] ?? 0) > 0
            ? $this->guidedMeditationModel->listByTherapist((int) ($patient['therapist_id'] ?? 0))
            : [];

        foreach ($meditations as &$meditation) {
            $meditation['audio_url'] = $this->buildApiUrl('patient-guided-meditation-audio', ['id' => (int) ($meditation['id'] ?? 0)]);
        }

        $this->ok(['items' => $meditations]);
    }

    public function guidedMeditationShow(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $meditationId = (int) ($_GET['id'] ?? 0);
        $meditation = $this->guidedMeditationModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $meditationId);
        if (!$meditation) {
            $this->fail('Meditação não encontrada.', 404);
        }

        $meditation['audio_url'] = $this->buildApiUrl('patient-guided-meditation-audio', ['id' => $meditationId]);

        $this->ok([
            'meditation' => $meditation,
            'entries' => $this->patientGuidedMeditationEntryModel->listByPatient((int) ($patient['id'] ?? 0), $meditationId),
        ]);
    }

    public function drawGuidedMeditationLetter(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $therapistId = (int) ($patient['therapist_id'] ?? 0);
        $meditationId = (int) ($_GET['meditation_id'] ?? 0);
        if (!$this->guidedMeditationModel->findByTherapistAndId($therapistId, $meditationId)) {
            $this->fail('Meditação não encontrada.', 404);
        }

        $allIds = $this->healingLetterModel->listIdsByTherapist($therapistId);
        if ($allIds === []) {
            $this->fail('Nenhuma carta de cura disponível para sorteio.', 404);
        }

        $cycle = $this->drawCycledItem((int) ($patient['id'] ?? 0), 'guided_meditation_letters', (string) $meditationId, $allIds, function (array $excludedIds) use ($therapistId): ?array {
            return $this->healingLetterModel->randomByTherapistExcludingIds($therapistId, $excludedIds);
        });

        $letter = $cycle['item'];
        if (!$letter) {
            $this->fail('Nenhuma carta de cura disponível para sorteio.', 404);
        }

        $this->ok([
            'letter' => [
                'id' => (int) ($letter['id'] ?? 0),
                'category' => (string) ($letter['category'] ?? ''),
                'text' => (string) ($letter['message_text'] ?? ''),
            ],
            'cycle' => $cycle['meta'],
        ], 'Carta sorteada.');
    }

    public function saveGuidedMeditationEntry(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $data = $this->requestData();
        $meditationId = (int) ($data['meditation_id'] ?? 0);
        $meditation = $this->guidedMeditationModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $meditationId);
        if (!$meditation) {
            $this->fail('Meditação não encontrada.', 404);
        }

        $letterText = trim((string) ($data['letter_text'] ?? ''));
        $patientNote = trim((string) ($data['patient_note'] ?? ''));
        if ($letterText === '' || $patientNote === '') {
            $this->fail('Carta e reflexão são obrigatórias.', 422);
        }

        $saved = $this->patientGuidedMeditationEntryModel->insert([
            'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
            'patient_id' => (int) ($patient['id'] ?? 0),
            'meditation_id' => $meditationId,
            'letter_id' => (int) ($data['letter_id'] ?? 0) ?: null,
            'letter_category' => $this->normalizeDailyMessageCategory((string) ($data['letter_category'] ?? 'dores')),
            'letter_text' => $letterText,
            'patient_note' => $patientNote,
            'share_with_therapist' => !empty($data['share_with_therapist']) ? 1 : 0,
            'listened_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            $this->fail('Falha ao salvar reflexão.', 500);
        }

        $this->ok(['entry_id' => $saved], 'Reflexão salva com sucesso.');
    }

    public function prayers(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $prayers = (int) ($patient['therapist_id'] ?? 0) > 0
            ? $this->prayerModel->listByTherapist((int) ($patient['therapist_id'] ?? 0))
            : [];

        foreach ($prayers as &$prayer) {
            $prayer['audio_url'] = $this->buildApiUrl('patient-prayer-audio', ['id' => (int) ($prayer['id'] ?? 0)]);
        }

        $this->ok(['items' => $prayers]);
    }

    public function prayerShow(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $prayerId = (int) ($_GET['id'] ?? 0);
        $prayer = $this->prayerModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $prayerId);
        if (!$prayer) {
            $this->fail('Oração não encontrada.', 404);
        }

        $prayer['audio_url'] = $this->buildApiUrl('patient-prayer-audio', ['id' => $prayerId]);

        $this->ok([
            'prayer' => $prayer,
            'entries' => $this->patientPrayerEntryModel->listByPatient((int) ($patient['id'] ?? 0), $prayerId),
        ]);
    }

    public function savePrayerEntry(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $data = $this->requestData();
        $prayerId = (int) ($data['prayer_id'] ?? 0);
        $prayer = $this->prayerModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $prayerId);
        if (!$prayer) {
            $this->fail('Oração não encontrada.', 404);
        }

        $patientNote = trim((string) ($data['patient_note'] ?? ''));
        if ($patientNote === '') {
            $this->fail('A reflexão é obrigatória.', 422);
        }

        $saved = $this->patientPrayerEntryModel->insert([
            'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
            'patient_id' => (int) ($patient['id'] ?? 0),
            'prayer_id' => $prayerId,
            'patient_note' => $patientNote,
            'share_with_therapist' => !empty($data['share_with_therapist']) ? 1 : 0,
            'listened_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            $this->fail('Falha ao salvar reflexão.', 500);
        }

        $this->ok(['entry_id' => $saved], 'Reflexão salva com sucesso.');
    }

    public function gratitude(): void
    {
        $auth = $this->requirePatient();
        $patientId = (int) ($auth['patient_id'] ?? 0);
        $context = $this->resolveGratitudeCycleContext($patientId);
        $entries = $this->patientGratitudeEntryModel->listByPatientAndCycle($patientId, (int) $context['current_cycle']);

        $this->ok([
            'entries' => $entries,
            'current_cycle' => (int) $context['current_cycle'],
            'next_cycle' => (int) $context['next_cycle'],
            'next_day' => (int) $context['next_day'],
            'is_current_cycle_locked' => (bool) $context['is_locked'],
        ]);
    }

    public function storeGratitudeEntry(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $data = $this->requestData();
        $contentHtml = $this->sanitizeRichText((string) ($data['content_html'] ?? ''));
        if (trim(strip_tags($contentHtml)) === '') {
            $this->fail('Escreva sua gratidão antes de salvar.', 422);
        }

        $context = $this->resolveGratitudeCycleContext((int) ($patient['id'] ?? 0));
        $saved = $this->patientGratitudeEntryModel->insert([
            'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
            'patient_id' => (int) ($patient['id'] ?? 0),
            'cycle_number' => (int) $context['next_cycle'],
            'day_number' => (int) $context['next_day'],
            'content_html' => $contentHtml,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            $this->fail('Falha ao salvar o registro de gratidão.', 500);
        }

        $this->ok([
            'entry_id' => $saved,
            'cycle_number' => (int) $context['next_cycle'],
            'day_number' => (int) $context['next_day'],
        ], 'Registro salvo com sucesso.');
    }

    public function devotionals(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $today = date('Y-m-d');
        $todayEntry = (int) ($patient['therapist_id'] ?? 0) > 0
            ? $this->devotionalEntryModel->findByTherapistAndDate((int) ($patient['therapist_id'] ?? 0), $today)
            : null;

        $this->ok([
            'today_entry' => $todayEntry,
            'records' => $this->patientDevotionalReflectionModel->listByPatient((int) ($patient['id'] ?? 0)),
        ]);
    }

    public function devotionalToday(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $entry = $this->devotionalEntryModel->findByTherapistAndDate((int) ($patient['therapist_id'] ?? 0), date('Y-m-d'));
        if (!$entry) {
            $this->fail('Nenhum devocional disponível para hoje.', 404);
        }

        $existing = $this->patientDevotionalReflectionModel->findByPatientAndEntry((int) ($patient['id'] ?? 0), (int) ($entry['id'] ?? 0));
        $this->ok([
            'entry' => $entry,
            'existing_reflection' => $existing,
        ]);
    }

    public function saveDevotionalReflection(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $data = $this->requestData();
        $entryId = (int) ($data['devotional_entry_id'] ?? 0);
        $reflectionHtml = $this->sanitizeRichText((string) ($data['reflection_html'] ?? ''));
        if (trim(strip_tags($reflectionHtml)) === '') {
            $this->fail('Escreva sua reflexão antes de salvar.', 422);
        }

        $entry = $this->devotionalEntryModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $entryId);
        if (!$entry) {
            $this->fail('Registro devocional não encontrado.', 404);
        }

        if ((string) ($entry['entry_date'] ?? '') !== date('Y-m-d')) {
            $this->fail('O salvamento só é permitido para o devocional do dia.', 422);
        }

        $compiledHtml = $this->buildPatientDevotionalCompiledHtml($entry, $reflectionHtml, $this->formatLongDate(date('Y-m-d')));
        $existing = $this->patientDevotionalReflectionModel->findByPatientAndEntry((int) ($patient['id'] ?? 0), $entryId);
        if ($existing) {
            $saved = $this->patientDevotionalReflectionModel->updateById((int) ($existing['id'] ?? 0), [
                'reflection_html' => $reflectionHtml,
                'compiled_html' => $compiledHtml,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $saved = $this->patientDevotionalReflectionModel->insert([
                'devotional_id' => (int) ($entry['devotional_id'] ?? 0),
                'devotional_entry_id' => $entryId,
                'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
                'patient_id' => (int) ($patient['id'] ?? 0),
                'reflection_html' => $reflectionHtml,
                'compiled_html' => $compiledHtml,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($saved === false) {
            $this->fail('Falha ao salvar sua reflexão.', 500);
        }

        $this->ok([
            'entry_id' => $entryId,
        ], 'Reflexão do devocional salva com sucesso.');
    }

    public function myAccount(): void
    {
        $auth = $this->requirePatient(false);
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $this->ok([
            'user' => [
                'id' => (int) ($auth['user_id'] ?? 0),
                'name' => (string) ($auth['user_name'] ?? ''),
                'email' => (string) ($auth['user_email'] ?? ''),
                'phone' => (string) ($auth['user_phone'] ?? ''),
            ],
            'patient' => $patient,
        ]);
    }

    public function updateMyAccount(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient(false);
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $userId = (int) ($auth['user_id'] ?? 0);
        $data = $this->requestData();
        $section = (string) ($data['section'] ?? 'profile');

        if ($section === 'password') {
            $newPassword = (string) ($data['new_password'] ?? '');
            $confirmPassword = (string) ($data['confirm_password'] ?? '');
            if (strlen($newPassword) < 6) {
                $this->fail('A nova senha deve ter pelo menos 6 caracteres.', 422);
            }
            if ($newPassword !== $confirmPassword) {
                $this->fail('A confirmação de senha não confere.', 422);
            }

            $updated = $this->userModel->updateById($userId, [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$updated) {
                $this->fail('Falha ao atualizar senha.', 500);
            }

            $this->ok([], 'Senha alterada com sucesso.');
        }

        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if ($name === '' || $email === '' || $phone === '') {
            $this->fail('Preencha todos os campos obrigatórios.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('E-mail inválido.', 422);
        }

        $existing = $this->userModel->findByEmail($email);
        if ($existing && (int) ($existing['id'] ?? 0) !== $userId) {
            $this->fail('Este e-mail já está em uso por outra conta.', 422);
        }

        $now = date('Y-m-d H:i:s');
        $userUpdated = $this->userModel->updateById($userId, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => $now,
        ]);

        $patientUpdated = $this->patientModel->updateById((int) ($patient['id'] ?? 0), [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => $now,
        ]);

        if (!$userUpdated && !$patientUpdated) {
            $this->fail('Nenhuma alteração foi aplicada.', 409);
        }

        $this->ok([], 'Dados atualizados com sucesso.');
    }

    public function registerDevice(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient(false);
        $data = $this->requestData();
        $deviceIdentifier = trim((string) ($data['device_identifier'] ?? ''));
        if ($deviceIdentifier === '') {
            $this->fail('device_identifier é obrigatório.', 422);
        }

        $saved = $this->patientDeviceModel->upsertDevice([
            'patient_id' => (int) ($auth['patient_id'] ?? 0),
            'user_id' => (int) ($auth['user_id'] ?? 0),
            'platform' => $this->normalizePlatform((string) ($data['platform'] ?? 'android')),
            'device_name' => trim((string) ($data['device_name'] ?? 'Flutter Device')),
            'device_identifier' => $deviceIdentifier,
            'push_token' => trim((string) ($data['push_token'] ?? '')),
            'app_version' => trim((string) ($data['app_version'] ?? '')),
            'locale' => trim((string) ($data['locale'] ?? 'pt-BR')),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$saved) {
            $this->fail('Falha ao registrar dispositivo.', 500);
        }

        $this->ok([], 'Dispositivo registrado com sucesso.');
    }

    public function trackEvent(): void
    {
        $this->requireMethod('POST');
        $auth = $this->requirePatient(false);
        $data = $this->requestData();
        $eventName = trim((string) ($data['event_name'] ?? ''));
        if ($eventName === '') {
            $this->fail('event_name é obrigatório.', 422);
        }

        $saved = $this->analyticsEventModel->logEvent([
            'patient_id' => (int) ($auth['patient_id'] ?? 0),
            'user_id' => (int) ($auth['user_id'] ?? 0),
            'platform' => $this->normalizePlatform((string) ($data['platform'] ?? 'android')),
            'event_name' => $eventName,
            'event_context' => trim((string) ($data['event_context'] ?? '')),
            'payload_json' => json_encode($data['payload'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        if ($saved === false) {
            $this->fail('Falha ao registrar evento.', 500);
        }

        $this->ok(['event_id' => $saved], 'Evento registrado.');
    }

    public function streamBookPdf(): void
    {
        $auth = $this->requirePatient();
        $bookId = (int) ($_GET['id'] ?? 0);
        $book = $this->bookModel->findPublishedByPatientAndId((int) ($auth['patient_id'] ?? 0), $bookId);
        if (!$book || empty($book['pdf_path'])) {
            http_response_code(404);
            echo 'Livro não encontrado.';
            exit;
        }

        $this->streamPdfFile((string) $book['pdf_path'], (string) ($book['pdf_original_name'] ?? 'livro.pdf'));
    }

    public function streamMaterialAsset(): void
    {
        $auth = $this->requirePatient();
        $assetId = (int) ($_GET['id'] ?? 0);
        $asset = $this->materialModel->findAssetByPatientAndId((int) ($auth['patient_id'] ?? 0), $assetId);
        if (!$asset || empty($asset['file_path'])) {
            http_response_code(404);
            echo 'Material não encontrado.';
            exit;
        }

        $assetType = (string) ($asset['asset_type'] ?? '');
        $downloadName = (string) ($asset['file_name'] ?? 'material');
        $mimeType = trim((string) ($asset['mime_type'] ?? ''));

        if ($assetType === 'pdf') {
            $this->streamPdfFile((string) $asset['file_path'], $downloadName);
        }
        if ($assetType === 'video') {
            $this->streamVideoFile((string) $asset['file_path'], $downloadName, $mimeType !== '' ? $mimeType : 'video/mp4');
        }
        if ($assetType === 'audio') {
            $this->streamAudioFile((string) $asset['file_path'], $downloadName, $mimeType !== '' ? $mimeType : 'audio/mpeg');
        }
        if ($assetType === 'image') {
            $this->streamInlineFile((string) $asset['file_path'], $downloadName, $mimeType !== '' ? $mimeType : 'image/jpeg');
        }

        http_response_code(415);
        echo 'Tipo de material não suportado.';
        exit;
    }

    public function streamTeraTubeVideo(): void
    {
        $auth = $this->requirePatient();
        $videoId = (int) ($_GET['id'] ?? 0);
        $video = $this->teraTubeVideoModel->findPublishedByPatientAndId((int) ($auth['patient_id'] ?? 0), $videoId);
        if (!$video || (string) ($video['source_type'] ?? '') !== 'upload' || empty($video['video_path'])) {
            http_response_code(404);
            echo 'Vídeo não encontrado.';
            exit;
        }

        $this->streamVideoFile((string) $video['video_path'], (string) ($video['video_original_name'] ?? 'video.mp4'), (string) ($video['video_mime_type'] ?? 'video/mp4'));
    }

    public function streamGuidedMeditationAudio(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $meditationId = (int) ($_GET['id'] ?? 0);
        $meditation = $this->guidedMeditationModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $meditationId);
        if (!$meditation || empty($meditation['audio_path'])) {
            http_response_code(404);
            echo 'Áudio não encontrado.';
            exit;
        }

        $this->streamAudioFile((string) $meditation['audio_path'], (string) ($meditation['audio_name'] ?? 'meditacao.mp3'));
    }

    public function streamPrayerAudio(): void
    {
        $auth = $this->requirePatient();
        $patient = $this->patientOrFail((int) ($auth['patient_id'] ?? 0));
        $prayerId = (int) ($_GET['id'] ?? 0);
        $prayer = $this->prayerModel->findByTherapistAndId((int) ($patient['therapist_id'] ?? 0), $prayerId);
        if (!$prayer || empty($prayer['audio_path'])) {
            http_response_code(404);
            echo 'Áudio não encontrado.';
            exit;
        }

        $this->streamAudioFile((string) $prayer['audio_path'], (string) ($prayer['audio_name'] ?? 'oracao.mp3'));
    }

    private function requestData(): array
    {
        $raw = file_get_contents('php://input');
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if ($raw !== false && $raw !== '' && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST;
    }

    private function requireMethod(string $method): void
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== strtoupper($method)) {
            $this->fail('Método não permitido.', 405);
        }
    }

    private function requirePatient(bool $enforceSubscription = true): array
    {
        $auth = ApiPatientAuth::authenticatedPatient();
        if (!$auth) {
            $this->fail('Token inválido ou expirado.', 401);
        }

        if ($enforceSubscription) {
            $this->ensureActiveSubscription((int) ($auth['patient_id'] ?? 0), (string) ($_GET['action'] ?? ''));
        }

        return [
            'token_id' => (int) ($auth['id'] ?? 0),
            'user_id' => (int) ($auth['user_id'] ?? 0),
            'patient_id' => (int) ($auth['patient_id'] ?? 0),
            'therapist_id' => (int) ($auth['therapist_id'] ?? 0),
            'user_name' => (string) ($auth['user_name'] ?? ''),
            'user_email' => (string) ($auth['user_email'] ?? ''),
            'user_phone' => (string) ($auth['user_phone'] ?? ''),
        ];
    }

    private function ensureActiveSubscription(int $patientId, string $action): void
    {
        $alwaysAllowed = [
            'patient-me',
            'patient-dashboard',
            'patient-tasks',
            'patient-task-show',
            'patient-task-respond',
            'patient-my-account',
            'patient-my-account-update',
            'patient-device-register',
            'patient-analytics-track',
            'patient-book-file',
            'patient-material-asset',
        ];

        if (in_array($action, $alwaysAllowed, true)) {
            return;
        }

        $subscription = $this->getActiveSubscription($patientId);
        if ($subscription !== null) {
            return;
        }

        $this->fail('Escolha um plano para continuar usando a plataforma.', 402, ['code' => 'subscription_required']);
    }

    private function getActiveSubscription(int $patientId): ?array
    {
        $this->patientSubscriptionModel->markExpiredSubscriptions();
        return $this->patientSubscriptionModel->findActiveByPatient($patientId);
    }

    private function patientOrFail(int $patientId): array
    {
        $patient = $this->patientModel->findById($patientId);
        if (!$patient) {
            $this->fail('Paciente não encontrado.', 404);
        }

        return $patient;
    }

    private function ok(array $data = [], string $message = 'OK'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function fail(string $message, int $statusCode = 400, array $extra = []): void
    {
        $this->json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $statusCode);
    }

    private function buildProfile(array $user, array $patient, ?array $subscription): array
    {
        return [
            'user' => [
                'id' => (int) ($user['id'] ?? $user['user_id'] ?? 0),
                'name' => (string) ($user['name'] ?? $user['user_name'] ?? ''),
                'email' => (string) ($user['email'] ?? $user['user_email'] ?? ''),
                'phone' => (string) ($user['phone'] ?? $user['user_phone'] ?? ''),
            ],
            'patient' => [
                'id' => (int) ($patient['id'] ?? 0),
                'name' => (string) ($patient['name'] ?? ''),
                'email' => (string) ($patient['email'] ?? ''),
                'phone' => (string) ($patient['phone'] ?? ''),
                'therapist_id' => (int) ($patient['therapist_id'] ?? 0),
                'status' => (string) ($patient['status'] ?? 'inactive'),
                'review_status' => (string) ($patient['review_status'] ?? 'approved'),
            ],
            'subscription' => $subscription,
        ];
    }

    private function buildApiUrl(string $action, array $query = []): string
    {
        $base = rtrim((string) Config::get('APP_URL', ''), '/');
        $params = array_merge(['action' => $action], $query);
        return $base . '/api.php?' . http_build_query($params);
    }

    private function decorateMaterialAssetsForPatient(array $assets): array
    {
        foreach ($assets as &$asset) {
            $assetId = (int) ($asset['id'] ?? 0);
            $assetType = (string) ($asset['asset_type'] ?? '');
            $asset['view_url'] = $assetType === 'url'
                ? (string) ($asset['file_url'] ?? '')
                : $this->buildApiUrl('patient-material-asset', ['id' => $assetId]);
        }

        return $assets;
    }

    private function decorateVideo(array $video, bool $isFavorite): array
    {
        $video['is_favorite'] = $isFavorite;
        $video['watch_url'] = $this->buildApiUrl('patient-video-show', ['id' => (int) ($video['id'] ?? 0)]);
        $video['stream_url'] = (string) ($video['source_type'] ?? '') === 'upload'
            ? $this->buildApiUrl('patient-video-file', ['id' => (int) ($video['id'] ?? 0)])
            : '';
        return $video;
    }

    private function normalizeDailyMessageCategory(string $value): string
    {
        $value = strtolower(trim($value));
        return in_array($value, ['dores', 'reflexivas', 'cura', 'motivacionais', 'conflitos'], true) ? $value : 'dores';
    }

    private function daysSince(string $createdAt): int
    {
        if ($createdAt === '') {
            return 0;
        }

        $created = strtotime($createdAt);
        if ($created === false) {
            return 0;
        }

        return max(0, (int) floor((time() - $created) / 86400));
    }

    private function sanitizeRichText(string $html): string
    {
        $value = trim($html);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/<\s*(script|style)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $value) ?? '';
        $value = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $value) ?? '';
        $value = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $value) ?? '';
        $value = preg_replace('/\s(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '', $value) ?? '';
        $value = preg_replace('/\s(href|src)\s*=\s*\'\s*javascript:[^\']*\'/i', '', $value) ?? '';

        return strip_tags($value, '<p><br><strong><b><em><i><u><s><ol><ul><li><blockquote><pre><code><h1><h2><h3><a><span>');
    }

    private function updateTaskResponseSafely(int $taskId, string $responseHtml): bool
    {
        $updated = $this->taskModel->updateById($taskId, [
            'patient_response_html' => $responseHtml,
            'responded_at' => date('Y-m-d H:i:s'),
            'status' => 'done',
        ]);

        if ($updated) {
            return true;
        }

        return $this->taskModel->updateById($taskId, [
            'status' => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function resolveGratitudeCycleContext(int $patientId): array
    {
        $latestStats = $this->patientGratitudeEntryModel->getLatestCycleStatsByPatient($patientId);
        if (!$latestStats) {
            return [
                'current_cycle' => 1,
                'next_cycle' => 1,
                'next_day' => 1,
                'is_locked' => false,
            ];
        }

        $maxCycle = (int) ($latestStats['cycle_number'] ?? 1);
        $cycleStats = $this->patientGratitudeEntryModel->getCycleStatsByPatient($patientId, $maxCycle);
        $maxDay = (int) ($cycleStats['max_day'] ?? 0);

        if ($maxDay >= 30) {
            return [
                'current_cycle' => $maxCycle,
                'next_cycle' => $maxCycle + 1,
                'next_day' => 1,
                'is_locked' => true,
            ];
        }

        return [
            'current_cycle' => $maxCycle,
            'next_cycle' => $maxCycle,
            'next_day' => $maxDay + 1,
            'is_locked' => false,
        ];
    }

    private function buildPatientDevotionalCompiledHtml(array $entry, string $reflectionHtml, string $currentDateLabel): string
    {
        $title = htmlspecialchars((string) ($entry['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $theme = htmlspecialchars((string) ($entry['theme'] ?? ''), ENT_QUOTES, 'UTF-8');
        $word = htmlspecialchars((string) ($entry['word_of_god'] ?? ''), ENT_QUOTES, 'UTF-8');
        $entryDate = htmlspecialchars((string) ($entry['entry_date'] ?? ''), ENT_QUOTES, 'UTF-8');
        $devotionalHtml = (string) ($entry['text_content'] ?? '');
        $currentDateLabelSafe = htmlspecialchars($currentDateLabel, ENT_QUOTES, 'UTF-8');

        return '<html><head><meta charset="utf-8"><title>Devocional do dia</title></head><body>'
            . '<h2>Devocional do dia</h2>'
            . '<p><strong>Data atual:</strong> ' . $currentDateLabelSafe . '</p>'
            . '<p><strong>Data do registro:</strong> ' . $entryDate . '</p>'
            . '<p><strong>Tema:</strong> ' . $theme . '</p>'
            . '<h3>' . $title . '</h3>'
            . '<p><strong>Palavra de Deus:</strong> ' . $word . '</p>'
            . '<div>' . $devotionalHtml . '</div>'
            . '<hr><h4>Minha reflexão</h4><div>' . $reflectionHtml . '</div>'
            . '</body></html>';
    }

    private function formatLongDate(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $months = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho',
            7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        return date('d', $timestamp) . ' de ' . ($months[(int) date('n', $timestamp)] ?? date('m', $timestamp)) . ' de ' . date('Y', $timestamp);
    }

    private function drawCycledItem(int $patientId, string $module, string $scopeKey, array $allIds, callable $resolver): array
    {
        $drawnIds = $this->patientAppCycleModel->getDrawnIds($patientId, $module, $scopeKey);
        $drawnIds = array_values(array_intersect($allIds, $drawnIds));
        $restarted = false;

        if (count($drawnIds) >= count($allIds)) {
            $drawnIds = [];
            $this->patientAppCycleModel->saveDrawnIds($patientId, $module, $scopeKey, []);
            $restarted = true;
        }

        $item = $resolver($drawnIds);
        $itemId = (int) (($item['id'] ?? 0));
        if ($itemId > 0) {
            $drawnIds[] = $itemId;
            $this->patientAppCycleModel->saveDrawnIds($patientId, $module, $scopeKey, $drawnIds);
        }

        return [
            'item' => $item,
            'meta' => [
                'drawn_count' => min(count(array_unique($drawnIds)), count($allIds)),
                'total_count' => count($allIds),
                'remaining_count' => max(count($allIds) - count(array_unique($drawnIds)), 0),
                'restarted' => $restarted,
            ],
        ];
    }

    private function normalizePlatform(string $platform): string
    {
        $platform = strtolower(trim($platform));
        return in_array($platform, ['android', 'ios'], true) ? $platform : 'android';
    }

    private function bookPdfExists(array $book): bool
    {
        $path = trim((string) ($book['pdf_path'] ?? ''));
        if ($path === '') {
            return false;
        }

        return is_file(dirname(__DIR__, 3) . '/' . ltrim($path, '/'));
    }

    private function streamPdfFile(string $relativePath, string $downloadName): void
    {
        $this->streamInlineFile($relativePath, $downloadName, 'application/pdf');
    }

    private function streamVideoFile(string $relativePath, string $downloadName, string $mimeType = 'video/mp4'): void
    {
        $this->streamInlineFile($relativePath, $downloadName, $mimeType);
    }

    private function streamAudioFile(string $relativePath, string $downloadName, string $mimeType = 'audio/mpeg'): void
    {
        $this->streamInlineFile($relativePath, $downloadName, $mimeType);
    }

    private function streamInlineFile(string $relativePath, string $downloadName, string $mimeType): void
    {
        $absolutePath = dirname(__DIR__, 3) . '/' . ltrim($relativePath, '/');
        if (!is_file($absolutePath)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: inline; filename="' . rawurlencode($downloadName) . '"');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
    }
}