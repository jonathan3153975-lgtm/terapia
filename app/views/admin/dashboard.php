<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \Config\Config::APP_NAME; ?> — Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo \Config\Config::APP_URL; ?>/public/css/dashboard.css" rel="stylesheet">
    <style>
        /* ── KPI Cards ── */
        .kpi-card { border: none; border-radius: 14px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
        .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.12); }
        .kpi-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1; color: #1f2937; }
        .kpi-label { font-size: .78rem; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        .kpi-trend { font-size: .78rem; }

        /* ── Quick Actions ── */
        .quick-action { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 10px;
                        border-radius: 12px; border: 2px solid #e5e7eb; text-decoration: none;
                        color: #374151; transition: all .2s; }
        .quick-action:hover { border-color: #2563eb; background: #eff6ff; color: #2563eb; transform: translateY(-2px); }
        .quick-action i { font-size: 1.6rem; }
        .quick-action span { font-size: .8rem; font-weight: 600; text-align: center; }

        /* ── Chart cards ── */
        .chart-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .chart-card .card-header { background: #fff; border-bottom: 1px solid #f3f4f6; border-radius: 14px 14px 0 0 !important; }

        /* ── Recent tables ── */
        .recent-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .table-hover tbody tr:hover { background-color: #f0f9ff; }

        /* ── Today timeline ── */
        .timeline-item { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-time { min-width: 50px; font-weight: 700; font-size: .9rem; color: #2563eb; }
        .timeline-body { flex: 1; }
        .timeline-name { font-weight: 600; font-size: .9rem; color: #111827; }
        .timeline-status { font-size: .75rem; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php $activeMenu = 'dashboard'; include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <button class="btn-menu" id="menuBtn"><i class="fas fa-bars"></i></button>
            <div class="topbar-actions">
                <span class="text-muted small me-3">
                    <i class="fas fa-calendar me-1"></i>
                    <?php echo date('d/m/Y'); ?>
                </span>
                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Novo Agendamento
                </a>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="page-content" style="overflow-y:auto">

            <!-- Page title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-0">Dashboard</h1>
                    <p class="mb-0">Bem-vindo(a), <?php echo \Helpers\Auth::userName(); ?>!</p>
                </div>
            </div>

            <!-- ── KPI CARDS ── -->
            <div class="row g-3 mb-4">
                <!-- Pacientes -->
                <div class="col-6 col-xl-3">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="text-decoration-none">
                        <div class="card kpi-card shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <div class="kpi-value"><?php echo $totalPatients; ?></div>
                                    <div class="kpi-label">Pacientes</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Atendimentos -->
                <div class="col-6 col-xl-3">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=records" class="text-decoration-none">
                        <div class="card kpi-card shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <div>
                                    <div class="kpi-value"><?php echo $totalRecords; ?></div>
                                    <div class="kpi-label">Atendimentos</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Agendamentos pendentes -->
                <div class="col-6 col-xl-3">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="text-decoration-none">
                        <div class="card kpi-card shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="kpi-value"><?php echo $pendingAppointments; ?></div>
                                    <div class="kpi-label">Ag. Pendentes</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Receita do mês -->
                <div class="col-6 col-xl-3">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="text-decoration-none">
                        <div class="card kpi-card shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="kpi-value" style="font-size:1.35rem">R$ <?php echo number_format($monthlyRevenue, 0, ',', '.'); ?></div>
                                    <div class="kpi-label">Receita este mês</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <?php if (!empty($isTherapistDashboard)): ?>
                <div class="col-6 col-xl-3">
                    <div class="card kpi-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <div class="kpi-value"><?php echo (int) ($totalTasks ?? 0); ?></div>
                                <div class="kpi-label">Tarefas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card kpi-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <div class="kpi-value"><?php echo (int) ($totalMessagesSent ?? 0); ?></div>
                                <div class="kpi-label">Mensagens enviadas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card kpi-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div>
                                <div class="kpi-value"><?php echo (int) ($totalStoredMaterials ?? 0); ?></div>
                                <div class="kpi-label">Materiais armazenados</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card kpi-card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="kpi-icon bg-dark bg-opacity-10 text-dark">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="kpi-value"><?php echo (int) ($totalMessagesStored ?? 0); ?></div>
                                <div class="kpi-label">Mensagens armazenadas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── AÇÕES RÁPIDAS ── -->
            <div class="card chart-card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Ações Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients&subaction=create" class="quick-action">
                                <i class="fas fa-user-plus text-primary"></i>
                                <span>Novo Paciente</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=create" class="quick-action">
                                <i class="fas fa-calendar-plus text-success"></i>
                                <span>Novo Agendamento</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments&subaction=create" class="quick-action">
                                <i class="fas fa-file-invoice-dollar text-info"></i>
                                <span>Registrar Pagamento</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="quick-action">
                                <i class="fas fa-calendar-alt text-warning"></i>
                                <span>Ver Agenda</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports" class="quick-action">
                                <i class="fas fa-chart-bar text-danger"></i>
                                <span>Relatórios</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=patients" class="quick-action">
                                <i class="fas fa-users text-secondary"></i>
                                <span>Lista de Pacientes</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── GRÁFICOS ── -->
            <div class="row g-3 mb-4">
                <!-- Gráfico de linha: atendimentos + receita por mês -->
                <div class="col-lg-8">
                    <div class="card chart-card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Atendimentos &amp; Receita — Últimos 6 Meses</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="lineChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Gráfico de rosca: agendamentos por status -->
                <div class="col-lg-4">
                    <div class="card chart-card h-100">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-info"></i>Agendamentos por Status</h6>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <canvas id="doughnutChart" style="max-height:220px"></canvas>
                            <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center" id="doughnutLegend"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── AGENDA DE HOJE + PRÓXIMOS ── -->
            <div class="row g-3 mb-4">
                <!-- Hoje -->
                <div class="col-md-6">
                    <div class="card recent-card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-2 text-primary"></i>Agenda de Hoje</h6>
                            <span class="badge bg-primary"><?php echo count($todayAppointments); ?></span>
                        </div>
                        <div class="card-body" style="overflow-y:auto; max-height:280px">
                            <?php if (empty($todayAppointments)): ?>
                            <p class="text-muted text-center py-4 mb-0"><i class="fas fa-check-circle text-success me-1"></i>Nenhuma consulta hoje.</p>
                            <?php else: ?>
                            <?php
                            $statusBadge = ['confirmed'=>'primary','pending'=>'warning','cancelled'=>'danger','completed'=>'success'];
                            $statusLabel = ['confirmed'=>'Confirmado','pending'=>'Pendente','cancelled'=>'Cancelado','completed'=>'Concluído'];
                            foreach ($todayAppointments as $a): ?>
                            <div class="timeline-item">
                                <div class="timeline-time"><?php echo date('H:i', strtotime($a['appointment_date'])); ?></div>
                                <div class="timeline-body">
                                    <div class="timeline-name"><?php echo htmlspecialchars($a['patient_name']); ?></div>
                                    <span class="badge bg-<?php echo $statusBadge[$a['status']] ?? 'secondary'; ?> timeline-status">
                                        <?php echo $statusLabel[$a['status']] ?? $a['status']; ?>
                                    </span>
                                </div>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=show&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Próximos agendamentos -->
                <div class="col-md-6">
                    <div class="card recent-card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-week me-2 text-success"></i>Próximos Agendamentos</h6>
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=calendar" class="btn btn-sm btn-outline-success">Ver todos</a>
                        </div>
                        <div class="card-body" style="overflow-y:auto; max-height:280px">
                            <?php if (empty($upcomingAppointments)): ?>
                            <p class="text-muted text-center py-4 mb-0">Nenhum agendamento futuro.</p>
                            <?php else: ?>
                            <?php foreach ($upcomingAppointments as $a): ?>
                            <div class="timeline-item">
                                <div class="timeline-time text-success" style="min-width:80px; font-size:.78rem">
                                    <?php echo date('d/m', strtotime($a['appointment_date'])); ?><br>
                                    <span class="text-muted"><?php echo date('H:i', strtotime($a['appointment_date'])); ?></span>
                                </div>
                                <div class="timeline-body">
                                    <div class="timeline-name"><?php echo htmlspecialchars($a['patient_name']); ?></div>
                                    <span class="badge bg-<?php echo $statusBadge[$a['status']] ?? 'secondary'; ?> timeline-status">
                                        <?php echo $statusLabel[$a['status']] ?? $a['status']; ?>
                                    </span>
                                </div>
                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=appointments&subaction=show&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ÚLTIMOS ATENDIMENTOS + PAGAMENTOS ── -->
            <div class="row g-3 mb-4">
                <!-- Últimos atendimentos -->
                <div class="col-lg-7">
                    <div class="card recent-card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-notes-medical me-2 text-success"></i>Últimos Atendimentos</h6>
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=reports&subaction=records" class="btn btn-sm btn-outline-success">Ver todos</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr><th>Data</th><th>Paciente</th><th>Resumo</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentRecords)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhum atendimento registrado.</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($recentRecords as $r):
                                            $preview = mb_substr(strip_tags($r['notes'] ?? ''), 0, 50);
                                        ?>
                                        <tr>
                                            <td class="text-nowrap"><?php echo date('d/m/Y', strtotime($r['record_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($preview) ?: '—'; ?></td>
                                            <td>
                                                <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=records&subaction=show&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Últimos pagamentos -->
                <div class="col-lg-5">
                    <div class="card recent-card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-wallet me-2 text-info"></i>Últimos Pagamentos</h6>
                            <a href="<?php echo \Config\Config::APP_URL; ?>/dashboard.php?action=payments" class="btn btn-sm btn-outline-info">Ver todos</a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Paciente</th><th>Valor</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentPayments)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Nenhum pagamento.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($recentPayments as $p):
                                        $pb = $p['status'] === 'paid' ? 'success' : ($p['status'] === 'cancelled' ? 'danger' : 'warning');
                                        $pl = $p['status'] === 'paid' ? 'Pago' : ($p['status'] === 'cancelled' ? 'Cancelado' : 'Pendente');
                                    ?>
                                    <tr>
                                        <td class="small"><?php echo htmlspecialchars($p['patient_name'] ?? '—'); ?></td>
                                        <td class="fw-bold small">R$ <?php echo number_format($p['amount'], 2, ',', '.'); ?></td>
                                        <td><span class="badge bg-<?php echo $pb; ?>"><?php echo $pl; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if ($pendingRevenue > 0): ?>
                            <div class="px-3 py-2 bg-warning bg-opacity-10 border-top small">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                <strong>R$ <?php echo number_format($pendingRevenue, 2, ',', '.'); ?></strong> em pagamentos pendentes
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div><!-- /dashboard-container -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?php echo \Config\Config::APP_URL; ?>/public/js/main.js"></script>
<script>
// ── Dados PHP → JS ──────────────────────────────────────────
const labels   = <?php echo json_encode($chartLabels); ?>;
const records  = <?php echo json_encode($chartRecords); ?>;
const revenues = <?php echo json_encode($chartRevenue); ?>;
const apptStatus = <?php echo json_encode(array_values($apptByStatus)); ?>;

// ── Gráfico de Linha ─────────────────────────────────────────
const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Atendimentos',
                data: records,
                backgroundColor: 'rgba(37,99,235,.15)',
                borderColor: '#2563eb',
                borderWidth: 2,
                borderRadius: 6,
                type: 'bar',
                yAxisID: 'y',
            },
            {
                label: 'Receita (R$)',
                data: revenues,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,.1)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#10b981',
                tension: 0.4,
                type: 'line',
                yAxisID: 'y1',
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        if (ctx.dataset.label === 'Receita (R$)') {
                            return ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits:2});
                        }
                        return ' ' + ctx.parsed.y + ' atendimento(s)';
                    }
                }
            }
        },
        scales: {
            y:  { beginAtZero: true, position: 'left',  title: { display: true, text: 'Atendimentos' }, ticks: { stepSize: 1 } },
            y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Receita (R$)' }, grid: { drawOnChartArea: false },
                  ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') } }
        }
    }
});

// ── Gráfico de Rosca ─────────────────────────────────────────
const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
const dLabels = ['Confirmados', 'Pendentes', 'Cancelados', 'Concluídos'];
const dColors = ['#2563eb', '#f59e0b', '#ef4444', '#10b981'];
new Chart(doughnutCtx, {
    type: 'doughnut',
    data: {
        labels: dLabels,
        datasets: [{
            data: apptStatus,
            backgroundColor: dColors,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } }
        },
        cutout: '65%'
    }
});

// Legenda customizada
const legend = document.getElementById('doughnutLegend');
dLabels.forEach(function(l, i) {
    legend.innerHTML += `<span class="d-flex align-items-center gap-1 small">
        <span style="width:10px;height:10px;border-radius:50%;background:${dColors[i]};display:inline-block"></span>
        ${l}: <strong>${apptStatus[i]}</strong>
    </span>`;
});
</script>
</body>
</html>
