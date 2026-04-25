<?php $title = 'Dashboard Terapeuta'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<?php
$engagementRate = $totalPatients > 0 ? (int) round(((int) $activePatients / (int) $totalPatients) * 100) : 0;
$contentVolume = (int) $totalMaterials + (int) $totalTasks + (int) ($totalDevotionalReflections ?? 0);
?>
<div class="container-fluid page-wrap dashboard-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="dashboard-hero dashboard-hero--therapist">
    <div class="dashboard-hero-grid">
      <div>
        <span class="dashboard-kicker">Painel terapêutico</span>
        <h1 class="dashboard-title">Sua rotina clínica com mais leveza, foco e presença.</h1>
        <p class="dashboard-copy">Acompanhe pacientes, organize a agenda e visualize rapidamente onde vale sua atenção hoje.</p>
        <div class="dashboard-stat-chips">
          <span class="dashboard-stat-chip"><i class="fa-solid fa-users"></i><?php echo (int) $totalPatients; ?> pacientes</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-heart-pulse"></i><?php echo $engagementRate; ?>% ativos</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-layer-group"></i><?php echo $contentVolume; ?> conteúdos e registros</span>
        </div>
        <div class="dashboard-quick-actions">
          <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients"><i class="fa-solid fa-users me-2"></i>Gerenciar pacientes</a>
          <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-schedule"><i class="fa-solid fa-calendar-days me-2"></i>Ver agenda</a>
        </div>
      </div>
      <aside class="dashboard-highlight-card">
        <span class="dashboard-highlight-kicker">Radar do dia</span>
        <div class="dashboard-highlight-row">
          <strong><?php echo (int) $scheduledAppointments; ?></strong>
          <span>consultas ainda previstas</span>
        </div>
        <div class="dashboard-highlight-row">
          <strong><?php echo (int) $completedAppointments; ?></strong>
          <span>consultas já realizadas</span>
        </div>
        <div class="dashboard-highlight-row <?php echo !empty($pendingReviewPatients) ? 'is-emphasis' : ''; ?>">
          <strong><?php echo (int) $pendingReviewPatients; ?></strong>
          <span>pacientes aguardando revisão</span>
        </div>
        <p class="dashboard-highlight-note mb-0">Use o painel para equilibrar acompanhamento clínico, materiais e tarefas em um só fluxo.</p>
      </aside>
    </div>
  </section>

  <div class="dashboard-metric-grid">
    <article class="dashboard-kpi-card dashboard-kpi-card--peach">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-users"></i>Pacientes</span>
      <h2><?php echo (int) $totalPatients; ?></h2>
      <p>Base total atualmente vinculada ao seu cuidado.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--mint">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-crown"></i>Pacientes ativos</span>
      <h2><?php echo (int) $activePatients; ?></h2>
      <p>Engajamento atual da sua carteira terapêutica.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--sky">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-circle-check"></i>Consultas realizadas</span>
      <h2><?php echo (int) $completedAppointments; ?></h2>
      <p>Histórico consolidado de atendimentos concluídos.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--sun">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-calendar-days"></i>Consultas agendadas</span>
      <h2><?php echo (int) $scheduledAppointments; ?></h2>
      <p>Compromissos futuros já programados na agenda.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--lavender">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-book"></i>Materiais</span>
      <h2><?php echo (int) $totalMaterials; ?></h2>
      <p>Acervo pronto para fortalecer a jornada dos pacientes.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--rose">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-list-check"></i>Tarefas</span>
      <h2><?php echo (int) $totalTasks; ?></h2>
      <p>Atividades ativas ou reutilizáveis para acompanhamento.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--aqua">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-sun"></i>Registros devocionais</span>
      <h2><?php echo (int) ($totalDevotionalReflections ?? 0); ?></h2>
      <p>Reflexões salvas que ajudam a ler a evolução emocional.</p>
    </article>
  </div>

  <?php if (!empty($pendingReviewPatients)): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span><i class="fa-solid fa-triangle-exclamation me-1"></i>Há <?php echo (int) $pendingReviewPatients; ?> paciente(s) pendente(s) de revisão.</span>
      <a class="btn btn-sm btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Revisar agora</a>
    </div>
  <?php endif; ?>

  <div class="row g-4 align-items-stretch">
    <div class="col-12 col-xxl-8">
      <section class="card dashboard-chart-card h-100">
        <div class="card-body p-4">
          <div class="dashboard-section-head mb-3">
            <div>
              <span class="dashboard-section-kicker">Evolução mensal</span>
              <h5 class="mb-1">Ritmo da sua operação clínica</h5>
              <p class="mb-0 text-muted">Compare crescimento de pacientes, consultas e tarefas ao longo dos meses.</p>
            </div>
          </div>
          <canvas id="therapistTrendChart" height="120"></canvas>
        </div>
      </section>
    </div>
    <div class="col-12 col-xxl-4">
      <section class="card dashboard-section-card h-100">
        <div class="card-body p-4">
          <div class="dashboard-section-head mb-3">
            <div>
              <span class="dashboard-section-kicker">Ações rápidas</span>
              <h5 class="mb-1">Atalhos do terapeuta</h5>
            </div>
          </div>
          <div class="dashboard-actions-grid">
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-financial">
              <i class="fa-solid fa-wallet"></i>
              <strong>Financeiro</strong>
              <span>Receitas, pendências e indicadores.</span>
            </a>
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-materials">
              <i class="fa-solid fa-book-open"></i>
              <strong>Materiais</strong>
              <span>Organize conteúdos e apoios terapêuticos.</span>
            </a>
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-predefined-tasks">
              <i class="fa-solid fa-list-check"></i>
              <strong>Tarefas</strong>
              <span>Monte fluxos terapêuticos com mais rapidez.</span>
            </a>
            <a class="dashboard-action-tile" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-devotionals">
              <i class="fa-solid fa-sun"></i>
              <strong>Devocional</strong>
              <span>Acompanhe registros e reflexões dos pacientes.</span>
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function () {
  if (typeof Chart === 'undefined') return;
  var labels = <?php echo json_encode($chartLabels ?? []); ?>;
  var patients = <?php echo json_encode($chartPatients ?? []); ?>;
  var appointments = <?php echo json_encode($chartAppointments ?? []); ?>;
  var tasks = <?php echo json_encode($chartTasks ?? []); ?>;

  var el = document.getElementById('therapistTrendChart');
  if (!el) return;

  new Chart(el, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { label: 'Novos pacientes', data: patients, backgroundColor: 'rgba(249, 132, 74, .82)', borderRadius: 12, maxBarThickness: 24 },
        { label: 'Consultas', data: appointments, backgroundColor: 'rgba(77, 144, 142, .82)', borderRadius: 12, maxBarThickness: 24 },
        { label: 'Tarefas', data: tasks, backgroundColor: 'rgba(144, 190, 109, .82)', borderRadius: 12, maxBarThickness: 24 }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            usePointStyle: true,
            boxWidth: 10
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(38, 70, 83, .08)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
