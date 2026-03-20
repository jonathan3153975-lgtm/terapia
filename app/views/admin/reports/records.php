<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Atendimentos - <?php echo \Config\Config::APP_NAME; ?></title>
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
                    <input type="hidden" name="subaction" value="records">
                    <?php
                    $months = ['01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril','05'=>'Maio','06'=>'Junho',
                               '07'=>'Julho','08'=>'Agosto','09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'];
                    ?>
                    <select name="month" class="form-select form-select-sm" style="width:130px">
                        <?php foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $month == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="year" value="<?php echo $year; ?>" min="2020" max="2099" class="form-control form-control-sm" style="width:90px">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                </form>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1>Relatório de Atendimentos</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports">Relatórios</a></li>
                        <li class="breadcrumb-item active">Atendimentos</li>
                    </ol>
                </nav>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Atendimentos — <?php echo ($months[$month] ?? $month) . '/' . $year; ?>
                    </h5>
                    <span class="badge bg-primary fs-6"><?php echo count($records); ?> registro(s)</span>
                </div>
                <div class="card-body">
                    <?php if (empty($records)): ?>
                    <p class="text-muted text-center py-3">Nenhum atendimento encontrado para o período.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Paciente</th>
                                    <th>Resumo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $r): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($r['record_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
                                    <td><?php $txt = strip_tags($r['notes'] ?? ''); echo htmlspecialchars(mb_strlen($txt) > 80 ? mb_substr($txt, 0, 80) . '...' : $txt); ?></td>
                                    <td>
                                        <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=show&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
