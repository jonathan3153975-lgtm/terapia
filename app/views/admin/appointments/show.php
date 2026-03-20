<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Agendamento - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>Terapia</span>
                </div>
                <button class="btn-menu-toggle" id="menuToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard" class="nav-link">
                        <i class="fas fa-chart-line"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link">
                        <i class="fas fa-users"></i><span>Pacientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link active">
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
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-id-card"></i> Ficha do Paciente
                    </a>
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=edit&id=<?php echo $appointment['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </header>

            <div class="page-content">
                <div class="page-header">
                    <h1>Detalhes do Agendamento</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar">Agenda</a></li>
                            <li class="breadcrumb-item active">Agendamento #<?php echo $appointment['id']; ?></li>
                        </ol>
                    </nav>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações do Agendamento</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $statusMap = [
                            'confirmed'  => ['label' => 'Confirmado', 'class' => 'primary'],
                            'pending'    => ['label' => 'Pendente',   'class' => 'warning'],
                            'cancelled'  => ['label' => 'Cancelado',  'class' => 'danger'],
                            'completed'  => ['label' => 'Concluído',  'class' => 'success'],
                        ];
                        $s = $statusMap[$appointment['status']] ?? ['label' => $appointment['status'], 'class' => 'secondary'];
                        ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Paciente</label>
                                <p class="form-control-plaintext">
                                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=show&id=<?php echo $patient['id']; ?>">
                                        <?php echo htmlspecialchars($patient['name']); ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Data</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?>
                                </p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Horário</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('H:i', strtotime($appointment['appointment_date'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-<?php echo $s['class']; ?> fs-6">
                                        <?php echo $s['label']; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label fw-bold">Observações</label>
                                <p class="form-control-plaintext">
                                    <?php echo !empty($appointment['notes']) ? nl2br(htmlspecialchars($appointment['notes'])) : '<span class="text-muted">Nenhuma observação.</span>'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
</body>
</html>
