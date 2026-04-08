<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Paciente - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Portal do Paciente</h1>
        <div>
            <a class="btn btn-outline-primary" href="<?php echo \Config\Config::APP_URL; ?>/patient.php?action=tasks">Minhas tarefas</a>
            <a class="btn btn-outline-danger" href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout">Sair</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Sessoes</small><h3><?php echo (int) $totalSessions; ?></h3></div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Tarefas</small><h3><?php echo (int) $totalTasks; ?></h3></div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Pendentes</small><h3><?php echo (int) $pendingTasks; ?></h3></div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Realizadas</small><h3><?php echo (int) $doneTasks; ?></h3></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5>Material acessado</h5>
                    <p class="display-6 mb-0"><?php echo (int) $materialAccessed; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-warning">
                <div class="card-body">
                    <h5>Mensagem diaria</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($dailyMessage); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
