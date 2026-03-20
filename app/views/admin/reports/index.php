<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - <?php echo \Config\Config::APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
    <style>
        .stat-card { border-left: 4px solid; }
        .stat-card.blue   { border-color: #0d6efd; }
        .stat-card.green  { border-color: #198754; }
        .stat-card.orange { border-color: #fd7e14; }
        .stat-card.purple { border-color: #6f42c1; }
        .stat-icon { font-size: 2rem; opacity: .15; }
        .month-names { font-size: .75rem; }
    </style>
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
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Pacientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="nav-link">
                        <i class="fas fa-wallet"></i>
                        <span>Valores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="nav-link active">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </li>
                <li class="nav-divider"></li>
                <li class="nav-item">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <p class="user-name"><?php echo \Helpers\Auth::userName(); ?></p>
                        <p class="user-role">Administrador</p>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOPBAR -->
            <header class="topbar">
                <button class="btn-menu" id="menuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-actions">
                    <!-- filtro mês/ano -->
                    <form method="GET" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="action" value="reports">
                        <input type="hidden" name="subaction" value="index">
                        <select name="month" class="form-select form-select-sm" style="width:130px">
                            <?php
                            $months = ['01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril',
                                       '05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto',
                                       '09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'];
                            foreach ($months as $num => $name):
                            ?>
                            <option value="<?php echo $num; ?>" <?php echo $month == $num ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="year" value="<?php echo $year; ?>" min="2020" max="2099"
                               class="form-control form-control-sm" style="width:90px">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </form>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                <div class="page-header">
                    <h1>Relatórios</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active">Relatórios</li>
                        </ol>
                    </nav>
                </div>

                <!-- Cards de totais gerais -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card stat-card blue h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Total de Pacientes</p>
                                    <h3 class="mb-0 fw-bold"><?php echo $totalPatients; ?></h3>
                                </div>
                                <i class="fas fa-users stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card green h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Total de Atendimentos</p>
                                    <h3 class="mb-0 fw-bold"><?php echo $totalRecords; ?></h3>
                                </div>
                                <i class="fas fa-notes-medical stat-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card orange h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Total de Agendamentos</p>
                                    <h3 class="mb-0 fw-bold"><?php echo $totalAppointments; ?></h3>
                                </div>
                                <i class="fas fa-calendar-check stat-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card purple h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Total de Pagamentos</p>
                                    <h3 class="mb-0 fw-bold"><?php echo $totalPayments; ?></h3>
                                </div>
                                <i class="fas fa-money-bill-wave stat-icon text-purple"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo do mês selecionado -->
                <?php
                $monthNames = ['01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril',
                               '05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto',
                               '09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'];
                $monthLabel = ($monthNames[$month] ?? $month) . ' / ' . $year;
                ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Resumo de <?php echo $monthLabel; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 border-end">
                                <p class="text-muted mb-1">Atendimentos no mês</p>
                                <h2 class="fw-bold text-success"><?php echo $recordsThisMonth; ?></h2>
                            </div>
                            <div class="col-md-4 border-end">
                                <p class="text-muted mb-1">Agendamentos no mês</p>
                                <h2 class="fw-bold text-primary"><?php echo $appointmentsThisMonth; ?></h2>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Receita confirmada</p>
                                <h2 class="fw-bold text-info">R$ <?php echo number_format($monthlyRevenue, 2, ',', '.'); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Links para relatórios detalhados -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-notes-medical fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Relatório de Atendimentos</h5>
                                <p class="text-muted small">Atendimentos registrados por período</p>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=records&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                   class="btn btn-success">
                                    <i class="fas fa-eye me-1"></i> Ver Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Atendimentos por Paciente</h5>
                                <p class="text-muted small">Ranking de atendimentos por paciente</p>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=patient-records"
                                   class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> Ver Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-calendar-check fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Relatório de Agendamentos</h5>
                                <p class="text-muted small">Agendamentos por status e período</p>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=appointments&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                   class="btn btn-warning">
                                    <i class="fas fa-eye me-1"></i> Ver Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-money-bill-wave fa-3x text-info mb-3"></i>
                                <h5 class="card-title">Relatório Financeiro</h5>
                                <p class="text-muted small">Receitas e pagamentos por período</p>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=payments&month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                                   class="btn btn-info text-white">
                                    <i class="fas fa-eye me-1"></i> Ver Relatório
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-chart-bar fa-3x text-secondary mb-3"></i>
                                <h5 class="card-title">Relatório Anual</h5>
                                <p class="text-muted small">Visão consolidada do ano <?php echo $year; ?></p>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=annual&year=<?php echo $year; ?>"
                                   class="btn btn-secondary">
                                    <i class="fas fa-eye me-1"></i> Ver Relatório
                                </a>
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
