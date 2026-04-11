<?php $title = 'Dashboard Admin'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-4">Administrador Geral</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-user-doctor me-1"></i>Terapeutas</small><h2><?php echo (int)$totalTherapists; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-users me-1"></i>Pacientes</small><h2><?php echo (int)$totalPatients; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-crown me-1"></i>Assinaturas ativas</small><h2><?php echo (int)$activeSubscriptions; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-sack-dollar me-1"></i>Total recebido</small><h2>R$ <?php echo number_format((float)$totalReceived,2,',','.'); ?></h2></div></div></div>
    <div class="col-md-12 col-xl-3"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-hard-drive me-1"></i>Espaço de materiais</small><h2><?php echo number_format($usedBytes/(1024*1024),2,',','.'); ?> MB</h2><small class="text-muted">Arquivos totais: <?php echo (int)$totalFiles; ?></small></div></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
      <div class="card h-100"><div class="card-body"><h6 class="mb-3">Evolução mensal</h6><canvas id="adminTrendChart" height="120"></canvas></div></div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="card h-100"><div class="card-body"><h6 class="mb-3">Distribuição atual</h6><canvas id="adminOverviewChart" height="180"></canvas></div></div>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapists">Gerenciar terapeutas</a>
    <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patient-packages">Pacotes de pacientes</a>
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
        { label: 'Terapeutas', data: therapists, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.12)', tension: .35, fill: true },
        { label: 'Pacientes', data: patients, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.12)', tension: .35, fill: true },
        { label: 'Assinaturas', data: subscriptions, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', tension: .35, fill: true },
        { label: 'Receita', data: revenue, borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.1)', tension: .35, fill: false, yAxisID: 'y1' }
      ] },
      options: { responsive: true, scales: { y: { beginAtZero: true }, y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } } } }
    });
  }

  var overviewCtx = document.getElementById('adminOverviewChart');
  if (overviewCtx) {
    new Chart(overviewCtx, {
      type: 'doughnut',
      data: {
        labels: ['Terapeutas', 'Pacientes', 'Assinaturas ativas'],
        datasets: [{ data: [<?php echo (int)$totalTherapists; ?>, <?php echo (int)$totalPatients; ?>, <?php echo (int)$activeSubscriptions; ?>], backgroundColor: ['#0ea5e9','#22c55e','#f59e0b'] }]
      }
    });
  }
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
