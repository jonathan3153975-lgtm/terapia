<?php $title = 'Portal do Paciente'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <?php
  $nextLabel = 'Sem sessão agendada';
  if (!empty($nextAppointment['session_date'])) {
    $nextLabel = date('d/m/Y H:i', strtotime((string) $nextAppointment['session_date']));
  }
  ?>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="mb-0">Meu dashboard</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="<?php echo $appUrl; ?>/patient.php?action=tasks"><i class="fa-solid fa-list-check me-1"></i>Minhas tarefas</a>
      <a class="btn btn-primary" href="<?php echo $appUrl; ?>/patient.php?action=materials"><i class="fa-solid fa-book me-1"></i>Meus materiais</a>
    </div>
  </div>

  <?php if (!empty($activeSubscription)): ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span><i class="fa-solid fa-crown me-1"></i>Assinatura ativa: <strong><?php echo htmlspecialchars((string) ($activeSubscription['plan_name'] ?? 'Plano')); ?></strong></span>
      <?php if (!empty($activeSubscription['ends_at'])): ?><small>Válida até <?php echo date('d/m/Y', strtotime((string) $activeSubscription['ends_at'])); ?></small><?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted"><i class="fa-solid fa-calendar-day me-1"></i>Dias desde o cadastro</small>
          <h2><?php echo (int) $daysSinceRegister; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted"><i class="fa-solid fa-heart-pulse me-1"></i>Sessões realizadas</small>
          <h2><?php echo (int) $sessionsDone; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted"><i class="fa-solid fa-bell me-1"></i>Próxima sessão</small>
          <div class="fw-semibold mt-2"><?php echo htmlspecialchars($nextLabel); ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card card-kpi h-100">
        <div class="card-body">
          <small class="text-muted"><i class="fa-solid fa-list-check me-1"></i>Tarefas pendentes</small>
          <h2><?php echo (int) $receivedTasks; ?></h2>
          <small class="text-muted">Concluídas: <?php echo (int) ($doneTasks ?? 0); ?> | Materiais: <?php echo (int) $receivedMaterials; ?></small>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card h-100 mt-3">
        <div class="card-body">
          <h6 class="mb-3">Minha evolução mensal</h6>
          <canvas id="patientTrendChart" height="100"></canvas>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="card h-100 mt-3">
        <div class="card-body d-flex flex-column justify-content-center text-center">
          <small class="text-muted mb-1">Taxa de conclusão de tarefas</small>
          <h2 class="mb-1"><?php echo (int) ($completionRate ?? 0); ?>%</h2>
          <div class="progress" style="height:10px;"><div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($completionRate ?? 0); ?>%;"></div></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.addEventListener('load', function () {
  if (typeof Chart === 'undefined') return;
  var labels = <?php echo json_encode($chartLabels ?? []); ?>;
  var sessions = <?php echo json_encode($chartSessions ?? []); ?>;
  var tasksDone = <?php echo json_encode($chartTasksDone ?? []); ?>;
  var el = document.getElementById('patientTrendChart');
  if (!el) return;

  new Chart(el, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        { label: 'Sessões', data: sessions, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .35 },
        { label: 'Tarefas concluídas', data: tasksDone, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.12)', fill: true, tension: .35 }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });
});
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
