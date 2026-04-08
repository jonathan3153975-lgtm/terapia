<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historico - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'patients'; include __DIR__ . '/../../partials/sidebar.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="btn btn-secondary mb-3">Voltar</a>
            <h1 class="mb-1">Historico de <?php echo htmlspecialchars($patient['name']); ?></h1>

            <ul class="nav nav-tabs mb-3" id="historyTabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-atendimentos" type="button">Atendimentos</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tarefas" type="button">Tarefas</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-interacao" type="button">Interacao</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-atendimentos">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Atendimentos registrados</h5></div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead><tr><th>Data</th><th>Descricao</th><th>Historico</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($records)): ?>
                                            <tr><td colspan="3" class="text-center text-muted py-4">Nenhum atendimento.</td></tr>
                                        <?php else: foreach ($records as $record): ?>
                                            <tr>
                                                <td><?php echo \Helpers\Utils::formatDate($record['record_date'], 'd/m/Y'); ?></td>
                                                <td><?php echo htmlspecialchars($record['description'] ?? '-'); ?></td>
                                                <td><?php echo mb_substr(strip_tags($record['notes'] ?? ''), 0, 90); ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Novo atendimento</h5></div>
                                <div class="card-body">
                                    <form class="form-submit" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=store-history-record">
                                        <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
                                        <div class="mb-2"><label class="form-label">Data</label><input class="form-control" type="date" name="record_date" required></div>
                                        <div class="mb-2"><label class="form-label">Descricao</label><input class="form-control" type="text" name="description" required></div>
                                        <div class="mb-2"><label class="form-label">Historico</label><div id="record-editor" style="height:180px;"></div><input type="hidden" name="notes" id="record-notes"></div>
                                        <button class="btn btn-primary" type="submit">Salvar atendimento</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-tarefas">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Tarefas atribuidas</h5></div>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead><tr><th>Data</th><th>Titulo</th><th>Status</th><th>Envio</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($tasks)): ?>
                                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma tarefa.</td></tr>
                                        <?php else: foreach ($tasks as $task): ?>
                                            <tr>
                                                <td><?php echo \Helpers\Utils::formatDate($task['due_date'], 'd/m/Y'); ?></td>
                                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                                <td><?php echo htmlspecialchars($task['status'] ?? 'pending'); ?></td>
                                                <td>
                                                    <?php if ((int)($task['sent_to_patient'] ?? 0) === 1): ?>
                                                        <span class="badge bg-success">Enviada</span>
                                                    <?php else: ?>
                                                        <form class="form-submit d-inline" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=send-task">
                                                            <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary">Enviar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">Nova tarefa</h5></div>
                                <div class="card-body">
                                    <form class="form-submit" method="POST" enctype="multipart/form-data" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=store-task">
                                        <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
                                        <div class="mb-2"><label class="form-label">Data</label><input type="date" class="form-control" name="due_date" required></div>
                                        <div class="mb-2"><label class="form-label">Titulo</label><input type="text" class="form-control" name="title" required></div>
                                        <div class="mb-2"><label class="form-label">Descricao da tarefa</label><div id="task-editor" style="height:220px;"></div><input type="hidden" name="description" id="task-description"></div>
                                        <div class="mb-2"><label class="form-label">Anexo (PDF/Imagem)</label><input type="file" class="form-control" name="attachment_file" accept=".pdf,image/*"></div>
                                        <div class="mb-2"><label class="form-label">Link</label><input type="url" class="form-control" name="attachment_link" placeholder="https://..."></div>
                                        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="send_to_patient" name="send_to_patient" value="1"><label class="form-check-label" for="send_to_patient">Enviar direto ao paciente</label></div>
                                        <button type="submit" class="btn btn-primary">Salvar tarefa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-interacao">
                    <div class="card"><div class="card-body text-muted">A aba Interacao foi reservada para implementacao futura.</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
<script>
const recordEditor = initializeQuillEditor('record-editor');
const taskEditor = initializeQuillEditor('task-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ color: [] }, { background: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['blockquote', 'code-block', 'link'],
            ['clean']
        ]
    }
});

$('form').on('submit', function() {
    if ($('#record-notes').length) {
        $('#record-notes').val(recordEditor.root.innerHTML);
    }
    if ($('#task-description').length) {
        $('#task-description').val(taskEditor.root.innerHTML);
    }
});

if (window.location.hash === '#tab-tarefas') {
    const trigger = document.querySelector('[data-bs-target="#tab-tarefas"]');
    if (trigger) {
        new bootstrap.Tab(trigger).show();
    }
}
</script>
</body>
</html>
