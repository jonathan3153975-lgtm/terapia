<?php $title = 'Dashboard Admin'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<?php
$averageRevenuePerSubscription = $activeSubscriptions > 0 ? ((float) $totalReceived / (int) $activeSubscriptions) : 0.0;
?>
<div class="container-fluid page-wrap dashboard-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="dashboard-hero dashboard-hero--admin">
    <div class="dashboard-hero-grid">
      <div>
        <span class="dashboard-kicker">Visão executiva</span>
        <h1 class="dashboard-title">Uma leitura mais clara e vibrante da operação inteira.</h1>
        <p class="dashboard-copy">Acompanhe crescimento, ocupação, receita e saúde da plataforma sem perder velocidade na tomada de decisão.</p>
        <div class="dashboard-stat-chips">
          <span class="dashboard-stat-chip"><i class="fa-solid fa-user-doctor"></i><?php echo (int) $totalTherapists; ?> terapeutas</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-users"></i><?php echo (int) $totalPatients; ?> pacientes</span>
          <span class="dashboard-stat-chip"><i class="fa-solid fa-crown"></i><?php echo (int) $activeSubscriptions; ?> assinaturas ativas</span>
        </div>
        <div class="dashboard-quick-actions">
          <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists"><i class="fa-solid fa-user-doctor me-2"></i>Gerenciar terapeutas</a>
          <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages"><i class="fa-solid fa-box-open me-2"></i>Pacotes de pacientes</a>
        </div>
      </div>
      <aside class="dashboard-highlight-card">
        <span class="dashboard-highlight-kicker">Resumo financeiro</span>
        <div class="dashboard-highlight-row">
          <strong>R$ <?php echo number_format((float) $totalReceived, 2, ',', '.'); ?></strong>
          <span>valor total recebido</span>
        </div>
        <div class="dashboard-highlight-row">
          <strong><?php echo number_format($usedBytes / (1024 * 1024), 2, ',', '.'); ?> MB</strong>
          <span>armazenamento usado em uploads</span>
        </div>
        <div class="dashboard-highlight-row is-emphasis">
          <strong>R$ <?php echo number_format($averageRevenuePerSubscription, 2, ',', '.'); ?></strong>
          <span>média por assinatura ativa</span>
        </div>
        <p class="dashboard-highlight-note mb-0">Você tem <?php echo (int) $totalFiles; ?> arquivo(s) no servidor e <?php echo (int) ($materialsFiles ?? 0); ?> material(is) armazenado(s).</p>
      </aside>
    </div>
  </section>

  <div class="dashboard-metric-grid dashboard-metric-grid--compact">
    <article class="dashboard-kpi-card dashboard-kpi-card--sky">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-user-doctor"></i>Terapeutas</span>
      <h2><?php echo (int) $totalTherapists; ?></h2>
      <p>Rede total de profissionais ativos ou em operação.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--mint">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-users"></i>Pacientes</span>
      <h2><?php echo (int) $totalPatients; ?></h2>
      <p>Base consolidada na plataforma.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--sun">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-crown"></i>Assinaturas ativas</span>
      <h2><?php echo (int) $activeSubscriptions; ?></h2>
      <p>Pacientes com acesso vigente ao ecossistema.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--peach">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-sack-dollar"></i>Total recebido</span>
      <h2>R$ <?php echo number_format((float) $totalReceived, 2, ',', '.'); ?></h2>
      <p>Receita acumulada pela operação.</p>
    </article>
    <article class="dashboard-kpi-card dashboard-kpi-card--lavender">
      <span class="dashboard-kpi-label"><i class="fa-solid fa-hard-drive"></i>Uploads</span>
      <h2><?php echo number_format($usedBytes / (1024 * 1024), 2, ',', '.'); ?> MB</h2>
      <p><?php echo (int) $totalFiles; ?> arquivo(s) no servidor.</p>
    </article>
  </div>

  <div class="row g-4 align-items-stretch">
    <div class="col-12 col-xxl-8">
      <section class="card dashboard-chart-card h-100">
        <div class="card-body p-4">
          <div class="dashboard-section-head mb-3">
            <div>
              <span class="dashboard-section-kicker">Evolução mensal</span>
              <h5 class="mb-1">Crescimento da plataforma</h5>
              <p class="mb-0 text-muted">Leia cadastros, assinaturas e receita em uma mesma curva de acompanhamento.</p>
            </div>
          </div>
          <canvas id="adminTrendChart" height="120"></canvas>
        </div>
      </section>
    </div>
    <div class="col-12 col-xxl-4">
      <section class="card dashboard-section-card h-100">
        <div class="card-body p-4">
          <div class="dashboard-section-head mb-3">
            <div>
              <span class="dashboard-section-kicker">Composição atual</span>
              <h5 class="mb-1">Distribuição operacional</h5>
            </div>
          </div>
          <canvas id="adminOverviewChart" height="210"></canvas>
          <div class="dashboard-inline-notes mt-3">
            <span><i class="fa-solid fa-folder-open"></i>Materiais: <?php echo (int) ($materialsFiles ?? 0); ?> arquivo(s)</span>
            <span><i class="fa-solid fa-database"></i><?php echo number_format(((int) ($materialsBytes ?? 0)) / (1024 * 1024), 2, ',', '.'); ?> MB em materiais</span>
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
  var therapists = <?php echo json_encode($chartTherapists ?? []); ?>;
  var patients = <?php echo json_encode($chartPatients ?? []); ?>;
  var subscriptions = <?php echo json_encode($chartSubscriptions ?? []); ?>;
  var revenue = <?php echo json_encode($chartRevenue ?? []); ?>;

  var trendCtx = document.getElementById('adminTrendChart');
  if (trendCtx) {
    new Chart(trendCtx, {
      type: 'line',
      data: { labels: labels, datasets: [
        { label: 'Terapeutas', data: therapists, borderColor: '#4d908e', backgroundColor: 'rgba(77, 144, 142, .12)', tension: .35, fill: true },
        { label: 'Pacientes', data: patients, borderColor: '#90be6d', backgroundColor: 'rgba(144, 190, 109, .12)', tension: .35, fill: true },
        { label: 'Assinaturas', data: subscriptions, borderColor: '#f6bd60', backgroundColor: 'rgba(246, 189, 96, .14)', tension: .35, fill: true },
        { label: 'Receita', data: revenue, borderColor: '#f9844a', backgroundColor: 'rgba(249, 132, 74, .12)', tension: .35, fill: false, yAxisID: 'y1' }
      ] },
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
          y: { beginAtZero: true, grid: { color: 'rgba(38, 70, 83, .08)' } },
          y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  var overviewCtx = document.getElementById('adminOverviewChart');
  if (overviewCtx) {
    new Chart(overviewCtx, {
      type: 'doughnut',
      data: {
        labels: ['Terapeutas', 'Pacientes', 'Assinaturas ativas'],
        datasets: [{ data: [<?php echo (int)$totalTherapists; ?>, <?php echo (int)$totalPatients; ?>, <?php echo (int)$activeSubscriptions; ?>], backgroundColor: ['#4d908e', '#90be6d', '#f9844a'], borderWidth: 0 }]
      },
      options: {
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              boxWidth: 10
            }
          }
        }
      }
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
