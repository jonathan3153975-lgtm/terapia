<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Terapeuta - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'therapists'; include __DIR__ . '/../../../partials/sidebar.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=therapists" class="btn btn-secondary mb-3">Voltar</a>
            <div class="card" style="max-width:700px;">
                <div class="card-header"><h5 class="mb-0">Cadastrar terapeuta</h5></div>
                <div class="card-body">
                    <form class="form-submit" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=therapists&subaction=store">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha inicial</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Salvar terapeuta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</body>
</html>
