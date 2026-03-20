<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Anual - <?php echo \Config\Config::APP_NAME; ?></title>
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
                <form method="GET" action="<?php echo \Config\Config::APP_URL; ?>/dashboard.php" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="action" value="reports">
                    <input type="hidden" name="subaction" value="annual">
                    <input type="number" name="year" value="<?php echo $year; ?>" min="2020" max="2099" class="form-control form-control-sm" style="width:100px">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Ver Ano</button>
                </form>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Relatório Anual — <?php echo $year; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports">Relatórios</a></li>
                        <li class="breadcrumb-item active">Anual</li>
                    </ol>
                </nav>
            </div>

            <?php
            $months = ['1'=>'Jan','2'=>'Fev','3'=>'Mar','4'=>'Abr','5'=>'Mai','6'=>'Jun',
                       '7'=>'Jul','8'=>'Ago','9'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
            $totalRecords      = array_sum(array_column($monthlyData, 'records'));
            $totalAppointments = array_sum(array_column($monthlyData, 'appointments'));
            $totalRevenue      = array_sum(array_column($monthlyData, 'revenue'));
            ?>

            <!-- Totais anuais -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-success text-center">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Receita Total <?php echo $year; ?></p>
                            <h3 class="fw-bold text-success">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary text-center">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Atendimentos <?php echo $year; ?></p>
                            <h3 class="fw-bold text-primary"><?php echo $totalRecords; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning text-center">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Agendamentos <?php echo $year; ?></p>
                            <h3 class="fw-bold text-warning"><?php echo $totalAppointments; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela mensal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Consolidado Mensal</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover text-center align-middle">
                            <thead>
                                <tr>
                                    <th class="text-start">Mês</th>
                                    <th>Atendimentos</th>
                                    <th>Agendamentos</th>
                                    <th>Receita Confirmada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthlyData as $m => $data): ?>
                                <tr>
                                    <td class="text-start fw-semibold"><?php echo $months[$m] ?? $m; ?></td>
                                    <td>
                                        <?php if ($data['records'] > 0): ?>
                                        <span class="badge bg-primary"><?php echo $data['records']; ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($data['appointments'] > 0): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $data['appointments']; ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="<?php echo $data['revenue'] > 0 ? 'text-success fw-bold' : 'text-muted'; ?>">
                                        R$ <?php echo number_format($data['revenue'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-start">Total</td>
                                    <td><?php echo $totalRecords; ?></td>
                                    <td><?php echo $totalAppointments; ?></td>
                                    <td class="text-success">R$ <?php echo number_format($totalRevenue, 2, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
