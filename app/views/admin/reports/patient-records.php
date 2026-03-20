<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimentos por Paciente - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'reports'; include __DIR__ . '/../../partials/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <button class="btn-menu" id="menuBtn"><i class="fas fa-bars"></i></button>
            <div class="topbar-actions">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Atendimentos por Paciente</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports">Relatórios</a></li>
                        <li class="breadcrumb-item active">Por Paciente</li>
                    </ol>
                </nav>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ranking de Atendimentos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($patients)): ?>
                    <p class="text-muted text-center py-3">Nenhum paciente cadastrado.</p>
                    <?php else: ?>
                    <?php $maxRecords = max(array_column($patients, 'total_records')) ?: 1; ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente</th>
                                    <th>CPF</th>
                                    <th style="min-width:200px">Atendimentos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $i => $p): ?>
                                <tr>
                                    <td class="text-muted"><?php echo $i + 1; ?>º</td>
                                    <td>
                                        <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['cpf']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:10px">
                                                <div class="progress-bar bg-primary" style="width:<?php echo ($p['total_records'] / $maxRecords) * 100; ?>%"></div>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $p['total_records']; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</body>
</html>
