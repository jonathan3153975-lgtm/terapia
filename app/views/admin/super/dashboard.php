<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador Geral - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'dashboard'; include __DIR__ . '/../../partials/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <button class="btn-menu" id="menuBtn"><i class="fas fa-bars"></i></button>
            <div class="topbar-actions">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=therapists&subaction=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Novo Terapeuta
                </a>
            </div>
        </header>

        <div class="page-content">
            <h1>Administrador Geral</h1>
            <p class="text-muted">Visao consolidada do sistema.</p>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3"><div class="card"><div class="card-body"><small class="text-muted">Terapeutas</small><h3><?php echo (int) $totalTherapists; ?></h3></div></div></div>
                <div class="col-6 col-xl-3"><div class="card"><div class="card-body"><small class="text-muted">Pacientes cadastrados</small><h3><?php echo (int) $totalPatients; ?></h3></div></div></div>
                <div class="col-6 col-xl-3"><div class="card"><div class="card-body"><small class="text-muted">Pacientes ativos</small><h3><?php echo (int) $totalActivePatients; ?></h3></div></div></div>
                <div class="col-6 col-xl-3"><div class="card"><div class="card-body"><small class="text-muted">Arquivos no servidor</small><h3><?php echo (int) $totalFiles; ?></h3><small><?php echo number_format($usedBytes / (1024 * 1024), 2, ',', '.'); ?> MB</small></div></div></div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Terapeutas cadastrados</h5>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=therapists">Gerenciar</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Nome</th><th>Email</th><th>Status</th><th>Cadastro</th></tr></thead>
                        <tbody>
                        <?php if (empty($therapists)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum terapeuta cadastrado.</td></tr>
                        <?php else: foreach ($therapists as $therapist): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($therapist['name']); ?></td>
                                <td><?php echo htmlspecialchars($therapist['email']); ?></td>
                                <td><span class="badge bg-<?php echo ($therapist['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($therapist['status'] ?? 'inactive'); ?></span></td>
                                <td><?php echo \Helpers\Utils::formatDate($therapist['created_at'], 'd/m/Y H:i'); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</body>
</html>
