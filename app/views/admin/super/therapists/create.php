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
    <?php $activeMenu = 'therapists'; include __DIR__ . '/../../partials/sidebar.php'; ?>
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
                            <div class="input-group">
                                <input type="text" class="form-control" name="password" id="therapistPassword" required>
                                <button type="button" class="btn btn-outline-secondary" id="btnGeneratePassword">Gerar senha</button>
                            </div>
                            <small class="text-muted">Use o botao para gerar uma senha forte automaticamente.</small>
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
<script>
function generateStrongPassword(length = 12) {
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower = 'abcdefghijkmnopqrstuvwxyz';
    const numbers = '23456789';
    const symbols = '!@#$%*?';
    const all = upper + lower + numbers + symbols;

    let password = '';
    password += upper[Math.floor(Math.random() * upper.length)];
    password += lower[Math.floor(Math.random() * lower.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];

    for (let i = password.length; i < length; i++) {
        password += all[Math.floor(Math.random() * all.length)];
    }

    return password.split('').sort(() => Math.random() - 0.5).join('');
}

$('#btnGeneratePassword').on('click', function() {
    const password = generateStrongPassword(12);
    $('#therapistPassword').val(password).trigger('change');
});
</script>
</body>
</html>
