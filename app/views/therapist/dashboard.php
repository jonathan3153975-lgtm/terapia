<?php $title = 'Dashboard Terapeuta'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <h3 class="mb-4">Dashboard do Terapeuta</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-users me-1"></i>Pacientes</small><h2><?php echo (int)$totalPatients; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-crown me-1"></i>Pacientes ativos</small><h2><?php echo (int)$activePatients; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-circle-check me-1"></i>Consultas realizadas</small><h2><?php echo (int)$completedAppointments; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-calendar-days me-1"></i>Consultas agendadas</small><h2><?php echo (int)$scheduledAppointments; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-book me-1"></i>Materiais</small><h2><?php echo (int)$totalMaterials; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-list-check me-1"></i>Tarefas</small><h2><?php echo (int)$totalTasks; ?></h2></div></div></div>
    <div class="col-md-6 col-xl-2"><div class="card card-kpi"><div class="card-body"><small><i class="fa-solid fa-sun me-1"></i>Registros devocionais</small><h2><?php echo (int)($totalDevotionalReflections ?? 0); ?></h2></div></div></div>
  </div>

  <?php if (!empty($pendingReviewPatients)): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-triangle-exclamation me-1"></i>Há <?php echo (int)$pendingReviewPatients; ?> paciente(s) pendente(s) de revisão.</span>
      <a class="btn btn-sm btn-outline-dark" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Revisar agora</a>
    </div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h6 class="mb-3">Evolução mensal</h6>
      <canvas id="therapistTrendChart" height="110"></canvas>
    </div>
  </div>

  <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=patients">Gerenciar pacientes</a>
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
        { label: 'Novos pacientes', data: patients, backgroundColor: 'rgba(14,165,233,.75)' },
        { label: 'Consultas', data: appointments, backgroundColor: 'rgba(34,197,94,.75)' },
        { label: 'Tarefas', data: tasks, backgroundColor: 'rgba(245,158,11,.75)' }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
