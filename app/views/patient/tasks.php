<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Tarefas - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Minhas tarefas</h1>
        <a class="btn btn-outline-secondary" href="<?php echo \Config\Config::APP_URL; ?>/patient.php?action=dashboard">Voltar</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Data</th><th>Titulo</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma tarefa recebida.</td></tr>
                <?php else: foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo \Helpers\Utils::formatDate($task['due_date'], 'd/m/Y'); ?></td>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td>
                            <?php if (($task['status'] ?? 'pending') !== 'done'): ?>
                                <form class="form-submit d-inline" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/patient.php?action=mark-task-done">
                                    <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Marcar realizada</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="bg-light"><?php echo $task['description']; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
