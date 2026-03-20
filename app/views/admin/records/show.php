<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Atendimento - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css" rel="stylesheet">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
    <style>
        .ql-editor { min-height: 200px; font-size: 1rem; }
        .ql-toolbar { display: none; }
        .ql-container { border-radius: 0 0 .375rem .375rem; border-color: #dee2e6 !important; }
        .ql-container.ql-snow { border: 1px solid #dee2e6; border-radius: .375rem; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo"><i class="fas fa-heartbeat"></i><span>Terapia</span></div>
            <button class="btn-menu-toggle" id="menuToggle"><i class="fas fa-times"></i></button>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard" class="nav-link">
                    <i class="fas fa-chart-line"></i><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link active">
                    <i class="fas fa-users"></i><span>Pacientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link">
                    <i class="fas fa-calendar-alt"></i><span>Agenda</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link">
                    <i class="fas fa-wallet"></i><span>Valores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i><span>Relatórios</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <li class="nav-item">
                <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i><span>Sair</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><i class="fas fa-user"></i></div>
                <div class="user-details">
                    <p class="user-name"><?php echo \Helpers\Auth::userName(); ?></p>
                    <p class="user-role">Administrador</p>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <header class="topbar">
            <button class="btn-menu" id="menuBtn"><i class="fas fa-bars"></i></button>
            <div class="topbar-actions">
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Paciente
                </a>
                <button type="button" class="btn btn-danger" onclick="deleteRecord(<?php echo $record['id']; ?>, <?php echo $patient['id']; ?>)">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Registro de Atendimento</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients">Pacientes</a></li>
                        <li class="breadcrumb-item">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>">
                                <?php echo htmlspecialchars($patient['name']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Atendimento #<?php echo $record['id']; ?></li>
                    </ol>
                </nav>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        Atendimento de <?php echo date('d/m/Y', strtotime($record['record_date'])); ?>
                    </h5>
                    <span class="text-muted small">Registrado em: <?php echo date('d/m/Y H:i', strtotime($record['created_at'])); ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Paciente</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($patient['name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Data do Atendimento</label>
                            <p class="form-control-plaintext"><?php echo date('d/m/Y', strtotime($record['record_date'])); ?></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notas do Atendimento</label>
                        <div id="notesViewer" class="border rounded p-3 bg-light" style="min-height:150px">
                            <?php echo $record['notes']; ?>
                        </div>
                    </div>

                    <?php if (!empty($record['updated_at']) && $record['updated_at'] !== $record['created_at']): ?>
                    <p class="text-muted small mb-0">Última atualização: <?php echo date('d/m/Y H:i', strtotime($record['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
<script>
function deleteRecord(id, patientId) {
    Swal.fire({
        title: 'Excluir registro?',
        text: 'Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        $.ajax({
            url: '<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=delete',
            method: 'POST',
            data: { id: id },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Excluído!', text: response.message, confirmButtonColor: '#2563eb' })
                        .then(function() {
                            window.location.href = '<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=' + patientId;
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: response.message, confirmButtonColor: '#ef4444' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao processar requisição.', confirmButtonColor: '#ef4444' });
            }
        });
    });
}
</script>
</body>
</html>
