<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terapeutas - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'therapists'; include __DIR__ . '/../../../partials/sidebar.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Terapeutas</h1>
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=therapists&subaction=create" class="btn btn-primary">Novo terapeuta</a>
            </div>
            <div class="card">
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
                                <td><?php echo htmlspecialchars($therapist['status'] ?? 'inactive'); ?></td>
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
</body>
</html>
